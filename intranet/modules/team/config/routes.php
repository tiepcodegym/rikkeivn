<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'prefix' => 'setting/team',
        'as' => 'setting.team.',
        'middleware' => 'auth',
    ], function () {
        Route::get('index', 'SettingController@index')->name('index');
        Route::get('/', 'SettingController@index')->name('index');

        //setting team
        Route::get('view/{id}', 'TeamController@view')->name('view')->where('id', '[0-9]+');
        Route::get('edit/{id}', 'TeamController@edit')->name('edit')->where('id', '[0-9]+');
        Route::post('move', 'TeamController@move')->name('move');
        Route::post('save', 'TeamController@save')->name('save');
        Route::delete('delete', 'TeamController@delete')->name('delete');
        //ajax
        Route::get('init', 'TeamController@initTeamSetting')->name('init');

        //setting position team
        Route::group([
            'prefix' => 'position',
            'as' => 'position.',
        ], function () {
            Route::get('view/{id}', 'PositionController@view')->name('view')->where('id', '[0-9]+');
            Route::post('move', 'PositionController@move')->name('move');
            Route::post('save', 'PositionController@save')->name('save');
            Route::delete('delete', 'PositionController@delete')->name('delete');
        });

        //setting rule
        Route::group([
            'prefix' => 'rule',
            'as' => 'rule.',
        ], function () {
            Route::post('save', 'PermissionController@saveTeam')->name('save');
        });
    });

//setting role
    Route::group([
        'prefix' => 'setting/role',
        'as' => 'setting.role.',
        'middleware' => 'auth',
    ], function () {
        Route::get('view/{id}', 'RoleController@view')->name('view')->where('id', '[0-9]+');
        Route::post('save', 'RoleController@save')->name('save');
        Route::delete('delete', 'RoleController@delete')->name('delete');
        Route::post('rule/save', 'PermissionController@saveRole')->name('rule.save');
    });

//manage setting
    Route::group([
        'prefix' => 'setting',
        'as' => 'setting.',
        'middleware' => 'auth',
    ], function () {
        //setting acl action
        Route::group([
            'prefix' => 'acl',
            'as' => 'acl.'
        ], function () {
            Route::get('/', 'AclController@index')->name('index');
            Route::get('create', 'AclController@create')->name('create');
            Route::get('edit/{id}', 'AclController@edit')->name('edit')->where('id', '[0-9]+');
            Route::post('save', 'AclController@save')->name('save');
            Route::delete('delete', 'AclController@delete')->name('delete');
        });
    });


