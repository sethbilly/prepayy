<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guarantor extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'contact_number', 'relationship', 'years_known', 'employer', 'position', 'user_id'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
