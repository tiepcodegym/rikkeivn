<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'middleware' => 'auth',
        'as' => 'request.',
        'prefix' => 'resource/request',
    ], function () {
        Route::get('create', 'RequestController@create')->name('create');
        Route::get('edit/{id}', 'RequestController@edit')->name('edit')->where('id', '[0-9]+');
        Route::get('detail/{id}', 'RequestController@detail')->name('detail')->where('id', '[0-9]+');
        Route::post('create', 'RequestController@store')->name('postCreate');
        Route::post('approved', 'RequestController@approved')->name('approved');
        Route::post('assignee', 'RequestController@assignee')->name('assignee');
        Route::get('list', 'RequestController@index')->name('list');
        Route::post('send_mail', 'RequestController@sendMail')->name('sendMail');
        Route::post('send_mail_recruiter', 'RequestController@sendMailRecruiter')->name('sendMailRecruiter');
        Route::post('showChannel', 'RequestController@showChannel')->name('showChannel');
        Route::post('saveChannel', 'RequestController@saveChannel')->name('saveChannel');
        Route::post('generate', 'RequestController@generate')->name('generate');
        Route::get('candidate-list/{id}', 'RequestController@candidateList')->name('candidateList');
        Route::post('saveAssign', 'RequestController@saveAssignRequest')->name('saveAssignRequest');
        Route::post('post-data', 'RequestController@postRequest')->name('postDataRequest');
        Route::post('post-data-recruitment', 'RequestController@postRequestRecruitment')->name('postDataRequestRecruitment');
    });

    Route::group([
        'middleware' => 'logged',
        'as' => 'request.',
        'prefix' => 'resource/request',
    ], function () {
        Route::post('approved', 'RequestController@approved')->name('approved');
        Route::post('assignee', 'RequestController@assignee')->name('assignee');
    });

    Route::get('resource/request/list/search/ajax', 'RequestController@searchAjax')->name('request.list.search.ajax');
    Route::group([
        'middleware' => 'auth',
        'as' => 'candidate.',
        'prefix' => 'resource/candidate',
    ], function () {
        Route::get('create', 'CandidateController@create')->name('create');
        Route::get('edit/{id}', 'CandidateController@edit')->name('edit')->where('id', '[0-9]+');
        Route::get('history', 'CandidateController@history')->name('history');
        Route::get('downloadcv/{filename}', 'CandidateController@downloadcv')->name('downloadcv');
        Route::get('check-attach-file/{filename}', 'CandidateController@checkAttachFile')->name('checkAttachFile');
        Route::get('download-attach/{filename}', 'CandidateController@downloadAttach')->name('downloadAttach');
        Route::get('importcv', 'CandidateController@importcv')->name('importcv');
        Route::post('postimportcv', 'CandidateController@postImportcv')->name('postimportcv');
        Route::post('postTest', 'CandidateController@storeTest')->name('postTest');
        Route::post('postInterview', 'CandidateController@storeInterview')->name('postInterview');
        Route::post('postOffer', 'CandidateController@storeOffer')->name('postOffer');
        Route::post('checkCandidateMail', 'CandidateController@checkCandidateMail')->name('checkCandidateMail');
        Route::post('deleteCandidate', 'CandidateController@deleteCandidate')->name('deleteCandidate');
        Route::post('getTeamByRequest', 'CandidateController@getTeamByRequest')->name('getTeamByRequest');
        Route::post('getPositionByTeam', 'CandidateController@getPositionByTeam')->name('getPositionByTeam');
        Route::get('checkExist', 'CandidateController@checkExist')->name('checkExist');
        Route::post('checkExist', 'CandidateController@postCheckExist')->name('postCheckExist');
        Route::post('send-mail-offer', 'CandidateController@sendMailOffer')->name('sendMailOffer');
        Route::post('send-mail-recruiter', 'CandidateController@sendMailOffer')->name('sendMailRecruiter');
        Route::post('send-mail-thanks', 'CandidateController@sendMailThanks')->name('sendMailThanks');
        Route::post('pdf-save/{id}', 'CandidateController@pdfSave')->name('pdfSave');
        Route::post('/updateRecruiter', 'CandidateController@updateRecruiter')->name('updateRecruiter');
        Route::get('/report', 'RecruitController@indexCandidate')->name('indexCandidate');
        Route::post('/existEmpPropertyValue', 'CandidateController@checkExistEmpPropertyValue')->name('checkExistEmpPropertyValue');
        Route::get('search', 'CandidateController@search')->name('search');
        Route::post('search-advance', 'CandidateController@searchAdvance')->name('searchAdvance');
        Route::get('/check-employee-email', 'CandidateController@checkEmployeeEmail')->name('check_employee_email');
        Route::post('get-employee-info', 'CandidateController@getEmployeeInfo')->name('employee.info');
        Route::get('follow', 'CandidateController@follow')->name('follow');
        Route::get('interested', 'InterestedCandidateController@interested')->name('interested');
        Route::post('interested/remove', 'InterestedCandidateController@removeInterested')->name('remove-interested');
        Route::post('interested/preview-mail', 'InterestedCandidateController@previewMail')->name('interested.preview-mail');
        Route::post('interested/send-mail', 'InterestedCandidateController@sendMail')->name('interested.send-mail');
    });
    Route::group([
        'middleware' => 'logged',
        'as' => 'candidate.',
        'prefix' => 'resource/candidate',
    ], function () {
        Route::post('/existEmpCard', 'CandidateController@checkExistEmpCard')->name('checkExistEmpCard');
        Route::post('update-status-candidate', 'CandidateController@updateStatus')->name('update_status_candidate');
        Route::post('/create-ricode-test', 'CandidateController@createRicodeTest')->name('create-ricode-test');
    });

    Route::group([
        'middleware' => 'logged',
        'as' => 'candidate.',
        'prefix' => 'resource/candidate',
    ], function () {
        Route::get('list', 'CandidateController@index')->name('list');
        Route::get('detail/{id}', 'CandidateController@detail')->name('detail')->where('id', '[0-9]+');
        Route::get('viewcv/{id}/{filename}', 'CandidateController@viewcv')->name('viewcv');
        Route::post('create', 'CandidateController@store')->name('postCreate');
    });
    Route::post('resource/candidate/calendar/check-room', 'CandidateController@checkRoomAvailable')->name('candidate.checkRoomAvailable');
    Route::post('resource/candidate/calendar/save', 'CandidateController@saveCalendar')->name('candidate.saveCalendar');
    Route::get('resource/candidate/calendar/form', 'CandidateController@getFormCalendar')->name('candidate.getFormCalendar');
    Route::get('resource/candidate/oauth2callback', 'CandidateController@oauth2callback')->name('candidate.oauth2callback');
    Route::post('resource/candidate/get-teams-by-requests', 'CandidateController@getTeamsByRequests')->name('candidate.getTeamsByRequests');
    Route::post('resource/candidate/get-poses-by-teams', 'CandidateController@getPosesByTeams')->name('candidate.getPosesByTeams');
    Route::post('resource/candidate/get-teams-by-request', 'CandidateController@getTeamsByRequest')->name('candidate.getTeamsByRequest');
    Route::post('resource/candidate/get-poses-by-team', 'CandidateController@getPosesByTeam')->name('candidate.getPosesByTeam');
    Route::get('resource/candidate/self_elected', 'CandidateController@selfElected')->name('candidate.self_elected');
    Route::post('postSelfElected', 'CandidateController@postSelfElected')->name('candidate.postSelfElected');
    Route::post('get_level_by_lang', 'CandidateController@getLevelByLang')->name('candidate.getLevelByLang');
    Route::get('resource/candidate/test-history/{email}/{id}', 'CandidateController@testHistory')->name('candidate.testHistory');
    Route::post('resource/candidate/save/comment', 'CandidateController@saveCommentCandidate')->name('candidate.comment');
    Route::post('resource/candidate/delete/comment', 'CandidateController@deleteComment')->name('candidate.delete.comment');
    Route::get('candidate/{id}/comment/list/ajax', 'CandidateController@commentListAjax')->name('candidate.comment.list.ajax')->where('id', '[0-9]+');

    Route::group([
        'middleware' => 'logged',
        'as' => 'candidate.',
        'prefix' => 'resource/candidate',
    ], function () {
        Route::post('reapply', 'CandidateController@reapply')->name('reapply');
    });

    Route::post('edit_date_contact', 'CandidateController@editDateContect')->name('candidate.editDateContect');
    Route::group([
        'middleware' => 'auth',
        'as' => 'dashboard.',
        'prefix' => 'resource/dashboard',
    ], function () {
        Route::get('index/{year?}/{id?}', 'DashboardController@index')->name('index');
        Route::get('utilization', 'DashboardController@utilization')->name('utilization');
        Route::post('ajax', 'DashboardController@ajax')->name('ajax');
        Route::post('viewWeekDetail', 'DashboardController@viewWeekDetail')->name('viewWeekDetail');
        Route::any('export', 'DashboardController@exportEmployeeEffort')->name('exportEmployeeEffort');
    });

    Route::group([
        'middleware' => 'logged',
        'as' => 'dashboard.',
        'prefix' => 'resource/dashboard',
    ], function () {
        Route::post('export-utilization', 'DashboardController@exportUtilization')->name('export_utilization');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'channel.',
        'prefix' => 'resource/setting/channel',
    ], function () {
        Route::get('create', 'ChannelController@create')->name('create');
        Route::post('create', 'ChannelController@store')->name('postCreate');
        Route::get('list', 'ChannelController@grid')->name('list');
        Route::get('edit/{id}', 'ChannelController@edit')->name('edit');
        Route::post('delete', 'ChannelController@deleteChannel')->name('delete');
        Route::post('ajaxToggleStatus', 'ChannelController@ajaxToggleStatus')->name('ajaxToggleStatus');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'languages.',
        'prefix' => 'resource/setting/languages',
    ], function () {
        Route::get('create', 'LanguagesController@create')->name('create');
        Route::post('create', 'LanguagesController@store')->name('postCreate');
        Route::get('list', 'LanguagesController@index')->name('list');
        Route::get('edit/{id}', 'LanguagesController@edit')->name('edit');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'recruit.',
        'prefix' => 'resource/recruitment'
    ], function () {
        Route::get('/', 'RecruitController@index')->name('index');
        Route::get('/statistics', 'RecruitController@statistics')->name('statistics');
        Route::get('/plan', 'RecruitController@buildPlan')->name('build_plan');
        Route::post('/update-plan', 'RecruitController@updatePlan')->name('update_plan');
        //report detail
        Route::get('/report/{timeType}/detail/{type}/{year}/{month?}', 'RecruitController@reportDetail')
            ->where('month', '[0-9]+')
            ->where('year', '[0-9]+')
            ->name('report_detail');
        Route::post('/export/{timeType}/detail/{type}/{year}/{month?}', 'RecruitController@exportDetail')
            ->where('year', '[0-9]+')
            ->where('month', '[0-9]+')
            ->name('export_detail');
        Route::post('/update-account-status', 'RecruitController@updateAccountStatus')->name('update.account.status');
        Route::post('/leader-approve', 'RecruitController@updateLeaderApprove')->name('update.leader.approve');
    });

    // monthly recruitment report
    Route::group([
        'middleware' => 'auth',
        'as' => 'monthly_report.',
        'prefix' => 'resource',
    ], function () {
        Route::get('report/monthly', 'RecruitController@monthlyReport')->name('recruit.index');
        Route::get('report/monthly/export', 'RecruitController@exportMonthlyReport')->name('recruit.export');
        Route::post('channel/change-color', 'ChannelController@changeColor')->name('channel.changeColor');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'plan.team.',
        'prefix' => 'resource/teams-feature'
    ], function () {
        Route::get('/', 'TeamFeatureController@index')->name('index');
        Route::get('/create', 'TeamFeatureController@create')->name('create');
        Route::post('/store', 'TeamFeatureController@store')->name('store');
        Route::get('/{id}/edit', 'TeamFeatureController@edit')->name('edit');
        Route::put('/{id}/update', 'TeamFeatureController@update')->name('update');
        Route::delete('/{id}/delete', 'TeamFeatureController@destroy')->name('destroy');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'programminglanguages.',
        'prefix' => 'resource/setting/programminglanguages',
    ], function () {
        Route::get('list', 'ProgrammingLanguagesController@index')->name('list');
        Route::get('create', 'ProgrammingLanguagesController@create')->name('create');
        Route::post('create', 'ProgrammingLanguagesController@store')->name('postCreate');
        Route::get('edit/{id}', 'ProgrammingLanguagesController@edit')->name('edit');
        Route::post('delete/{id}', 'ProgrammingLanguagesController@delete')->name('delete');
        Route::post('ajaxDelete', 'ProgrammingLanguagesController@ajaxDelete')->name('ajaxDelete');
    });
