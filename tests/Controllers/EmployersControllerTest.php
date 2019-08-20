<?php

use App\Entities\Employer;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EmployersControllerTest extends TestCase
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

    public function test_can_add_employer()
    {
        $company = $this->faker->company;
        $ownerEmail = $this->faker->unique()->safeEmail;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.employers.create')
            ->type($company, 'name')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type($ownerEmail, 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->check('generate_password')
            ->press('Save changes')
            ->seeInDatabase('employers', ['name' => $company])
            ->seeInDatabase('users', ['is_account_owner' => 1, 'email' => $ownerEmail])
            ->seeRouteIs('callens.employers.index');
    }

    public function test_throws_validation_error_for_invalid_requests()
    {
        $this->actingAs($this->appOwner)
            ->visitRoute('callens.employers.create')
            ->type('', 'name')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type('ben manford', 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->check('generate_password')
            ->press('Save changes')
            ->seeRouteIs('callens.employers.create')
            // Assert that the name field error is displayed
            ->seeText('The name field is required')
            ->seeText('The owner.email must be a valid email address');
    }

    public function test_can_populate_employer_to_update()
    {
        $owner = $this->createEmployerAccountOwner();
        $employer = $owner->institutable;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.employers.edit', ['employer' => $employer])
            ->seeInField('name', $employer->name)
            ->seeInField('address', $employer->address)
            ->seeInField('owner[firstname]', $owner->firstname)
            ->seeInField('owner[lastname]', $owner->lastname)
            ->seeInField('owner[email]', $owner->email)
            ->seeInField('owner[contact_number]', $owner->contact_number);
    }

    public function test_can_update_employer()
    {
        $employer = factory(Employer::class)->create();

        $company = $this->faker->company;
        $ownerEmail = $this->faker->unique()->safeEmail;

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.employers.edit', ['partner' => $employer])
            ->type($company, 'name')
            ->type($this->faker->address, 'address')
            ->type($this->faker->firstName, 'owner[firstname]')
            ->type($this->faker->lastName, 'owner[lastname]')
            ->type($ownerEmail, 'owner[email]')
            ->type($this->faker->phoneNumber, 'owner[contact_number]')
            ->check('generate_password')
            ->press('Save changes')
            ->seeRouteIs('callens.employers.index');
    }

    public function test_can_get_employers()
    {
        $employers = factory(Employer::class, 2)
            ->create()
            ->each(function(Employer $employer) {
                $this->createEmployerAccountOwner($employer);
            });

        $this->actingAs($this->appOwner)
            ->visitRoute('callens.employers.index')
            ->seeText($employers[0]->name)
            ->seeText($employers[0]->accountOwner->getFullName())
            ->seeText($employers[0]->accountOwner->contact_number)
            ->seeText($employers[1]->name)
            ->seeText($employers[1]->accountOwner->getFullName())
            ->seeText($employers[1]->accountOwner->contact_number);
    }
}
