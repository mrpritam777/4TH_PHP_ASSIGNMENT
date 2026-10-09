<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalDetails extends Model
{
    protected $fillable = [
        'infrastructure_id',
        'hospital_type',
        'beds',
        'emergency_phone',
        'emergency_facility',
        'departments',
        'email',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'emergency_facility' => 'boolean',
        ];
    }

    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }
}
