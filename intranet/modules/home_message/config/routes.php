<?php
//SubscriberNotify
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function () {
    Route::group([
        'prefix' => 'home_message',
        'as' => 'home_message.',
        'middleware' => 'auth',
    ], function () {

        Route::get('/', 'HomeMessageController@getAll')->name('all-home-message');
        Route::get('/single/{id}', 'HomeMessageController@single')->name('detail-home-message')->where('id', '[0-9]+');
        Route::get('/get-priority-by-group/{id}', 'HomeMessageController@ajaxGetPriorityByGroup')->name('get-priority-by-group')->where('id', '[0-9]+');
        Route::get('/get-day-of-year', 'HomeMessageController@getAllDayOfYear')->name('get-day-of-year');

        Route::post('/insert', 'HomeMessageController@insert')->name('insert-home-message');
        Route::post('/update/{id}', 'HomeMessageController@update')->name('update-home-message')->where('id', '[0-9]+');
        Route::post('/delete/{id}', 'HomeMessageController@delete')->name('delete-home-message')->where('id', '[0-9]+');


        Route::get('/group', 'HomeMessageGroupController@getAll')->name('all-group');
        Route::get('/group/single/{id}', 'HomeMessageGroupController@single')->name('detail-group')->where('id', '[0-9]+');

        Route::post('/group/insert', 'HomeMessageGroupController@insert')->name('insert-group');
        Route::post('/group/update/{id}', 'HomeMessageGroupController@update')->name('update-group')->where('id', '[0-9]+');
        Route::post('/group/delete/{id}', 'HomeMessageGroupController@delete')->name('delete-group')->where('id', '[0-9]+');


        Route::get('/banner', 'HomeMessageBannerController@getAll')->name('all-banner');
        Route::get('/banner/single/{id}', 'HomeMessageBannerController@single')->name('detail-banner')->where('id', '[0-9]+');

        Route::post('/banner/insert', 'HomeMessageBannerController@insert')->name('insert-banner');
        Route::post('/banner/update/{id}', 'HomeMessageBannerController@update')->name('update-banner')->where('id', '[0-9]+');
        Route::post('/banner/delete/{id}', 'HomeMessageBannerController@delete')->name('delete-banner')->where('id', '[0-9]+');

        Route::get('/banner/find-blog', 'HomeMessageBannerController@findBlog')->name('find-blog-banner');
    });
});
