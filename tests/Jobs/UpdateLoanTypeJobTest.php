<?php

use App\Entities\LoanType;
use App\Entities\User;
use App\Jobs\UpdateLoanTypeJob;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\Request;

class UpdateLoanTypeJobTest extends TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var LoanType
     */
    private $loanType;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->loanType = factory(LoanType::class)->create();
    }

    private function getRequest(User $user, $overrides = [])
    {
        $request = new Request(array_merge([
            'name' => $this->faker->word
        ], $overrides));

        $request->setUserResolver(function () use ($user) { return $user; });

        return $request;
    }

    public function test_admin_can_update_loan_type()
    {
        $user = factory(User::class, 'appOwner')->create();
        $request = $this->getRequest($user);

        $loanType = dispatch(new UpdateLoanTypeJob($request, $this->loanType));

        $this->assertEquals($request->get('name'), $loanType->name);
    }

    public function test_partner_can_update_loan_type()
    {
        $user = factory(User::class, 'partner')->create();
        $request = $this->getRequest($user);

        $loanType = dispatch(new UpdateLoanTypeJob($request, $this->loanType));

        $this->assertEquals($request->get('name'), $loanType->name);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_will_throw_conflict_error_for_existing_loan_type()
    {
        $user = factory(User::class, 'appOwner')->create();
        $request = $this->getRequest($user);
        factory(LoanType::class)->create(['name' => $request->get('name')]);

        dispatch(new UpdateLoanTypeJob($request, $this->loanType));
    }
}
