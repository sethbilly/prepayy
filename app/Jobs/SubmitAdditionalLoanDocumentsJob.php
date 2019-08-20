<?php

namespace App\Jobs;

use App\Entities\FileEntry;
use App\Entities\RequestedLoanDocument;
use App\Notifications\LoanDocumentSubmitted;
use CloudLoan\Traits\GenerateFilenameTrait;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class SubmitAdditionalLoanDocumentsJob
{
    use GenerateFilenameTrait;
    
    /**
     * @var Request
     */
    private $request;
    /**
     * @var RequestedLoanDocument
     */
    private $document;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param RequestedLoanDocument $document
     */
    public function __construct(Request $request, RequestedLoanDocument $document)
    {
        $this->request = $request;
        $this->document = $document;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->submitAdditionalDocuments();
    }

    /**
     * @return bool
     */
    private function submitAdditionalDocuments(): bool
    {
        $wasUpdated = false;

        if ($this->request->has('response')) {
            $wasUpdated = $this->document->update(['response' => $this->request->get('response')]);
        }

        $filesWereUploaded = $this->uploadFileAttachments();

        if ($wasUpdated || $filesWereUploaded) {
            $this->document->user->notify(new LoanDocumentSubmitted($this->document));
        }

        return $wasUpdated || $filesWereUploaded;
    }

    /**
     * @return bool
     */
    private function uploadFileAttachments(): bool
    {
        $key = 'files';

        if (!$this->request->hasFile($key)) {
            return false;
        }

        $files = collect($this->request->file($key))
            // Get valid uploaded files
            ->filter(function(UploadedFile $file) {
                return $file->isValid();
            })
            // Save the file in the database
            ->map(function(UploadedFile $file) {
                $filename = $this->generateFilename($file, FileEntry::LOAN_DOCUMENTS_DIRECTORY);

                if ($file->storeAs(FileEntry::LOAN_DOCUMENTS_DIRECTORY, $filename, FileEntry::getStorageDriver())) {
                    return new FileEntry([
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'bucket' => FileEntry::getStorageDriver()
                    ]);
                }
            })
            ->filter(function($rec) {
                return $rec instanceof FileEntry;
            });

        if ($files->isEmpty()) {
            return false;
        }

        $this->document->files()->saveMany($files->all());

        return true;
    }
}
