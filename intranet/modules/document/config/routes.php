<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'middleware' => 'logged'
    ], function () {
        Route::get('/', 'DocumentController@showList')
            ->name('list');
    Route::get('/view/{id}/{slug}', 'DocumentController@view')
            ->where('id', '[0-9]+')
            ->name('view');
    Route::get('/type/{id}/{slug}', 'DocumentController@viewType')
            ->where('id', '[0-9]+')
            ->name('type.view');
    Route::get('/team/{id}/{slug}', 'DocumentController@viewTeam')
            ->where('id', '[0-9]+')
            ->name('team.view');
    Route::get('/download/{docId}/file/{fileId}', 'DocumentController@frontDownload')
            ->where('docId', '[0-9]+')
            ->where('fileId', '[0-9]+')
            ->name('file.download');
    Route::get('view/file/{id}/{slug?}', 'DocumentController@read')
            ->where('id', '[0-9]+')
            ->name('read');
    Route::get('/doc-file-update', 'DocumentController@updateFileMagazine')
            ->name('update_file');
});

Route::group([
    'prefix' => 'manage',
    'as' => 'admin.'
], function () {
    Route::group([
       'middleware' => 'logged', 
    ], function () {
        Route::get('/', 'DocumentController@index')->name('index');
        Route::get('/edit/{id?}', 'DocumentController@edit')
                ->where('id', '[0-9]+')
                ->name('edit');
        Route::post('/save', 'DocumentController@save')->name('save');
        Route::post('/check-exists', 'DocumentController@checkExists')->name('check_exists');
        Route::post('/feedback/{id}', 'DocumentController@feedback')
                ->where('id', '[0-9]+')
                ->name('feedback');
        Route::delete('/delete/{id}', 'DocumentController@delete')
                ->where('id', '[0-9]+')
                ->name('delete');
        Route::get('/download/{docId}/file/{id}', 'DocumentController@download')
                ->where('docId', '[0-9]+')
                ->where('id', '[0-9]+')
                ->name('download');
        Route::post('/set-current/{docId}/file/{fileId}', 'DocumentController@setCurrentFile')
                ->where('docId', '[0-9]+')
                ->where('fileId', '[0-9]+')
                ->name('file.set_current');
        Route::delete('/delete-file/{docId}/file/{id}', 'DocumentController@deleteFile')
                ->where('docId', '[0-9]+')
                ->where('id', '[0-9]+')
                ->name('file.delete');
        Route::post('/send-mail-publish/{id}', 'DocumentController@publish')
                ->where('id', '[0-9]+')
                ->name('publish');
        Route::post('/add-assignee/{docId}', 'DocumentController@addAssignee')
                ->where('docId', '[0-9]+')
                ->name('add_assignee');
        Route::delete('/delete-assignee/{docId}/{empId}', 'DocumentController@deleteAssignee')
                ->where('docId', '[0-9]+')
                ->where('empId', '[0-9]+')
                ->name('delete_assignee');
        //search reviewer
        Route::get('search-assignees', 'DocumentController@searchAssignees')
                ->name('search_assignees');
        Route::get('suggest-reviewers', 'DocumentController@getSuggestReviewers')
                ->name('suggest_reviewers');

        Route::group([
            'prefix' => 'comments',
            'as' => 'comment.'
        ], function () {
            Route::post('/{docId}/save', 'CommentController@save')
                    ->where('docId', '[0-9]+')
                    ->name('save');
            Route::delete('/{docId}/delete/{id}', 'CommentController@delete')
                    ->where('docId', '[0-9]+')
                    ->where('id', '[0-9]+')
                    ->name('delete');
            Route::get('/{docId}/list', 'CommentController@index')
                    ->where('docId', '[0-9]+')
                    ->name('list');
        });

        Route::group([
            'prefix' => 'request',
            'as' => 'request.'
        ], function () {
            Route::get('/index', 'RequestController@index')->name('index');
            Route::get('/edit/{id?}', 'RequestController@edit')
                    ->where('id', '[0-9]+')
                    ->name('edit');
            Route::post('/save', 'RequestController@save')->name('save');
            Route::post('/feedback/{id}', 'RequestController@feedback')
                    ->where('id', '[0-9]+')
                    ->name('feedback');
            Route::delete('/delete/{id}', 'RequestController@delete')
                    ->where('id', '[0-9]+')
                    ->name('delete');
            Route::get('/search/ajax', 'RequestController@searchAjax')
                    ->name('search.ajax');
        });
    });

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'types',
        'as' => 'type.'
    ], function () {
        Route::get('/', 'DocTypeController@index')->name('index');
        Route::get('/edit/{id?}', 'DocTypeController@edit')
                ->where('id', '[0-9]+')
                ->name('edit');
        Route::post('/save', 'DocTypeController@save')->name('save');
        Route::delete('/delete/{id}', 'DocTypeController@delete')->name('delete');
    });

        Route::get('/help', 'DocumentController@help')->name('help');
    });
});
