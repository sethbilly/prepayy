<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;


class FinancialInstitution extends Model
{
    /**
     * Pivot table for partner employers
     */
    const TABLE_PARTNER_EMPLOYERS = 'employer_financial_institution';
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'abbr',
        'code',
        'contact_number',
        'email',
        'address'
    ];

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
     * @return MorphOne
     */
    public function dashboardBranding(): MorphOne
    {
        return $this->morphOne(BrandStyle::class, 'institutable');
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
     * @return BelongsToMany
     */
    public function partnerEmployers(): BelongsToMany
    {
        return $this->belongsToMany(
            Employer::class, self::TABLE_PARTNER_EMPLOYERS, 'financial_institution_id', 'employer_id'
        )->withTimestamps();
    }

    /**
     * @return HasManyThrough
     */
    public function loanApplications(): HasManyThrough
    {
        return $this->hasManyThrough(
            LoanApplication::class, LoanProduct::class, 'financial_institution_id', 'loan_product_id'
        );
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
    public function loanProducts(): HasMany
    {
        return $this->hasMany(LoanProduct::class, 'financial_institution_id');
    }
}
