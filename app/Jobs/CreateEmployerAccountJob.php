<?php

namespace App\Jobs;

use App\Entities\Employer;
use App\Entities\User;
use App\Notifications\AccountOwnerCreated;
use CloudLoan\Traits\AddsAccountOwnerTrait;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateEmployerAccountJob
{
    use AddsAccountOwnerTrait;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Employer
     */
    private $employer;
    /**
     * @var bool
     */
    private $isNewAccountOwner = false;
    /**
     * @var bool
     */
    private $sendPasswordSetupLink = false;
    /**
     * @var PasswordBroker
     */
    private $broker;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Employer $employer
     */
    public function __construct(Request $request, Employer $employer = null)
    {
        $this->request = $request;
        $this->employer = $employer ?? new Employer();
        $this->request = $request;
        $this->isNewAccountOwner = !$this->employer->accountOwner;
        // Generate and send password instructions if explicitly requested or if new account is created
        $this->sendPasswordSetupLink = $this->request->has('generate_password') || $this->isNewAccountOwner;
    }

    /**
     * Execute the job.
     * Broker is never passed in explicitly. The IOC injects it for us when the handle method is called
     *
     * @param PasswordBroker $broker
     * @return Employer
     */
    public function handle(PasswordBroker $broker)
    {
        $this->broker = $broker;
        return $this->addEmployer();
    }

    /**
     * @return Employer
     */
    private function addEmployer(): Employer
    {
        return DB::transaction(function() {
            dispatch(new AddEmployerJob($this->request, $this->employer));

            // Add the account owner
            $owner = $this->createAccountOwner($this->request, $this->employer);

            // Send password setup instructions to the account owner
            $this->sendAccountOwnerPasswordSetupInstructions($owner);

            // Add default account roles
            dispatch(new CreateDefaultOrganizationRolesJob($this->request, $this->employer));

            return $this->employer->load(['accountOwner']);
        });
    }

    /**
     * @param User $accountOwner
     * @return bool
     */
    private function sendAccountOwnerPasswordSetupInstructions(User $accountOwner)
    {
        if (!$this->sendPasswordSetupLink) {
            return false;
        }

        $token = $this->broker->getRepository()->create($accountOwner);
        if ($this->isNewAccountOwner) {
            // Send new account setup notification
            $accountOwner->notify(new AccountOwnerCreated($token));
        }

        // Send account update notification
        $accountOwner->notify(new AccountOwnerCreated($token));
    }
}
