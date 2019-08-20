<?php

use App\Entities\User;
use App\Jobs\RegisterAppOwnerJob;
use Illuminate\Http\Request;

class RegisterAppOwnerJobTest extends TestCase
{
    private function getRequest(): Request
    {
        $faker = Faker\Factory::create();

        return new Request([
            'type' => 'owner',
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => $faker->safeEmail,
            'password' => $faker->password(8)
        ]);
    }

    public function test_can_register_app_owner()
    {
        $req = $this->getRequest();

        $user = dispatch(new RegisterAppOwnerJob($req));

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($req->get('firstname'), $user->firstname);
        $this->assertEquals($req->get('lastname'), $user->lastname);
        $this->assertEquals($req->get('email'), $user->email);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_register_multiple_app_owners()
    {
        factory(User::class, 'appOwner')->create();
        $req = $this->getRequest();

        dispatch(new RegisterAppOwnerJob($req));
    }
}
