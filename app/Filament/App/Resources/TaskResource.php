<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationLabel = 'Tugas Saya';

    // FITUR UTAMA: Kacamata Kuda (Member hanya lihat tugasnya sendiri)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // BAGIAN 1: INFORMASI UTAMA
            Forms\Components\Section::make('Detail Tugas')
                ->schema([
                    // Tampilkan Proyek (Disabled/Read Only) biar tau konteks
                    Forms\Components\Select::make('project_id')
                        ->label('Proyek')
                        ->relationship('project', 'name')
                        ->disabled() // Gaboleh ganti proyek sembarangan
                        ->required(),

                    Forms\Components\TextInput::make('title')
                        ->label('Judul Tugas')
                        ->required(),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('status')
                                ->options(\App\Enums\TaskStatus::class)
                                ->required(),
                            
                            Forms\Components\Select::make('priority')
                                ->options([
                                    'High' => '🔥 High', 
                                    'Normal' => '🟦 Normal', 
                                    'Low' => '🟩 Low'
                                ]),

                            Forms\Components\DatePicker::make('due_date')
                                ->label('Tenggat Waktu'),
                        ]),
                    
                    Forms\Components\RichEditor::make('description')
                        ->columnSpanFull(),
                ]),

            // BAGIAN 2: CHECKLIST / SUBTASKS (Fitur ala Trello) ✅
            Forms\Components\Section::make('Checklist Pekerjaan')
                ->schema([
                    Forms\Components\Repeater::make('subtasks')
                        ->label('Langkah-langkah')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nama Langkah')
                                ->required(),
                            Forms\Components\Checkbox::make('is_completed')
                                ->label('Selesai'),
                        ])
                        ->defaultItems(0)
                        ->grid(1) // Tampil baris per baris
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('project.name')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Status'),
            ])
            ->bulkActions([]); // Member tidak boleh hapus massal
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}