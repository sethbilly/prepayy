<?php

use App\Entities\BrandStyle;
use App\Entities\FinancialInstitution;
use App\Entities\User;
use App\Jobs\CreateFinancialInstitutionAccountJob;
use App\Notifications\AccountOwnerCreated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateFinancialInstitutionAccountJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

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

        $this->user = factory(User::class, 'appOwner')->create();
        $this->faker = Faker\Factory::create();
    }

    private function getRequest(): Request
    {
        return $this->getAuthenticatedRequest([
            'name' =>  $this->faker->company,
            'abbr' => $this->faker->languageCode,
            'code' => $this->faker->citySuffix,
            'address' => $this->faker->address,
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'owner' => [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'email' => $this->faker->email,
                'password' => $this->faker->password(8),
                'contact_number' => $this->faker->phoneNumber
            ],
            'style' => $this->faker->sentence
        ], $this->user);
    }

    public function test_can_add_financial_institution()
    {
        $req = $this->getRequest();

        $institution = dispatch(new CreateFinancialInstitutionAccountJob($req));

        $this->assertInstanceOf(FinancialInstitution::class, $institution);
        $this->assertEquals($req->get('name'), $institution->name);
        $this->assertEquals($req->get('abbr'), $institution->abbr);
        $this->assertEquals($req->get('email'), $institution->email);
        $this->assertEquals($req->get('contact_number'), $institution->contact_number);
        
        $this->assertInstanceOf(User::class, $institution->accountOwner);
        $this->assertEquals($req->input('owner.firstname'), $institution->accountOwner->firstname);
        $this->assertEquals($req->input('owner.email'), $institution->accountOwner->email);
        $this->assertEquals($req->input('owner.lastname'), $institution->accountOwner->lastname);

        $this->assertInstanceOf(BrandStyle::class, $institution->dashboardBranding);
        $this->assertEquals($req->get('style'), $institution->dashboardBranding->style);

        $this->assertTrue($institution->roles->count() > 0);
    }

    public function test_can_update_financial_institution()
    {
        $institution = factory(FinancialInstitution::class)->create();
        $req = $this->getRequest();

        dispatch(new CreateFinancialInstitutionAccountJob($req, $institution));

        $this->assertInstanceOf(FinancialInstitution::class, $institution);
        $this->assertEquals($req->get('name'), $institution->name);
        $this->assertEquals($req->get('abbr'), $institution->abbr);
        $this->assertEquals($req->get('email'), $institution->email);
        $this->assertEquals($req->get('contact_number'), $institution->contact_number);

        $this->assertInstanceOf(User::class, $institution->accountOwner);
        $this->assertEquals($req->input('owner.firstname'), $institution->accountOwner->firstname);
        $this->assertEquals($req->input('owner.email'), $institution->accountOwner->email);
        $this->assertEquals($req->input('owner.lastname'), $institution->accountOwner->lastname);

        $this->assertInstanceOf(BrandStyle::class, $institution->dashboardBranding);
        $this->assertEquals($req->get('style'), $institution->dashboardBranding->style);
    }

    public function test_sends_account_created_notification()
    {
        $owner = $this->createInstitutionAccountOwner();
        $req = $this->getRequest();
        $req->merge(['generate_password' => true]);

        $this->expectsNotification($owner, AccountOwnerCreated::class);

        dispatch(new CreateFinancialInstitutionAccountJob($req, $owner->institutable));
    }
}
