<?php

use App\Entities\Country;
use App\Entities\Employer;
use App\Entities\IdentificationCard;
use App\Entities\LoanApplication;
use App\Entities\LoanProduct;
use App\Entities\User;
use App\Http\Controllers\BorrowersController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowersControllerTest extends TestCase
{
    use GetMockUploadedFileTrait;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Faker\Generator
     */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->faker = Faker\Factory::create();
    }

    public function test_can_update_borrower_basic_info()
    {
        $details = [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'othernames' => $this->faker->name,
            'contact_number' => $this->faker->phoneNumber,
            'ssnit' => $this->faker->creditCardNumber
        ];

        $this->actingAs($this->user)
            ->visitRoute('user.profile.index')
            ->type($details['firstname'], 'user[firstname]')
            ->type($details['lastname'], 'user[lastname]')
            ->type($details['othernames'], 'user[othernames]')
            ->type($details['contact_number'], 'user[contact_number]')
            ->type($details['ssnit'], 'user[ssnit]')
            ->press('Save changes')
            ->assertResponseOk();

        $this->user = $this->user->fresh();

        foreach ($details as $key => $val) {
            $this->assertEquals($val, $this->user->{$key});
        }
    }

    public function test_can_update_borrower_employment()
    {
        $emps = factory(Employer::class, 2)->create();

        $details = [
            'id' => $emps->first()->id,
            'contract_type' => 'Full Time',
            'position' => $this->faker->jobTitle,
            'department' => $this->faker->sentence,
            'salary' => $this->faker->numberBetween(1000, 5000)
        ];

        $this->actingAs($this->user)
            ->visitRoute('user.profile.index')
            ->select($details['id'], 'employer[id]')
            ->select($details['contract_type'], 'employer[contract_type]')
            ->type($details['position'], 'employer[position]')
            ->type($details['department'], 'employer[department]')
            ->type($details['salary'], 'employer[salary]')
            ->press('Save changes')
            ->assertResponseOk();

        $this->user = $this->user->fresh(['employers']);

        foreach ($details as $key => $val) {
            if ($key == 'id') {
                $this->assertEquals($val, $this->user->employers->first()->id);
            } else {
                $this->assertEquals($val, $this->user->employers->first()->pivot->{$key});
            }
        }
    }

    public function test_can_update_borrower_identification_card()
    {
        $idTypes = IdentificationCard::getIdentificationTypes();

        $details = [
            'type' => $idTypes[0],
            'number' => $this->faker->bankAccountNumber,
            'issue_date' => $this->faker->date('d-m-Y'),
            'expiry_date' => $this->faker->date('d-m-Y')
        ];

        $this->actingAs($this->user)
            ->visitRoute('user.profile.index')
            ->select($details['type'], 'id_card[type]')
            ->type($details['number'], 'id_card[number]')
            ->type($details['issue_date'], 'id_card[issue_date]')
            ->type($details['expiry_date'], 'id_card[expiry_date]')
            ->press('Save changes')
            ->assertResponseOk();

        $this->user = $this->user->fresh(['idCards']);

        foreach ($details as $key => $val) {
            if (in_array($key, ['issue_date', 'expiry_date'])) {
                $this->assertEquals($val, $this->user->idCards->first()->{$key}->format('d-m-Y'));
            } else {
                $this->assertEquals($val, $this->user->idCards->first()->{$key});
            }
        }
    }

    public function test_will_populate_borrower_profile_form()
    {
        $this->user->dob = Carbon::now()->subYears(rand(10, 20));
        $this->user->country()->associate(Country::first());

        $this->actingAs($this->user)
            ->visitRoute('user.profile.index')
            ->seeInField('user[firstname]', $this->user->firstname)
            ->seeInField('user[lastname]', $this->user->lastname)
            ->seeInField('user[othernames]', $this->user->othernames)
            ->seeInField('user[contact_number]', $this->user->contact_number)
            ->seeInField('user[ssnit]', $this->user->ssnit)
            ->seeInField('user[dob]', $this->user->dob->format('d-m-Y'))
            ->seeIsSelected('user[country_id]', $this->user->country->id)
            ->seeElement('select[name="employer[id]"]')
            ->seeElement('select[name="employer[contract_type]"]')
            ->seeElement('input[name="employer[position]"]')
            ->seeElement('input[name="employer[department]"]')
            ->seeElement('select[name="id_card[type]"]')
            ->seeElement('input[name="id_card[issue_date]"]')
            ->seeElement('input[name="id_card[expiry_date]"]')
            ->seeElement('input[name="id_card[number]"]');
    }
}
