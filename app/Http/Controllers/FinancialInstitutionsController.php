<?php

namespace App\Http\Controllers;

use App\Entities\BrandStyle;
use App\Entities\Employer;
use App\Entities\FinancialInstitution;
use App\Http\Requests\AddFinancialInstitutionRequest;
use App\Http\Requests\AddInstitutionPartnerEmployerRequest;
use App\Jobs\AddInstitutionPartnerEmployerJob;
use App\Jobs\CreateFinancialInstitutionAccountJob;
use App\Jobs\GetInstitutionPartnerEmployersJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FinancialInstitutionsController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        //dd("Test");
        $search = $request->get('search');
        $limit = $request->get('limit', 30);
        $partners = FinancialInstitution::paginate($limit);

        //dd($partners);

        return view('dashboard.callens.partners.index', compact('partners', 'search', 'limit'));
    }

    public function create()
    {
        $title = 'New Financial Partner';
        $brandStyle = BrandStyle::getDefaultStyle('partner');
        $partner = new FinancialInstitution();

        return view('dashboard.callens.partners.create')->with(compact('brandStyle', 'partner', 'title'));
    }

    public function store(AddFinancialInstitutionRequest $request)
    {
        try {
            dispatch(new CreateFinancialInstitutionAccountJob($request));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error adding financial institution', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back()->withInput();
        }

        flash()->success("The financial institution was added successfully.");

        return redirect()->route('callens.partners.index');
    }

    public function edit(FinancialInstitution $partner)
    {
        $title = 'Edit Financial Partner';

        return view('dashboard.callens.partners.create')
            ->with(compact('partner', 'title'))
            ->with([
                'brandStyle' => $partner->dashboardBranding ?? BrandStyle::getDefaultStyle('partner')
            ]);
    }

    public function update(AddFinancialInstitutionRequest $request, FinancialInstitution $partner)
    {
        try {
            dispatch(new CreateFinancialInstitutionAccountJob($request, $partner));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating financial institution', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back();
        }

        flash()->success("Financial institution was updated successfully.");

        return redirect()->route('callens.partners.index');
    }

    /**
     * @param Request $request
     * @return View
     */
    public function getPartnerEmployers(Request $request) : View
    {
        $limit = $request->get('limit', 30);
        $search = $request->get('search');
        $partnerEmployers = dispatch(new GetInstitutionPartnerEmployersJob($request));
        $allEmployers = Employer::all();
        
        return view('dashboard.financial_institutions.employers.index')->with(
            compact('partnerEmployers', 'allEmployers', 'limit', 'search')
        );
    }

    /**
     * @param AddInstitutionPartnerEmployerRequest $request
     * @return mixed
     */
    public function addPartnerEmployer(AddInstitutionPartnerEmployerRequest $request)
    {
        try {
            dispatch(new AddInstitutionPartnerEmployerJob($request));

            flash()->success('Partner employer was successfully added');
        } catch (ConflictWithExistingRecord $e) {
            flash()->error($e->getMessage());
        }

        return back();
    }

    /**
     * @param Request $request
     * @param Employer $employer
     * @return mixed
     */
    public function deletePartnerEmployer(Request $request, Employer $employer)
    {
        $wasDeleted = $request->user()->institutable->partnerEmployers()->detach($employer->id);

        $wasDeleted ?
            flash()->success('Partner employer was successfully deleted') :
            flash()->error('Sorry! The partner employer was not deleted');

        return back();
    }
}