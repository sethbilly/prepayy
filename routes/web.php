<?php

$routePath = 'routes/partials/';

/*
 * Inclusion of route partials using require_once causes it to be loaded once during
 * tests failing on subsequent calls
 * See: https://laracasts.com/discuss/channels/testing/phpunit-says-route-not-defined
 */

// Application admin routes
require(base_path($routePath . 'callens.php'));

require(base_path($routePath . 'partner.php'));

require(base_path($routePath . 'employer.php'));

Route::group(['middleware' => ['auth']], function() {

    // User management
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'UsersController@index']);
        Route::get('{user}/edit', ['as' => 'edit', 'uses' => 'UsersController@edit']);
        Route::get('create', ['as' => 'create', 'uses' => 'UsersController@create']);
        Route::post('', ['as' => 'store', 'uses' => 'UsersController@store']);
        Route::put('{user}', ['as' => 'update', 'uses' => 'UsersController@update']);
        Route::delete('{user}', ['as' => 'delete', 'uses' => 'UsersController@destroy']);
    });

    // Role routes
    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'RolesController@index']);
        Route::get('{role}/edit', ['as' => 'edit', 'uses' => 'RolesController@edit']);
        Route::get('create', ['as' => 'create', 'uses' => 'RolesController@create']);
        Route::post('', ['as' => 'store', 'uses' => 'RolesController@store']);
        Route::put('{role}', ['as' => 'update', 'uses' => 'RolesController@update']);
        Route::delete('{role}', ['as' => 'delete', 'uses' => 'RolesController@destroy']);
    });

    // Approval levels
    Route::group(['prefix' => 'approval-levels', 'as' => 'approval_levels.'], function() {
        Route::get('', ['as' => 'index', 'uses' => 'ApprovalLevelsController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'ApprovalLevelsController@create']);
        Route::get('{level}/edit', ['as' => 'edit', 'uses' => 'ApprovalLevelsController@edit']);
        Route::post('', ['as' => 'store', 'uses' => 'ApprovalLevelsController@store']);
        Route::put('{level}', ['as' => 'update', 'uses' => 'ApprovalLevelsController@update']);
        Route::delete('{level}', ['as' => 'destroy', 'uses' => 'ApprovalLevelsController@destroy']);
    });
});

// Homepage
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

// Loan Products
Route::group(['prefix' => 'products', 'as' => 'loan_products.'], function() {
    Route::group(['middleware' => ['auth']], function() {
        Route::get('', ['as' => 'index', 'uses' => 'LoanProductsController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'LoanProductsController@create']);
        Route::get('{product}/edit', ['as' => 'edit', 'uses' => 'LoanProductsController@edit']);
        Route::post('', ['as' => 'store', 'uses' => 'LoanProductsController@store']);
        Route::put('{product}', ['as' => 'update', 'uses' => 'LoanProductsController@update']);

        Route::group(['prefix' => 'types', 'as' => 'types.'], function () {
            Route::get('', 'LoanTypesController@index')->name('index');
            Route::post('', 'LoanTypesController@store')->name('store');
            Route::put('{type}', 'LoanTypesController@update')->name('update');
            Route::delete('{type}', 'LoanTypesController@destroy')->name('delete');
        });
    });
    Route::get('browse', ['as' => 'browse', 'uses' => 'LoanProductsController@browseProducts']);
});

// Borrower
require(base_path($routePath . 'borrower.php'));
require(base_path($routePath . 'loan_applications.php'));

// Register auth routes last - The /{login?} acts as a catch all route
require(base_path($routePath . 'auth.php'));