<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar; 
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; 
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser, HasAvatar 
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'google_id','role', 'avatar_url', 
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') return $this->hasRole('admin');
        if ($panel->getId() === 'app') return true;
        return false;
    }


    public function getFilamentAvatarUrl(): ?string
    {
        
        if (empty($this->avatar_url)) {
            return null;
        }

       
        if (str_contains($this->avatar_url, 'http')) {
            return $this->avatar_url; 
        }

        
        return Storage::url($this->avatar_url); 
    }

    public function projects(): BelongsToMany
    {
        
        return $this->belongsToMany(Project::class, 'project_user')
            ->withTimestamps();
    }
}