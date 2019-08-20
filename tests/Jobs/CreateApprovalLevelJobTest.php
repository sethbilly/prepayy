<?php

use App\Entities\ApprovalLevel;
use App\Entities\User;
use App\Jobs\CreateApprovalLevelJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateApprovalLevelJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
    }

    private function getRequest(User $user): Request
    {
        return $this->getAuthenticatedRequest([
            'name' => $this->faker->name
        ], $user);
    }

    public function test_can_add_approval_level()
    {
        $user = $this->createInstitutionAccountOwner();
        $req = $this->getRequest($user);

        $approvalLevel = dispatch(new CreateApprovalLevelJob($req));

        $this->assertInstanceOf(ApprovalLevel::class, $approvalLevel);
        $this->assertEquals($req->get('name'), $approvalLevel->name);
        $this->assertEquals(str_slug($req->get('name')), $approvalLevel->slug);
        $this->assertEquals($user->institutable->id, $approvalLevel->institutable->id);
        $this->assertEquals($user->institutable->type, $approvalLevel->institutable->type);
    }

    public function test_can_update_approval_level()
    {
        $user = $this->createEmployerAccountOwner();
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $req = $this->getRequest($user);

        dispatch(new CreateApprovalLevelJob($req, $level));

        $this->assertEquals($req->get('name'), $level->name);
        $this->assertEquals($user->institutable->id, $level->institutable->id);
        $this->assertEquals($user->institutable->type, $level->institutable->type);
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_add_duplicate_approval_level()
    {
        $user = $this->createEmployerAccountOwner();
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $req = $this->getRequest($user);
        $req->merge(['name' => $level->name]);

        dispatch(new CreateApprovalLevelJob($req));
    }

    /**
     * @expectedExceptionCode 422
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_update_to_existing_approval_level()
    {
        $user = $this->createEmployerAccountOwner();
        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);
        $req = $this->getRequest($user);
        $req->merge(['name' => $levels->first()->name]);

        dispatch(new CreateApprovalLevelJob($req, $levels->last()));
    }
}
