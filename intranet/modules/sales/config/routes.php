<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'middleware' => 'auth',
        'as' => 'css.'
    ], function () {
        Route::get('css/create', 'CssController@create')->name('create');
        Route::get('css/update/{id}', 'CssController@update')->name('update');
        Route::post('css/get_rikker', 'CssController@getRikkerInfo')->name('getRikkerInfo');
        Route::post('css/save', 'CssController@save')->name('save');
        Route::get('sales/css/list', 'CssController@grid')->name('list');
        Route::get('sales/css/export/{year?}', 'CssController@export')->name('css-export');
        Route::get('css/view/{id}', 'CssController@view')->name('view');
        Route::get('css/list/make/{id}', 'CssController@listMake')->name('listMake');
        Route::get('css/cancel', 'CssController@cancelMake')->name('cancel');
        Route::get('css/analyze', 'CssController@analyze')->name('analyze');
        Route::post('css/filter_analyze', 'CssController@filterAnalyze')->name('filterAnalyze');
        Route::post('css/apply_analyze', 'CssController@applyAnalyze')->name('applyAnalyze');
        Route::post('css/show_analyze_list_project/{criteriaIds}/{teamIds}/{projectTypeIds}/{startDate}/{endDate}/{criteriaType}/{curpage}/{orderBy}/{ariaType}', 'CssController@showAnalyzeListProject')->name('showAnalyzeListProject');
        Route::post('css/get_list_less_three_star/{cssresultids}/{curpage}/{orderby}/{ariatype}', 'CssController@getListLessThreeStar')->name('getListLessThreeStar');
        Route::post('css/get_proposes/{cssresultids}/{curpage}/{orderby}/{ariatype}', 'CssController@getProposes')->name('getProposes');
        Route::post('css/get_list_less_three_star_question/{questionid}/{cssresultids}/{curpage}/{orderby}/{ariatype}', 'CssController@getListLessThreeStarByQuestion')->name('getListLessThreeStarByQuestion');
        Route::post('css/get_proposes_question/{questionid}/{cssresultids}/{curpage}/{orderby}/{ariatype}', 'CssController@getProposesByQuestion')->name('getProposesByQuestion');
        Route::get('css/export_excel/{cssresultid}', 'CssController@exportExcel')->name('exportExcel');
        //Route::get('css/reset', 'CssController@reset')->name('reset');
        Route::post('css/sendMailCustomer', 'CssController@sendMailCustomer')->name('sendMailCustomer');
        Route::post('css/saveCssMail', 'CssController@saveCssMail')->name('saveCssMail');
        Route::post('css/deleteItem', 'CssController@deleteItem')->name('deleteItem');
        Route::post('css/show_all_make', 'CssController@showAllMake')->name('showAllMake');
    });
    Route::post('css/ajax/get-pm-and-sales', 'CssController@getPmAndSales')->name('ajax_get.pm_and_sales');
    Route::get('css/success/', 'CssController@success')->name('success');
    Route::get('css/welcome/{token}/{id}', 'CssController@welcome')->name('welcome');
    Route::post('css/welcome/{token}/{id}', 'CssController@welcome')->name('welcome');
    Route::get('css/make/{token}/{id}', 'CssController@make')->name('make');
    Route::post('css/saveResult', 'CssController@saveResult')->name('saveResult');
    Route::post('css/insertAnalysisResult', 'CssController@insertAnalysisCss')->name('insertAnalysisResult');
    Route::post('css/approveStatusCss', 'CssController@approveStatusCss')->name('approveStatusCss');
    Route::post('css/reviewStatusCss', 'CssController@reviewStatusCss')->name('reviewStatusCss');
    Route::post('css/sendmail', 'CssController@sendMail')->name('sendMail');
    Route::post('css/deleteMail', 'CssController@deleteMail')->name('deleteMail');
    Route::post('css-result/cancel', 'CssController@cancelCssResult')->name('cancelCssResult');

    Route::group([
        'middleware' => 'logged',
        'as' => 'css.'
    ], function () {
        Route::get('css/detail/{id}', 'CssController@detail')->name('detail');
        Route::post('css/setTeam', 'CssController@setTeam')->name('setTeam');
        Route::get('css/preview/{token}/{id}', 'CssController@preview')->name('preview');
        Route::post('css/import-template/{token}/{id}', 'CssController@importTemplate')->name('importTemplate');
        Route::get('css/download-template/{type}', 'CssController@downloadTemplate')->name('downloadTemplate');
    });

    Route::post('css/cancel', 'CssController@cancelCss')->name('cancel.css');

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'customer',
        'as' => 'customer.'
    ], function () {
        // Route::get('create', 'CustomerController@create')->name('create');
        // Route::post('create', 'CustomerController@store')->name('postCreate');
        Route::get('list', 'CustomerController@lists')->name('list');
        Route::get('projects-list/{type}/{id}', 'CustomerController@getProjectsList')->name('getProjectsList');
        Route::get('edit/{id}', 'CustomerController@edit')->name('edit');
        Route::post('delete', 'CustomerController@delete')->name('delete');
        Route::post('checkExistsCustomer', 'CustomerController@checkExists')->name('checkExistsCustomer');
        Route::post('merge', 'CustomerController@merge')->name('merge');
        Route::post('import-excel', 'CustomerController@importExcel')->name('import-excel');
        Route::get('/format-excel-file', 'CustomerController@downloadFormatFile')->name('downloadFormatFile');
    });  

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'company',
        'as' => 'company.'
    ], function () {
        Route::get('create', 'CompanyController@create')->name('create');
        Route::post('create', 'CompanyController@store')->name('postCreate');
        Route::get('list', 'CompanyController@lists')->name('list');
        Route::get('edit/{id}', 'CompanyController@edit')->name('edit');
        Route::post('delete', 'CompanyController@delete')->name('delete');
        Route::post('merge', 'CompanyController@merge')->name('merge');
        Route::post('checkExits', 'CompanyController@checkExits')->name('checkExits');
    });

