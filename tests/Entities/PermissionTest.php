<?php

use App\Entities\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class PermissionTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
    }

    private function get_permission_helper(array $permData, string $perm_type): Collection
    {
        $perms = collect($permData['permissions'])
            ->filter(function(array $perm) use ($perm_type) {
                return array_key_exists($perm_type, $perm) && $perm[$perm_type];
            });

        return collect(['group_name' => $permData['group_name'], 'permissions' => $perms]);
    }

    private function get_user_management_permissions(string $perm_type): Collection
    {
        return $this->get_permission_helper(PermissionSeeder::getUserManagementPermissions(), $perm_type);
    }

    private function get_role_management_permissions(string $perm_type): Collection
    {
        return $this->get_permission_helper(PermissionSeeder::getRoleManagementPermissions(), $perm_type);
    }

    private function get_approval_level_management_permissions(string $perm_type): Collection
    {
        return $this->get_permission_helper(PermissionSeeder::getApprovalLevelManagementPermission(), $perm_type);
    }

    private function get_employer_management_permissions(string $perm_type): Collection
    {
        return $this->get_permission_helper(PermissionSeeder::getEmployerManagementPermissions(), $perm_type);
    }

    private function get_loan_management_permissions(string $perm_type): Collection
    {
        return $this->get_permission_helper(PermissionSeeder::getLoanManagementPermissions(), $perm_type);
    }

    /**
     *
     * @param Collection $expectedPerms
     * @param Collection $actualPerms
     */
    private function assert_permissions_were_retrieved(Collection $expectedPerms, Collection $actualPerms)
    {
        $expectedPerms->get('permissions')
            ->each(function(array $row) use ($actualPerms, $expectedPerms) {
                $perm = $actualPerms->get($expectedPerms['group_name'])
                    ->first(function(Permission $obj) use ($row) {
                        return $obj->name === $row['name'];
                    });

                $this->assertInstanceOf(Permission::class, $perm);
            });
    }

    public function test_can_get_grouped_permissions_for_employers()
    {
        // An employer can manage users, roles and approval levels
        $key = 'is_employer_permission';
        $userPerms = $this->get_user_management_permissions($key);
        $rolePerms = $this->get_role_management_permissions($key);
        $approvalPerms = $this->get_approval_level_management_permissions($key);
        $loanPerms = $this->get_loan_management_permissions($key);
        $empPerms = $this->get_employer_management_permissions($key);

        $this->assertTrue($userPerms->get('permissions')->count() > 0);
        $this->assertTrue($rolePerms->get('permissions')->count() > 0);
        $this->assertTrue($approvalPerms->get('permissions')->count() > 0);
        $this->assertFalse($loanPerms->get('permissions')->count() > 0);
        $this->assertFalse($empPerms->get('permissions')->count() > 0);

        // Get grouped permissions
        $groupedPerms = Permission::getGroupedEmployerPermissions();

        $this->assertCount($userPerms->get('permissions')->count(), $groupedPerms->get($userPerms['group_name']));
        $this->assertCount($rolePerms->get('permissions')->count(), $groupedPerms->get($rolePerms['group_name']));
        $this->assertCount($approvalPerms->get('permissions')->count(), $groupedPerms->get($approvalPerms['group_name']));
        $this->assertFalse($groupedPerms->has($loanPerms['group_name']));
        $this->assertFalse($groupedPerms->has($empPerms['group_name']));

        // Assert that all user permissions were retrieved
        $this->assert_permissions_were_retrieved($userPerms, $groupedPerms);
        // Assert that all role permissions were retrieved
        $this->assert_permissions_were_retrieved($rolePerms, $groupedPerms);
        // Assert that all approval permissions were retrieved
        $this->assert_permissions_were_retrieved($approvalPerms, $groupedPerms);
    }

    public function test_can_get_grouped_permissions_for_callens()
    {
        // Callens has user, role and employer management permissions
        $key = 'is_app_owner_permission';
        $userPerms = $this->get_user_management_permissions($key);
        $rolePerms = $this->get_role_management_permissions($key);
        $approvalPerms = $this->get_approval_level_management_permissions($key);
        $loanPerms = $this->get_loan_management_permissions($key);
        $empPerms = $this->get_employer_management_permissions($key);

        $this->assertTrue($userPerms->get('permissions')->count() > 0);
        $this->assertTrue($rolePerms->get('permissions')->count() > 0);
        $this->assertTrue($empPerms->get('permissions')->count() > 0);
        $this->assertFalse($approvalPerms->get('permissions')->count() > 0);
        $this->assertFalse($loanPerms->get('permissions')->count() > 0);

        // Get grouped permissions
        $groupedPerms = Permission::getGroupedAppOwnerPermissions();

        $this->assertCount($userPerms->get('permissions')->count(), $groupedPerms->get($userPerms['group_name']));
        $this->assertCount($rolePerms->get('permissions')->count(), $groupedPerms->get($rolePerms['group_name']));
        $this->assertCount($empPerms->get('permissions')->count(), $groupedPerms->get($empPerms['group_name']));
        $this->assertFalse($groupedPerms->has($loanPerms['group_name']));
        $this->assertFalse($groupedPerms->has($approvalPerms['group_name']));

        // Assert that all user permissions were retrieved
        $this->assert_permissions_were_retrieved($userPerms, $groupedPerms);
        // Assert that all role permissions were retrieved
        $this->assert_permissions_were_retrieved($rolePerms, $groupedPerms);
        // Assert that all employer permissions were retrieved
        $this->assert_permissions_were_retrieved($empPerms, $groupedPerms);
    }

    public function test_can_get_grouped_permissions_for_institutions()
    {
        // Institutions have user, role, loan, approval level and employer management permissions
        $key = 'is_partner_permission';
        $userPerms = $this->get_user_management_permissions($key);
        $rolePerms = $this->get_role_management_permissions($key);
        $approvalPerms = $this->get_approval_level_management_permissions($key);
        $loanPerms = $this->get_loan_management_permissions($key);
        $empPerms = $this->get_employer_management_permissions($key);

        $this->assertTrue($userPerms->get('permissions')->count() > 0);
        $this->assertTrue($rolePerms->get('permissions')->count() > 0);
        $this->assertTrue($empPerms->get('permissions')->count() > 0);
        $this->assertTrue($approvalPerms->get('permissions')->count() > 0);
        $this->assertTrue($loanPerms->get('permissions')->count() > 0);

        // Get grouped permissions
        $groupedPerms = Permission::getGroupedPartnerPermissions();

        $this->assertCount($userPerms->get('permissions')->count(), $groupedPerms->get($userPerms['group_name']));
        $this->assertCount($rolePerms->get('permissions')->count(), $groupedPerms->get($rolePerms['group_name']));
        $this->assertCount($approvalPerms->get('permissions')->count(), $groupedPerms->get($approvalPerms['group_name']));
        $this->assertCount($loanPerms->get('permissions')->count(), $groupedPerms->get($loanPerms['group_name']));
        $this->assertCount($empPerms->get('permissions')->count(), $groupedPerms->get($empPerms['group_name']));

        // Assert that all user permissions were retrieved
        $this->assert_permissions_were_retrieved($userPerms, $groupedPerms);
        // Assert that all role permissions were retrieved
        $this->assert_permissions_were_retrieved($rolePerms, $groupedPerms);
        // Assert that all approval permissions were retrieved
        $this->assert_permissions_were_retrieved($approvalPerms, $groupedPerms);
        // Assert that all employer permissions were retrieved
        $this->assert_permissions_were_retrieved($empPerms, $groupedPerms);
        // Assert that all loan permissions were retrieved
        $this->assert_permissions_were_retrieved($loanPerms, $groupedPerms);
    }
}
