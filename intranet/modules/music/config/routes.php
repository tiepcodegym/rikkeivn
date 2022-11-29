<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'middleware' => 'auth',
    ], function () {
        Route::get('/manage/order', 'ManageMusicController@order')
            ->name('manage.order');
        Route::post('/manage/order/delete/{id}', 'ManageMusicController@deleteOrder')
            ->name('manage.order.del');
        Route::post('manage/offices/delManyOrder', 'ManageMusicController@delManyOrder')
            ->name('manage.order.delMany');

        Route::get('/manage/offices', 'ManageMusicController@offices')
            ->name('manage.offices');
        Route::post('/manage/offices/delete/{id}', 'ManageMusicController@deleteOffice')
            ->name('manage.offices.del');
        Route::get('/manage/offices/create', 'ManageMusicController@createOffice')
            ->name('manage.offices.create');
        Route::get('/manage/offices/edit/{id}', 'ManageMusicController@editOffice')
            ->name('manage.offices.edit');
        Route::post('/manage/offices/save', 'ManageMusicController@saveOffice')
            ->name('manage.offices.save');
        Route::get('manage/offices/checkName', 'ManageMusicController@checkName')
            ->name('manage.offices.checkName');
    });

    Route::group([
        'middleware' => 'logged',
    ], function () {
        Route::get('order', 'OrderController@index')
            ->name('order');
        Route::get('order/office/{id}', 'OrderController@office')
            ->name('order.office');
        Route::post('/order/save', 'OrderController@save')
            ->name('order.save');
        Route::post('/order/vote', 'OrderController@vote')
            ->name('order.vote');
        Route::post('/order/play', 'OrderController@play')
            ->name('order.play');
    });
});

