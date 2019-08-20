<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 16/01/2017
 * Time: 17:09
 */
// Get password reset link via email
Route::get('password/reset', ['as' => 'password.forgot.get', 'uses' => 'Auth\ForgotPasswordController@showLinkRequestForm']);
Route::post('password/email', ['as' => 'password.forgot.post', 'uses' => 'Auth\ForgotPasswordController@sendResetLinkEmail']);
// Reset password via email token
Route::post('password/reset', ['as' => 'password.reset.post', 'uses' => 'Auth\ResetPasswordController@reset']);
Route::get('password/reset/{token}', ['as' => 'password.reset.get', 'uses' => 'Auth\ResetPasswordController@showResetForm']);
// Register link
Route::get('register/{type?}', ['as' => 'register.get', 'uses' => 'Auth\RegisterController@showRegistrationForm']);
Route::post('register', ['as' => 'register.post', 'uses' => 'Auth\RegisterController@register']);
// Login links
Route::post('login', ['as' => 'login.post', 'uses' => 'Auth\LoginController@login']);
Route::post('logout', ['as' => 'logout.post', 'uses' => 'Auth\LoginController@logout']);

Route::get('/login', ['as' => 'login.get', 'uses' => 'Auth\LoginController@showLoginForm']);