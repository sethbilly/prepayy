<?php
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\Role;
use App\Entities\User;

/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 17/01/2017
 * Time: 13:51
 */
trait CreatesUsersTrait
{
    /**
     * @param Employer|null $employer
     * @return User
     */
    public function createEmployerAccountOwner(Employer $employer = null): User
    {
        $employer = $employer ?? factory(Employer::class)->create();
        $user = factory(User::class, 'employer')->make(['institutable_id' => $employer->id]);
        $user->is_account_owner = 1;
        $user->save();

        $this->assignUserAccountOwnerRole($user);

        return $user;
    }

    /**
     * Assign the given user the account owner role
     * @param User $user
     */
    private function assignUserAccountOwnerRole(User $user)
    {
        $role = Role::firstOrCreate(['name' => Role::ROLE_ACCOUNT_OWNER]);

        if (!$user->hasRole($role->name)) {
            $user->attachRole($role);
        }
    }

    /**
     * @return User
     */
    public function createApplicationOwner(): User
    {
        $user = factory(User::class, 'appOwner')->create();

        $role = Role::firstOrCreate(['name' => Role::ROLE_APP_OWNER]);

        $user->attachRole($role);

        return $user;
    }

    /**
     * @param FinancialInstitution|null $institution
     * @return User
     */
    public function createInstitutionAccountOwner(FinancialInstitution $institution = null): User
    {
        $institution = $institution ?? factory(FinancialInstitution::class)->create();
        $user = factory(User::class, 'partner')->make(['institutable_id' => $institution->id]);
        $user->is_account_owner = 1;
        $user->save();

        $this->assignUserAccountOwnerRole($user);

        return $user;
    }
}