<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // 1. LIHAT DAFTAR PROYEK
    public function viewAny(User $user): bool
    {
        return true; 
    }

    // 2. LIHAT DETAIL PROYEK (PENTING: Tambahkan ini!)
    // Agar member yang BUKAN leader tetap bisa masuk lihat detail (Read Only)
    public function view(User $user, Project $project): bool
    {
        // Member boleh lihat jika dia ada di dalam tim proyek itu
        // ATAU jika dia Admin
        return $project->members->contains($user->id) || $user->hasRole('admin');
    }

    // 3. BUAT PROYEK BARU
    public function create(User $user): bool
    {
        // Opsional: Kalau Member boleh bikin project sendiri, biarkan true.
        // Kalau cuma admin, ganti jadi: return $user->hasRole('admin');
        return true; 
    }

    // 4. EDIT PROYEK
    public function update(User $user, Project $project): bool
    {
        // Hanya Leader atau Admin yang boleh edit
        return $user->id === $project->leader_id || $user->hasRole('admin');
    }

    // 5. HAPUS PROYEK
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->leader_id || $user->hasRole('admin');
    }
}