<?php

use App\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    use DatabaseMigrationsForSqlite, DatabaseTransactions;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->runMigrations();
    }

    /**
     * @param array $data
     * @param User|null $user
     * @return Request
     */
    protected function getAuthenticatedRequest(array $data = [], User $user = null) {
        $user = $user ?? factory(User::class)->create();

        $req = new Request($data);

        $req->setUserResolver(function() use ($user) { return $user; });

        return $req;
    }
}
