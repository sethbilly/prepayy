<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use App\Entities\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CreateDefaultOrganizationRolesJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Model
     */
    private $model;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Model $model
     */
    public function __construct(Request $request, Model $model)
    {
        $this->request = $request;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        return $this->createDefaultRoles();
    }

    private function createDefaultRoles(): Collection
    {
        $roles = collect($this->getRoles())
            ->filter(function(array $row) {
                return $this->model instanceof FinancialInstitution ? $row['is_partner'] : $row['is_employer'];
            });

        // Do not add default roles if roles have been setup
        return $this->model->roles()->count() > 0 ? collect([]) : $this->addRoles($roles);
    }

    /**
     * Add the list of roles
     * @param Collection $collection
     * @return Collection
     */
    private function addRoles(Collection $collection): Collection
    {
        return $collection->map(function(array $row) {
            $this->request->merge([
                'role' => $row['role'],
                'permissions' => $row['permissions']
            ]);

            $role = new Role([
                'institutable_id' => $this->model->id,
                'institutable_type' => $this->model->getMorphClass(),
            ]);

            return dispatch(new AddRoleAndPermissionsJob($this->request, $role));
        });
    }

    private function getRoles(): array
    {
        return [
            [
                'role' => [
                    'name' => 'User Accounts Manager',
                    'display_name' => 'User Accounts Manager',
                    'description' => 'User has permission to manage user accounts (add, edit and delete them)'
                ],
                'permissions' => [
                    'add-user', 'edit-user', 'delete-user'
                ],
                'is_partner' => true,
                'is_employer' => true
            ],
            [
                'role' => [
                    'name' => 'Roles Manager',
                    'display_name' => 'Roles Manager',
                    'description' => 'User has permission to manage roles (add, edit and delete them)'
                ],
                'permissions' => [
                    'add-role', 'edit-role', 'delete-role'
                ],
                'is_partner' => true,
                'is_employer' => true
            ],
            [
                'role' => [
                    'name' => 'Loans Approver',
                    'display_name' => 'Loans Approver',
                    'description' => 'User can approver employee loan applications'
                ],
                'permissions' => [
                    'approve-loan-application'
                ],
                'is_partner' => true,
                'is_employer' => true
            ]
        ];
    }
}
