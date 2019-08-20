<?php

use App\Entities\Guarantor;
use App\Entities\User;

class GuarantorTest extends TestCase
{
    /**
     * @var Guarantor
     */
    private $guarantor;

    public function setUp()
    {
        parent::setUp();
        $this->guarantor = factory(Guarantor::class)->create();
    }

    public function test_can_get_user_being_guaranteed()
    {
        $this->assertInstanceOf(User::class, $this->guarantor->user);
    }
}
