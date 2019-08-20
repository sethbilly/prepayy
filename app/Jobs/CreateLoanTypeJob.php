<?php

namespace App\Jobs;

use App\Entities\LoanType;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class CreateLoanTypeJob
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
     * @return LoanType
     */
    public function handle()
    {
        $this->ensureHasNotBeenAdded();

        return $this->createLoanType();
    }

    private function ensureHasNotBeenAdded()
    {
        $loanType = LoanType::where($this->request->only(['name']))->first();

        if ($loanType) {
            throw ConflictWithExistingRecord::fromModel($loanType);
        }

        return true;
    }

    private function createLoanType(): LoanType
    {
        $details = $this->request->only(['name']);
        $details['user_id'] = $this->request->user()->id;

        return LoanType::create($details);
    }
}
