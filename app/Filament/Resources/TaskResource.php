<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\Project; 
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    
    protected static ?string $navigationGroup = 'Manajemen Tugas'; // Mengelompokkan menu
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        // Kita gunakan schema form yang sama dengan Kanban agar konsisten
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('assignee', 'name')->label('Assignee'),
                Forms\Components\Select::make('priority')
                    ->options(['High'=>'High', 'Normal'=>'Normal', 'Low'=>'Low']),
                Forms\Components\RichEditor::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                
                // Menampilkan nama Proyek
                Tables\Columns\TextColumn::make('project.name')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                // Menampilkan siapa yang mengerjakan
                Tables\Columns\TextColumn::make('assignee.name')
                    ->icon('heroicon-m-user')
                    ->placeholder('Unassigned'),

                // Status dengan warna-warni
                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('priority')
                    ->color(fn (string $state): string => match ($state) {
                        'High' => 'danger',
                        'Normal' => 'info',
                        'Low' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // FILTER CANGGIH: Admin bisa pilih task dari proyek mana
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Filter by Project')
                    ->relationship('project', 'name'),
                    
                // Filter berdasarkan Status
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\TaskStatus::class),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}