<?php

use App\Entities\ApprovalLevel;
use App\Entities\Employer;
use App\Entities\FileEntry;
use App\Entities\FinancialInstitution;
use App\Entities\Guarantor;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\Permission;
use App\Entities\Role;
use App\Entities\User;
use CloudLoan\Traits\UuidModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    public function test_will_encrypt_raw_password()
    {
        $password = 'User Password';
        $this->user->password = $password;

        $this->assertNotEquals($password, $this->user->password);
        $this->assertFalse(Hash::needsRehash($this->user->password));
    }

    public function test_can_get_uuid()
    {
        $this->assertNotNull($this->user->uuid);
        $this->assertTrue(preg_match(UuidModel::$uuid4Pattern, $this->user->uuid) == 1);
    }

    public function test_is_application_owner()
    {
        $this->assertFalse($this->user->isApplicationOwner());

        $this->user->is_app_owner = 1;

        $this->assertTrue($this->user->isApplicationOwner());
    }

    public function test_is_account_owner()
    {
        $this->assertFalse($this->user->isAccountOwner());

        $this->user->is_account_owner = 1;

        $this->assertTrue($this->user->isAccountOwner());
    }

    public function test_is_financial_institution_staff()
    {
        $this->assertFalse($this->user->isFinancialInstitutionStaff());
        $this->assertNull($this->user->institutable);

        $partner = factory(FinancialInstitution::class)->create();
        $this->user->update([
            'institutable_id' => $partner->id,
            'institutable_type' => $partner->getMorphClass()
        ]);

        // Reload the relations
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->isFinancialInstitutionStaff());
        $this->assertEquals($partner->id, $this->user->institutable->id);
    }

    public function test_is_employer_staff()
    {
        $this->assertFalse($this->user->isEmployerStaff());
        $this->assertNull($this->user->institutable);

        $employer = factory(Employer::class)->create();
        $this->user->update([
            'institutable_id' => $employer->id,
            'institutable_type' => $employer->getMorphClass()
        ]);

        // Reload the relations
        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->isEmployerStaff());
        $this->assertEquals($employer->id, $this->user->institutable->id);
    }

    public function test_is_borrower()
    {
        $this->assertTrue($this->user->isBorrower());
        $this->assertNull($this->user->institutable);
    }

    public function test_can_get_approval_level()
    {
        $this->assertNull($this->user->approvalLevel);

        $approvalLevel = factory(ApprovalLevel::class)->create([
            'institutable_id' => $this->user->institutable_id,
            'institutable_type' => $this->user->institutable_type
        ]);

        $this->user->update(['approval_level_id' => $approvalLevel->id]);
        $this->user = $this->user->fresh();

        $this->assertInstanceOf(ApprovalLevel::class, $this->user->approvalLevel);
        $this->assertEquals($approvalLevel->id, $this->user->approvalLevel->id);
    }

    private function addEmployers(): Collection
    {
        $employers = factory(Employer::class, 2)->create();
        $faker = Faker\Factory::create();

        DB::table('employer_user')->insert([
            [
                'user_id' => $this->user->id,
                'employer_id' => $employers->first()->id,
                'position' => $faker->jobTitle,
                'contract_type' => 'Full Time'
            ],
            [
                'user_id' => $this->user->id,
                'employer_id' => $employers->last()->id,
                'position' => $faker->jobTitle,
                'contract_type' => 'Part Time'
            ]
        ]);

        return $employers;
    }

    public function test_can_get_users_employers()
    {
        $employers = $this->addEmployers();

        $this->assertCount(2, $this->user->employers);

        $this->user->employers->each(function(Employer $emp, $i) use ($employers) {
            $this->assertEquals($emp->id, $employers[$i]->id);
        });
    }

    public function test_can_get_current_employer()
    {
        $this->assertNull($this->user->currentEmployer());

        $employers = $this->addEmployers();

        $this->user = $this->user->fresh();
        $this->assertInstanceOf(Employer::class, $this->user->currentEmployer());
        $this->assertEquals($employers->last()->id, $this->user->currentEmployer()->id);
    }

    public function test_can_get_users_identification_cards()
    {
        $cards = factory(IdentificationCard::class, 2)->create([
            'user_id' => $this->user->id
        ]);
        
        $this->assertCount(2, $this->user->idCards);
        
        $cards->each(function(IdentificationCard $card, $i) {
            $this->assertEquals($card->id, $this->user->idCards[$i]->id);
        });
    }

    public function test_can_get_current_identification_card()
    {
        $this->assertNull($this->user->currentIdCard());

        $cards = factory(IdentificationCard::class, 2)->create([
            'user_id' => $this->user->id
        ]);

        $this->user = $this->user->fresh();

        $this->assertInstanceOf(IdentificationCard::class, $this->user->currentIdCard());
        $this->assertEquals($cards->last()->id, $this->user->currentIdCard()->id);
    }

    public function test_can_get_profile_picture()
    {
        $this->assertNull($this->user->picture);
        
        $file = factory(FileEntry::class)->create([
            'bucket' => FileEntry::AVATAR_UPLOAD_DIRECTORY,
            'fileable_id' => $this->user->id,
            'fileable_type' => $this->user->getMorphClass()
        ]);

        $this->user = $this->user->fresh(['picture']);

        $this->assertInstanceOf(FileEntry::class, $this->user->picture);
        $this->assertEquals($file->id, $this->user->picture->id);
    }

    public function test_can_edit_user()
    {
        // Create non-account owner with edit-user permissions
        // Assert that user can edit all non-account owner accounts
        $role = factory(Role::class)->create(['name' => 'User Manager']);
        $perms = Permission::whereIn('name', ['add-user', 'edit-user'])->get();
        $role->attachPermissions($perms);

        $this->user->attachRole($role);

        $owner = factory(User::class)->create(['is_account_owner' => 1]);
        $nonOwner = factory(User::class)->create();

        $this->assertTrue($this->user->canEditUser($nonOwner));
        $this->assertFalse($this->user->canEditUser($owner));

        // Assert that an account owner can edit his account as well as everybody else's
        $this->user->is_account_owner = true;
        $this->user->save();

        $this->assertTrue($this->user->canEditUser($nonOwner));
        $this->assertTrue($this->user->canEditUser($owner));
    }

    public function test_can_get_fullname()
    {
        $this->assertEquals($this->user->firstname . ' ' . $this->user->lastname, $this->user->getFullName());
    }

    public function test_can_get_loan_applications()
    {
        $this->assertCount(0, $this->user->loanApplications);

        $applications = factory(LoanApplication::class, 2)->create(['user_id' => $this->user->id]);

        $this->user = $this->user->fresh();

        $this->assertCount(2, $this->user->loanApplications);

        $applications->each(function(LoanApplication $application, $i) {
            $this->assertEquals($application->id, $this->user->loanApplications[$i]->id);
        });
    }

    public function test_can_get_guarantors()
    {
        $this->assertCount(0, $this->user->guarantors);

        $guarantors = factory(Guarantor::class, 2)->create(['user_id' => $this->user->id]);

        $this->user = $this->user->fresh();

        $this->assertCount(2, $this->user->guarantors);

        $guarantors->each(function(Guarantor $guarantor, $i) {
            $this->assertEquals($guarantor->id, $this->user->guarantors[$i]->id);
        });
    }

    public function test_can_get_user_by_institution()
    {
        $employers = factory(Employer::class, 2)->create()
            ->each(function (Employer $employer) {
                return factory(User::class, 'employer', 2)->create([
                    'institutable_id' => $employer->id
                ]);
            });
        $employer = $employers->first();

        $users = User::getByInstitution($employer->id, $employer->getMorphClass());

        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);

        $users->each(function (User $user) use ($employer) {
           $this->assertEquals($user->institutable_id, $employer->id);
           $this->assertEquals($user->institutable_type, $employer->getMorphClass());
        });
    }

    public function test_can_get_specified_columns_using_by_institution()
    {
        $employer = factory(Employer::class)->create();
        factory(User::class, 'employer', 2)->create([
            'institutable_id' => $employer->id
        ]);

        $users = User::getByInstitution($employer->id, $employer->getMorphClass(), ['id']);

        $this->assertCount(2, $users);

        $users->each(function ($user) {
            $columns = array_keys($user->toArray());
            $this->assertCount(1, $columns);
            $this->assertEquals('id', $columns[0]);
        });
    }
}
