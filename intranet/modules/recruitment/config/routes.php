<?php

Route::get('recruitment/applies/presenter','RecruitmentAppliesController@getPresenter')->name('get.applies.presenter')
        ->middleware('logged');

Route::group([
    'prefix' => 'recruitment/email-marketing',
    'middleware' => ['auth', 'localization'],
    'as' => 'email.'
], function () {
    Route::get('/', 'MailMarketingController@index')
            ->name('index');
    Route::get('/candidates', 'MailMarketingController@getCandidates')
            ->name('candidate.list');
    Route::post('/send-email', 'MailMarketingController@sendMail')
            ->name('send');
    Route::post('/save-config-mail', 'MailMarketingController@saveConfigMail')
            ->name('save_mail');
    Route::get('/preview-email', 'MailMarketingController@previewEmail')
            ->name('preview');
    Route::get('/get-template-content', 'MailMarketingController@getTemplateContent')
            ->name('template.get_content');
    Route::get('/get-request-filter', 'MailMarketingController@getRequestDetailFilter')
            ->name('get_request_filter');
    Route::get('/candidates/view-will-send', 'MailMarketingController@viewCddWillSend')
            ->name('view_cdd_will_send');
});