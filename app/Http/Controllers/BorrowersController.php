<?php

namespace App\Http\Controllers;

use App\Entities\Country;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanProduct;
use App\Entities\User;
use App\Http\Requests\CheckLoanEligibilityRequest;
use App\Http\Requests\LoanApplicationFormRequest;
use App\Http\Requests\UpdateBorrowerRequest;
use App\Jobs\GetLoanProductsJob;
use App\Jobs\IsEligibleForLoanJob;
use App\Jobs\SubmitLoanApplicationJob;
use App\Jobs\UpdateBorrowerProfileJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowersController extends Controller
{
    const SKIP_LOAN_GUIDELINES_COOKIE = 'skip_loan_guidelines';
    const CURRENT_EMPLOYER = 'employer_id';

    /**
     * Get the borrower's profile page
     * @param Request $request
     * @return mixed
     */
    public function profile(Request $request)
    {
        return view('dashboard.borrower.profile')->with($this->getProfileData($request->user()));
    }

    /**
     * Get data for the user's profile page
     * @param User $user
     * @return array
     */
    private function getProfileData(User $user): array
    {
        $data['institutions'] = FinancialInstitution::orderBy('name', 'asc')->get();
        $data['employers'] = Employer::all();
        $data['contractTypes'] = ['Full Time', 'Part Time'];
        $data['countries'] = Country::orderBy('name')->get();
        $data['idTypes'] = IdentificationCard::getIdentificationTypes();

        // The authenticated user's current employer
        $data['currentEmployer'] = $user->currentEmployer() ?? new Employer();
        // The authenticated user's current id card
        $data['idCard'] = $user->currentIdCard() ?? new IdentificationCard();

        return $data;
    }

    public function getLoans(Request $request)
    {
        $applications = $request->user()->loanApplications()->paginate($request->get('limit', 6));

        return view('dashboard.borrower.loans')->with(compact('applications'));
    }

    public function store(UpdateBorrowerRequest $request)
    {
        try {
            $data = $this->dispatch(new UpdateBorrowerProfileJob($request));
            //checking the boolean value
            if($data) {
                flash()->success("Your profile was updated successfully");

                return redirect()->route('user.profile.index');

            }else if(!$data){
                flash()->error("An error occurred. Check your dates at the 'ID Details' Tab. ");

                return back();
            }

        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating profile', ['error' => $exception->getMessage()]);

            flash()->error("An error occurred while updating the user. Please try again!");

            return back();
        }



    }
}