Route::group([
    'middleware' => 'logged',
    'prefix' => 'search/ajax',
    'as' => 'search.ajax.'
], function(){
    Route::get('customer', 'CustomerController@searchAjax')->name('customer');
    Route::get('searchCusByCompany', 'CustomerController@searchCustomerAjax')->name('searchCustomerAjax');
    Route::get('company/{id?}', 'CompanyController@searchAjax')->name('company');
});

    Route::post('css/wellcom/historyAjax', 'CssController@historyAjax')->name('historyAjax');
    Route::get('css/detail/history/{id}', 'CssController@detailCssCus')->name('detailCustomer');

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'sales/tracking/',
    ], function () {
        Route::get('', 'TrackingController@index')->name('tracking');
        Route::get('mytasks', 'TrackingController@myTasks')->name('tracking.myTasks');
        Route::post('mytasks', 'TrackingController@saveTasks')->name('tracking.saveTasks');
        Route::get('feedbacks', 'TrackingController@customerFeedback')->name('tracking.feedbacks');
        Route::get('risks', 'TrackingController@risks')->name('tracking.risks');
        Route::post('risks', 'TrackingController@saveRisk')->name('tracking.save.risks');
    });

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'sales/request-opportunity',
    ], function () {
        Route::group([
            'as' => 'req.list.oppor.'
        ], function () {
            Route::get('/', 'RequestOpporController@index')->name('index');
        });

        Route::group([
            'as' => 'req.oppor.'
        ], function () {
            Route::get('/edit/{id?}', 'RequestOpporController@edit')
                ->where('id', '[0-9]+')
                ->name('edit');
            Route::post('/save', 'RequestOpporController@save')
                ->name('save');
            Route::delete('/delete/{id}', 'RequestOpporController@delete')
                ->where('id', '[0-9]+')
                ->name('delete');
            Route::post('/check-exists', 'RequestOpporController@checkExists')
                ->name('check_exists');
            Route::post('/export', 'RequestOpporController@export')
                ->name('export');
        });

        Route::group([
            'prefix' => 'css',
        ], function () {
            Route::post('/export-css/{id?}', 'CssController@exportCss')->name('css.export.css');
        });

        Route::group([
            'as' => 'req.apply.oppor.'
        ], function () {
            Route::group([
                'prefix' => 'api'
            ], function () {
                Route::get('/{id}', 'RequestOpporController@getOppor')
                    ->name('getOppor');
                Route::get('/list-cv-notes/{id}', 'RequestOpporController@getListCvNotes')
                    ->where('id', '[0-9]+')
                    ->name('cv_member.list');
                Route::post('/save-cv-member', 'RequestOpporController@saveCvMember')
                    ->name('cv_member.save');
                Route::delete('/delete-cv-member/{id}', 'RequestOpporController@deleteCvMember')
                    ->where('id', '[0-9]+')
                    ->name('cv_member.delete');
            });
        });
    });

    Route::group([
        'middleware' => 'logged',
        'prefix' => 'sales/request-opportunity',
        'as' => 'req.apply.oppor.'
    ], function () {
        Route::get('/view/{id?}', 'RequestOpporController@view')
            ->where('id', '[0-9]+')
            ->name('view');
    });
});