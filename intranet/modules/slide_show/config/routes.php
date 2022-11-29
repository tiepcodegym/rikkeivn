<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::get('slide-show/', 'SlideShowController@index')->name('slide-show');
    Route::get('slide', 'SlideShowController@index')->name('slide-show-index');
    Route::get('/slide-ajax', 'SlideShowController@loadSlideAjax')->name('slide-ajax');
    Route::post('check-password-slider', 'SlideShowController@checkPasswordSlider')->name('check-password-slider');
    Route::post('get-file-for-slide', 'SlideShowController@getFileForSlide')->name('get-file-for-slide');
    Route::group(['prefix' => 'slide-show', 'middleware' => 'logged'], function () {
        Route::get('list-slider', 'SlideShowController@listSlideShow')->name('list-slider');
        Route::post('list', 'SlideShowController@getSliderByDate')->name('slide-list');
        Route::post('detail', 'SlideShowController@detailSlide')->name('slide-detail');
        Route::post('create', 'SlideShowController@createSlide')->name('slide-create');
        Route::post('delete', 'SlideShowController@deleteSlide')->name('delete-slide');
        Route::get('preview/{id}', 'SlideShowController@previewSlide')->name('preview');
        Route::post('change-paswword', 'SlideShowController@changePassword')->name('change-paswword');
        Route::post('change-birthday-company', 'SlideShowController@changeBirthday')->name('change-birthday-company');
        Route::post('get-template-interval', 'SlideShowController@getTemplateInterval')->name('get-template-interval');
        Route::get('get-template-image', 'SlideShowController@getTemplateImage')->name('get-template-image');
        Route::get('setting', 'SlideShowController@setting')->name('setting');
        Route::get('create-video-default', 'SlideShowController@createVidelDefault')->name('create-video-default');
        Route::get('video-edit/{id}', 'SlideShowController@detailVideo')->name('video-edit');
        Route::post('post-video-default', 'SlideShowController@postVideoDefault')->name('post-video-default');
        Route::delete('delete-video-default', 'SlideShowController@deleteVideoDefault')->name('delete-video-default');
        Route::post('process/check', 'SlideShowController@processCheck')->name('process.check');
        Route::get('get-template-logo', 'SlideShowController@urlGetTemplateLogo')->name('get-template-logo');
    });
// mail birthday slide
    Route::group([
        'middleware' => 'auth',
    ], function () {
        // mail manage membership employee event::mail.membership.employee.*
        Route::group([
            'as' => 'admin.slide.birthday.',
            'prefix' => 'slide_show/birthday',
        ], function () {
            Route::get('/', 'SlideShowController@showBirthdayPattern')
                ->name('show');
            Route::get('preview/birthday', 'SlideShowController@previewBirthdayPattern')
                ->name('preview');
            Route::post('save', 'SlideShowController@saveBirthdayPattern')
                ->name('save');
        });

    });
});