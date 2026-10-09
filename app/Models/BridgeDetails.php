<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BridgeDetails extends Model
{
    protected $fillable = [
        'infrastructure_id',
        'bridge_type',
        'length',
        'width',
        'lanes',
        'material',
        'load_capacity',
        'structural_condition',
        'surface_condition',
        'safety_status',
        'last_inspection_date',
    ];

    protected function casts(): array
    {
        return [
            'last_inspection_date' => 'date',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
