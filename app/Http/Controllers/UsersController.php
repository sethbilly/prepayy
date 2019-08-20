<?php

namespace App\Http\Controllers;

use App\Entities\User;
use App\Http\Requests\CreateUserAccountRequest;
use App\Jobs\CreateUserAccountJob;
use App\Jobs\GetInstitutionApprovalLevelsJob;
use App\Jobs\GetRolesForInstitutionJob;
use App\Jobs\GetUsersForInstitutionJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        // Get users of the institution
        $users = dispatch(new GetUsersForInstitutionJob($request));
        $search = $request->get('search');
        $limit = $request->get('limit', 20);

        return view('dashboard.users.index')->with(compact('users', 'search', 'limit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $title = 'New User';
        $user = new User();
        $roles = dispatch(new GetRolesForInstitutionJob($request));
        $approvalLevels = dispatch(new GetInstitutionApprovalLevelsJob($request));

        return view('dashboard.users.create')
            ->with(compact('user', 'roles', 'approvalLevels', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateUserAccountRequest  $request
     * @return \Illuminate\Http\Response|RedirectResponse
     */
    public function store(CreateUserAccountRequest $request)
    {
        try {
            dispatch(new CreateUserAccountJob($request));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error adding user', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back();
        } catch (MissingMandatoryParametersException $exception) {
            logger()->error($exception->getMessage());

            flash()->error($exception->getMessage());

            return back()->withInput();
        }

        flash()->success("User was added successfully");

        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param User $user
     * @return View
     */
    public function edit(Request $request, User $user)
    {
        $title = 'Edit User';
        $roles = dispatch(new GetRolesForInstitutionJob($request));
        $approvalLevels = dispatch(new GetInstitutionApprovalLevelsJob($request));

        return view('dashboard.users.create')
            ->with(compact('user', 'roles', 'approvalLevels', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateUserAccountRequest $request
     * @param User $user
     * @return \Illuminate\Http\Response|RedirectResponse
     */
    public function update(CreateUserAccountRequest $request, User $user)
    {
        try {
            dispatch(new CreateUserAccountJob($request, $user));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating user', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back();
        } catch (MissingMandatoryParametersException $exception) {
            logger()->error($exception->getMessage());

            flash()->error($exception->getMessage());

            return back();
        }

        flash()->success('User was updated successfully');

        return redirect()->route('users.index');
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Account owners cannot be deleted
        $wasDeleted = $user->isAccountOwner() ? false : $user->delete();

        $wasDeleted ?
            flash()->success($user->getFullName() . ' was deleted') :
            flash()->error($user->getFullName() . ' was not deleted');

        return back();
    }
}