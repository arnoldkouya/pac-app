<?php

namespace App\Models;

use Bow\Database\Barry\Model;
use App\Models\Traits\LatestTrait;
use App\Models\Traits\PremiumTrait;
use App\Models\Traits\CoverUrlTrait;
use App\Models\Traits\TaggableTrait;
use Bow\Database\Barry\Relations\HasMany;
use Bow\Database\Barry\Relations\BelongsTo;

class Podcast extends Model
{
    use CoverUrlTrait;
    use LatestTrait;
    use PremiumTrait;
    use TaggableTrait;

    /**
     * Podcast constructor
     *
     * @return mixed
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        Podcast::deleting(fn ($challenge) => $challenge->comments()->delete());
    }

    /**
     * Get all comment that are assigned to the challenge
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'commentable_id')
            ->where('commentable_type', Podcast::class);
    }

    /**
     * Get the author info
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Determine if source is video
     *
     * @return bool
     */
    public function isVideo()
    {
        $extension = pathinfo($this->source, PATHINFO_EXTENSION);

        return in_array($extension, ['mp4']);
    }

    /**
     * Mutate title to slug
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return str_slug($this->title);
    }

    /**
     * __mutator
     *
     * @return string
     */
    public function getDurationAttribute(string $value)
    {
        $value = (int) $value;

        return $value . ' ' . $this->duration_type;
    }
}
