<?php

use App\Entities\Permission;
use App\Entities\User;
use App\Jobs\GetPermissionsForInstitutionJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class GetPermissionsForInstitutionJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    public function test_can_get_app_owner_permissions()
    {
        $user = $this->createApplicationOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $permissions = dispatch(new GetPermissionsForInstitutionJob($req));
        $expected = Permission::getGroupedAppOwnerPermissions();

        $this->assertTrue($expected->count() > 0);
        $this->assertCount($expected->count(), $permissions);

        $expected->each(function($collection, $key) use ($permissions) {
            $this->assertTrue($permissions->has($key));

            $this->assertTrue($collection->count() > 0);
            $this->assertCount($collection->count(), $permissions[$key]);

            $collection->each(function(Permission $perm, $i) use ($key, $permissions) {
                $this->assertEquals($perm->name, $permissions[$key][$i]->name);
            });
        });
    }

    public function test_can_get_employer_permissions()
    {
        $user = $this->createEmployerAccountOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $permissions = dispatch(new GetPermissionsForInstitutionJob($req));
        $expected = Permission::getGroupedEmployerPermissions();

        $this->assertTrue($expected->count() > 0);
        $this->assertCount($expected->count(), $permissions);

        $expected->each(function($collection, $key) use ($permissions) {
            $this->assertTrue($permissions->has($key));

            $this->assertTrue($collection->count() > 0);
            $this->assertCount($collection->count(), $permissions[$key]);

            $collection->each(function(Permission $perm, $i) use ($key, $permissions) {
                $this->assertEquals($perm->name, $permissions[$key][$i]->name);
            });
        });
    }

    public function test_can_get_partner_permissions()
    {
        $user = $this->createInstitutionAccountOwner();
        $req = $this->getAuthenticatedRequest([], $user);

        $permissions = dispatch(new GetPermissionsForInstitutionJob($req));
        $expected = Permission::getGroupedPartnerPermissions();

        $this->assertTrue($expected->count() > 0);
        $this->assertCount($expected->count(), $permissions);

        $expected->each(function($collection, $key) use ($permissions) {
            $this->assertTrue($permissions->has($key));

            $this->assertTrue($collection->count() > 0);
            $this->assertCount($collection->count(), $permissions[$key]);

            $collection->each(function(Permission $perm, $i) use ($key, $permissions) {
                $this->assertEquals($perm->name, $permissions[$key][$i]->name);
            });
        });
    }
}
