<?php

namespace App\Jobs;

use App\Entities\LoanType;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class UpdateLoanTypeJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanType
     */
    private $loanType;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanType $loanType
     */
    public function __construct(Request $request, LoanType $loanType)
    {
        $this->request = $request;
        $this->loanType = $loanType;
    }

    /**
     * Execute the job.
     *
     * @return LoanType
     */
    public function handle()
    {
        $this->ensureDoesNotConflictWithExistingAccount();

        return $this->updateLoanType();
    }

    private function ensureDoesNotConflictWithExistingAccount()
    {
        $loanType = LoanType::where($this->request->only(['name']))->first();

        if ($loanType && $this->loanType->id !== $loanType->id) {
            throw ConflictWithExistingRecord::fromModel($loanType);
        }

        return true;
    }

    private function updateLoanType(): LoanType
    {
        $this->loanType->update($this->request->only(['name']));

        return $this->loanType;
    }
}
