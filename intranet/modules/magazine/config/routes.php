<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group(
        ['prefix' => 'magazines', 'middleware' => 'auth'], function () {
        Route::get('/', 'MagazineController@manage')->name('manage');
        Route::get('/get-images', 'MagazineController@getImages')->name('get_images');
        Route::get('/create', 'MagazineController@create')->name('create');
        Route::post('/store', 'MagazineController@store')->name('save');
        Route::get('/{id}/edit', 'MagazineController@edit')->name('edit')->where('id', '[0-9]+');
        Route::post('/{id}/update', 'MagazineController@update')->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}/delete', 'MagazineController@delete')->name('delete')->where('id', '[0-9]+');

        /* mine */
        Route::post('/upload-image', 'MagazineController@uploadImage')->name('upload_image');
    }
    );
    Route::get('magazine/{id}/{slug?}', 'MagazineController@read')
        ->name('read')->where('id', '[0-9]+');
    Route::get('magazines/list', 'MagazineController@listAll')
        ->name('list');
});
