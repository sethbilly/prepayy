<?php

namespace App\Jobs;

use App\Entities\LoanApplicationStatus;
use App\Entities\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanApplicationRequestsJob
{
    /**
     * @var Request
     */
    private $request;

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
        $this->request = $request;
        $this->user = $request->user();
    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle()
    {
        return $this->getApplications();
    }

    private function getApplications()
    {
        // Get the list of loan applications
        $stmt = $this->user->isFinancialInstitutionStaff() || $this->user->isEmployerStaff() ?
            $this->user->institutable->loanApplications() :
            $this->user->loanApplications();

        if ($this->user->isFinancialInstitutionStaff()) {
            $stmt->whereIn(
                'loan_application_status_id',
                LoanApplicationStatus::getPartnerNonDisbursedStatuses()->pluck('id')->all()
            );
        }

        // Employers can access all employee loans which have been submitted for approval
        if ($this->user->isEmployerStaff()) {
            $status = LoanApplicationStatus::getDraftStatus();
            $stmt->where('loan_application_status_id', '<>', $status->id);
        }

        return $stmt
            // Employers and borrowers can filter applications by institution
            ->when($this->request->has('institution_id'), function ($q) {
                return $q->where('institution_id', $this->request->get('institution_id'));
            })
            // Employers and financial institutions can filter applications by borrower
            ->when($this->request->has('borrower_id'), function ($q) {
                return $q->where('user_id', $this->request->get('borrower_id'));
            })
            // Financial institutions and borrowers can filter applications by employer
            ->when($this->request->has('employer_id'), function ($q) {
                return $q->where('employer_id', $this->request->get('employer_id'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->request->get('limit', 15));
    }
}
