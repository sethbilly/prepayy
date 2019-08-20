<?php

use App\Entities\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data[] = self::getApprovalLevelManagementPermission();
        $data[] = self::getRoleManagementPermissions();
        $data[] = self::getUserManagementPermissions();
        $data[] = self::getEmployerManagementPermissions();
        $data[] = self::getLoanManagementPermissions();

        $permissions = collect($data)
            ->filter(function (array $row) {
                return array_key_exists('permissions', $row);
            })
            ->map(function (array $row) {
                // Iterate the permissions and add them
                return collect($row['permissions'])
                    ->map(function (array $permRow) use ($row) {
                        $permRow['group_name'] = array_key_exists('group_name', $row) ?
                            $row['group_name'] : null;

                        return $this->addPermission($permRow);
                    });
            })
            // The previous step returns a multidimensional collection
            ->flatten();

        Permission::whereNotIn('id', $permissions->pluck('id')->all())->delete();
    }

    /**
     * @param $row
     * @return Permission
     */
    private function addPermission($row): Permission
    {
        $perm = Permission::firstOrNew(['name' => $row['name']]);

        foreach ($perm->getFillable() as $fillable) {
            if (array_key_exists($fillable, $row)) {
                $perm[$fillable] = $row[$fillable];
            }
        }

        $perm->save();

        return $perm;
    }

    /**
     * @return array
     */
    public static function getApprovalLevelManagementPermission(): array
    {
        return [
            'group_name' => 'Approval Level Management',
            'permissions' => [
                [
                    'name' => 'add-approval-level',
                    'display_name' => 'Add Approval Level',
                    'description' => 'User can add/create approval levels',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => false
                ],
                [
                    'name' => 'edit-approval-level',
                    'display_name' => 'Edit Approval Level',
                    'description' => 'User can edit approval levels',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => false
                ],
                [
                    'name' => 'delete-approval-level',
                    'display_name' => 'Delete Approval Level',
                    'description' => 'User can delete approval levels',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => false
                ]
            ]
        ];
    }

    /**
     * Get the role permissions
     * @return array
     */
    public static function getRoleManagementPermissions()
    {
        return [
            'group_name' => 'Roles Management',
            'permissions' => [
                [
                    'name' => 'add-role',
                    'display_name' => 'Add New Role',
                    'description' => 'User can add/create a new role with it\'s set of permissions',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'edit-role',
                    'display_name' => 'Edit Existing Role',
                    'description' => 'User can edit the name and/or permissions of an existing role',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'delete-role',
                    'display_name' => 'Delete Role',
                    'description' => 'User can edit the name and/or permissions of an existing role',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ]
            ]
        ];
    }

    /**
     * Get user permissions
     * @return array
     */
    public static function getUserManagementPermissions(): array
    {
        return [
            'group_name' => 'User Management',
            'permissions' => [
                [
                    'name' => 'add-user',
                    'display_name' => 'Add New User',
                    'description' => 'User can add/create user accounts and assign roles/permissions',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'edit-user',
                    'display_name' => 'Edit Existing User',
                    'description' => 'User can edit user accounts along with their roles/permissions',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'delete-user',
                    'display_name' => 'Delete User',
                    'description' => 'User can delete user accounts',
                    'is_partner_permission' => true,
                    'is_employer_permission' => true,
                    'is_app_owner_permission' => true
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getEmployerManagementPermissions(): array
    {
        return [
            'group_name' => 'Employer Management',
            'permissions' => [
                [
                    'name' => 'add-employer',
                    'display_name' => 'Add Employer',
                    'description' => 'User can add/create new employer accounts',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'edit-employer',
                    'display_name' => 'Edit Employer',
                    'description' => 'User can edit employer accounts',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => true
                ],
                [
                    'name' => 'delete-employer',
                    'display_name' => 'Delete User',
                    'description' => 'User can delete employer accounts',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => true
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getLoanManagementPermissions(): array
    {
        return [
            'group_name' => 'Loan Product Management',
            'permissions' => [
                [
                    'name' => 'add-loan-product',
                    'display_name' => 'Add Loan Product',
                    'description' => 'User can add/create new loan products',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => false
                ],
                [
                    'name' => 'edit-loan-product',
                    'display_name' => 'Edit Loan Product',
                    'description' => 'User can edit loan products',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => false
                ],
                [
                    'name' => 'delete-loan-product',
                    'display_name' => 'Delete User',
                    'description' => 'User can delete loan products',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => false
                ],
                [
                    'name' => 'approve-loan-application',
                    'display_name' => 'Process Loan Application',
                    'description' => 'User can approve/decline/process loan applications',
                    'is_partner_permission' => true,
                    'is_employer_permission' => false,
                    'is_app_owner_permission' => false
                ]
            ]
        ];
    }
}
