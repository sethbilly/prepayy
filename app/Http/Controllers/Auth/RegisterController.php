<?php

namespace App\Http\Controllers\Auth;

use App\Entities\Role;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Jobs\AddRoleAndPermissionsJob;
use App\Jobs\AddUserJob;
use App\Jobs\RegisterAppOwnerJob;
use App\Jobs\RegisterBorrowerJob;
use App\Notifications\BorrowerAccountCreated;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/callens/partners';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data): User
    {
        // Setup the application owner account
        if (User::count() == 0) {
            return dispatch(new RegisterAppOwnerJob(new Request($data)));
        }

        // Register a borrower
        $user = dispatch(new AddUserJob(new Request($data), new User()));
        // Send account activation instructions via mail to the user
        $user->notify(new BorrowerAccountCreated());

        return $user;
    }

    /**
     * @param Request $request
     * @param User $user
     * @return mixed
     */
    protected function registered(Request $request, User $user)
    {
        if ($user->isApplicationOwner()) {
            return redirect()->route('callens.partners.index');
        }
        return redirect()->route('user.profile.index');
    }

    /**
     * Show the registration form
     * @param Request $request
     * @return mixed
     */
    public function showRegistrationForm(Request $request)
    {
        $type = $request->route()->getParameter('type');

        // Exactly one app owner can be registered
        if ($type && strcasecmp($type, 'owner') == 0 && User::isAppOwner()->count() > 0) {
            abort(404);
        }
        return view('auth.register', compact('type'));
    }
}
