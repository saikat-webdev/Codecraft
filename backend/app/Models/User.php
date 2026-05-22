<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'bio',
        'is_admin',
        'is_suspended',
        'learning_goal',
        'preferred_language',
        'daily_learning_time',
        'skill_level',
        'xp',
        'level',
        'streak_count',
        'longest_streak',
        'last_activity_date',
        'sudden_test_difficulty',
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
            'daily_learning_time' => 'integer',
            'is_admin' => 'boolean',
            'is_suspended' => 'boolean',
            'last_activity_date' => 'date',
        ];
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('earned_at');
    }

    public function suddenTestAttempts(): HasMany
    {
        return $this->hasMany(SuddenTestAttempt::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }
}
