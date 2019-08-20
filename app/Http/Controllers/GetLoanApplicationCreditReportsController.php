<?php

namespace App\Http\Controllers;

use App\Entities\LoanApplication;
use App\Http\Requests\GetCreditReportRequest;
use App\Jobs\GetLoanApplicationCreditReportJob;
use CloudLoan\Libraries\Xds\ConsumerMatchNotFoundException;
use Illuminate\Contracts\View\View;

class GetLoanApplicationCreditReportsController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param GetCreditReportRequest $request - request object is used here purely for
     * validating authorization of the request.
     * @param LoanApplication $application
     * @return View
     */
    public function show(GetCreditReportRequest $request, LoanApplication $application)
    {
        $report = null;

        try {
            $report = $this->dispatch(new GetLoanApplicationCreditReportJob($application));

            logger()->debug(print_r($report, 1));
        } catch (ConsumerMatchNotFoundException $exception) {
            logger()->error($exception->getMessage(), [$exception]);

            $error = "Credit report for {$application->getUser()->getFullName()} was not found";
            flash()->error($error);
        } catch (\SoapFault $exception) {
            logger()->error($exception->getMessage(), [$exception]);

            $error = 'An internal error occurred while retrieving the credit report.';
            $error .= 'Please try again later';
            flash()->error($error);
        }

        return view('dashboard.loan_applications.credit_report')
            ->with(compact('report', 'application'));
    }
}
