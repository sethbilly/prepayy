<?php

namespace App\Jobs;

use App\Entities\ApprovalLevel;
use App\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetInstitutionApprovalLevelsJob
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user = $request->user();
    }

    /**
     * Execute the job.
     *
     * @return Collection
     */
    public function handle()
    {
        return $this->getApprovalLevels();
    }

    /**
     * @return Collection
     */
    private function getApprovalLevels(): Collection
    {
        return ApprovalLevel::where([
            'institutable_id' => $this->user->institutable_id,
            'institutable_type' => $this->user->institutable_type
        ])->orderBy('id')->get();
    }
}
