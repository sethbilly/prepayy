<?php

namespace App\Entities;

use App\Jobs\CanApproveLoanApplicationJob;
use Carbon\Carbon;
use CloudLoan\Traits\UuidModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait, Notifiable, UuidModel, SoftDeletes;

    const EMPLOYER_USER_TABLE = 'employer_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'othernames',
        'contact_number',
        'email',
        'dob',
        'ssnit',
        'country_id',
        'institutable_id',
        'institutable_type',
        'approval_level_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'dob',
        'deleted_at'
    ];

    /**
     * Set the password attribute
     * @param string $password
     */
    public function setPasswordAttribute(string $password)
    {
        $this->attributes["password"] = Hash::needsRehash($password)
            ? Hash::make($password) : $password;
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsAppOwner(Builder $query): Builder
    {
        return $query->where('is_app_owner', 1);
    }

    /**
     * @param Carbon|string $val
     */
    public function setDobAttribute($val)
    {
        $date = !($val instanceof Carbon) ? Carbon::parse($val) : $val;

        $this->attributes['dob'] = $date ?? null;
    }

    /**
     * @return MorphTo
     */
    public function institutable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalLevel::class, 'approval_level_id');
    }

    /**
     * True if the user is an application owner
     * @return bool
     */
    public function isApplicationOwner(): bool
    {
        return boolval($this->is_app_owner);
    }

    /**
     * True if the user is the account owner for his/her organization
     * @return bool
     */
    public function isAccountOwner(): bool
    {
        return boolval($this->is_account_owner);
    }

    /**
     * @return bool
     */
    public function hasAccountOrAppOwnerRole(): bool
    {
        return $this->hasRole(Role::ROLE_ACCOUNT_OWNER, Role::ROLE_APP_OWNER);
    }

    /**
     * True if the user is a staff of a financial institution
     * @return bool
     */
    public function isFinancialInstitutionStaff(): bool
    {
        return $this->institutable && $this->institutable instanceof FinancialInstitution;
    }

    /**
     * True if user is a staff of an employer
     * @return bool
     */
    public function isEmployerStaff(): bool
    {
        return $this->institutable && $this->institutable instanceof Employer;
    }

    /**
     * @return bool
     */
    public function isBorrower(): bool
    {
        return is_null($this->institutable) && !$this->isApplicationOwner();
    }

    /**
     * Get full name
     * @return string
     */
    public function getFullName()
    {
        $name = $this->firstname ?? '';

        if (!empty($this->lastname)) {
            $name .= empty($name) ? $this->lastname : ' ' . $this->lastname;
        }

        return $name;
    }

    /**
     * The user's current employer
     * @return BelongsToMany
     */
    public function currentEmployerRelation(): BelongsToMany
    {
        // Current employer is the last added/updated employment details
        // For example: if a user states he works at Callens, then updates to working
        // at QLS and later comes to update to working at Callens, only the updated_at
        // fields change hence should be used in retrieving current employment details
        return $this->employers()->orderBy('pivot_updated_at', 'desc')->orderBy('id',
            'desc')->limit(1);
    }

    /**
     * Returns the user's current employer
     * @return Employer|null
     */
    public function currentEmployer()
    {
        $relation = 'currentEmployerRelation';

        if (!$this->relationLoaded($relation)) {
            $this->load($relation);
        }

        return $this->currentEmployerRelation->first();
    }

    /**
     * List of employers of the user
     * @return BelongsToMany
     */
    public function employers(): BelongsToMany
    {
        return $this->belongsToMany(Employer::class, self::EMPLOYER_USER_TABLE, 'user_id',
            'employer_id')
            ->withPivot(['contract_type', 'position', 'department', 'salary'])
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function currentIdCardRelation(): HasMany
    {
        // Similar to current employer, the last updated id card is the user's current
        // card of identification. For similar updated at times, order by id
        return $this->idCards()->orderBy('updated_at', 'desc')->orderBy('id',
            'desc')->limit(1);
    }

    /**
     * Returns the user's most recently added identification card
     *
     * @return IdentificationCard|null
     */
    public function currentIdCard()
    {
        $relation = 'currentIdCardRelation';

        if (!$this->relationLoaded($relation)) {
            $this->load($relation);
        }


        return $this->currentIdCardRelation->first();
    }

    public function idCards(): HasMany
    {
        return $this->hasMany(IdentificationCard::class, 'user_id');
    }

    public function picture(): MorphOne
    {
        return $this->morphOne(FileEntry::class, 'fileable')->where('bucket',
            FileEntry::AVATAR_UPLOAD_DIRECTORY);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function canEditUser(User $user): bool
    {
        // No one but the account owner himself should be able to edit the account
        // owner's account
        return $user->isAccountOwner() ?
            $this->isAccountOwner() :
            $this->ability([Role::ROLE_ACCOUNT_OWNER, Role::ROLE_APP_OWNER], 'edit-user');
    }

    /**
     * @return HasMany
     */
    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @param int $id
     * @return Employer|null
     */
    public function findEmployer(int $id)
    {
        return $this->employers()->where('id', $id)->first();
    }

    /**
     * Returns true if user has loan approval permission
     * @return bool
     */
    public function isLoanApprover(): bool
    {
        return $this->ability([Role::ROLE_ACCOUNT_OWNER], ['approve-loan-application']);
    }

    /**
     * @return int|null
     */
    public function getApprovalLevelId()
    {
        return $this->approval_level_id;
    }

    /**
     * @param $institutionId
     * @param $institutionType
     * @param array $columns list of columns to return from the User table
     * @return Collection
     */
    public static function getByInstitution(
        $institutionId,
        $institutionType,
        $columns = []
    ) {
        $builder = self::where([
            'institutable_id' => $institutionId,
            'institutable_type' => $institutionType
        ]);

        return !empty($columns) ? $builder->get($columns) : $builder->get();
    }

    /**
     * Returns true if the comparison user belongs to the same institution as the current
     * user
     * @param User $user
     * @return bool
     */
    public function belongsToSameInstitution(User $user): bool
    {
        return $this->institutable_id == $user->institutable_id &&
            $this->institutable_type == $user->institutable_type;
    }

    /**
     * Returns true if user can approve the loan application
     * @param LoanApplication $application
     * @return bool
     */
    public function canApproveLoanApplication(LoanApplication $application): bool
    {
        return dispatch(new CanApproveLoanApplicationJob($this, $application));
    }

    /**
     * Returns the user's date of birth
     * @param string $format
     * @return string
     */
    public function getDob($format = 'd/m/Y'): string
    {
        return $this->dob ? $this->dob->format($format) : '';
    }
}
