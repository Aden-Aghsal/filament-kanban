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

class User extends Authenticatable implements FilamentUser, HasAvatar // <--- 3. Tambah HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'avatar_url', // <--- 4. Pastikan ada avatar_url (atau kita buat accessor)
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Logika Panel Akses (Yang sudah kita buat tadi)
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') return $this->hasRole('admin');
        if ($panel->getId() === 'app') return true;
        return false;
    }

    // --- LOGIKA AVATAR ---
    
    // Memberitahu Filament dimana letak URL foto profil user
    public function getFilamentAvatarUrl(): ?string
    {
        // Jika user punya foto di kolom 'avatar_url', pakai itu.
        // Jika tidak, pakai placeholder gratis dari UI Avatars.
        return $this->avatar_url 
            ? Storage::url($this->avatar_url) 
            : null; 
            // Filament otomatis fallback ke inisial jika null, 
            // tapi null di sini agar dia nge-cek disk dulu.
    }

    public function projects(): BelongsToMany
    {
        // User bisa punya banyak Proyek
        return $this->belongsToMany(Project::class, 'project_user')
            ->withTimestamps();
    }
}