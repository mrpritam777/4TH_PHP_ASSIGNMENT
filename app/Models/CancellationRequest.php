<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CancellationRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tender_id',
        'requested_by',
        'requester_role',
        'reason',
        'status',
        'decided_by',
        'decided_at',
        'decision_note',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * A decision may be made by an admin who is not the original requester,
     * or by the contractor involved when the request was raised by an admin.
     */
    public function canBeDecidedBy(?User $user): bool
    {
        if (! $user || ! $this->isPending()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->id !== (int) $this->requested_by;
        }

        if ($user->isContractor() && (int) $this->requested_by !== $user->id) {
            if ($this->requester_role !== 'admin') {
                return false;
            }

            $contractor = $user->contractor;

            if (! $contractor) {
                return false;
            }

            // The awarded contractor or any contractor with a bid on the tender.
            if ((int) $this->tender->awarded_contractor_id === $contractor->id) {
                return true;
            }

            return $this->tender->bids()->where('contractor_id', $contractor->id)->exists();
        }

        return false;
    }
}
