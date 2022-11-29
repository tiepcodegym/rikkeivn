<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        'middleware' => 'logged',
    ], function () {
        Route::group([
            'prefix' => 'late-in-early-out',
            'as' => 'comelate.',
        ], function () {
//            // Route::get('register', 'ComeLateController@comelateRegister')->name('register');
//            Route::get('admin/register', 'ComeLateController@adminRegister')->name('admin-register');
//            Route::post('save', 'ComeLateController@comelateSaveRegister')->name('save');
//            Route::post('save-admin-register', 'ComeLateController@saveAdminRegister')->name('save-admin-register');
//            Route::get('edit/{id}', 'ComeLateController@comelateEditRegister')->name('edit')->where('id', '[0-9]+');
//            Route::post('update', 'ComeLateController@comelateUpdateRegister')->name('update');
//            Route::get('delete', 'ComeLateController@comelateDeleteRegister')->name('delete');
//            Route::get('approve', 'ComeLateController@comelateApproveRegister')->name('approve');
//            Route::get('disapprove', 'ComeLateController@comelateDisapproveRegister')->name('disapprove');
//            Route::get('view-popup', 'ComeLateController@comelateRegisterViewPopup')->name('view-popup');
//            Route::get('detail/{id}', 'ComeLateController@comelateDetailRegister')->name('detail')->where('id', '[0-9]+');
//            Route::get('register-list/{status?}', 'ComeLateController@comelateRegisterList')->name('register-list')->where('status', '[0-9]+');
//            Route::get('approve-list/{status?}', 'ComeLateController@comelateApproveList')->name('approve-list')->where('status', '[0-9]+');
            Route::get('find-employee', 'ComeLateController@findEmployee')->name('find-employee');
            Route::get('ajax-search-employee', 'ComeLateController@searchEmployeeAjax')->name('ajax-search-employee');
            Route::get('ajax-search-employee-ot', 'ComeLateController@searchEmployeeOtDisallowAjax')->name('ajax-search-employee-ot-disallow');	
            Route::get('ajax-search-employee-can-approve/{route}', 'ComeLateController@searchEmployeeCanApproveAjax')->name('ajax-search-employee-can-approve');
            Route::get('check-register-exist', 'ComeLateController@checkRegisterExist')->name('check-register-exist');
        });
    });
