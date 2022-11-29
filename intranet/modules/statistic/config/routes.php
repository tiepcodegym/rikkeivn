<?php
// statistic project
Route::group([
    //'middleware' => 'auth',
    'prefix' => 'project/statistic',
    'as' => 'project.activity.',
    'namespace' => 'Project'
], function() {
    Route::post('slide/pass', 'ActivityController@slidePassPost')->name('slide.pass.post');
    Route::get('slide/{ids?}', 'ActivityController@slideView')->name('slide.view');
    Route::get('get/{action?}/{ids?}', 'ActivityController@getInfo')
        ->name('get.info');
    Route::get('/{ids?}', 'ActivityController@index')->name('index');
});