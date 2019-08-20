<?php

namespace App\Jobs;

use App\Entities\FileEntry;
use App\Entities\LoanProduct;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use CloudLoan\Traits\GenerateFilenameTrait;
use Illuminate\Http\Request;

class CreateLoanProductJob
{
    use GenerateFilenameTrait;

    /**
     * @var Request
     */
    private $request;
    /**
     * @var LoanProduct
     */
    private $loanProduct;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param LoanProduct $loanProduct
     */
    public function __construct(Request $request, LoanProduct $loanProduct = null)
    {
        $this->request = $request;
        $this->loanProduct = $loanProduct ?? new LoanProduct([
                'financial_institution_id' => $request->user()->institutable_id
            ]);
    }

    /**
     * Execute the job.
     *
     * @return LoanProduct|null
     */
    public function handle()
    {
        return $this->addLoanProduct();
    }

    /**
     * @return LoanProduct|null
     * @throws ConflictWithExistingRecord
     */
    private function addLoanProduct()
    {
        $this->checkIsNotExistingProduct();

        // Create or update the product
        $wasUpdated = $this->addProductHelper();

        // Add the product images
        $imageWasAdded = $this->addProductImage();

        return $wasUpdated || $imageWasAdded ? $this->loanProduct : null;
    }

    /**
     * Check if product has already been added
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function checkIsNotExistingProduct(): bool
    {
        $rec = LoanProduct::findByInstitutionAndSlug(
            $this->request->user()->institutable, str_slug($this->request->get('name')), false
        );

        if (empty($rec) || $rec->id == $this->loanProduct->id) {
            return false;
        }

        throw ConflictWithExistingRecord::fromModel($rec);
    }

    /**
     * @return bool
     */
    private function addProductHelper(): bool
    {
        foreach ($this->loanProduct->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $this->loanProduct[$fillable] = $this->request->get($fillable);
            }
        }
        $this->loanProduct->slug = $this->request->get('name', null);

        return $this->loanProduct->save();
    }

    /**
     * @return bool
     */
    private function addProductImage(): bool
    {
        $key = 'image';

        if (!$this->request->hasFile($key) || !$this->request->file($key)->isValid()) {
            return false;
        }

        $bucket = 'products';

        // Delete any existing image for the product  and upload the new file
        $oldImage = $this->loanProduct->images()->first();

        $file = $this->request->file($key);
        $filename = $this->generateFilename($file, $bucket);

        // Hopefully all files upload successfully
        if (!$file->storeAs($bucket, $filename, FileEntry::getStorageDriver())) {
            return false;
        }

        $this->loanProduct->images()->save(new FileEntry([
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'bucket' => $bucket
        ]));

        if ($oldImage) {
            $oldImage->delete();
        }

        return true;
    }
}
