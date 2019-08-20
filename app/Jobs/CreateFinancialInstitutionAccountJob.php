<?php

namespace App\Jobs;

use App\Entities\BrandStyle;
use App\Entities\FinancialInstitution;
use App\Entities\User;
use App\Notifications\AccountOwnerCreated;
use CloudLoan\Traits\AddsAccountOwnerTrait;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class CreateFinancialInstitutionAccountJob
{
    use AddsAccountOwnerTrait;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var FinancialInstitution
     */
    private $institution;
    /**
     * @var bool
     */
    private $sendPasswordSetupLink = false;
    /**
     * @var bool
     */
    private $isNewAccountOwner = false;

    /**
     * @var TokenRepositoryInterface
     */
    private $tokens;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param FinancialInstitution $institution
     */
    public function __construct(Request $request, FinancialInstitution $institution = null)
    {
        $this->tokens = Password::getRepository();
        $this->request = $request;
        $this->institution = $institution ?? new FinancialInstitution();
        $this->isNewAccountOwner = !$this->institution->accountOwner;
        // Generate and send password instructions if explicitly requested or if new account is created
        $this->sendPasswordSetupLink = $this->request->has('generate_password') || $this->isNewAccountOwner;
    }

    /**
     * Execute the job.
     * Broker is never passed in explicitly. The IOC injects it for us when the handle method is called
     *
     * @return FinancialInstitution
     */
    public function handle()
    {
        return $this->addInstitution();
    }

    /**
     * @return FinancialInstitution
     */
    private function addInstitution(): FinancialInstitution
    {
        return DB::transaction(function() {
            dispatch(new AddFinancialInstitutionJob($this->request, $this->institution));

            // Add the account owner
            $owner = $this->createAccountOwner($this->request, $this->institution);

            // Add the institution's brand style
            $this->addBrandStyle();

            // Send password setup instructions to the account owner
            $this->sendAccountOwnerPasswordSetupInstructions($owner);

            // Create default roles for the institution
            dispatch(new CreateDefaultOrganizationRolesJob($this->request, $this->institution));

            return $this->institution->load(['accountOwner', 'dashboardBranding']);
        });
    }

    /**
     * Add the institution's brand style
     * @return bool
     */
    private function addBrandStyle(): bool
    {
        if ($this->request->has('style')) {
            $brandStyle = $this->institution->dashboardBranding ?? new BrandStyle([
                    'institutable_id' => $this->institution->id,
                    'institutable_type' => $this->institution->getMorphClass()
                ]);

            return dispatch(new AddBrandStyleJob($this->request, $brandStyle));
        }

        return false;
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

        $token = $this->tokens->create($accountOwner);
        
        if ($this->isNewAccountOwner) {
            $accountOwner->notify(new AccountOwnerCreated($token));
        }

        $accountOwner->notify(new AccountOwnerCreated($token));
    }
}
