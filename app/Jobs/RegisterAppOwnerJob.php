<?php

namespace App\Jobs;

use App\Entities\Role;
use App\Entities\User;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class RegisterAppOwnerJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return User
     */
    public function handle()
    {
        return $this->setupOwner();
    }

    /**
     * @return User
     */
    private function setupOwner(): User
    {
        $this->checkHasNotSetupOwner();

        $user = new User();
        $user->is_app_owner = true;
        $user->is_account_owner = true;

        // Add the user
        dispatch(new AddUserJob($this->request, $user));

        // Add the app owner role
        $role = dispatch(new AddRoleAndPermissionsJob(new Request([
            'role' => [
                'name' => Role::ROLE_APP_OWNER,
                'display_name' => 'Application Owner',
                'description' => 'Owner of the application'
            ]
        ])));
        $user->syncRoles([$role->id]);

        return $user;
    }

    /**
     * @return bool
     */
    private function checkHasNotSetupOwner(): bool
    {
        $rec = User::isAppOwner()->first();

        if (empty($rec)) {
            return true;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }
}
