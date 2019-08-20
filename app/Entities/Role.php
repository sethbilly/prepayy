<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laratrust\LaratrustRole;

class Role extends LaratrustRole
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'display_name', 'description', 'institutable_id', 'institutable_type'];

    /**
     * App owner role
     * NB: An app owner is also an account owner
     */
    const ROLE_APP_OWNER = 'app-owner';
    /**
     * Account owner role
     */
    const ROLE_ACCOUNT_OWNER = 'account-owner';

    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * @return MorphTo
     */
    public function institutable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param string $val
     */
    public function setNameAttribute(string $val)
    {
        $this->attributes['name'] = str_slug($val);
    }

    public function canApproveLoans()
    {
        return $this->hasPermission('approve-loan-application');
    }
}
