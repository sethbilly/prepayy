<?php

namespace App\Jobs;

use App\Entities\Employer;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class AddEmployerJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Employer
     */
    private $employer;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param Employer $employer
     */
    public function __construct(Request $request, Employer $employer = null)
    {
        $this->request = $request;
        $this->employer = $employer ?? new Employer();
    }

    /**
     * Execute the job.
     *
     * @return Employer|null
     */
    public function handle()
    {
        return $this->addEmployer() ? $this->employer : null;
    }

    /**
     * @return bool
     */
    private function addEmployer(): bool
    {
        $this->checkIfIsExistingEmployer();

        foreach ($this->employer->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $this->employer[$fillable] = $this->request->get($fillable);
            }
        }
        $this->employer->slug = $this->employer->name;

        // For new employer accounts, add information about the user creating the account
        if (!$this->employer->id) {
            $this->employer->user_id = $this->request->user()->id;
        }

        return $this->employer->save();
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkIfIsExistingEmployer(): bool
    {
        $rec = Employer::where('name', $this->request->get('name'))->first();

        // Institution names are distinct
        if (empty($rec) || ($this->employer && $this->employer->id == $rec->id)) {
            return false;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }
}
