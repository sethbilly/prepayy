<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetInstitutionPartnerEmployersJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var FinancialInstitution
     */
    private $institution;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->institution = $request->user()->institutable;
    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle()
    {
        return $this->getPartnerEmployers();
    }

    private function getPartnerEmployers(): Paginator
    {
        return $this->institution->partnerEmployers()
            ->when($this->request->has('search'), function($q) {
                return $q->where('name', 'like', $this->request->get('search') . '%');
            })
            ->orderBy('name')
            ->paginate($this->request->get('limit', 20));
    }
}
