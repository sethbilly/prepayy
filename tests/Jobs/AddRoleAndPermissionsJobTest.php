<?php

use App\Entities\Permission;
use App\Entities\Role;
use App\Jobs\AddRoleAndPermissionsJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class AddRoleAndPermissionsJobTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    private function getRequest(): Request
    {
        $perms = Permission::limit(3)->get()
            ->map(function(Permission $rec) {
                return $rec->name;
            });

        $name = $this->faker->name;
        
        return $this->getAuthenticatedRequest([
            'role' => [
                'name' => str_slug($name),
                'display_name' => $name,
                'description' => $this->faker->sentence
            ],
            'permissions' => $perms
        ]);
    }

    public function test_can_add_role()
    {
        $req = $this->getRequest();

        $role = dispatch(new AddRoleAndPermissionsJob($req));

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($req->input('role.name'), $role->name);
        $this->assertEquals($req->input('role.display_name'), $role->display_name);
        $this->assertEquals($req->input('role.description'), $role->description);
        $this->assertCount(count($req->get('permissions')), $role->permissions);

        collect($req->get('permissions'))
            ->each(function($perm, $i) use ($role) {
                $this->assertEquals($perm, $role->permissions[$i]->name);
            });
    }

    public function test_can_update_role()
    {
        $oldRole = factory(Role::class)->create();
        $req = $this->getRequest();

        $role = dispatch(new AddRoleAndPermissionsJob($req, $oldRole));

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($oldRole->id, $role->id);
        $this->assertEquals($req->input('role.name'), $role->name);
        $this->assertEquals($req->input('role.display_name'), $role->display_name);
        $this->assertEquals($req->input('role.description'), $role->description);

        collect($req->get('permissions'))
            ->each(function($perm, $i) use ($role) {
                $this->assertEquals($perm, $role->permissions[$i]->name);
            });
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_add_duplicate_role()
    {
        $roleOne = factory(Role::class)->create();
        $req = $this->getRequest();
        $req->merge(['role' => ['name' => $roleOne->name]]);

        dispatch(new AddRoleAndPermissionsJob($req));
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_update_to_existing_role()
    {
        $roles = factory(Role::class, 2)->create();

        $req = $this->getRequest();
        $req->merge(['role' => ['name' => $roles->first()->name]]);

        dispatch(new AddRoleAndPermissionsJob($req, $roles->last()));
    }

    public function test_does_not_add_duplicate_role_permissions()
    {
        $role = factory(Role::class)->create();
        $perms = Permission::take(5)->get();

        $perms->each(function(Permission $perm) use ($role) {
            $role->attachPermission($perm);
        });

        $req = $this->getRequest();
        $req->merge([
            'role' => ['name' => $role->name],
            'permissions' => $perms->pluck('name')->all()
        ]);

        $addedRole = dispatch(new AddRoleAndPermissionsJob($req, $role));

        $this->assertCount(5, $addedRole->permissions);

        $perms->each(function(Permission $permission) use ($addedRole) {
            $this->assertTrue($addedRole->hasPermission($permission->name));
        });
    }
}
