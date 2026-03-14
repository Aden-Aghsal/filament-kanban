<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Pages\MemberKanbanBoard;
use App\Filament\App\Resources\ProjectResource\Pages;
use App\Filament\App\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

                        // --- TAMBAHKAN KOTAK PROJECT LEADER DI SINI ---
                       // --- TAMBAHKAN KOTAK PROJECT LEADER DI SINI ---
                        Forms\Components\Select::make('leader_id')
                            ->label('Project Leader')
                            ->relationship(
                                'leader',
                                'name',
                                modifyQueryUsing: fn ($query) => $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin')),
                            )
                            ->searchable(['name', 'email']) // Tambah ini biar bisa cari pakai email
                            ->preload()
                            ->hiddenOn('create')
                            ->required(fn (string $operation): bool => $operation === 'edit')
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
                            ->allowHtml() // KUNCI UTAMA
                            ->columnSpanFull(),
                       
                        // ----------------------------------------------

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

                        Forms\Components\Select::make('status')
                            ->options([
                                'Planned' => 'Planned',
                                'In Progress' => 'In Progress',
                                'Done' => 'Done',
                            ])
                            ->default('Planned')
                            ->required(),
                    ])->columns(2),

                // ... (Section Timeline dan Details biarkan sama) ...
                Forms\Components\Section::make('Timeline')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')->native(false)->beforeOrEqual('end_date')
    ->validationMessages([
        'before_or_equal' => 'The start date must not exceed the deadline.',]),
                        Forms\Components\DatePicker::make('end_date')->native(false)->afterOrEqual('start_date')
    ->validationMessages([
        'after_or_equal' => 'The deadline cannot be earlier than the start date.',]),
                    ])->columns(2),

                Forms\Components\Section::make('Details')
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
            ->recordUrl(fn (Project $record) =>
                MemberKanbanBoard::getUrl(['project' => $record->id])
            )
            ->contentGrid([
                'default' => 1,
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                // KITA HAPUS Panel::make() SUPAYA TIDAK DOUBLE CARD
                Tables\Columns\Layout\Stack::make([
                    
                    // BARIS 1: Judul Project & Status
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('name')
                            ->weight('bold')
                            ->size('lg')
                            ->searchable()
                            ->sortable(),
                    ]),

                    // BARIS 2: Nama Leader
                  // BARIS 2: Nama Leader + Avatar Asli
                    Tables\Columns\TextColumn::make('leader_info')
                        ->extraAttributes(['style' => 'width: 100%; display: block; margin-top: 4px;'])
                        ->getStateUsing(function (Project $record) {
                            $name = $record->leader?->name ?? 'No Leader';
                            $email = $record->leader?->email ?? '';
                            $safeName = e($name);
                            $safeEmail = e($email);
                            
                            // Ambil avatar lewat function Filament (sama kayak di My Tasks)
                            $avatarUrl = $record->leader?->getFilamentAvatarUrl();
                            $safeAvatarUrl = e($avatarUrl);
                            
                            // Buat inisial nama jika tidak ada avatar (misal: "Ardhan Aghsal" -> "AA")
                            $initials = $record->leader 
                                ? collect(explode(' ', $name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') 
                                : '?';
                            $safeInitials = e($initials);

                            // Render tag IMG atau DIV inisial
                            $avatarHtml = $avatarUrl
                                ? '<img src="' . $safeAvatarUrl . '" style="width: 36px; height: 36px; border-radius: 9999px; object-fit: cover; flex-shrink: 0;">'
                                : '<div style="width: 36px; height: 36px; border-radius: 9999px; background-color: #14b8a6; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; color: white; flex-shrink: 0;">' . $safeInitials . '</div>';

                            // Gabungkan avatar dengan nama & email
                            return new \Illuminate\Support\HtmlString('
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    ' . $avatarHtml . '
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600; font-size: 14px;" class="text-gray-800 dark:text-gray-100">' . $safeName . '</span>
                                        <span style="font-size: 12px;" class="text-gray-500 dark:text-gray-400">' . $safeEmail . '</span>
                                    </div>
                                </div>
                            ');
                        }),
                    // BARIS 3: Timeline & Priority
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('timeline')
                            ->label('Timeline')
                            ->getStateUsing(function ($record) {
                                if (!$record->start_date && !$record->end_date) return '—';
                                $start = $record->start_date ? \Carbon\Carbon::parse($record->start_date)->format('d M Y') : '?';
                                $end = $record->end_date ? \Carbon\Carbon::parse($record->end_date)->format('d M Y') : '?';
                                return "{$start} – {$end}";
                            })
                            ->badge() 
                            ->color(function (Project $record) {
                                if (!$record->end_date) return 'gray';
                                
                                $totalTasks = (int) ($record->tasks_count ?? 0);
                                $doneTasks = (int) ($record->done_tasks_count ?? 0);
                                if ($totalTasks > 0 && $totalTasks === $doneTasks) return 'success';

                                $endDate = \Carbon\Carbon::parse($record->end_date)->startOfDay();
                                $today = \Carbon\Carbon::today();

                                if ($endDate->lt($today)) return 'danger'; 
                                if ($endDate->eq($today)) return 'warning'; 
                                return 'gray'; 
                            })
                            ->icon(function (Project $record) {
                                if (!$record->end_date) return 'heroicon-m-calendar';
                                
                                $totalTasks = (int) ($record->tasks_count ?? 0);
                                $doneTasks = (int) ($record->done_tasks_count ?? 0);
                                if ($totalTasks > 0 && $totalTasks === $doneTasks) return 'heroicon-m-check-circle';
                                
                                $endDate = \Carbon\Carbon::parse($record->end_date)->startOfDay();
                                $today = \Carbon\Carbon::today();

                                if ($endDate->lt($today)) return 'heroicon-m-exclamation-circle'; 
                                if ($endDate->eq($today)) return 'heroicon-m-clock'; 
                                
                                return 'heroicon-m-calendar'; 
                            })

                        
                    ]),

                    // BARIS 4: PROGRESS BAR + AUTO STATUS
                  // BARIS 4: PROGRESS BAR + AUTO STATUS + PRIORITY
                    Tables\Columns\Layout\Split::make([
                        // 1. Kolom Progress Bar
                        Tables\Columns\TextColumn::make('progress')
                            ->extraAttributes(['style' => 'width: 100%; display: block;']) 
                            ->getStateUsing(function (Project $record) {
                                $totalTasks = (int) ($record->tasks_count ?? 0);
                                $doneTasks = (int) ($record->done_tasks_count ?? 0);
                                $percentage = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                                // LOGIKA PENENTUAN STATUS (TAMPILAN)
                                $statusDisplay = 'Planned';
                                $statusColor = 'gray';

                                if ($totalTasks > 0) {
                                    if ($percentage === 100) {
                                        $statusDisplay = 'Done';
                                        $statusColor = 'rgb(16 185 129)'; 
                                    } elseif ($percentage > 0) {
                                        $statusDisplay = 'In Progress';
                                        $statusColor = 'rgb(59 130 246)'; 
                                    }
                                }

                                return new \Illuminate\Support\HtmlString('
                                    <div style="width: 100%; margin-top: 8px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                            <span class="text-xs font-bold" style="color: ' . $statusColor . ';">' . $statusDisplay . '</span>
                                            <span class="text-xs font-bold" style="color: rgb(var(--primary-500));">' . $percentage . '% (' . $doneTasks . '/' . $totalTasks . ')</span>
                                        </div>
                                        <div class="bg-gray-200 dark:bg-gray-700" style="width: 100%; border-radius: 9999px; height: 8px; overflow: hidden;">
                                            <div style="height: 100%; border-radius: 9999px; background-color: rgb(var(--primary-500)); transition: width 0.5s ease-in-out; width: ' . $percentage . '%;"></div>
                                        </div>
                                    </div>
                                ');
                            })
                            ->grow(), // <-- Membuat progress bar mengambil semua sisa ruang di kiri

                        // 2. Kolom Priority
                        Tables\Columns\TextColumn::make('priority')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'Low' => 'gray',
                                'Normal' => 'info',
                                'High' => 'warning',
                                'Urgent' => 'danger',
                                default => 'secondary',
                            })
                            ->icon('heroicon-m-flag')
                            ->grow(false), // <-- Mencegah badge melebar
                    ])->from('md')->extraAttributes(['style' => 'align-items: center;']), // Memastikan keduanya sejajar vertikal di tengah
                        

                ])->space(3),
            ])
            ->filters([
               TernaryFilter::make('is_archived')
                    ->label('Project Status')
                    ->placeholder('Active Projects Only') // Teks default saat baru buka halaman
                    ->trueLabel('View Archived Projects') // Opsi 1
                    ->falseLabel('View All Projects')     // Opsi 2
                    ->queries(
                        true: fn ($query) => $query->where('is_archived', true),    // Kalau user pilih "View Archived"
                        false: fn ($query) => $query,                               // Kalau user pilih "View All" (semua dimunculkan)
                        blank: fn ($query) => $query->where('is_archived', false),  // DEFAULT: Hanya yang aktif (tidak diarsip)
                    ),

                SelectFilter::make('status')
                    ->label('Status')
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
                        DatePicker::make('created_from')
                            ->label('From Date'),
                        DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From: ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until: ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('open_kanban')
                    ->label('Kanban')
                    ->icon('heroicon-m-view-columns')
                    ->color('success')
                    ->button()
                    ->url(fn (Project $record): string => 
                        MemberKanbanBoard::getUrl(['project' => $record->id])
                    ),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->color('primary'),

                Tables\Actions\Action::make('toggle_archive')
                    ->label(fn (Project $record) => $record->is_archived ? 'Activate' : 'Archive')
                    ->icon(fn (Project $record) => $record->is_archived ? 'heroicon-m-arrow-path' : 'heroicon-m-archive-box-arrow-down')
                    ->color(fn (Project $record) => $record->is_archived ? 'gray' : 'warning')
                    ->iconButton()
                    ->tooltip(fn (Project $record) => $record->is_archived ? 'Reactivate Project' : 'Archive Project')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Project $record) => $record->is_archived ? 'Reactivate Project?' : 'Archive Project?')
                    ->modalDescription(function (Project $record) {
                        if ($record->is_archived) return 'The project will reappear in the active list.';
                        
                        if ($record->status !== 'Done') {
                            return "⚠️ WARNING: This project's status is still '{$record->status}'. Are you sure you want to archive it even though it's not finished?";
                        }
                        return "The project status is 'Done'. It will be moved to the Archive tab.";
                    })
                    ->modalSubmitActionLabel(fn (Project $record) => 
                        (!$record->is_archived && $record->status !== 'Done') 
                        ? 'Yes, Force Archive' 
                        : 'Yes, Continue'
                    )
                    ->action(function (Project $record) {
                        $record->update(['is_archived' => ! $record->is_archived]);
                        \Filament\Notifications\Notification::make()
                            ->title($record->is_archived ? 'Project Archived' : 'Project Reactivated')
                            ->success()
                            ->send();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Delete Selected'),

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
            RelationManagers\MembersRelationManager::class,
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
            ->whereHas('members', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['leader', 'members'])
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn (Builder $query) => $query->where('status', 'Done'),
            ]);
    }
}
