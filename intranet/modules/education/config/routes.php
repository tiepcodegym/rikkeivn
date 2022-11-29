<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'middleware' => 'logged',
        'as' => 'education.teaching.',
        'prefix' => 'manager',
    ], function () {
        Route::get('teachings/create', 'RegisterTeachingController@create')->name('teachings.create');
        Route::get('teachings/{id}', 'RegisterTeachingController@show')->name('teachings.show');
        Route::get('teachings/{id}/show-detail', 'RegisterTeachingController@showDetail')->name('teachings.show_detail');
        Route::post('teachings', 'RegisterTeachingController@store')->name('teachings.store');
        Route::put('teachings/{id}', 'RegisterTeachingController@update')->name('teachings.update');
        Route::get('teachings/{id}/send', 'RegisterTeachingController@send')->name('teachings.send');
        Route::get('teachings', 'RegisterTeachingController@index')->name('teachings.index');
        Route::get('courses/type/{type_id}', 'RegisterTeachingController@getCourseTypeId')->name('courses.get-type');
        Route::get('courses/{course_id}', 'RegisterTeachingController@getCourse')->name('courses.get-course');
        Route::get('courses/register/{id}', 'RegisterTeachingController@register')->name('courses.register');
        Route::get('classes/{course_id}', 'RegisterTeachingController@getClassByCourseId')->name('classes.get-class');
        Route::get('class_detail/{class_id}', 'RegisterTeachingController@getClassDetailById')->name('class_detail.get-class-detail');
        Route::get('register-teaching/detail/{course_type_id}/{course_id}/{class_id}', 'RegisterTeachingController@getRegisterTeaching')->name('get-register-teaching');
        Route::post('/import-employee', 'EducationCourseController@import')->name('import');
    });

    Route::group([
        'middleware' => 'auth',
        'as' => 'education.teaching.',
        'prefix' => 'manager',
    ], function () {
        Route::get('hr/teachings', 'RegisterTeachingController@managerTeachings')->name('hr.index');
        Route::put('hr/teachings/{id}', 'RegisterTeachingController@updateCurator')->name('hr.update');
        Route::put('hr/teachings/reject/{id}', 'RegisterTeachingController@updateReject')->name('hr.reject');
        Route::get('ajax-course-id', 'RegisterTeachingController@getCourseId')->name('ajax-course-id');
    });

    Route::group([
        //    'middleware' => 'auth',
        'as' => 'education.ot.',
        'prefix' => 'team'
    ], function(){
        Route::get('OT_list', 'EmployeeOTController@index')->name('index');
        Route::post('/export-ot-list', 'EmployeeOTController@exportOTList')->name('export.ot_list');
    });
});

