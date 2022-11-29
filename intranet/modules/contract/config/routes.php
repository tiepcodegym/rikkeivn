<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'prefix' => 'manage',
        'as' => 'manage.'
    ], function () {
        Route::group([
            'prefix' => 'contract',
            'middleware' => 'auth',
            'as' => 'contract.'
        ], function () {
            Route::get('/list/{tab}/', 'ContractController@index')->name('index')->where('tab', '[a-z-]+');
            Route::get('/show/{id}/', 'ContractController@show')->name('show')->where('id', '[0-9]+');
            Route::get('/create', 'ContractController@create')->name('create');
            Route::get('/edit/{id}', 'ContractController@edit')->name('edit')->where('id', '[0-9]+');

            Route::post('/export/{tab}', 'ContractController@export')->name('export');

            Route::post('/save', 'ContractController@insert')->name('save');
            Route::post('/import-excel', 'ContractController@importExcel')->name('import-excel');
            Route::post('/update/{id}', 'ContractController@update')->name('update')->where('id', '[0-9]+');
            Route::delete('/delete/{id}', 'ContractController@delete')->name('delete')->where('id', '[0-9]+');
            Route::get('/employee/search/ajax/{type?}', 'ContractController@listSearchAjax')
                ->name('employee.search.ajax');

            Route::get('/import-excel/histories', 'ContractController@histories')
                ->name('histories');
            Route::get('/import-excel/download/{fileName}', 'ContractController@download')
                ->name('download');
            Route::post('/synchronize', 'ContractController@pushContractToEmp')
                ->name('synchronize');
        });

        Route::get('/format-excel-file', 'ContractController@downloadFormatFile')->name('downloadFormatFile');
    });

    // profile employee
    Route::group([
        'middleware' => 'logged',
        'as' => 'contract.',
        'prefix' => 'profile/contract',
    ], function () {
        Route::get('list', 'ContractController@contract')->name('list');
        Route::get('list/{id}', 'ContractController@contractByEmpId')->name('employee-list');
        Route::post('update-confirm', 'ContractController@updateConfirm')->name('update-confirm');
    });
});