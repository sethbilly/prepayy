<?php

namespace App\Jobs;

use App\Entities\ApprovalLevel;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetLoanApprovalLevelLabel
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var LoanApplication
     */
    private $application;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanApplication $application
     */
    public function __construct(Request $request, LoanApplication $application)
    {
        $this->user = $request->user();
        $this->application = $application;
    }

    /**
     * Execute the job.
     *
     * @return \stdClass|null
     */
    public function handle()
    {
        return $this->getLabel();
    }

    private function getLabel()
    {
        return $this->user->isEmployerStaff() ?
            $this->getEmployerLabel() :
            $this->getPartnerLabel();
    }

    private function getEmployerLabel()
    {
        $status = $this->application->getLoanApplicationStatus();

        if (!$status->isEmployerStatus()) {
            return null;
        }

        $statusDeclined = LoanApplicationStatus::getEmployerDeclinedStatus();

        return $this->getInstitutionStatusLabel($statusDeclined);
    }

    private function getPartnerLabel()
    {
        $status = $this->application->getLoanApplicationStatus();

        if (!$status->isPartnerNonDisbursedStatus()) {
            return null;
        }

        $statusDeclined = LoanApplicationStatus::getPartnerDeclinedStatus();

        return $this->getInstitutionStatusLabel($statusDeclined);
    }

    private function getInstitutionStatusLabel(LoanApplicationStatus $statusDeclined)
    {
        $levels = $this->getInstitutionsApprovalLevels();

        if ($levels->isEmpty()) {
            return null;
        }

        $approvers = $this->getLoanInstitutionApprovers();
        $loanLabel = null;


        foreach ($levels as $i => $level) {
            $approver = $this->getApproverAtApprovalLevel($level, $approvers);

            if (empty($approver)) {
                $loanLabel = $this->getLabelObject('Pending', 'at ' . $level->name);
                break;
            } else {
                if ($this->wasDeclinedByApprover($approver, $statusDeclined)) {
                    $loanLabel = $this->getLabelObject('Declined', 'at ' . $level->name);
                    break;
                }
            }
        }

        return $loanLabel;
    }

    private function getInstitutionsApprovalLevels(): Collection
    {
        if ($this->user->institutable) {
            return $this->user->institutable->approvalLevels->sortBy('id');
        }

        return collect([]);
    }

    private function getLoanInstitutionApprovers(): Collection
    {
        return $this->application->approvers()
            ->whereIn('id', $this->user->institutable->staffMembers()->pluck('id')->all())
            ->get();
    }

    /**
     * Returns the user who approved the loan at a given approval level
     * @param ApprovalLevel $level
     * @param Collection $approvers
     * @return User|null
     */
    private function getApproverAtApprovalLevel(
        ApprovalLevel $level,
        Collection $approvers
    ) {
        return $approvers->first(function (User $user) use ($level) {
            return $user->getApprovalLevelId() == $level->id;
        });
    }

    /**
     * @param User $user
     * @param LoanApplicationStatus $statusDeclined
     * @return bool
     */
    private function wasDeclinedByApprover(
        User $user,
        LoanApplicationStatus $statusDeclined
    ): bool {
        return $user->pivot->loan_application_status_id == $statusDeclined->id;
    }

    /**
     * @param string $label
     * @param string $level
     * @return \stdClass
     */
    private function getLabelObject(string $label, string $level): \stdClass
    {
        $labelObject = new \stdClass();
        $labelObject->label = $label;
        $labelObject->level = $level;

        return $labelObject;
    }
}
