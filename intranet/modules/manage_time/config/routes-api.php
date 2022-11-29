<?php

Route::get('/employee-times/not-leave-or-onsite', 'RegisterTimeController@listRegNotLeaveOrOnsite')
        ->name('emp.register');
