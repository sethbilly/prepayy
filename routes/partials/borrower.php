<?php

Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['auth']], function() {
    // Profile
    Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'BorrowersController@profile']);
        Route::get('create', ['as' => 'create', 'uses' => 'BorrowersController@create']);
        Route::post('', ['as' => 'store', 'uses' => 'BorrowersController@store']);
        Route::get('{borrower}/edit', ['as' => 'edit', 'uses' => 'BorrowersController@edit']);
    });
});