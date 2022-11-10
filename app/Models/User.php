<?php

namespace App\Models;

use App\Models\Scopes\VerifiedScope;
use Illuminate\Auth\Passwords\CanResetPassword as ResettablePassword;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property string $provider_uid
 * @property \Illuminate\Support\Carbon $email_verified_at
 * @property int $provider_id
 * @property Provider $provider
 * @property \Illuminate\Database\Eloquent\Collection $blogs
 * @property \Illuminate\Database\Eloquent\Collection $subscriptions
 * @property \Illuminate\Database\Eloquent\Collection $comments
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail, CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, ResettablePassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider_id',
        'provider_uid',
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

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        // static::addGlobalScope(new VerifiedScope());
    }

    /**
     * 이메일이 인증된 사용자만
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeVerified(Builder $query, ...$params)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * 서비스 제공자
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * 블로그
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * 내가 구독한 블로그
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subscriptions()
    {
        return $this->belongsToMany(Blog::class)
            ->as('subscription');
    }

    /**
     * 댓글
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 피드
     *
     * @param  int  $perBlog
     * @return Collection
     */
    public function feed(int $perBlog = 5)
    {
        return $this->subscriptions
            ->reduce(function (Collection $feed, Blog $subscription) use ($perBlog) {
                $posts = $subscription->posts()
                    ->latest()
                    ->limit($perBlog)
                    ->get();

                return $feed->merge($posts);
            }, collect())
            ->sort(function ($a, $b) {
                return $a['created_at']->lessThan($b['created_at']);
            });
    }
}