//remove old request asset
    Route::group([
        'middleware' => 'auth',
        'as' => 'test.history.',
        'prefix' => 'resource/candidate/test-schedule'
    ], function () {
        Route::get('/', 'TestScheduleController@index')->name('index');
    });

    Route::group([
        'middleware' => 'logged',
    ], function () {
        Route::get('utilization/emp/search/ajax', 'DashboardController@empSearchAjax')
            ->name('utilization.emp.search.ajax');

        Route::get('resource/busy', 'BusyController@index')
            ->name('busy.index');
    });

    Route::get('resource/request/send-data/{token}', 'RequestController@sendDataWebVn')->name('sendDataRequest');
    Route::post('resource/candidate/insert-intranet', 'CandidateController@insertIntranet')->name('insertIntranet');
    Route::get('resource/candidate/languages/{token}', 'CandidateController@getLanguages')->name('getLanguages');
    Route::post('resource/candidate/change-status', 'CandidateController@changeStatus')->name('resource.changeStatus');

    Route::group([
        'middleware' => 'auth',
        'as' => 'libfolk.',
        'prefix' => 'resource/setting/libfolk',
    ], function () {
        Route::get('list', 'LibFolkController@index')->name('list');
        Route::post('create', 'LibFolkController@create')->name('create');
        Route::get('edit/{id}', 'LibFolkController@edit')->name('edit');
        Route::post('store', 'LibFolkController@store')->name('store');
    });

