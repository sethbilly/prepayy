<?php

use App\Entities\FinancialInstitution;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\CreateRoleJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateRoleJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createInstitutionAccountOwner();
    }

    public function getRequest(): Request
    {
        $faker = Faker\Factory::create();
        $perms = Permission::where('group_name', 'user management')->pluck('name')->all();

        return $this->getAuthenticatedRequest([
            'role' => [
                'display_name' => $faker->name,
                'description' => $faker->sentence
            ],
            'permissions' => $perms
        ], $this->user);
    }

    public function test_can_add_role()
    {
        $req = $this->getRequest();

        $role = dispatch(new CreateRoleJob($req));

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($req->input('role.display_name'), $role->display_name);
        $this->assertEquals($req->input('role.description'), $role->description);
        $this->assertEquals(str_slug($req->input('role.display_name')), $role->name);
        $this->assertCount(count($req->get('permissions')), $role->permissions);
        $this->assertInstanceOf(FinancialInstitution::class, $role->institutable);

        foreach ($req->get('permissions') as $i => $perm) {
            $this->assertEquals($perm, $role->permissions[$i]->name);
        }
    }

    public function test_can_update_role()
    {
        $oldRole = factory(Role::class)->create();
        $req = $this->getRequest();

        $role = dispatch(new CreateRoleJob($req, $oldRole));

        $this->assertEquals($oldRole->id, $role->id);
        $this->assertEquals($oldRole->name, $role->name);
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($req->input('role.display_name'), $role->display_name);
        $this->assertEquals($req->input('role.description'), $role->description);
        $this->assertEquals(str_slug($req->input('role.display_name')), $role->name);
        $this->assertCount(count($req->get('permissions')), $role->permissions);
        $this->assertInstanceOf(FinancialInstitution::class, $role->institutable);

        foreach ($req->get('permissions') as $i => $perm) {
            $this->assertEquals($perm, $role->permissions[$i]->name);
        }
    }
}
