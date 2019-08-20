<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalLevel extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'slug', 'institutable_id', 'institutable_type'];

    /**
     * @return MorphTo
     */
    public function institutable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'approval_level_id');
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setSlugAttribute(string $val)
    {
        $this->attributes['slug'] = str_slug($val);
    }
}
