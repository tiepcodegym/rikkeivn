<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'prefix' => 'welfare',
        'as' => 'welfare.',
        'middleware' => 'logged',
    ], function () {
        Route::get('/', 'EventController@index')
            ->name('event.index');
        Route::get('edit/{id}', 'EventController@edit')
            ->name('event.edit');
        Route::get('create', 'EventController@create')
            ->name('event.create');
        Route::get('detail', 'EventController@detail')
            ->name('event.detailpost');
        Route::post('save', 'EventController@save')
            ->name('event.save');
        Route::delete('delete', 'EventController@delete')
            ->name('event.delete');
        Route::get('restore/{id?}', 'EventController@restore')
            ->name('event.restore');
        Route::post('sendMailNotiEvent/{id}', 'EventController@sendMailNotify')
            ->name('event.sendMailNotify');
        Route::group([
            'prefix' => 'partner',
            'as' => 'partner.',
        ], function () {
            Route::get('/', 'PartnersController@index')
                ->name('index');
            Route::get('create', 'PartnersController@create')
                ->name('create');
            Route::post('add', 'PartnersController@add')
                ->name('add');
            Route::get('edit/{id?}', 'PartnersController@edit')
                ->name('edit');
            Route::post('delete', 'PartnersController@delete')
                ->name('delete');
            Route::get('list', 'PartnersController@getList')
                ->name('list');
            Route::group([
                'prefix' => 'group',
                'as' => 'group.',
            ], function () {
                Route::post('add', 'PartnerGroupController@add')
                    ->name('add');
                Route::post('edit', 'PartnerGroupController@edit')
                    ->name('edit');
                Route::post('delete', 'PartnerGroupController@delete')
                    ->name('delete');
                Route::get('list', 'PartnerGroupController@getList')
                    ->name('list');
            });
        });

        Route::group([
            'prefix' => 'organizer',
            'as' => 'organizer.',
        ], function () {
            Route::get('/', 'OrganizerController@index')
                ->name('index');
            Route::get('add', 'OrganizerController@create')
                ->name('create');
        });
        Route::group([
            'prefix' => 'group',
            'as' => 'group.',
        ], function () {
            Route::get('/', 'GroupController@index')->name('index');
            Route::get('create', 'GroupController@create')
                ->name('create');
            Route::get('delete/{id?}', 'GroupController@delete')
                ->name('delete');
            Route::post('save', 'GroupController@save')
                ->name('save');
            Route::post('saveAjax', 'GroupController@saveAjax')
                ->name('saveAjax');
            Route::get('list', 'GroupController@getList')
                ->name('list');
        });
        Route::group([
            'prefix' => 'purpose',
            'as' => 'purpose.',
        ], function () {
            Route::get('/', 'PurposeController@index')->name('index');
            Route::get('create', 'PurposeController@create')
                ->name('create');
            Route::post('save', 'PurposeController@save')
                ->name('save');
            Route::post('saveAjax', 'PurposeController@saveAjax')
                ->name('saveAjax');
            Route::get('delete/{id?}', 'PurposeController@delete')
                ->name('delete');
            Route::get('List', 'PurposeController@getList')
                ->name('list');
        });
        Route::group([
            'prefix' => 'formImplements',
            'as' => 'formImplements.',
        ], function () {
            Route::get('/', 'FormImplementsController@index')->name('index');
            Route::get('create', 'FormImplementsController@create')
                ->name('create');
            Route::post('save', 'FormImplementsController@save')
                ->name('save');
            Route::post('saveAjax', 'FormImplementsController@saveAjax')
                ->name('saveAjax');
            Route::get('delete/{id?}', 'FormImplementsController@delete')
                ->name('delete');
            Route::get('list', 'FormImplementsController@getList')
                ->name('list');
        });
        Route::group([
            'prefix' => 'participant',
            'as' => 'participant.',
        ], function () {
            Route::get('/', 'ParticipantController@index')
                ->name('index');
        });
        Route::get('file', function () {
            return view('welfare::event.include.file');
        });
        Route::post('add-file', 'EventController@uploadFile')
            ->name('add.file');
        Route::post('delete-file', 'EventController@deleteFile')
            ->name('file.delete');
        Route::get('datatables/{id?}', 'WelEmployeeController@getBasicData')
            ->name('datatables.data');
        Route::get('saveAjax/{id?}', 'WelEmployeeController@saveAjax')
            ->name('datatables.save');
        Route::get('RelativeAttach/{id?}', 'WelRelativeAttachController@getBasicData')
            ->name('RelativeAttach.data');
        Route::get('RelativesaveAjax/{id?}', 'WelRelativeAttachController@saveAjax')
            ->name('RelativeAttach.save');
        Route::get('WelFreMore/{id?}', 'WelFeeMoreController@getBasicData')
            ->name('WelFreMore.data');
        Route::get('WelFreMoreSave/{id?}', 'WelFeeMoreController@saveAjax')
            ->name('WelFreMore.save');
        Route::get('WelFreMoreDelete/{id?}', 'WelFeeMoreController@delete')
            ->name('WelFreMore.delete');
        Route::get('employee/{id?}', 'WelEmployeeController@searchAjax')
            ->name('employee.search.ajax');
        Route::group([
            'prefix' => 'relation',
            'as' => 'relation.',
            'middleware' => 'auth',
        ], function () {
            Route::get('list', 'RelationNameController@index')
                ->name('list');
            Route::get('create', 'RelationNameController@create')
                ->name('create');
            Route::get('edit/{id}', 'RelationNameController@edit')
                ->name('edit');
            Route::post('save', 'RelationNameController@save')
                ->name('save');
            Route::delete('delete', 'RelationNameController@delete')
                ->name('delete');
            Route::get('check-name/{id?}', 'RelationNameController@checkName')
                ->name('check.name');
        });
        Route::group([
            'prefix' => 'relative/attach',
            'as' => 'relative.attach.',
        ], function () {
            Route::get('edit', 'WelRelativeAttachController@edit')
                ->name('edit');
            Route::post('add', 'WelRelativeAttachController@save')
                ->name('add');
            Route::post('delete', 'WelRelativeAttachController@delete')
                ->name('delete');
        });
        Route::get('confirm/{id}', 'WelEmployeeController@viewConfirm')
            ->name('confirm.welfare');
        Route::get('confirm/edit/{id}', 'WelEmployeeController@editConfirm')
            ->name('confirm.edit.welfare');
        Route::get('confirm/preview/{id}', 'WelEmployeeController@previewConfirm')
            ->name('confirm.welfare.preview');
        Route::post('confirm', 'WelEmployeeController@confirm')
            ->name('post.confirm.welfare');
        Route::post('send-mail', 'EventController@sendMail')
            ->name('send.mail');
        Route::post('preview-mail', 'EventController@previewMail')
            ->name('preview.mail');

        Route::get('getEmployeeParticipants/{id?}/{event?}', 'ParticipantController@getEmployeeParticipants')
            ->name('edit.getEmployee');
        Route::post('saveEmployeeParticipants', 'ParticipantController@saveEmployeeParticipants')
            ->name('save.getEmployee');
        Route::get('deleteEmployeeParticipants/{event?}/{id?}', 'WelEmployeeController@deleteEmployeeParticipants')
            ->name('delete.getEmployee');
        Route::post('reviewEmployeeAttach', 'WelRelativeAttachController@reviewEmployeeAttach')
            ->name('review.EmployeeAttach');
        Route::post('editAttachEmployee', 'WelRelativeAttachController@editAttachEmployee')
            ->name('edit.AttachEmployee');
        Route::post('saveEditAttachEmployee', 'WelRelativeAttachController@saveAttachEmployee')
            ->name('save.Edit.AttachEmployee');
        Route::post('getTeamEmployee', 'ParticipantController@getTeamEmployee')
            ->name('get.Team.Employee');
        Route::post('registerOnline', 'EventController@registerOnline')
            ->name('register.online');
        Route::post('showEmployeeCost', 'WelEmployeeController@showEmployeeCost')
            ->name('show.employee.cost');
        Route::post('saveEmployeeCost', 'WelEmployeeController@saveEmployeeCost')
            ->name('save.employee.cost');
        Route::get('dataTableEmployee', 'OrganizerController@showDataEmployee')
            ->name('show.data.Employee');
        Route::post('saveDataOrganizer', 'OrganizerController@saveData')
            ->name('save.data.organizer');
        Route::post('saveAllEmployeeJoin', 'WelEmployeeController@saveAllEmployeeJoin')
            ->name('save.all.employee.join');
        Route::post('checkFavorable', 'WelRelativeAttachController@checkFavorable')
            ->name('check.favorable');

        Route::group([
            'prefix' => 'export',
            'as' => 'export.',
        ], function () {
            Route::get('employee/{id?}', 'ExportController@exportEmpoyees')
                ->name('employee')->where('id', '[0-9]+');
            Route::get('employee/participate/{id?}', 'ExportController@exportEmployeesParticipate')
                ->name('employee.participate')->where('id', '[0-9]+');
            Route::get('employee/joined/{id?}', 'ExportController@exportEmployeesHaveJoined')
                ->name('employee.joined')->where('id', '[0-9]+');
            Route::get('employee/attached/{id?}', 'ExportController@exportAttached')
                ->name('employee.attached')->where('id', '[0-9]+');
            Route::get('fee/expected/{id?}/{filter}', 'ExportController@exportFee')
                ->name('fee')->where('id', '[0-9]+');
        });

        Route::group([
            'prefix' => 'attach/session',
            'as' => 'attach.session.',
        ], function () {
            Route::post('add', 'WelRelativeAttachController@addSessionAttached')
                ->name('add');
            Route::get('edit/{key?}', 'WelRelativeAttachController@editSessionAttached')
                ->name('edit')->where('key', '[0-9]+');
            Route::post('delete', 'WelRelativeAttachController@deleteSessionAttached')
                ->name('delete')->where('key', '[0-9]+');
        });
        Route::get('select-ajax/{welId?}/{favorable?}', 'WelRelativeAttachController@selectAjax')
            ->name('relation.select.ajax');

    });
    Route::group([
        'prefix' => 'team',
        'as' => 'team.',
        'middleware' => 'logged',
    ], function () {
        Route::get('team/member/{id?}', 'WelEmployeeController@getMemberByTeam')
            ->name('member.index')->where('id', '[0-9]+');
    });
});

