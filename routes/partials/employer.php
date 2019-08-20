<?php

Route::group(['prefix' => 'employer', 'as' => 'employer.', 'middleware' => 'auth'], function() {
    // Loan settings
    Route::group(['prefix' => 'settings', 'as' => 'loan_settings.'], function () {
        Route::group(['prefix' => 'approval-levels', 'as' => 'approval_levels.'], function() {
            Route::get('', ['as' => 'index', 'uses' => 'ApprovalLevelsController@index']);
            Route::get('create', ['as' => 'create', 'uses' => 'ApprovalLevelsController@create']);
            Route::get('{level}/edit', ['as' => 'edit', 'uses' => 'ApprovalLevelsController@edit']);
            Route::post('', ['as' => 'store', 'uses' => 'ApprovalLevelsController@store']);
            Route::put('{level}', ['as' => 'update', 'uses' => 'ApprovalLevelsController@update']);
            Route::delete('{level}', ['as' => 'destroy', 'uses' => 'ApprovalLevelsController@destroy']);
        });
    });
});