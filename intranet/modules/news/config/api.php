<?php
Route::get('posts', 'PostController@getPosts')->name('post.get.list');
Route::get('posts/{slug}', 'PostController@getPostsDetail')->name('post.get.detail');