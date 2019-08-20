<?php

use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Password;

class ResetPasswordControllerTest extends TestCase
{
    use CreatesUsersTrait, DatabaseTransactions;
    
    public function resetPasswordProvider()
    {
        return [
            ['appOwner', 'callens.partners.index'],
            ['partner', 'roles.index'],
            ['employer', 'roles.index'],
            ['borrower', 'user.profile.index']
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
     * @dataProvider resetPasswordProvider
     * @param string $type
     * @param $route
     */
    public function test_can_reset_password(string $type, $route)
    {
        $user = $this->getUserAccount($type);
        
        // Create a password token for the user 
        $tokenRepo = Password::getRepository();
        $token = $tokenRepo->create($user);
        
        $this->visitRoute('password.reset.get', ['token' => $token, 'email' => $user->email])
            ->seeElement('input[name="email"]')
            ->seeElement('input[name="password"]')
            ->seeElement('input[name="password_confirmation"]')
            ->seeInField('token', $token)
            ->seeInField('email', $user->email)
            ->type($user->email, 'password')
            ->type($user->email, 'password_confirmation')
            ->press('Reset Password')
            ->seeRouteIs($route);
    }
}
