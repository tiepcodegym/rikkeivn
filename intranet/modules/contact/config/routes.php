<?php

Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::get('/', 'ContactController@index')->name('index');
    Route::get('list', 'ContactController@getList')->name('get.list');
});
