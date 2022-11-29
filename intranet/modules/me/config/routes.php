<?php
Route::group([
   'middleware' => 'localization' 
], function () {
    Route::group([
        'middleware' => ['auth']
    ], function() {
        Route::group([
            'as' => 'proj.'
        ], function () {
            Route::get('/edit', 'MeController@edit')->name('edit');
            Route::get('/projects-of-pm', 'MeController@getProjectsOfPM')->name('get_pm_projects');
            Route::get('/months-of-project', 'MeController@getMonthsOfProject')->name('get_months_project');
            Route::get('/members-of-project', 'MeController@getMembersOfProject')->name('get_members_project');
            Route::post('/submit', 'MeController@submit')->name('submit');
        });

        Route::post('/save-point', 'MeController@savePoint')->name('save_point');

        Route::group([
            'prefix' => 'team',
            'as' => 'team.',
        ], function () {
            Route::get('/edit', 'MeTeamController@edit')->name('edit');
            Route::get('/get-members', 'MeTeamController@getMembers')->name('get_member');
            Route::post('/submit', 'MeTeamController@submit')->name('submit');
        });

        Route::group([
            'prefix' => 'review',
            'as' => 'review.',
        ], function () {
            Route::get('/list', 'MeController@listReview')->name('list');
            Route::get('/data', 'MeController@getReviewData')->name('data');
            Route::post('/update-status', 'MeController@updateStatus')->name('update_status');
            Route::post('/multiple-update-status', 'MeController@multiUpdateStatus')->name('multi_update_status');
            Route::get('/project-not-evaluate', 'MeController@getProjsNotEval')->name('proj_not_eval');
        });

        Route::delete('/admin/delete', 'MeController@deleteItem')->name('admin.delete_item');

        Route::group([
            'prefix' => 'view-member',
            'as' => 'view.member.'
        ], function () {
            Route::get('/', 'MeController@viewMember')->name('index');
            Route::get('/data', 'MeController@getViewMemberData')->name('data');
        });
    });

    Route::group([
        'prefix' => 'comment',
        'as' => 'comment.',
        'middleware' => 'logged'
    ], function () {
        Route::get('/get-attribute-comments', 'CommentController@getAttributeComments')
                ->name('get_attr_comments');
        Route::post('/add', 'CommentController@addComment')
                ->name('add');
        Route::delete('/delete/{id}', 'CommentController@deleteComment')
                ->where('id', '[0-9]+')
                ->name('delete');
        Route::get('get-evaluation-comments', 'CommentController@getEvalComments')
                ->name('get_eval_comments');
        Route::post('/add-list-comment', 'CommentController@addListComment')->name('add-list-comment');
    });

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
        'middleware' => 'logged',
    ], function () {
        Route::get('/', 'MeController@listConfirm')->name('confirm');
        Route::get('/data', 'MeController@getConfirmData')->name('confirm.data');
        Route::post('/update-status', 'MeController@staffUpdate')->name('confirm.update_status');
    });
});
