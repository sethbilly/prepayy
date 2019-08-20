<?php

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\Role;
use App\Jobs\CreateDefaultOrganizationRolesJob;
use Illuminate\Http\Request;

class CreateDefaultOrganizationRolesJobTest extends TestCase
{
    use CreatesUsersTrait;

    public function test_can_add_partner_default_roles()
    {
        $user = $this->createInstitutionAccountOwner();
        $partner = $user->institutable;

        $req = $this->getAuthenticatedRequest([], $user);
        
        $roles = dispatch(new CreateDefaultOrganizationRolesJob($req, $partner));

        $this->assertTrue($roles->count() > 0);
        $this->assertCount($roles->count(), $partner->roles);
        $this->assertEquals('User Accounts Manager', $partner->roles[0]->display_name);
        $this->assertEquals('Roles Manager', $partner->roles[1]->display_name);
        $this->assertEquals('Loans Approver', $partner->roles[2]->display_name);
    }

    public function test_can_add_employer_default_roles()
    {
        $user = $this->createEmployerAccountOwner();
        $employer = $user->institutable;

        $req = $this->getAuthenticatedRequest([], $user);


        $roles = dispatch(new CreateDefaultOrganizationRolesJob($req, $employer));

        $this->assertTrue($roles->count() > 0);
        $this->assertCount($roles->count(), $employer->roles);
        $this->assertEquals('User Accounts Manager', $employer->roles[0]->display_name);
        $this->assertEquals('Roles Manager', $employer->roles[1]->display_name);
        $this->assertEquals('Loans Approver', $employer->roles[2]->display_name);
    }

    public function test_will_not_add_default_roles_if_roles_exist()
    {
        $user = $this->createInstitutionAccountOwner();
        $partner = $user->institutable;
        
        $req = $this->getAuthenticatedRequest([], $user);;
        
        factory(Role::class)->create([
            'institutable_id' => $partner->id,
            'institutable_type' => $partner->getMorphClass(),
        ]);

        $roles = dispatch(new CreateDefaultOrganizationRolesJob($req, $partner));

        $this->assertCount(0, $roles);
    }
}
