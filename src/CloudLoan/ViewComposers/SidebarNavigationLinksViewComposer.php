<?php
/**
 * Created by PhpStorm.
 * User: ntimobedyeboah
 * Date: 11/24/16
 * Time: 4:48 PM
 */

namespace CloudLoan\ViewComposers;


use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SidebarNavigationLinksViewComposer
{
    public function compose(View $view)
    {
        $sideNavLinks = $this->getSidebarNavigationLinks();

        return $view->with(compact('sideNavLinks'));
    }

    /**
     * Return menu links that matches user's
     * roles and permissions
     *
     * @return Collection
     */
    private function getSidebarNavigationLinks(): Collection
    {
        $authUser = auth()->user();

        $links = array_to_object(trans("sidebar_menu.{$this->getUserableKey($authUser)}.links"));

        return collect($links)->filter(function ($link) use ($authUser) {
            return $this->hasMatchedRole($authUser, $link->roles) ||
            $this->hasMatchedPermission($authUser, $link->permissions) ||
            $this->hasNoAbilities($link);
        });
    }

    /**
     * Returns links with no roles and permissions
     *
     * @param $link
     * @return bool
     */
    private function hasNoAbilities($link)
    {
        return empty($link->roles)  && empty($link->permissions);
    }

    /**
     * Returns links with roles that match the auth
     * user's roles
     *
     * @param $user
     * @param $linkRoles
     * @return mixed
     */
    private function hasMatchedRole($user, $linkRoles)
    {
        return $user->hasRole($linkRoles);
    }

    /**
     * Returns links with permissions that match the auth
     * user's permissions
     *
     * @param $user
     * @param $permissions
     * @return mixed
     */
    private function hasMatchedPermission($user, $permissions)
    {
        return $user->can($permissions);
    }

    private function getUserableKey($authUser)
    {
        return $authUser->institutable ?
            str_replace('morph', '', strtolower($authUser->institutable_type)) : 'callens';
    }
}