<?php

namespace App\Jobs;

use App\Entities\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetUsersForInstitutionJob
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
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        return $this->getUsers();
    }

    /**
     * @return Paginator
     * @throws \InvalidArgumentException
     */
    private function getUsers(): Paginator
    {
        $searchKey = 'search';

        // Application owner user lists should not include borrowers
        return User::with(['roles'])
            ->where([
                'institutable_id' => $this->request->user()->institutable_id,
                'institutable_type' => $this->request->user()->institutable_type,
            ])
            ->when($this->request->user()->isApplicationOwner(), function($q) {
                return $q->where('is_app_owner', 1);
            })
            ->when($this->request->has($searchKey), function ($query) use ($searchKey) {
                return $query
                    ->where('firstname', 'like', $this->request->get($searchKey) . '%')
                    ->orWhere('lastname', 'like', $this->request->get($searchKey) . '%')
                    ->orWhere('email', 'like', $this->request->get($searchKey) . '%');
            })
            ->paginate($this->request->get('limit', 20));
    }
}
