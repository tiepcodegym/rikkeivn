<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'prefix' => 'email/notification',
        'as' => 'email.notification.',
        'middleware' => 'auth',
    ], function () {
        Route::get('/', 'IndexController@index')->name('index');
        Route::post('send-email', 'IndexController@sendEmail')->name('send-email');
        Route::get('search-email', 'IndexController@searchEmail')->name('search-email');
    });
});
