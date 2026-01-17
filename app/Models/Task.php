<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'project_id', 
        'user_id', 
        'title', 
        'description', 
        'status', 
        'priority', 
        'due_date',
        'subtasks' 
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'subtasks' => 'array',
    ];

    // Relasi ke Project
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi ke User (Orang yang mengerjakan)
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}