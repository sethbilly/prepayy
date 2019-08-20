<?php
/**
 * Created by PhpStorm.
 * User: kwabenaboadu
 * Date: 15/02/2017
 * Time: 09:14
 */
Route::group(['as' => 'loan_applications.', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => '{partner}/{product}'], function () {
        Route::get('guidelines', [
            'as' => 'guidelines',
            'uses' => 'LoanApplicationsController@getLoanGuideLines'
        ]);
        Route::get('eligibility', [
            'as' => 'eligibility',
            'uses' => 'LoanApplicationsController@getLoanEligibility'
        ]);
        Route::post('eligibility', [
            'as' => 'eligibility.post',
            'uses' => 'LoanApplicationsController@checkLoanEligibility'
        ]);
        Route::get('apply', [
            'as' => 'apply',
            'uses' => 'LoanApplicationsController@getApplicationForm'
        ]);
        Route::post('apply', [
            'as' => 'apply.post',
            'uses' => 'LoanApplicationsController@storeLoanApplication'
        ]);
    });
    Route::group(['prefix' => 'applications'], function () {
        Route::get('', ['as' => 'index', 'uses' => 'LoanApplicationsController@index']);
        Route::get('{application}/edit', [
            'as' => 'edit',
            'uses' => 'LoanApplicationsController@edit'
        ]);
        Route::put('{application}', [
            'as' => 'update',
            'uses' => 'LoanApplicationsController@update'
        ]);
        Route::get('{application}', [
            'as' => 'show',
            'uses' => 'LoanApplicationsController@show'
        ]);
        Route::put('{application}/approve', [
            'as' => 'approve',
            'uses' => 'LoanApplicationsController@approve'
        ]);
        Route::get('{application}/confirm-submission', [
            'as' => 'confirm_submission',
            'uses' => 'LoanApplicationsController@getConfirmPartnerSubmission'
        ]);
        Route::post('{application}/confirm-submission', [
            'as' => 'confirm_submission.post',
            'uses' => 'LoanApplicationsController@postConfirmPartnerSubmission'
        ]);
        Route::get('{application}/credit-report', [
            'as' => 'credit_report',
            'uses' => 'GetLoanApplicationCreditReportsController@show'
        ]);

        Route::group(['prefix' => '{application}/documents', 'as' => 'documents.'],
            function () {
                Route::get('{document}/details', [
                    'as' => 'show',
                    'uses' => 'LoanApplicationsController@showRequestedDocumentDetails'
                ]);
                Route::post('request', [
                    'as' => 'request',
                    'uses' => 'LoanApplicationsController@requestDocuments'
                ]);
                Route::post('{document}/respond', [
                    'as' => 'respond',
                    'uses' => 'LoanApplicationsController@addRequestedDocument'
                ]);
            });
    });
});