<?php

namespace App\Http\Controllers;

use App\Entities\LoanType;
use App\Http\Requests\CreateLoanTypeRequest;
use App\Jobs\CreateLoanTypeJob;
use App\Jobs\GetLoanTypesJob;
use App\Jobs\UpdateLoanTypeJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoanTypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(Request $request)
    {
        $loanTypes = $this->dispatch(new GetLoanTypesJob($request));

        return view('dashboard.loan_types.index')
            ->with(compact('loanTypes'))
            ->with([
                'search' => $request->get('search'),
                'limit' => $request->get('limit')
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateLoanTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateLoanTypeRequest $request)
    {
        try {
            $this->dispatch(new CreateLoanTypeJob($request));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error adding loan type', [
                'error' => $exception->getMessage()
            ]);

            flash()->error("The loan type has already been added!");

            return back();

        }
        flash()->success("Loan type was added successfully");

        return redirect()->route('loan_products.types.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  CreateLoanTypeRequest  $request
     * @param  LoanType  $type
     * @return \Illuminate\Http\Response
     */
    public function update(CreateLoanTypeRequest $request, LoanType $type)
    {
        try {
            $this->dispatch(new UpdateLoanTypeJob($request, $type));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating loan type', [
                'error' => $exception->getMessage()
            ]);

            flash()->error("Another loan type with the same name exists!");

            return back();

        }
        flash()->success("Loan type was updated successfully");

        return redirect()->route('loan_products.types.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  LoanType  $type
     * @return RedirectResponse
     */
    public function destroy(LoanType $type)
    {
        $loansCount = $type->loans_count;

        if ($loansCount) {
            flash()->error('Loan type is associated with loans. Cannot be deleted');
            return back();
        }

        $type->delete();
        flash()->success('Loan type deleted');

        return back();
    }
}
