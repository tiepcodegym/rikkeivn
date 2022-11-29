<?php

Route::group(['middleware' => 'auth'], function () {
    Route::group([
        'as' => 'field.',
        'prefix' => 'field'
    ], function() {
        Route::get('manage/{id?}', 'FieldController@index')
            ->name('manage.index')
            ->where('id', '[0-9]+');
        Route::get('manage/get/item', 'FieldController@getItem')
            ->name('manage.get.item');
        Route::get('manage/get/tag/item', 'FieldController@getTagItem')
            ->name('manage.get.tag.item');
        Route::get('manage/tag/count','FieldController@tagCount')
            ->name('manage.tag.count');
        
        Route::post('manage/save', 'FieldController@save')
            ->name('manage.save');
        Route::delete('manage/delete', 'FieldController@delete')
            ->name('manage.delete');
        Route::post('manage/tag/add', 'FieldController@tagAdd')
            ->name('manage.tag.add');
        Route::post('manage/tag/save', 'FieldController@tagSave')
            ->name('manage.tag.save');
        Route::post('manage/tag/approve', 'FieldController@tagApprove')
            ->name('manage.tag.approve');
        Route::delete('manage/tag/delete', 'FieldController@tagDelete')
            ->name('manage.tag.delete');
        Route::post('manage/tag/review/link', 'FieldController@tagReviewLink')
            ->name('manage.tag.review.link');
    });
});

Route::group(['middleware' => 'logged'], function () {
    Route::group([
        'as' => 'object.project.',
        'prefix' => 'object/project'
    ], function() {
        Route::get('/', 'ProjectController@index')
            ->name('index');
        Route::get('data/normal', 'ProjectController@dataNormal')
            ->name('data.normal');
        Route::get('data/check/exists', 'ProjectController@checkexists')
            ->name('data.check.exists');
        Route::get('get/data/item', 'ProjectController@getDataItem')
            ->name('get.data.item');
        Route::get('get/data/item/member', 'ProjectController@getDataItemMember')		
            ->name('get.data.item.member');
        Route::get('get/scope', 'ProjectController@getScope')		
            ->name('get.scope');
        
        Route::post('create', 'ProjectController@create')
            ->name('create');
        Route::post('updateInput', 'ProjectController@saveInput')
            ->name('update.input');
        Route::post('member/save', 'ProjectController@memberSave')
            ->name('member.save');
        Route::delete('member/delete', 'ProjectController@memberDelete')
            ->name('member.delete');

        Route::get('data/list', 'ProjectController@dataList')
                ->name('data.list');
        Route::get('data/count-tags', 'ProjectController@getFieldsTagCount')
                ->name('data.count_tag');
        Route::get('edit-tags', 'ProjectController@getEditTags')
                ->name('edit.tag');
        Route::post('save-tags', 'ProjectController@saveTags')
                ->name('save.tag');
        Route::get('suggest-tags', 'ProjectController@suggestTags')
                ->name('suggest.tags');
        Route::post('save-assignee', 'ProjectController@saveAssignee')
                ->name('save.assignee');
        Route::post('submit-tags', 'ProjectController@submitTags')
                ->name('submit.tag');
        Route::post('approve-tags', 'ProjectController@approveTags')
                ->name('approve.tag');
        Route::post('bulk-action-tags', 'ProjectController@bulkActions')
                ->name('action.tag');
        Route::post('add-tag', 'ProjectController@addTag')
                ->name('add.tag');
        Route::delete('delete-tag', 'ProjectController@deleteTag')
                ->name('delete.tag');
        Route::get('tags-list', 'ProjectController@tagsList')
                ->name('tags.list');
    });
    
    Route::group([
        'as' => 'search.project.',
        'prefix' => 'search/project'
    ], function() {
        Route::get('/', 'SearchProjectController@index')
                ->name('index');
        //api
        Route::get('get/data/normal', 'SearchProjectController@getDataNormal')
                ->name('get.data.normal');
        Route::get('get/most/tags', 'SearchProjectController@getMostTag')
                ->name('get.most.tag');
        Route::get('get/tags/more', 'SearchProjectController@getTagMore')
                ->name('get.tags.more');
        Route::get('get/data/employees', 'SearchProjectController@getEmployeesList')
                ->name('get.data.employees');
        Route::get('get/search/tag', 'SearchProjectController@getSearchTag')
                ->name('get.search.tag');
        Route::get('get/leader/teams', 'SearchProjectController@getLeaderTeam')
                ->name('get.leader.team');
        Route::get('get/employee/busy/rate', 'SearchProjectController@getEmployeeBusyRate')
                ->name('get.employee.busy.rate');
    });
    
    // local storage
    Route::group([
        'as' => 'ldb.',
        'prefix' => 'ldb'
    ], function() {
        Route::get('proj/tag/version', 'LdbController@version')
            ->name('proj.tag.version');
        Route::get('proj/tag/get/data', 'LdbController@getAllProjTag')
            ->name('proj.tag.get.all.data');
    });
    
    Route::group(['as' => 'storage.', 'prefix' => 'storage'], function () {
        Route::get('/export/tags', 'StorageController@exportTagData')
                ->name('export.tags');
    });
});

Route::get('search/tag/select2/{fieldCode?}', 'TagController@searchTagSelect2')
    ->name('search.tag.select2')
    ->middleware(['logged']);