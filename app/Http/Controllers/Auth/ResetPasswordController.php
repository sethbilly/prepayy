<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use CloudLoan\Traits\RedirectsToUserDashboard;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords, RedirectsToUserDashboard;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

    /**
     * @param $response
     * @return mixed
     */
    protected function sendResetResponse($response)
    {
        return $this->redirectToDashboard(auth()->user(), ['status' => trans($response)]);
    }
}
