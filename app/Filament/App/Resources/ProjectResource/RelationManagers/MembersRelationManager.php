<?php

namespace App\Filament\App\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\User;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Team Members';

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
                // --- 1. TABEL MEMBER DENGAN AVATAR ---
                Tables\Columns\TextColumn::make('name')
                    ->label('Name Member')
                    ->searchable()
                    ->formatStateUsing(function (string $state, User $record) {
                        $safeState = e($state);
                        $safeName = e($record->name);
                        $avatarUrl = $record->getFilamentAvatarUrl();
                        $safeAvatarUrl = e($avatarUrl);
                        $initials = collect(explode(' ', $record->name))
                            ->map(fn ($n) => mb_substr($n, 0, 1))
                            ->take(2)
                            ->join('');
                        $safeInitials = e($initials);
                        $avatarHtml = $avatarUrl
                            ? "<img src='{$safeAvatarUrl}' alt='{$safeName}' class='w-8 h-8 rounded-full shadow-sm object-cover'>"
                            : "<div class='w-6 h-6 rounded-full bg-[#2563eb] text-white text-xs font-bold flex items-center justify-center shadow-sm'>{$safeInitials}</div>";

                        // Kembalikan HTML String (Avatar + Nama)
                        return new \Illuminate\Support\HtmlString("
                            <div class='flex items-center gap-3'>
                                {$avatarHtml}
                                <span class='font-medium text-gray-900 dark:text-gray-100'>{$safeState}</span>
                            </div>
                        ");
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->searchable(), // Biar bisa dicari lewat kotak search tabel

                Tables\Columns\TextColumn::make('pivot.created_at')
                    ->label('Joined At')
                    ->dateTime('d M Y')
                    ->icon('heroicon-m-calendar-days')
                    ->badge() // Kasih badge abu-abu biar cakep
                    ->color('gray'),
            ])
            // ->headerActions([]) // <<--- TOMBOL ADD MEMBER SUDAH DIHAPUS TOTAL
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Delete')
                    ->icon('heroicon-m-trash')
                    ->visible(fn ($livewire) => 
                        $livewire->getOwnerRecord()->leader_id === auth()->id() || 
                        auth()->user()->hasRole('admin')
                    )
                    ->after(function (Model $record, $livewire) {
                        Task::where('project_id', $livewire->getOwnerRecord()->id)
                            ->where('user_id', $record->id)
                            ->delete();

                        $project = $livewire->getOwnerRecord();
                        $notification = \Filament\Notifications\Notification::make()
                            ->title(e($record->name) . ' left the team')
                            ->success();

                        $notifyIds = $project->members()
                            ->pluck('users.id')
                            ->push($project->leader_id)
                            ->unique()
                            ->filter(fn ($id) => (int) $id !== (int) $record->id)
                            ->values()
                            ->all();

                        \App\Jobs\SendFilamentDatabaseNotification::dispatch(
                            $notification->toArray(),
                            $notifyIds,
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->visible(fn ($livewire) => 
                            $livewire->getOwnerRecord()->leader_id === auth()->id() || 
                            auth()->user()->hasRole('admin')
                        )
                        ->after(function (\Illuminate\Database\Eloquent\Collection $records, $livewire) {
                            $userIds = $records->pluck('id')->toArray();
                            
                            Task::where('project_id', $livewire->getOwnerRecord()->id)
                                ->whereIn('user_id', $userIds)
                                ->delete();

                            $project = $livewire->getOwnerRecord();
                            foreach ($records as $record) {
                                $notification = \Filament\Notifications\Notification::make()
                                    ->title(e($record->name) . ' left the team')
                                    ->success();

                                $notifyIds = $project->members()
                                    ->pluck('users.id')
                                    ->push($project->leader_id)
                                    ->unique()
                                    ->filter(fn ($id) => (int) $id !== (int) $record->id)
                                    ->values()
                                    ->all();

                                \App\Jobs\SendFilamentDatabaseNotification::dispatch(
                                    $notification->toArray(),
                                    $notifyIds,
                                );
                            }
                        }),
                ]),
            ]);
    }
}
