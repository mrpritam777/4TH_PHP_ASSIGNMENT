<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadDetails extends Model
{
    protected $fillable = [
        'infrastructure_id',
        'road_type',
        'start_point',
        'end_point',
        'length',
        'width',
        'lanes',
    ];

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
