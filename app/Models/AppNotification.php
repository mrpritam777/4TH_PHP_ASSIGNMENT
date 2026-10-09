<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'read',
        'link',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('read', false);
    }

    public function markAsRead(): void
    {
        $this->update(['read' => true]);
    }

    /**
     * Create a single in-app notification. The link is stored so that email or
     * push channels can be plugged in later without changing call sites.
     */
    public static function send(?int $userId, string $title, ?string $message = null, string $type = 'info', ?string $link = null): static
    {
        return static::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'read' => false,
        ]);
    }

    /**
     * @param  array<int, int>  $userIds
     */
    public static function sendToUsers(array $userIds, string $title, ?string $message = null, string $type = 'info', ?string $link = null): void
    {
        foreach (array_unique(array_filter($userIds)) as $userId) {
            static::send((int) $userId, $title, $message, $type, $link);
        }
    }

    /**
     * @return array<int, int>
     */
    public static function adminIds(): array
    {
        return User::where('role', 'admin')->pluck('id')->all();
    }
}
