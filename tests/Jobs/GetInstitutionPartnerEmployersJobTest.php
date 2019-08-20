<?php

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\User;
use App\Jobs\GetInstitutionPartnerEmployersJob;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetInstitutionPartnerEmployersJobTest extends TestCase
{
    use CreatesUsersTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var FinancialInstitution
     */
    private $institution;

    /**
     * @var Collection
     */
    private $employers;

    public function setUp()
    {
        parent::setUp();
        $this->user = $this->createInstitutionAccountOwner();
        $this->institution = $this->user->institutable;
        $this->employers = factory(Employer::class, 3)->create();

        $this->institution->partnerEmployers()->sync($this->employers->pluck('id')->all());
    }

    private function getRequest(): Request
    {
        $req = new Request([]);

        $req->setUserResolver(function () {
            return $this->user;
        });

        return $req;
    }

    public function test_can_get_partner_employers()
    {
        $results = dispatch(new GetInstitutionPartnerEmployersJob($this->getRequest()));

        $this->assertCount(3, $results);

        $this->employers
            ->sortBy('name')
            ->values()
            ->each(function (Employer $employer, $i) use ($results) {
                $this->assertEquals($employer->id, $results->items()[$i]->id);
            });
    }

    public function test_can_search_for_partner_employer()
    {
        $req = $this->getRequest();
        $req->merge(['search' => $this->employers->first()->name]);

        $results = dispatch(new GetInstitutionPartnerEmployersJob($req));

        $this->assertCount(1, $results);
        $this->assertEquals($this->employers->first()->id, $results->items()[0]->id);
    }
}
