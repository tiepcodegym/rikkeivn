<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'prefix' => 'admin/manage/asset',
        'as' => 'asset.',
        'middleware' => 'auth',
    ], function () {
        Route::get('/', 'AssetController@index')->name('index');
        Route::get('add', 'AssetController@add')->name('add');
        Route::post('save', 'AssetController@save')->name('save');
        Route::delete('delete', 'AssetController@delete')->name('delete');
        Route::get('edit/{id}', 'AssetController@edit')->name('edit')->where('id', '[0-9]+');
        Route::get('view/{id}', 'AssetController@view')->name('view')->where('id', '[0-9]+');
        Route::post('asset-allocation', 'AssetController@assetAllocation')->name('asset-allocation');
        Route::post('asset-retrieval', 'AssetController@assetRetrieval')->name('asset-retrieval');
        Route::post('approve', 'AssetController@approve')->name('approve');
        Route::post('confirm-repaired-maintained', 'AssetController@confirmRepairedAndMaintained')->name('confirm-repaired-maintained');
        Route::post('asset-lost-notification', 'AssetController@assetLostNotification')->name('asset-lost-notification');
        Route::post('asset-broken-notification', 'AssetController@assetBrokenNotification')->name('asset-broken-notification');
        Route::post('asset-suggest-liquidate', 'AssetController@assetSuggestLiquidate')->name('asset-suggest-liquidate');
        Route::post('asset-suggest-repair-maintenance', 'AssetController@assetSuggestRepairMaintenance')->name('asset-suggest-repair-maintenance');
        Route::get('ajax-get-asset-information', 'AssetController@ajaxGetAssetInformation')->name('ajax-get-asset-information');
        Route::get('ajax-get-attribute-and-code', 'AssetController@ajaxGetAssetAttributesAndCode')->name('ajax-get-attribute-and-code');
        Route::get('ajax-get-asset-to-approve', 'AssetController@ajaxGetAssetToApprove')->name('ajax-get-asset-to-approve');
        Route::get('ajax-get-employee-to-report', 'AssetController@ajaxGetEmployeesToReport')->name('ajax-get-employee-to-report');
        Route::get('ajax-get-asset-to-report', 'AssetController@ajaxGetAssetsToReport')->name('ajax-get-asset-to-report');
        Route::get('ajax-get-modal-report', 'AssetController@ajaxGetModalReport')->name('ajax-get-modal-report');
        Route::post('view-report', 'AssetController@viewReport')->name('view-report');
        Route::post('importFile', 'AssetController@importFile')->name('importFile');
        Route::get('get-asset', 'AssetController@getAsset')->name('getAsset');
        Route::get('asset-it-profile', 'AssetController@assetITProfile')->name('assetITProfile');
        Route::post('it-confirm', 'AssetController@itConfirm')->name('itConfirm');
        Route::post('export', 'AssetController@exportAsset')->name('export_asset');
        Route::post('asset-return', 'AssetController@assetReturn')->name('asset-return');
        Route::get('request-asset-to-warehouse', 'AssetController@assetToWarehouse')->name('asset_to_warehouse');

        Route::group([
            'prefix' => 'group',
            'as' => 'group.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'AssetGroupController@index')->name('index');
            Route::post('save', 'AssetGroupController@save')->name('save');
            Route::delete('delete', 'AssetGroupController@delete')->name('delete');
            Route::get('check-exist-group-name', 'AssetGroupController@checkExistAssetGroupName')->name('check-exist-group-name');
            Route::post('importFile', 'AssetGroupController@importFile')->name('importFile');
        });

        Route::group([
            'prefix' => 'warehouse',
            'as' => 'warehouse.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'WarehouseController@index')->name('index');
            Route::post('save', 'WarehouseController@save')->name('save');
            Route::delete('delete', 'WarehouseController@delete')->name('delete');
            Route::get('check-exist', 'WarehouseController@checkExist')->name('check-exist');
        });

        Route::group([
            'prefix' => 'category',
            'as' => 'category.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'AssetCategoryController@index')->name('index');
            Route::post('save', 'AssetCategoryController@save')->name('save');
            Route::delete('delete', 'AssetCategoryController@delete')->name('delete');
            Route::get('checkExist', 'AssetCategoryController@checkExist')->name('checkExist');
            Route::post('importFile', 'AssetCategoryController@importFile')->name('importFile');
        });

        Route::group([
            'prefix' => 'origin',
            'as' => 'origin.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'AssetOriginController@index')->name('index');
            Route::post('save', 'AssetOriginController@save')->name('save');
            Route::delete('delete', 'AssetOriginController@delete')->name('delete');
            Route::get('check-exist-origin-name', 'AssetOriginController@checkExistAssetOriginName')->name('check-exist-origin-name');
        });

        Route::group([
            'prefix' => 'supplier',
            'as' => 'supplier.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'AssetSupplierController@index')->name('index');
            Route::post('save', 'AssetSupplierController@save')->name('save');
            Route::delete('delete', 'AssetSupplierController@delete')->name('delete');
            Route::post('importFile', 'AssetSupplierController@importFile')->name('importFile');
            Route::get('checkExist', 'AssetSupplierController@checkExist')->name('checkExist');
        });

        Route::group([
            'prefix' => 'attribute',
            'as' => 'attribute.',
            'middleware' => 'auth',
        ], function () {
            Route::get('/', 'AssetAttributeController@index')->name('index');
            Route::post('save', 'AssetAttributeController@save')->name('save');
            Route::delete('delete', 'AssetAttributeController@delete')->name('delete');
            Route::get('check-exist-attribute-name', 'AssetAttributeController@checkExistAssetAttributeName')->name('check-exist-attribute-name');
        });
    });

    Route::group([
        'prefix' => 'admin/asset/manage/inventory',
        'as' => 'inventory.',
        'middleware' => 'auth'
    ], function () {
        Route::get('/', 'InventoryController@index')->name('index');
        Route::get('/edit/{id?}', 'InventoryController@edit')
            ->where('id', '[0-9]+')
            ->name('edit');
        Route::post('/save', 'InventoryController@save')->name('save');
        Route::delete('/delete/{id}', 'InventoryController@delete')
            ->where('id', '[0-9]+')
            ->name('delete');
        Route::get('/detail-item/{id}', 'InventoryController@detail')
            ->where('id', '[0-9]+')
            ->name('item_detail');
        Route::delete('/delete-item/{id}', 'InventoryController@deleteItem')
            ->where('id', '[0-9]+')
            ->name('item_delete');
        Route::post('/export-detail', 'InventoryController@export')->name('export');
        Route::post('/mail-alert/{id}', 'InventoryController@mailAlert')->name('alert');
        Route::get('asset-ajax', 'InventoryController@getPersonalAssetAjax')->name('personal_asset_ajax');
    });

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        'middleware' => 'logged',
    ], function () {
        Route::get('asset', 'AssetController@viewPersonalAsset')->name('view-personal-asset');
        Route::post('confirm-allocation', 'AssetController@confirmAllocation')->name('confirm-allocation');
        Route::post('confirm-asset-inventory', 'AssetController@confirmAssetInventory')->name('confirm_inventory');
        Route::post('confirm-handover', 'AssetController@confirmHandover')->name('confirm-handover');
        Route::get('asset-ajax', 'AssetController@getPersonalAssetAjax')->name('personal_asset_ajax');
        Route::get('request-asset', 'AssetController@myRequests')->name('my_request_asset');
        Route::get('/request-asset/view/{id}', 'AssetController@viewRequest')->name('view')->where('id', '[0-9]+');
    });

    Route::group([
        'prefix' => 'admin/resource',
        'as' => 'resource.',
    ], function () {
        Route::group([
            'prefix' => 'request-asset',
            'as' => 'request.',
            'middleware' => 'auth',
        ], function () {
            Route::post('review', 'RequestAssetController@reviewRequest')->name('review');
            Route::post('approve', 'RequestAssetController@approveRequest')->name('approve');
        });
        Route::group([
            'prefix' => 'request-asset',
            'as' => 'request.',
            'middleware' => 'logged',
        ], function () {
            Route::get('/', 'RequestAssetController@index')->name('index');
            Route::get('view/{id}', 'RequestAssetController@viewRequest')->name('view')->where('id', '[0-9]+');
            Route::get('view-it-warehouse/{id}', 'RequestAssetController@viewRequestItWarehouse')->name('view_it_warehouse');
            Route::get('edit/{id?}', 'RequestAssetController@editRequest')->name('edit')->where('id', '[0-9]+');
            Route::post('save', 'RequestAssetController@saveRequest')->name('save');
            Route::delete('delete', 'RequestAssetController@delete')->name('delete');
            Route::get('ajax-search-employee', 'RequestAssetController@searchEmployeeAjax')->name('ajax-search-employee');
            Route::get('ajax-search-employee-review', 'RequestAssetController@searchEmployeeReviewAjax')->name('ajax-search-employee-review');
            Route::get('ajax-get-request-asset-to-allocation', 'RequestAssetController@ajaxGetRequestAssetToAllocation')->name('ajax-get-request-asset-to-allocation');
            Route::post('update/{id}/quantity', 'RequestAssetController@updateQuantity')
                ->where('id', '[0-9]+')
                ->name('update_qty');
            Route::post('update/{id}/category_id', 'RequestAssetController@updateCategoryId')
                ->where('id', '[0-9]+')
                ->name('update_cate_id');
            Route::get('ajax-search-leader-review', 'RequestAssetController@searchLeaderReviewAjax')->name('ajax-search-leader-review');
            Route::post('delete-request', 'RequestAssetController@deleteRequest')->name('delete-request');
    });
});

    Route::group([
        'prefix' => 'admin/manage/asset',
        'as' => 'asset.',
        'middleware' => 'logged',
    ], function () {
        Route::post('asset-request-to-wh', 'AssetController@assetRequestToWh')->name('asset-request-to-wh');
        Route::get('get-asset-profile', 'AssetController@getAssetProfile')->name('getAssetProfile');
        Route::get('search-asset-ajax', 'AssetController@searchAjax')->name('search.ajax');
        Route::get('search-asset-ajax-by-empId', 'AssetController@searchAjaxByEmpId')->name('search.ajax_by_emp_id');
        Route::get('search-asset-ajax-by-warehouse', 'AssetController@searchAjaxByWarehouse')->name('search.ajax_by_warehouse');
        Route::post('save-note', 'AssetController@saveNote')->name('saveNoteOfEmp');
        Route::post('importFileConfigure', 'AssetController@importFileConfigure')->name('importFileConfigure');
        Route::post('import-serial-number', 'AssetController@importSerialNumber')->name('import-serial-number');
        Route::get('ajax/show-asset-to-warehouse', 'AssetController@showAssetToWarehouse')->name('show_asset_to_warehouse');
        Route::post('ajax/save-asset-to-warehouse', 'AssetController@saveAssetToWarehouse')->name('save_asset_to_warehouse');
    });

    Route::group([
        'prefix' => 'admin/asset/manage/report',
        'as' => 'report.',
        'middleware' => 'auth'
    ], function () {
        Route::get('/', 'ReportAssetController@index')->name('index');
        Route::get('/detail/{id}', 'ReportAssetController@detail')->where('id', '[0-9]+')->name('detail');
        Route::post('/confirm/{id}', 'ReportAssetController@confirm')->where('id', '[0-9]+')->name('confirm');
        Route::delete('/delete/{id}', 'ReportAssetController@delete')->where('id', '[0-9]+')->name('delete');
    });

    Route::group([
        'prefix' => 'admin/setting-asset',
        'as' => 'setting.',
        'middleware' => 'auth',
    ], function () {
        Route::get('/', 'AssetSettingController@index')->name('index');
    });
});