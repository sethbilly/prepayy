<?php

namespace App\Jobs;

use App\Entities\User;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Http\Request;

class AddUserJob
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
     * @param User $user
     */
    public function __construct(Request $request, User $user)
    {
        $this->request = $request;
        $this->user = $this->restoreUserIfDeleted($user);
    }

    /**
     * If the user to add/update has been deleted,
     * @param User $user
     * @return User
     */
    private function restoreUserIfDeleted(User $user): User
    {
        $deletedUser = User::onlyTrashed()
            ->where('email', $this->request->get('email'))
            ->first();

        if (empty($deletedUser)) {
            return $user;
        }

        if (!$user->belongsToSameInstitution($deletedUser)) {
            throw ConflictWithExistingRecord::fromModel($deletedUser);
        }

        $deletedUser->restore();

        return $deletedUser;
    }

    /**
     * Execute the job.
     *
     * @return User|null
     */
    public function handle()
    {
        return $this->addUser() ? $this->user : null;
    }

    /**
     * @return bool
     */
    private function addUser(): bool
    {

        $this->ensureIsNotAccountConflict();

        foreach ($this->user->getFillable() as $fillable) {
            if ($this->request->has($fillable)) {
                $this->user[$fillable] = $this->request->get($fillable);
            }
        }

        if ($this->request->has('password')) {
            $this->user->password = $this->request->get('password');
        }

        return $this->user->save();
    }

    private function ensureIsNotAccountConflict()
    {
        //return $this->isConflictingEmail() || $this->isConflictingPhoneNumber();
        return $this->isConflictingEmail();
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function isConflictingEmail(): bool
    {
        $rec = User::withTrashed()
            ->where(['email' => $this->request->get('email')])
            ->first();

        if (empty($rec) || ($this->user->id == $rec->id)) {
            return false;
        }

        $message = "Account conflict! User with email ({$rec->email}) exists";
        throw ConflictWithExistingRecord::fromModel($rec, $message);
    }

    /**
     * @return bool
     * @throws ConflictWithExistingRecord
     */
    private function isConflictingPhoneNumber(): bool
    {
        $rec = User::withTrashed()
            ->where(['contact_number' => $this->request->get('contact_number')])
            ->first();

        if (empty($rec) || ($this->user->id == $rec->id)) {
            return false;
        }

        $message = "Account conflict! User with phone number ({$rec->contact_number}) exists";
        throw ConflictWithExistingRecord::fromModel($rec, $message);   
    }
}
