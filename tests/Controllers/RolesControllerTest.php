<?php

use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

class RolesControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_displays_no_roles_added(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);

        $this->actingAs($user)
            ->visitRoute("roles.index")
            ->seeLink('Add New Role', route("roles.create"))
            ->seeText('Add roles and assign them to your users')
            ->seeText('Your roles list')
            ->click('Add New Role')
            ->seeRouteIs("roles.create");
    }

    public function get_roles_provider()
    {
        return [
            ['partner'],
            ['callens'],
             ['employer']
        ];
    }

    private function create_user_helper(string $routePrefix): User
    {
        switch ($routePrefix) {
            case 'partner':
                $user = $this->createInstitutionAccountOwner();
                break;
            case 'callens':
                $user = $this->createApplicationOwner();
                break;
            default:
                $user = $this->createEmployerAccountOwner();
        }

        return $user;
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_can_get_roles_index(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);
        $permissions = Permission::all();

        // Create 3 roles for the institution
        $roles = factory(Role::class, 3)
            ->create([
                'institutable_id' => $user->institutable_id,
                'institutable_type' => $user->institutable_type,
            ])
            ->each(function(Role $role) use ($permissions) {
                 // Add a permission for each role
                $role->attachPermission($permissions->random());
            });

        $this->actingAs($user)
            ->visitRoute("roles.index")
            ->seeLink($roles[0]->display_name, route("roles.edit", ['role' => $roles[0]]))
            ->seeText($roles[0]->display_name)
            ->seeText($roles[1]->display_name)
            ->seeText($roles[2]->display_name)
            // Validate the displayed descriptions
            ->seeText($roles[0]->description)
            ->seeText($roles[1]->description)
            ->seeText($roles[2]->description)
            // Validate the displayed permissions
            ->seeText($roles[0]->permissions()->pluck('display_name')->implode(', '))
            ->seeText($roles[1]->permissions()->pluck('display_name')->implode(', '))
            ->seeText($roles[2]->permissions()->pluck('display_name')->implode(', '));
    }

    /**
     * @param string $routePrefix
     * @return Collection
     */
    private function get_permissions_helper(string $routePrefix): Collection
    {
        switch ($routePrefix) {
            case 'partner':
                $permissions = Permission::getGroupedPartnerPermissions();
                break;
            case 'callens':
                $permissions = Permission::getGroupedAppOwnerPermissions();
                break;
            default:
                $permissions = Permission::getGroupedEmployerPermissions();
        }

        return $permissions;
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_can_add_role(string $routePrefix)
    {
        $faker = Faker\Factory::create();
        $user = $this->create_user_helper($routePrefix);
        // Get 2 permissions to assign
        $groupedPerms = $this->get_permissions_helper($routePrefix);
        $perms = $groupedPerms->first()->pluck('name');

        $name = $faker->name;
        $this->actingAs($user)
            ->visitRoute("roles.create")
            ->seeText('Roles Management')
            ->seeText('User Management')
            ->submitForm('Save changes', [
                'role' => [
                    'display_name' => $name,
                    'description' => $faker->sentence
                ],
                'permissions' => $perms->all()
            ])
            ->seeRouteIs("roles.index")
            ->seeInDatabase('roles', [
                'name' => str_slug($name),
                'institutable_id' => $user->institutable_id,
                'institutable_type' => $user->institutable_type
            ]);
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_populates_data_to_update(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);
        $role = factory(Role::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $permissions = Permission::whereIn('name', ['add-user', 'add-role'])->get();
        $role->attachPermission($permissions->first());
        $role->attachPermission($permissions->last());

        $this->actingAs($user)
            ->visitRoute("roles.edit", ['role' => $role])
            ->seeInField('role[display_name]', $role->display_name)
            ->seeInField('role[description]', $role->description)
            ->seeIsChecked('add-user')
            ->seeIsChecked('add-role');
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_can_update_role(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);
        $role = factory(Role::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $permissions = Permission::whereIn('name', ['add-user', 'add-role'])->get();
        $role->attachPermission($permissions->first());
        $role->attachPermission($permissions->last());

        $this->actingAs($user)
            ->visitRoute("roles.edit", ['role' => $role])
            ->press('Save changes')
            ->seeRouteIs("roles.index");
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_will_display_delete_link(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);
        $role = factory(Role::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $permissions = Permission::whereIn('name', ['add-user', 'add-role'])->get();
        $role->attachPermission($permissions->first());
        $role->attachPermission($permissions->last());

        $this->actingAs($user)
            ->visitRoute('roles.index', ['role' => $role])
            ->seeText('Delete');
    }

    /**
     * @dataProvider get_roles_provider
     * @param string $routePrefix
     */
    public function test_will_delete_role(string $routePrefix)
    {
        $user = $this->create_user_helper($routePrefix);
        $role = factory(Role::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $permissions = Permission::whereIn('name', ['add-user', 'add-role'])->get();
        $role->attachPermission($permissions->first());
        $role->attachPermission($permissions->last());

        $this->actingAs($user)
            ->delete(route('roles.delete', ['role' => $role]));

        $deletedRole = Role::where('id', $role->id)->first();

        $this->assertNull($deletedRole);
    }
}
