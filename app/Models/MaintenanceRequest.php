<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRequest extends Model
{
    public const STATUSES = [
        'pending',
        'under_review',
        'assigned',
        'in_progress',
        'on_hold',
        'completed',
        'cancelled',
    ];

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'request_number',
        'infrastructure_id',
        'reported_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'estimated_cost',
        'actual_cost',
        'start_date',
        'expected_completion_date',
        'completion_date',
        'before_image',
        'after_image',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
            'start_date' => 'date',
            'expected_completion_date' => 'date',
            'completion_date' => 'date',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MaintenanceUpdate::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('request_number', 'like', "%{$term}%")
                ->orWhereHas('infrastructure', fn ($i) => $i->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopePriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }

    public function isOverdue(): bool
    {
        return $this->expected_completion_date
            && $this->expected_completion_date->isPast()
            && ! in_array($this->status, ['completed', 'cancelled']);
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            'low' => 'badge-gray',
            'medium' => 'badge-blue',
            'high' => 'badge-orange',
            'critical' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'badge-yellow',
            'under_review' => 'badge-purple',
            'assigned' => 'badge-blue',
            'in_progress' => 'badge-blue',
            'on_hold' => 'badge-orange',
            'completed' => 'badge-green',
            'cancelled' => 'badge-red',
            default => 'badge-gray',
        };
    }
}