//    Route::group([
//        'prefix' => 'profile',
//        'as' => 'profile.',
//        'middleware' => 'auth',
//    ], function () {
//        Route::group([
//            'prefix' => 'late-in-early-out',
//            'as' => 'comelate.',
//        ], function () {
//            Route::get('register', 'ComeLateController@comelateRegister')->name('register');
//        });
//    });

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        'middleware' => 'logged',
    ], function () {
        Route::group([
            'prefix' => 'business-trip',
            'as' => 'mission.',
        ], function () {
            // Route::get('register', 'MissionController@missionRegister')->name('register');
            Route::get('admin/register', 'MissionController@adminRegister')->name('admin-register');
            Route::post('save', 'MissionController@missionSaveRegister')->name('save');
            Route::post('save-admin-register', 'MissionController@saveAdminRegister')->name('save-admin-register');
            Route::get('edit/{id}', 'MissionController@missionEditRegister')->name('edit')->where('id', '[0-9]+');
            Route::post('update', 'MissionController@missionUpdateRegister')->name('update');
            Route::get('delete', 'MissionController@missionDeleteRegister')->name('delete');
            Route::get('approve', 'MissionController@missionApproveRegister')->name('approve');
            Route::get('disapprove', 'MissionController@missionDisapproveRegister')->name('disapprove');
            Route::get('view-popup', 'MissionController@missionRegisterViewPopup')->name('view-popup');
            Route::get('detail/{id}', 'MissionController@missionDetailRegister')->name('detail')->where('id', '[0-9]+');
            Route::get('register-list/{status?}', 'MissionController@missionRegisterList')->name('register-list')->where('status', '[0-9]+');
            Route::get('approve-list/{status?}', 'MissionController@missionApproveList')->name('approve-list')->where('status', '[0-9]+');
            Route::get('relates-list/{status?}', 'MissionController@missionRelatesList')->name('relates-list')->where('status', '[0-9]+');
            Route::get('find-employee', 'MissionController@findEmployee')->name('find-employee');
            Route::get('check-register-exist', 'MissionController@checkRegisterExist')->name('check-register-exist');
            Route::get('register', 'MissionController@missionRegister')->name('register');
            Route::post('get-working-time-employees', 'MissionController@getWorkingTimeEmployees')->name('get-working-time-employees');
        });
    });


Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
    'middleware' => 'logged',
], function() {
    Route::group([
        'prefix' => 'supplement',
        'as' => 'supplement.',
    ], function() {
        // Route::get('register', 'SupplementController@supplementRegister')->name('register');
        Route::get('admin/register', 'SupplementController@adminRegister')->name('admin-register');
        Route::post('save', 'SupplementController@supplementSaveRegister')->name('save');
        Route::post('save-admin-register', 'SupplementController@saveAdminRegister')->name('save-admin-register');
        Route::get('edit/{id}', 'SupplementController@supplementEditRegister')->name('edit')->where('id', '[0-9]+');
        Route::post('update', 'SupplementController@supplementUpdateRegister')->name('update');
        Route::get('delete', 'SupplementController@supplementDeleteRegister')->name('delete');
        Route::get('approve', 'SupplementController@supplementApproveRegister')->name('approve');
        Route::get('disapprove', 'SupplementController@supplementDisapproveRegister')->name('disapprove');
        Route::get('view-popup', 'SupplementController@supplementRegisterViewPopup')->name('view-popup');
        Route::get('detail/{id}', 'SupplementController@supplementDetailRegister')->name('detail')->where('id', '[0-9]+');
        Route::get('register-list/{status?}', 'SupplementController@supplementRegisterList')->name('register-list')->where('status', '[0-9]+');
        Route::get('approve-list/{status?}', 'SupplementController@supplementApproveList')->name('approve-list')->where('status', '[0-9]+');
        Route::get('relates-list/{status?}', 'SupplementController@supplementRelatesList')->name('relates-list')->where('status', '[0-9]+');
        Route::get('find-employee', 'SupplementController@findEmployee')->name('find-employee');
        Route::get('check-register-exist', 'SupplementController@checkRegisterExist')->name('check-register-exist');
        Route::get('ajax-get-approver', 'SupplementController@ajaxGetApprover')->name('ajax-get-approver');
    });
});

Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
    'middleware' => 'auth',
], function() {
    Route::group([
        'prefix' => 'supplement',
        'as' => 'supplement.',
    ], function() {
        Route::get('register', 'SupplementController@supplementRegister')->name('register');
    });
});

