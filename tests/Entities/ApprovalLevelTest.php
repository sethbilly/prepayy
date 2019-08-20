<?php

use App\Entities\ApprovalLevel;
use App\Entities\FinancialInstitution;
use App\Entities\User;

class ApprovalLevelTest extends TestCase
{
    /**
     * @var ApprovalLevel
     */
    private $approvalLevel;

    public function setUp()
    {
        parent::setUp();
        $this->approvalLevel = factory(ApprovalLevel::class)->create();
    }

    public function test_can_get_institutable()
    {
        $this->assertNull($this->approvalLevel->institutable);

        $partner = factory(FinancialInstitution::class)->create();
        $this->approvalLevel->update([
            'institutable_id' => $partner->id,
            'institutable_type' => $partner->getMorphClass()
        ]);

        $this->approvalLevel = $this->approvalLevel->fresh();
        $this->assertInstanceOf(FinancialInstitution::class, $this->approvalLevel->institutable);
        $this->assertEquals($this->approvalLevel->institutable->id, $partner->id);
    }

    public function test_can_get_users_at_approval_level()
    {
        $this->assertCount(0, $this->approvalLevel->users);
        
        $users = factory(User::class, 2)->create(['approval_level_id' => $this->approvalLevel->id]);

        $this->approvalLevel = $this->approvalLevel->fresh();
        $this->assertCount(2, $this->approvalLevel->users);

        $users->each(function(User $user, $i) {
            $this->assertEquals($user->id, $this->approvalLevel->users[$i]->id);
        });
    }

    public function test_can_get_route_key_name()
    {
        $this->assertEquals('slug', $this->approvalLevel->getRouteKeyName());
    }

    public function test_can_slugifies_slug()
    {
        $name = 'New Approval Level';

        $this->approvalLevel->slug = $name;

        $this->assertEquals(str_slug($name), $this->approvalLevel->slug);
    }
}
