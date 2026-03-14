<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use RyanChandler\FilamentProgressColumn\ProgressColumn; 
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; 
    protected static ?string $navigationGroup = 'Project Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Information')
                  
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Project Name')
                            ->required()
                            ->columnSpanFull(),

                      // --- MULAI PERUBAHAN PROJECT LEADER ---
                        Forms\Components\Select::make('leader_id')
                            ->label('Project Leader')
                            ->relationship(
                                'leader',
                                'name',
                                modifyQueryUsing: fn ($query) => $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin')),
                            )
                            ->searchable(['name', 'email']) // Tambah ini biar bisa cari pakai email
                            ->preload()
                            ->required()
                         
                            // --- RENDER HTML UNTUK AVATAR & EMAIL ---
                            ->getOptionLabelFromRecordUsing(function (\App\Models\User $record) {
                                $safeName = e($record->name);
                                $safeEmail = e($record->email);
                                $avatarUrl = $record->getFilamentAvatarUrl();
                                $safeAvatarUrl = e($avatarUrl);
                                $initials = collect(explode(' ', $record->name))
                                    ->map(fn ($n) => mb_substr($n, 0, 1))
                                    ->take(2)
                                    ->join('');
                                $safeInitials = e($initials);
                                $avatarHtml = $avatarUrl
                                    ? "<img class='w-6 h-6 rounded-full object-cover' src='{$safeAvatarUrl}' alt='{$safeName}' />"
                                    : "<div class='w-6 h-6 rounded-full bg-teal-500 text-white text-[10px] font-bold flex items-center justify-center'>{$safeInitials}</div>";
                                return "
                                    <div class='flex items-center gap-2'>
                                        {$avatarHtml}
                                        <div class='flex flex-col'>
                                            <span class='font-medium'>{$safeName}</span>
                                            <span class='text-xs text-gray-500'>{$safeEmail}</span>
                                        </div>
                                    </div>
                                ";
                            })
                            ->allowHtml() 
                            ->columnSpanFull(),
                

                        Forms\Components\Select::make('status')
                            ->label('Project Status')
                            ->options([
                                'Planned' => 'Planned',
                                'In Progress' => 'In Progress',
                                'Done' => 'Done',
                            ])
                            ->default('Planned')
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'Low' => 'Low',
                                'Normal' => 'Normal',
                                'High' => 'High',
                                'Urgent' => 'Urgent',
                            ])
                            ->default('Normal')
                            ->required(),
                    ])
                    ->columns(2),

              Forms\Components\Section::make('Timeline')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')->native(false)->beforeOrEqual('end_date')
    ->validationMessages([
        'before_or_equal' => 'The start date must not exceed the deadline.',]),
                        Forms\Components\DatePicker::make('end_date')->native(false)->afterOrEqual('start_date')
    ->validationMessages([
        'after_or_equal' => 'The deadline cannot be earlier than the start date.',]),
                    ])->columns(2),

                Forms\Components\Section::make('Description Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Full Description')
                            ->disableToolbarButtons(['attachFiles', 'codeBlock'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // INI YANG BIKIN KALAU BARIS DI-KLIK LANGSUNG KE KANBAN 
            ->recordUrl(fn (Project $record): string => \App\Filament\Pages\AdminKanbanBoard::getUrl(['project' => $record->id]))
            
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('leader.name')
                    ->label('Project Leader')
                    ->description(fn (Project $record) => $record->leader?->email)
                    // ->icon('heroicon-m-user-circle')
                    ->searchable()
                    ->sortable(),

               

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Planned' => 'gray',
                        'In Progress' => 'warning',
                        'Done' => 'success',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Low' => 'gray',
                        'Normal' => 'info',
                        'High' => 'warning',
                        'Urgent' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('timeline')
                    ->label('Timeline')
                    ->getStateUsing(function ($record) {
                        if (!$record->start_date && !$record->end_date) return '—';
                        $start = $record->start_date ? Carbon::parse($record->start_date)->format('d M Y') : '?';
                        $end = $record->end_date ? Carbon::parse($record->end_date)->format('d M Y') : '?';
                        return "{$start} – {$end}";
                    })
                    ->badge()
                    ->color(function (Project $record) {
                        if (!$record->end_date) return 'gray';
                        if ($record->status === 'Done') return 'success';

                        $endDate = Carbon::parse($record->end_date)->startOfDay();
                        $today = Carbon::today();

                        if ($endDate->lt($today)) return 'danger';
                        if ($endDate->eq($today)) return 'warning';
                        return 'info';
                    })
                    ->icon(function (Project $record) {
                        if (!$record->end_date) return 'heroicon-m-calendar';
                        if ($record->status === 'Done') return 'heroicon-m-check-circle';
                        
                        $endDate = Carbon::parse($record->end_date)->startOfDay();
                        $today = Carbon::today();

                        if ($endDate->lt($today)) return 'heroicon-m-exclamation-circle';
                        if ($endDate->eq($today)) return 'heroicon-m-clock';
                        
                        return 'heroicon-m-calendar';
                    }),

               Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function (Project $record) {
                        $totalTasks = (int) ($record->tasks_count ?? 0);
                        $doneTasks = (int) ($record->done_tasks_count ?? 0);
                        $percentage = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                        // Warna bar: Biru saat jalan, Hijau saat 100%, Abu-abu kalau kosong
                        $barColor = 'rgb(59 130 246)'; // Biru (Primary/Info)
                        if ($totalTasks > 0 && $percentage === 100) {
                            $barColor = 'rgb(16 185 129)'; // Hijau (Success)
                        } elseif ($totalTasks === 0) {
                            $barColor = 'rgb(156 163 175)'; // Abu-abu
                        }

                        return new \Illuminate\Support\HtmlString('
                            <div style="min-width: 120px; max-width: 200px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 0.75rem; font-weight: 600;" class="text-gray-700 dark:text-gray-200">' . $percentage . '%</span>
                                    <span style="font-size: 0.75rem; font-weight: 500;" class="text-gray-500 dark:text-gray-400">' . $doneTasks . '/' . $totalTasks . ' Tasks</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-gray-700" style="width: 100%; border-radius: 9999px; height: 6px; overflow: hidden;">
                                    <div style="height: 100%; border-radius: 9999px; background-color: ' . $barColor . '; transition: width 0.5s ease-in-out; width: ' . $percentage . '%;"></div>
                                </div>
                            </div>
                        ');
                    }),

                Tables\Columns\ImageColumn::make('members')
                    ->label('Team')
                    ->getStateUsing(fn (Project $record) => $record->members
                        ->map(fn ($member) => $member->getFilamentAvatarUrl())
                        ->filter()
                        ->values()
                        ->all()
                    )
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(isSeparate: true),
            ])
            ->filters([
                TernaryFilter::make('is_archived')
                    ->label('Project Status')
                    ->placeholder('Active Projects Only')
                    ->trueLabel('View Archived Projects')
                    ->falseLabel('View Active Projects')
                    ->queries(
                        true: fn ($query) => $query->where('is_archived', true),
                        false: fn ($query) => $query->where('is_archived', false),
                        blank: fn ($query) => $query->where('is_archived', false),
                    ),

                SelectFilter::make('leader_id')
                    ->label('Filter by Leader')
                    ->relationship('leader', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Project Status')
                    ->multiple() 
                    ->options([
                        'Planned' => 'Planned',
                        'In Progress' => 'In Progress',
                        'Done' => 'Done',
                    ]),

                SelectFilter::make('priority')
                    ->label('Priority')
                    ->multiple()
                    ->options([
                        'Low' => 'Low',
                        'Normal' => 'Normal',
                        'High' => 'High',
                        'Urgent' => 'Urgent',
                    ]),

                Filter::make('timeline_range')
                    ->form([
                        DatePicker::make('created_from')->label('Start Date From'),
                        DatePicker::make('created_until')->label('End Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'Start from: ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until: ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
               Tables\Actions\Action::make('open_kanban')
                    ->label('View Kanban')
                    ->icon('heroicon-m-view-columns')
                    ->color('success')
                    ->button()
                    ->url(fn (Project $record): string => \App\Filament\Pages\AdminKanbanBoard::getUrl(['project' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('primary'),

                Tables\Actions\Action::make('toggle_archive')
                    ->label(fn (Project $record) => $record->is_archived ? 'Unarchive' : 'Archive')
                    ->icon(fn (Project $record) => $record->is_archived ? 'heroicon-m-arrow-path' : 'heroicon-m-archive-box-arrow-down')
                    ->color(fn (Project $record) => $record->is_archived ? 'gray' : 'warning')
                    ->iconButton()
                    ->tooltip(fn (Project $record) => $record->is_archived ? 'Unarchive Project' : 'Archive Project')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Project $record) => $record->is_archived ? 'Unarchive Project?' : 'Archive Project?')
                    ->modalDescription(function (Project $record) {
                        if ($record->is_archived) return 'The project will reappear in the active list.';
                        if ($record->status !== 'Done') {
                            return "⚠️ WARNING: This project is still '{$record->status}'. Are you sure you want to archive it before completion?";
                        }
                        return "Project status is 'Done'. It will be moved to the Archive tab.";
                    })
                    ->modalSubmitActionLabel(fn (Project $record) => 
                        (!$record->is_archived && $record->status !== 'Done') ? 'Yes, Force Archive' : 'Yes, Continue'
                    )
                    ->action(function (Project $record) {
                        $record->update(['is_archived' => ! $record->is_archived]);
                        \Filament\Notifications\Notification::make()
                            ->title($record->is_archived ? 'Project Archived' : 'Project Unarchived')
                            ->success()
                            ->send();
                    }),
            ])
           ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // 1. Delete Bulk Action
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),

                    // 2. Archive Bulk Action
                    Tables\Actions\BulkAction::make('archive_massal')
                        ->label('Archive Selected')
                        ->icon('heroicon-m-archive-box-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Archive Selected Projects?')
                        ->modalDescription('All selected projects will be moved to the Archive tab.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_archived' => true]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Selected projects archived successfully!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // 3. Update Status Bulk Action
                    Tables\Actions\BulkAction::make('update_status_massal')
                        ->label('Update Status')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Select New Status')
                                ->options([
                                    'Planned' => 'Planned',
                                    'In Progress' => 'In Progress',
                                    'Done' => 'Done',
                                ])
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Projects status updated successfully!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['leader', 'members'])
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn (Builder $query) => $query->where('status', 'Done'),
            ]);
    }
}
