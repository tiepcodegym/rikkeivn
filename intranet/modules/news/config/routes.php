<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'middleware' => 'auth',
    ], function () {
        Route::get('manage/post', 'ManagePostController@index')
            ->name('manage.post.index');
        Route::get('manage/post/create', 'ManagePostController@create')
            ->name('manage.post.create');
        Route::get('manage/post/edit/{id}', 'ManagePostController@edit')
            ->name('manage.post.edit')->where('id', '[0-9]+');
        Route::post('manage/post/save', 'ManagePostController@save')
            ->name('manage.post.save');
        Route::post('manage/post/deleteFile', 'ManagePostController@deletedFile')
            ->name('manage.post.delete.file');

        Route::get('manage/category', 'ManageCategoryController@index')
            ->name('manage.category.index');
        Route::get('manage/category/create', 'ManageCategoryController@create')
            ->name('manage.category.create');
        Route::get('manage/category/edit/{id}', 'ManageCategoryController@edit')
            ->name('manage.category.edit')->where('id', '[0-9]+');
        Route::post('manage/category/save', 'ManageCategoryController@save')
            ->name('manage.category.save');

        Route::get('manage/send/email', 'ManageSendEmailController@index')
            ->name('manage.email.send.index');
        Route::post('manage/send/email/post', 'ManageSendEmailController@post')
            ->name('manage.email.send.post');
        Route::get('manage/post/list/ajax', 'ManageSendEmailController@listPostAjax')
            ->name('manage.email.send.post.list.ajax');

        Route::get('manage/comment', 'ManageCommentController@index')
            ->name('manage.comment.index');
        Route::post('manage/comment/deleteAll', 'ManageCommentController@deleteAllComment')
            ->name('manage.comment.deleteAllComment');
        Route::get('manage/comment/detail/{id}', 'ManageCommentController@detail')
            ->name('manage.comment.detail');
        Route::post('manage/comment/changeStatusComment/{id}', 'ManageCommentController@changeStatusComment')
            ->name('manage.comment.changeStatusComment');
        Route::post('manage/comment/changeStatusAll', 'ManageCommentController@changeStatusAll')
            ->name('manage.comment.changeStatusAll');
        Route::post('approveComment', 'PostController@approveComment')
            ->name('post.approveComment');
        Route::post('manage/comment/unApproveAll', 'ManageCommentController@unApproveAll')
            ->name('manage.comment.unApproveAll');

        Route::get('opinions', 'OpinionController@index')->name('opinions.index');
        Route::get('opinions/{id}/edit', 'OpinionController@edit')->where('id', '[0-9]+')->name('opinions.edit');
        Route::post('opinions/{id}', 'OpinionController@update')->where('id', '[0-9]+')->name('opinions.update');
        Route::delete('opinions/{id}', 'OpinionController@delete')->where('id', '[0-9]+')->name('opinions.delete');

        Route::get('posters', 'PosterController@index')->name('posters.index');
        Route::post('posters', 'PosterController@store')->name('posters.store');
        Route::get('posters/create', 'PosterController@create')->name('posters.create');
        Route::get('posters/{id}/edit', 'PosterController@edit')->where('id', '[0-9]+')->name('posters.edit');
        Route::post('posters/{id}', 'PosterController@update')->where('id', '[0-9]+')->name('posters.update');
        Route::delete('posters/{id}', 'PosterController@delete')->where('id', '[0-9]+')->name('posters.delete');

        Route::get('manage/featured_article', 'FeaturedArticleController@index')
            ->name('manage.featured_article.index');
        Route::put('manage/featured_article/update', 'FeaturedArticleController@updateImpotentAndSetTop')
            ->name('manage.featured_article.update');
    });

    Route::group([
        'middleware' => 'logged',
    ], function () {
        Route::get('/', 'PostController@index')
            ->name('post.index');
        Route::get('cat/{slug}', 'PostController@index')
            ->name('post.index.cat');
        Route::get('post/{slug}', 'PostController@view')
            ->name('post.view');
        Route::post('like', 'PostController@like')
            ->name('post.like');
        Route::get('post/get/all/count', 'PostController@getAllCount')
            ->name('post.get.all.count');
        Route::get('listLike', 'PostController@getListLike')
            ->name('post.listLike');
        Route::post('comment', 'PostController@comment')
            ->name('post.comment');
        Route::post('getMoreComment', 'PostController@getMoreComment')
            ->name('post.moreComment');
        Route::post('comment/delete', 'PostController@deleteComment')
            ->name('post.comment.delete');

        Route::post('opinions', 'OpinionController@store')->name('opinions.store');
    });

    Route::get('render/{render?}', 'PostController@postForGuest')
        ->name('post.guest');
    Route::post('publish-news', 'PostController@publishNews')
        ->name('post.publishNews');
    Route::post('publish-news-recruitment', 'PostController@publishNewsRecruitment')
        ->name('post.publishNewsRecruitment');
});