Route::post('supplement-register', 'SupplementController@modalSupplementRegister');
Route::post('submit-supplement-register', 'SupplementController@supplementSaveRegister');

Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
    'middleware' => 'logged',
], function() {
    Route::group([
        'prefix' => 'leave-day',
        'as' => 'leave.',
    ], function() {
        // Route::get('register', 'LeaveDayController@register')->name('register');
        Route::get('admin/register', 'LeaveDayController@adminRegister')->name('admin-register');
        Route::post('save-admin-register', 'LeaveDayController@saveAdminRegister')->name('save-admin-register');
        Route::post('save', 'LeaveDayController@saveRegister')->name('save');
        Route::get('edit/{id}', 'LeaveDayController@editRegister')->name('edit')->where('id', '[0-9]+');
        Route::post('update', 'LeaveDayController@updateRegister')->name('update');
        Route::get('delete', 'LeaveDayController@deleteRegister')->name('delete');
        Route::get('approve', 'LeaveDayController@approveRegister')->name('approve');
        Route::get('disapprove', 'LeaveDayController@disapproveRegister')->name('disapprove');
        Route::get('view-popup', 'LeaveDayController@showPopupDetailRegister')->name('view-popup');
        Route::get('detail/{id}', 'LeaveDayController@showDetailRegister')->name('detail')->where('id', '[0-9]+');
        Route::get('register-list/{status?}', 'LeaveDayController@getRegisterList')->name('register-list')->where('status', '[0-9]+');
        Route::get('related-list/{status?}', 'LeaveDayController@getRelatedList')->name('related-list')->where('status', '[0-9]+');
        Route::get('approve-list/{status?}', 'LeaveDayController@getApproveList')->name('approve-list')->where('status', '[0-9]+');
        Route::get('find-employee', 'LeaveDayController@findEmployee')->name('find-employee');
        Route::get('check-register-exist', 'LeaveDayController@checkRegisterExist')->name('check-register-exist');
        Route::get('check-register-type-exist', 'LeaveDayController@checkRegisterTypeExist')->name('check-register-type-exist');
        Route::get('ajax-get-approver', 'LeaveDayController@ajaxGetApprover')->name('ajax-get-approver');
        Route::post('get-time-setting', 'LeaveDayController@getTimeSetting')->name('get-time-setting');
        Route::get('ajax-get-leave-reason', 'LeaveDayController@ajaxGetLeaveDayReason')->name('ajax-get-leave-reason');
        Route::get('ajax-get-leave-relation', 'LeaveDayController@ajaxGetLeaveDayRelation')->name('ajax-get-leave-relation');
        Route::get('acquisition-status', 'LeaveDayController@getAcquisitionStatus')->name('acquisition-status');
        Route::get('acquisition-status-detail/{id}/{date}', 'LeaveDayController@getAcquisitionStatusDetail')->name('acquisition-status-detail');
        Route::get('view-popup-detail', 'LeaveDayController@showPopupDetailAcquisitionStatus')->name('view-popup-detail');
        Route::get('reapply/{id}', 'LeaveDayController@reapplyRegister')->name('reapply')->where('id', '[0-9]+');
        // Route::post('export-excel-acquisition-status', 'LeaveDayController@exportExcelAcquisitionStatus')->name('export-excel-acquisition-status');
    });
});

Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
    'middleware' => 'auth',
], function() {
    Route::group([
        'prefix' => 'leave-day',
        'as' => 'leave.',
    ], function() {
        Route::get('register', 'LeaveDayController@register')->name('register');
    });
});

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => 'auth',
], function() {
    Route::group([
        'prefix' => 'timekeeping-management',
        'as' => 'timekeeping-management.',
    ], function() {
        Route::get('setting', 'TimekeepingManagementController@index')->name('index');
        Route::post('setting/update', 'TimekeepingManagementController@update')->name('update');
    });
});

