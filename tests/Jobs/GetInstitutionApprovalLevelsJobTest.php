<?php

use App\Entities\ApprovalLevel;
use App\Entities\User;
use App\Jobs\GetInstitutionApprovalLevelsJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class GetInstitutionApprovalLevelsJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    private function getUser(string $type): User
    {
        switch ($type) {
            case 'partner':
                return $this->createInstitutionAccountOwner();
            default:
                return $this->createEmployerAccountOwner();
        }
    }
    public function approvalLevelsProvider()
    {
        return [
            ['partner'],
            ['employer']
        ];
    }

    /**
     * @dataProvider approvalLevelsProvider
     * @param string $type
     */
    public function test_can_get_approval_levels(string $type)
    {
        $user = $this->getUser($type);
        $req = $this->getAuthenticatedRequest([], $user);

        $levels = dispatch(new GetInstitutionApprovalLevelsJob($req));

        $this->assertCount(0, $levels);

        // Create 2 levels for the institution
        $approvalLevels = factory(ApprovalLevel::class, 3)
            ->create()
            ->filter(function($level, $i) {
                return $i < 2;
            })
            ->map(function (ApprovalLevel $level, $i) use ($user) {
                $level->update([
                    'institutable_id' => $user->institutable_id,
                    'institutable_type' => $user->institutable_type
                ]);
                return $level;
            });

        // Refresh the user to allow reload of its institutable relation
        $user = $user->fresh();
        $req->setUserResolver(function() use($user) {return $user;});

        $levels = dispatch(new GetInstitutionApprovalLevelsJob($req));

        $this->assertCount(2, $levels);

        $approvalLevels->each(function(ApprovalLevel $level, $i) use ($levels) {
            $this->assertEquals($level->id, $levels[$i]->id);
        });
    }
}
