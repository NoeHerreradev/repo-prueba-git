<?php

namespace App\Models;

use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['title', 'description', 'due_at', 'completed_at', 'priority', 'user_id', 'subjectable_type', 'subjectable_id'])]
class Task extends Model
{
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'priority' => TaskPriority::class,
        ];
    }

    public function subjectable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->due_at !== null && $this->due_at->isPast();
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }
}
