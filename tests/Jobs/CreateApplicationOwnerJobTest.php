<?php

use App\Entities\Role;
use App\Entities\User;
use App\Jobs\CreateApplicationOwnerJob;
use Illuminate\Http\Request;

class CreateApplicationOwnerJobTest extends TestCase
{
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
        $email = $this->faker->safeEmail;

        return new Request([
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->firstName,
            'email' => $email,
            'password' => $email,
            'password_confirmation' => $email,
        ]);
    }
    public function test_can_register_app_owner()
    {
        $req = $this->getRequest();

        $user = dispatch(new CreateApplicationOwnerJob($req));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($req->get('email'), $user->email);
        $this->assertTrue($user->hasRole(Role::ROLE_APP_OWNER));
        $this->assertTrue($user->isApplicationOwner());
        $this->assertTrue($user->isAccountOwner());
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_register_duplicate_app_owner()
    {
        factory(User::class, 'appOwner')->create();
        $req = $this->getRequest();

        dispatch(new CreateApplicationOwnerJob($req));
    }
}
