<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar; // <--- 1. Import ini
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; // <--- 2. Import ini
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser, HasAvatar 
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'avatar_url', 
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Logika Panel Akses 
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') return $this->hasRole('admin');
        if ($panel->getId() === 'app') return true;
        return false;
    }


    public function getFilamentAvatarUrl(): ?string
    {

        return $this->avatar_url 
            ? Storage::url($this->avatar_url) 
            : null; 

    }

    public function projects(): BelongsToMany
    {
        
        return $this->belongsToMany(Project::class, 'project_user')
            ->withTimestamps();
    }
}