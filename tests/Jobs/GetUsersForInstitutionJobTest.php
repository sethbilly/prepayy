<?php

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\User;
use App\Jobs\GetUsersForInstitutionJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetUsersForInstitutionJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var Collection
     */
    private $users;

    public function setUp()
    {
        parent::setUp();

        $partner = factory(FinancialInstitution::class)->create();
        $employer = factory(Employer::class)->create();

        // Create 6 users
        $users[] = $this->createApplicationOwner();
        $users[] = $this->createApplicationOwner();
        $users[] = $this->createEmployerAccountOwner($employer);
        $users[] = $this->createEmployerAccountOwner($employer);
        $users[] = $this->createInstitutionAccountOwner($partner);
        $users[] = $this->createInstitutionAccountOwner($partner);
        // Create borrower profile
        $users[] = factory(User::class)->create();

        $this->users = collect($users);

        // Delete all but the above created users (creating employers leads to creation of app owners)
        User::whereNotIn('id', $this->users->pluck('id')->all())->delete();
    }

    public function getUsersProvider()
    {
        return [
            // Keys to access the $this->users collection
            [0],
            [2],
            [4]
        ];
    }

    /**
     * @dataProvider getUsersProvider
     * @param int $key
     */
    public function test_can_get_users_for_institution(int $key)
    {
        $req = $this->getAuthenticatedRequest([], $this->users[$key]);

        $actualUsers = dispatch(new GetUsersForInstitutionJob($req));

        $this->assertCount(2, $actualUsers);

        $expectedUsers = $this->users->slice($key, 2)->values();

        $this->assertCount(2, $expectedUsers);

        $expectedUsers->each(function(User $user, $i) use ($actualUsers) {
            $this->assertEquals($user->id, $actualUsers->items()[$i]->id);
        });
    }

    /**
     * @dataProvider getUsersProvider
     * @param int $key
     */
    public function test_can_search_for_users_of_institution(int $key)
    {
        $expectedUser = $this->users[$key];
        $req = $this->getAuthenticatedRequest([], $expectedUser);
        $req->merge(['search' => $expectedUser->firstname]);

        $actualUsers = dispatch(new GetUsersForInstitutionJob($req));

        $this->assertCount(1, $actualUsers);
        $this->assertEquals($expectedUser->id, $actualUsers->items()[0]->id);
    }
}
