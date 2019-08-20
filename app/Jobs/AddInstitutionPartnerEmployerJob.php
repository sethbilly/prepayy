<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class AddInstitutionPartnerEmployerJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var FinancialInstitution
     */
    private $institution;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->institution = $request->user()->institutable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->addPartnerEmployer();
    }

    /**
     * @throws ConflictWithExistingRecord
     */
    private function addPartnerEmployer()
    {
        $key = 'employer_id';

        // Each partner employer can be added exactly once
        $emp = $this->institution->partnerEmployers()->where('id', $this->request->get($key))->first();

        if (!empty($emp)) {
            throw ConflictWithExistingRecord::fromModel($emp);
        }

        $this->institution->partnerEmployers()->attach($this->request->get($key));
    }
}
