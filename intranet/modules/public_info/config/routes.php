<?php

Route::get('dang-ky-khoa-hoc-brse', 'IndexController@registerCourseBrse')
    ->name('register.course.brse');

Route::get('/api/test/results-ricode', ['as' => 'result-ricode', 'uses' => 'IndexController@resultRicodeTest']);