//team 
    Route::group([
        'prefix' => 'team',
        'as' => 'team.',
        'middleware' => 'auth',
    ], function () {
        // member manage
        Route::group([
            'prefix' => 'member',
            'as' => 'member.'
        ], function () {
            //Route::get('edit/{id}','MemberController@edit')->name('edit')->where('id', '[0-9]+');
            //Route::post('save','MemberController@save')->name('save');
            //Route::delete('leave','MemberController@leave')->name('leave');
            Route::get('upload', 'MemberController@getUploadMember')->name('get-upload-member');
            Route::post('upload', 'MemberController@postUploadMember')->name('post-upload-member');
            Route::get('check-uploaded', 'MemberController@checkUploadMember')->name('check-uploaded');
            Route::post('export', 'MemberController@exportMembers')->name('export_member');
            Route::get('upload-family-info', 'MemberController@getUploadFamilyInfo')->name('get-upload-family-info');
            Route::post('upload-family-info', 'MemberController@postUploadFamilyInfo')->name('post-upload-family-info');
        });
    });

    Route::group([
        'middleware' => 'logged',
        'prefix' => 'search/autocomplete',
        'as' => 'search.autocomplete.'
    ], function () {
        Route::get('skill', 'AjaxController@skillSearch')
            ->name('skill');
        Route::get('edu', 'AjaxController@eduSearch')
            ->name('edu');
        Route::get('cer', 'AjaxController@cerSearch')
            ->name('cer');
    });

    Route::group([
        'middleware' => 'logged',
        'prefix' => 'team/report',
        'as' => 'team.report'
    ], function () {
        Route::get('certificates', 'CertificateController@index')
            ->name('certificates');
        Route::post('reportCertificate', 'CertificateController@report')
            ->name('reportCertificate');
        Route::get('exportCertificate', 'CertificateController@export')
            ->name('exportCertificate');
        Route::post('cookieCertificate', 'CertificateController@createCookeiFilter')
            ->name('cookieCertificate');
        Route::post('forgetCertificate', 'CertificateController@forgetCookieFilter')
            ->name('forgetCertificate');
    });

    Route::get('team/member/{statusWork?}', 'MemberController@index')
        ->middleware('logged')
        ->name('team.member.index')
        ->where('id', '[0-9]+')
        ->where('statusWork', '[a-z]+');

    Route::group([
        'middleware' => 'logged',
        'prefix' => 'profile',
        'as' => 'member.profile.',
    ], function () {
        Route::get('create', 'ProfileController@create')
            ->name('create');
        Route::get('welfare', 'ProfileController@welfare')
            ->name('welfare');
        // typeId = int or "create"
        Route::get('{employeeId?}/{type?}/{typeId?}', 'ProfileController@profile')
            ->name('index')
            ->where('employeeId', '[0-9]+');
        Route::post('{employeeId?}/save/{type?}/{typeId?}', 'ProfileController@save')
            ->name('save')
            ->where('employeeId', '[0-9]+')
            ->where('typeId', '[0-9]+');
        Route::post('{employeeId?}/save/status/{type?}/{typeId?}','ProfileController@saveEmployeeStatus')
            ->name('save.employee.status')
            ->where('employeeId', '[0-9]+')
            ->where('typeId', '[0-9]+');
        Route::post('{employeeId?}/save/status/{type?}/{typeId?}/ajax','ProfileController@changeEmployeeApprover')
            ->name('change.employee.approver')
            ->where('employeeId', '[0-9]+')
            ->where('typeId', '[0-9]+');
        Route::post('{employeeId?}/save/status/{type?}/{typeId?}/save','ProfileController@saveImage')
            ->name('save.image')
            ->where('employeeId', '[0-9]+')
            ->where('typeId', '[0-9]+');
        Route::post('delete/{id}', 'ProfileController@delete')
            ->name('delete')
            ->where('id', '[0-9]+');
        Route::delete('{employeeId?}/delete/{type?}/{typeId?}/{itemId?}', 'ProfileController@deleteItem2')
            ->name('delete.relative2')
            ->where('employeeId', '[0-9]+')
            ->where('typeId', '[0-9]+');
//    Route::get('{employeeId}/{type}/create','ProfileController@profile')
//        ->name('item.relate.create')
//        ->where('employeeId', '[0-9]+');
        Route::post('{employeeId}/{type}/delete/{typeId?}', 'ProfileController@deleteItemRelate')
            ->name('item.relate.delete')->where('employeeId', '[0-9]+');
        Route::post('{employeeId?}/cv', 'ProfileController@feedbackSkillSheet')
            ->name('skillsheet.feedback');
        Route::get('{employeeId?}/cv/comment/list/ajax', 'ProfileController@commentListAjax')
            ->name('skillsheet.comment.list.ajax')->where('id', '[0-9]+');
        Route::post('{employeeId}/cv/export', 'ProfileController@exportCv')
            ->where('employeeId', '[0-9]+')
            ->name('skillsheet.export');
        Route::post('{employeeId}/{type}/check-exists', 'ProfileController@checkExists')
            ->where('employeeId', '[0-9]+')
            ->name('check_exists');
        /*Route::group([
           'prefix' => 'prize',
            'as'    => 'prize.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@listPrize')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createPrize')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{id}/{employee_id?}', 'ProfileController@editPrize')->name('edit')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deletePrize')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@savePrize')->name('save')->where('employee_id', '[0-9]+');
        });*/
        /*Route::get('{employeeId?}/work','ProfileController@workInfo')
            ->name('work')
            ->where('employee_id', '[0-9]+');
        Route::get('{employeeId?}/contact','ProfileController@contact')
            ->name('contact')
            ->where('employee_id', '[0-9]+');
        Route::get('{employeeId?}/health','ProfileController@health')
            ->name('health')
            ->where('employee_id', '[0-9]+');
        Route::get('{employeeId?}/hobby','ProfileController@hobby')
            ->name('hobby')
            ->where('employee_id', '[0-9]+');

        Route::match(['get','post'],'costume/edit/{employee_id?}','ProfileController@costume')->name('costume')->where('employee_id', '[0-9]+');
        Route::match(['get','post'],'politic/edit/{employee_id?}','ProfileController@politic')->name('politic')->where('employee_id', '[0-9]+');
        Route::match(['get','post'],'military/edit/{employee_id?}','ProfileController@military')->name('military')->where('employee_id', '[0-9]+');
        Route::group([
            'prefix' => 'japaninfo',
            'as'    => 'japaninfo.',
        ], function() {
            Route::get('/{employee_id?}', 'ProfileController@japanInfo')->name('index')->where('employee_id', '[0-9]+');
        });
        Route::group([
            'prefix' => 'cv',
            'as' => 'cv.',
        ], function() {
            Route::get('/{employee_id?}', 'ProfileController@myCV')->name('index')->where('employee_id', '[0-9]+');
            Route::get('savecv/{employee_id?}', 'ProfileController@pdfSave')->name('savecv')->where('employee_id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'relationship',
            'as'    => 'relationship.',
        ], function(){
           Route::get('/{employee_id?}', 'ProfileController@relationship')->name('index')->where('employee_id', '[0-9]+');
           Route::get('create/{employee_id?}', 'ProfileController@createRelationship')->name('create')->where('employee_id', '[0-9]+');
           Route::get('view/{id}/{employee_id?}', 'ProfileController@viewRelationship')->name('view')
                   ->where('id', '[0-9]+')
                   ->where('employee_id', '[0-9]+');
           Route::get('edit/{id}/{employee_id?}', 'ProfileController@editRelationship')->name('edit')
                   ->where('id', '[0-9]+')
                   ->where('employee_id', '[0-9]+');
           Route::delete('delete', 'ProfileController@removeRelationship')->name('delete');
           Route::post('save/{employee_id}', 'ProfileController@saveRelationship')->name('save')->where('employee_id', '[0-9]+');
        });
        Route::group([
           'prefix' => 'education',
            'as'    => 'education.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@education')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createEducation')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{id}/{employee_id?}', 'ProfileController@editEducation')->name('edit')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deleteEducation')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@saveEducation')->name('save')->where('employee_id', '[0-9]+');
        });
        Route::group([
           'prefix' => 'cetificate',
            'as'    => 'cetificate.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@cetificate')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createCetificate')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{id}/{employee_id?}', 'ProfileController@editCetificate')->name('edit')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deleteCetificate')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@saveCetificate')->name('save')->where('employee_id', '[0-9]+');
        });
        Route::group([
           'prefix' => 'skill',
            'as'    => 'skill.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@skill')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createSkill')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{type}/{id}/{employee_id?}', 'ProfileController@editSkill')->name('edit')
                    ->where('type', '[0-9]+')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deleteSkill')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@saveSkill')->name('save')->where('employee_id', '[0-9]+');
        });

        Route::group([
           'prefix' => 'docExpire',
            'as'    => 'docexpire.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@docExpire')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createDoc')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{id}/{employee_id?}', 'ProfileController@editDoc')->name('edit')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deleteDoc')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@saveDoc')->name('save')->where('employee_id', '[0-9]+');
        });
        Route::group([
           'prefix' => 'attachment',
            'as'    => 'attachment.',
        ], function(){
            Route::get('/{employee_id?}', 'ProfileController@listAttach')->name('index')->where('employee_id', '[0-9]+');
            Route::get('create/{employee_id?}', 'ProfileController@createAttach')->name('create')->where('employee_id', '[0-9]+');
            Route::get('edit/{id}/{employee_id?}', 'ProfileController@editAttach')->name('edit')
                    ->where('id', '[0-9]+')
                    ->where('employee_id', '[0-9]+');
            Route::delete('delete', 'ProfileController@deleteAttach')->name('delete');
            Route::post('save/{employee_id?}', 'ProfileController@saveAttach')->name('save')->where('employee_id', '[0-9]+');
            Route::get('download/{filename}', 'ProfileController@downloadAttach')->name('download');
        });
        Route::group([
            'prefix' => 'japanExperience',
            'as'    => 'japanExperience.',
        ], function() {
            Route::get('/{employee_id?}', 'ProfileController@editJapanExperience')->name('index')->where('employee_id', '[0-9]+');
            Route::post('save/{emplyee_id?}', 'ProfileController@saveExperience')->name('save')->where('emplyee_id', '[0-9]+');
        });*/
    });


