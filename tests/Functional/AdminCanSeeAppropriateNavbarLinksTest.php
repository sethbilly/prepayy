<?php
use App\Entities\User;
use CloudLoan\ViewComposers\SidebarNavigationLinksViewComposer;

/**
 * Created by PhpStorm.
 * User: benjaminmanford
 * Date: 2/13/17
 * Time: 2:19 PM
 */

class AdminCanSeeAppropriateNavbarLinksTest extends TestCase
{
    use CreatesUsersTrait, InvokePrivateFieldsAndMethodsTrait;

    private $faker;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->runMigrations();

        $this->faker = \Faker\Factory::create();
    }

    private function getLinksFromLangFile()
    {
        $composer = new SidebarNavigationLinksViewComposer();

        $links = $this->invokeMethod($composer, 'getSidebarNavigationLinks');

        return $links;
    }

    private function getLinksForLoggedInUser(User $user)
    {
        auth()->login($user);

        return $this->getLinksFromLangFile();
    }

    /**
     * Test if callens admin sees appropriate sidebar navigation links
     */
    public function test_callens_admin_can_see_appropriate_links()
    {
        $links = $this->getLinksForLoggedInUser($this->createApplicationOwner());

        $this->visitRoute('roles.index');
        $this->assertCount(5, $links);
        $this->assertEquals('Partners', $links->first()->title);
        // Penultimate item
        $this->assertEquals('users.index', $links->get($links->count() - 2)->route);
        $this->assertEquals('loan_products.types.index', $links->last()->route);
    }

    /**
     * Test if financial institution admin sees appropriate sidebar navigation links
     */
    public function test_partner_admin_can_see_appropriate_links()
    {
        $links = $this->getLinksForLoggedInUser($this->createInstitutionAccountOwner());

        $this->visitRoute('roles.index');
        $this->assertCount(6, $links);
        $this->assertEquals('Roles', $links->first()->title);
        $this->assertEquals('loan_applications.index', $links->last()->route);
    }

    /**
     * Test if employer admin sees appropriate sidebar navigation links
     */
    public function test_employer_admin_can_see_appropriate_links()
    {
        $links = $this->getLinksForLoggedInUser($this->createEmployerAccountOwner());

        $this->visitRoute('roles.index');
        $this->assertCount(4, $links);
        $this->assertEquals('Roles', $links->first()->title);
        $this->assertEquals('loan_applications.index', $links->last()->route);

    }
}