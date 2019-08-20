<?php

namespace App\Jobs;

use App\Entities\Permission;
use App\Entities\Role;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddRoleAndPermissionsJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Role
     */
    private $role;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Role $role
     */
    public function __construct(Request $request, Role $role = null)
    {
        $this->request = $request;
        $this->role = $role ?? new Role(['name' => $this->request->input('role.name')]);
    }

    /**
     * Execute the job.
     *
     * @return Role
     */
    public function handle()
    {
        return $this->addRole();
    }

    /**
     * @return Role
     */
    private function addRole(): Role
    {
        // Ensure duplicate roles are not added
        $this->checkIsNotExistingRole();

        return DB::transaction(function() {
            // Add the role
            $this->addRoleHelper($this->request->get('role'));

            // Add the permissions
            $perms = collect($this->request->get('permissions'))
                ->map(function(string $perm) {
                    // Permissions are seeded and should never be created manually
                    return Permission::where('name', $perm)->first();
                })
                ->filter(function($rec) {
                    // Get permission records which were found
                    return !empty($rec);
                });

            $this->role->permissions()->sync($perms->pluck('id')->all());

            return $this->role;
        });
    }

    /**
     * @param array $roleDetails
     * @return bool
     */
    private function addRoleHelper(array $roleDetails): bool
    {
        foreach ($this->role->getFillable() as $fillable) {
            if (array_key_exists($fillable, $roleDetails)) {
                $this->role[$fillable] = $roleDetails[$fillable];
            }
        }
        // Scope the role to the logged in user's institution
        $this->role->institutable_id = $this->role->institutable_id ?? $this->request->user()->institutable_id ?? null;
        $this->role->institutable_type = $this->role->institutable_type ?? $this->request->user()->institutable_type ?? null;

        return $this->role->save();
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkIsNotExistingRole(): bool
    {
        $rec = Role::where([
            'name' => $this->request->input('role.name'),
            'institutable_id' => $this->request->user()->institutable_id ?? null,
            'institutable_type' => $this->request->user()->institutable_type ?? null,
        ])->first();

        if (empty($rec) || $rec->id == $this->role->id) {
            return false;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }
}