//CHECK POINT
    Route::group([
        'middleware' => 'logged',
        'as' => 'checkpoint.',
        'prefix' => 'team/checkpoint',
    ], function () {
        Route::get('create', 'CheckpointController@create')->name('create');
        Route::get('update/{id}', 'CheckpointController@update')->name('update');
        Route::post('save', 'CheckpointController@save')->name('save');
        Route::post('setEmp', 'CheckpointController@setEmp')->name('setEmp');
        Route::get('preview/{token}/{id}', 'CheckpointController@preview')->name('preview');
        Route::get('welcome/{token}/{id}', 'CheckpointController@welcome')->name('welcome');
        Route::get('make/{token}/{id}', 'CheckpointController@make')->name('make');
        Route::get('success', 'CheckpointController@success')->name('success');
        Route::post('save_result', 'CheckpointController@saveResult')->name('saveResult');
        Route::post('send_mail', 'CheckpointController@sendMail')->name('sendMail');
        Route::get('detail/{id}', 'CheckpointController@detail')->name('detail');
        Route::post('save_result_leader', 'CheckpointController@saveResultLeader')->name('saveResultLeader');
        Route::get('list', 'CheckpointController@grid')->name('list');
        Route::get('list-self', 'CheckpointController@listself')->name('listself');
        Route::get('made/{id}', 'CheckpointController@made')->name('made');
        Route::get('reset', 'CheckpointController@reset')->name('reset');
        Route::get('period.create', 'CheckpointController@createPeriod')->name('period.create');
        Route::post('period/save', 'CheckpointController@savePeriodCheckpoint')->name('period.save');
        Route::post('period/delete', 'CheckpointController@deletePeriod')->name('period.delete');
        Route::get('period', 'CheckpointController@listPeriod')->name('period.list');
    });

    Route::group([
        'middleware' => 'logged',
    ], function () {
        Route::get('employee/list/search/ajax/{type?}', 'MemberController@listSearchAjax')
            ->name('employee.list.search.ajax');
        Route::get('employee/list/search/external/ajax', 'MemberController@listSearchAjaxExternal')
            ->name('employee.list.search.external.ajax');
        Route::post('export_relationship', 'MemberController@exportMemberRelationship')->name('team.member.export_member.relationship');
        Route::get('team/list/search/ajax/{type?}', 'TeamController@listSearchAjax')
            ->name('team.list.search.ajax');
        Route::get('team/list/search/ajax-origin/{type?}', 'TeamController@listSearchAjaxOrigin')
            ->name('team.list.search.ajax.origin');
        //employee info
        Route::get('employee/information', 'MemberController@getEmployeeInfo')
            ->name('employee.infor');
    });
    Route::get('check-pass', 'ProfileController@checkAppPass')->name('check-app-pass');
});
