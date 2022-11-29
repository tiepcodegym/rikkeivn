<?php
//Contact
Route::group([
    'prefix' => 'contact',
    'as' => 'contact.',
    'namespace' => 'Contact'
], function () {
    Route::get('search-employees', 'ContactController@searchEmployees')
        ->name('search.employee');
    Route::get('skillsheet', 'ContactController@getSkillsheet')->name('skillsheet');
});
//Team
Route::group([
    'prefix' => 'team',
    'as' => 'team.',
    'namespace' => 'Team'
], function () {
    Route::get('lists', 'TeamController@getList')
        ->name('lists');
    Route::post('owner-lists', 'TeamController@getOwnerList')
        ->name('owner-lists');
    Route::get('roles', 'TeamController@getRolesList')
        ->name('role.lists');
    Route::get('holidays', 'TeamController@getHolidays')
        ->name('holidays');
    Route::post('total-employees', 'TeamController@getTotalEmployee')
        ->name('employee.total');
    Route::post('get-point-employees', 'TeamController@getPointEmployees')->name('employee.point');
});
// Employee
Route::group([
    'prefix' => 'employee',
    'as' => 'employee.',
    'namespace' => 'Employee'
], function () {
    Route::get('get-total', 'EmployeeController@getTotal')->name('total');
    Route::get('get-info', 'EmployeeController@getInfo')->name('info');
    Route::get('skills', 'EmployeeController@getSkills')->name('skills');
    Route::get('list', 'EmployeeController@getList')->name('list');
    Route::get('get-info-full', 'EmployeeController@getInfoFull')->name('infofull');
    Route::post('get-info-full-list', 'EmployeeController@getInfoFullList')->name('infofull-list');
    Route::get('onsite-jp', 'EmployeeController@listEmployeesOnsiteJapan')->name('onsite-jp');
    Route::post('onsite-vn', 'EmployeeController@listEmployeesOnsiteVietNam')->name('onsite-vn');
    Route::post('onsite-in-month', 'EmployeeController@listEmployeesOnsiteInMonth')->name('onsite-in-month');
    Route::post('utilization', 'EmployeeController@utilization')->name('utilization');
    Route::post('update-resignation', 'EmployeeController@updateResignation')->name('update-resignation');
    Route::post('create', 'EmployeeController@createEmployee')->name('create');
    Route::get('not-in-project', 'EmployeeController@getEmpNotInProject')->name('not-in-project');
    Route::get('roles', 'EmployeeController@getEmployeeRole')->name('roles');
    Route::get('get-teams-leader', 'EmployeeController@getTeamsLeader')->name('get-teams-leader');

});
// Asset
Route::group([
    'prefix' => 'asset',
    'as' => 'asset.',
    'namespace' => 'Asset'
], function () {
    Route::get('get-info', 'AssetController@getInfo')->name('info');
    Route::get('all', 'AssetController@getAll')->name('all');
    Route::get('list', 'AssetController@getList')->name('list');
    Route::get('assets-of-employee', 'AssetController@getAssetsOfEmployee')->name('assets-of-employee');
    Route::post('request-asset-candidate', 'AssetController@requestAssetCandidate')->name('request_asset_candidate');
});

// Project
Route::group([
    'prefix' => 'project',
    'as' => 'project.',
    'namespace' => 'Project'
], function () {
    Route::get('list', 'ProjectController@getList')->name('list');
    Route::post('revenue-list', 'ProjectController@getRevenueList')->name('revenue-list');
    Route::get('info', 'ProjectController@getInfo')->name('info');
    Route::post('billable-effort', 'ProjectController@getBillableEffortByProjectIds')->name('billable-effort');
    Route::get('list-timekeeping', 'ProjectController@getProjTimekeeping')->name('list-timekeeping');
    Route::get('report-timekeeping-aggregate', 'ProjectController@reportProjTimekeeping')->name('report-tk-aggregate');
    Route::get('proj-css-result', 'ProjectController@getProjectCssResultByProjIds')->name('proj-css-result');
    Route::get('member', 'ProjectController@getMember')->name('member');
    Route::get('list-in-month', 'ProjectController@getListInMonth')->name('list-in-month');
});

