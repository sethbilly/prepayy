<?php

namespace App\Jobs;

use App\Entities\Role;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetRolesForInstitutionJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle(): Paginator
    {
        return $this->getRoles();
    }

    /**
     * @return Paginator
     */
    private function getRoles(): Paginator
    {
        $searchKey = 'search';

        // Account owner and app owner roles are private to the application and should never be exposed for viewing
        return Role::with(['permissions'])->where([
            'institutable_id' => $this->request->user()->institutable_id,
            'institutable_type' => $this->request->user()->institutable_type,
        ])->when($this->request->has($searchKey), function ($query) use ($searchKey) {
            return $query
                ->where('display_name', 'like', $this->request->get($searchKey) . '%')
                ->orWhereHas('permissions', function ($q) use ($searchKey) {
                    $q->where('permissions.display_name', 'like', $this->request->get($searchKey) . '%');
                });
        })->whereNotIn('name', [Role::ROLE_APP_OWNER, Role::ROLE_ACCOUNT_OWNER])
            ->paginate($this->request->get('limit', 20));
    }
}
