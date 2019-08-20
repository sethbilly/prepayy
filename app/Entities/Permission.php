<?php

namespace App\Entities;

use Illuminate\Support\Collection;
use Laratrust\LaratrustPermission;

class Permission extends LaratrustPermission
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'display_name', 'description', 'is_employer_permission', 'group_name',
        'is_app_owner_permission', 'is_partner_permission'
    ];

    /**
     * @param array $permTypes
     * @return Collection
     */
    public static function getGroupedPermissions(array $permTypes = []): Collection
    {
        $perms = Permission::when(!empty($permTypes), function($q) use ($permTypes) {
            return $q->where($permTypes);
        })->get();

        $groupedPermissions = collect([]);
        $perms->map(function(Permission $permission) use (&$groupedPermissions) {
            if (!$groupedPermissions->has($permission->group_name)) {
                $groupedPermissions->put($permission->group_name, collect([]));
            }
            $groupedPermissions->get($permission->group_name)->push($permission);
        });

        return $groupedPermissions;
    }

    public static function getGroupedPartnerPermissions(): Collection
    {
        return self::getGroupedPermissions(['is_partner_permission' => 1]);
    }

    public static function getGroupedAppOwnerPermissions(): Collection
    {
        return self::getGroupedPermissions(['is_app_owner_permission' => 1]);
    }

    public static function getGroupedEmployerPermissions(): Collection
    {
        return self::getGroupedPermissions(['is_employer_permission' => 1]);
    }
}
