<?php

Route::group([
        'middleware' => 'auth',
        'prefix' => 'manage',
        'namespace' => 'Admin',
        'as' => 'manage.'
    ], function () {
    
        Route::group(['prefix' => 'list', 'as' => 'vote.'], function () {
            Route::get('/', ['as' => 'index', 'uses' => 'VoteController@index']);
            Route::get('/create', ['as' => 'create', 'uses' => 'VoteController@create']);
            Route::post('/store', ['as' => 'store', 'uses' => 'VoteController@store']);
            Route::get('/{id}/edit', ['as' => 'edit', 'uses' => 'VoteController@edit'])
                    ->where('id', '[0-9]+');
            Route::put('/{id}/update', ['as' => 'update', 'uses' => 'VoteController@update'])
                    ->where('id', '[0-9]+');
            Route::delete('/{id}/delete', ['as' => 'delete', 'uses' => 'VoteController@delete'])
                    ->where('id', '[0-9]+');
            Route::post('/{id}/sendmail-nominate', ['as' => 'sendmail.nominate', 'uses' => 'VoteController@sendNominateEmail'])
                    ->where('id', '[0-9]+');
            Route::post('/{id}/sendmail-vote', ['as' => 'sendmail.vote', 'uses' => 'VoteController@sendVoteEmail'])
                    ->where('id', '[0-9]+');
        });
        
        /******** nomination ******/
        Route::get('nominees/{vote_id}/list-ajax', ['as' => 'nominee.load_data', 'uses' => 'NominationController@getNomineesAjaxData'])
                ->where('vote_id', '[0-9]+');
        Route::get('nominators/{vote_id}/{nominee_id}/list-ajax', ['as' => 'nominator.load_data', 'uses' => 'NominationController@getNominatorsAjaxData'])
                ->where('vote_id', '[0-9]+')
                ->where('nominee_id', '[0-9]+'); 
        
        /******** vote nominee ******/
        Route::group(['prefix' => 'vote-nominees', 'as' => 'vote_nominee.'], function () {
            Route::get('/{vote_id}/list-ajax', ['as' => 'load_data', 'uses' => 'NominationController@getVoteNomineesAjaxData'])
                    ->where('vote_id', '[0-9]+');
            Route::put('/{id}/update-description', ['as' => 'update_desc', 'uses' => 'NominationController@updateVoteNomineeDesc'])
                    ->where('id', '[0-9]+');
            Route::delete('/{id}/delete', ['as' => 'delete', 'uses' => 'NominationController@deleteVoteNominee'])
                    ->where('id', '[0-9]+');
            Route::get('/employees/search-ajax', ['as' => 'list_employee', 'uses' => 'NominationController@searchEmployees']);
            Route::post('vote-nominees/store', ['as' => 'store', 'uses' => 'NominationController@storeVoteNominee']);
        });

        /******* voter ********/
        Route::get('voters/{vote_nominee_id}/list-ajax', ['as' => 'voter.load_data', 'uses' => 'NominationController@getVotersAjaxData'])
                ->where('vote_nominee_id', '[0-9]+');
});

/******** front *********/
Route::group(['middleware' => 'logged'], function () {
    Route::get('nomination/employees/search-ajax', ['as' => 'list_employee', 'uses' => 'VoteController@searchNomineeEmployees']);
    Route::get('/confirm-nominate/{key}', ['as' => 'nominee_confirm', 'uses' => 'VoteController@nomineeConfirmEmail']);
    Route::get('/nominate/{slug}', ['as' => 'show_nominate', 'uses' => 'VoteController@showNominate']);
    Route::get('/self-nominate/{slug}', ['as' => 'show_self_nominate', 'uses' => 'VoteController@showSelfNominate']);
    Route::get('/detail/{slug}', ['as' => 'detail', 'uses' => 'VoteController@detail']);
    Route::get('/{slug}', ['as' => 'show_vote', 'uses' => 'VoteController@showVote']);
    Route::post('/nominate/{vote_id}', ['as' => 'add_nominate', 'uses' => 'VoteController@addNominate'])
            ->where('vote_id', '[0-9]+');
    Route::post('/self-nominate/{vote_id}', ['as' => 'add_self_nominate', 'uses' => 'VoteController@addSelfNominate'])
            ->where('vote_id', '[0-9]+');
    Route::post('/add-voter/{vote_id}', ['as' => 'add_voter', 'uses' => 'VoteController@addVoter'])
            ->where('vote_id', '[0-9]+');
    Route::post('/vote-nominee/{vote_nominee_id}', ['as' => 'vote_nominee', 'uses' => 'VoteController@addVote']);
});
