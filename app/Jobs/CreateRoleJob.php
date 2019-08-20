<?php

namespace App\Jobs;

use App\Entities\Role;
use Illuminate\Http\Request;

class CreateRoleJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Role
     */
    private $role;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Role $role
     */
    public function __construct(Request $request, Role $role = null)
    {
        $this->request = $request;
        $this->role = $role;
    }

    /**
     * Execute the job.
     *
     * @return Role
     */
    public function handle()
    {
        return $this->addRole();
    }

    /**
     * @return Role
     */
    private function addRole(): Role
    {
        // If the request does not contain the name of the role, set the name to slug of the display name
        // Merge the logged in user's institution to the role being added
        if (!$this->request->has('role.name')) {
            $role = $this->request->get('role');
            $role['name'] = $this->request->input('role.display_name');
            $this->request->merge(['role' => $role]);
        }

        return dispatch(new AddRoleAndPermissionsJob($this->request, $this->role));
    }
}
