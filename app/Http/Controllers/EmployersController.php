<?php

namespace App\Http\Controllers;

use App\Entities\BrandStyle;
use App\Entities\Employer;
use App\Http\Requests\AddEmployerRequest;
use App\Jobs\CreateEmployerAccountJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployersController extends Controller
{

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
        $limit = $request->get('limit', 30);
        $search = $request->get('search');

        $employers = Employer::when($request->has('search'), function ($q) use ($request) {
            return $q->where('name', 'like', $request->get('search') . '%');
        })->paginate($limit);

        return view('dashboard.callens.employers.index', compact('employers', 'search', 'limit'));
    }

    public function create()
    {
        $title = 'New Employer';
        $employer = new Employer();

        return view('dashboard.callens.employers.create')->with(compact('employer', 'title'));
    }

    public function edit(Employer $employer)
    {
        $title = "Edit Employer";

        return view('dashboard.callens.employers.create')->with(compact('employer', 'title'));
    }

    /**
     * @param AddEmployerRequest $request
     * @return mixed
     */
    public function store(AddEmployerRequest $request)
    {
        try {
            dispatch(new CreateEmployerAccountJob($request));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error adding employer', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back()->withInput();
        }

        flash()->success("Employer was added successfully");

        return redirect()->route('callens.employers.index');
    }

    /**
     * @param AddEmployerRequest $request
     * @param Employer $employer
     * @return mixed
     */
    public function update(AddEmployerRequest $request, Employer $employer)
    {
        try {
            dispatch(new CreateEmployerAccountJob($request, $employer));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating employer', ['error' => $exception->getMessage()]);

            flash()->error($exception->getMessage());

            return back();
        }

        flash()->success("Employer was updated successfully");

        return redirect()->route('callens.employers.index');
    }
}