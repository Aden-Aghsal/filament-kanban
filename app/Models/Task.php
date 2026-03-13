<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Models\Project; 
use App\Models\User;    
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        'subtasks',
        'comments',
        'sort_order',
        // 'user_id' yang double sudah saya hapus
    ];

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }

    protected $casts = [
        'status' => TaskStatus::class, 
        'subtasks' => 'array',         
        // 'subtasks' double sudah saya hapus
        'comments' => 'array', 
        'due_date' => 'date', 
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
   
}