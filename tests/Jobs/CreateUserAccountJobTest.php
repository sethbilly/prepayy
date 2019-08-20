<?php

use App\Entities\ApprovalLevel;
use App\Entities\Employer;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\CreateUserAccountJob;
use App\Notifications\UserCreated;
use App\Notifications\UserPasswordChangeRequested;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateUserAccountJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var User
     */
    private $authUser;

    /**
     * @var Employer
     */
    private $employer;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->employer = factory(Employer::class)->create();
        $this->authUser = $this->createEmployerAccountOwner($this->employer);
        $this->faker = Faker\Factory::create();
    }

    private function getRequest(): Request
    {
        $roles = factory(Role::class, 2)
            ->create()
            ->map(function(Role $role) {
                $role->attachPermissions(Permission::orderBy('id')->take(2)->get()->all());

                return $role;
            });

        return $this->getAuthenticatedRequest([
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'contact_number' => $this->faker->phoneNumber,
            'roles' => $roles->pluck('id')->all()
        ], $this->authUser);
    }

    public function test_can_create_user_account()
    {
        $req = $this->getRequest();
        $user = new User([
            'institutable_id' => $this->authUser->institutable_id,
            'institutable_type' => $this->authUser->institutable_type,
        ]);
        $this->expectsNotification($user, UserCreated::class);

        dispatch(new CreateUserAccountJob($req, $user));

        $user = $user->fresh();
        $this->assertEquals($req->get('firstname'), $user->firstname);
        $this->assertEquals($req->get('lastname'), $user->lastname);
        $this->assertEquals($req->get('email'), $user->email);
        $this->assertTrue($user->isEmployerStaff());
        $this->assertCount(2, $user->roles);

        foreach ($req->get('roles') as $i => $id) {
            $this->assertEquals($id, $user->roles[$i]->id);
        }
    }

    public function test_can_create_partner_user_account()
    {
        $owner = $this->createInstitutionAccountOwner();
        $req = $this->getRequest();
        $req->setUserResolver(function() use($owner) {return $owner;});

        $user = dispatch(new CreateUserAccountJob($req));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($req->get('firstname'), $user->firstname);
        $this->assertEquals($req->get('lastname'), $user->lastname);
        $this->assertEquals($req->get('email'), $user->email);
        $this->assertFalse($user->isEmployerStaff());
        $this->assertTrue($user->isFinancialInstitutionStaff());

        foreach ($req->get('roles') as $i => $id) {
            $this->assertEquals($id, $user->roles[$i]->id);
        }
    }

    public function test_can_update_user_account()
    {
        $req = $this->getRequest();
        $req->merge(['generate_password' => 1]);
        $user = factory(User::class)->create([
            'institutable_id' => $this->authUser->institutable_id,
            'institutable_type' => $this->authUser->institutable_type,
        ]);
        $this->expectsNotification($user, UserPasswordChangeRequested::class);

        dispatch(new CreateUserAccountJob($req, $user));

        $this->assertEquals($req->get('firstname'), $user->firstname);
        $this->assertEquals($req->get('lastname'), $user->lastname);
        $this->assertEquals($req->get('email'), $user->email);
        $this->assertCount(2, $user->roles);

        foreach ($req->get('roles') as $i => $id) {
            $this->assertEquals($id, $user->roles[$i]->id);
        }
    }

    public function test_cannot_update_account_owner_roles()
    {
        $user = $this->createEmployerAccountOwner();
        $req = $this->getRequest();

        dispatch(new CreateUserAccountJob($req, $user));

        $this->assertCount(1, $user->roles);
        $this->assertEquals(Role::ROLE_ACCOUNT_OWNER, $user->roles->first()->name);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function test_will_throw_error_if_user_approval_level_is_not_provided()
    {
        $req = $this->getRequest();
        factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $this->employer->id,
            'institutable_type' => $this->employer->getMorphClass()
        ]);
        $role = Role::where('id', $req->get('roles')[0])->first();
        $approverPermission = Permission::where('name', 'approve-loan-application')
            ->first();
        $role->attachPermission($approverPermission);

        $user = new User();
        $user->institutable()->associate($this->employer);

        dispatch(new CreateUserAccountJob($req, $user));
    }

    public function test_will_assign_user_to_approval_level()
    {
        $req = $this->getRequest();
        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $this->employer->id,
            'institutable_type' => $this->employer->getMorphClass()
        ]);

        $req->merge(['approval_level_id' => $levels[1]->id]);

        $role = Role::where('id', $req->get('roles')[0])->first();
        $approverPermission = Permission::where('name', 'approve-loan-application')
            ->first();
        $role->attachPermission($approverPermission);

        $user = new User();
        $user->institutable()->associate($this->employer);

        $this->expectsNotification($user, UserCreated::class);

        $newUser = dispatch(new CreateUserAccountJob($req, $user));

        $this->assertEquals($levels[1]->id, $newUser->approval_level_id);
    }
}
