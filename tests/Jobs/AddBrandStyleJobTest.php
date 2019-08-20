<?php

use App\Entities\BrandStyle;
use App\Jobs\AddBrandStyleJob;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class AddBrandStyleJobTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
    }

    public function test_can_add_brand_style()
    {
        $style = '/* Some comment here */ .sidenav {background: blue} .sidenav ul li:hover{color: red}';

        $brandStyle = new BrandStyle();
        $wasAdded = dispatch(new AddBrandStyleJob(new Request(compact('style')), $brandStyle));

        $this->assertTrue($wasAdded);
        $this->assertEquals($style, $brandStyle->style);
    }

    public function test_can_update_brand_style()
    {
        $brandStyle = factory(BrandStyle::class)->create();
        $style = '/* Some comment here */ .sidenav {background: blue} .sidenav ul li:hover{color: red}';

        $wasUpdated = dispatch(new AddBrandStyleJob(new Request(compact('style')), $brandStyle));

        $this->assertTrue($wasUpdated);
        $this->assertEquals($style, $brandStyle->style);
    }
}
