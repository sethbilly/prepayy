<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use Illuminate\Http\Request;

class IsEligibleForLoanJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var FinancialInstitution
     */
    private $partner;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param FinancialInstitution $partner
     */
    public function __construct(Request $request, FinancialInstitution $partner)
    {
        $this->request = $request;
        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return (bool) $this->partner->partnerEmployers()->where('id', $this->request->get('employer_id'))->count();
    }
}
