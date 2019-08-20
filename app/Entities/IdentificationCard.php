<?php

namespace App\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentificationCard extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['type', 'number', 'issue_date', 'expiry_date', 'user_id'];

    /**
     * @var array
     */
    protected $dates = [
        'issue_date',
        'expiry_date'
    ];

    /**
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @param string $val
     */
    public function setIssueDateAttribute(string $val)
    {
        $date = Carbon::parse($val);

        $this->attributes['issue_date'] = $date ?? null;
    }

    /**
     * @param string $val
     */
    public function setExpiryDateAttribute(string $val)
    {
        $date = Carbon::parse($val);

        $this->attributes['expiry_date'] = $date ?? null;
    }

    public static function getIdentificationTypes(): array
    {
        return [
            'Voters ID',
            'Passport',
            'Drivers License',
            'National ID',
            'Social Security'
        ];
    }

    public function getIdNameForXDS(): string
    {
        switch ($this->type) {
            case 'Voters ID':
                return 'Voters ID';
            case 'Passport':
                return 'Passport No';
            case 'Drivers License':
                return 'Drivers License No';
            case 'National ID':
                return 'National ID';
            case 'Social Security':
                return 'Social Security No';
            default:
                return '';
        }
    }
}
