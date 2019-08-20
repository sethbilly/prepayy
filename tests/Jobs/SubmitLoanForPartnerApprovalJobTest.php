<?php

use App\Entities\LoanApplication;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Jobs\SubmitLoanApplicationJob;
use App\Jobs\SubmitLoanForPartnerApproval;
use App\Notifications\LoanApprovalRequest;
use Illuminate\Support\Facades\Notification;

class SubmitLoanForPartnerApprovalJobTest extends TestCase
{
    /**
     * @var LoanApplication
     */
    private $application;

    public function setUp()
    {
        parent::setUp();
        $this->application = factory(LoanApplication::class)->create();
    }

    public function test_can_submit_application_for_partner_approval()
    {
        Notification::fake();

        $token = str_random(8);

        cache()->put(SubmitLoanApplicationJob::PARTNER_SUBMIT_TOKEN_KEY, [
            'token' => $token,
            'application_id' => $this->application->id
        ], 60);

        // Add 2 user's with loan approval permission for the employer
        $role = factory(Role::class)->create();
        $perm = Permission::where('name', 'approve-loan-application')->first();
        $role->attachPermission($perm);

        $users = factory(User::class, 'partner', 2)
            ->create([
                'institutable_id' => $this->application->loanProduct->institution->id
            ])
            ->map(function (User $user) use ($role) {
                $user->attachRole($role);

                return $user;
            });

        $req = $this->getAuthenticatedRequest(['submission_token' => $token], $this->application->user);

        $wasSubmitted = dispatch(new SubmitLoanForPartnerApproval($req, $this->application));

        $this->assertTrue($wasSubmitted);

        Notification::assertSentTo($users, LoanApprovalRequest::class);
    }

    public function test_cannot_submit_application_with_invalid_submission_token()
    {
        $req = $this->getAuthenticatedRequest([], $this->application->user);

        $wasSubmitted = dispatch(new SubmitLoanForPartnerApproval($req, $this->application));

        $this->assertFalse($wasSubmitted);
    }
}
