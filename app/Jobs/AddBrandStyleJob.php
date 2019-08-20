<?php

namespace App\Jobs;

use App\Entities\BrandStyle;
use Illuminate\Http\Request;

class AddBrandStyleJob
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var BrandStyle
     */
    private $brandStyle;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param BrandStyle $style
     */
    public function __construct(Request $request, BrandStyle $style)
    {
        $this->request = $request;
        $this->brandStyle = $style;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        if ($this->brandStyle->style !== $this->request->get('style')) {
            $this->brandStyle->style = $this->request->get('style');

            return $this->brandStyle->save();
        }

        return false;
    }
}
