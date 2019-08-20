<?php

namespace App\Jobs;

use App\Entities\Permission;
use App\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetPermissionsForInstitutionJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = $request->user();
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        return $this->getPermissions();
    }

    /**
     * @return Collection
     */
    private function getPermissions(): Collection
    {
        if ($this->user->isFinancialInstitutionStaff()) {
            return Permission::getGroupedPartnerPermissions();
        } else if ($this->user->isApplicationOwner()) {
            return Permission::getGroupedAppOwnerPermissions();
        } else if ($this->user->isEmployerStaff()) {
            return Permission::getGroupedEmployerPermissions();
        }

        return collect([]);
    }
}
