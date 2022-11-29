<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function() {
    Route::group([
        'prefix' => 'it',
        'as' => 'it.',
        'middleware' => 'logged',
    ], function () {
        Route::group([
            'prefix' => 'request',
            'as' => 'request.',
        ], function () {
            Route::get('/{status?}', 'TicketClientController@listTicketsCreated')
                ->name('status')->where('status', '[0-9]+');

            Route::get('check/{id}', 'TicketClientController@checkTicket')
                ->name('check')->where('id', '[0-9]+');

            Route::post('change-status', 'TicketClientController@changeStatus')
                ->name('change-status');

            Route::post('change-status-resolved', 'TicketClientController@changeStatusResolved')
                ->name('change-status-resolved');

            Route::post('change-team', 'TicketClientController@changeTeamIT')
                ->name('change-team');

            Route::post('change-deadline', 'TicketClientController@changeDeadline')
                ->name('change-deadline');

            Route::post('change-priority', 'TicketClientController@changePriority')
                ->name('change-priority');

            Route::post('change-related-person', 'TicketClientController@changeRelatedPerson')
                ->name('change-related-person');

            Route::post('comment', 'TicketClientController@saveComment')
                ->name('comment');

            Route::post('save', 'TicketClientController@saveTicket')
                ->name('save');

            Route::post('assign', 'TicketClientController@assignedTo')
                ->name('assign');

            Route::get('mark-read', 'TicketClientController@markRead')
                ->name('mark-read');

            Route::get('find-employee', 'TicketClientController@findEmployee')->name('find-employee');

            Route::group([
                'prefix' => 'assigned',
                'as' => 'assigned.',
            ], function () {
                Route::get('/{status?}', 'TicketClientController@listTicketsAssigned')
                    ->name('status')->where('status', '[0-9]+');
            });

            Route::group([
                'prefix' => 'related',
                'as' => 'related.',
            ], function () {
                Route::get('/{status?}', 'TicketClientController@listTicketsOfRelatedPerson')
                    ->name('status')->where('status', '[0-9]+');
            });

            Route::group([
                'prefix' => 'team',
                'as' => 'team.',
            ], function () {
                Route::get('/{status?}', 'TicketClientController@listTicketsOfTeam')
                    ->name('status')->where('status', '[0-9]+');
            });

            Route::group([
                'prefix' => 'dashboard',
                'as' => 'dashboard.',
            ], function () {
                Route::get('/{status?}', 'TicketClientController@listTicketsOfDepartmentIT')
                    ->name('status')->where('status', '[0-9]+');
            });

        });

        Route::get('/ticketover', 'TicketClientController@getTicketCronjob')
            ->name('ticketover');
    });
});