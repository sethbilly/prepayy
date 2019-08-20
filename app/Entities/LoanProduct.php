<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LoanProduct extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description', 'min_amount', 'max_amount', 'interest_per_year',
        'financial_institution_id', 'loan_type_id'
    ];

    /**
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(FinancialInstitution::class, 'financial_institution_id');
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

        if (!array_key_exists($key, $this->attributes)) {
            $this->setSlugAttribute($this->attributes['name']);
        }

        return $this->attributes[$key];
    }

    /**
     * @return MorphMany
     */
    public function images(): MorphMany
    {
        return $this->morphMany(FileEntry::class, 'fileable');
    }

    public function loanType()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    /**
     * @param FinancialInstitution $institution
     * @param string $slug
     * @param bool $failIfNotFound
     * @return LoanProduct|null
     */
    public static function findByInstitutionAndSlug(
        FinancialInstitution $institution,
        string $slug,
        $failIfNotFound = true
    )
    {
        $stmt = self::where(['financial_institution_id' => $institution->id, 'slug' => $slug]);

        return $failIfNotFound ? $stmt->firstOrFail() : $stmt->first();
    }

    public static function getMinLoanAmount()
    {
        return self::min('min_amount');
    }

    public static function getMaxLoanAmount()
    {
        return self::max('max_amount');
    }
}