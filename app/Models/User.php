<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isContractor(): bool
    {
        return $this->role === 'contractor';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function contractor(): HasOne
    {
        return $this->hasOne(Contractor::class);
    }

    /**
     * Contractors may sign in to review their application status even while the
     * account is not yet activated; feature access is enforced by middleware.
     */
    public function canLogin(): bool
    {
        if ($this->isContractor()) {
            return true;
        }

        return $this->isActive();
    }

    public function reportedRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'reported_by');
    }

    public function assignedRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    public function activeTasks(): HasMany
    {
        return $this->assignedRequests()->whereIn('status', ['assigned', 'in_progress']);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(MaintenanceUpdate::class, 'updated_by');
    }

    public function infrastructures(): HasMany
    {
        return $this->hasMany(Infrastructure::class, 'created_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('read', false);
    }
}