// Operations
Route::group([
    'prefix' => 'operations',
    'as' => 'operations.',
    'namespace' => 'Operations'
], function () {
    Route::post('project-operations', 'OperationController@getOperationReports')->name('operation-reports');
    Route::post('delete-operation-project', 'OperationController@deleteProjectAddition')->name('delete_operation');
    Route::post('create-operation-project', 'OperationController@createProjectAddition')->name('create_operation');
    Route::post('project-future', 'OperationController@getProjectFuture')->name('project-future');
    Route::post('project-cost-update', 'OperationController@projectCostUpdate')->name('project-cost-update');
    Route::post('get-project-kind', 'OperationController@getProjectKind')->name('get-project-kind');
    Route::post('project-operations-team', 'OperationController@getOperationReportsTeam')->name('operation-reports-team');
});

// HRM API
Route::group([
    'prefix' => 'hrm',
    'as' => 'hrm.',
    'namespace' => 'Hrm'
], function () {
    Route::get('branches', 'HrmCommonController@getBranches')->name('branches.list');
    Route::get('branches/teams-bo', 'HrmBoController@getTeamBo')->name('branches.bo.list');
    Route::get('branches/teams-fo', 'HrmFoController@getTeamFo')->name('branches.fo.list');
    Route::get('branches/teams', 'HrmTotalController@getAllTeams')->name('branches.total.list');
    Route::post('contract/update', 'HrmCommonController@saveContract')->name('contract.save');
    Route::post('contract/delete', 'HrmCommonController@deleteContract')->name('contract.delete');
    Route::post('teachings/create', 'HrmCommonController@createTeaching')->name('teaching.store');

    Route::group([
        'prefix' => 'bo',
        'as' => 'bo.',
    ], function () {
        Route::get('branches/employees', 'HrmBoController@getBoEachBranch')->name('branches.employees.list');
        Route::get('branches/divisions/employees', 'HrmBoController@getBoDivisionEachBranch')->name('branches.divisions.employees.list');
        Route::get('statistical-employees/in-out', 'HrmBoController@getStatisticalEmployeeInOut')->name('statistical.employee.in-out');
        Route::get('employees/leave-in-month', 'HrmBoController@getListLeaveCompanyInMonth')->name('employees.leave-in-month');
        Route::get('employees/birthday-in-month', 'HrmBoController@getListBirthdayInMonth')->name('employees.birthday-in-month');
        Route::post('employees/new-employees-in-month', 'HrmBoController@getNewEmployeesInMonth')->name('employees.new.list');
        Route::get('employees/expired-contract-in-month', 'HrmBoController@getExpiredContractInMonth')->name('employees.expired-contract-in-month');
       
    });

    Route::group([
        'prefix' => 'fo',
        'as' => 'fo.',
    ], function () {
        Route::get('overall', 'HrmFoController@getFoOverall')->name('overall');
        Route::get('employees/allocations', 'HrmFoController@getFoAllocation')->name('employees.allocation');
        Route::get('employees/efforts/projects', 'HrmFoController@getFoEffortProject')->name('employees.effort.project');
        Route::get('employees/efforts/roles', 'HrmFoController@getFoEffortRole')->name('employees.effort.role');
        Route::get('employees/efforts/roles/day', 'HrmFoController@getFoEffortRoleForDay')->name('employees.effort.role.day');
        Route::get('employees/efforts/employee', 'HrmFoController@getFoEffortEmployee')->name('employees.effort.employee');
    });

    Route::group([
        'prefix' => 'total',
        'as' => 'total.',
    ], function () {
        Route::get('/total-employees', 'HrmTotalController@getTotalEmployees')->name('total-employees');
        Route::get('branches/total-employees', 'HrmTotalController@getTotalEmployeesEachBranch')->name('branches.total-employees');
        Route::get('divisions/total-employees', 'HrmTotalController@getTotalEmployeesDivision')->name('divisions.total-employees');
        Route::get('/contract-types', 'HrmTotalController@getContractType')->name('contract-types');
        Route::get('/age-genders', 'HrmTotalController@getAgeGenders')->name('age-genders');
        Route::get('/seniorities', 'HrmTotalController@getSeniorities')->name('seniorities');
        Route::get('/educations', 'HrmTotalController@getEducations')->name('educations');
        Route::get('/certificates', 'HrmTotalController@getCertificates')->name('certificates');
        Route::get('/divisions/total-employees/popup', 'HrmTotalController@getTotalDivisionPopup')->name('divisions.total-employees.popup');
        Route::get('/hrByBranch', 'HrmTotalController@getHRByBranch')->name('hrByBranch');
        Route::post('/requests', 'HrmTotalController@getTotalRequest')->name('total-request');
        Route::post('/total-candidates', 'HrmTotalController@getTotalCandidates')->name('total-candidates');
    });

    Route::group([
        'prefix' => 'recruitment',
        'as' => 'recruitment.',
    ], function () {
        Route::get('/campaigns', 'HrmRecruitmentController@getCampaign')->name('campaigns');
        Route::get('/reports', 'HrmRecruitmentController@getReport')->name('reports');
        Route::get('/targets', 'HrmRecruitmentController@getTarget')->name('targets');
        Route::get('/candidate-references', 'HrmRecruitmentController@getPositionApplyAndCandidateStatus')->name('candidates.references');
        Route::get('/candidates', 'HrmRecruitmentController@getCandidates')->name('candidates');
        Route::get('/candidate-requests', 'HrmRecruitmentController@getCandidateRequests')->name('candidate-requests');
    });

    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.',
    ], function () {
          Route::get('/employees/ids', 'HrmProfileController@getEmployeeIds')->name('employees.ids');
          Route::get('/employees/{type}', 'HrmProfileController@getProfileEmployees')->name('employees');
          Route::get('/initial/{type}', 'HrmProfileController@setProfileInitial')->name('initial');
    });
});

