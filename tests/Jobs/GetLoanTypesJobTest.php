<?php

use App\Entities\LoanType;
use App\Entities\User;
use App\Jobs\GetLoanTypesJob;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanTypesJobTest extends TestCase
{
    /**
     * @var User
     */
    private $admin;

    /**
     * @var User
     */
    private $partner;

    /**
     * @var Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->admin = factory(User::class, 'appOwner')->create();
        $this->partner = factory(User::class, 'partner')->create();

        factory(LoanType::class, 2)->make()
            ->each(function(LoanType $type, $i) {
                if ($i == 0) {
                    $type->user()->associate($this->partner);
                }
                $type->save();
            });
    }

    private function getRequest(User $user): Request
    {
        $request = new Request();

        $request->setUserResolver(function () use ($user) { return $user; });

        return $request;
    }

    public function test_can_get_all_loan_types_as_admin()
    {
        $req = $this->getRequest($this->admin);

        $loanTypes = dispatch(new GetLoanTypesJob($req));

        $this->assertCount(2, $loanTypes);
        $this->assertInstanceOf(Paginator::class, $loanTypes);
    }

    public function test_can_get_loan_types_created_by_partner()
    {
        $req = $this->getRequest($this->partner);

        $loanTypes = dispatch(new GetLoanTypesJob($req));

        $this->assertCount(1, $loanTypes);
        $this->assertInstanceOf(Paginator::class, $loanTypes);
    }
}
