<?php

use App\Entities\User;
use CloudLoan\Traits\DatabaseMigrationsForSqlite;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegisterControllerTest extends TestCase
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

    public function test_can_register_application_owner()
    {
        $password = $this->faker->password(8);
        $email = $this->faker->unique()->safeEmail;

        $this->visitRoute('register.get')
            ->seeLink('Sign in', route('login.get'))
            ->type('owner', 'type')
            ->type($this->faker->firstName, 'firstname')
            ->type($this->faker->lastName, 'lastname')
            ->type($email, 'email')
            ->type($password, 'password')
            ->type($password, 'password_confirmation')
            ->press('Sign up')
            ->seeInDatabase('users', ['email' => $email, 'is_app_owner' => 1, 'is_account_owner' => 1])
            ->seeInDatabase('roles', ['name' => 'app-owner'])
            ->seeRouteIs('callens.partners.index');
        
        $appOwner = User::isAppOwner()->first();
        $this->assertTrue($appOwner->hasRole('app-owner'));
    }

    public function test_can_visit_login_page_from_register_page()
    {
        $this->visitRoute('register.get')
            ->click('Sign in')
            ->seeRouteIs('login.get');
    }

    public function test_can_register_borrower()
    {
        // Register the application owner
        $this->createApplicationOwner();

        $password = $this->faker->password(8);
        $email = $this->faker->unique()->safeEmail;

        $this->visitRoute('register.get')
            ->type($this->faker->firstName, 'firstname')
            ->type($this->faker->lastName, 'lastname')
            ->type($email, 'email')
            ->type($password, 'password')
            ->type($password, 'password_confirmation')
            ->press('Sign up')
            ->seeInDatabase('users', [
                'email' => $email, 'is_app_owner' => 0, 'is_account_owner' => 0, 'institutable_id' => null
            ])
            ->seeRouteIs('user.profile.index');
    }
}