Route::group([
    'prefix' => 'timekeeping',
    'as' => 'timekeeping.',
    'middleware' => 'logged',
], function() {
    Route::get('manage-timekeeping-table', 'TimekeepingController@manageTimekeepingTable')->name('manage-timekeeping-table');
    Route::post('save-timekeeping-table', 'TimekeepingController@saveTimekeepingTable')->name('save-timekeeping-table');
    Route::get('timekeeping-detail/{timekeepingTableId?}', 'TimekeepingController@getTimekeepingDetail')
        ->name('timekeeping-detail')
        ->where('timekeepingTableId', '[0-9]+');
    Route::get('timekeeping-aggregate/{timekeepingTableId?}', 'TimekeepingController@getTimekeepingAggregate')
        ->name('timekeeping-aggregate')
        ->where('timekeepingTableId', '[0-9]+');
    Route::post('upload-file', 'TimekeepingController@uploadFileTimekeeping')->name('upload-file');
    Route::post('post-upload-file', 'TimekeepingController@postUploadTimekeeping')->name('post-upload-file');
    Route::post('import-time-in-out', 'TimekeepingController@importTimeInOut')->name('import-time-in-out');
    Route::post('get-data-related-module', 'TimekeepingController@getDataFromRelatedModules')->name('get-data-related-module');
    Route::post('update-day-off', 'TimekeepingController@updateDayOff')->name('update-day-off');
    Route::post('update-timekeeping-aggregate', 'TimekeepingController@updateTimekeepingAggregate')->name('update-timekeeping-aggregate');
    Route::post('add-emp-to-timekeeping', 'TimekeepingController@addEmpToTimekeeping')->name('addEmpToTimekeeping');
    Route::post('remove-emp-from-timekeeping', 'TimekeepingController@removeEmpFromTimekeeping')->name('removeEmpFromTimekeeping');
    Route::get('export-aggregate/{timekeepingTableId}', 'TimekeepingController@exportTimkeepingAggregate')->name('export-aggregate')->where('timekeepingTableId', '[0-9]+');
    Route::get('export-detail/{timekeepingTableId}', 'TimekeepingController@exportTimekeepingDetail')->name('export-detail')->where('timekeepingTableId', '[0-9]+');
    Route::get('export-late-minutes/{timekeepingTableId}', 'TimekeepingController@exportLateMinutes')->name('export-late-minutes')->where('timekeepingTableId', '[0-9]+');
    Route::get('ajax-get-timekeeping-table', 'TimekeepingController@ajaxGetTimekepingTables')->name('ajax-get-timekeeping-table');
    Route::delete('delete-timekeeping-table','TimekeepingController@deleteTimekeepingTable')->name('delete-timekeeping-table');
    Route::post('save-row-keeping', 'TimekeepingController@saveRowKeeping')->name('saveRowKeeping');
    Route::post('update-time-timekeeping-table', 'TimekeepingController@updateTimeTableTimekeeping')->name('update-time-timekeeping-table');
    Route::post('update-lock-up', 'TimekeepingController@updateLockUp')->name('update-lock-up');
    Route::get('list-employee-after-lock/{id}', 'TimekeepingController@getEmpAfterLock')->name('list-employee-after-lock');
    Route::post('get-data-aggregate-module-wfh', 'TimekeepingController@getDataFromAggregateModulesWfh')->name('get-data-aggregate-module-wfh');
});

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => 'auth',
], function() {
    Route::group([
        'prefix' => 'manage-day-of-leave',
        'as' => 'manage-day-of-leave.',
    ], function() {
        Route::get('/', 'LeaveDayManageController@index')->name('index');
        Route::post('edit', 'LeaveDayManageController@edit')->name('edit');
        Route::get('delete', 'LeaveDayManageController@delete')->name('delete');
        Route::post('import', 'LeaveDayManageController@importFile')->name('import');
        Route::get('export', 'LeaveDayManageController@exportFile')->name('export');
        Route::get('histories-list', 'LeaveDayManageController@viewHistory')->name('histories');
        Route::get('histories-detail/{id}', 'LeaveDayManageController@viewHistoryDetail')->name('histories.detail');
    });
});

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => 'auth',
], function() {
    Route::group([
        'prefix' => 'manage-reason-leave',
        'as' => 'manage-reason-leave.',
    ], function() {
        Route::get('/', 'LeaveReasonManageController@listReason')->name('index');
        Route::post('save', 'LeaveReasonManageController@saveReason')->name('save');
        Route::get('check-name', 'LeaveReasonManageController@checkName')->name('check-name');
        Route::post('/delete/{id}', 'LeaveReasonManageController@deleteReason')->name('delete')->where('id', '[0-9]+');
    });

    Route::group([
        'prefix' => 'staff-are-late',
        'as' => 'staff-late.',
    ], function() {
        Route::get('/', 'ComeLateController@showNotLateTime')->name('show');
        Route::post('create-not-late-time', 'ComeLateController@createNotLateTime')->name('create-not-late-time');
        Route::post('update-not-late-time', 'ComeLateController@updateNotLateTime')->name('update-not-late-time');
        Route::post('delete-not-late-time', 'ComeLateController@deleteNotLateTime')->name('delete-not-late-time');

        Route::group([
            'prefix' => 'not-late',
            'as' => 'not-late.',
        ], function() {
            Route::get('/', 'ComeLateController@showNotLate')->name('show');
            Route::post('update-not-late', 'ComeLateController@updateNotLate')->name('update-not-late');
            Route::post('create-not-late', 'ComeLateController@createNotLate')->name('create-not-late');
            Route::post('delete-not-late', 'ComeLateController@deleteNotLate')->name('delete-not-late');
        });
    });
});


