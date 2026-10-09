<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderBid extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_AWARDED = 'awarded';

    protected $fillable = [
        'tender_id',
        'contractor_id',
        'bid_amount',
        'proposal',
        'documents',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'bid_amount' => 'float',
            'documents' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }
}
