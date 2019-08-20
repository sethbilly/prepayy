<?php

use App\Entities\BrandStyle;
use App\Entities\FinancialInstitution;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BrandStyleTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
    }

    public function test_can_get_brand_owner() {
        $style = factory(BrandStyle::class, 'partnerStyle')->create();
        
        $this->assertInstanceOf(FinancialInstitution::class, $style->institutable);
    }
    
    public function test_can_get_default_style() {
        $rec = BrandStyle::getDefaultStyle('partner');

        $this->assertEquals(config('brand_style.partner'), $rec->style);
    }

    public function stylesheet_provider() {
        return [
            ['/* Some comment here */ .sidenav {background: blue} .sidenav ul li:hover{color: red}'],
            ['#parent {background: blue} .sidenav ul > li:hover{color: red}']
        ];
    }

    /**
     * @dataProvider stylesheet_provider
     * @param $style
     */
    public function test_can_get_brand_stylesheet($style) {
        $stylesheet = '<style type="text/css">'. $style . '</style>';

        $rec = new BrandStyle(['style' => $style]);

        $this->assertEquals($stylesheet, $rec->getBrandStylesheet());
    }

    public function test_can_get_default_brand_stylesheet() {
        $brandStyle = config('brand_style.partner');
        $this->assertEquals($brandStyle, BrandStyle::getDefaultStyle('partner')->style);
        $this->assertEquals(
            '<style type="text/css">'.$brandStyle.'</style>', BrandStyle::getDefaultStylesheet('partner')
        );
    }
}
