<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingDetails extends Model
{
    protected $fillable = [
        'infrastructure_id',
        'building_type',
        'floors',
        'area',
        'department',
    ];

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
