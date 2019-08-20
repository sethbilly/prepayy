<?php

use App\Entities\Employer;
use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EmployerTest extends TestCase
{
    use DatabaseTransactions, CreatesUsersTrait;

    /**
     * @var Employer
     */
    private $employer;

    public function setUp()
    {
        parent::setUp();
        $this->employer = factory(Employer::class)->create();
    }

    public function test_will_slugify_raw_slug()
    {
        $slug = 'The new slug';
        $this->employer->slug = $slug;

        $this->assertEquals(str_slug($slug), $this->employer->slug);
    }

    public function test_can_get_creator()
    {
        $this->assertInstanceOf(User::class, $this->employer->creator);
    }

    public function test_can_get_account_owner()
    {
        $this->assertNull($this->employer->accountOwner);

        $user = $this->createEmployerAccountOwner($this->employer);

        $this->employer = $this->employer->fresh();
        $this->assertInstanceOf(User::class, $this->employer->accountOwner);
        $this->assertEquals($user->id, $this->employer->accountOwner->id);
    }

    public function test_can_get_route_key_name()
    {
        $this->assertEquals('slug', $this->employer->getRouteKeyName());
    }

    public function test_can_get_employers()
    {
        $this->assertCount(0, $this->employer->staffMembers);

        $users = factory(User::class, 4)->create()
            ->filter(function ($user, $i) {
                return $i < 2;
            })
            ->map(function (User $user) {
                $user->update([
                    'institutable_id' => $this->employer->id,
                    'institutable_type' => $this->employer->getMorphClass(),
                ]);

                return $user;
            });

        $this->employer = $this->employer->fresh(['staffMembers']);
        $this->assertCount(2, $this->employer->staffMembers);

        $users->each(function (User $user, $i) {
            $this->assertEquals($user->id, $this->employer->staffMembers[$i]->id);
        });
    }
}