//hr weekly report
    Route::group(
        [
            'middleware' => 'auth',
            'as' => 'hr_wr.',
            'prefix' => 'resource/hr-weekly-report'
        ],
        function () {
            Route::get('/', 'HrWeeklyReportController@index')->name('index');
            Route::post('/save-note', 'HrWeeklyReportController@saveNote')->name('save_note');
        }
    );

//resource weekly report
    Route::group(
        [
            'middleware' => 'auth',
            'as' => 'enroll_addvice.',
            'prefix' => 'resource/enrollment-advice'
        ],
        function () {
            Route::get('/', 'EnrollmentAdviceController@index')->name('index');
            Route::post('/update-status', 'EnrollmentAdviceController@updateStatus')->name('update.status');
        }
    );

    Route::post('form/config/send-form', 'EnrollmentAdviceController@insert')->middleware('cors');

    Route::post('/resource/export-developers', 'ExportController@exportDevs')
        ->name('export.devs')
        ->middleware('auth');
//resource free effort
    Route::group([
        'middleware' => 'auth',
        'prefix' => 'resource/employees-available',
        'as' => 'available.'
    ], function () {
        Route::get('/', 'AvailableController@index')->name('index');
        Route::get('/project-in-time', 'AvailableController@projectInTime')->name('project.intime');
        Route::post('/save-notes', 'AvailableController@saveNote')->name('save_note');
        Route::post('/export', 'AvailableController@export')->name('export');
        Route::post('/update-data', 'AvailableController@updateData')->name('update_data');
    });

