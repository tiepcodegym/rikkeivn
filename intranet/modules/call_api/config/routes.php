<?php
Route::group([
    'prefix' => 'sonar',
    'namespace'  => 'Sonar',
    'as'         => 'sonar.',
    'middleware' => 'logged',
], function() {
    Route::post('user/create', 'UserController@create')
        ->name('user.create');
    Route::post('user/change_password', 'UserController@changePassword')
        ->name('user.change.password');
    Route::post('project/create/{id}/{type?}', 'ProjectController@create')
        ->name('project.create')
        ->where('id', '[0-9]+');
});
//redmine
Route::group([
    'prefix' => 'redmine',
    'namespace'  => 'Redmine',
    'as'         => 'redmine.',
    'middleware' => 'logged',
], function() {
    Route::post('account/create', 'UserController@create')
        ->name('user.create');
    Route::post('account/change/pass', 'UserController@changePass')
        ->name('user.change.pass');
});

Route::group([
    'prefix' => 'gitlab',
    'namespace'  => 'Gitlab',
    'as'         => 'gitlab.',
    'middleware' => 'logged',
], function() {
    Route::post('account/create', 'UserController@create')
        ->name('user.create');
    Route::post('account/change/pass', 'UserController@changePass')
        ->name('user.change.pass');
});
