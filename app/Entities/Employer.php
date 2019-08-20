<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Employer extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'address'];

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param string $val
     */
    public function setSlugAttribute(string $val)
    {
        $this->attributes['slug'] = str_slug($val);
    }

    /**
     * @return string
     */
    public function getSlugAttribute(): string
    {
        $key = 'slug';

        if (empty($this->attributes[$key])) {
            $this->attributes[$key] = str_slug($this->attributes['name']);
        }

        return $this->attributes[$key];
    }

    /**
     * @return MorphOne
     */
    public function accountOwner(): MorphOne
    {
        return $this->morphOne(User::class, 'institutable')->where('is_account_owner', 1);
    }

    /**
     * @return MorphMany
     */
    public function approvalLevels(): MorphMany
    {
        return $this->morphMany(ApprovalLevel::class, 'institutable');
    }

    /**
     * @return MorphMany
     */
    public function roles(): MorphMany
    {
        return $this->morphMany(Role::class, 'institutable');
    }

    /**
     * @return MorphMany
     */
    public function staffMembers(): MorphMany
    {
        return $this->morphMany(User::class, 'institutable');
    }

    /**
     * @return HasMany
     */
    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class, 'employer_id');
    }
}
