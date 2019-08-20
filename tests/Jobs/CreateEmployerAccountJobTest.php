<?php

use App\Entities\Employer;
use App\Entities\User;
use App\Jobs\CreateEmployerAccountJob;
use App\Notifications\AccountOwnerCreated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateEmployerAccountJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class, 'appOwner')->create();
        $this->faker = Faker\Factory::create();
    }

    private function getRequest(): Request
    {
        return $this->getAuthenticatedRequest([
            'name' =>  $this->faker->company,
            'address' => $this->faker->address,
            'owner' => [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'email' => $this->faker->email,
                'password' => $this->faker->password(8),
                'contact_number' => $this->faker->phoneNumber
            ]
        ], $this->user);
    }

    public function test_can_add_employer()
    {
        $req = $this->getRequest();

        $rec = dispatch(new CreateEmployerAccountJob($req));

        $this->assertInstanceOf(Employer::class, $rec);
        $this->assertEquals($req->get('name'), $rec->name);
        $this->assertEquals($req->get('address'), $rec->address);

        $this->assertInstanceOf(User::class, $rec->accountOwner);
        $this->assertEquals($req->input('owner.firstname'), $rec->accountOwner->firstname);
        $this->assertEquals($req->input('owner.email'), $rec->accountOwner->email);
        $this->assertEquals($req->input('owner.lastname'), $rec->accountOwner->lastname);
        $this->assertEquals($this->user->id, $rec->creator->id);

        $this->assertTrue($rec->roles->count() > 0);
    }

    public function test_can_update_employer()
    {
        $employer = factory(Employer::class)->create();
        $req = $this->getRequest();

        $rec = dispatch(new CreateEmployerAccountJob($req, $employer));

        $this->assertInstanceOf(Employer::class, $rec);
        $this->assertEquals($employer->id, $rec->id);
        $this->assertEquals($req->get('name'), $rec->name);
        $this->assertEquals($req->get('address'), $rec->address);

        $this->assertInstanceOf(User::class, $rec->accountOwner);
        $this->assertEquals($req->input('owner.firstname'), $rec->accountOwner->firstname);
        $this->assertEquals($req->input('owner.email'), $rec->accountOwner->email);
        $this->assertEquals($req->input('owner.lastname'), $rec->accountOwner->lastname);
    }

    public function test_sends_account_created_notification()
    {
        $owner = $this->createEmployerAccountOwner();
        $req = $this->getRequest();
        $req->merge(['generate_password' => true]);

        $this->expectsNotification($owner, AccountOwnerCreated::class);

        dispatch(new CreateEmployerAccountJob($req, $owner->institutable));
    }
}