Route::get('/route-here', 'MainModelNameController@doHere')->name('doHere');
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
        'prefix' => 'team/training-request',
    ], function () {
        Route::get('list', 'EducationRequestController@index')->name('request.list');
        Route::get('create', 'EducationRequestController@create')->name('request.create');
        Route::post('create', 'EducationRequestController@store')->name('request.store');
        Route::get('edit/{id}', 'EducationRequestController@edit')->name('request.edit');
        Route::post('edit/{id}', 'EducationRequestController@update')->name('request.update');
    });
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
        'prefix' => 'hr/training-request',
    ], function () {
        Route::get('list', 'EducationRequestController@hrIndex')->name('request.hr.list');
        Route::get('create', 'EducationRequestController@hrCreate')->name('request.hr.create');
        Route::post('create', 'EducationRequestController@hrStore')->name('request.hr.store');
        Route::get('edit/{id}', 'EducationRequestController@hrEdit')->name('request.hr.edit');
        Route::post('edit/{id}', 'EducationRequestController@hrUpdate')->name('request.hr.update');
        Route::post('export-education-request', 'EducationRequestController@export')->name('request.hr.export');
    });
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
        'prefix' => 'training/ajax/',
    ], function () {
        Route::get('tag/list', 'EducationRequestController@getTagAjax')->name('request.ajax-tag-list');
        Route::get('title/list', 'EducationRequestController@getTitleAjax')->name('request.ajax-title-list');
        Route::get('person-assigned/list', 'EducationRequestController@getPersonAssignedAjax')->name('request.ajax-person-assigned-list');
        Route::get('course/list', 'EducationRequestController@getCourseAjax')->name('request.ajax-course-list');
    });
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
        'prefix' => 'HR/course',
    ], function () {
        Route::get('list', 'EducationCourseController@index')->name('list');
        Route::get('new', 'EducationCourseController@create')->name('new');
        Route::get('ajax-course-code', 'EducationCourseController@getCourseCode')->name('ajax-course-code');
        Route::get('ajax-giang-vien', 'EducationCourseController@getGiangVien')->name('ajax-giang-vien');
        Route::get('ajax-hr_id', 'EducationCourseController@getHrId')->name('ajax-hr_id');
        Route::post('getMaxCourseCode', 'EducationCourseController@getMaxCourseCode')->name('MaxCourseCode');
        Route::post('getMaxClassCode', 'EducationCourseController@getMaxClassCode')->name('MaxClassCode');
        Route::post('getEmpCodeById', 'EducationCourseController@getEmpCodeById')->name('getEmpCodeById');
        Route::post('addCourse', 'EducationCourseController@addCourse')->name('addCourse');
        Route::post('copyCourse', 'EducationCourseController@copyCourse')->name('copyCourse');
        Route::post('updateCourse', 'EducationCourseController@updateCourse')->name('updateCourse');
        Route::post('checkEmailTeacher', 'EducationCourseController@checkEmailTeacher')->name('checkEmailTeacher');
        Route::post('updateCourseInfo', 'EducationCourseController@updateCourseInfo')->name('updateCourseInfo');
        Route::get('detail/{id}/{flag}', 'EducationCourseController@detail')->name('detail')->where('id', '[0-9]+');
        Route::get('detail/{id}/{flag?}', 'EducationCourseController@detail')->name('detailv2')->where('id', '[0-9]+');
        Route::post('/export', 'EducationCourseController@export')
            ->name('export');
        Route::post('export-list/{id}/{flag}', 'EducationCourseController@exportListManager')
            ->name('export-list')->where('id', '[0-9]+');
        Route::post('export-result/{id}/{flag}', 'EducationCourseController@exportResultManager')
            ->name('export-result')->where('id', '[0-9]+');
        Route::get('getFormCalendar', 'EducationCourseController@getFormCalendar')->name('getFormCalendar');
        Route::get('ajaxSearchEmployeeEmail', 'EducationCourseController@ajaxSearchEmployeeEmail')->name('ajaxSearchEmployeeEmail');
        Route::get('searchEmployeeAjaxEmailList', 'EducationCourseController@searchEmployeeAjaxEmailList')->name('searchEmployeeAjaxEmailList');
        Route::get('searchEmployeeAjaxNameList', 'EducationCourseController@searchEmployeeAjaxNameList')->name('searchEmployeeAjaxNameList');
        Route::get('searchEmployeeAjaxNameCodeList', 'EducationCourseController@searchEmployeeAjaxNameCodeList')->name('searchEmployeeAjaxNameCodeList');
        Route::get('searchHrAjaxList', 'EducationCourseController@searchHrAjaxList')->name('searchHrAjaxList');
    });

    // Start employee certificates
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
    ], function () {
        Route::get('hr/certificates/', 'CertificateController@index')
            ->name('certificates.index');
        Route::post('hr/certificates/export', 'CertificateController@export')->name('certificates.export');
    });
    // End employee certificates

    Route::group([
        'middleware' => 'auth',
        'as' => 'education.settings.',
        'prefix' => 'setting/educations',
    ], function () {
        Route::get('types', 'SettingEducationController@index')->name('types.index');
        Route::get('types/create', 'SettingEducationController@create')->name('types.create');
        Route::post('types/create', 'SettingEducationController@store')->name('types.store');
        Route::get('types/{id}', 'SettingEducationController@show')->name('types.show');
        Route::get('types/{id}/show-detail', 'SettingEducationController@showDetail')->name('types.show_detail');
        Route::put('types/{id}', 'SettingEducationController@update')->name('types.update');
        Route::post('types/{id}/check-exit-code', 'SettingEducationController@checkExitCodeEducation')->name('types.check-exit-code');
        Route::delete('types/{id}', 'SettingEducationController@delete')->name('types.delete');

        Route::get('branches', 'SettingAddressMailController@index')->name('branch-mail');
        Route::get('branches/{id}', 'SettingAddressMailController@show')->name('show-mail');
        Route::post('branches/{id}', 'SettingAddressMailController@update')->name('update-mail');

        Route::get('template-mails/{type?}', 'SettingTemplateMailController@index')->name('index-template');
        Route::post('template-mails', 'SettingTemplateMailController@updateTemplate')->name('update-template');
    });

    // Start Manger employee education
    Route::group([
        'middleware' => 'auth',
        'as' => 'education.',
    ], function () {
        Route::get('manager/employees', 'EmployeeManagerController@index')->name('manager.employee.index');
        Route::get('manager/employees/detail/{id}', 'EmployeeManagerController@detail')->name('manager.employee.detail');
        Route::get('manager/employees/ajax-education-detail', 'EmployeeManagerController@ajaxEducationDetail')->name('manager.employee.ajax.education.detail');
        Route::post('manager/employees/export', 'EmployeeManagerController@educationExport')->name('manager.employee.export');
    });
    // End Manger employee education

    Route::group([
        'middleware' => 'logged',
        'as' => 'education-profile.',
        'prefix' => 'profile/course',
    ], function () {
        Route::post('getMaxCourseCode', 'EducationCourseController@getMaxCourseCode')->name('MaxCourseCode');
        Route::post('getMaxClassCode', 'EducationCourseController@getMaxClassCode')->name('MaxClassCode');
        Route::post('getEmpCodeById', 'EducationCourseController@getEmpCodeById')->name('getEmpCodeById');
        Route::post('sendFeedback', 'EducationCourseController@sendFeedback')->name('sendFeedback');
        Route::post('registerShift', 'EducationCourseController@registerShift')->name('registerShift');
        Route::get('detail/{id}/{flag}', 'EducationCourseController@detailEmployees')->name('detail')->where('id', '[0-9]+');
        Route::post('/export', 'EducationCourseController@export')
            ->name('export');
        Route::post('export-list/{id}/{flag}', 'EducationCourseController@exportListManager')
            ->name('export-list')->where('id', '[0-9]+');
        Route::post('export-result/{id}/{flag}', 'EducationCourseController@exportResultManager')
            ->name('export-result')->where('id', '[0-9]+');
        Route::get('getFormCalendar', 'EducationCourseController@getFormCalendar')->name('getFormCalendar');
        Route::post('addDocumentFromTeacher', 'EducationCourseController@addDocumentFromTeacher')->name('addDocumentFromTeacher');
    });

    Route::group([
        'middleware' => 'logged',
        'as' => 'profile.',
        'prefix' => 'profile/course',
    ], function () {
        Route::get('list', 'EducationCourseController@profileList')->name('profileList');
        Route::get('/{id_class}/{id_shift}/register', 'EducationCourseController@register')->name('register')->where('id_class', '[0-9]+')->where('id_shift', '[0-9]+');
        Route::get('/{id_shift}/delete', 'EducationCourseController@delete')->name('delete')->where('id_shift', '[0-9]+');
    });
});

