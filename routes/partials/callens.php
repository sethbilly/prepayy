<?php

Route::group(['prefix' => 'callens', 'as' => 'callens.', 'middleware' => ['auth']], function() {
    // Partner routes
    Route::group(['prefix' => 'partners', 'as' => 'partners.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'FinancialInstitutionsController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'FinancialInstitutionsController@create']);
        Route::post('', ['as' => 'store', 'uses' => 'FinancialInstitutionsController@store']);
        Route::get('{partner}/edit', ['as' => 'edit', 'uses' => 'FinancialInstitutionsController@edit']);
        Route::put('{partner}', ['as' => 'update', 'uses' => 'FinancialInstitutionsController@update']);
    });
    // Employer routes
    Route::group(['prefix' => 'employers', 'as' => 'employers.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'EmployersController@index']);
        Route::get('create', ['as' => 'create', 'uses' => 'EmployersController@create']);
        Route::post('', ['as' => 'store', 'uses' => 'EmployersController@store']);
        Route::get('{employer}/edit', ['as' => 'edit', 'uses' => 'EmployersController@edit']);
        Route::put('{employer}', ['as' => 'update', 'uses' => 'EmployersController@update']);
    });
});