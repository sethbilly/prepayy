<?php

use App\Entities\User;
use App\Jobs\AddUserJob;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class AddUserJobTest extends TestCase
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

    private function getUserRequest()
    {
        return $this->getAuthenticatedRequest([
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->faker->password(8),
            'othernames' => $this->faker->name
        ]);
    }

    public function test_can_add_new_user_account()
    {
        $req = $this->getUserRequest();

        $user = dispatch(new AddUserJob($req, new User()));

        collect($req->all())
            ->each(function($val, $key) use ($user) {
                if (array_key_exists($key, $user->getFillable())) {
                    $this->assertEquals($val, $user[$key]);
                }
            });
    }

    public function test_can_update_existing_user_account()
    {
        $user = factory(User::class)->create();

        $req = $this->getUserRequest();
        $req->merge(['email' => $user->email]);

        $updatedUser = dispatch(new AddUserJob($req, $user));

        $this->assertEquals($user->id, $updatedUser->id);
        $this->assertEquals($req->get('firstname'), $user->firstname);
        $this->assertEquals($req->get('lastname'), $user->lastname);
        $this->assertEquals($req->get('othernames'), $user->othernames);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     * @expectedExceptionCode 422
     */
    public function test_cannot_add_user_with_email_conflict()
    {
        $user = factory(User::class)->create();

        $req = $this->getUserRequest();
        $req->replace(['email' => $user->email]);

        dispatch(new AddUserJob($req, new User()));
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_update_user_with_email_conflict()
    {
        $users = factory(User::class, 2)->create();

        // Attempt updating user 2's email to that of user 1
        $req = $this->getUserRequest();
        $req->replace(['email' => $users->first()->email]);

        dispatch(new AddUserJob($req, $users->last()));
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_add_user_with_contact_number_conflict() 
    {
        $user = factory(User::class)->create();

        $req = $this->getUserRequest();
        $req->replace(['contact_number' => $user->contact_number]);

        dispatch(new AddUserJob($req, new User()));
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_update_user_with_contact_number_conflict() 
    {
        $users = factory(User::class, 2)->create();

        // Attempt updating user 2's email to that of user 1
        $req = $this->getUserRequest();
        $req->replace(['contact_number' => $users->first()->contact_number]);

        dispatch(new AddUserJob($req, $users->last()));
    }

    public function test_will_restore_and_update_account_of_soft_deleted_user()
    {
        $user = factory(User::class)->create();
        $user->delete();

        $req = $this->getUserRequest();
        $req->replace(['email' => $user->email]);

        $newUser = dispatch(new AddUserJob($req, new User()));

        $this->assertEquals($newUser->id, $user->id);
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_add_user_that_conflicts_with_soft_deleted_user()
    {
        $user = factory(User::class, 'employer')->create();
        $user->delete();

        $req = $this->getUserRequest();
        $req->replace(['email' => $user->email]);

        dispatch(new AddUserJob($req, new User()));
    }
}
