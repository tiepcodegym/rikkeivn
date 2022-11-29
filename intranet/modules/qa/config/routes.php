<?php

Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
    Route::get('list', 'CategoryController@index')->name('index');
    Route::get('get/list', 'CategoryController@getList')->name('get.list');
    
    Route::get('get/item', 'CategoryController@getItem')->name('get.item');
    Route::post('save', 'CategoryController@save')->name('save');
});

Route::group(['prefix' => 'topic', 'as' => 'topic.'], function () {
    Route::get('list', 'TopicController@index')->name('index');
});