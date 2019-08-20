<?php

namespace App\Http\Controllers;

use App\Entities\LoanProduct;
use App\Jobs\GetLoanProductsJob;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle anonymous browsing of loan products irrespective of institution
     * @return mixed
     */
    public function index()
    {
        // Get 5 random loan products for display
        $products = LoanProduct::with(['institution', 'images'])->inRandomOrder()->take(5)->get();

        return view('home.index')->with(compact('products'));
    }
}