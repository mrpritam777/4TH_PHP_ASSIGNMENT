<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'business_registration_no',
        'work_categories',
        'experience',
        'licenses',
        'id_document',
        'business_document',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'work_categories' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(TenderBid::class, 'contractor_id');
    }

    public function awardedTenders(): HasMany
    {
        return $this->hasMany(Tender::class, 'awarded_contractor_id');
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Category slugs the contractor works on.
     *
     * @return array<int, string>
     */
    public function categorySlugs(): array
    {
        $ids = is_array($this->work_categories) ? $this->work_categories : [];

        if (empty($ids)) {
            return [];
        }

        return InfrastructureType::whereIn('id', $ids)->pluck('slug')->all();
    }

    public function categoryNames()
    {
        $ids = is_array($this->work_categories) ? $this->work_categories : [];

        return InfrastructureType::whereIn('id', $ids)->orderBy('name')->get();
    }
}
