<?php

use App\Entities\Employer;
use App\Entities\FileEntry;
use App\Entities\IdentificationCard;
use App\Entities\User;
use App\Jobs\UpdateBorrowerProfileJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Mock;

class UpdateBorrowerProfileJobTest extends TestCase
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

    private function getBasicDetails(): array
    {
        return [
            'user' => [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'othernames' => $this->faker->name,
                'contact_number' => $this->faker->phoneNumber,
                'dob' => $this->faker->date(),
                'ssnit' => $this->faker->creditCardNumber()
            ]
        ];
    }

    private function getEmploymentDetails(): array
    {
        return [
            'employer' => [
                'id' => factory(Employer::class)->create()->id,
                'contract_type' => 'Full Time',
                'position' => $this->faker->company,
                'salary' => 2000
            ]
        ];
    }

    private function getCardDetails(): array
    {
        return [
            'id_card' => [
                'type' => $this->faker->text,
                'number' => $this->faker->creditCardNumber,
                'issue_date' => $this->faker->date(),
                'expiry_date' => $this->faker->date()
            ]
        ];
    }

    private function getRequest(array $data): Request
    {
        $req = new Request($data);

        $req->setUserResolver(function () {
            return $this->user;
        });

        return $req;
    }

    public function test_can_update_basic_information()
    {
        $mockFile = $this->getMockUploadedFile(__FILE__);
        $mockReq = Mockery::mock($this->getRequest($this->getBasicDetails()));

        $mockReq->shouldReceive('hasFile')->andReturn(true);
        $mockReq->shouldReceive('file')->andReturn($mockFile);

        $wasUpdated = dispatch(new UpdateBorrowerProfileJob($mockReq));

        $this->assertTrue($wasUpdated);
        $this->assertEquals($mockReq->input('user.firstname'), $this->user->firstname);
        $this->assertEquals($mockReq->input('user.lastname'), $this->user->lastname);
        $this->assertEquals($mockReq->input('user.othernames'), $this->user->othernames);
        $this->assertEquals($mockReq->input('user.contact_number'), $this->user->contact_number);
        $this->assertEquals($mockReq->input('user.dob'), $this->user->dob->format('Y-m-d'));
        $this->assertEquals($mockReq->input('user.ssnit'), $this->user->ssnit);
        $this->assertInstanceOf(FileEntry::class, $this->user->picture);
    }


    public function test_can_add_employment_details()
    {
        $req = $this->getRequest($this->getEmploymentDetails());

        $wasUpdated = dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertTrue($wasUpdated);
        $this->assertEquals($req->input('employer.id'), $this->user->employers->first()->id);
        $this->assertEquals(
            $req->input('employer.contract_type'),
            $this->user->employers->first()->pivot->contract_type
        );
        $this->assertEquals(
            $req->input('employer.position'),
            $this->user->employers->first()->pivot->position
        );
        $this->assertEquals(
            $req->input('employer.salary'),
            $this->user->employers->first()->pivot->salary
        );
    }

    public function test_can_add_identification_card()
    {
        $req = $this->getRequest($this->getCardDetails());

        $wasUpdated = dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertTrue($wasUpdated);
        $this->assertEquals($req->input('id_card.type'), $this->user->idCards->first()->type);
        $this->assertEquals($req->input('id_card.number'), $this->user->idCards->first()->number);
        $this->assertEquals($req->input('id_card.issue_date'),
            $this->user->idCards->first()->issue_date->format('Y-m-d'));
        $this->assertEquals($req->input('id_card.expiry_date'),
            $this->user->idCards->first()->expiry_date->format('Y-m-d'));
    }

    public function test_will_update_existing_id_card()
    {
        $card = factory(IdentificationCard::class)->create([
            'user_id' => $this->user->id
        ]);
        $details = $this->getCardDetails();
        $details['id_card']['type'] = $card->type;
        $req = $this->getRequest($details);

        dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertCount(1, $this->user->idCards);
        $this->assertNotEquals($card->number, $this->user->idCards->first()->number);
        $this->assertEquals($req->input('id_card.number'), $this->user->idCards->first()->number);
        $this->assertEquals($req->input('id_card.type'), $this->user->idCards->first()->type);
    }

    public function test_will_not_override_existing_id_card()
    {
        $card = factory(IdentificationCard::class)->create(['user_id' => $this->user->id]);
        $req = $this->getRequest($this->getCardDetails());

        dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertCount(2, $this->user->idCards);
        $this->assertEquals($card->id, $this->user->idCards->first()->id);
        $this->assertNotEquals($card->id, $this->user->idCards->last()->id);
        $this->assertNotEquals($card->number, $this->user->idCards->last()->number);
        $this->assertEquals($req->input('id_card.number'), $this->user->idCards->last()->number);
        $this->assertEquals($req->input('id_card.type'), $this->user->idCards->last()->type);
    }

    public function test_will_update_existing_picture()
    {
        $file = factory(FileEntry::class)->create([
            'fileable_id' => $this->user->id,
            'fileable_type' => $this->user->getMorphClass()
        ]);
        $mockFile = $this->getMockUploadedFile(__FILE__);
        $mockReq = Mockery::mock($this->getRequest([]));

        $mockReq->shouldReceive('hasFile')->andReturn(true);
        $mockReq->shouldReceive('file')->andReturn($mockFile);

        dispatch(new UpdateBorrowerProfileJob($mockReq));

        $this->assertNotEquals($file->filename, $this->user->picture->filename);
    }

    public function test_will_update_existing_employment_details()
    {
        $employer = factory(Employer::class)->create();

        DB::table('employer_user')->insert([
            'employer_id' => $employer->id,
            'position' => $this->faker->jobTitle,
            'contract_type' => 'Part Time',
            'user_id' => $this->user->id
        ]);

        $details = $this->getEmploymentDetails();
        $details['employer']['id'] = $employer->id;

        $req = $this->getRequest($details);

        dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertCount(1, $this->user->employers);
        $this->assertNotEquals('Part Time', $this->user->employers->first()->contract_type);
        $this->assertEquals($req->input('employer.position'), $this->user->employers->first()->pivot->position);
        $this->assertEquals($req->input('employer.contract_type'),
            $this->user->employers->first()->pivot->contract_type);
    }

    public function test_will_not_delete_existing_employer()
    {
        $employer = factory(Employer::class)->create();

        DB::table('employer_user')->insert([
            'employer_id' => $employer->id,
            'position' => $this->faker->jobTitle,
            'contract_type' => 'Part Time',
            'user_id' => $this->user->id
        ]);

        $req = $this->getRequest($this->getEmploymentDetails());

        dispatch(new UpdateBorrowerProfileJob($req));

        $this->assertCount(2, $this->user->employers);
        $this->assertEquals($employer->id, $this->user->employers->first()->id);
        $this->assertNotEquals($employer->id, $this->user->employers->last()->id);
        $this->assertNotEquals($req->input('employer.contract_type'), $this->user->employers->first()->contract_type);
        $this->assertEquals($req->input('employer.position'), $this->user->employers->last()->pivot->position);
        $this->assertEquals($req->input('employer.contract_type'), $this->user->employers->last()->pivot->contract_type);
    }
}
