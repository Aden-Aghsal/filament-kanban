<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Project extends Model
{
    use HasFactory;

    // 1. PERBAIKAN FILLABLE (Cuma daftar nama kolom)
    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'start_date', // Jangan ada '=> date' di sini
        'end_date',   // Cukup nama kolomnya saja
        'leader_id', 
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            // ucfirst() membuat huruf PERTAMA di kalimat jadi besar (Contoh: "Bikin fitur login")
            set: fn (string $value) => ucfirst($value),
        );
    }

    // 2. PERBAIKAN CASTS (Di sini tempat mengatur tipe data)
    protected $casts = [
        'start_date' => 'date', // Ubah jadi Object Tanggal biar bisa di-format()
        'end_date' => 'date',   // Ubah jadi Object Tanggal
    ];

    // Relasi ke Tugas-Tugas
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Relasi ke Ketua Tim (Leader)
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    // Relasi ke Anggota Tim (Members) 
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withTimestamps();
    }

    // Perhitungan Progress Bar
    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;

        $completed = $this->tasks()->where('status', 'Done')->count();
        
        return round(($completed / $total) * 100);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}