Route::group([
    'prefix' => 'timekeeping',
    'as' => 'timekeeping.',
    'middleware' => 'logged',
], function() {
    Route::group([
        'prefix' => 'manage',
        'as' => 'manage.',
    ], function() {
        Route::get('late-in-early-out/{team?}', 'ComeLateController@comelateManageList')->name('comelate')->where('team', '[0-9]+');
        Route::get('business-trip/{team?}', 'MissionController@missionManageList')->name('mission')->where('team', '[0-9]+');
        Route::get('supplement/{team?}', 'SupplementController@supplementManageList')->name('supplement')->where('team', '[0-9]+');
        Route::get('leave-day/{team?}', 'LeaveDayController@getManageList')->name('leave')->where('team', '[0-9]+');
        Route::get('report/{team?}/{country?}', 'ReportController@index')->name('report')->where(['team'=>'[0-9]{2}-[0-9]{4}','country'=>"[1-2]{1}"]);
        Route::post('export', 'ReportController@export')->name('export');
        Route::get('report-business-trip/{team?}', 'MissionController@reportApproved')->name('report-business-trip')->where('team', '[0-9]+');
        Route::get('report-business-trip-export/{team?}', 'MissionController@reportApprovedExport')->name('report-business-trip-export')->where('team', '[0-9]+');
        Route::get('report-project-timekeeping-systena', 'ProjectController@index')->name('report_project_timekeeping_systena');
        Route::post('export-project-timekeeping-systena', 'ProjectController@exportProjectSystena')->name('export_project_timekeeping_systena');
        // Route::get('export-project-timekeeping-systena', 'ProjectController@exportProjectSystena')->name('export_project_timekeeping_systena');
        Route::post('add-employee-project-systena', 'ProjectController@addProjectSystena')->name('add_project_systena');
        Route::post('delete-employee-project-systena', 'ProjectController@removeEmpProjSystena')->name('delete_project_systena');
    });
});

// Route::group([
//     'prefix' => 'timekeeping',
//     'as' => 'timekeeping.',
//     'middleware' => 'logged',
// ], function() {
//     Route::group([
//         'prefix' => 'salary',
//         'as' => 'salary.',
//     ], function() {
//         Route::get('salary-table-list', 'SalaryController@salaryTableList')->name('salary-table-list');
//         Route::post('save-salary-table', 'SalaryController@saveSalaryTable')->name('save-salary-table');
//         Route::get('salary-table-detail/{id}', 'SalaryController@salaryTableDetail')->name('salary-table-detail')->where('id', '[0-9]+');
//         Route::post('upload-salary-table', 'SalaryController@uploadFileSalaryTable')->name('upload-salary-table');
//     });
// });

