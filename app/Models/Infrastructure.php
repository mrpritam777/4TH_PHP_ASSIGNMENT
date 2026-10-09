<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Infrastructure extends Model
{
    protected $fillable = [
        'type_id',
        'name',
        'code',
        'location',
        'address',
        'latitude',
        'longitude',
        'construction_year',
        'condition',
        'description',
        'image',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(InfrastructureType::class, 'type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bridgeDetails(): HasOne
    {
        return $this->hasOne(BridgeDetails::class);
    }

    public function roadDetails(): HasOne
    {
        return $this->hasOne(RoadDetails::class);
    }

    public function buildingDetails(): HasOne
    {
        return $this->hasOne(BuildingDetails::class);
    }

    public function hospitalDetails(): HasOne
    {
        return $this->hasOne(HospitalDetails::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function details()
    {
        return match ($this->type?->slug) {
            'bridge' => $this->bridgeDetails,
            'road' => $this->roadDetails,
            'building' => $this->buildingDetails,
            'hospital' => $this->hospitalDetails,
            default => null,
        };
    }

    public function lastMaintenance(): HasOne
    {
        return $this->hasOne(MaintenanceRequest::class)
            ->where('status', 'completed')
            ->latestOfMany('completion_date');
    }

    public function scopeOfType(Builder $query, string $slug): Builder
    {
        return $query->whereHas('type', fn ($q) => $q->where('slug', $slug));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function scopeCondition(Builder $query, ?string $condition): Builder
    {
        return $condition ? $query->where('condition', $condition) : $query;
    }

    public function getConditionBadgeClass(): string
    {
        return match ($this->condition) {
            'excellent' => 'badge-green',
            'good' => 'badge-green',
            'fair' => 'badge-yellow',
            'poor' => 'badge-orange',
            'critical' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public function imageUrl(): string
    {
        if ($this->image && (str_starts_with($this->image, 'http') || str_starts_with($this->image, '/'))) {
            return $this->image;
        }

        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return match ($this->type?->slug) {
            'bridge' => asset('images/infra/bridge-default.svg'),
            'road' => asset('images/infra/road-default.svg'),
            'building' => asset('images/infra/building-default.svg'),
            'hospital' => asset('images/infra/hospital-default.svg'),
            default => asset('images/infra/bridge-default.svg'),
        };
    }

    public function getDetailsUrl(): string
    {
        return match ($this->type?->slug) {
            'bridge' => route('admin.bridges.show', $this),
            'road' => route('admin.roads.show', $this),
            'building' => route('admin.buildings.show', $this),
            'hospital' => route('admin.hospitals.show', $this),
            default => route('admin.infrastructures.index'),
        };
    }
}
