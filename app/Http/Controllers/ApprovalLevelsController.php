<?php

namespace App\Http\Controllers;

use App\Entities\ApprovalLevel;
use App\Http\Requests\AddApprovalLevelRequest;
use App\Jobs\CreateApprovalLevelJob;
use App\Jobs\GetInstitutionApprovalLevelsJob;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApprovalLevelsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $approvalLevels = dispatch(new GetInstitutionApprovalLevelsJob($request));

        return view('dashboard.approvals.index')->with(compact('approvalLevels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $title = 'New Approval Level';
        $approvalLevel = new ApprovalLevel();
        // The approval level number. Example Level # 1
        $levelNumber = $request->user()->institutable->approvalLevels->count() + 1;

        return view('dashboard.approvals.create')
            ->with(compact('approvalLevel', 'levelNumber', 'title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AddApprovalLevelRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddApprovalLevelRequest $request)
    {
        try {
            dispatch(new CreateApprovalLevelJob($request));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error adding approval level', [
                'error' => $exception->getMessage()
            ]);

            flash()->error("An error occurred while adding the approval level. Please try again!");

            return back();
        }

        flash()->success("Approval level was added successfully");

        return redirect()->route('approval_levels.index');
    }

    /**
     * @param Request $request
     * @param string $slug
     * @return ApprovalLevel
     */
    private function getApprovalLevel(Request $request, string $slug): ApprovalLevel
    {
        return ApprovalLevel::where([
            'slug' => $slug,
            'institutable_id' => $request->user()->institutable_id,
            'institutable_type' => $request->user()->institutable_type
        ])->firstOrFail();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param  string $level
     * @return View
     */
    public function edit(Request $request, string $level)
    {
        $title = 'Edit Approval Level';
        $approvalLevel = $this->getApprovalLevel($request, $level);
        // Level number is one more than levels added before it
        $levelNumber = $request->user()
                ->institutable->approvalLevels
                ->where('id', '<', $approvalLevel->id)->count() + 1;

        return view('dashboard.approvals.create')
            ->with(compact('approvalLevel', 'levelNumber', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  AddApprovalLevelRequest $request
     * @param  string $slug
     * @return \Illuminate\Http\Response
     */
    public function update(AddApprovalLevelRequest $request, string $slug)
    {
        $approvalLevel = $this->getApprovalLevel($request, $slug);

        try {
            dispatch(new CreateApprovalLevelJob($request, $approvalLevel));
        } catch (ConflictWithExistingRecord $exception) {
            logger()->error('Error updating approval level',
                ['error' => $exception->getMessage()]);

            flash()->error("An error occurred while updating the approval level. Please try again!");

            return back();
        }

        flash()->success("Approval level was updated successfully");

        return redirect()->route('approval_levels.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param string $slug
     * @return RedirectResponse
     */
    public function destroy(Request $request, string $slug)
    {
        $rec = $this->getApprovalLevel($request, $slug);

        return $rec->delete() ?
            redirect()->route('approval_levels.index') :
            back()->withErrors(['error' => sprintf('%s was not deleted', [$slug])]);
    }
}
