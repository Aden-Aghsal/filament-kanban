<?php

namespace App\Filament\App\Pages;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use App\Enums\TaskStatus;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\Checkbox;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\URL;
use App\Jobs\SendFilamentDatabaseNotification;

class MemberKanbanBoard extends KanbanBoard
{
    public bool $disableEditModal = true; 
    protected static string $statusEnum = TaskStatus::class;
    protected static string $model = Task::class;
    protected static string $recordTitleAttribute = 'title';
    protected static string $recordStatusAttribute = 'status';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static bool $shouldRegisterNavigation = false; 

    public ?int $filterProjectId = null;

    protected function findProjectTask(int|string $recordId): ?Task
    {
        if (! $this->filterProjectId) {
            return null;
        }

        return Task::whereKey($recordId)
            ->where('project_id', $this->filterProjectId)
            ->first();
    }

    public function recordClicked($recordId, array $data): void
    {
        if (! $this->findProjectTask($recordId)) {
            abort(403);
        }

        redirect()->to(\App\Filament\App\Resources\TaskResource::getUrl('edit', ['record' => $recordId]));
    }

    public function mount(): void
    {
        $this->filterProjectId = request()->query('project');

        if (! $this->filterProjectId) {
            redirect()->to('/app/projects');
        }

        $project = Project::with('members')->find($this->filterProjectId);
        if (! $project) {
            abort(404);
        }

        $isAllowed = $project->leader_id === auth()->id()
            || $project->members->contains(auth()->id())
            || auth()->user()->hasRole('admin');

        if (! $isAllowed) {
            abort(403);
        }
    }

