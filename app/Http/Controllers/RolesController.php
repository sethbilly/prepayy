<?php

namespace App\Http\Controllers;

use App\Entities\Role;
use App\Http\Requests\AddRoleRequest;
use App\Jobs\CreateRoleJob;
use App\Jobs\GetPermissionsForInstitutionJob;
use App\Jobs\GetRolesForInstitutionJob;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    /**
     * RolesController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $roles = dispatch(new GetRolesForInstitutionJob($request));
        $search = $request->get('search');
        $limit = $request->get('limit', 20);

        return view('dashboard.roles.index')->with(compact('roles', 'search', 'limit'));
    }

    public function create(Request $request)
    {
        $title = 'New Role';
        $role = new Role();
        $permissions = dispatch(new GetPermissionsForInstitutionJob($request));

        return view('dashboard.roles.create')->with(compact('role', 'permissions', 'title'));
    }

    public function store(AddRoleRequest $request)
    {
        try {
            dispatch(new CreateRoleJob($request));
        } catch (\Exception $e) {
            logger()->error('Error adding role', ['error' => $e->getMessage()]);

            flash()->error("An error occurred while adding the role. Please try again!");

            return back();
        }

        flash()->success("Role was added successfully");

        return redirect()->route('roles.index');
    }

    /**
     * @param Request $request
     * @param string $roleName
     * @return Role
     */
    private function getRoleRequestedInRoute(Request $request, string $roleName): Role
    {
        return Role::where([
            'name' => $roleName,
            'institutable_id' => $request->user()->institutable_id,
            'institutable_type' => $request->user()->institutable_type
        ])->firstOrFail();
    }

    // To allow the usage of role names in the edit url, do not use laravel's route model binding feature
    public function edit(Request $request, string $roleName)
    {
        $title = 'Edit Role';
        // To allow the use of role names in the url, do not use
        $role = $this->getRoleRequestedInRoute($request, $roleName);
        $permissions = dispatch(new GetPermissionsForInstitutionJob($request));
        $addedPermissions = $role->permissions()->pluck('name')->all();

        return view('dashboard.roles.create')->with(compact('role', 'permissions', 'addedPermissions', 'title'));
    }

    // To allow the usage of role names in the edit url, do not use laravel's route model binding feature
    public function update(AddRoleRequest $request, string $roleName)
    {
        $role = $this->getRoleRequestedInRoute($request, $roleName);

        try {
            dispatch(new CreateRoleJob($request, $role));
        } catch (\Exception $e) {
            logger()->error('Error updating role', ['error' => $e->getMessage()]);

            flash()->error("An error occurred while updating the role. Please try again!");

            return back();
        }

        flash()->success("Role was updated successfully");

        return redirect()->route('roles.index');
    }

    /**
     * @param Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        $wasDeleted = $role->delete();

        $wasDeleted ?
            flash()->success($role->display_name . ' was deleted') :
            flash()->error($role->display_name . ' was not deleted');

        return back();
    }
}
