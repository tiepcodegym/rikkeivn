<?php
Route::group([
    'prefix' => 'file',
    'as' => 'file.',
    'middleware' => 'auth',
], function() {
    Route::get('add/{type}', 'FileManagerController@add')->name('add');
    Route::post('postAddFile', 'FileManagerController@postAddFile')->name('postAddFile');
    Route::get('get-leader-team', 'FileManagerController@getLeaderTeam')->name('get-leader-team');
    Route::get('', 'FileManagerController@index')->name('index');
    Route::get('checkCodeExist', 'FileManagerController@checkCodeExist')->name('check-code-file-exist');
    Route::get('get-ceo-company', 'FileManagerController@getCeoCompany')->name('get-ceo-company');
    Route::post('delete', 'FileManagerController@delete')->name('delete');
    Route::get('edit-approval/{id}', 'FileManagerController@editApproval')->name('editApproval');
    Route::post('postEditFile', 'FileManagerController@postEditFile')->name('postEditFile');
    Route::get('list/{type}', 'FileManagerController@listItem')->name('list');
});