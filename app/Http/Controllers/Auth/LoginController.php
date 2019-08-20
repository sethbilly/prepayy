<?php

namespace App\Http\Controllers\Auth;

use App\Entities\User;
use App\Http\Controllers\Controller;
use CloudLoan\Traits\RedirectsToUserDashboard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, RedirectsToUserDashboard;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/callens/partners';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return mixed
     */
    protected function authenticated(Request $request, User $user)
    {
        if ($user->isApplicationOwner()) {
            return redirect()->intended(route('callens.partners.index'));
        } else if ($user->isFinancialInstitutionStaff() || $user->isEmployerStaff()) {
            return redirect()->intended(route('roles.index'));
        } else {
            return redirect()->intended(route('loan_applications.index'));
        }
    }
}
