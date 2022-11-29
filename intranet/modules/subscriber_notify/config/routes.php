<?php
//SubscriberNotify
Route::group([
    'prefix' => 'subscriber_notify',
    'as' => 'subscriber_notify.',
], function () {
    Route::post('/supplement/{employee_id}/{type}', 'SupplementController@subscriber')
        ->name('supplement')
        ->where(['employee_id' => '[0-9]+', 'type' => '[0-9]+']);

    Route::post('/leave-day/{employee_id}/{type}', 'LeaveDayController@subscriber')
        ->name('leave-day')
        ->where(['employee_id' => '[0-9]+', 'type' => '[0-9]+']);

    Route::post('/ot/{employee_id}/{type}', 'OtController@subscriber')
        ->name('ot')
        ->where(['employee_id' => '[0-9]+', 'type' => '[0-9]+']);

});
