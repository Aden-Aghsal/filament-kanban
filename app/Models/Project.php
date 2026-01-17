<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'leader_id', // ID Pemilik/Ketua Project
    ];

    // 1. Relasi ke Tugas-Tugas
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // 2. Relasi ke Ketua Tim (Leader)
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // 3. Relasi ke Anggota Tim (Members) 
    public function members(): BelongsToMany
    {
        // Menghubungkan Project ke User lewat tabel pivot 'project_user'
        return $this->belongsToMany(User::class, 'project_user')
        ->withTimestamps();
    }

    // 4. Perhitungan Progress Bar (Otomatis)
    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;

        //  periksa status 'Done' sesuai dengan Enum TaskStatus
        $completed = $this->tasks()->where('status', 'Done')->count();
        
        return round(($completed / $total) * 100);
    }
}