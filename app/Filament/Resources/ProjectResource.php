<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use RyanChandler\FilamentProgressColumn\ProgressColumn; 
use App\Filament\App\Pages\MemberKanbanBoard;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase'; 
    protected static ?string $navigationLabel = 'Kelola Proyek';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Proyek')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Proyek')
                                    ->required(),
                                
                                
                                Forms\Components\Select::make('leader_id')
                                    ->label('Project Leader')
                                    ->relationship('leader', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Admin bisa memindahkan kepemilikan proyek di sini.'),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('priority')
                                        ->label('Prioritas')
                                        ->options([
                                            'Low' => '🟩 Low',
                                            'Normal' => '🟦 Normal',
                                            'High' => '🔥 High',
                                            'Urgent' => '⚡ Urgent',
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
                                ]),
                            ]),
                    ])->columnSpan(2),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Jadwal & Status')
                            ->schema([
                                Forms\Components\DatePicker::make('start_date'),
                                Forms\Components\DatePicker::make('end_date'),
                                
                                
                                Forms\Components\Toggle::make('is_archived')
                                    ->label('Arsipkan Proyek')
                                    ->onColor('danger')
                                    ->helperText('Proyek yang diarsipkan akan disembunyikan dari dashboard member.'),
                                
                                Forms\Components\Select::make('visibility')
                                    ->options([
                                        'public' => 'Public',
                                        'private' => 'Private',
                                    ])
                                    ->default('public'),
                            ]),
                    ])->columnSpan(1),

                Forms\Components\Section::make('Detail')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->disableToolbarButtons(['attachFiles'])
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Project $record) => 'Leader: ' . $record->leader->name), // Tampilkan nama leader di bawah judul
                
                
                ProgressColumn::make('completion_percentage')
                    ->label('Progress')
                    ->color('warning')
                    ->poll('5s'),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Total Task')
                    ->badge(),

                
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Urgent' => 'danger',
                        'High' => 'warning',
                        'Normal' => 'primary',
                        'Low' => 'success',
                    }),

                Tables\Columns\ToggleColumn::make('is_archived')
                    ->label('Arsip'),
            ])
            ->filters([
                
                Tables\Filters\SelectFilter::make('leader_id')
                    ->label('Filter by Leader')
                    ->relationship('leader', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Planned' => 'Rencana',
                        'In Progress' => 'Sedang Jalan',
                        'Done' => 'Selesai',
                    ]),
            ])
            ->actions([
                
                Tables\Actions\Action::make('open_kanban')
                    ->label('Pantau')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn (Project $record): string => 
        route('filament.app.pages.member-kanban-board', ['project' => $record->id])
    )
                    ->openUrlInNewTab(), 

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
}