<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use App\Entities\LoanProduct;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class GetLoanProductsJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param FinancialInstitution $institution restricts the results to the products of the institution
     */
    public function __construct(
        Request $request,
        FinancialInstitution $institution = null
    ) {
        $this->request = $request;

        // Add the passed in institution to the institutions to restrict the results to
        if ($institution) {
            $this->request->merge([
                'institution_ids' => array_merge(
                    $this->request->get('institution_ids', []), [$institution->id]
                )
            ]);
        }

    }

    /**
     * Execute the job.
     *
     * @return Paginator
     */
    public function handle()
    {
        return $this->getLoanProducts();
    }

    /**
     * @return Paginator
     */
    private function getLoanProducts(): Paginator
    {
        $institutionIds = collect($this->request->get('institution_ids', []))
            ->filter(function ($id) {
                return is_numeric($id) && intval($id) > 0;
            });
        $minAmount = $this->request->get('min_amount', 0);
        $loanTypeId = $this->request->get('loan_type_id');

        return (new LoanProduct())->with(['loanType'])
            ->when($this->request->has('search'), function ($q) {
                $search = $this->request->get('search');

                return $q->where('name', 'like', $search . '%')
                    ->orWhere('description', 'like', $search . '%');
            })
            ->when($institutionIds->count(), function ($q) use ($institutionIds) {
                return $q->whereIn('financial_institution_id', $institutionIds);
            })
            ->when(is_numeric($loanTypeId) && !empty($loanTypeId),
                function ($q) use ($loanTypeId) {
                    return $q->where('loan_type_id', $loanTypeId);
                })
            ->when(is_numeric($minAmount) && !empty($minAmount),
                function ($q) use ($minAmount) {
                    return $q->where('max_amount', '>=', $minAmount);
                })
            ->paginate($this->request->get('limit', 30));
    }
}
