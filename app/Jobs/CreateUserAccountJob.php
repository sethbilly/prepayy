<?php

namespace App\Jobs;

use App\Entities\ApprovalLevel;
use App\Entities\Role;
use App\Entities\User;
use App\Notifications\UserCreated;
use App\Notifications\UserPasswordChangeRequested;
use CloudLoan\Exceptions\ConflictWithExistingRecord;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class CreateUserAccountJob
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
     * @var bool
     */
    private $generatePassword;
    /**
     * @var bool
     */
    private $isNewAccount;

    /**
     * @var PasswordBroker
     */
    private $broker;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param User $user
     */
    public function __construct(Request $request, User $user = null)
    {
        $this->request = $request;
        $this->user = $user ?? new User([
                'institutable_id' => $request->user()->institutable_id,
                'institutable_type' => $request->user()->institutable_type,
            ]);
        $this->isNewAccount = empty($this->user->id);
        $this->generatePassword = $this->isNewAccount || $request->has('generate_password');
    }

    /**
     * Execute the job.
     *
     * @param PasswordBroker $broker
     * @return User
     */
    public function handle(PasswordBroker $broker)
    {
        $this->broker = $broker;
        $this->addUser();

        return $this->user;
    }

    /**
     * @return bool
     */
    private function addUser(): bool
    {
        return DB::transaction(function() {
            // Set is app owner to true for app owner accounts
            $this->user->is_app_owner = $this->request->user()->isApplicationOwner() ? 1 : 0;

            $this->user = dispatch(new AddUserJob($this->request, $this->user));

            // Users with app owner or account owner roles cannot be modified
            if (!$this->user->hasRole(Role::ROLE_ACCOUNT_OWNER, Role::ROLE_APP_OWNER)) {
                $this->ensureLoanApproverIsAssignedAnApprovalLevel();
                $this->user->roles()->sync($this->request->get('roles', []));
            }

            // Send password settings notification
            $this->sendPasswordNotification();

            return true;
        });
    }

    /**
     * Send password settings notification
     * @return bool
     */
    private function sendPasswordNotification()
    {
        if (!$this->generatePassword) {
            return false;
        }
        $token = $this->broker->getRepository()->create($this->user);

        $this->isNewAccount ?
            $this->user->notify(new UserCreated($token)) :
            $this->user->notify(new UserPasswordChangeRequested($token));
    }

    /**
     * If institution has approval levels and user is assigned loan approval role,
     * ensure user is assigned to an approval level
     */
    private function ensureLoanApproverIsAssignedAnApprovalLevel()
    {
        $approvalLevels = ApprovalLevel::where([
            'institutable_id' => $this->user->institutable_id,
            'institutable_type' => $this->user->institutable_type
        ])->get();

        if ($approvalLevels->isEmpty()) {
            return;
        }

        $roles = Role::whereIn('id', $this->request->get('roles', []))->get();

        $roles->each(function (Role $role) use ($approvalLevels) {
            if (!$role->canApproveLoans()) {
                return;
            }

            $approvalLevel = $approvalLevels->first(function (ApprovalLevel $level) {
               return $level->id == $this->request->get('approval_level_id');
            });

            if (empty($approvalLevel)) {
                $message = 'Approval level to assign user to is required!';
                throw new MissingMandatoryParametersException($message);
            }
        });
    }
}
