<?php

use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
    }

    public function getUserProvider()
    {
        return [
            ['partner'],
            ['callens'],
            ['employer']
        ];
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_can_get_users_list(string $routePrefix)
    {
        $user = $this->createUserHelper($routePrefix);
        $users = factory(User::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
            'is_app_owner' => $user->isApplicationOwner() ? 1 : 0
        ]);

        $this->actingAs($user)
            ->visitRoute("users.index")
            ->seeLink($user->getFullName(), route("users.edit", ['user' => $user]))
            ->seeLink($users[0]->getFullName(), route("users.edit", ['user' => $users[0]]))
            ->seeLink($users[1]->getFullName(), route("users.edit", ['user' => $users[1]]));
    }

    private function createUserHelper(string $routePrefix): User
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
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_can_create_user(string $routePrefix)
    {
        $user = $this->createUserHelper($routePrefix);
        // Create 2 roles for the institution
        $roles = factory(Role::class, 2)
            ->create([
                'institutable_id' => $user->institutable_id,
                'institutable_type' => $user->institutable_type,
            ])
            ->map(function(Role $role) {
                $perms = Permission::inRandomOrder()->take(2)->get();
                $role->attachPermissions($perms->all());

                return $role;
            });

        $email = $this->faker->unique()->safeEmail;

        $this->actingAs($user)
            ->visitRoute("users.create")
            ->seeIsChecked('generate_password')
            ->submitForm('Save changes', [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastname,
                'email' => $email,
                'contact_number' => $this->faker->phoneNumber,
                'roles' => $roles->pluck('id')->all()
            ])
            ->seeInDatabase('users', [
                'email' => $email,
                'institutable_id' => $user->institutable_id,
                'institutable_type' => $user->institutable_type
            ])
            ->seeRouteIs("users.index");
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_will_populate_data_to_edit(string $routePrefix)
    {
        $owner = $this->createUserHelper($routePrefix);
        $user = factory(User::class)->create([
            'institutable_id' => $owner->institutable_id,
            'institutable_type' => $owner->institutable_type
        ]);
        // Create 2 roles for the institution
        $roles = factory(Role::class, 2)
            ->create([
                'institutable_id' => $owner->institutable_id,
                'institutable_type' => $owner->institutable_type
            ])
            ->map(function(Role $role) {
                $perms = Permission::inRandomOrder()->take(2)->get();
                $role->attachPermissions($perms->all());

                return $role;
            });
        $user->attachRole($roles->first());

        $this->actingAs($owner)
            ->visitRoute("users.edit", ['user' => $user])
            ->seeInField('firstname', $user->firstname)
            ->seeInField('lastname', $user->lastname)
            ->seeInField('email', $user->email)
            ->seeInField('contact_number', $user->contact_number)
            ->dontSeeIsChecked('generate_password')
            ->seeIsChecked('roles-' . $roles->first()->id);
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_can_get_edit_user(string $routePrefix)
    {
        $owner = $this->createUserHelper($routePrefix);
        $user = factory(User::class)->create([
            'institutable_id' => $owner->institutable_id,
            'institutable_type' => $owner->institutable_type
        ]);
        // Create 2 roles for the institution
        $roles = factory(Role::class, 2)
            ->create([
                'institutable_id' => $owner->institutable_id,
                'institutable_type' => $owner->institutable_type
            ])
            ->map(function(Role $role) {
                $perms = Permission::inRandomOrder()->take(2)->get();
                $role->attachPermissions($perms->all());

                return $role;
            });
        $user->attachRole($roles->first());

        $this->actingAs($owner)
            ->visitRoute("users.edit", ['user' => $user])
            ->press('Save changes')
            ->seeRouteIs("users.index");
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_will_display_delete_link($routePrefix)
    {
        $user = $this->createUserHelper($routePrefix);
        factory(User::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
            'is_app_owner' => $user->isApplicationOwner() ? 1 : 0
        ]);

        $this->actingAs($user)
            ->visitRoute("users.index")
            ->seeText('Delete');
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_cannot_delete_account_owner($routePrefix)
    {
        $user = $this->createUserHelper($routePrefix);

        $this->actingAs($user)
            ->delete(route('users.delete', ['user' => $user]));

        $user = $user->fresh();

        $this->assertFalse($user->trashed());
    }

    /**
     * @dataProvider getUserProvider
     * @param string $routePrefix
     */
    public function test_can_delete_non_account_owner($routePrefix)
    {
        $accountOwner = $this->createUserHelper($routePrefix);

        $userType = $routePrefix == 'callens' ? 'appOwner' : $routePrefix;
        $user = factory(User::class, $userType)->create(['is_account_owner' => 0]);

        $this->actingAs($accountOwner)
            ->delete(route('users.delete', ['user' => $user]));

        $user = $user->fresh();

        $this->assertTrue($user->trashed());
    }
}
