<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 06/02/2017
 * Time: 10:58
 */

namespace CloudLoan\Traits;


use App\Entities\User;

trait RedirectsToUserDashboard
{
    /**
     * Redirect to the user's account dashboard
     * @param User $user
     * @param array $data
     * @return mixed
     */
    public function redirectToDashboard(User $user, array $data = [])
    {
        if ($user->isApplicationOwner()) {
            return redirect()->guest(route('callens.partners.index'))->with($data);
        } else if ($user->isFinancialInstitutionStaff() || $user->isEmployerStaff()) {
            return redirect()->guest(route('roles.index'))->with($data);
        }

        return redirect()->guest(route('user.profile.index'))->with($data);
    }
}