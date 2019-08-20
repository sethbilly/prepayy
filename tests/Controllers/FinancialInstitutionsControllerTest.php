<?php

use App\Entities\BrandStyle;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FinancialInstitutionsControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var User
     */
    private $appOwner;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->appOwner = $this->createApplicationOwner();
        $this->faker = Faker\Factory::create();
    }

    public function test_can_add_new_institution()
    {
        $company = $this->faker->company;
        $ownerEmail = $this->faker->unique()->safeEmail;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.partners.create')
            ->type($company, 'name')
            ->type($this->faker->languageCode, 'abbr')
            ->type($this->faker->countryCode, 'code')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type($ownerEmail, 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->type($this->faker->sentence, 'style')
            ->check('generate_password')
            ->press('Save changes')
            ->seeInDatabase('financial_institutions', ['name' => $company])
            ->seeInDatabase('users', ['is_account_owner' => 1, 'email' => $ownerEmail])
            ->seeRouteIs('callens.partners.index');
    }

    public function test_will_throw_validation_errors_for_invalid_requests()
    {
        $ownerEmail = $this->faker->unique()->safeEmail;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.partners.create')
            ->type('', 'name')
            ->type($this->faker->languageCode, 'abbr')
            ->type($this->faker->countryCode, 'code')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type($ownerEmail, 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->type($this->faker->sentence, 'style')
            ->check('generate_password')
            ->press('Save changes')
            ->seeRouteIs('callens.partners.create')
            // Assert that the name field error is displayed
            ->seeText('The name field is required');
    }

    public function test_will_populate_institution_data_to_update()
    {
        $owner = $this->createInstitutionAccountOwner();
        $institution = $owner->institutable;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.partners.edit', ['partner' => $institution])
            ->seeText('Partner Details')
            ->seeInField('name', $institution->name)
            ->seeInField('abbr', $institution->abbr)
            ->seeInField('address', $institution->address)
            ->seeInField('code', $institution->code)
            ->seeInField('style', BrandStyle::getDefaultStyle()->style)
            ->seeInField('owner[firstname]', $owner->firstname)
            ->seeInField('owner[lastname]', $owner->lastname)
            ->seeInField('owner[email]', $owner->email)
            ->seeInField('owner[contact_number]', $owner->contact_number);
    }

    public function test_can_update_institution()
    {
        $institution = factory(FinancialInstitution::class)->create();

        $company = $this->faker->company;
        $ownerEmail = $this->faker->unique()->safeEmail;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.partners.edit', ['partner' => $institution])
            ->type($company, 'name')
            ->type($this->faker->languageCode, 'abbr')
            ->type($this->faker->countryCode, 'code')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type($ownerEmail, 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->type($this->faker->sentence, 'style')
            ->check('generate_password')
            ->press('Save changes')
            ->seeRouteIs('callens.partners.index');
    }

    public function test_can_get_institutions()
    {
        $institutions = factory(FinancialInstitution::class, 2)
            ->create()
            ->each(function(FinancialInstitution $institution) {
                $this->createInstitutionAccountOwner($institution);
            });

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.partners.index')
            ->seeText($institutions[0]->name)
            ->seeText($institutions[0]->accountOwner->contact_number)
            ->seeText($institutions[1]->name)
            ->seeText($institutions[1]->accountOwner->contact_number);
    }

    public function test_can_get_institution_partner_employers()
    {
        $user = $this->createInstitutionAccountOwner();

        $emps = factory(Employer::class, 3)->create();
        $user->institutable->partnerEmployers()->sync($emps->pluck('id')->all());

        $this->actingAs($user)
            ->visitRoute('partner.employers.index')
            ->seeText($emps[0]->name)
            ->seeText($emps[1]->name)
            ->seeText($emps[2]->name)
            ->seeElement('button[name="delete-employer-button-0"]')
            ->seeElement('button[name="delete-employer-button-1"]')
            ->seeElement('button[name="delete-employer-button-2"]');
    }

    public function test_will_display_no_partner_employers_message()
    {
        $user = $this->createInstitutionAccountOwner();

        $this->actingAs($user)
            ->visitRoute('partner.employers.index')
            ->see('Add employers you are partnering with');
    }

    public function test_can_delete_institution_partner_employers()
    {
        $user = $this->createInstitutionAccountOwner();

        $emp = factory(Employer::class)->create();
        $user->institutable->partnerEmployers()->attach($emp->id);

        $this->actingAs($user)
            ->visitRoute('partner.employers.index')
            ->press('Delete')
            ->seeRouteIs('partner.employers.index')
            ->see('Partner employer was successfully deleted');
    }

    public function test_can_add_institution_partner_employer()
    {
        $user = $this->createInstitutionAccountOwner();

        $emps = factory(Employer::class, 3)->create();

        $this->actingAs($user)
            ->visitRoute('partner.employers.index')
            ->select($emps[0]->id, 'employer_id')
            ->press('Save changes')
            ->seeRouteIs('partner.employers.index')
            ->see('Partner employer was successfully added');

        $this->assertCount(1, $user->institutable->partnerEmployers);
        $this->assertEquals($emps[0]->id, $user->institutable->partnerEmployers[0]->id);
    }
}
