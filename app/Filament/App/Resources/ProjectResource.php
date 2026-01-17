<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProjectResource\Pages;
use App\Filament\App\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use RyanChandler\FilamentProgressColumn\ProgressColumn;
use App\Filament\App\Pages\MemberKanbanBoard;


class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

        public static function form(Form $form): Form
{
    return $form
        ->schema([
            
            Forms\Components\Section::make('Informasi Proyek')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Proyek')
                        ->required()
                        ->placeholder('Misal: Website Toko Online'),

                   
                    Forms\Components\Select::make('priority')
                        ->label('Prioritas')
                        ->options([
                            'Low' => ' Low',
                            'Normal' => 'Normal',
                            'High' => 'High',
                            'Urgent' => 'Urgent',
                        ])
                        ->default('Normal')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'Planned' => 'Rencana',
                            'In Progress' => 'Sedang Jalan',
                            'Done' => 'Selesai',
                        ])
                        ->default('Planned')
                        ->required(),
                ])->columns(2),

       
            Forms\Components\Section::make('Timeline')
                ->schema([
                    Forms\Components\DatePicker::make('start_date'),
                    Forms\Components\DatePicker::make('end_date'),
                ])->columns(2),

            // 
Forms\Components\Section::make('Detail')
    ->schema([
        Forms\Components\RichEditor::make('description')
            ->label('Deskripsi Lengkap')
            ->placeholder('Tulis detail proyek di sini (Teks saja)...')
            
            
            
           
            ->disableToolbarButtons([
                'attachFiles', 
                'codeBlock',   
            ])
            
            ->columnSpanFull(),
    ]),
        ]);
}


  public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Project Name')
                ->searchable()
                ->weight('bold') // Biar lebih tegas
                ->description(fn (Project $record): string => \Illuminate\Support\Str::limit($record->description, 30)), // Tambah deskripsi kecil di bawah judul

            Tables\Columns\ImageColumn::make('members.avatar_url') 
                ->label('Team')
                ->circular()
                ->stacked() 
                ->limit(3) 
                ->tooltip(fn (Project $record): string => $record->members->pluck('name')->implode(', ')),

        
            ProgressColumn::make('completion_percentage')
                ->label('Progress')
                ->color(fn ($state) => match(true) {
                    $state >= 100 => 'success',
                    $state >= 50 => 'warning',
                    default => 'danger',
                })
                ->poll('5s'),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'completed' => 'success',
                    'active' => 'primary',
                    'on_hold' => 'warning',
                    'archived' => 'gray',
                    default => 'info',
                }),

            Tables\Columns\TextColumn::make('due_date')
                ->label('Deadline')
                ->date('d M Y')
                ->sortable()
                ->icon('heroicon-m-calendar'),

            Tables\Columns\TextColumn::make('tasks_count')
                ->counts('tasks')
                ->label('Tasks')
                ->badge()
                ->color('gray'),
        ])
        ->actions([
            
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('open_kanban')
                    ->label('Kanban Board')
                    ->icon('heroicon-m-view-columns')
                    ->color('info')
                    ->url(fn (Project $record): string => 
                        MemberKanbanBoard::getUrl(['project' => $record->id])
                    ),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), 
            ])
        ]);
}
        
    public static function getRelations(): array
{
    return [
        
        \App\Filament\App\Resources\ProjectResource\RelationManagers\MembersRelationManager::class,
    ];
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
{
  
    return parent::getEloquentQuery()->whereHas('members', function ($query) {
        $query->where('user_id', auth()->id());
    });
}
}
