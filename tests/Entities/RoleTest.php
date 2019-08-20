<?php

use App\Entities\FinancialInstitution;
use App\Entities\Permission;
use App\Entities\Role;

class RoleTest extends TestCase
{
    /**
     * @var Role
     */
    private $role;

    public function setUp()
    {
        parent::setUp();
        $this->role = factory(Role::class)->create();
    }

    public function test_can_get_institutable()
    {
        $this->assertNull($this->role->institutable);

        $partner = factory(FinancialInstitution::class)->create();
        $this->role->update([
            'institutable_id' => $partner->id,
            'institutable_type' => $partner->getMorphClass()
        ]);

        $this->role = $this->role->fresh();

        $this->assertInstanceOf(FinancialInstitution::class, $this->role->institutable);
        $this->assertEquals($partner->id, $this->role->institutable->id);
    }

    public function test_slugifies_role_name()
    {
        $name = 'Name of role';
        $this->role->name = $name;

        $this->assertEquals(str_slug($name), $this->role->name);
    }

    public function test_can_get_route_key_name()
    {
        $this->assertEquals('name', $this->role->getRouteKeyName());
    }

    public function test_returns_true_if_has_loan_approval_permission()
    {
        $permission = Permission::where('name', 'approve-loan-application')->first();
        $this->role->attachPermission($permission);

        $this->assertTrue($this->role->canApproveLoans());
    }

    public function test_returns_false_if_does_not_have_loan_approval_permission()
    {
        $permission = Permission::where('name', '<>', 'approve-loan-application')
            ->first();
        $this->role->attachPermission($permission);

        $this->assertFalse($this->role->canApproveLoans());
    }
}
