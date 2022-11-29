<?php
Route::group([
    'prefix' => 'setting',
    'as' => 'setting.',
    'middleware' => 'logged',
], function() {
    Route::get('list-admin', 'AdminSettingController@listAdmin')->name('list');
    Route::post('add-employee', 'AdminSettingController@saveEmployee')->name('save-employee');
    Route::post('editData', 'AdminSettingController@editData')->name('edit');
    Route::post('delete', 'AdminSettingController@delete')->name('delete');

});

Route::group([
    'prefix' => 'setting',
    'as' => 'setting-ot.',
    'middleware' => 'auth',
], function () {
    Route::get('ot-admin', 'AdminOTController@otAdmin')->name('ott');
    Route::post('add-employee-ot', 'AdminOTController@saveEmployee')->name('save-employee-ot');
    Route::post('ot-editData', 'AdminOTController@editData')->name('edit-ot');
    Route::post('ot-delete', 'AdminOTController@delete')->name('delete-ot');


});


Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'middleware' => 'logged',
        'as' => 'mobile.config.'
    ], function () {
        Route::get('mobile/config', 'AdminMobileConfigController@index')->name('index');
        Route::post('mobile/store', 'AdminMobileConfigController@store')->name('store');
        Route::post('mobile/{id}', 'AdminMobileConfigController@update')->name('update');
    });
});
