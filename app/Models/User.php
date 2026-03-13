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
use Illuminate\Contracts\Auth\MustVerifyEmail;


class User extends Authenticatable implements FilamentUser, HasAvatar,MustVerifyEmail
// , MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'google_id', 
        'avatar_url', 
        'email_verified_at' 
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ==========================================
    // PANEL ACCESS
    // ==========================================
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Admin Panel -> Cuma boleh Admin
        if ($panel->getId() === 'admin') {
            return $this->hasRole('admin');
        }

        // 2. App Panel -> Member & Admin boleh masuk
        if ($panel->getId() === 'app') {
            return $this->hasAnyRole(['admin', 'member']);
        }

        return false;
    }

    // ==========================================
    // AVATAR LOGIC
    // ==========================================
  public function getFilamentAvatarUrl(): ?string
    {
        if (empty($this->avatar_url)) {
            return null;
        }

        if (str_starts_with($this->avatar_url, 'http')) {
            return $this->avatar_url;
        }

        return Storage::disk('public')->url($this->avatar_url);
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user')->withTimestamps();
    }
}