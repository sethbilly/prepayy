<?php

use App\Entities\Employer;
use App\Entities\User;
use App\Jobs\AddEmployerJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class AddEmployerJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var Faker\Generator
     */
    private $faker;

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
        $this->user = $this->createApplicationOwner();
    }

    public function test_can_add_employer()
    {
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'address' => $this->faker->address
        ], $this->user);

        $rec = dispatch(new AddEmployerJob($req));

        $this->assertInstanceOf(Employer::class, $rec);
        $this->assertEquals($req->get('name'), $rec->name);
        $this->assertEquals($req->get('address'), $rec->address);
        $this->assertInstanceOf(User::class, $rec->creator);
        $this->assertEquals($req->user()->id, $rec->creator->id);
    }

    public function test_can_update_institution()
    {
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'address' => $this->faker->address
        ], $this->user);
        $emp = factory(Employer::class)->create();
        $this->assertNotNull($emp->user_id);

        $rec = dispatch(new AddEmployerJob($req, $emp));

        $this->assertInstanceOf(Employer::class, $rec);
        $this->assertEquals($req->get('name'), $rec->name);
        $this->assertEquals($req->get('address'), $rec->address);
        $this->assertInstanceOf(User::class, $rec->creator);
        // Update should not alter the creator of the employer account
        $this->assertNotEquals($req->user()->id, $rec->creator->id);
        $this->assertEquals($emp->creator->id, $rec->creator->id);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     * @expectedExceptionCode 422
     */
    public function test_throws_error_if_adding_duplication_institution()
    {
        $rec = factory(Employer::class)->create();
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'address' => $this->faker->address
        ], $this->user);
        $req->merge(['name' => $rec->name]);

        dispatch(new AddEmployerJob($req));
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     * @expectedExceptionCode 422
     */
    public function test_throws_error_if_update_conflicts_with_existing_institution()
    {
        $emps = factory(Employer::class, 2)->create();
        $req = $this->getAuthenticatedRequest([
            'name' => $this->faker->company,
            'address' => $this->faker->address
        ], $this->user);
        $req->merge(['name' => $emps->first()->name]);

        dispatch(new AddEmployerJob($req, $emps->last()));
    }
}
