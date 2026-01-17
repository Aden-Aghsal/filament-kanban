<?php

namespace App\Filament\App\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Anggota Tim';

    

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true; 
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Undang Teman')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->visible(fn ($livewire) => 
                        $livewire->getOwnerRecord()->leader_id === auth()->id() || 
                        auth()->user()->hasRole('admin')
                    ),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-m-trash')
                    ->visible(fn ($livewire) => 
                        $livewire->getOwnerRecord()->leader_id === auth()->id() || 
                        auth()->user()->hasRole('admin')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->visible(fn ($livewire) => 
                            $livewire->getOwnerRecord()->leader_id === auth()->id() || 
                            auth()->user()->hasRole('admin')
                        ),
                ]),
            ]);
    }
}