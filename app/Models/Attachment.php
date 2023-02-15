<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Castables\Link;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $original_name
 * @property string $name
 * @property \App\Castables\Link $link
 * @property int $post_id
 * @property \App\Models\Post $post
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Attachment extends Model
{
    use HasFactory, Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'original_name',
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'link' => Link::class,
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::whereNull('post_id');
    }

    /**
     * Prepare the model for pruning.
     */
    public function pruning(): void
    {
        Storage::disk('public')->delete($this->name);
    }

    /**
     * 글
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
