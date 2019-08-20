<?php

namespace App\Jobs;

use App\Entities\Country;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\IdentificationCard;
use App\Entities\User;
use Illuminate\Http\Request;

class GetBorrowerLoanProfileDataJob
{
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->user = $request->user();
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle()
    {
        return $this->getBorrowerData();
    }

    private function getBorrowerData(): array
    {
        $data['institutions'] = FinancialInstitution::orderBy('name', 'asc')->get();
        $data['employers'] = Employer::all();
        $data['contractTypes'] = ['Full Time', 'Part Time'];
        $data['countries'] = Country::orderBy('name')->get();
        $data['idTypes'] = IdentificationCard::getIdentificationTypes();

        // The authenticated user's current employer
        $data['currentEmployer'] = $this->user->currentEmployer() ?? new Employer();
        // The authenticated user's current id card
        $data['idCard'] = $this->user->currentIdCard() ?? new IdentificationCard();

        return $data;
    }
}
