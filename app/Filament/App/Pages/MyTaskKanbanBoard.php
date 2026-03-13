<?php

namespace App\Filament\App\Pages;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Enums\TaskStatus;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\Checkbox;
use App\Filament\App\Pages\MemberKanbanBoard; 
use App\Jobs\SendFilamentDatabaseNotification;

class MyTaskKanbanBoard extends KanbanBoard
{
    public bool $disableEditModal = true;
    protected static string $model = Task::class;
    protected static string $statusEnum = TaskStatus::class;
    protected static string $recordTitleAttribute = 'title';
    protected static string $recordStatusAttribute = 'status';
    
    // Icon dan Nama Menu di Sidebar
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'My Tasks';
   protected static ?string $slug = '';

    protected function findUserTask(int|string $recordId): ?Task
    {
        return Task::whereKey($recordId)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function recordClicked($recordId, array $data): void
    {
        if (! $this->findUserTask($recordId)) {
            abort(403);
        }

        redirect()->to(\App\Filament\App\Resources\TaskResource::getUrl('edit', ['record' => $recordId]));
    }

    protected function statuses(): Collection
    {
        return collect(TaskStatus::statuses());
    }

    // KUNCI UTAMA: Hanya panggil tugas milik User yang sedang Login
    protected function records(): Collection
    {
        $tasks = Task::query()
            ->where('user_id', auth()->id()) // Filter tugas milik saya
            ->with(['project.leader', 'assignee'])  // Bawa data project + leader untuk kartu
            ->orderByRaw('due_date IS NULL, due_date ASC') 
            ->orderBy('sort_order') 
            ->get();

        // Flag khusus agar card tahu ini konteks "My Tasks"
        $tasks->each(fn ($task) => $task->setAttribute('is_my_tasks', true));

        return $tasks;
    }

    // --- FUNGSI PENYUAP DATA KE DALAM FORM EDIT ---
    protected function getEditModalRecordData(int|string $recordId, array $data): array
    {
        // Tarik data task sekalian relasi project dan leadernya
        $task = Task::with('project.leader')
            ->whereKey($recordId)
            ->where('user_id', auth()->id())
            ->first();
        if (! $task) {
            abort(403);
        }
        
        return array_merge($task->toArray(), [
            // Paksa format tanggal biar dibaca DatePicker
            'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null,
            
            // Masukkan nama leader ke dalam form 'project_leader'
            'project_leader' => $task->project?->leader?->name ?? 'No Leader Assigned',
        ]);
    }

 protected function getEditModalFormSchema(string|int|null $recordId): array
    {
        // Ambil data task beserta relasi project dan leadernya
        $task = $recordId ? $this->findUserTask($recordId) : null;
        if ($recordId && ! $task) {
            abort(403);
        }
        $leaderName = $task?->project?->leader?->name ?? 'No Leader Assigned';

        return [
            TextInput::make('title')->label('Task Title')->required(),
            
            // Di My Tasks, Project tidak boleh diubah (Disabled) tapi harus kelihatan
            Select::make('project_id')
                ->label('Project')
                ->options(Project::pluck('name', 'id'))
                ->disabled()
                ->dehydrated(false), // Cegah error saat save

            // Nama Project Leader (Read Only)
            TextInput::make('project_leader')
                ->label('Project Leader')
                ->disabled() 
                ->dehydrated(false),

            RichEditor::make('description')->label('Description')->disableToolbarButtons([
        'attachFiles', // Ini yang mematikan fitur upload gambar/file
    ]),
            
            // Saya tambahkan juga Subtasks biar fiturnya sama persis dengan MemberKanban
            Repeater::make('subtasks')
                ->label('Subtasks')
                ->schema([
                    TextInput::make('title')->label('Step Name')->required(),
                    Checkbox::make('is_completed')->label('Completed')->default(false),
                ])
                ->columns(2) 
                ->defaultItems(0), 

            Select::make('priority')
                ->options([
                    'High' => 'High',
                    'Normal' => 'Normal',
                    'Low' => 'Low',
                ])
                ->default('Normal'),
                
         
           DatePicker::make('due_date')
                ->native(true) 
                ->displayFormat('d M Y')
                ->closeOnDateSelection()
                ->label('Due Date'),
        ];
    }

    // --- METHOD EDIT WITH NOTIFICATION TO EVERYONE ---
    protected function editRecord($recordId, array $data, array $state): void
    {
        $task = $this->findUserTask($recordId);
        if (! $task) {
            abort(403);
        }
        if ($task) {
            $payload = $data;

            if (! array_key_exists('due_date', $payload) && array_key_exists('due_date', $state)) {
                $payload['due_date'] = $state['due_date'];
            }

            if (array_key_exists('due_date', $payload)) {
                $payload['due_date'] = filled($payload['due_date'])
                    ? Carbon::parse($payload['due_date'])->toDateString()
                    : null;
            }

            $task->update($payload);
            
            // Notify everyone else in the project about the update
            $project = $task->project;
            if ($project) {
                $allMembers = $project->members->push($project->leader);
                
                $usersToNotify = $allMembers->filter(function ($user) {
                    return $user && $user->id !== auth()->id();
                })->unique('id');

                if ($usersToNotify->isNotEmpty()) {
                    $notification = Notification::make()
                        ->title('Task Updated')
                        ->body('' . e(auth()->user()->name) . ' updated the task: ' . e($task->title) . '')
                        ->icon('heroicon-o-pencil-square')
                        ->success()
                        ->url(MemberKanbanBoard::getUrl(['project' => $project->id]));

                    SendFilamentDatabaseNotification::dispatch(
                        $notification->toArray(),
                        $usersToNotify->pluck('id')->all(),
                    );
                }
            }

            Notification::make()->title('Task Updated Successfully')->success()->send();

            $this->dispatch('$refresh');
        }
    }

    // --- METHOD COMMENT WITH TARGETED NOTIFICATION ---
    public function commentTaskAction(): Action
    {
        return Action::make('commentTask')
            ->modalHeading('Task Comment')
            ->modalWidth('md') 
            ->modalSubmitActionLabel('SEND')
            ->form(function (array $arguments) {
                $task = $this->findUserTask($arguments['record'] ?? null);
                
                return [
                    Placeholder::make('history')
                        ->hiddenLabel() 
                        ->content(view('filament.kanban.comments-history', ['task' => $task])),
                        
                    Textarea::make('new_comment')
                        ->hiddenLabel()
                        ->placeholder('Type your reply here...')
                        ->required(),
                ];
            })
            ->action(function (array $data, array $arguments) {
                $task = $this->findUserTask($arguments['record']);
                if (! $task) {
                    abort(403);
                }

                $comments = $task->comments ?? [];
                
                $comments[] = [
                    'user_name' => auth()->user()->name,
                    'content' => $data['new_comment'],
                    'created_at' => now()->format('d M, H:i'), 
                ];
                
                $task->update(['comments' => $comments]);
                
                // TARGETED NOTIFICATION LOGIC
                $targetUser = null;
                
                if ($task->user_id && auth()->id() !== $task->user_id) {
                    $targetUser = User::find($task->user_id);
                } elseif ($task->project && auth()->id() !== $task->project->leader_id) {
                    $targetUser = User::find($task->project->leader_id);
                }

                if ($targetUser) {
                    $notification = Notification::make()
                        ->title('New Comment')
                        ->body(e(auth()->user()->name) . ' commented on task: ' . e($task->title))
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->info()
                        ->url(MemberKanbanBoard::getUrl(['project' => $task->project_id]));

                    SendFilamentDatabaseNotification::dispatch(
                        $notification->toArray(),
                        [$targetUser->id],
                    );
                }
                
                Notification::make()->title('Comment sent successfully!')->success()->send();
            });
    }

    // --- METHOD STATUS CHANGED WITH NOTIFICATION TO EVERYONE ---
    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        $record = $this->findUserTask($recordId);
        if (! $record) {
            abort(403);
        }
        if ($record) {
            $record->update(['status' => $status]);

            $project = $record->project;

            if ($project) {
                $allMembers = $project->members->push($project->leader);
                
                $usersToNotify = $allMembers->filter(function ($user) {
                    return $user && $user->id !== auth()->id();
                })->unique('id');

                if ($usersToNotify->isNotEmpty()) {
                    $notification = Notification::make()
                        ->title('Task Progress Updated')
                        ->body(e(auth()->user()->name) . ' moved the task ' . e($record->title) . ' to ' . e(ucfirst($status)))
                        ->icon('heroicon-o-arrows-right-left')
                        ->success()
                        ->url(MemberKanbanBoard::getUrl(['project' => $project->id]));

                    SendFilamentDatabaseNotification::dispatch(
                        $notification->toArray(),
                        $usersToNotify->pluck('id')->all(),
                    );
                }
            }
        }
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Task::whereKey($id)
                ->where('user_id', auth()->id())
                ->update(['sort_order' => $index + 1]);
        }
    }

    protected function getRecordContent(\Illuminate\Database\Eloquent\Model $record): string|null
    {
        return view('filament.kanban.card-content', [
            'record' => $record,
            // 'showProjectName' => true, <-- Ini sudah dihapus karena kita pakai trik URL (isMyTasks) di blade
        ])->render();
    }
}