// Resource
Route::group([
    'prefix' => 'resource',
    'as' => 'resource.',
    'namespace' => 'Resource'
], function () {
    // Get recruitment data
    Route::get('data', 'ResourceController@getRecruitmentData')->name('resource-data');
    Route::get('data-request-approved', 'ResourceController@getDataRequestApproved')->name('resource-data-request-approved');
});

Route::group([
    'prefix' => 'news',
    'as' => 'news.',
    'namespace' => 'News'
], function () {
    Route::get('/list-post/{idgte?}', 'PostController@listPost')->name('list-post');
    Route::get('/detail-post/{id}', 'PostController@detailPost')->name('detail-post');
    Route::get('/data-post-intranet', 'PostController@getDataPosts')->name('data-post-intranet');
    Route::post('/categories', 'PostController@getCategories')->name('data-category-intranet');
});

//Timesheet
Route::group([
    'prefix' => 'timesheets',
    'as' => 'timesheets.',
    'namespace' => 'Project'
], function () {
    Route::get('/get-timesheet', 'TimesheetController@getTimesheet')->name('get-timesheet');
});

//Timesheet
Route::group([
    'prefix' => 'timekeeping',
    'as' => 'timekeeping.',
    'namespace' => 'Timekeeping'
], function () {
    Route::get('/get-time-in-out', 'TimekeepingController@getTimeInOut')->name('get-time-in-out');
    Route::post('/update-related-person', 'TimekeepingController@updateRelatedPerson')->name('update-related-person');
});

//Company
Route::group([
    'prefix' => 'company',
    'as' => 'company.',
    'namespace' => 'Company'
], function () {
    Route::get('/', 'CompanyController@getCompany')->name('get-company');
    Route::get('/get-compnay-crm', 'CompanyController@getCompanyCrm')->name('get-company');
});

Route::group([
    'prefix' => 'mobiledata',
    'as' => 'mobiledata.',
    'namespace' => 'Mobiledata'
], function () {
    Route::post('store', 'AdminMobileConfigController@store')->name('store');
});

// Email queue
Route::group([
    'prefix' => 'emailqueue',
    'as' => 'emailqueue.',
    'namespace' => 'Emailqueue'
], function () {
    Route::post('store', 'EmailQueueController@store')->name('store');
    Route::post('storeMe', 'EmailQueueController@storeMe')->name('storeMe');

});

//Customer
Route::group([
    'prefix' => 'customer',
    'as' => 'customer.',
    'namespace' => 'Company'
], function () {
    Route::get('companies', 'CompanyController@getCompanyById')->name('get-company');
    Route::get('contacts', 'CompanyController@getContact')->name('get-contact');
});
