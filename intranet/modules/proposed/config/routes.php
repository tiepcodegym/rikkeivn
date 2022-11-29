<?php

Route::group([
    'middleware' => 'localization',
    'prefix' => Session::get('app.locale')
], function () {
    Route::group([
        'prefix' => 'manage-proposed',
        'as' => 'manage-proposed.',
        'middleware' => 'logged',
    ], function () {
//        Route::group([
//            'prefix' => 'category',
//            'as' => 'category.',
//        ], function () {
//            Route::get('/', 'ProposedCategoryController@index')->name('index');
//            Route::post('store', 'ProposedCategoryController@store')->name('store');
//            Route::post('update', 'ProposedCategoryController@update')->name('update');
//            Route::post('delete/{id}', 'ProposedCategoryController@delete')->name('delete');
//        });

        Route::group([
            'prefix' => '',
        ], function () {
            Route::get('edit/{id}', 'ProposedController@edit')->name('edit');
            Route::post('update/{id}', 'ProposedController@update')->name('update');
            Route::post('delete/{id}', 'ProposedController@delete')->name('delete');
            Route::get('/{id?}', 'ProposedController@index')->name('index');
        });
    });
});
