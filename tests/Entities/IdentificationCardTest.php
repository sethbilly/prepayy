<?php

use App\Entities\IdentificationCard;
use App\Entities\User;

class IdentificationCardTest extends TestCase
{
    /**
     * @var IdentificationCard
     */
    private $card;

    public function setUp()
    {
        parent::setUp();
        $this->card = factory(IdentificationCard::class)->create();
    }

    public function test_can_get_card_owner()
    {
        $this->assertInstanceOf(User::class, $this->card->owner);
    }
}
