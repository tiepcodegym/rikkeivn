<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::post('/lang', 'PagesController@postLang')->name('switchLang');
// Home page
    Route::get('/', 'PagesController@home')->name('home');
    Route::get('/auth/connect/{provider}', 'AuthController@login')->name('login');
    Route::get('/auth/connected/{provider}', 'AuthController@callback')->name('login.callback');
    Route::get('/logout', 'AuthController@logout')->name('logout');
    //refresh account
    Route::get('/auth/refresh', 'AuthController@refreshAccount')->name('refresh.account');

    // Change locale
    Route::get('/set-locale/{locale}', 'LocaleController@change')->name('change-locale');

    //error page
    Route::get('404', 'ErrorController@noRoute')->name('no.route');
    Route::get('errors', 'ErrorController@errors')->name('errors.system');
    Route::get('errors/general', 'ErrorController@errorGeneral')->name('errors.general');

    //grid filter action
    Route::get('/grid/filter/request', 'GridFilterController@request')->name('grid.filter.request');
    Route::get('/grid/filter/remove', 'GridFilterController@remove')->name('grid.filter.remove');
    Route::get('/grid/filter/flush', 'GridFilterController@flush')->name('grid.filter.flush');
    Route::get('/grid/filter/pager', 'GridFilterController@pager')->name('grid.filter.pager');

    //upload file ajax
    Route::post('upload/skill', 'UploadController@imageSkill')->name('upload.skill');

    //load skill data ajax
    Route::get('ajax/skills/autocomplete', 'AjaxController@skillAutocomplete')
        ->name('ajax.skills.autocomplete')
        ->middleware(['logged']);

    //manage setting
    Route::group([
        'prefix' => 'setting',
        'as' => 'setting.',
        'middleware' => ['auth']
    ], function () {
        //setting menu action
        Route::group([
            'prefix' => 'menu',
            'as' => 'menu.'
        ], function () {
            //menu item
            Route::group([
                'prefix' => 'item',
                'as' => 'item.'
            ], function () {
                Route::get('/', 'MenuItemController@index')->name('index');
                Route::get('create', 'MenuItemController@create')->name('create');
                Route::get('edit/{id}', 'MenuItemController@edit')->name('edit')->where('id', '[0-9]+');
                Route::post('save', 'MenuItemController@save')->name('save');
                Route::delete('delete', 'MenuItemController@delete')->name('delete');
            });

            //menu group
            Route::group([
                'prefix' => 'group',
                'as' => 'group.'
            ], function () {
                Route::get('/', 'MenuGroupController@index')->name('index');
                Route::get('create', 'MenuGroupController@create')->name('create');
                Route::get('edit/{id}', 'MenuGroupController@edit')->name('edit')->where('id', '[0-9]+');
                Route::post('save', 'MenuGroupController@save')->name('save');
                Route::delete('delete', 'MenuGroupController@delete')->name('delete');
            });
        });

        Route::group([
            'prefix' => 'system/data',
            'as' => 'system.data.'
        ], function () {
            Route::get('{type?}', 'SettingSystemDataController@index')->name('index');
            Route::post('/save', 'SettingSystemDataController@save')->name('save');
            Route::post('/check/connect/{api}', 'SettingSystemDataController@checkConnection')
                ->name('check.connect')->where('api', '[a-z]+');
            Route::post('/delete/email/process/queue', 'SettingSystemDataController@deleteEmailProcessQueue')
                ->name('delete.email.process.queue');
            Route::post('/delete/email/queue/data', 'SettingSystemDataController@deleteEmailQueueData')
                ->name('delete.email.queue.data');
            Route::post('/delete/timekeeping/process', 'SettingSystemDataController@deleteTimekeepingProcess')
                ->name('delete.timekeeping.process');
            Route::post('delete/process/queue/{type}', 'SettingSystemDataController@deleteProcessQueue')
                ->name('delete.process.queue');
            Route::post('delete/acl/draft', 'SettingSystemDataController@deleteAclDraft')
                ->name('delete.acl.draft');
            Route::post('clear/cache/', 'SettingSystemDataController@clearCache')
                ->name('clear.cache');
            Route::post('refresh/version/seeder', 'SettingSystemDataController@refreshVersionSeeder')
                ->name('refresh.version.seeder');
        });
        Route::get('/system/db-logs', 'DBLogController@index')->name('system.db_logs');
    });

    //Log
    Route::group([
        'prefix' => 'log',
        'as' => 'log.',
        'middleware' => ['logged'],
    ], function () {
        Route::get('/', 'LogController@index')->name('index');
        Route::get('/download/{filename}', 'LogController@download')->name('download');
    });

    Route::group([
        'prefix' => 'setting',
        'as' => '',
        'middleware' => ['auth']
        ], function() { 
            Route::get('/email-queues', 'EmailQueuesController@index')->name('email-queues');
    });
});
