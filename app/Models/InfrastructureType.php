<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class InfrastructureType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class, 'type_id');
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(Tender::class, 'infrastructure_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * A category may only be deleted when no infrastructure asset or tender
     * still references it.
     */
    public function deletable(): bool
    {
        return ! $this->infrastructures()->exists() && ! $this->tenders()->exists();
    }

    public function blockingReferences(): int
    {
        return $this->infrastructures()->count() + $this->tenders()->count();
    }

    /**
     * URL used by the sidebar submenu for this category. The four default
     * categories keep their dedicated pages; any category created through
     * "Add More" is shown in the filtered All Infrastructure list.
     */
    public function adminUrl(): string
    {
        $dedicated = [
            'bridge' => 'admin.bridges.index',
            'road' => 'admin.roads.index',
            'building' => 'admin.buildings.index',
            'hospital' => 'admin.hospitals.index',
        ];

        if (isset($dedicated[$this->slug])) {
            return route($dedicated[$this->slug]);
        }

        return route('admin.infrastructures.index', ['type' => $this->slug]);
    }

    public function menuLabel(): string
    {
        return Str::plural($this->name);
    }
}
