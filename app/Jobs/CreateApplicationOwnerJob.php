<?php

namespace App\Jobs;

use App\Entities\Role;
use App\Entities\User;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class CreateApplicationOwnerJob
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
        return $this->createAppOwner();
    }

    /**
     * Create the application owner's account
     * @return User
     */
    private function createAppOwner(): User
    {
        // There can be only one application owner who also is the account owner
        $this->checkHasNoAppOwner();

        $user = new User();
        $user->is_app_owner = 1;
        $user->is_account_owner = 1;

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
     * Throws exception if an application owner has been registered
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkHasNoAppOwner(): bool
    {
        $user = User::isAppOwner()->first();

        if (!$user) {
            return true;
        }

        throw ConflictWithExistingRecord::fromModel($user);
    }
}
