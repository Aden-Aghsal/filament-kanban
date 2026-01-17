<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Siapa yang boleh melihat daftar User di sidebar?
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Siapa yang boleh membuat/edit/hapus user?
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }
}