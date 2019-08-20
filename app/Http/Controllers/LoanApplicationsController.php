<?php

namespace App\Http\Controllers;

use App\Entities\Country;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanApplicationStatus;
use App\Entities\LoanProduct;
use App\Entities\RequestedLoanDocument;
use App\Entities\User;
use App\Http\Requests\AdditionalLoanDocumentRequest;
use App\Http\Requests\ApproveLoanRequest;
use App\Http\Requests\CanApplyForLoanRequest;
use App\Http\Requests\CheckLoanEligibilityRequest;
use App\Http\Requests\SubmitAdditionalDocumentRequest;
use App\Http\Requests\SubmitForPartnerApprovalRequest;
use App\Http\Requests\UpdateBorrowerRequest;
use App\Jobs\ApproveLoanApplicationJob;
use App\Jobs\CalculateMonthlyAmountPayableJob;
use App\Jobs\CanApproveLoanApplicationJob;
use App\Jobs\GetBorrowerLoanProfileDataJob;
use App\Jobs\GetLoanApplicationRequestsJob;
use App\Jobs\GetLoanApprovalLevelLabel;
use App\Jobs\GetLoanRegistrationButtonsJob;
use App\Jobs\IsEligibleForLoanJob;
use App\Jobs\RequestLoanDocumentJob;
use App\Jobs\SubmitAdditionalLoanDocumentsJob;
use App\Jobs\SubmitLoanApplicationJob;
use App\Jobs\SubmitLoanForPartnerApproval;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LoanApplicationsController extends Controller
{
    const SKIP_LOAN_GUIDELINES_COOKIE = 'skip_loan_guidelines';
    const CURRENT_EMPLOYER = 'employer_id';

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $applications = dispatch(new GetLoanApplicationRequestsJob($request));

        foreach ($applications as $application) {
            $application->setRepaymentInformation();
            $application->status_label = $this->dispatch(new GetLoanApprovalLevelLabel(
                $request, $application
            ));
        }

        return view('dashboard.loan_applications.index')->with(compact('applications'));
    }

    /**
     * @param CanApplyForLoanRequest $request
     * @param FinancialInstitution $partner
     * @param string $slug
     * @return mixed
     */
    public function getLoanGuideLines(
        CanApplyForLoanRequest $request,
        FinancialInstitution $partner,
        string $slug
    ) {
        // User wants to skip the loan guidelines
        if ($request->hasCookie(self::SKIP_LOAN_GUIDELINES_COOKIE)) {
            return redirect()->route('loan_applications.eligibility',
                ['partner' => $partner, 'product' => $slug]);
        }
        $amount = $request->get('amount');
        $tenure = $request->get('tenure');
        $product = LoanProduct::findByInstitutionAndSlug($partner, $slug);

        return view('dashboard.loan_applications.guidelines')->with(
            compact('product', 'amount', 'tenure')
        );
    }

    /**
     * Get the loan eligibility page
     * @param CanApplyForLoanRequest $request
     * @param FinancialInstitution $partner
     * @param string $slug
     * @return mixed
     */
    public function getLoanEligibility(
        CanApplyForLoanRequest $request,
        FinancialInstitution $partner,
        string $slug
    ) {
        // If user has opted to skip the loan application guidelines, show only employer verification page,
        // else, show loan guidelines, then the employer verification page

        // Set skip loan guidelines cookie
        if ($request->has('skip_guidelines')) {
            cookie()->queue(cookie(self::SKIP_LOAN_GUIDELINES_COOKIE, true,
                365 * 24 * 60));
        }

        $amount = $request->get('amount');
        $tenure = $request->get('tenure');
        $allEmployers = Employer::all();
        $product = LoanProduct::findByInstitutionAndSlug($partner, $slug);

        return view('dashboard.loan_applications.eligibility')->with(
            compact('allEmployers', 'product', 'amount', 'tenure')
        );
    }

    /**
     * @param CheckLoanEligibilityRequest $request
     * @param FinancialInstitution $partner
     * @param string $slug
     * @return mixed
     */
    public function checkLoanEligibility(
        CheckLoanEligibilityRequest $request,
        FinancialInstitution $partner,
        string $slug
    ) {
        if (!dispatch(new IsEligibleForLoanJob($request, $partner))) {
            return $this->sendNotEligibleForLoanResponse($request, $partner);
        }

        // Save the current employer id for verification on the registration page
        $request->session()->put(self::CURRENT_EMPLOYER, $request->get('employer_id'));

        return redirect()->route('loan_applications.apply', [
            'partner' => $partner,
            'product' => $slug,
            'amount' => $request->get('amount'),
            'tenure' => $request->get('tenure')
        ]);
    }

    /**
     * @param Request $request
     * @param FinancialInstitution $partner
     * @return mixed
     */
    private function sendNotEligibleForLoanResponse(
        Request $request,
        FinancialInstitution $partner
    ) {
        $employer = Employer::findOrFail($request->get('employer_id'));

        flash()->error('Sorry! Employees of ' . $employer->name . ' are not eligible to apply loans from ' . $partner->name);

        return redirect()->route('loan_applications.index');
    }

    /**
     * @param CanApplyForLoanRequest $request
     * @param FinancialInstitution $partner
     * @param string $slug
     * @return mixed
     */
    public function getApplicationForm(
        CanApplyForLoanRequest $request,
        FinancialInstitution $partner,
        string $slug
    ) {
        $request->merge(['employer_id' => $request->session()->get(self::CURRENT_EMPLOYER)]);

        if (!dispatch(new IsEligibleForLoanJob($request, $partner))) {
            return $this->sendNotEligibleForLoanResponse($request, $partner);
        }

        $product = LoanProduct::findByInstitutionAndSlug($partner, $slug);
        $buttons = dispatch(new GetLoanRegistrationButtonsJob($request));
        $amount = $request->get('amount', $product->min_amount);
        $tenure = $request->get('tenure', 1);
        $profileData = $this->dispatch(new GetBorrowerLoanProfileDataJob($request));
        $isEditable = true;

        return view('dashboard.loan_applications.registration')
            ->with($profileData)
            ->with(compact('product', 'buttons', 'amount', 'tenure', 'isEditable'));
    }

    /**
     * @param UpdateBorrowerRequest $request
     * @param FinancialInstitution $partner
     * @param string $slug
     * @return mixed
     */
    public function storeLoanApplication(
        UpdateBorrowerRequest $request,
        FinancialInstitution $partner,
        string $slug
    ) {
        // Ensure employer's employees are eligible for loans
        $request->merge([
            'employer_id' => $request->has('employer.id') ?
                $request->input('employer.id') :
                $request->session()->get(self::CURRENT_EMPLOYER)
        ]);

        if (!dispatch(new IsEligibleForLoanJob($request, $partner))) {
            return $this->sendNotEligibleForLoanResponse($request, $partner);
        }

        // Remove the employer id from the session
        $request->session()->forget(self::CURRENT_EMPLOYER);

        $product = LoanProduct::findByInstitutionAndSlug($partner, $slug);

        $job = new SubmitLoanApplicationJob($request, $product);
        $application = dispatch($job);

        if ($job->isSubmitForEmployerApproval()) {
            $msg = sprintf('Your loan application has been submitted to %s for approval',
                $application->employer->name);
        } else {
            $msg = 'Your loan application has been saved. To resume the application, ';
            $msg .= 'click the edit application link';
        }

        flash()->success($msg);

        return redirect()->route('loan_applications.index');
    }

    /**
     * @param Request $request
     * @param LoanApplication $application
     * @return mixed
     */
    public function show(Request $request, LoanApplication $application)
    {
        // Get the employer associated with this application
        $employer = $application->employer_id ? $application->getUsersEmployer() : null;
        $canApprove = dispatch(
            new CanApproveLoanApplicationJob($request->user(), $application)
        );
        $statusApprove = $request->user()->isEmployerStaff()
            ? LoanApplicationStatus::getEmployerApprovedStatus()
            : LoanApplicationStatus::getPartnerApprovedStatus();
        $statusDecline = $request->user()->isEmployerStaff()
            ? LoanApplicationStatus::getEmployerDeclinedStatus()
            : LoanApplicationStatus::getPartnerDeclinedStatus();

        $requestedInformation = $request->user()->isEmployerStaff()
            ? $application->getEmployerRequestedInformation()
            : $application->getPartnerRequestedInformation();

        $application->status_label = $this->dispatch(new GetLoanApprovalLevelLabel(
            $request, $application
        ));

        return view('dashboard.loan_applications.show')
            ->with(
                compact(
                    'application', 'employer', 'canApprove', 'statusApprove',
                    'statusDecline', 'requestedInformation'
                )
            );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param CanApplyForLoanRequest $request
     * @param LoanApplication $application
     * @return View
     */
    public function edit(CanApplyForLoanRequest $request, LoanApplication $application)
    {
        $product = $application->loanProduct;

        $data = $this->dispatch(new GetBorrowerLoanProfileDataJob($request));

        $data['guarantor'] = $application->guarantor ?? null;
        // Get the employer associated with this application
        $data['currentEmployer'] = $application->employer_id ?
            $application->getUsersEmployer() : $data['currentEmployer'];
        $data['amount'] = $application->amount;
        $data['tenure'] = $application->tenure_in_years;

        $buttons = dispatch(new GetLoanRegistrationButtonsJob($request, $application));

        $hasRequestedInformation = $application->requestedDocuments()->count() > 0;
        // Application cannot be edited once approved or declined by either an employer
        // or financial institution
        $isEditable = !$application->isApprovedOrDeclined();

        return view('dashboard.loan_applications.registration')
            ->with($data)
            ->with(compact('isEditable', 'hasRequestedInformation'))
            ->with(compact('product', 'application', 'buttons'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBorrowerRequest|Request $request
     * @param LoanApplication $application
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBorrowerRequest $request, LoanApplication $application)
    {
        // You can't trick us by changing to an unacceptable employer
        $request->merge([
            'employer_id' => $request->input('employer.id')
        ]);
        if (!dispatch(new IsEligibleForLoanJob($request,
            $application->getInstitution()))
        ) {
            return $this->sendNotEligibleForLoanResponse($request,
                $application->getInstitution());
        }

        // Set the id of the application we are editing
        $request->merge(['loan_application_id' => $application->id]);

        $job = new SubmitLoanApplicationJob($request, $application->loanProduct);
        $application = dispatch($job);

        if ($job->isSubmitForPartnerApproval()) {
            $msg = sprintf('Please enter your %s digit submission token',
                SubmitLoanApplicationJob::SUBMIT_TOKEN_LENGTH);
        } elseif ($job->isSubmitForEmployerApproval()) {
            $msg = sprintf('Your loan application has been submitted to %s for approval',
                $application->employer->name);
        } else {
            $msg = 'Your loan application has been saved. To resume the application, click the edit application link';
        }

        flash()->success($msg);

        return $job->isSubmitForPartnerApproval() ?
            redirect()->route('loan_applications.confirm_submission',
                ['application' => $application]) :
            redirect()->route('loan_applications.index');
    }

    /**
     * @param ApproveLoanRequest $request
     * @param LoanApplication $application
     * @return mixed
     */
    public function approve(ApproveLoanRequest $request, LoanApplication $application)
    {
        dispatch(new ApproveLoanApplicationJob($request, $application));

        $status = $application->fresh()->getLoanApplicationStatus();
        $message = $status->isEmployerApproved() || $status->isPartnerApproved()
            ? 'The loan application has been approved'
            : 'The loan application has been declined';

        flash()->success($message);

        return back();
    }

    /**
     * @param CanApplyForLoanRequest $request
     * @param LoanApplication $application
     * @return View
     */
    public function getConfirmPartnerSubmission(
        CanApplyForLoanRequest $request,
        LoanApplication $application
    ) {
        return view('dashboard.loan_applications.confirm_submission')->with(compact('application'));
    }

    /**
     * @param SubmitForPartnerApprovalRequest $request
     * @param LoanApplication $application
     * @return mixed
     */
    public function postConfirmPartnerSubmission(
        SubmitForPartnerApprovalRequest $request,
        LoanApplication $application
    ) {
        $wasSubmitted = dispatch(new SubmitLoanForPartnerApproval($request,
            $application));

        $wasSubmitted ?
            flash()->success(sprintf('Your application has been submitted to %s for approval',
                $application->loanProduct->institution->name)) :
            flash()->error('The submission token is invalid!');

        return $wasSubmitted ?
            redirect()->route('loan_applications.index') :
            back();
    }

    /**
     * @param AdditionalLoanDocumentRequest $request
     * @param LoanApplication $application
     * @return mixed
     */
    public function requestDocuments(
        AdditionalLoanDocumentRequest $request,
        LoanApplication $application
    ) {
        dispatch(new RequestLoanDocumentJob($request, $application));

        $message = $request->user()->isEmployerStaff()
            ? 'Your request for changes has been sent'
            : 'Your request for additional documents has been sent';

        flash()->success($message);

        return back();
    }

    public function addRequestedDocument(
        SubmitAdditionalDocumentRequest $request,
        LoanApplication $application,
        RequestedLoanDocument $document
    ) {
        $wasUpdated = dispatch(new SubmitAdditionalLoanDocumentsJob($request, $document));

        $wasUpdated ?
            flash()->success('The response were successfully sent') :
            flash()->error('The response was not submitted');

        return back();
    }
}