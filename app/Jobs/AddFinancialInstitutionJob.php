<?php

namespace App\Jobs;

use App\Entities\FinancialInstitution;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class AddFinancialInstitutionJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var FinancialInstitution
     */
    private $institution;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param FinancialInstitution $institution
     */
    public function __construct(Request $request, FinancialInstitution $institution = null)
    {
        $this->request = $request;
        $this->institution = $institution;
    }

    /**
     * Execute the job.
     *
     * @return FinancialInstitution|null
     */
    public function handle()
    {
        return $this->addInstitution() ? $this->institution : null;
    }

    /**
     * @return bool
     */
    private function addInstitution(): bool
    {
        $this->checkIfIsExistingInstitution();

        if (empty($this->institution)) {
            $this->institution = new FinancialInstitution();
        }

        foreach ($this->institution->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $this->institution[$fillable] = $this->request->get($fillable);
            }
        }
        $this->institution->slug = $this->institution->name;

        return $this->institution->save();
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkIfIsExistingInstitution(): bool
    {
        $rec = FinancialInstitution::where('name', $this->request->get('name'))->first();

        // Institution names are distinct
        if (empty($rec) || ($this->institution && $this->institution->id == $rec->id)) {
            return false;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }
}
