<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_badge_id',
        'account_number',
        'bank_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function currentBadge(): BelongsTo
    {
        return $this->belongsTo(Badge::class)->withDefault();
    }

    public function nextBadge(): ?Badge
    {
        return Badge::where('required_achievement_count', '>', $this->currentBadge->required_achievement_count ?? 0)
            ->orderBy('required_achievement_count')
            ->first();
    }

    public function getUnlockedAchievements(): array
    {
        return $this->achievements->pluck('name')->toArray();
    }

    public function getNextAvailableAchievements(): array
    {
        return Achievement::query()->whereNotIn('name', $this->getUnlockedAchievements())
            ->orderBy('required_purchase_count')
            ->pluck('name')
            ->toArray();
    }
}
