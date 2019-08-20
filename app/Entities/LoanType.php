<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function loansCountRelation()
    {
        return $this->hasOne(LoanProduct::class, 'loan_type_id')
            ->selectRaw('loan_type_id, count(*) as loans_count')
            ->groupBy('loan_type_id');
    }

    public function getLoansCountAttribute()
    {
        $relation = 'loansCountRelation';
        if (!$this->relationLoaded($relation)) {
            $this->load($relation);
        }

        return $this->loansCountRelation->loans_count ?? 0;
    }
}
