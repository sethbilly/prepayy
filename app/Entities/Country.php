<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'short_name', 'dial_code'];
    
    
}
