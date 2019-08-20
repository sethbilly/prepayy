<?php

namespace App\Jobs;

use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\LoanType;
use App\Entities\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanTypesJob
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
        $this->user = $this->request->user();
    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle()
    {
        $limit = $this->request->get('limit', 20);

        $builder = new LoanType();

        if (!$this->user->isApplicationOwner()) {
            $staffIds = User::getByInstitution(
                $this->user->institutable_id, $this->user->institutable_type
            )->pluck('id')->toArray();

            $builder = $builder->whereIn('user_id', $staffIds);
        }

        if ($this->request->has('search')) {
            $builder = $builder->where('name', 'like', $this->request->get('search') . '%');
        }

        return $builder->paginate($limit);
    }
}
