<?php
Route::group([
    'prefix' => 'help',
    'middleware' => 'auth'
], function() {
    Route::get('/create', 'HelpController@create')->name('manage.help.create');
    Route::get('/edit/{id}', 'HelpController@edit')->name('manage.help.edit')->where('id', '[0-9]+');    
    //ajax route
    Route::get('/help', 'HelpController@getHelpbyID')->name('display.help.edit');
    Route::post('/save', 'HelpController@save')->name('manage.help.save');
    Route::post('/delete', 'HelpController@delete')->name('manage.help.delete');
});

Route::group([
    'prefix' => 'help',
    'middleware' => 'logged'
], function() {
    Route::get('/view/{id?}', 'DisplayController@display')->name('display.help.view');
    //ajax route
    Route::get('/show', 'DisplayController@getHelpContentbyID')->name('display.help.show');
    Route::get('/search', 'DisplayController@searchHelp')->name('display.help.search');
});
