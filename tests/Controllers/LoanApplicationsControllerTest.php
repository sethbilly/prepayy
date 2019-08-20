<?php

use App\Entities\Country;
use App\Entities\Employer;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use App\Http\Controllers\LoanApplicationsController;
use App\Jobs\SubmitLoanApplicationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LoanApplicationsControllerTest extends TestCase
{
    use CreatesUsersTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->faker = Faker\Factory::create();
    }

    public function test_will_redirect_if_not_eligible_for_loan()
    {
        $product = factory(LoanProduct::class)->create();
        $employers = factory(Employer::class, 2)->create();

        $this->actingAs($this->user)
            ->visitRoute('loan_products.browse')
            ->seeLink('Apply', route('loan_applications.guidelines', [
                'partner' => $product->institution,
                'product' => $product,
                'amount' => $product->min_amount,
                'tenure' => 1
            ]))
            ->click('Apply')
            ->seePageIs(route('loan_applications.guidelines', [
                'partner' => $product->institution,
                'product' => $product,
                'amount' => $product->min_amount,
                'tenure' => 1
            ]))
            ->seeText('Welcome, ' . $this->user->getFullName())
            ->seeText('Verify Eligibility for the Loan')
            ->seeText('Complete the Loan Application Form')
            ->seeText('Request Employer’s Approval')
            ->seeText('Track Progress')
            ->seeElement('button[type="submit"]')
            ->press('Continue')
            ->seePageIs(route('loan_applications.eligibility', [
                'partner' => $product->institution,
                'product' => $product,
                'amount' => $product->min_amount,
                'tenure' => 1
            ]))
            ->seeInElement('select[name="employer_id"]', $employers[0]->name)
            ->seeInElement('select[name="employer_id"]', $employers[1]->name)
            ->select($employers[0]->id, 'employer_id')
            ->press('Check availability')
            ->seePageIs(route('loan_applications.index'));
    }

    public function test_cannot_access_loan_form_if_current_employer_is_not_set()
    {
        $product = factory(LoanProduct::class)->create();

        $this->actingAs($this->user)
            ->get(route('loan_applications.apply',
                ['partner' => $product->institution, 'product' => $product]))
            ->assertResponseStatus(404)
            ->seeText('Page Not Found');
    }

    public function test_cannot_access_loan_form_if_current_employer_is_not_eligible()
    {
        $product = factory(LoanProduct::class)->create();
        $employer = factory(Employer::class)->create();

        session()->put(LoanApplicationsController::CURRENT_EMPLOYER, $employer->id);

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.apply',
                ['partner' => $product->institution, 'product' => $product])
            ->seePageIs(route('loan_applications.index'));
    }

    public function test_can_access_loan_form_for_eligible_loans()
    {
        $product = factory(LoanProduct::class)->create();
        $employer = factory(Employer::class)->create();

        session()->put(LoanApplicationsController::CURRENT_EMPLOYER, $employer->id);

        // Indicate partnership between employer and the institution
        $product->institution->partnerEmployers()->attach($employer->id);

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.apply', [
                'partner' => $product->institution,
                'product' => $product,
                'amount' => $product->min_amount,
                'tenure' => 1
            ])
            ->seeInField('amount', $product->min_amount)
            ->seeInField('tenure', 1)
            ->seeInField('user[firstname]', $this->user->firstname)
            ->seeInField('user[lastname]', $this->user->lastname)
            ->seeInField('user[othernames]', $this->user->othernames)
            ->seeInField('user[contact_number]', $this->user->contact_number)
            ->seeInField('user[ssnit]', $this->user->ssnit)
            ->seeElement('select[name="employer[id]"]')
            ->seeElement('select[name="employer[contract_type]"]')
            ->seeElement('input[name="employer[position]"]')
            ->seeElement('input[name="employer[department]"]')
            ->seeElement('select[name="id_card[type]"]')
            ->seeElement('input[name="id_card[issue_date]"]')
            ->seeElement('input[name="id_card[expiry_date]"]')
            ->seeElement('input[name="id_card[number]"]')
            ->seeElement('input[name="guarantor[name]"]')
            ->seeElement('input[name="guarantor[relationship]"]')
            ->seeElement('input[name="guarantor[years_known]"]')
            ->seeElement('input[name="guarantor[contact_number]"]')
            ->seeElement('input[name="guarantor[employer]"]')
            ->seeElement('input[name="guarantor[position]"]')
            ->seeElement('button[type="submit"]')
            ->seeElement('button[value="1"]');
    }

    public function test_can_save_loan_application_draft()
    {
        $product = factory(LoanProduct::class)->create();
        $employer = factory(Employer::class)->create();

        session()->put(LoanApplicationsController::CURRENT_EMPLOYER, $employer->id);

        // Indicate partnership between employer and the institution
        $product->institution->partnerEmployers()->attach($employer->id);

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.apply',
                ['partner' => $product->institution, 'product' => $product])
            ->press('Save draft')
            ->seePageIs(route('loan_applications.index'))
            ->seeText('Your loan application has been saved. To resume the application, click the edit application link');
    }

    public function test_is_required_to_complete_loan_form_before_submission()
    {
        $product = factory(LoanProduct::class)->create();
        $employer = factory(Employer::class)->create();

        session()->put(LoanApplicationsController::CURRENT_EMPLOYER, $employer->id);

        // Indicate partnership between employer and the institution
        $product->institution->partnerEmployers()->attach($employer->id);

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.apply',
                ['partner' => $product->institution, 'product' => $product])
            ->press('Request employers approval')
            ->seePageIs(route('loan_applications.apply',
                ['partner' => $product->institution, 'product' => $product]))
            ->seeText('The employer.position field is required');
    }

    public function test_can_submit_loan_application_for_employer_approval()
    {
        $product = factory(LoanProduct::class)->create();
        $employers = factory(Employer::class, 2)->create();
        $product->institution->partnerEmployers()->attach($employers[0]->id);

        session()->put(LoanApplicationsController::CURRENT_EMPLOYER, $employers[0]->id);

        $this->user->dob = Carbon::now()->subYears(15);
        $this->user->country()->associate(Country::first());

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.apply', [
                'partner' => $product->institution,
                'product' => $product,
                'amount' => $product->min_amount,
                'tenure' => 2
            ])
            ->type($this->faker->creditCardNumber, 'user[ssnit]')
            ->select($employers[0]->id, 'employer[id]')
            ->select('Full Time', 'employer[contract_type]')
            ->type($this->faker->jobTitle, 'employer[position]')
            ->type($this->faker->text, 'employer[department]')
            ->type($this->faker->numberBetween(2000, 4000), 'employer[salary]')
            ->select('Voters ID', 'id_card[type]')
            ->type($this->faker->creditCardNumber, 'id_card[number]')
            ->type(Carbon::now()->format('d-m-Y'), 'id_card[issue_date]')
            ->type(Carbon::now()->format('d-m-Y'), 'id_card[expiry_date]')
            ->type($this->faker->name, 'guarantor[name]')
            ->type('Colleague', 'guarantor[relationship]')
            ->type(2, 'guarantor[years_known]')
            ->type($this->faker->phoneNumber, 'guarantor[contact_number]')
            ->type($this->faker->company, 'guarantor[employer]')
            ->type($this->faker->jobTitle, 'guarantor[position]')
            ->press('Request employers approval')
            ->seePageIs(route('loan_applications.index'))
            ->seeText(sprintf(
                'Your loan application has been submitted to %s for approval',
                $employers->first()->name
            ))
            // Assert the loan application is displayed
            ->seeText($product->name)
            ->seeText(number_format($product->min_amount, 2))
            ->seeText('at ' . number_format($product->interest_per_year, 2) . '%')
            ->seeText('for 2')
            ->seeText('years')
            ->seeText('Repay')
            ->seeText('for 24 months');
    }

    public function test_can_submit_application_for_partner_approval()
    {
        $application = factory(LoanApplication::class)->make(['tenure_in_years' => 2]);
        // Mark application as approved by employer
        $application->loanApplicationStatus()->associate(LoanApplicationStatus::getEmployerApprovedStatus());
        $application->save();

        // User must be an employee of the employer
        $user = $application->user;
        $user->dob = Carbon::now()->subYears(15);
        $user->country()->associate(Country::first());

        $product = $application->loanProduct;

        $token = str_random(8);

        // Mock the cache object's behavior
        Cache::shouldReceive('pull')->andReturn([
            'token' => $token,
            'application_id' => $application->id
        ]);
        Cache::shouldReceive('put')->andReturnNull();
        Cache::shouldReceive('forget')->andReturnNull();

        $this->actingAs($user)
            ->visitRoute('loan_applications.edit', ['application' => $application])
            ->press('Submit application')
            ->seePageIs(route('loan_applications.confirm_submission',
                ['application' => $application]))
            ->type($token, 'submission_token')
            ->press('Submit')
            ->seeText(sprintf(
                'Your application has been submitted to %s for approval',
                $application->loanProduct->institution->name
            ))
            // Assert the loan application is displayed
            ->seeText($product->name)
            ->seeText(number_format($application->amount, 2))
            ->seeText('at ' . number_format($product->interest_per_year, 2) . '%')
            ->seeText('for 2')
            ->seeText('Repay')
            ->seeText('for 24 months');
    }

    public function test_will_display_no_loan_applications()
    {
        $this->actingAs($this->user)
            ->visitRoute('loan_applications.index')
            ->seeText('You haven\'t applied for a loan yet')
            ->seeLink('Browse loans', route('loan_products.browse'));
    }

    public function test_can_update_existing_loan_application()
    {
        $application = factory(LoanApplication::class)->create();

        $this->user = $application->user;
        $employer = $this->user->employers()->where('id', $application->employer_id)->first();

        $this->actingAs($this->user)
            ->visitRoute('loan_applications.index')
            ->seeLink($application->loanProduct->name, route('loan_applications.edit', [
                'application' => $application
            ]))
            ->click($application->loanProduct->name)
            ->seePageIs(route('loan_applications.edit', ['application' => $application]))
            ->seeInField('user[firstname]', $this->user->firstname)
            ->seeInField('user[lastname]', $this->user->lastname)
            ->seeInField('user[othernames]', $this->user->othernames)
            ->seeInField('user[contact_number]', $this->user->contact_number)
            ->seeIsSelected('employer[id]', $employer->id)
            ->seeIsSelected('employer[contract_type]', $employer->pivot->contract_type)
            ->seeInField('employer[position]', $employer->pivot->position)
            ->seeInField('employer[department]', $employer->pivot->department)
            ->seeIsSelected('id_card[type]', $application->identificationCard->type)
            ->seeInField('id_card[number]', $application->identificationCard->number)
            ->seeInField('id_card[issue_date]',
                $application->identificationCard->issue_date->format('d-m-Y'))
            ->seeInField('id_card[expiry_date]',
                $application->identificationCard->expiry_date->format('d-m-Y'))
            ->press('Save draft')
            ->seePageIs(route('loan_applications.index'))
            ->seeText('Your loan application has been saved.');
    }

    public function test_can_view_loan_for_approval()
    {
        $user = $this->createEmployerAccountOwner();
        $application = factory(LoanApplication::class)->create();
        $application->user->update([
            'dob' => Carbon::now()->subYears(20)
        ]);

        $employer = $application->user->employers()->find($application->employer_id);

        $this->actingAs($user)
            ->visitRoute('loan_applications.show', ['application' => $application])
            ->assertViewHas('statusApprove',
                LoanApplicationStatus::getEmployerApprovedStatus())
            ->assertViewHas('statusDecline',
                LoanApplicationStatus::getEmployerDeclinedStatus())
            ->seeText($application->loanProduct->name)
            ->seeText(' by ' . $application->loanProduct->institution->name)
            ->seeText('GHS ' . number_format($application->amount, 2))
            ->seeText($application->loanApplicationStatus->status)
            ->seeText('Created by')
            ->seeText('on ' . $application->created_at->format('jS M, Y'))
            ->seeText('Basic Details')
            ->seeText($application->user->getFullName())
            ->seeText($application->user->contact_number)
            ->seeText($application->user->ssnit)
            ->seeText($application->user->dob->format('jS M, Y'))
            ->seeText('Employer Details')
            ->seeText($employer->name)
            ->seeText($employer->pivot->position)
            ->seeText($employer->pivot->contract_type)
            ->seeText('ID Details')
            ->seeText($application->identificationCard->type)
            ->seeText($application->identificationCard->number)
            ->seeText($application->identificationCard->issue_date->format('jS M, Y'))
            ->seeText($application->identificationCard->expiry_date->format('jS M, Y'))
            ->seeText('Guarantor Details')
            ->seeText($application->guarantor->name)
            ->seeText($application->guarantor->contact_number)
            ->seeText($application->guarantor->relationship)
            ->seeText($application->guarantor->years_known)
            ->seeText($application->guarantor->employer)
            ->seeText($application->guarantor->position);
    }

    public function test_can_approve_loan_application()
    {
        $application = factory(LoanApplication::class)->create();
        $application->loanApplicationStatus()->associate(
            LoanApplicationStatus::getEmployerPendingStatus()
        );
        $application->save();

        $role = factory(Role::class)->create();
        $role->attachPermission(Permission::where('name',
            'approve-loan-application')->first());

        $user = factory(User::class, 'employer')->create([
            'institutable_id' => $application->employer_id
        ]);
        $user->attachRole($role);

        $this->actingAs($user)
            ->visitRoute('loan_applications.show', ['application' => $application])
            ->assertViewHas('canApprove', true)
            ->seeText('Approve')
            ->seeText('Decline')
            ->makeRequest('put',
                route('loan_applications.approve', ['application' => $application]), [
                    'status_id' => LoanApplicationStatus::getEmployerApprovedStatus()->id
                ])
            ->seePageIs(route('loan_applications.show', ['application' => $application]))
            ->seeText('The loan application has been approved');
    }

    public function test_cannot_approve_loan_if_not_employer_or_partner_staff()
    {
        $application = factory(LoanApplication::class)->create();
        $application->loanApplicationStatus()->associate(
            LoanApplicationStatus::getEmployerPendingStatus()
        );
        $application->save();

        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->visitRoute('loan_applications.show', ['application' => $application])
            ->seeText('Approve')
            ->seeText('Decline')
            ->put(route('loan_applications.approve', ['application' => $application]), [
                    'status_id' => LoanApplicationStatus::getEmployerApprovedStatus()->id
                ])
            ->assertResponseStatus(403);
    }

    public function test_can_request_additional_documents()
    {
        // Create loan application and approve it
        // View application and request for document
        $application = factory(LoanApplication::class)->create();
        $application->loanApplicationStatus()->associate(
            LoanApplicationStatus::getPartnerPendingStatus()
        );
        $application->save();

        $user = factory(User::class, 'partner')->create();
        $user->institutable()->associate($application->loanProduct->institution);
        $user->save();

        $this->actingAs($user)
            ->visitRoute('loan_applications.show', ['application' => $application])
            ->seeText('Request for Additional Information')
            ->type($this->faker->sentence, 'request')
            ->press('Send')
            ->seePageIs(route('loan_applications.show', ['application' => $application]))
            ->seeText('Your request for additional documents has been sent');
    }

    public function test_cannot_request_documents_if_not_partner_or_employer_staff()
    {
        $application = factory(LoanApplication::class)->create();
        $application->loanApplicationStatus()->associate(
            LoanApplicationStatus::getPartnerPendingStatus()
        );
        $application->save();

        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->visitRoute('loan_applications.show', ['application' => $application])
            ->seeText('Request for Additional Information')
            ->type($this->faker->sentence, 'request')
            ->post(route('loan_applications.documents.request', [
                'application' => $application]))
            ->assertResponseStatus(403);
    }
}
