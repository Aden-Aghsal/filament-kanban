<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    
    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Project $project): bool
    {

        return $project->members->contains($user->id) || $user->hasRole('admin');
    }

   
    public function create(User $user): bool
    {

        return true; 
    }

  
    public function update(User $user, Project $project): bool
    {
       
        return $user->id === $project->leader_id || $user->hasRole('admin');
    }

    // 5. HAPUS PROYEK
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id || $user->hasRole('admin');
    }
}