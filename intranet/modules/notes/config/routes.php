<?php

Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
Route::group([
    'middleware' => 'logged',
], function() {
    Route::get('/manage/create', 'ManageNotesController@create')
    	->name('manage.notes.create');
    Route::post('/manage/save', 'ManageNotesController@save')
    	->name('manage.notes.save');
    Route::get('/manage/edit/{id}', 'ManageNotesController@edit')
    	->name('manage.notes.edit');

    Route::get('/manage/index', 'ManageNotesController@index')
        ->name('manage.notes.getIndex');
    Route::get('/manage/getdata', 'ManageNotesController@anyData')
        ->name('manage.notes.anyData');

    // user view
    Route::get('/', 'ReleaseNotesController@index')
    	->name('notes.index');
    Route::get('/{id}', 'ReleaseNotesController@getDetail')
        ->name('notes.detail');
});
});
