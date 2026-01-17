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
            // KOTAK 1: Info Utama
            Forms\Components\Section::make('Informasi Proyek')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Proyek')
                        ->required()
                        ->placeholder('Misal: Website Toko Online'),

                    // PERUBAHAN 1: Ganti Client Name jadi Priority
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
                ])->columns(2),

            // KOTAK 2: Jadwal (Sama seperti sebelumnya)
            Forms\Components\Section::make('Timeline')
                ->schema([
                    Forms\Components\DatePicker::make('start_date'),
                    Forms\Components\DatePicker::make('end_date'),
                ])->columns(2),

            // KOTAK 3: Detail & Upload
Forms\Components\Section::make('Detail')
    ->schema([
        Forms\Components\RichEditor::make('description')
            ->label('Deskripsi Lengkap')
            ->placeholder('Tulis detail proyek di sini (Teks saja)...')
            
            // HAPUS konfigurasi fileAttachmentsDirectory yang tadi
            
            // TAMBAHKAN INI: Matikan tombol upload gambar
            ->disableToolbarButtons([
                'attachFiles', // Tombol klip/gambar akan hilang
                'codeBlock',   // (Opsional) Matikan blok kode jika tidak perlu
            ])
            
            ->columnSpanFull(),
    ]),
        ]);
}


    public static function table(Table $table): Table
{
    return $table
        ->columns([
            // 1. Nama Project
            Tables\Columns\TextColumn::make('name')
                ->searchable(),
            
            // 2. Progress Bar (Plugin)
            ProgressColumn::make('completion_percentage')
                ->label('Progress')
                ->color('warning')
                ->poll('5s'),

            // 3. Total Task
            Tables\Columns\TextColumn::make('tasks_count')
                ->counts('tasks')
                ->label('Total Task'),
        ]) // <--- Perhatikan penutup array di sini
        ->actions([
            Tables\Actions\Action::make('open_kanban')
                ->label('Buka Papan')
                ->icon('heroicon-m-view-columns')
                ->color('success')
                ->url(fn (Project $record): string => 
                    MemberKanbanBoard::getUrl(['project' => $record->id])
                ),
                Tables\Actions\EditAction::make()
                ->label('Detail & Tim'), 
        
        ]);

        
}
    public static function getRelations(): array
{
    return [
        // Pakai tanda backslash (\) di depan biar PHP baca dari akar folder
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
    // Tampilkan proyek di mana saya adalah LEADER atau ANGGOTA
    return parent::getEloquentQuery()->whereHas('members', function ($query) {
        $query->where('user_id', auth()->id());
    });
}
}
