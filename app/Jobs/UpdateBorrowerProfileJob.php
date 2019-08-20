<?php

namespace App\Jobs;

use App\Entities\FileEntry;
use App\Entities\IdentificationCard;
use App\Entities\User;
use Carbon\Carbon;
use CloudLoan\Traits\GenerateFilenameTrait;
use Illuminate\Http\Request;

class UpdateBorrowerProfileJob
{
    use GenerateFilenameTrait;

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
     * @return bool
     */
    public function handle()
    {
        return $this->updateProfile();
    }

    private function updateProfile(): bool
    {
        // Return variable
        $return_value = '';
        // Update the basic information
        if ($this->request->has('user')) {
            $req = new Request($this->request->get('user'));
            $req->setUserResolver(function () {
                return $this->request->user();
            });

            dispatch(new AddUserJob($req, $this->user));
        }

        // Use fresh copy of user when adding employment, id cards and picture to
        // keep relations of the auth user unmodified
        $user = $this->user->fresh();

        // Add the employment details
        $employer = $this->addEmploymentDetails($user);

        // Add the id details
        $identification = $this->addIdentificationCard($user);

        // Upload profile picture
        $this->uploadProfilePicture($user);

        // Returning a boolean for the dispatch method
        if($employer && $identification)
        {
            $return_value =  true;
        }

        if(!$employer || !$identification)
        {
            $return_value =  false;
        }

        return $return_value;
    }

    private function addEmploymentDetails(User $user): bool
    {
        $updateEmploymentDetails = $this->request->has('employer') &&
            !empty($this->request->get('employer')) &&
            !empty($this->request->input('employer.id'));

        if (!$updateEmploymentDetails) {
            return false;
        }

        // If the employer has already been added, update it, else add it
        $user->employers()->where('id', $this->request->input('employer.id'))->count() ?
            $user->employers()->updateExistingPivot($this->request->input('employer.id'),
                [
                    'contract_type' => $this->request->input('employer.contract_type'),
                    'position' => $this->request->input('employer.position'),
                    'department' => $this->request->input('employer.department'),
                    'salary' => $this->request->input('employer.salary')
                ]) :
            $user->employers()->attach($this->request->input('employer.id'), [
                'contract_type' => $this->request->input('employer.contract_type'),
                'position' => $this->request->input('employer.position'),
                'department' => $this->request->input('employer.department'),
                'salary' => $this->request->input('employer.salary')
            ]);

        return true;
    }

    private function addIdentificationCard(User $user)
    {
        $return_id_value = '';
        $shouldUpdateIdCard = $this->request->has('id_card') &&
            !empty($this->request->get('id_card')) &&
            !empty($this->request->input('id_card.type'));

        if (!$shouldUpdateIdCard) {
            return false;
        }

        // If the card has already been added, update it, else create it
        $card = $user->idCards()->firstOrNew([
            'type' => $this->request->input('id_card.type')
        ]);

        foreach (['number', 'issue_date', 'expiry_date'] as $field) {
            if ($this->request->has("id_card.{$field}")) {
                $card->{$field} = $this->request->input("id_card.{$field}");
            }
        }

        // Validating the dates on the card
        $date_of_issue = new \DateTime($card->issue_date);
        $date_of_expiry = new \DateTime($card->expiry_date);

        $results = $date_of_issue->diff($date_of_expiry);
        $date_interval = $results->format('%R%a');
        if ($date_interval > 0) {
            $card->save();
            $return_id_value = true;
        }elseif ($date_interval < 0){
            $return_id_value =  false;
        }

        return $return_id_value;
    }


    /**
     * @param User $user
     * @return bool
     */
    private function uploadProfilePicture(User $user): bool
    {
        $key = 'picture';

        if (!$this->request->hasFile($key)) {
            return false;
        }

        $filename = $this->generateFilename($this->request->file($key),
            FileEntry::AVATAR_UPLOAD_DIRECTORY);

        $this->request->file($key)->storeAs(FileEntry::AVATAR_UPLOAD_DIRECTORY, $filename,
            FileEntry::getStorageDriver());

        $oldPicture = $user->picture;

        $oldPicture ?
            $user->picture()->update([
                'filename' => $filename,
                'bucket' => FileEntry::AVATAR_UPLOAD_DIRECTORY,
                'original_filename' => $this->request->file($key)->getClientOriginalName(),
                'mime' => $this->request->file($key)->getMimeType()
            ]) :
            $user->picture()->create([
                'filename' => $filename,
                'bucket' => FileEntry::AVATAR_UPLOAD_DIRECTORY,
                'original_filename' => $this->request->file($key)->getClientOriginalName(),
                'mime' => $this->request->file($key)->getMimeType()
            ]);

        if ($oldPicture) {
            FileEntry::deleteCachedUrl($oldPicture);
        }

        return true;
    }
}
