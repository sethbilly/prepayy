<?php

use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    public function setUp()
    {
        parent::setUp();
    }

    public function staffLoginProvider()
    {
        return [
            ['appOwner', 'callens.partners.index'],
            ['partner', 'roles.index'],
            ['employer', 'roles.index'],
            ['borrower', 'loan_applications.index']
        ];
    }

    private function getUserAccount(string $type): User
    {
        switch ($type) {
            case 'appOwner':
                return $this->createApplicationOwner();
            case 'partner':
                return $this->createInstitutionAccountOwner();
            case 'employer':
                return $this->createEmployerAccountOwner();
            default:
                return factory(User::class)->create();
        }
    }

    /**
     * @dataProvider staffLoginProvider
     * @param string $type
     * @param string $route
     */
    public function test_can_login(string $type, string $route)
    {
        $user = $this->getUserAccount($type);

        $this->visitRoute('login.get')
            ->seeLink('Reset Password', route('password.forgot.get'))
            ->type($user->email, 'email')
            ->type($user->email, 'password')
            ->press('Sign in')
            ->seeRouteIs($route)
            ->assertResponseOk();
    }

    public function test_can_click_on_registration_link_to_show_registration_page()
    {
        $this->visitRoute('login.get')
            ->seeLink('Register', route('register.get'))
            ->click('Register')
            ->seeRouteIs('register.get');
    }
}
