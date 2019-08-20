<?php

use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\GetRolesForInstitutionJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class GetRolesForInstitutionJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    public function setUp()
    {
        parent::setUp();
    }

    public function test_can_get_roles_for_partners()
    {
        $user = $this->createInstitutionAccountOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(0, $roles);

        // Add 3 roles for the institution
        $addedRoles = factory(Role::class, 3)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(3, $roles);

        $addedRoles->each(function(Role $role, $i) use ($roles) {
            $this->assertEquals($role->name, $roles[$i]->name);
        });
    }

    public function test_can_get_roles_for_app_owners()
    {
        $user = $this->createApplicationOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(0, $roles);

        // Add 3 roles for the institution
        $addedRoles = factory(Role::class, 3)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(3, $roles);

        $addedRoles->each(function(Role $role, $i) use ($roles) {
            $this->assertEquals($role->name, $roles[$i]->name);
        });
    }

    public function test_can_get_roles_for_employers()
    {
        $user = $this->createEmployerAccountOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(0, $roles);

        // Add 3 roles for the institution
        $addedRoles = factory(Role::class, 3)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $roles = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(3, $roles);

        $addedRoles->each(function(Role $role, $i) use ($roles) {
            $this->assertEquals($role->name, $roles[$i]->name);
        });
    }

    public function test_can_search_roles_by_name()
    {
        $user = $this->createApplicationOwner();
        $roles = factory(Role::class, 3)->create();
        $req = $this->getAuthenticatedRequest(['search' => $roles->first()->display_name], $user);

        $results = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(1, $results);
        $this->assertEquals($roles->first()->id, $results->items()[0]->id);
    }

    public function test_can_search_roles_by_permission_name()
    {
        $user = $this->createApplicationOwner();
        $perms = Permission::take(3)->get();
        $roles = factory(Role::class, 3)
            ->create()
            ->each(function(Role $role, $i) use ($perms) {
                if ($i < 2) {
                    $role->attachPermissions([$perms[0], $perms[1]]);
                } else {
                    $role->attachPermission($perms[2]);
                }
            });
        $req = $this->getAuthenticatedRequest(['search' => $perms[0]->display_name], $user);

        $results = dispatch(new GetRolesForInstitutionJob($req));

        $this->assertCount(2, $results);
        $this->assertEquals($roles[0]->id, $results->items()[0]->id);
        $this->assertEquals($roles[1]->id, $results->items()[1]->id);
    }
}
