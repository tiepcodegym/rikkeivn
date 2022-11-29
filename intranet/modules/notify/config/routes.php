<?php

Route::group(['middleware' => 'logged'], function () {
    Route::get('/all', 'NotifyController@index')
            ->name('index');
    Route::get('/load-data', 'NotifyController@loadNotify')
            ->name('load_data');
    Route::put('/set-read', 'NotifyController@read')
            ->name('set_read');
    Route::put('/reset-notify-number', 'NotifyController@resetNotiNum')
            ->name('reset_noti_num');
    Route::get('/refresh-data', 'NotifyController@refreshData')
            ->name('refresh_data');
});

Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'middleware' => 'auth',
        'as' => 'admin.notify.'
    ], function () {
        Route::get('list', 'NotificationController@index')->name('index');
        Route::get('create', 'NotificationController@create')->name('create');
        Route::post('store', 'NotificationController@store')->name('store');
        Route::get('{id}/edit', 'NotificationController@edit')->name('edit');
        Route::post('{id}', 'NotificationController@update')->name('update');
        Route::get('{id}', 'NotificationController@destroy')->name('destroy');
    });
});
