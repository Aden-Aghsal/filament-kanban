<?php

namespace App\Filament\App\Resources\TaskResource\Pages;

use App\Filament\App\Resources\TaskResource;
use App\Models\Project;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $projectId = $data['project_id'] ?? null;

        if (! $user || ! $projectId) {
            abort(403);
        }

        if (! $user->hasRole('admin')) {
            $allowed = Project::query()
                ->whereKey($projectId)
                ->where(function ($query) use ($user) {
                    $query->where('leader_id', $user->id)
                        ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('user_id', $user->id));
                })
                ->exists();

            if (! $allowed) {
                abort(403);
            }
        }

        return $data;
    }
  protected function getRedirectUrl(): string
    {
        return \App\Filament\App\Pages\MemberKanbanBoard::getUrl(['project' => $this->record->project_id]);
    }

}