// Route::group([
//     'prefix' => 'profile',
//     'as' => 'profile.',
//     'middleware' => 'logged',
// ], function() {
//     Route::group([
//         'prefix' => 'salary',
//         'as' => 'salary.',
//     ], function() {
//         Route::get('salary-list', 'SalaryController@salaryList')->name('salary-list');
//         Route::get('salary-detail/{id?}', 'SalaryController@salaryDetail')->name('salary-detail')->where('id', '[0-9]+');
//         Route::get('timekeeping-detail/{id}', 'SalaryController@timekeepingDetail')->name('timekeeping-detail')->where('id', '[0-9]+');
//     });
// });

    Route::post('get-leave-register', 'LeaveDayController@getLeaveRegister');
    //Route::post('leave-register', 'LeaveDayController@leaveRegister');
    Route::post('leave-register', 'LeaveDayController@saveRegister');

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        // 'middleware' => 'logged',
        'middleware' => 'auth',
    ], function () {
        Route::get('timekeeping-list', 'TimekeepingController@getTimekeepingListByEmployee')->name('timekeeping-list');
        Route::get('timekeeping/{id?}', 'TimekeepingController@getTimekeepingDetailByEmployee')->name('timekeeping')->where('id', '[0-9]+');
    });

    Route::group([
        'prefix' => 'working-times',
        'as' => 'wktime.'
    ], function () {
        Route::group(['middleware' => 'logged'], function () {
            Route::get('/', 'WorkingTimeCompayController@index')->name('index');
            Route::get('register', 'WorkingTimeCompayController@register')->name('register');
            Route::post('register', 'WorkingTimeCompayController@saveRegister')->name('save_register');
            Route::get('edit/{id}', 'WorkingTimeCompayController@edit')->name('edit')->where('id', '[0-9]+');
            Route::post('update/{id}', 'WorkingTimeCompayController@update')->name('update')->where('id', '[0-9]+');
            Route::get('detail/{id}', 'WorkingTimeCompayController@edit')->name('detail')->where('id', '[0-9]+');
            Route::get('register-list/{status?}', 'WorkingTimeCompayController@listRegister')->name('register.list');
            Route::delete('register/{id}', 'WorkingTimeCompayController@deleteRegister')->name('delete')->where('id', '[0-9]+');
            Route::get('register-apporve-list/{status?}', 'WorkingTimeCompayController@listRegisterApprove')->name('register.approve.list');
            Route::post('approve-register', 'WorkingTimeCompayController@approveRegister')->name('approve_register');
            Route::get('register-related-list/{status?}', 'WorkingTimeCompayController@listRegisterRelated')->name('register.related.list');
           

            // Route::get('register-apporve-list/{status?}', 'WorkingTimeController@listRegisterApprove')->name('register.approve.list');
            Route::get('search-approver', 'WorkingTimeController@searchApprover')->name('search_approver');
            Route::get('logs', 'WorkingTimeController@logWorkingTime')->name('log_time');
            Route::post('logs', 'WorkingTimeController@saveLogWorkingTime')->name('save_log_time');
        });
        Route::group([
            'middleware' => 'auth',
            'prefix' => 'manage',
            'as' => 'manage.'
        ], function () {
            Route::get('/', 'WorkingTimeCompayController@listManage')->name('list');
            Route::get('/log-times', 'WorkingTimeController@listLogTimes')->name('list.logs');
        });
        Route::group([
            'middleware' => 'auth',
        ], function () {
        });
    });

    //team 
    Route::group([
        'prefix' => 'team',
        'as' => 'division.',
        'middleware' => 'logged',
    ], function () {
        Route::get('list-timekeeping-aggregates/{tkTableId?}', 'TimekeepingController@getTimekeepingAggregates')->name('list-tk-aggregates');
        Route::get('ajax-get-tk-table', 'TimekeepingController@ajaxGetTKTables')->name('ajax-get-tk-table');
        Route::get('timekeeping/{idTable}/{idEmp}', 'TimekeepingController@getTkgDetailByEmployee')->name('timekeeping')->where('idEmp', '[0-9]+');
        Route::get('lead-export-aggregate/{idTable}', 'TimekeepingController@leadExportAggregate')->name('lead-export-aggregate');
        Route::get('list-day-of-leave', 'LeaveDayManageController@listDayOfLeave')->name('list-day-of-leave');
        Route::get('late-minute-report', 'TimekeepingController@reportMinuteLate')->name('late-minute-report');
        Route::post('export-late-minute', 'TimekeepingController@exportMinuteLate')->name('export-late-minute');
    });

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        'middleware' => 'auth',
    ], function() {
        Route::group([
            'prefix' => 'work-place-management',
            'as' => 'wpmanagement.',
        ], function() {
            Route::get('/', 'WorkPlaceController@index')->name('index');
            Route::post('/import', 'WorkPlaceController@importFile')->name('import');
            Route::get('/export', 'WorkPlaceController@exportFile')->name('export');
        });
    });
    
    Route::group([
        'middleware' => 'auth',
        'as' => 'hr.',
    ], function () {
        Route::get('hr/report-onsite', 'MissionController@reportOnsiteWithYear')->name('report-onsite');
        Route::get('hr/export-report-onsite', 'MissionController@exportOnsite')->name('export-onsite');
        Route::post('hr/grateful-store', 'GratefulEmployeeOnsiteController@store')->name('grateful-store');
        Route::post('hr/grateful-remove', 'GratefulEmployeeOnsiteController@remove')->name('grateful-remove');
    });
});

Route::group([
    'middleware' => 'logged',
], function() {
    Route::get('export-number-leave-day-employee-onsite/{year}', 'ReportController@exportLeaveDaysEmployeeOnsite')->where('year', '[0-9]+');
});

