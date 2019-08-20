<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 17/01/2017
 * Time: 15:43
 */

namespace CloudLoan\Traits;


use App\Entities\Role;
use App\Entities\User;
use App\Jobs\AddRoleAndPermissionsJob;
use App\Jobs\AddUserJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait AddsAccountOwnerTrait
{
    /**
     * Add account owner
     * @param Request $request
     * @param Model $model
     * @return User
     */
    private function createAccountOwner(Request $request, Model $model): User
    {
        $owner = $this->createAccountOwnerHelper($request, $model);
        $role = $this->getAccountOwnerRole($request);

        if (!$owner->hasRole($role->name)) {
            $owner->attachRole($role);
        }

        return $owner;
    }

    /**
     * @param Request $request
     * @param Model $model the organization the account owner belongs to
     * @return User
     */
    private function createAccountOwnerHelper(Request $request, Model $model): User
    {
        $accountOwner = $model->accountOwner ?? new User([
                'institutable_id' => $model->id,
                'institutable_type' => $model->getMorphClass()
            ]);
        $accountOwner->is_account_owner = true;

        $req = new Request($request->get('owner'));
        $req->setUserResolver(function() use ($request) { return $request->user(); });

        return dispatch(new AddUserJob($req, $accountOwner));
    }

    /**
     * @param Request $request
     * @return Role
     */
    private function getAccountOwnerRole(Request $request): Role
    {
        $req = new Request([
            'role' => [
                'name' => Role::ROLE_ACCOUNT_OWNER,
                'display_name' => 'Account Owner',
                'description' => 'Overall administrator (owner) of the institution\'s account'
            ]
        ]);
        $req->setUserResolver(function() use ($request) {$request->user();});

        $role = Role::firstOrNew(['name' => Role::ROLE_ACCOUNT_OWNER]);

        return dispatch(new AddRoleAndPermissionsJob($req, $role));
    }
}