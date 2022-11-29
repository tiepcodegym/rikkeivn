<?php

//Setting api
Route::group([
    'prefix' => 'setting',
    'as' => 'setting.',
    'middleware' => 'auth',
], function () {
    Route::get('/api-tokens', 'SettingController@apiToken')
            ->name('tokens.list');
    Route::get('/api-tokens/edit/{id?}', 'SettingController@editApiToken')
            ->where('id', '[0-9]+')
            ->name('tokens.edit');
    Route::post('/api-tokens/save', 'SettingController@saveApiToken')
            ->name('tokens.save');
});
