<?php

use App\Entities\FinancialInstitution;
use App\Jobs\AddFinancialInstitutionJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class AddFinancialInstitutionJobTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }
    
    public function test_can_add_institution()
    {
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'abbr' => $this->faker->companySuffix,
            'code' => $this->faker->languageCode,
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address
        ]);

        $institution = dispatch(new AddFinancialInstitutionJob($req));

        $this->assertInstanceOf(FinancialInstitution::class, $institution);
        $this->assertEquals($req->get('name'), $institution->name);
        $this->assertEquals($req->get('abbr'), $institution->abbr);
        $this->assertEquals($req->get('code'), $institution->code);
        $this->assertEquals($req->get('contact_number'), $institution->contact_number);
        $this->assertEquals($req->get('email'), $institution->email);
        $this->assertEquals($req->get('address'), $institution->address);
    }

    public function test_can_update_institution()
    {
        $institution = factory(FinancialInstitution::class)->create();
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'abbr' => $this->faker->companySuffix,
            'code' => $this->faker->languageCode,
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address
        ]);

        $institution = dispatch(new AddFinancialInstitutionJob($req, $institution));

        $this->assertInstanceOf(FinancialInstitution::class, $institution);
        $this->assertEquals($req->get('name'), $institution->name);
        $this->assertEquals($req->get('abbr'), $institution->abbr);
        $this->assertEquals($req->get('code'), $institution->code);
        $this->assertEquals($req->get('contact_number'), $institution->contact_number);
        $this->assertEquals($req->get('email'), $institution->email);
        $this->assertEquals($req->get('address'), $institution->address);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     * @expectedExceptionCode 422
     */
    public function test_throws_error_if_adding_duplication_institution()
    {
        $institution = factory(FinancialInstitution::class)->create();
        $req = $this->getAuthenticatedRequest([
            'name' => $institution->name,
            'abbr' => $this->faker->companySuffix,
            'code' => $this->faker->languageCode,
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address
        ]);

        dispatch(new AddFinancialInstitutionJob($req));
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     * @expectedExceptionCode 422
     */
    public function test_throws_error_if_update_conflicts_with_existing_institution()
    {
        $institutions = factory(FinancialInstitution::class, 2)->create();
        $req = $this->getAuthenticatedRequest([
            'name' => $institutions->first()->name,
            'abbr' => $this->faker->companySuffix,
            'code' => $this->faker->languageCode,
            'contact_number' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'address' => $this->faker->address
        ]);

        dispatch(new AddFinancialInstitutionJob($req, $institutions->last()));
    }
}
