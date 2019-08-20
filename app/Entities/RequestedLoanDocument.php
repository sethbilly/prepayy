<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RequestedLoanDocument extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['loan_application_id', 'request', 'response', 'user_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(FileEntry::class, 'fileable');
    }

    /**
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
