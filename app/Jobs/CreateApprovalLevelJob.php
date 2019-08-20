<?php

namespace App\Jobs;

use App\Entities\ApprovalLevel;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class CreateApprovalLevelJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ApprovalLevel
     */
    private $level;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param ApprovalLevel $level
     */
    public function __construct(Request $request, ApprovalLevel $level = null)
    {
        $this->request = $request;
        $this->level = $level ?? new ApprovalLevel();
    }

    /**
     * Execute the job.
     *
     * @return ApprovalLevel
     */
    public function handle()
    {
        return $this->addApprovalLevel();
    }

    /**
     * @return ApprovalLevel
     * @throws ConflictWithExistingRecord
     */
    private function addApprovalLevel(): ApprovalLevel
    {
        $this->checkIsNotExistingRecord();

        $this->level->institutable_id = $this->request->user()->institutable_id;
        $this->level->institutable_type = $this->request->user()->institutable_type;
        $this->level->name = $this->request->get('name');
        $this->level->slug = $this->request->get('name');

        $this->level->save();

        return $this->level;
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkIsNotExistingRecord(): bool
    {
        $rec = ApprovalLevel::where([
            'institutable_id' => $this->request->user()->institutable_id,
            'institutable_type' => $this->request->user()->institutable_type,
            'name' => $this->request->get('name')
        ])->first();

        if (empty($rec) || ($this->level && $this->level->id == $rec->id)) {
            return false;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }
}
