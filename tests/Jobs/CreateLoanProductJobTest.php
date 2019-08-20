<?php

use App\Entities\FileEntry;
use App\Entities\LoanProduct;
use App\Entities\User;
use App\Jobs\CreateLoanProductJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class CreateLoanProductJobTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait, GetMockUploadedFileTrait;

    /**
     * @var Faker\Generator
     */
    private $faker;
    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();
        $this->user = $this->createInstitutionAccountOwner();
    }

    private function getRequest(): Request
    {
        return $this->getAuthenticatedRequest([
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'min_amount' => $this->faker->numberBetween(100, 500),
            'max_amount' => $this->faker->numberBetween(1000, 2000),
            'interest_per_year' => $this->faker->numberBetween(1, 12)
        ], $this->user);
    }

    public function test_can_create_loan_product()
    {
        $mockUpload = $this->getMockUploadedFile(__FILE__);
        $mockRequest = Mockery::mock($this->getRequest());

        $mockRequest->shouldReceive('hasFile')->once()->with('image')->andReturn(true);
        $mockRequest->shouldReceive('file')->with('image')->andReturn($mockUpload);

        $loanProduct = dispatch(new CreateLoanProductJob($mockRequest));

        $this->assertInstanceOf(LoanProduct::class, $loanProduct);
        $this->assertEquals($mockRequest->get('name'), $loanProduct->name);
        $this->assertEquals($mockRequest->get('description'), $loanProduct->description);
        $this->assertEquals($mockRequest->get('min_amount'), $loanProduct->min_amount);
        $this->assertEquals($mockRequest->get('max_amount'), $loanProduct->max_amount);
        $this->assertEquals($mockRequest->get('interest_per_year'), $loanProduct->interest_per_year);
        $this->assertCount(1, $loanProduct->images);
    }

    /**
     * @expectedException CloudLoan\Exceptions\ConflictWithExistingRecord
     */
    public function test_cannot_add_duplicate_product()
    {
        $product = factory(LoanProduct::class)->create([
            'financial_institution_id' => $this->user->institutable_id
        ]);

        $req = $this->getRequest();
        $req->merge(['name' => $product->name]);

        dispatch(new CreateLoanProductJob($req));
    }

    public function test_can_update_loan_product()
    {
        // Create a product with one image
        $product = factory(LoanProduct::class)->create([
            'financial_institution_id' => $this->user->institutable_id
        ]);
        $oldImage = $product->images()->save(factory(FileEntry::class)->make());

        // Update the product and its image
        $mockUpload = $this->getMockUploadedFile(__FILE__);
        $mockRequest = Mockery::mock($this->getRequest());

        $mockRequest->shouldReceive('hasFile')->once()->with('image')->andReturn(true);
        $mockRequest->shouldReceive('file')->with('image')->andReturn($mockUpload);

        $loanProduct = dispatch(new CreateLoanProductJob($mockRequest, $product));

        $this->assertInstanceOf(LoanProduct::class, $loanProduct);
        $this->assertEquals($product->id, $loanProduct->id);
        $this->assertEquals($mockRequest->get('name'), $loanProduct->name);
        $this->assertEquals($mockRequest->get('description'), $loanProduct->description);
        $this->assertEquals($mockRequest->get('min_amount'), $loanProduct->min_amount);
        $this->assertEquals($mockRequest->get('max_amount'), $loanProduct->max_amount);
        $this->assertEquals($mockRequest->get('interest_per_year'), $loanProduct->interest_per_year);
        $this->assertCount(1, $loanProduct->images);
        $this->assertNotEquals($oldImage->id, $loanProduct->images->first()->id);
    }
}
