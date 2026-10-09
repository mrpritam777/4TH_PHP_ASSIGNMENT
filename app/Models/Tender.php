<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tender extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_AWARDED = 'awarded';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuses that may be targeted by a cancellation request. */
    public const CANCELLABLE_STATUSES = [
        self::STATUS_PUBLISHED,
        self::STATUS_CLOSED,
        self::STATUS_AWARDED,
    ];

    protected $fillable = [
        'title',
        'description',
        'infrastructure_type_id',
        'location',
        'estimated_budget',
        'deadline',
        'required_documents',
        'status',
        'allow_bid_edit',
        'published_at',
        'created_by',
        'awarded_contractor_id',
        'awarded_at',
        'awarded_amount',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'published_at' => 'datetime',
            'awarded_at' => 'datetime',
            'required_documents' => 'array',
            'allow_bid_edit' => 'boolean',
            'estimated_budget' => 'float',
            'awarded_amount' => 'float',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InfrastructureType::class, 'infrastructure_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function awardedContractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'awarded_contractor_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(TenderBid::class);
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('location', 'like', "%{$term}%"));
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isExpired(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }

    /**
     * Bidding is only possible while the tender is published and the deadline has not passed.
     */
    public function acceptsBids(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && ! $this->isExpired();
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES, true);
    }

    public function hasPendingCancellation(): bool
    {
        return $this->cancellationRequests()->where('status', 'pending')->exists();
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_PUBLISHED && $this->isExpired()) {
            return 'closed';
        }

        return $this->status;
    }
}