//staff statistic
    Route::group([
        'middleware' => 'auth',
        'prefix' => 'resource/staff-statistics',
        'as' => 'staff.stat.'
    ], function () {
        Route::get('/{timeType}', 'StaffStatController@index')
            ->name('index');
    });

    Route::group([
        'middleware' => 'logged',
        'as' => 'candidate.',
        'prefix' => 'resource/candidate',
    ], function () {
        Route::get('recommend', 'RecommendCandidateController@recommend')->name('recommend');
        Route::post('recommend', 'RecommendCandidateController@createRecommend')->name('create.recommend');
        Route::get('recommend/list', 'RecommendCandidateController@listMyRecommend')->name('list.recommend');
        Route::post('checkMailRecommend', 'RecommendCandidateController@checkMailRecommend')->name('checkMailRecommend');
        Route::get('recommend/edit/{id}', 'RecommendCandidateController@edit')->name('edit.recommend');
        Route::post('recommend/update/{id}', 'RecommendCandidateController@update')->name('update.recommend');
        Route::get('recommend/reapply/{id}', 'RecommendCandidateController@reapplyEdit')->name('reapply.edit');
        Route::post('recommend/re-apply', 'RecommendCandidateController@reapplyRecommend')->name('reapply.recommend');
        Route::post('recommend/ajaxSearchByRegion', 'RecommendCandidateController@SearchByRegion')->name('SearchByRegion');
        Route::get('recommend/ajaxGetListRecommendByChannel', 'RecommendCandidateController@ajaxGetListRecommendByChannel')->name('ajaxGetListRecommendByChannel');
    });
});
