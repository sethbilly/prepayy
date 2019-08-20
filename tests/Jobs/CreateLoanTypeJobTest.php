<?php

use App\Entities\LoanType;
use App\Entities\User;
use App\Jobs\CreateLoanTypeJob;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\Request;

class CreateLoanTypeJobTest extends TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    private function getRequest(User $user, $overrides = [])
    {
        $request = new Request(array_merge([
            'name' => $this->faker->word
        ], $overrides));
        
        $request->setUserResolver(function () use ($user) { return $user; });
        
        return $request;
    }

    public function test_admin_can_create_loan_type()
    {
        $user = factory(User::class, 'appOwner')->create();
        $request = $this->getRequest($user);

        $loanType = dispatch(new CreateLoanTypeJob($request));

        $this->assertInstanceOf(LoanType::class, $loanType);
    }

    public function test_partner_can_create_loan_type()
    {
        $user = factory(User::class, 'partner')->create();
        $request = $this->getRequest($user);

        $loanType = dispatch(new CreateLoanTypeJob($request));

        $this->assertInstanceOf(LoanType::class, $loanType);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_will_throw_conflict_error_for_existing_loan_type()
    {
        $user = factory(User::class, 'appOwner')->create();
        $request = $this->getRequest($user);
        factory(LoanType::class)->create(['name' => $request->get('name')]);

        dispatch(new CreateLoanTypeJob($request));
    }
}
