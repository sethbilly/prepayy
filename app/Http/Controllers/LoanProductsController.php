<?php

namespace App\Http\Controllers;

use App\Entities\ApprovalLevel;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\LoanProduct;
use App\Entities\LoanType;
use App\Http\Requests\CreateLoanProductRequest;
use App\Jobs\CreateLoanProductJob;
use App\Jobs\GetLoanPayablesJob;
use App\Jobs\GetLoanProductsJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class LoanProductsController extends Controller
{
    /**
     * Handle browsing of loan products belonging to a given institution
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $products = dispatch(new GetLoanProductsJob($request,
            $request->user()->institutable));
        $search = $request->get('search');
        $limit = $request->get('limit', 20);

        return view('dashboard.products.index')->with(compact('products', 'search',
            'limit'));
    }

    /**
     * Browse the list of loan products
     * @param Request $request
     * @return mixed
     */
    public function browseProducts(Request $request)
    {
        $productList = dispatch(new GetLoanProductsJob($request));
        $products = $this->dispatch(new GetLoanPayablesJob($request, $productList));

        $filters = $request->only([
            'search',
            'limit',
            'min_amount',
            'institution_ids',
            'loan_type_id',
            'tenure'
        ]);
        $allInstitutions = FinancialInstitution::orderBy('name', 'asc')->get();
        $loanTypes = LoanType::orderBy('name')->get();
        $minLoanAmount = LoanProduct::getMinLoanAmount();
        $maxLoanAmount = LoanProduct::getMaxLoanAmount();

        return view('dashboard.products.browse')
            ->with(compact(
                'products',
                'search',
                'allInstitutions',
                'loanTypes',
                'limit',
                'minLoanAmount',
                'maxLoanAmount'
            ))
            ->with($filters);
    }

    public function create()
    {
        $title = 'New Loan Product';
        $product = new LoanProduct();
        $loanTypes = LoanType::orderBy('name', 'asc')->get();

        return view('dashboard.products.create')
            ->with(compact('product', 'title', 'loanTypes'));
    }

    public function store(CreateLoanProductRequest $request)
    {
        try {
            dispatch(new CreateLoanProductJob($request));
        } catch (ConflictWithExistingRecord $e) {
            logger()->error('Add loan product error', ['error' => $e->getMessage()]);
            flash()->error($e->getMessage());

            return back();
        }

        flash()->success('Loan product was added successfully');

        return redirect()->route('loan_products.index');
    }


    public function edit(Request $request, string $slug)
    {
        $title = 'Edit Loan Product';
        $product = LoanProduct::findByInstitutionAndSlug($request->user()->institutable,
            $slug);
        $loanTypes = LoanType::orderBy('name')->get();

        return view('dashboard.products.create')
            ->with(compact('product', 'title', 'loanTypes'));
    }

    public function update(CreateLoanProductRequest $request, string $slug)
    {
        $product = LoanProduct::findByInstitutionAndSlug($request->user()->institutable,
            $slug);

        try {
            dispatch(new CreateLoanProductJob($request, $product));
        } catch (ConflictWithExistingRecord $e) {
            logger()->error('Loan product update error', ['error' => $e->getMessage()]);

            flash()->error($e->getMessage());

            return back();
        }

        flash()->success('Loan product was updated successfully');

        return redirect()->route('loan_products.index');
    }
}
