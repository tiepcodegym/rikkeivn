<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::get('/', ['as' => 'index', 'uses' => 'TestController@index']);
    Route::get('/switch-language', 'TestController@switchLang')->name('switch_lang');
    Route::post('/auth', ['as' => 'auth', 'uses' => 'TestController@checkAuth']);

    Route::group(['middleware' => 'auth'], function () {
        //upload images
        Route::get('/upload-files', ['as' => 'admin.get_upload_images', 'uses' => 'TestController@getUploadImages']);
        Route::post('/post-upload-images', ['as' => 'admin.upload_images', 'uses' => 'TestController@postUploadImages']);
        Route::delete('/images/{id}', ['as' => 'admin.delete_image', 'uses' => 'TestController@deleteImage']);
        Route::get('/images/check-in-use', ['as' => 'admin.image.check_in_use', 'uses' => 'TestController@checkImageInUse']);
        Route::post('/images/multi-actions', ['as' => 'admin.image.multi_actions', 'uses' => 'TestController@multiActions']);
    });
    //Exam list
    Route::get('/exam-list', ['as' => 'test.exam_list', 'uses' => 'AssigneeController@index']);
    Route::post('/select-option', 'AssigneeController@selectOption')->name('select_option');
    Route::post('/select-employee', 'AssigneeController@selectEmployee')->name('select_employee');
    Route::post('/save-data', 'AssigneeController@saveAssignee')->name('save_data');
    //Candidate
    Route::get('/candidate-information', ['as' => 'candidate.input_infor', 'uses' => 'CandidateController@inputInfor']);
    Route::post('/candidate-information', ['as' => 'candidate.save_infor', 'uses' => 'CandidateController@saveInfor']);
    Route::get('/candidate-information/view', ['as' => 'candidate.view_infor', 'uses' => 'CandidateController@view']);
    Route::get('/load-cadidates', ['as' => 'candidate.load_data', 'uses' => 'CandidateController@ajaxLoadData']);
    Route::post('/check-candidate-information', ['as' => 'candidate.check_infor', 'uses' => 'CandidateController@checkCandidateInfo']);
    //Type
    Route::get('/get-by-type', ['as' => 'get_by_type', 'uses' => 'TestController@getByType']);
    //Test
    Route::get('/select-test', ['as' => 'select_test', 'uses' => 'TestController@selectTest']);
    Route::post('/select-test', ['as' => 'post_select_test', 'uses' => 'TestController@postSelectTest']);
    Route::any('/candidate-testing', ['as' => 'candidate_test', 'uses' => 'TestController@candidateTest']);
    Route::get('/cadidate/history', ['as' => 'candidate_history', 'uses' => 'TestController@candidateHistory']);
    Route::get('/view/{code}', ['as' => 'view_test', 'uses' => 'TestController@getShowTest']);
    Route::post('/submit-test', ['as' => 'submit_test', 'uses' => 'TestController@submitTest']);
    
    Route::get('/results/{id}', ['as' => 'result', 'uses' => 'TestController@testResult'])
        ->where('id', '[0-9]+');
    Route::get('/view-results', ['as' => 'view-result', 'uses' => 'TestController@viewTestResult'])->where('id', '[0-9]+');

    Route::post('/check-do-test', ['as' => 'check_do_test', 'uses' => 'TestController@checkDoTest']);
    Route::post('/update-leave-time', ['as' => 'update_leave_time', 'uses' => 'TestController@updateLeaveTime']);
    Route::put('/update-answer', ['as' => 'update_answer', 'uses' => 'TestController@updateAnswer']);
    Route::post('/save-temp-answer/{id}', ['as' => 'save_temp', 'uses' => 'TestController@saveTempAnswer'])
        ->where('id', '[0-9]+');
    Route::any('{code}', ['as' => 'view', 'uses' => 'TestController@view']);

    // manage
    Route::group([
        'prefix' => 'manage',
        'namespace' => 'Admin',
        'middleware' => 'auth'
    ], function () {
        Route::group([
            'prefix' => 'tests',
        ], function () {
            Route::get('search-test', ['as' => 'admin.test.search_test', 'uses' => 'TestController@searchAjax']);
            Route::get('/', 'TestController@index')->name('admin.test.index');
            Route::get('create', 'TestController@create')->name('admin.test.create');
            Route::get('show/{id}', 'TestController@show')->name('admin.test.show')
                    ->where('id', '[0-9]+');
            Route::get('edit/{id}', 'TestController@edit')->name('admin.test.edit')
                    ->where('id', '[0-9]+');
            Route::post('save', 'TestController@save')->name('admin.test.save');
            Route::delete('delete/{id}', 'TestController@destroy')->name('admin.test.destroy')
                    ->where('id', '[0-9]+');
            Route::post('m-actions', ['as' => 'admin.test.m_action', 'uses' => 'TestController@mAction']);
            Route::post('import', ['as' => 'admin.test.import', 'uses' => 'TestController@importTest']);
            Route::post('get-more-result', ['as' => 'admin.test.get_more_result', 'uses' => 'TestController@getMoreResult']);
            Route::get('/{id}/results', ['as' => 'admin.test.results', 'uses' => 'TestController@listResults'])
                ->where('id', '[0-9]+');
            Route::get('/analytics', ['as' => 'admin.test.analytics', 'uses' => 'TestController@AjaxGetAnalytics'])
                ->where('id', '[0-9]+');
            Route::post('/{id}/export-results', ['as' => 'admin.test.export_results', 'uses' => 'TestController@exportResults'])
                ->where('id', '[0-9]+');
            Route::delete('results/{id}/destroy', ['as' => 'admin.test.remove_result', 'uses' => 'TestController@removeResult'])
                ->where('id', '[0-9]+');
            Route::post('results/multi-delete', ['as' => 'admin.test.multi_delete', 'uses' => 'TestController@removeMultiResult']);
            Route::post('reset-random', ['as' => 'admin.test.reset_random', 'uses' => 'TestController@resetTestingRandom']);
            Route::get('get-written-cat', ['as' => 'admin.test.getWrittenCat', 'uses' => 'TestController@ajaxGetWrittenCat']);
        });

        Route::group([
            'prefix' => 'questions'
        ], function () {
            Route::get('/edit', ['as' => 'admin.test.question.edit', 'uses' => 'QuestionController@getEdit']);
            Route::post('/save', ['as' => 'admin.test.question.save', 'uses' => 'QuestionController@postSave']);
            Route::post('/store', ['as' => 'admin.test.question.store', 'uses' => 'QuestionController@store']);
            Route::get('/create', ['as' => 'admin.test.question.create', 'uses' => 'QuestionController@create']);
            Route::delete('/{id}/delete', ['as' => 'admin.test.question.delete', 'uses' => 'QuestionController@delete'])
                ->where('id', '[0-9]+');
            Route::get('/{id}/full-edit', ['as' => 'admin.test.question.full_edit', 'uses' => 'QuestionController@fullEdit'])
                ->where('id', '[0-9]+');
            Route::put('/{id}/full-update', ['as' => 'admin.test.question.full_update', 'uses' => 'QuestionController@fullUpdate'])
                ->where('id', '[0-9]+');
            Route::put('/{id}/written-update', ['as' => 'admin.test.question.written_update', 'uses' => 'QuestionController@updateWrittenQuestion'])
                ->where('id', '[0-9]+');
            Route::post('/update-type', ['as' => 'admin.test.queston.update_type', 'uses' => 'QuestionController@updateType']);
            Route::post('/copy-to-test', ['as' => 'admin.test.question.copy_to', 'uses' => 'QuestionController@copyToTest']);
            Route::post('/export-excel', ['as' => 'admin.test.question.export_excel', 'uses' => 'QuestionController@exportExcel']);
            Route::post('/category/store', ['as' => 'admin.test.question.add_category', 'uses' => 'QuestionController@addCategory']);
        });

        /*Route::group([
            'prefix' => 'question-categoreis',
            'as' => 'qcat.',
        ], function () {
            Route::get('/', 'QCatController@index')->name('index');
            Route::get('/edit/{id?}', 'QCatController@edit')->name('edit')
                    ->where('id', '[0-9]+');
            Route::get('/save', 'QCatController@save')->name('save');
        });*/

        Route::get('/passwords', ['as' => 'admin.test.passwords', 'uses' => 'PasswordController@index']);
        Route::post('/update-password', ['as' => 'admin.test.update_pass', 'uses' => 'PasswordController@update']);

        //type
        Route::resource('types', 'TypeController', ['names' => test_Renames('admin.type')]);
        //Candidate
        Route::get('/candidate-information', ['as' => 'candidate.admin.index', 'uses' => 'CandidateController@index']);
        Route::get('/candidate-information/{id}/show', ['as' => 'candidate.admin.show', 'uses' => 'CandidateController@show']);
        Route::get('/candidate-information/{id}/edit', ['as' => 'candidate.admin.edit', 'uses' => 'CandidateController@edit']);
        Route::put('/candidate-information/{id}/update', ['as' => 'candidate.admin.update', 'uses' => 'CandidateController@update']);
        Route::post('/candidate-information/import', ['as' => 'candidate.admin.import', 'uses' => 'CandidateController@import']);
        Route::delete('/candidate-information/{id}', ['as' => 'candidate.admin.destroy', 'uses' => 'CandidateController@destroy']);
    });
});
