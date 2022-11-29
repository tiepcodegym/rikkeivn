<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'prefix' => 'ot',
        'as' => 'ot.',
        'middleware' => 'logged',
    ], function () {
        // Route::get('/register', 'OtRegisterController@showRegisterForm')
        //     ->name('register');
        Route::get('admin/register', 'OtRegisterController@adminRegister')->name('admin-register');
        Route::get('/list/{empType}/{listType?}', 'OtRegisterController@getRegisterList')->where('empType', '[1-3]')->where('listType', '[1-4]')
            ->name('list');
        Route::get('/edit/{id?}', 'OtRegisterController@editRegister')->where('id', '[0-9]+')
            ->name('editot');
        Route::get('/detail/{id?}', 'OtRegisterController@detailRegister')->where('id', '[0-9]+')
            ->name('detail');
        Route::get('/searchemployee', 'OtEmployeeController@getEmployeeForSearch')
            ->name('searchemp');
        Route::get('ajax-search-employee', 'OtEmployeeController@ajaxSearchEmployee')
            ->name('ajax-search-employee');
        Route::get('ajax-search-approver', 'OtEmployeeController@searchEmployeeCanApproveAjax')
            ->name('ajax-search-approver');
        Route::get('/searchreg', 'OtRegisterController@getRegisterForSearch')
            ->name('searchreg');
        Route::get('/getProjectMember', 'OtEmployeeController@getProjectMember')
            ->name('getProjectMember');
        Route::get('/getProjectApprovers', 'OtEmployeeController@getProjectApprovers')
            ->name('getProjectApprovers');
        Route::get('/getTeamEmployee', 'OtEmployeeController@getTeamEmployee')
            ->name('getTeamEmployee');
        Route::post('/saveot', 'OtRegisterController@saveOt')
            ->name('saveot');
        Route::post('/save-admin-register', 'OtRegisterController@saveAdminRegister')
            ->name('save-admin-register');
        Route::get('/checkOccupiedTimeSlot', 'OtEmployeeController@checkOccupiedTimeSlot')
            ->name('checkOccupiedTimeSlot');
        Route::get('/ajax-check-occupied-time-slot', 'OtEmployeeController@ajaxCheckOccupiedTimeSlot')
            ->name('ajaxCheckOccupiedTimeSlot');
        Route::get('/ajaxCheckExistTimeSlotByEmployees', 'OtEmployeeController@ajaxCheckExistTimeSlotByEmployees')
            ->name('ajaxCheckExistTimeSlotByEmployees');
        Route::post('/approve', 'OtRegisterController@approve')
            ->name('approver.approve');
        Route::post('/massApprove', 'OtRegisterController@massApprove')
            ->name('approver.massApprove');
        Route::post('/delete', 'OtRegisterController@delete')
            ->name('delete');
        Route::post('/reject', 'OtRegisterController@reject')
            ->name('approver.reject');
        Route::post('/massReject', 'OtRegisterController@massReject')
            ->name('approver.massReject');
        Route::get('view-popup', 'OtRegisterController@showPopupDetailRegister')->name('view-popup');
        Route::get('ajax-change-registrant', 'OtRegisterController@ajaxChangeRegistrant')->name('ajax-change-registrant');
        Route::get('ajax-change-project', 'OtRegisterController@ajaxChangeProject')->name('ajax-change-project');
        Route::get('get-project-ot', 'OtRegisterController@getProjectOt')->name('get-project-ot');
    });

Route::group([
    'prefix' => 'ot',
    'as' => 'ot.',
    'middleware' => 'auth',
], function() {
    Route::get('/register', 'OtRegisterController@showRegisterForm')
        ->name('register');

    Route::group([
        'prefix' => 'manage',
        'as' => 'manage.',
    ], function() {
        Route::get('/report-ot/{team?}','OtController@reportOTApproved')->name('report_manage_ot')->where('team', '[0-9]+');
    });
});

    Route::group([
        'prefix' => 'timekeeping',
        'middleware' => 'logged',
    ], function () {
        Route::group([
            'prefix' => 'manage',
        ], function () {
            Route::get('/ot/{team?}', 'OtRegisterController@getManageList')
                ->name('profile.manage.ot')->where('team', '[0-9]+');
        });
    });
});
