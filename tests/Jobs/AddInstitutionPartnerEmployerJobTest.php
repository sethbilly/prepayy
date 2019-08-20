<?php

use App\Entities\Employer;
use App\Entities\User;
use App\Jobs\AddInstitutionPartnerEmployerJob;
use Illuminate\Http\Request;

class AddInstitutionPartnerEmployerJobTest extends TestCase
{
    use CreatesUsersTrait;

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createInstitutionAccountOwner();
    }

    private function getRequest(): Request
    {
        $req = new Request([
            'employer_id' => factory(Employer::class)->create()->id
        ]);

        $req->setUserResolver(function() { return $this->user; });

        return $req;
    }

    public function test_can_add_partner_employer()
    {
        $req = $this->getRequest();

        dispatch(new AddInstitutionPartnerEmployerJob($req));

        $this->assertCount(1, $this->user->institutable->partnerEmployers);
        $this->assertEquals($req->get('employer_id'), $this->user->institutable->partnerEmployers->first()->id);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_will_not_readd_partner_employer()
    {
        $req = $this->getRequest();

        $this->user->institutable->partnerEmployers()->attach($req->get('employer_id'));

        dispatch(new AddInstitutionPartnerEmployerJob($req));
    }
}
