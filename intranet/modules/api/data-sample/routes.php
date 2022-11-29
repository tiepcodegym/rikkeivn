<?php
return [
    'employee' => [
        'label' => 'Employee',
        'api' => [
            'api::contact.search.employee' => 'API search employee',
            'api::contact.skillsheet' => 'API get skillsheet by email',
            'api::employee.total' => 'API get total employee',
            'api::employee.info' => 'API get employee information',
            'api::employee.skills' => 'API skills of all employees',
            'api::employee.onsite-jp' => 'API get employees onsite Japan with periods list',
            'api::employee.onsite-vn' => 'API get employees Viet Nam Division onsite in Viet Nam',
            'api::employee.onsite-in-month' => 'API get list of onsite employees for the month',
            'api::employee.utilization' => "API calculates employee's efforts by day, week, month",
            'api::employee.update-resignation' => "API set nghỉ việc cho nhân viên",
            'api::employee.create' => "API tạo mới nhân viên",
            'api::employee.not-in-project' => "API get danh sách nhân viên không có trong dự án",
            'api::employee.roles' => " API get list of employees with role PM on system",
            'api::employee.get-teams-leader' => " API get list of Team leader of Each Division on system",

        ],
    ],
    'team' => [
        'label' => 'Team',
        'api' => [
            'api::team.lists' => 'API get teams list',
            'api::team.owner-lists' => 'API get owner teams list of an employee',
            'api::team.roles.lists' => 'API get roles list',
            'api::team.employee.total' => 'API get total employee of team',
            'api::team.holidays' => 'API get holidays',
            'api::team.employee.point' => 'API get point employees of team',
        ],
    ],
    'asset' => [
        'label' => 'Asset',
        'api' => [
            'api::asset.info' => 'API get asset information',
            'api::asset.all' => 'API get all assets',
            'api::asset.list' => 'API get assets list',
            'api::asset.assets-of-employee' => 'API assets list of employee',
            'api::asset.request_asset_candidate' => 'API yêu cầu tài sản cho ứng viên',
        ],
    ],
    'project' => [
        'label' => 'Project',
        'api' => [
            'api::project.list' => 'API get projects list',
            'api::project.revenue-list' => 'API get Revenue projects list',
            'api::project.info' => 'API get project information',
            'api::project.billable-effort' => 'API get billable effort',
            'api::project.list-timekeeping' => 'API get timekeeping, key employee follow project',
            'api::project.report-timekeeping-aggregate' => 'API report timekeeping aggregate, key project follow project',
            'api::project.proj-css-result' => 'API get list css by array project id, key project id',
            'api::project.member' => 'API get projects member',
            'api::project.list-in-month' => 'API get list projects in months',
        ],
    ],
    'operation' => [
        'label' => 'operation',
        'api' => [
            'api::operations.operation-reports' => 'API get operation list',
            'api::operations.delete_operation' => 'API Delete Operation Project',
            'api::operations.create_operation' => 'API Create Operation Project',
            'api::operations.project-future' => 'API get Operation project Future',
            'api::operations.project-cost-update' => 'API update cost Operation project',
            'api::operations.get-project-kind' => 'API get project kind',
            'api::operations.operation-reports-team' => 'API get operation list by team',
        ],
    ],
    'hrm' => [
        'label' => 'HRM',
        'api' => [
            'api::hrm.branches.list' => 'API get common branches',
            'api::hrm.branches.bo.list' => 'API get BO teams each Branch',
            'api::hrm.branches.fo.list' => 'API get FO teams each Branch',
            'api::hrm.branches.total.list' => 'API get All teams each Branch',
            'api::hrm.contract.save' => 'API insert/update contract',
            'api::hrm.contract.delete' => 'API delete contract',
            'api::hrm.teaching.store' => 'API register of teaching',

            'api::hrm.bo.branches.employees.list' => 'API Get total BO for each Branches',
            'api::hrm.bo.branches.divisions.employees.list' => 'API get Total BO for each Division',
            'api::hrm.bo.statistical.employee.in-out' => 'API get Ratio In-out',
            'api::hrm.bo.employees.leave-in-month' => 'API get list employees leave in month',
            'api::hrm.bo.employees.birthday-in-month' => 'API get list employees birthday in month',
            'api::hrm.bo.employees.expired-contract-in-month' => 'API get list employees expiration of the contract in month',
            'api::hrm.bo.employees.new.list' => 'API get list new employees in month',

            'api::hrm.fo.overall' => 'API get list Front Office Overall',
            'api::hrm.fo.employees.allocation' => 'API get list employees Allocate',
            'api::hrm.fo.employees.effort.project' => 'API get Effort for each kind of project',
            'api::hrm.fo.employees.effort.role' => 'API get percentage effort for each role',
            'api::hrm.fo.employees.effort.role.day' => 'API get percentage effort for each role (day)',
            'api::hrm.fo.employees.effort.employee' => 'API get percentage effort employee',

            'api::hrm.total.total-employees' => 'API get total employees',
            'api::hrm.total.branches.total-employees' => 'API get total employees in branch',
            'api::hrm.total.divisions.total-employees' => 'API get total employees in division',
            'api::hrm.total.contract-types' => 'API get total type of contract',
            'api::hrm.total.age-genders' => 'API get total ages and genders',
            'api::hrm.total.seniorities' => 'API get total seniorities',
            'api::hrm.total.educations' => 'API get total educations',
            'api::hrm.total.certificates' => 'API get total certificates',
            'api::hrm.total.divisions.total-employees.popup' => 'API get total employees in division popup',
            'api::hrm.total.hrByBranch' => 'API get human resource and onsite by branch',
            'api::hrm.total.total-request' => 'API get list requests',
            'api::hrm.total.total-candidates' => 'API get total candidates',

            'api::hrm.recruitment.reports' => 'API get Recruitment\'s report',
            'api::hrm.recruitment.targets' => 'API get Recruitment\'s targets',
            'api::hrm.recruitment.campaigns' => 'API get Recruitment\'s campaigns',

            'api::hrm.recruitment.candidates.references' => 'API get candidates references',
            'api::hrm.recruitment.candidates' => 'API get candidates',
            'api::hrm.recruitment.candidate-requests' => 'API get candidate request',

            'api::hrm.profile.employees.ids' => 'API Get all IDS of Employees for getting profile purpose',
            'api::hrm.profile.employees' => 'API Get Employee\'s profile',
            'api::hrm.profile.initial' => 'API Set initial Employee\'s profile',
        ],
    ],
    'Resource' => [
        'label' => 'Webvn',
        'api' => [
            'api::resource.resource-data' => 'API get resource data from intranet',
            'api::resource.resource-data-request-approved' => 'API get request data approved from intranet',
            'api::news.data-post-intranet' => 'API get data posts from intranet',
        ],
    ],
    'news' => [
        'label' => 'News',
        'api' => [
            'api::news.list-post' => 'API get list post',
            'api::news.detail-post' => 'API get detail post',
        ],
    ],
    'Timesheets' => [
        'label' => 'Timesheets',
        'api' => [
            'api::timesheets.detail' => 'API get timesheet detail',
        ],
    ],
    'timeKeeping' => [
        'label' => 'Timekeeping',
        'api' => [
            'api::timekeeping.get-time-in-out' => 'API get time time in and out employee timekeeping',
        ],
    ],
    'company' => [
        'label' => 'Company',
        'api' => [
            'api::company.get-company' => 'API get customer companies information',
            'api::customer.get-company' => 'API get list companies',
            'api::contact.get-contact' => 'API get list contact',
        ],
    ],
    'mobileData' => [
        'label' => 'Mobiledata',
        'api' => [
            'api::mobiledata.store' => 'API upload file manage attackment',
        ],
    ],
    'emailqueue' => [
        'label' => 'Email queue',
        'api' => [
            'api::emailqueue.store' => 'API insert email to table email_queue',
        ],
    ],
];
