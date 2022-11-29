<?php

Route::get('/', ['as' => 'index', 'uses' => 'TestController@index']);
Route::post('/auth', ['as' => 'auth', 'uses' => 'TestController@checkAuth']);

Route::get('/select-test', ['as' => 'select_test', 'uses' => 'TestController@selectTest']);
Route::get('/get-tests', ['as' => 'get_tests', 'uses' => 'TestController@getTests']);
Route::post('/select-test', ['as' => 'post_select_test', 'uses' => 'TestController@postSelectTest']);
Route::get('/show/{id}', ['as' => 'show', 'uses' => 'TestController@show'])->where(['id' => '[0-9]+']);
Route::get('/finish', ['as' => 'finish', 'uses' => 'TestController@finish']);

$pre_man = config('app.manage', 'manage');
Route::group(['prefix' => $pre_man, 'namespace' => 'Admin', 'middleware' => 'auth'], function () {
    Route::resource('tests', 'TestController', [
        'names' => [
            'index' => "admin.test.index",
            'create' => "admin.test.create",
            'store' => "admin.test.store",
            'show' => "admin.test.show",
            'edit' => "admin.test.edit",
            'update' => "admin.test.update",
            'destroy' => "admin.test.destroy"
        ]
    ]);
    Route::post('tests/m-actions', ['as' => 'admin.test.m_action', 'uses' => 'TestController@mAction']);  
    Route::get('/passwords', ['as' => 'admin.test.passwords', 'uses' => 'PasswordController@index']);
    Route::post('/update-password', ['as' => 'admin.test.update_pass', 'uses' => 'PasswordController@update']);
});

