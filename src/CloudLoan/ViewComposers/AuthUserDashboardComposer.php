<?php
/**
 * Created by PhpStorm.
 * User: benjaminmanford
 * Date: 1/20/17
 * Time: 5:31 PM
 */

namespace CloudLoan\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;


class AuthUserDashboardComposer
{
    /**
     * @var Request
     */
    private $request;

    /**
     * AuthUserComposer constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param View $view
     */
    public function compose(View $view) {
        $authUser = $this->request->user();

        //dd($authUser);

        $view->with('authUser', $authUser);

        // Add organization (employer/partner) for organization's views
        $employer = $authUser && $authUser->isFinancialInstitutionStaff() ? $authUser->institutable : null;
        $partner = $authUser && $authUser->isEmployerStaff() ? $authUser->institutable : null;

        //dd($employer);

        if ($employer) {
            $view->with('employer', $employer);
        } else if ($partner) {
            $view->with('partner', $partner);
        }
    }
}