    public function getTitle(): string | Htmlable
    {
        $project = Project::find($this->filterProjectId);
        $projectName = $project ? $project->name : 'Team Board';
        $safeProjectName = e($projectName);
        
        $backUrl = url('/app/projects');

        return new HtmlString('
            <div class="flex items-center gap-3">
                <a href="' . $backUrl . '" class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition" title="Kembali ke Daftar Project">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <span>' . $safeProjectName . '</span>
            </div>
        ');
    }

    protected function statuses(): Collection
    {
        return collect(TaskStatus::statuses());
    }

    protected function records(): Collection
    {
        return Task::query()
            ->where('project_id', $this->filterProjectId)
            ->with(['assignee'])
            ->orderBy('sort_order')
            ->get();
    }

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        $record = $this->findProjectTask($recordId);
        if (! $record) {
            abort(403);
        }

        $record->update(['status' => $status]);

        $project = $record->project;

        if ($project) {
            $totalTasks = $project->tasks()->count();
            $doneTasks = $project->tasks()->where('status', 'Done')->count();
            
            $newProjectStatus = 'Planned';
            if ($totalTasks > 0) {
                if ($doneTasks === $totalTasks) {
                    $newProjectStatus = 'Done';
                } elseif ($doneTasks > 0) {
                    $newProjectStatus = 'In Progress';
                }
            }
            
            if ($project->status !== $newProjectStatus) {
                $project->update(['status' => $newProjectStatus]);
            }

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
                    ->actions([ 
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('View Kanban')
                            ->button()
                            ->url(MemberKanbanBoard::getUrl(['project' => $project->id])),
                    ]);

                SendFilamentDatabaseNotification::dispatch(
                    $notification->toArray(),
                    $usersToNotify->pluck('id')->all(),
                );
            }
        }
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Task::whereKey($id)
                ->where('project_id', $this->filterProjectId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    protected function getRecordContent(\Illuminate\Database\Eloquent\Model $record): string|null
    {
        $flagColor = match ($record->priority) {
            'High' => 'text-danger-500',
            'Normal' => 'text-warning-500',
            'Low' => 'text-success-500',
            default => 'text-gray-400',
        };

        $user = $record->assignee ?? $record->user; 
        $userInitials = $user ? substr($user->name, 0, 1) : '?';

        return view('filament.kanban.card-content', [
            'record' => $record,
            'flagColor' => $flagColor,
            'userInitials' => $userInitials,
            'showProjectName' => false,
        ])->render();
    }

    public function commentTaskAction(): Action
    {
        return Action::make('commentTask')
            ->modalHeading('Task Comment')
            ->modalWidth('md') 
            ->modalSubmitActionLabel('SEND')
            ->form(function (array $arguments) {
                $task = $this->findProjectTask($arguments['record'] ?? null);
                
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
                $task = $this->findProjectTask($arguments['record']);
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
                        ->actions([ 
                            \Filament\Notifications\Actions\Action::make('view')
                                ->label('View Task')
                                ->button()
                                ->url(MemberKanbanBoard::getUrl(['project' => $task->project_id])),
                        ]);

                    SendFilamentDatabaseNotification::dispatch(
                        $notification->toArray(),
                        [$targetUser->id],
                    );
                }
                
                Notification::make()->title('Comment sent successfully!')->success()->send();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inviteMember')
                ->label('Collaboration')
                ->icon('heroicon-m-user-plus')
               ->color(Color::hex('#6D28D9'))
                ->form([ 
                    Select::make('user_id')
                        ->label('Find User')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function () {
                            $currentProjectId = $this->filterProjectId;
                            return User::query()
                                ->where('id', '!=', auth()->id()) 
                                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
                                ->whereDoesntHave('projects', function ($query) use ($currentProjectId) {
                                    $query->where('projects.id', $currentProjectId);
                                })
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    $safeName = e($user->name);
                                    $safeEmail = e($user->email);
                                    $avatarUrl = $user->getFilamentAvatarUrl();
                                    $safeAvatarUrl = e($avatarUrl);
                                    $initials = collect(explode(' ', $user->name))
                                        ->map(fn ($n) => mb_substr($n, 0, 1))
                                        ->take(2)
                                        ->join('');
                                    $safeInitials = e($initials);
                                    $avatarHtml = $avatarUrl
                                        ? "<img class='w-6 h-6 rounded-full object-cover' src='{$safeAvatarUrl}' alt='{$safeName}' />"
                                        : "<div class='w-6 h-6 rounded-full bg-teal-500 text-white text-[10px] font-bold flex items-center justify-center'>{$safeInitials}</div>";
                                    $html = "
                                        <div class='flex items-center gap-2'>
                                            {$avatarHtml}
                                            <div class='flex flex-col'>
                                                <span class='font-medium'>{$safeName}</span>
                                                <span class='text-xs text-gray-500'>{$safeEmail}</span>
                                            </div>
                                        </div>
                                    ";
                                    return [$user->id => $html];
                                })
                                ->toArray();
                        })
                        ->allowHtml(), 
                ])
                ->action(function (array $data) { 
                    $project = Project::find($this->filterProjectId);
                    $targetUser = User::find($data['user_id']);
                    $currentUser = auth()->user();

                    if ($project && $targetUser) {
                        $pendingInvite = $targetUser->notifications()
                            ->whereNull('read_at')
                            ->where('data->viewData->type', 'project_invite')
                            ->where('data->viewData->project_id', $project->id)
                            ->exists();

                        if ($pendingInvite) {
                            Notification::make()
                                ->title('Invitation already sent')
                                ->warning()
                                ->send();
                            return;
                        }

                        $ttlDays = (int) config('invites.link_ttl_days', 2);
                        $expiresAt = now()->addDays(max($ttlDays, 1));
                        $acceptUrl = URL::temporarySignedRoute(
                            'project.accept-invite',
                            $expiresAt,
                            ['project' => $project->id, 'user' => $targetUser->id]
                        );
                        $rejectUrl = URL::temporarySignedRoute(
                            'project.reject-invite',
                            $expiresAt,
                            ['project' => $project->id, 'user' => $targetUser->id]
                        );

                        $notification = Notification::make()
                            ->title('Project Invitation')
                            ->body(e($currentUser->name) . ' invited you to join the project: ' . e($project->name))
                            ->viewData([
                                'type' => 'project_invite',
                                'project_id' => $project->id,
                                'user_id' => $targetUser->id,
                            ])
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('accept')
                                    ->label('Accept')
                                    ->button()
                                    ->color('success')
                                    ->url($acceptUrl)
                                    ->markAsRead(), 

                                \Filament\Notifications\Actions\Action::make('reject')
                                    ->label('Decline')
                                    ->color('danger')
                                    ->url($rejectUrl)
                                    ->markAsRead(),
                            ])
                            ->persistent();

                        SendFilamentDatabaseNotification::dispatch(
                            $notification->toArray(),
                            [$targetUser->id],
                        );

                        Notification::make()->title('Invitation Sent')->success()->send();
                    }
                }),

            Action::make('createTask')
                ->label('Create Task')
                ->icon('heroicon-m-plus')
                ->url(fn (): string => \App\Filament\App\Resources\TaskResource::getUrl('create', [
                    'project' => $this->filterProjectId,
                ])),

            ActionGroup::make([
                Action::make('leaveProject')
                    ->label('Leave Project')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Leave Project?')
                    ->modalDescription('Are you sure you want to leave this project? Your tasks in this project will be removed.')
                    ->modalSubmitActionLabel('Yes, Leave')
                    ->action(function () {
                        $project = Project::find($this->filterProjectId);
                        $currentUser = auth()->user();

                        if (! $project || ! $currentUser) {
                            return;
                        }

                        if ($project->leader_id === $currentUser->id || $currentUser->hasRole('admin')) {
                            Notification::make()
                                ->title('You cannot leave this project')
                                ->warning()
                                ->send();
                            return;
                        }

                        Task::where('project_id', $project->id)
                            ->where('user_id', $currentUser->id)
                            ->delete();

                        $project->members()->detach($currentUser->id);

                        $notification = Notification::make()
                            ->title(e($currentUser->name) . ' left the team')
                            ->success();

                        $notifyIds = $project->members()
                            ->pluck('users.id')
                            ->push($project->leader_id)
                            ->unique()
                            ->filter(fn ($id) => (int) $id !== (int) $currentUser->id)
                            ->values()
                            ->all();

                        SendFilamentDatabaseNotification::dispatch(
                            $notification->toArray(),
                            $notifyIds,
                        );

                        Notification::make()
                            ->title('You left the project')
                            ->success()
                            ->send();

                        redirect()->to('/app/projects');
                    }),
            ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->label('More'),
        ];
    }
}
