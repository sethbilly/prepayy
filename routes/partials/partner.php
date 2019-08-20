<?php

Route::group(['prefix' => 'partner', 'as' => 'partner.', 'middleware' => ['auth']], function() {
    // Employer routes
    Route::group(['prefix' => 'employers', 'as' => 'employers.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'FinancialInstitutionsController@getPartnerEmployers']);
        Route::post('', ['as' => 'store', 'uses' => 'FinancialInstitutionsController@addPartnerEmployer']);
        Route::delete('{employer}', ['as' => 'destroy', 'uses' => 'FinancialInstitutionsController@deletePartnerEmployer']);
    });
});