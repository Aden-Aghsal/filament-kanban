<?php

namespace App\Filament\App\Pages;

use App\Models\Task;
use App\Models\Project;
use App\Enums\TaskStatus;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use App\Models\User;
use Filament\Notifications\Notification;

class MemberKanbanBoard extends KanbanBoard
{
    
    protected static string $statusEnum = TaskStatus::class;
    protected static string $model = Task::class;
    protected static string $recordTitleAttribute = 'title';
    protected static string $recordStatusAttribute = 'status';
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    
    
    protected static bool $shouldRegisterNavigation = false; 

    public ?int $filterProjectId = null;

 
    public function mount(): void
    {
        $this->filterProjectId = request()->query('project');

        
        if (! $this->filterProjectId) {
            redirect()->to('/app/projects');
        }
    }

   
    public function getTitle(): string 
    {
        $project = Project::find($this->filterProjectId);
        return $project ? " {$project->name}" : 'Papan Kerja Tim';
    }

    protected function statuses(): \Illuminate\Support\Collection
    {
        
        return collect(TaskStatus::statuses());
    }

    
    protected function eloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::eloquentQuery()
            ->where('project_id', $this->filterProjectId)
            ->orderBy('sort_order');
    }

    
    protected function getEditModalFormSchema(string|int|null $recordId): array
    {
        return [
            TextInput::make('title')->label('Judul Tugas')->required(),
            
          
            Select::make('project_id')
                ->label('Proyek')
                ->options(Project::pluck('name', 'id'))
                ->default($this->filterProjectId)
                ->disabled()
                ->dehydrated()
                ->required(),

         
            Select::make('user_id')
                ->label('Penanggung Jawab')
                ->relationship('assignee', 'name')
                ->searchable()
                ->preload()
                ->options(function () {
                    $projectId = $this->filterProjectId;
                    if (!$projectId) return [];
                    return \App\Models\User::whereHas('projects', function ($query) use ($projectId) {
                        $query->where('project_id', $projectId);
                    })->pluck('name', 'id');
                }),

            RichEditor::make('description')->label('Deskripsi'),
            
            Select::make('priority')
                ->options([
                    'High' => '🔥 High',
                    'Normal' => '🟦 Normal',
                    'Low' => '🟩 Low',
                ])
                ->default('Normal'),
                
            DatePicker::make('due_date')->label('Tenggat Waktu'),
        ];
    }

   
    protected function getHeaderActions(): array
    {
        return [
           
            Action::make('back')
                ->label('Kembali')
                ->color('gray')
                ->url('/app/projects'), 

           
            Action::make('inviteMember')
                ->label('Undang Tim')
                ->icon('heroicon-m-user-plus')
                ->color('info')
                ->form([
                    Select::make('user_id')
                        ->label('Pilih Anggota Baru')
                        ->searchable()
                        ->preload()
                        ->required()
                       
                        ->options(function () {
                            $currentProjectId = $this->filterProjectId;
                            
                            return User::whereDoesntHave('projects', function ($query) use ($currentProjectId) {
                                $query->where('project_id', $currentProjectId);
                            })->pluck('name', 'id');
                        }),
                ])
                ->action(function (array $data) {
                    $project = Project::find($this->filterProjectId);
                    
                    if ($project) {
                       
                        $project->members()->attach($data['user_id']);

                        Notification::make()
                            ->title('Anggota Berhasil Ditambahkan')
                            ->success()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Tambah Tugas')
                ->model(Task::class)
                ->form([
                    TextInput::make('title')->label('Judul Tugas')->required(),
                    RichEditor::make('description')->label('Deskripsi'),
                    Select::make('priority')
                        ->options(['High'=>'High', 'Normal'=>'Normal', 'Low'=>'Low'])
                        ->default('Normal'),
                    DatePicker::make('due_date')->label('Deadline'),
                    
                    
                    Hidden::make('project_id')->default($this->filterProjectId),
                    Hidden::make('user_id')->default(auth()->id()), 
                    Hidden::make('status')->default('Initiated'), 
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    $data['project_id'] = $this->filterProjectId;
                    // $data['user_id'] = auth()->id(); 
                    return $data;
                }),
        ];
    }

   
    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Task::find($recordId)->update(['status' => $status]);
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Task::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }
}