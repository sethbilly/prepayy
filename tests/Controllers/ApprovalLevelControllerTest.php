<?php

use App\Entities\ApprovalLevel;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApprovalLevelControllerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    public function getInstitutionTypes()
    {
        return [
            ['partner'],
            ['employer']
        ];
    }

    private function getUser(string $type): User
    {
        switch ($type) {
            case 'partner':
                return $this->createInstitutionAccountOwner();
            default:
                return $this->createEmployerAccountOwner();
        }
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_no_approval_levels(string $type)
    {
        $user = $this->getUser($type);

        $this->actingAs($user)
            ->visitRoute("approval_levels.index")
            ->seeText('List of Approval Levels')
            ->seeText('Add loan approval levels for your organization')
            ->seeLink('Add Approval Level')
            ->dontSeeElement('table');
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_can_get_approval_levels(string $type)
    {
        $user = $this->getUser($type);
        $levels = factory(ApprovalLevel::class, 2)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type,
        ]);

        $this->actingAs($user)
            ->visitRoute("approval_levels.index")
            ->seeText('Name')
            ->seeText('Level')
            ->seeLink('Add Approval Level')
            ->dontSeeText('List of Approval Levels')
            ->seeLink($levels[0]->name, route("approval_levels.edit", ['level' => $levels[0]]))
            ->seeLink($levels[1]->name, route("approval_levels.edit", ['level' => $levels[1]]))
            ->seeText('Level 1')
            ->seeText('Level 2');
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_can_create_approval_level(string $type)
    {
        $user = $this->getUser($type);

        $levelName = 'Approval Level One';

        $this->actingAs($user)
            ->visitRoute("approval_levels.create")
            ->seeInField('level', 'Level # 1')
            ->type($levelName, 'name')
            ->press('Save changes')
            ->seeInDatabase('approval_levels', [
                'name' => $levelName,
                'institutable_id' => $user->institutable_id,
                'institutable_type' => $user->institutable_type
            ])
            ->seeRouteIs("approval_levels.index");
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_populates_data_to_edit(string $type)
    {
        $user = $this->getUser($type);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $this->actingAs($user)
            ->visitRoute("approval_levels.edit", ['level' => $level])
            ->seeInField('name', $level->name)
            ->seeInField('level', 'Level # 1');
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_can_update_approval_level(string $type)
    {
        $user = $this->getUser($type);
        $level = factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $this->actingAs($user)
            ->visitRoute("approval_levels.edit", ['level' => $level])
            ->seeInField('name', $level->name)
            ->press('Save changes')
            ->assertResponseOk()
            ->seeRouteIs("approval_levels.index");
    }

    /**
     * @dataProvider getInstitutionTypes
     * @param string $type
     */
    public function test_can_delete_approval_level(string $type)
    {
        $user = $this->getUser($type);
        factory(ApprovalLevel::class)->create([
            'institutable_id' => $user->institutable_id,
            'institutable_type' => $user->institutable_type
        ]);

        $this->actingAs($user)
            ->visitRoute("approval_levels.index")
            ->press('delete-app-level-button-1')
            ->assertResponseOk()
            ->assertEquals(0, ApprovalLevel::count());
    }
}
