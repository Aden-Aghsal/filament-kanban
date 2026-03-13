<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($task->user_id === $user->id) {
            return true;
        }

        $project = $task->project;
        if (! $project) {
            return false;
        }

        return $project->leader_id === $user->id || $project->members->contains($user->id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($task->user_id === $user->id) {
            return true;
        }

        $project = $task->project;
        if (! $project) {
            return false;
        }

        return $project->leader_id === $user->id;
    }
}
