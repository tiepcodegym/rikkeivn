<?php
Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
    'middleware' => 'logged',
], function() {
    Route::get('fines-money', 'FinesMoneyController@index')->name('fines-money');
});

Route::group([
    'prefix' => 'fines-money',
    'as' => 'fines-money.',
    'middleware' => 'logged',
], function() {
    Route::group([
        'prefix' => 'manage',
        'as' => 'manage.',
    ], function() {
        Route::get('list/{tab}/', 'FinesMoneyController@listFinesMoney')->name('list')->where('tab', '[a-z-]+');
        Route::get('history', 'FinesMoneyController@historyFinesMoney')->name('history');
        Route::post('edit-money', 'FinesMoneyController@editMoney')->name('edit-money');
        Route::post('import', 'FinesMoneyController@importFile')->name('import');
        Route::post('export', 'FinesMoneyController@export')->name('export');
        Route::post('update_import', 'FinesMoneyController@updateImport')->name('update_import');
    });
});