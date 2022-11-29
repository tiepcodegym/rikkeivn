<?php
Route::group(['middleware' => 'localization', 'prefix' => Session::get('locale')], function () {
    Route::get('project/create', 'ProjectController@create')->name('project.create');
    Route::post('project/reward/export', 'RewardController@exportReward')
        ->name('reward.exportReward');
    Route::post('project/create', 'ProjectController@store')->name('project.create');
    Route::post('project/checkExists', 'ProjectController@checkExists')
        ->name('project.checkExists');
    Route::post('project/checkExistsSourceServer', 'ProjectController@checkExistsSourceServer')
        ->name('project.checkExistsSourceServer');
    Route::get('project/edit/{id}', 'ProjectController@edit')
        ->name('project.edit')->where('id', '[0-9]+');
    Route::post('project/get-current-skill-employee', 'ProjectController@getCurrentSkill')
        ->name('project.get-current-skill');
    Route::post('project/save/purchase', 'ProjectController@savePurchase')
        ->name('project.save.purchase');
    Route::post('project/get/purchase', 'ProjectController@getPurchase')
        ->name('project.get.purchase');
    Route::post('project/save/purchase-to-crm', 'ProjectController@savePurchaseToCRM')
        ->name('project.save.purchase_to_crm');
    
    Route::get('project/get-json-employee-project', 'ProjectController@getJsonEmpProject')
    ->name('project.get-json-employee-project');

    Route::post('project/delete/file', 'ProjectController@deleteFile')
        ->name('project.delete.file');
//Route Project report
    Route::get('project/project-report/{projectId}', 'ProjectController@getProjectCalendarReport')
        ->name('project.get-calendar-report');
    Route::get('project/project-calendar-report/{reportId}', 'ProjectController@getProjectCalendarReportByDate')
        ->name('project.get-report-by-id');
    Route::post('project/project-calendar-report/create/{projectId}', 'ProjectController@publishCalendarReport')
        ->name('project.create-calendar-report');
    Route::post('project/project-calendar-report/update/{reportId}', 'ProjectController@updateCalendarReport')
        ->name('project.update-calendar-report');
    Route::delete('project/delete-report/{reportId}', 'ProjectController@deleteCalendarReport')
        ->name('project.delete-report');
    Route::post('project/sync-team-allocation', 'ProjectController@syncProjectAllocation')
        ->name('project.sync_project_allocation');
    Route::post('project/sync-report-example', 'ProjectController@syncReportExample')
        ->name('project.sync_report_example');
    Route::post('project/{id}/delete', 'ProjectController@delete')
        ->name('project.delete')->where('id', '[0-9]+');
    Route::post('project/add-critical-dependencies', 'ProjectController@addCriticalDependencies')->name('project.add_critical_dependencies');
    Route::post('project/add-assumption-constrain', 'ProjectController@addAssumptionConstrain')->name('project.add_assumption_constrain');
    Route::post('project/add-risk', 'ProjectController@addRisk')->name('project.add_risk');
    Route::post('project/add-stage-and-milestone', 'ProjectController@addStageAndMilestone')->name('project.add_stage_and_milestone');
    Route::post('project/add-training', 'ProjectController@addTraining')->name('project.add_training');
    Route::post('project/add-external-interface', 'ProjectController@addExternalInterface')->name('project.add_external_interface');
    Route::post('project/add-tool-and-infrastructure', 'ProjectController@addToolAndInfrastructure')->name('project.add_tool_and_infrastructure');
    Route::post('project/add-communication', 'ProjectController@addCommunication')->name('project.add_communication');
    Route::post('project/add-deliverable', 'ProjectController@addDeliverable')->name('project.add_deliverable');
    Route::post('project/add-assumptions', 'ProjectController@addAssumptions')->name('project.add_assumptions');
    Route::post('project/add-member-communication', 'ProjectController@addMemberCommunication')->name('project.add_member_communication');
    Route::post('project/add-customer-communication', 'ProjectController@addCustomerCommunication')->name('project.add_customer_communication');
    Route::post('project/add-project-communication', 'ProjectController@addProjectCommunication')->name('project.add_project_communication');
    Route::post('project/add-skill-request', 'ProjectController@addSkillRequest')->name('project.add_skill_request');
    Route::post('project/add-security', 'ProjectController@addSecurity')->name('project.add_security');
    Route::post('project/update-note', 'ProjectController@updateProjectWONote')->name('project.update_note');
    Route::post('project/add-performance', 'ProjectController@addPerformance')->name('project.add_performance');
    Route::post('project/add-quality', 'ProjectController@addQuality')->name('project.add_quality');
    Route::post('project/add-quality-plan', 'ProjectController@addQualityPlan')->name('project.add_quality_plan');
    Route::post('project/add-cm-plan', 'ProjectController@addCMPlan')->name('project.add_cm_plan');
    Route::post('project/{id}/submit-workorder', 'ProjectController@submitWorkorder')
        ->name('project.submit_workorder')->where('id', '[0-9]+');
    Route::post('project/add-project-member', 'ProjectController@addProjectMember')->name('project.add_project_member');
    Route::post('project/update-reason', 'ProjectController@updateReason')->name('project.update_reason');
    Route::post('project/generate-select-leader', 'ProjectController@generateSelectLeader')->name('project.generate_select_leader');
    Route::post('project/get-content-table', 'ProjectController@getContentTable')->name('project.get_content_table');
    Route::get('project/{id}/workorder/log/ajax', 'ProjectController@logListAjax')
        ->name('workorder.log.list.ajax')->where('id', '[0-9]+');
    Route::get('project/{id}/dashboard/log/ajax', 'PointController@dashboardLogListAjax')
        ->name('dashboard.log.list.ajax')->where('id', '[0-9]+');
    Route::post('project/check-has-stage', 'ProjectController@checkHasStageMilestone')->name('project.check_has_stage');
    Route::post('project/edit-basic-info', 'ProjectController@editBasicInfo')->name('project.edit_basic_info');
    Route::post('project/update-pm', 'ProjectController@getALlPmOfProjectByAjax')->name('project.wo.update-pm');
    Route::post('project/check-is-change-status-wo', 'ProjectController@checkIsChangeStatusWo')->name('project.check_is_change_status_wo');
    Route::get('project/wo/edit-risk', 'ProjectController@editRisk')
        ->name('wo.editRisk');
    Route::get('project/wo/edit-nc', 'ProjectController@editNc')
        ->name('wo.editNc');
    Route::post('project/wo/save-risk', 'ProjectController@saveRisk')
        ->name('wo.saveRisk');
    Route::get('project/wo/edit-issue', 'ProjectController@editIssue')
        ->name('wo.editIssue');
    Route::post('project/wo/save-issue', 'ProjectController@saveIssue')
        ->name('wo.saveIssue');
    Route::get('project/wo/form-nc', 'ProjectController@getFormNC')->name('wo.getFormNC');
    Route::get('project/wo/form-opportunity', 'ProjectController@getFormOpportunity')->name('wo.getFormOpportunity');
    Route::get('project/wo/form-view-opp', 'ProjectController@getFormViewOpportunity')->name('wo.getFormViewOpp');
    Route::post('project/wo/change-risk-status', 'ProjectController@changeRiskStatus')
        ->name('wo.changeRiskStatus');
    Route::post('project/wo/other/popoverGetAttribute', 'ProjectController@popoverGetAttribute')->name('project.wo.other.popover');
    Route::post('project/update-time', 'ProjectController@updateTime')->name('project.updateTime');
    Route::post('project/gen-cus-sale-by-company', 'ProjectController@genCusAndSaleByCompany')->name('project.gen_cus_and_sale_by_company');
    Route::post('project/add-devices-expenses', 'ProjectController@addDevicesExpenses')->name('project.add_devices_expenses');
    /* Project dashboard */
    Route::get('project/dashboard', 'PointController@index')
        ->name('dashboard');
    Route::get('project/dashboard/{type}', 'PointController@index')->where('type', 'watch-list')
        ->name('dashboard.isWatch');
    Route::post('project/ajaxAddOrRemoveWatcher', 'PointController@ajaxAddOrRemoveWatcher')
        ->name('ajaxAddOrRemoveWatcher');
    Route::get('project/baseline-all', 'PointController@projectBaseline')
        ->name('baseline.all');
    Route::get('project/point/{id}', 'PointController@edit')
        ->name('point.edit')->where('id', '[0-9]+');
    Route::post('project/point/save/{id}', 'PointController@save')
        ->name('point.save')->where('id', '[0-9]+');
    Route::post('project/point/updateNote/{id}', 'PointController@updateNote')
        ->name('point.updateNote')->where('id', '[0-9]+');
    Route::get('project/point/getPoint', 'PointController@getPoint')->name('point.getPoint');
    Route::get('project/dashboard/baseline/{slug}', 'PointController@baseline')
        ->name('point.baseline')->where('slug', '[0-9\-]+');
    Route::get('project/dashboard/baseline/detail/{id}', 'PointController@baselineDetail')
        ->name('point.baseline.detail')->where('id', '[0-9]+');
    Route::get('project/{id}/dashboard/baseline/detail/slug/{slug}', 'PointController@baselineDetailSlug')
        ->name('point.baseline.detail.slug')
        ->where('slug', '[0-9\-]+')
        ->where('id', '[0-9]+');
    Route::get('project/dashboard/export/{ids?}', 'PointController@export')
        ->name('dashboard.export')->where('ids', '[0-9\-]+');
    Route::post('project/task/titles', 'TaskController@taskTitles')
        ->name('dashboard.task.title');
    Route::get('project/dashboard/{id}/init/ajax', 'PointController@initAjax')
        ->name('point.init.ajax')->where('id', '[0-9\-]+');
    Route::get('project/dashboard/{id}/report/ajax', 'PointController@reportListAjax')
        ->name('point.report.list.ajax')->where('id', '[0-9\-]+');
    Route::get('project/dashboard/{id}/deliver/ajax', 'PointController@deliverListAjax')
        ->name('point.deliver.list.ajax')->where('id', '[0-9\-]+');
    Route::post('project/dashboard/{id}/report', 'PointController@reportSubmit')
        ->name('point.report')->where('id', '[0-9\-]+');
    Route::post('project/dashboard/raise', 'PointController@raise')
        ->name('dashboard.raise');
    Route::post('project/dashboard/raise/destroy/{id}', 'PointController@raiseDestroy')
        ->name('dashboard.raise.destroy')->where('id', '[0-9\-]+');
    Route::post('project/dashboard/notes', 'PointController@dashboardNotes')
        ->name('dashboard.notes');
    Route::post('project/dashboard/deliver/save/{id}', 'PointController@deliverSave')
        ->name('dashboard.deliver.save')->where('id', '[0-9\-]+');
    Route::get('project/dashboard/help', 'PointController@helpView')
        ->name('point.dashboard.help');
    Route::post('project/dashboard/raise/baseline', 'PointController@raiseBaseline')
        ->name('dashboard.raise.baseline');
    Route::post('project/dashboard/raise/destroy/{id}/baseline', 'PointController@raiseDestroyBaseline')
        ->name('dashboard.raise.destroy.baseline')->where('id', '[0-9\-]+');
    Route::get('project/dashboard/export/baseline/{ids?}', 'PointController@exportBaseline')
        ->name('dashboard.export.baseline')->where('ids', '[0-9\-]+');
    Route::get('project/wo/help', 'PointController@helpViewWo')
        ->name('wo.help');
    Route::post('project/point/{id}/cost_productivity/save', 'PointController@saveCostProductivityProgLang')
        ->name('cost.productivity.save')->where('id', '[0-9\-]+');
    Route::post('project/dashboard/{id}/check-report-note', 'PointController@checkReportNote')
        ->name('point.check-report-note')->where('id', '[0-9\-]+');
    Route::post('project/insert-compliance', 'PointController@insertCompliance')
        ->name('point.insert-compliance');
    // Project plan
    Route::get('project/plan/{projectId}', 'ProjectPlanController@index')->name('plan.comment');
    Route::get('project/plan/{projectId?}/comment/list/ajax', 'ProjectPlanController@commentListAjax')
        ->name('plan.comment.list.ajax')->where('id', '[0-9]+');
    Route::post('project/plan/save-comment', 'ProjectPlanController@saveComment')->name('plan.saveComment');
    Route::post('project/plan/upload-file', 'ProjectPlanController@uploadFile')->name('plan.upload');
    Route::get('project/plan/download-file/{filename}', 'ProjectPlanController@downloadFile')->name('plan.download');
    Route::delete('project/plan/delete-file/{filename}', 'ProjectPlanController@deleteFile')->name('plan.delete-file');
    Route::post('project/plan/get-project-member', 'ProjectPlanController@getProjectMember')->name('plan.projectMember');
    Route::post('project/get-day-project', 'ProjectController@getDayOfProject')->name('project.get-day-project');

    Route::get('issue/{id}', 'TaskController@detail')
        ->name('issue.detail')->where('id', '[0-9]+');
    Route::post('issue/delete', 'TaskController@deleteIssue')
        ->name('issue.delete');
    Route::post('risk/cancel', 'RiskController@riskCancel')
        ->name('risk.cancel');
    Route::get('risk/{id}', 'RiskController@detail')
        ->name('risk.detail')->where('id', '[0-9]+');
    Route::post('issue', 'TaskController@saveCommentIssue')->name('issue.save.comment');
    Route::post('plan/save-file', 'ProjectPlanController@saveCommentPlan')->name('plan.save.comment');
    Route::get('project/issue/download/{id}', 'ProjectController@downloadFile')->name('issue.download');
    /* Project task */
    Route::get('project/{id}/task', 'TaskController@index')
        ->name('task.index')->where('id', '[0-9]+');
    Route::get('project/{id}/task/approve', 'TaskController@approve')
        ->name('task.index.approve')->where('id', '[0-9]+');
    Route::get('project/{id}/task/add', 'TaskController@add')
        ->name('task.add')->where('id', '[0-9]+');
    Route::get('task/{id}', 'TaskController@edit')
        ->name('task.edit')->where('id', '[0-9]+');
    Route::get('task/self', 'TaskController@taskSelf')
        ->name('task.my.task');
    Route::get('project/{id}/task/ajax', 'TaskController@taskListAjax')
        ->name('task.list.ajax')->where('id', '[0-9]+');
    Route::post('project/{id}/task/generate-html', 'TaskController@generateHtml')
        ->name('task.generateHtml')->where('id', '[0-9]+');
    Route::post('task/save', 'TaskController@save')->name('task.save');
    Route::post('task/save/comment', 'TaskController@saveComment')
        ->name('task.save.comment');
    Route::post('task/delete/comment', 'TaskController@deleteComment')
        ->name('task.delete.comment');
    Route::post('project/{id}/task/add/ajax', 'TaskController@addAjax')
        ->name('task.add.ajax')->where('id', '[0-9]+');
    Route::post('task/ajax/{id}', 'TaskController@editAjax')
        ->name('task.edit.ajax')->where('id', '[0-9]+');
    Route::post('task/get_child', 'TaskController@taskChild')
        ->name('task.task_child.ajax');
    Route::post('task/get_risk', 'TaskController@taskRisk')
        ->name('task.task_risk.ajax');

    Route::post('project/cate/save', 'ProjectController@saveCate')
        ->name('project.cate.save');
    Route::post('project/contact/save', 'ProjectController@saveContact')
        ->name('project.contact.save');
    Route::post('project/update/close', 'ProjectController@updateCloseDate')
        ->name('project.update.close');
    Route::post('project/{id}/sync/sourceserver', 'SoruceServerController@sync')
        ->name('sync.source.server')->where('id', '[0-9]+');

    Route::post('task/wo/{id}/review/submit/{myTask?}', 'ApproveController@reviewSubmit')
        ->name('task.wo.review.submit')
        ->where('id', '[0-9]+')->where('userId', '[0-9]+');
    Route::post('task/wo/{id}/review/feedback/{myTask?}', 'ApproveController@reviewFeedback')
        ->name('task.wo.review.feedback')
        ->where('id', '[0-9]+')->where('userId', '[0-9]+');
    Route::post('task/wo/{id}/approve/submit{myTask?}', 'ApproveController@approveSubmit')
        ->name('task.wo.approve.submit')->where('id', '[0-9]+');
    Route::post('task/wo/{id}/approve/feedback{myTask?}', 'ApproveController@approveFeedback')
        ->name('task.wo.approve.feedback')->where('id', '[0-9]+');
    Route::post('task/wo/{id}/undo/feedback{myTask?}', 'ApproveController@undoFeedback')
        ->name('task.wo.undo.feedback')->where('id', '[0-9]+');
    Route::post('task/wo/{id}/approver/change', 'ApproveController@changeApprover')
        ->name('task.wo.approver.change')->where('id', '[0-9]+');
    Route::post('task/wo/{id}/reviewer/add', 'ApproveController@addReviewer')
        ->name('task.wo.reviewer.add')->where('id', '[0-9]+');
    Route::post('task/wo/{id}/reviewer/delete', 'ApproveController@deleteReviewer')
        ->name('task.wo.reviewer.delete')->where('id', '[0-9]+');
    Route::get('task/{id}/comment/list/ajax', 'TaskController@commentListAjax')
        ->name('task.comment.list.ajax')->where('id', '[0-9]+');
    Route::get('task/{id}/history/list/ajax', 'TaskController@historyListAjax')
        ->name('task.history.list.ajax')->where('id', '[0-9]+');

// reward
    Route::get('project/{id}/reward/{taskID?}', 'RewardController@index')
        ->name('reward')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/get-content-table', 'RewardController@getContentTable')
        ->name('reward.get.content.table')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/comment', 'RewardController@comment')
        ->name('reward.comment')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/submit/{taskID?}', 'RewardController@submit')
        ->name('reward.submit')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/feedback/{taskId?}', 'RewardController@feedback')
        ->name('reward.feedback')
        ->where('id', '[0-9]+')
        ->where('taskId', '[0-9]+');
    Route::post('project/{id}/reward/confirm', 'RewardController@confirm')
        ->name('reward.confirm')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/approve', 'RewardController@approve')
        ->name('reward.approve')->where('id', '[0-9]+');
    Route::post('project/{id}/reward/budget/save/{save?}/{monthReward?}', 'RewardController@budgetSave')
        ->name('reward.budget.save')
        ->where('id', '[0-9]+')
        ->where('save', '[01]');
    Route::post('project/{id}/reward/update-bonus-money', 'RewardController@updateBonusMoney')
        ->name('reward.update.bonusMoney')
        ->where('id', '[0-9]+');
    Route::post('project/{id}/reward/budget/public', 'RewardController@budgetPublic')
        ->name('reward.budget.public')->where('id', '[0-9]+');
    Route::post('project/reward/employee/comment', 'RewardController@rewardComment')
        ->name('reward.employee.comment');
    Route::post('project/reward/employee/get-comment', 'RewardController@getComment')
        ->name('reward.employee.getComment');
    Route::post('project/reward/actual/delete', 'RewardController@deleteActual')
        ->name('reward.actual.delete');
    Route::post('project/reward/actual/edit/number', 'RewardController@editNumber')
        ->name('reward.actual.edit.number');
//delete add type
    Route::delete('project/reward/delete/employee', 'RewardController@deleteEmployee')
        ->name('reward.delete.employee');
    Route::post('project/show_team', 'ProjectController@showTeamByProj')
        ->name('project.show.team');
    Route::post('project/show_approver', 'ProjectController@showApproverByProj')->name('project.show.approver');
// general task
    Route::get('task/general/list/{tab?}', 'TaskGeneralController@index')
        ->name('task.general.list');
    Route::get('task/general/create', 'TaskGeneralController@create')
        ->name('task.general.create');
    Route::get('task/general/create/ajax/{id?}', 'TaskGeneralController@createAjax')
        ->where('id', '[0-9]+')
        ->name('task.general.create.ajax');
    Route::post('task/general/save/{id?}', 'TaskGeneralController@save')
        ->name('task.general.save')->where('id', '[0-9]+');
    Route::post('task/general/save/priority/{id?}/{priority?}', 'TaskGeneralController@updatePriorityStatus')
        ->name('task.general.save.priority')->where('id', '[0-9]+')->where('priority', '[0-9]');
    Route::post('task/general/save/status/{id?}/{status?}', 'TaskGeneralController@updatePriorityStatus')
        ->name('task.general.save.status')->where('id', '[0-9]+')->where('status', '[0-9]');
    Route::post('task/contract_confirm/{projectId}', 'TaskController@contractConfirm')
        ->name('task.contractConfirm');

// task - project
    Route::post('task/project/import-task', 'TaskGeneralController@importProjectTask')
        ->name('task.project.import-task')->where('id', '[0-9]+')->where('status', '[0-9]');
    //ncm
    Route::post('task/ncm/create/{id}', 'NcmController@create')
        ->name('task.ncm.create')->where('id', '[0-9]+');
    Route::post('task/ncm/edit/{id}', 'NcmController@edit')
        ->name('task.ncm.edit')->where('id', '[0-9]+');
    Route::post('task/ncm/save', 'NcmController@save')
        ->name('task.ncm.save');
    Route::post('task/ncm/{id}/delete', 'NcmController@delete')
        ->name('task.ncm.delete')->where('id', '[0-9]+');
    Route::get('ncm/pdf', 'NcmController@getPDF')
        ->name('ncm.pdf');
    Route::post('nc/save-nc', 'NcmController@saveNC')->name('nc.save');
    Route::get('nc/{id}', 'NcmController@detail')->name('nc.detail')->where('id', '[0-9]+');
    Route::get('nc/delete', 'NcmController@deleteNC')->name('nc.delete');

    Route::group([
        'as' => 'report.',
        'prefix' => 'opportunity'
    ], function () {
        Route::post('/save', 'OpportunityWOController@save')->name('opportunity.save');
        Route::get('/{id}', 'OpportunityWOController@detail')->name('opportunity.detail')->where('id', '[0-9]+');
        Route::post('/export', 'OpportunityWOController@export')->name('opportunity.export');
    });

    /* Monthly Evaluation */
    Route::group(['prefix' => '/project/monthly-evaluation', 'middleware' => 'auth'], function () {
        Route::get('/', 'MeEvalController@index')->name('project.eval.index');
        Route::get('/leader-review', 'MeEvalController@listByLeader')->name('project.eval.list_by_leader');
        Route::post('/update', 'MeEvalController@update')->name('project.eval.update');
        Route::get('/load-project-and-members', 'MeEvalController@getProjectAndMembers')->name('project.eval.get_project_and_members');
        Route::post('/add-attribute-point', 'MeEvalController@addAttrPoint')->name('project.eval.add_attr_point');
        Route::put('/leader-update/{id}', 'MeEvalController@leaderUpdate')->name('project.eval.leader_update')
            ->where('id', '[0-9]+');
        Route::post('/multi-actions', 'MeEvalController@multiActions')->name('project.eval.multi_actions');
        //Route::post('/get-point-attribute-time', 'MeEvalController@loadPointAttrTime')->name('project.eval.get_point_attr_time');
        Route::get('/get-months-of-project', 'MeEvalController@loadMonthsOfProject')->name('project.eval.load_project_months');
        Route::post('/update-avg-point', 'MeEvalController@updateAvgPoint')->name('project.eval.update_avg_point');
        Route::post('/coo-edit-point', 'MeEvalController@cooEditPoint')->name('me.coo_edit_point');
        Route::delete('/{id}/delete', 'MeEvalController@delete')->name('me.delete_item')
            ->where('id', '[0-9]+');

        // Upload monthly timesheet
//    Route::get('/get-upload-timesheet', 'MeEvalController@getUploadTimeSheet')->name('timesheet.eval.get_upload');
//    Route::post('/post-upload-timesheet', 'MeEvalController@postUploadTimeSheet')->name('timesheet.eval.post_upload');

        // Leader view member of team
        Route::get('/leader-view-member-of-team', 'MeEvalController@leaderViewMemberOfTeam')->name('project.eval.leader_view_of_team');
        Route::get('/review-statistic', 'MeEvalController@reviewStatistic')->name('project.eval.review_statistic');

        /* Manage attributes */
        Route::get('/attributes', 'MeAttributeController@index')->name('eval.attr.index');
        Route::get('/attributes/create', 'MeAttributeController@create')->name('eval.attr.create');
        Route::post('/attributes/store', 'MeAttributeController@store')->name('eval.attr.store');
        Route::get('/attributes/{id}/edit', 'MeAttributeController@edit')->name('eval.attr.edit')->where('id', '[0-9]+');
        Route::put('/attributes/{id}/update', 'MeAttributeController@update')->name('eval.attr.update')->where('id', '[0-9]+');
        Route::get('/attributes/{id}/delete', 'MeAttributeController@destroy')->name('eval.attr.destroy')->where('id', '[0-9]+');
    });
    Route::group([
        'prefix' => 'profile/evaluation'
    ], function () {
        Route::get('/', 'MeEvalController@listByStaft')->name('project.profile.confirm');
        Route::get('/activities', 'MeActivityController@activity')->name('profile.me.activity');
        Route::post('/save-activities', 'MeActivityController@saveActivity')->name('profile.me.save.activity');
    });
    Route::group(['prefix' => '/project/monthly-evaluation'], function () {
        Route::put('/staff-update/{id}', 'MeEvalController@staffUpdate')->name('project.eval.staff_update')
            ->where('id', '[0-9]+');
        Route::post('/add-comment', 'MeEvalController@addComment')->name('project.eval.add_comment');
        Route::delete('/remove-comment/{id}', 'MeEvalController@removeComment')->name('project.eval.remove_comment')
            ->where('id', '[0-9]+');
        Route::get('/load-attribute-comments', 'MeEvalController@loadAttrComments')->name('project.eval.load_attr_comments');
        Route::get('/help', 'MeEvalController@help')->name('project.eval.help');
        //view activity
        Route::get('/member-activities', 'MeActivityController@viewMembers')->name('me_activity.view');
    });

    Route::group(['prefix' => '/project/team/monthly-evaluation'], function () {
        Route::get('/', 'MeTeamController@create')->name('team.eval.create');
        Route::get('/load-members', 'MeTeamController@loadMembers')->name('team.eval.load_members');
        Route::post('/submit', 'MeTeamController@submit')->name('team.eval.submit');
    });
    Route::get('project-team/search-ajax', 'MeEvalController@searchProjectOrTeam')->name('me.search.project.team.ajax');
    Route::get('report/common-risk', 'CommonRiskController@listView')->name('report.common-risk');
    Route::get('report/common-issue', 'CommonIssueController@listView')->name('report.common-issue');

    Route::group([
        'middleware' => 'auth',
        'as' => 'report.',
        'prefix' => 'report',
    ], function () {
        Route::get('risk', 'RiskController@risk')->name('risk');
        Route::get('issue', 'TaskController@issue')->name('issue');
        Route::get('ncm', 'NcmController@viewList')->name('ncm');
        Route::get('ncm/detail/{id}', 'NcmController@viewDetail')
            ->name('ncm.detail')->where('id', '[0-9]+');
        Route::get('gitlab/{page}', 'SoruceServerController@listGitLabProjects')->name('gitlab');
        Route::get('/opportunity', 'OpportunityWOController@index')->name('opportunity');
    });
    Route::post('risk', 'RiskController@saveComment')->name('risk.save.comment');
    Route::post('risk', 'RiskController@saveComment')->name('risk.save.comment');
    Route::get('project/issue/download/{id}', 'ProjectController@downloadFile')->name('issue.download');
    Route::group([
        'middleware' => 'logged',
        'as' => 'report.',
        'prefix' => 'report',
    ], function () {
        Route::get('risk/detail/{id}', 'RiskController@detail')->name('risk.detail');
    });
    Route::group([
        'middleware' => 'auth',
    ], function () {
//        common risk
        Route::get('common-risk/detail/{id}', 'CommonRiskController@detail')->name('commonRisk.detail');
        Route::get('common-risk/delete', 'CommonRiskController@delete')
            ->name('commonRisk.delete');
        Route::post('project/save-common-risk', 'CommonRiskController@saveCommonRisk')
            ->name('commonRisk.save');

//        common issue
        Route::get('common-issue/detail/{id}', 'CommonIssueController@detail')->name('commonIssue.detail');
        Route::get('common-issue/delete', 'CommonIssueController@delete')
            ->name('commonIssue.delete');
        Route::post('project/save-common-issue', 'CommonIssueController@saveCommonIssue')
            ->name('commonIssue.save');
        
    });
    Route::get('project/edit-common-risk', 'CommonRiskController@editCommonRisk')
            ->name('commonRisk.edit');
    Route::get('project/edit-common-issue', 'CommonIssueController@editCommonIssue')
            ->name('commonIssue.edit');

    Route::get('report/issue/detail/{id}', 'TaskController@detail')->name('task.detail');
    Route::get('report/reward', 'RewardController@listActual')->name('report.reward.list');
    Route::get('report/reward/export', 'RewardController@exportApproveData')->name('report.reward.export');
    Route::post('report/reward/osdc-base-export', 'RewardController@rewardExport')->name('report.reward_osdc_base.export');

    Route::get('project/search-ajax', 'ProjectController@listSearchAjax')->name('list.search.ajax');
    Route::get('project/search-member-by-ajax', 'ProjectController@listSearchTeamMemberByAjax')->name('list.search.member.ajax');
    // view evaluated
    Route::group(['prefix' => 'monthly-evaluation', 'middleware' => 'auth'], function () {
        Route::get('/view-evaluated', 'MeEvalController@viewEvaluated')->name('me.view.evaluated');
        Route::get('/not-evaluate', 'MeEvalController@notEvaluate')->name('me.view.not_evaluate');
        //config
        Route::get('/config-data', 'MeEvalController@configData')->name('me.config_data');
        Route::post('/save-config', 'MeEvalController@saveConfig')->name('me.config_data.save');
    });
    // ME reward
    Route::group(['prefix' => 'monthly-evaluation-reward', 'middleware' => 'auth'], function () {
        Route::get('/edit', 'MeRewardController@edit')->name('me.reward.edit');
        Route::post('/update-comment', 'MeRewardController@updateComment')->name('me.reward.update_comment');
        Route::post('/submit', 'MeRewardController@submit')->name('me.reward.submit');
        Route::get('/review', 'MeRewardController@review')->name('me.reward.review');
        Route::post('/approve', 'MeRewardController@approve')->name('me.reward.approve');
        Route::post('/export-data', 'MeRewardController@exportData')->name('me.reward.export_data');
        Route::post('/update-paid', 'MeRewardController@updatePaid')->name('me.reward.update_paid');
        Route::delete('/delete-item', 'MeRewardController@deleteItem')->name('me.reward.delete_item');
        Route::get('/total-reward', 'MeRewardController@getTotalReward')->name('me.reward.total_reward');
        Route::post('/import-excel', 'MeRewardController@importExcel')->name('me.reward.import_excel');
        Route::get('/import-excel/download/{fileName}', 'MeRewardController@download')->name('me.reward.download_excel');
        Route::get('/format-excel-file', 'MeRewardController@downloadFormatFile')->name('me.reward.downloadFormatFile');
    });

    Route::group(['prefix' => 'get', 'as' => 'get.'], function () {
        Route::get('working/days', 'ApiController@workingDays')
            ->name('working.days');
    });
    // Monthly report
    Route::group(
        [
            'prefix' => 'team/monthly-report',
            'as' => 'monthly.report.',
            'middleware' => 'auth'
        ], function () {
        Route::get('/index', 'MonthlyReportController@index')->name('index');
        Route::post('/update-data', 'MonthlyReportController@update')->name('update');
        Route::post('/search', 'MonthlyReportController@search')->name('search');
        Route::post('/import-billable', 'MonthlyReportController@importBillable')->name('import_billable');
        Route::post('/export-billable', 'MonthlyReportController@exportBillable')->name('export_billable');
        Route::get('/dconfig', 'MonthlyReportController@teamConfig')->name('team_config');
        Route::post('/save-dconfig', 'MonthlyReportController@saveTeamConfig')->name('save_config');
    });
    Route::get('/team/monthly-report', 'MonthlyReportController@help')
        ->middleware('logged')
        ->name('monthly.report.help');
    //opportunity
    Route::group([
        'prefix' => 'sales/opportunity',
        'as' => 'oppor.',
        'middleware' => 'auth'
    ], function () {
        Route::get('/index', 'OpportunityController@index')->name('index');
        Route::get('/edit/{id?}', 'OpportunityController@edit')
            ->where('id', '[0-9]+')
            ->name('edit');
        Route::post('/check-exists', 'OpportunityController@checkExists')->name('check_exists');
        Route::post('/store', 'OpportunityController@store')->name('store');
        Route::get('/get-tab-content', 'OpportunityController@getTabContent')->name('get_tab_content');
        Route::delete('/delete/{id}', 'OpportunityController@delete')->name('delete');
        Route::post('/add-members', 'OpportunityController@addMembers')->name('add_members');
    });
    // kpi
    Route::group([
        'middleware' => 'logged',
        'as' => 'kpi.',
        'prefix' => 'kpi',
    ], function () {
        Route::get('/', 'KpiController@index')->name('index');
    });

    Route::group([
        'middleware' => 'auth',
        'prefix' => 'project',
    ], function () {
        Route::match(['get', 'post'], 'setting', 'ApiController@setting')->name('setting.general');
        Route::any('/export-member/{id?}', 'ProjectController@exportMember')->name('project.export');
        Route::any('/export-member-by-month/{id?}', 'ProjectController@exportMemberByMonth')->name('project.export-by-month');
        Route::post('/export-project/{id?}', 'ProjectController@exportProject')->name('project.export.project');
        Route::get('/export-production-cost/{id?}', 'ProjectController@productionCostExport')->name('project.export.productionCostExport');
    });

    Route::group([
        'prefix' => 'project',
    ], function () {
        Route::post('/export-issue/{id?}', 'TaskController@exportIssue')->name('project.export.issue');
        Route::post('/export-risk/{id?}', 'RiskController@exportRisk')->name('project.export.risk');
        Route::post('/export-common-risk/{id?}', 'CommonRiskController@export')->name('project.export.commonRisk');
        Route::post('/export-common-issue/{id?}', 'CommonIssueController@export')->name('project.export.commonIssue');
    });

    Route::group([
        'prefix' => 'project-operations',
        'as' => 'operation.',
        'middleware' => 'auth'
    ], function () {
        Route::get('overview', 'OperationController@indexOverview')->name('overview');
        Route::get('members', 'OperationController@indexMember')->name('members');
        Route::get('projects', 'OperationController@indexProjects')->name('projects');
        Route::post('/operation-reports', 'OperationController@getOperationReports')->name('getOperationReport');

        Route::post('/operation-point-update', 'OperationController@getPointUpdateUrl')->name('getPointUpdateUrl');
        Route::post('/operation-project/create', 'OperationController@storeProjectAddition')->name('create');
        Route::post('/delete-operation-project', 'OperationController@deleteProjectAddition')->name('delete_operation');
        Route::post('/delete-operation-production-cost', 'OperationController@deleteOperaionProductionCost')->name('delete_operation-production-cost');
        Route::post('/project-cost-update', 'OperationController@updateProjectCost')->name('update_project_cost');
    });
    Route::group([
        'prefix' => 'operations',
        'as' => 'operation.',
        'middleware' => 'auth'
    ], function () {
        Route::get('/project-future', 'OperationController@getProjectFuture')->name('project-future.get');
        Route::post('/project-future', 'OperationController@postProjectFuture')->name('project-future.post');
    });

    Route::group([
        'prefix' => 'timesheets',
        'as' => 'timesheets.',
        'middleware' => 'logged'
    ], function () {
        Route::get('/', 'TimesheetController@index')->name('index');
        Route::get('/create', 'TimesheetController@create')->name('create');
        Route::post('/store', 'TimesheetController@store')->name('store');
        Route::get('/edit/{timesheet}', 'TimesheetController@edit')->name('edit');
        Route::post('/update/{timesheet}', 'TimesheetController@update')->name('update');
        Route::post('/destroy/{timesheet}', 'TimesheetController@destroy')->name('destroy');

        Route::get('/get-po', 'TimesheetController@getPO')->name('get-po');
        Route::get('/get-line-item', 'TimesheetController@getLineItem')->name('get-line-item');
        Route::get('/reload-period', 'TimesheetController@reloadPeriod')->name('reload-period');
        Route::get('/sync-timesheet', 'TimesheetController@syncTimesheet')->name('sync-timesheet');
    });

    Route::group([
        'middleware' => 'auth'
    ], function () {
        Route::get('purchase-order/crm/list', 'PurchaseOrderController@listView')->name('purchaseOrder.list');
    });
});
