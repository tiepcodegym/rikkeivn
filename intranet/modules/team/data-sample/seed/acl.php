<?php

return [
    'A-2' => [
        'label' => 'Profile',
        'child' => [
            'view.profile.v1' => [
                'label' => 'View profile',
                'routes' => [
                    'team::member.profile.index',
                ],
                'guide' => trans('team::view.acl guide member profile index'),
            ],
            'edit.profile.v1' => [
                'label' => 'Edit / create profile',
                'routes' => [
                    'team::member.profile.save',
                ],
                'guide' => "<ul>
                    <li><b>Sửa profile của nhân viên</b></li>
                    <li>Quyền công ty: Sửa được thông tin tất cả thành viên công ty</li>
                    <li>Quyền team: Có quyền sửa skillsheet, approve skillsheet của thành viên team mình; Có quyền duyệt/từ chối chứng chỉ (không được sửa)</li>
                    <li>Quyền cá nhân hoặc không cấp quyền: Sửa được thông tin cá nhân của mình, trừ 2 tab thông tin chung và thông tin công việc</li>
                    </ul>",
            ],
            'edit.profile.special.roles' => [
                'label' => 'Edit special roles',
                'routes' => [
                    'team::member.profile.edit.roles',
                ],
                'guide' => '<ul>'
                . '<li><b>Sửa vai trò đặt biệt của nhân viên</b></li>'
                . '<li>Chỉ cần có quyền là có thể sửa được</li>'
                . '</ul>',
            ],
            'view.list.member' => [
                'label' => 'View list member',
                'routes' => [
                    'team::team.member.index',
                ],
                'guide' => '<ul>'
                . '<li><b>Xem danh sách của nhân viên.</b></li>'
                . '<li>Quyền công ty: Xem được danh sách tất cả thành viên công ty.</li>'
                . '<li>Quyền team: Xem được danh sách tất cả thành viên trong team và team phụ trách(nếu có)</li>'
                . '<li>Quyền cá nhân: Xem được danh sách tất cả thành viên trong team mà nhân viên đó phụ trách</li>'
                . '<li>Không cấp quyền: Không hiển thị.</li>'
                . '</ul>',
            ],
            'view.full.contact' => [
                'label' => 'View full contact',
                'routes' => [
                    'contact::get.list',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem thêm số điện thoại và ngày sinh của nhân viên</b></li>'
                    . '<li>Chỉ cần có quyền là có thể xem được</li>'
                    . '</ul>',
            ],
            'view.profile.skillsheet' => [
                'label' => 'View skillsheet',
                'routes' => [
                    'team::member.profile.skillsheet',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem skillsheet của nhân viên.</b></li>'
                    . '<li>Quyền công ty: Xem được skillsheet của tất cả thành viên công ty.</li>'
                    . '<li>Quyền team: Xem được skillsheet của thành viên trong team và thành viên trong team phụ trách.</li>'
                    . '<li>Quyền cá nhân hoặc không cấp quyền: Xem được skillsheet của mình.</li>'
                    . '</ul>',
            ],
            'manage.contract' => [
                'label' => 'Quản lý hợp đồng',
                'routes' => [
                    'contract::manage.contract.index',
                    'contract::manage.contract.show',
                    'contract::manage.contract.create',
                    'contract::manage.contract.edit',
                    'contract::manage.contract.save',
                    'contract::manage.contract.import-excel',
                    'contract::manage.contract.download',
                    'contract::manage.contract.export',
                    'contract::manage.contract.histories',
                    'contract::manage.contract.update',
                    'contract::manage.contract.delete',
                    'contract::manage.contract.synchronize',
                    'contract::manage.contract.employee.search.ajax',
                ],
                'guide' => '<ul>'
                    . '<li><b>Quyền quản lý danh sách hợp đồng nhân viên</b></li>'
                    . '<li>Quyền công ty: Quản lý được hợp đồng toàn bộ nhân viên trong công ty.</li>'
                    . '<li>Quyền team hoặc cá nhân: Chỉ quản lý được hợp đồng nhân viên trong phạm vi team mình đang trực thuộc.</li>'
                    . '</ul>',
            ],
            'receive.notify.contract' => [
                'label' => 'Nhận thông báo khi hợp đồng nhân viên sắp hết hạn',
                'routes' => [
                    'contract::manage.contract.show',
                ],
                'guide' => '<ul>'
                    . '<li>Nhân viên khi được gán quyền với phạm vi bất kỳ đều có thể nhận thông báo các hợp đồng sắp hết hạn trong phạm vi chi nhánh nhân viên đó đang trực thuộc</li>'
                    . '</ul>',
            ],
            /*'view.team.member' => [
                'label' => 'View list member',
                'routes' => [
                    'team::team.member.index',
                    'team::team.member.edit',
                    'team::team.member.save',
                ]
            ],
            'edit.information.base' => [
                'label' => 'Edit base information, link facebook, upload CV',
                'routes' => [
                    'team::team.member.edit',
                    'team::team.member.save',
                ]
            ],
            'edit.team.position' => [
                'label' => 'Edit information team, position of member',
                'routes' => [
                    'team::team.member.edit.team.position'
                ]
            ],
            'edit.role' => [
                'label' => 'Edit information role of member',
                'routes' => [
                    'team::team.member.edit.role'
                ]
            ],
            'edit.skill' => [
                'label' => 'Edit skill',
                'routes' => [
                    'team::team.member.edit.skill'
                ]
            ],
            'edit.experience' => [
                'label' => 'Edit experience project',
                'routes' => [
                    'team::team.member.edit.exerience'
                ]
            ],
            'delete.employee.left' => [
                'label' => 'Set employee left work',
                'routes' => [
                ]
            ],
            'add.account' => [
                'label' => 'Register new member',
                'routes' => [
                    'team::team.member.create',

                ]
            ],
            'downloadcv.employee' => [
                'label' => 'Download CV',
                'routes' => [
                    'team::team.member.edit.downloadcv',
                ]
            ],
                        'edit.employee_code.member' => [
                'label' => 'Edit employee code',
                'routes' => [
                    'team::team.member.editEmployeeCode',
                ]
            ], */
            'upload.team.member' => [
                'label' => 'Upload member',
                'routes' => [
                    'team::team.member.get-upload-member',
                    'team::team.member.post-upload-member',
                    'team::team.member.check-uploaded',
                    'team::team.member.get-upload-family-info',
                    'team::team.member.post-upload-family-info',
                ],
                'guide' => '<ul>'
                    . '<li><b>Upload file excel data nhân viên, import vào hệ thống</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>'
            ],
            'export.team.member' => [
                'label' => 'Export member',
                'routes' => [
                    'team::team.member.export_member'
                ],
                'guide' => trans('team::view.acl guide Export member')
            ],
            'export.team.dev.member' => [
                'label' => 'Export Devs',
                'routes' => [
                    'resource::export.devs',
                ],
                'guide' => '<ul>'
                    . '<li>Xuất thông tin developer, QA</li>'
                    . '</ul>'
            ],
            'delete.member' => [
                'label' => 'Delete member',
                'routes' => [
                    'team::team.member.delete',
                ]
            ],
            'staff.statistics' => [
                'label' => 'Thống kê nhân sự',
                'routes' => [
                    'resource::staff.stat.*'
                ]
            ],
            'work.place.management' => [
                'label' => 'Quản lý địa điểm làm việc',
                'routes' => [
                    'manage_time::profile.wpmanagement.index',
                    'manage_time::profile.wpmanagement.import',
                    'manage_time::profile.wpmanagement.export',
                ]
            ],
        ],
    ], // end A-2 profile

    'C-1' => [
        'label' => 'Css',
        'child' => [
            'view.list.css' => [
                'label' => 'View list css',
                'routes' => [
                    'sales::css.list',
                    'sales::css.css-export',
                    'sales::css.export.css'
                ]
            ],
            'css.preview' => [
                'label' => 'CSS preview',
                'routes' => [
                    'sales::css.preview',
                ]
            ],
            'css_result.cancel' => [
                'label' => 'Cancel css result',
                'routes' => [
                    'sales::cancelCssResult',
                ]
            ],
            'view.CSS detail.css' => [
                'label' => 'Xem chi tiết CSS',
                'routes' => [
                    'sales::css.cssDetail',
                    'sales::css.update',
                ]
            ],
            'view.detail make.css' => [
                'label' => 'Xem chi tiết bài làm CSS',
                'routes' => [
                    'sales::css.view',
                    'sales::css.listMake',
                    'sales::css.detail',
                    'sales::css.exportExcel',
                    'sales::css.showAllMake'
                ]
            ],
            'edit.detail.css' => [
                'label' => 'Create and edit css',
                'routes' => [
                    'sales::css.create',
                    'sales::css.update',
                    'sales::css.getRikkerInfo',
                    'sales::css.save',
                    'sales::css.view',
                    'sales::css.detail',
                    'sales::css.cancel',
                ],
                'guide' => trans('team::view.acl guide create and edit css')
            ],
            'approve.detail.css' => [
                'label' => 'Approve css detail' ,
                'routes' => [
                    'sales::approveStatusCss',
                ],
                'guide' => '<ul>'
                    . '<li><b>Quyền approve và feedback css detail</b></li>'
                    . '</ul>'
            ],
            'review.detail.css' => [
                'label' => 'Review css detail' ,
                'routes' => [
                    'sales::reviewStatusCss',
                ],
                'guide' => '<ul>'
                    . '<li><b>Phạm vi toàn công ty: review all css</b></li>'
                    . '<li><b>Phạm vi team: Role đặc biệt PQA phụ trách dự án + PQA allocation review</b></li>'
                    . '</ul>'
            ],
            'submit.detail.css' => [
                'label' => 'Submit css detail' ,
                'routes' => [
                    'sales::insertAnalysisResult',
                ],
                'guide' => '<ul>'
                    . '<li><b>Quyền submit css detail</b></li>'
                    . '<li><b>Role PM với quyền toàn công ty</b></li>'
                    . '</ul>'
            ],
            'view.analyze.css' => [
                'label' => 'Analyze css',
                'routes' => [
                    'sales::css.analyze',
                    'sales::css.filterAnalyze',
                    'sales::css.applyAnalyze',
                    'sales::css.showAnalyzeListProject',
                    'sales::css.getListLessThreeStar',
                    'sales::css.getProposes',
                    'sales::css.getListLessThreeStarByQuestion',
                    'sales::css.getProposesByQuestion',
                ]
            ],
            'sendmail.css' => [
                'label' => 'Send mail',
                'routes' => [
                    'sales::css.sendMailCustomer',
                    'sales::css.saveCssMail'
                ]
            ],
            'delete item.css' => [
                'label' => 'Delete CSS item',
                'routes' => [
                    'sales::css.deleteItem',
                ]
            ]
        ]
    ], //end css

    'C-2' => [
        'label' => 'Check point',
        'child' => [
            'edit.checkpoint' => [
                'label' => 'Create and edit check point',
                'routes' => [
                    'team::checkpoint.create',
                    'team::checkpoint.update',
                    'team::checkpoint.preview',
                    'team::checkpoint.save',
                    'team::checkpoint.setEmp',
                    'sales::css.getRikkerInfo'
                ]
            ],
            'list.checkpoint' => [
                'label' => 'Danh sách check point',
                'routes' => [
                    'team::checkpoint.list',
                ]
            ],
            'detail.checkpoint' => [
                'label' => 'Xem chi tiết check point',
                'routes' => [
                    'team::checkpoint.checkpointdetail',
                ]
            ],
            'made.checkpoint' => [
                'label' => 'Xem danh sách bài làm của check point',
                'routes' => [
                    'team::checkpoint.made',
                ]
            ],
            'make.checkpoint' => [
                'label' => 'Làm check point',
                'routes' => [
                    'team::checkpoint.welcome',
                    'team::checkpoint.make',
                    'team::checkpoint.success',
                    'team::checkpoint.saveResult',
                    'team::checkpoint.sendMail',
                ]
            ],
            'cmt.checkpoint' => [
                'label' => 'Nhận xét checkpoint của nhân viên',
                'routes' => [
                    'team::checkpoint.cmt',
                ]
            ],
            'list.period' => [
                'label' => 'Danh sách kỳ checkpoint',
                'routes' => [
                    'team::checkpoint.period.create',
                    'team::checkpoint.period.save',
                    'team::checkpoint.period.delete',
                    'team::checkpoint.period.list',
                ]
            ],
        ]
    ], //end check point
    'C-3' => [
        'label' => 'Customer',
        'child' => [
            'add.customer' => [
                'label' => 'Add or edit customer',
                'routes' => [
                    'sales::customer.create',
                    'sales::customer.postCreate',
                    'sales::customer.checkExistsCustomer',
                    'sales::customer.getProjectsList',
                ]
            ],
            'edit.customer' => [
                'label' => 'Edit customer',
                'routes' => [
                    'sales::customer.postCreate',
                    'sales::customer.edit',
                    'sales::customer.checkExistsCustomer',
                    'sales::customer.getProjectsList',
                    'sales::customer.merge',
                ]
            ],
            'list.customer' => [
                'label' => 'List customer',
                'routes' => [
                    'sales::customer.list',
                    'sales::customer.merge',
                ]
            ],
            'delete.customer' => [
                'label' => 'Delete customer',
                'routes' => [
                    'sales::customer.delete',
                ]
            ],
            'import.customer' => [
                'label' => 'Import customer',
                'routes' => [
                    'sales::customer.import-excel',
                ]
            ],
        ]
    ],
    'C-4' => [
        'label' => 'Company',
        'child' => [
            'add.company' => [
                'label' => 'Add or edit company',
                'routes' => [
                    'sales::company.create',
                    'sales::company.postCreate',
                    'sales::company.edit',
                    'sales::company.checkExits',
                ]
            ],
            'list.company' => [
                'label' => 'List company',
                'routes' => [
                    'sales::company.list',
                    'sales::company.merge',
                ]
            ],
            'delete.company' => [
                'label' => 'Delete company',
                'routes' => [
                    'sales::company.delete',
                ]
            ],
        ]
    ],
    'C-7' => [
        'label' => 'Sales',
        'child' => [
            'sales.tracking' => [
                'label' => 'Sales tracking',
                'routes' => [
                    'sales::tracking',
                    'sales::tracking.myTasks',
                    'sales::tracking.feedbacks',
                    'sales::tracking.risks',
                    'sales::tracking.saveTasks',
                    'sales::tracking.save.risks',
                ]
            ],
        ],
        'child' => [
            'sales.request.opportunity' => [
                'label' => 'View list Opportunities',
                'routes' => [
                    'sales::req.list.oppor.index'
                ],
                'guide' => '<ul>'
                . '<li><strong>Xem danh sách Opportunity</strong></li>'
                . '<li><storng>Quyền công ty:</strong> xem được toàn bộ Opportunity</li>'
                . '<li><storng>Quyền team:</strong> xem được opportunity có người tạo cùng team</li>'
                . '<li><storng>Quyền cá nhân:</strong> xem được opportunity do mình tạo</li>'
                . '</ul>'
            ],
            'sales.edit.request.opportunity' => [
                'label' => 'Create/Edit Opportunity',
                'routes' => [
                    'sales::req.oppor.*',
                ],
                'guide' => '<ul>'
                . '<li><strong>Quyền công ty:</strong> Thêm, sửa, xóa tất cả Opportunity</li>'
                . '<li><strong>Quyền team:</strong> Thêm, sửa, xóa các Opportunity có người tạo cùng team</li>'
                . '<li><strong>Quyền cá nhân:</strong> Thêm, sửa, xóa các Opportunity của mình tạo</li>'
                . '</ul>'
            ],
            'sale.apply.request.opportunity' => [
                'label' => 'View and comment opportunity',
                'routes' => [
                    'sales::req.apply.oppor.*'
                ],
                'guide' => '<ul>'
                . '<li><strong>Chỉ cần có quyền:</strong> Xem và comment Opportunity</li>'
                . '</ul>'
            ],
        ]
    ],
    'C-5' => [
        'label' => 'Resource request',
        'child' => [
            'edit.resource request' => [
                'label' => 'Create and edit resource request',
                'routes' => [
                    'resource::request.create',
                    'resource::request.edit',
                    'resource::request.postCreate',
                    'resource::request.sendMail',
                ]
            ],
            'list.resource request' => [
                'label' => 'Danh sách resource request',
                'routes' => [
                    'resource::request.list',
                ]
            ],
            'approve.resource request' => [
                'label' => 'Approve request',
                'routes' => [
                    'resource::request.approved',
                    'resource::request.sendMailRecruiter',
                    'resource::request.saveAssignRequest',
                ]
            ],
            'assignee.resource request' => [
                'label' => 'Assignee request',
                'routes' => [
                    'resource::request.assignee',
                    'resource::request.sendMailRecruiter',
                ]
            ],
            'detail.resource request' => [
                'label' => 'Detail request',
                'routes' => [
                    'resource::request.detail',
                    'resource::request.generate',
                    'resource::request.candidateList'
                ]
            ],
            'publish.resource request' => [
                'label' => 'Publish request to webvn',
                'routes' => [
                    'resource::request.postDataRequest',
                    'resource::request.postDataRequestRecruitment',
                ]
            ]
            ,
            'dashboard.resource' => [
                'label' => 'Resource dashboard',
                'routes' => [
                    'resource::dashboard.index',
                ]
            ],
            'utilization.resource' => [
                'label' => 'Resource utilization',
                'routes' => [
                    'resource::dashboard.utilization',
                    'resource::dashboard.viewWeekDetail',
                    'resource::dashboard.ajax',
                ]
            ],
            'approve.change approver request' => [
                'label' => 'Change request approver',
                'routes' => [
                    'resource::request.changeApprover',
                ]
            ],
            'resource.available' => [
                'label' => 'Available resources',
                'routes' => [
                    'resource::available.*'
                ],
                'guide' => '<ul>'
                . '<li><b>Xem danh sách nhân viên Free</b></li>'
                . '<li>Quyền công ty: xêm được tất cả nhân viên</li>'
                . '<li>Quyền team: xem danh sách trong team</li>'
                . '<li>Quyền cá nhân hoặc không có quyền: không được truy cập.</li>'
                . '</ul>'
            ],
            'resource.available.export' => [
                'label' => 'Export employee available',
                'routes' => [
                    'resource::permiss.available.export'
                ],
                'guide' => '<ul>'
                . '<li><b>Export danh sách nhân viên Free</b></li>'
                . '<li>Chỉ cần có quyền: Export được thành viên theo quyền <b>Available resources</b></li>'
                . '</ul>'
            ],
            'channel.resource' => [
                'label' => 'Create channel',
                'routes' => [
                    'resource::channel.create',
                    'resource::channel.edit',
                    'resource::channel.postCreate',
                    'resource::channel.ajaxToggleStatus',
                ]
            ],
            'channel list.resource' => [
                'label' => 'List channel',
                'routes' => [
                    'resource::channel.list',
                ]
            ],
            'channel of request.resource' => [
                'label' => 'Add/edit channel of request',
                'routes' => [
                    'resource::request.addChannelRequest',
                    'resource::request.saveChannel',
                ]
            ],
            'channel delete.resource' => [
                'label' => 'Delete channel',
                'routes' => [
                    'resource::channel.delete',
                ]
            ]
        ]
    ], //end request
    'C-6' => [
        'label' => 'Candidate',
        'child' => [
            'create.candidate' => [
                'label' => 'Create candidate',
                'routes' => [
                    'resource::candidate.create',
                    'resource::candidate.postCreate',
                    'resource::candidate.postTest',
                    'resource::candidate.postInterview',
                    'resource::candidate.postOffer',
                    'resource::candidate.getTeamByRequest',
                    'resource::candidate.getPositionByTeam',
                    'resource::candidate.checkCandidateMail',
                    'resource::candidate.checkExistEmpPropertyValue',
                    'resource::candidate.check_employee_email',
                    'resource::candidate.employee.info',
                ]
            ],
            'edit.candidate' => [
                'label' => 'Edit candidate',
                'routes' => [
                    'resource::candidate.edit',
                    'resource::candidate.postCreate',
                    'resource::candidate.postTest',
                    'resource::candidate.postInterview',
                    'resource::candidate.postOffer',
                    'resource::candidate.getTeamByRequest',
                    'resource::candidate.getPositionByTeam',
                    'resource::candidate.checkCandidateMail',
                    'resource::candidate.checkExistEmpPropertyValue',
                    'resource::candidate.check_employee_email',
                    'resource::candidate.update_status_candidate',
                ]
            ],
            'list.candidate' => [
                'label' => 'Danh sách candidate',
                'routes' => [
                    'resource::candidate.list',
                    'resource::test.history.index',
                    'resource::enroll_addvice.index',
                    'resource::enroll_addvice.update.status',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem list candidate, list enrollment advice</b> - từ page 100 fresher</li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>'
            ],
            'detail.candidate' => [
                'label' => 'Detail candidate',
                'routes' => [
                    'resource::candidate.detail',
                    'resource::candidate.postCreate',
                    'resource::candidate.checkAttachFile',
                    'resource::candidate.downloadAttach',
                    'resource::candidate.pdfSave',
                ]
            ],
            'more_infor.candidate' => [
                'label' => 'View more information',
                'routes' => [
                    'resource::candidate.detail.more_infor'
                ]
            ],
            'sendMailToCandidate.candidate' => [
                'label' => 'Send mail to candidate',
                'routes' => [
                    'resource::candidate.sendMailOffer',
                    'resource::candidate.sendMailThanks',
                ]
            ],
            'sendMailToRecruiter.candidate' => [
                'label' => 'Send mail to recruiter',
                'routes' => [
                    'resource::candidate.sendMailOffer',
                    'resource::candidate.sendMailRecruiter',
                ]
            ],
            'delete.candidate' => [
                'label' => 'Delete candidate',
                'routes' => [
                    'resource::candidate.delete',
                ]
            ],
            'history.candidate' => [
                'label' => 'Candidate history action',
                'routes' => [
                    'resource::candidate.history',
                ]
            ],
            'interested.candidate' => [
               'label' => 'Ứng viên thuộc vùng quan tâm',
                'routes' => [
                    'resource::candidate.interested',
                    'resource::candidate.remove-interested',
                    'resource::candidate.interested.preview-mail',
                    'resource::candidate.interested.send-mail',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem danh sách ứng viên thuộc vùng quan tâm</b></li>'
                    . '<li>Quyền công ty: Xem được tất cả ứng viên thuộc vùng quan tâm</li>'
                    . '<li>Quyền team: Xem được tất cả ứng viên thuộc vùng quan tâm</li>'
                    . '<li>Quyền cá nhân: Chỉ xem được danh sách ứng viên mình quản lý.</li>'
                    . '</ul>',
            ],
            'downloadcv.candidate' => [
                'label' => 'Download CV',
                'routes' => [
                    'resource::candidate.downloadcv',
                ]
            ],
            'view.candidate' => [
                'label' => 'View CV',
                'routes' => [
                    'resource::candidate.viewcv',
                ]
            ],
            'importcv.candidate' => [
                'label' => 'Import CV',
                'routes' => [
                    'resource::candidate.importcv',
                    'resource::candidate.postimportcv',
                ]
            ],
            'checkExist.candidate' => [
                'label' => 'Check Exist',
                'routes' => [
                    'resource::candidate.checkExist',
                    'resource::candidate.postCheckExist',
                ]
            ],
            'candidate.recruit.report' => [
                'label' => 'Recruitment report',
                'routes' => [
                    'resource::recruit.*'
                ]
            ],
            'candidate.candidate.report' => [
                'label' => 'Candidate statistics',
                'routes' => [
                    'resource::candidate.indexCandidate',
                ]
            ],
            'recruit.manage.team.plan' => [
                'label' => 'Team plan manage',
                'routes' => [
                    'resource::plan.team.*'
                ]
            ],
            'candidate.recruit.assign' => [
                'label' => 'Mass assign recruiter',
                'routes' => [
                    'resource::candidate.updateRecruiter',
                ]
            ],
            'search.candidate' => [
                'label' => 'Search advance',
                'routes' => [
                    'resource::candidate.search',
                    'resource::candidate.searchAdvance'
                ]
            ],
            'search.export.candidate' => [
                'label' => 'Export candidate',
                'routes' => [
                    'resource::candidate.export_search'
                ],
                'guide' => '<ul>'
                    . '<li><b>Xuất danh sách tìm kiếm candidate</b></li>'
                    . '<li>chỉ cần có quyền, lọc theo quyền "Search advance"</li>'
                    . '</ul>'
            ],
            'hr.weekly.report' => [
                'label' => 'Hr weekly report',
                'routes' => [
                    'resource::hr_wr.index',
                    'resource::hr_wr.save_note'
                ]
            ],

            'follow.candidate' => [
                'label' => 'Theo dõi ứng viên',
                'routes' => [
                    'resource::candidate.follow',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem danh sách ứng viên theo dõi</b></li>'
                    . '<li>Quyền công ty: Xem được danh sách tất cả ứng viên</li>'
                    . '<li>Quyền team: Xem được danh sách tất cả ứng viên</li>'
                    . '<li>Quyền cá nhân: Chỉ xem được danh sách ứng viên do mình quản lý.</li>'
                    . '</ul>',
            ],
            'candidate.send_email_marketing' => [
                'label' => 'Send email marketing',
                'routes' => [
                    'recruitment::email.*',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem danh sách và gửi email marketing đến các ứng viên</b></li>'
                    . '<li>Quyền công ty: xem và gửi email được cho tất cả ứng viên</li>'
                    . '<li>Quyền team: xem và gửi email được cho tất cả ứng viên cùng team, hoặc có người tuyển dụng, người tạo, người giới thiệu, người phỏng vấn là người đang dùng</li>'
                    . '<li>Quyền cá nhân: xem và gửi email được cho tất cả ứng viên có người tuyển dụng, người tạo, người giới thiệu, người phỏng vấn là người đang dùng</li>'
                . '</ul>'
            ],
            'recruitment.monthly.report' => [
                'label' => 'View monthly recruitment report',
                'routes' => [
                    'resource::monthly_report.recruit.index',
                    'resource::monthly_report.recruit.export',
                    'resource::monthly_report.channel.changeColor',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem danh sách báo cáo tuyển dụng hàng tháng</b></li>'
                    . '<li>Chỉ cần có quyền là có thể xem được</li>'
                    . '</ul>'
            ],
        ]
    ], //end candidate
    'education-1' => [
        'label' => 'Education Manager',
        'child' => [
            'education.managers.list' => [
                'label' => 'Education List',
                'routes' => [
                    'education::education.list'
                ]
            ],
            'education.managers.create' => [
                'label' => 'Education Create',
                'routes' => [
                    'education::education.new',
                    'education::education.getFormCalendar',
                    'education::education.getEmpCodeById',
                    'education::education.MaxCourseCode',
                    'education::education.MaxClassCode',
                    'education::education.addCourse',
                ]
            ],
            'education.managers.detail' => [
                'label' => 'Education Detail',
                'routes' => [
                    'education::education.detail',
                    'education::education.detailv2',
                    'education::education.getFormCalendar',
                    'education::education.copyCourse',
                    'education::education.updateCourse',
                    'education::education.checkEmailTeacher',
                    'education::education.updateCourseInfo',
                    'education::education.MaxCourseCode',
                    'education::education.MaxClassCode',
                    'education::education.getEmpCodeById',
                    'education::education.export',
                    'education::education.export-list',
                    'education::education.export-result',
                    'education::education.ajaxSearchEmployeeEmail',
                    'education::education.searchEmployeeAjaxEmailList',
                    'education::education.searchEmployeeAjaxNameList',
                    'education::education.searchEmployeeAjaxNameCodeList',
                    'education::education.searchHrAjaxList',
                ]
            ],
            'education-profile.managers.detail' => [
                'label' => 'Education Detail Profiles',
                'routes' => [
                    'education::education-profile.detail',
                    'education::education-profile.getFormCalendar',
                    'education::education-profile.copyCourse',
                    'education::education-profile.updateCourse',
                    'education::education-profile.checkEmailTeacher',
                    'education::education-profile.updateCourseInfo',
                    'education::education-profile.MaxCourseCode',
                    'education::education-profile.MaxClassCode',
                    'education::education-profile.getEmpCodeById',
                    'education::education-profile.registerShift'
                ]
            ],
            'education.managers.profile.list' => [
                'label' => 'My Education List',
                'routes' => [
                    'education::profile.profileList',
                    'education::education-profile.detail',
                    'education::education-profile.register',
                    'education::education-profile.delete',
                    'education::education-profile.sendFeedback',
                    'education::education-profile.addDocumentFromTeacher'
                ]
            ],
            'education.hr.certificates' => [
                'label' => 'Danh sách chứng chỉ',
                'label_en' => 'List Certificate',
                'label_ja' => 'リスト証明書',
                'routes' => [
                    'education::education.certificates.*'
                ]
            ],
            'education.manager.employees' => [
                'label' => 'Thống kê đào tạo',
                'label_jp' => 'Thống kê đào tạo',
                'label_en' => 'Thống kê đào tạo',
                'routes' => [
                    'education::education.manager.employee.*',
                ]
            ],
            'education.ot.list' => [
                'label' => 'Thống kê OT',
                'label_en' => 'Statistic OT',
                'label_ja' => 'Thống kê OT',
                'routes' => [
                    'education::education.ot.*',
                ]
            ]
        ]
    ], //end T-1
    'P-2' => [
        'label' => 'Project',
        'child' => [
            'project.create' => [
                'label' => 'Create project',
                'routes' => [
                    'project::project.create',
                ]
            ],
            'project.edit' => [
                'label' => 'Edit project',
                'routes' => [
                    'project::project.edit',
                ]
            ],
            'project.delete' => [
                'label' => 'Delete project',
                'routes' => [
                    'project::project.delete',
                ]
            ],
            'project.edit.workorder' => [
                'label' => 'Edit project workorder',
                'routes' => [
                    'project::project.add_critical_dependencies',
                    'project::project.add_assumption_constrain',
                    'project::project.add_risk',
                    'project::project.add_stage_and_milestone',
                    'project::project.add_training',
                    'project::project.add_external_interface',
                    'project::project.add_tool_and_infrastructure',
                    'project::project.add_communication',
                    'project::wo.editRisk',
                    'project::wo.saveRisk',
                    'project::project.add_devices_expenses',
                ]
            ],
            'project.dashboard.view' => [
                'label' => 'View Project Dashboard',
                'routes' => [
                    'project::dashboard',
                ]
            ],
            'project.baselineAll.view' => [
                'label' => 'View Baseline All',
                'routes' => [
                    'project::baseline.all',
                ]
            ],
            'project.point.edit' => [
                'label' => 'Edit project point',
                'routes' => [
                    'project::point.edit',
                    'project::point.save',
                ]
            ],
            'project.task.edit' => [
                'label' => 'Edit project task',
                'routes' => [
                    'project::task.save',
                ]
            ],
            'project.task.approve.edit' => [
                'label' => 'Workorder approve',
                'routes' => [
                    'project-access::task.approve.save',
                ]
            ],
            'project.task.approve.review' => [
                'label' => 'Workorder review',
                'routes' => [
                    'project-access::task.approve.review.save',
                ]
            ],
            'project.task.approve.chagnge.reviewer' => [
                'label' => 'Workorder change review',
                'routes' => [
                    'project-access::task.approve.chagnge.reviewer',
                ]
            ],
            'project.task.approve.chagnge.approver' => [
                'label' => 'Workorder change approver',
                'routes' => [
                    'project-access::task.approve.chagnge.approver',
                ]
            ],
            'project.reward.view' => [
                'label' => 'View project reward',
                'routes' => [
                    'project::reward',
                    'project::report.reward.export'
                ]
            ],
            'project.reward.submit' => [
                'label' => 'Submit project reward',
                'routes' => [
                    'project::reward.submit',
                ]
            ],
            'project.reward.confirm' => [
                'label' => 'Verify project reward',
                'routes' => [
                    'project::reward.confirm',
                ]
            ],
            'project.reward.approve' => [
                'label' => 'Confirm project reward',
                'routes' => [
                    'project::reward.approve',
                ]
            ],
            'project.reward.base.actual.edit' => [
                'label' => 'Edit project reward base actual',
                'routes' => [
                    'project::reward.base.actual.edit',
                ],
                'guide' => '<ul>'
                . '<li><b>Sửa các thông số của reward base actual</b>'
                . '<br/>Các chỉ số có thể sửa: budget reward, số bug</li>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'project.reward.budget.view' => [
                'label' => 'View project reward budget',
                'routes' => [
                    'project::reward.budget.view',
                ]
            ],
            'project.reward.budget.update' => [
                'label' => 'Update project reward budget',
                'routes' => [
                    'project::reward.budget.update',
                ]
            ],
            'project.reward.update.bonus.money' => [
                'label' => 'Update pay bonus money',
                'routes' => [
                    'project::reward.update.bonusMoney',
                ]
            ],
            'task.general.view' => [
                'label' => 'View task general',
                'routes' => [
                    'project::task.general.view',
                ]
            ],
            'task.general.edit' => [
                'label' => 'Edit task general',
                'routes' => [
                    'project::task.general.edit',
                ]
            ],
            'project.reward.actual.delete' => [
                'label' => 'Delete reward actual',
                'routes' => [
                    'project::reward.actual.delete',
                ]
            ],
            'project.css.reward' => [
                'label' => 'Reward for high CSS',
                'routes' => [
                    'project::css.reward.flag',
                ]
            ],
            'project.statistic.dashboard' => [
                'label' => 'Project dashboard statistic',
                'routes' => [
                    'project::statistic.dashboard',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem thống kê các chỉ số của project: loc, bug</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'project.setting.general' => [
                'label' => 'Project setting',
                'routes' => [
                    'project::setting.general',
                ],
                'guide' => '<ul>'
                . '<li><b>Setting project: ngày baselinedate</b></li>'
                . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'project.operation_overview.report' => [
                'label' => 'Project Operation Overview',
                'routes' => [
                    'project::operation.overview',
                    'project::operation.getOperationReport',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting Operation Overview</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'project.operation_member.report' => [
                'label' => 'Project Operation Member Report',
                'routes' => [
                    'project::operation.members',
                    'project::operation.getOperationReport',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting Operation Member Report</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'project.operation_project.report' => [
                'label' => 'Project Operation Projects Report',
                'routes' => [
                    'project::operation.projects',
                    'project::operation.getOperationReport',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting Operation Projects Report</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'purchaseOrder.list' => [
                'label' => 'Show list purchase order',
                'routes' => [
                    'project::purchaseOrder.list'
                ],
                'guide' => '<ul>'
                    . '<li>Quyền công ty: thấy được tất cả hợp đồng</li>'
                    . '<li>Quyền team: thấy được các hợp đồng của team có quyền</li>'
                    . '<li>Quyền cá nhân: ko có quyền</li>'
                    . '</ul>',
            ],
            'generate.project.operation.report' => [
                'label' => 'Generate Project Operation Report',
                'routes' => [
                    'project::operation.getOperationReport',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'update-point.project.operation.report' => [
                'label' => 'Update Point Project Operation Members Report',
                'routes' => [
                    'project::operation.getPointUpdateUrl',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'update-cost.project.operation.report' => [
                'label' => 'Update Cost Project Operation Report',
                'routes' => [
                    'project::operation.update_project_cost',
                    'project::operation.project-future.get',
                    'project::operation.project-future.post',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'create.project.operation.report' => [
                'label' => '(Project Operation) Create Upcoming Project',
                'routes' => [
                    'project::operation.create',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'delete.project.operation.report' => [
                'label' => '(Project Operation) Delete Upcoming Project',
                'routes' => [
                    'project::operation.delete_operation',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'delete-production-cost.project.operation.report' => [
                'label' => '(Project Operation) Delete Project Production Cost',
                'routes' => [
                    'project::operation.delete_operation-production-cost',
                ],
                'guide' => '<ul>'
                    . '<li><b>Setting project Operation</b></li>'
                    . '<li>Có quyền: chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'project.export.member' => [
                'label' => 'Project export member',
                'routes' => [
                    'project::project.export',
                    'project::project.export-by-month',
                ],
            ],
            'project.export.project' => [
                'label' => 'Export project ',
                'routes' => [
                    'project::project.export.project',
                    'project::project.export-by-month',
                ],
                'guide' => '<ul>'
                    . '<li><b>Export các dự án đang on-going tính đến thời điểm hiện tại</b></li>'
                    . '</ul>',
            ],
            'project.edit-approved-production-cost-detail' => [
                'label' => 'Xem và sửa Production Cost price detail',
                'routes' => [
                    'project::project.edit-approved-production-cost-detail',
                ],
                'guide' => '<ul>'
                    . '<li>Quyền công ty: thấy và sửa được tất cả dự án</li>'
                    . '<li>Quyền team: thấy và sửa đc các team có quyền</li>'
                    . '<li>Quyền cá nhân: ko có quyền</li>'
                    . '</ul>',
            ],
            'project.approved-production-cost' => [
                'label' => 'Duyệt Production Cost',
                'routes' => [
                    'project::project.approved-production-cost',
                ],
                'guide' => '<ul>'
                    . '<li>Phê duyệt chi phí sản xuất</li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
        ]
    ],

    'P-3' => [
        'label' => 'Monthly Evaluation',
        'child' => [
            'project.me.create_edit' => [
                'label' => 'Create/Edit monthly evaluation',
                'routes' => [
                    'project::project.eval.index',
                    'project::project.eval.get_project_and_members',
                    'project::project.eval.update',
                    'project::project.eval.leader_update',
                    'project::project.eval.add_attr_point',
                    'project::project.eval.change_value',
                    'project::project.eval.add_comment',
                    'project::project.eval.load_attr_comments',
                    'project::project.eval.get_point_attr_time',
                    'project::project.eval.load_project_months',
                    'project::project.eval.load_team_project',
                    'project::project.eval.update_avg_point',
                    'me::proj.*',
                    'me::save_point',
                    'me::comment.*',
                ],
                'guide' => '<ul>'
                . '<li><b>Tạo và chỉnh sửa ME cho dự án</b></li>'
                . '<li><b>Quyền công ty: </b> Tạo và sửa ME cho tất cả dự án</li>'
                . '<li><b>Quyền team: </b> Tạo và sửa ME cho các dự án trong team</li>'
                . '<li><b>Quyền cá nhân: </b> Tạo và sửa ME cho các dự án mình làm PM/Sub-PM</li>'
                . '</ul>',
            ],
            'project.me.create_edit.team' => [
                'label' => 'Create/Edit Team evaluation',
                'routes' => [
                    'project::team.eval.*',
                    'project::project.eval.update',
                    'project::project.eval.add_attr_point',
                    'project::project.eval.change_value',
                    'project::project.eval.add_comment',
                    'project::project.eval.load_attr_comments',
                    'project::project.eval.get_point_attr_time',
                    'project::project.eval.update_avg_point',
                    'me::team.*',
                    'me::save_point',
                    'me::comment.*',
                ],
                'guide' => '<ul>'
                . '<li><b>Tạo và chỉnh sửa ME cho Division:</b> chức năng tạo ME cho role PQA trong dự án</li>'
                . '<li><b>Quyền công ty: </b> Tạo và sửa ME cho tất cả Division</li>'
                . '<li><b>Quyền team và cá nhân: </b> Tạo và sửa ME cho team mình</li>'
                . '</ul>',
            ],
            'project.me.review' => [
                'label' => 'Review monthly evaluation',
                'routes' => [
                    'project::project.eval.*',
                    'me::review.*',
                    'me::comment.*',
                    'me::view.member.*'
                ],
                'guide' => '<ul>'
                . '<li><b>Review ME</b></li>'
                . '<li><b>Quyền công ty:</b> Review được tất cả</li>'
                . '<li><b>Quyền team và cá nhân: </b> Review các ME dự án mình làm Leader/Sub-Leader và ME team của nhân viên team mình</li>'
                . '</ul>',
            ],
            'project.me_activity.view' => [
                'label' => 'View member activities',
                'routes' => [
                    'project::me_activity.view'
                ],
                'guide' => '<ul>'
                . '<li><b>Xem hoạt động ME của nhân viên</b></li>'
                . '<li><b>Quyền công ty: </b> Xem được tất cả hoạt động ME đã điền của nhân viên trong công ty</li>'
                . '<li><b>Quyền team: </b> Xem được hoạt động ME đã điền của nhân viên trong team và nhân viên trong dự án cùng team</li>'
                . '<li><b>Quyền cá nhân: </b> Xem được hoạt động ME đã điền của nhân viên cùng dự án</li>'
                . '</ul>',
            ],
            /*'timesheet.eval.upload' => [
                'label' => 'Upload bảng chấm công',
                'routes' => [
                    'project::timesheet.eval.get_upload',
                    'project::timesheet.eval.post_upload'
                ]
            ],*/
            'project.me.attributes' => [
                'label' => 'Manage attributes',
                'routes' => [
                    'project::eval.attr.*'
                ],
                'guide' => '<ul><li><b>Quản lý tiêu chí ME: </b> Chỉ cần có quyền có thể thêm/sửa/xóa</li></ul>',
            ],
            'project.me.view_evaluated' => [
                'label' => 'View evaluated',
                'routes' => [
                    'project::me.view.*'
                ],
                'guide' => '<ul>'
                . '<li><b>Xem các nhân viên đã được đánh giá ME</b></li>'
                . '<li><b>Quyền công ty: </b> Xem được tất cả nhân viên đã được đánh giá</li>'
                . '<li><b>Quyền team: </b> Xem được tất cả nhân viên đã được đánh giá trong các dự án cùng team</li>'
                . '<li><b>Quyền cá nhân: </b> Không có quyền</li>'
                . '</ul>',
            ],
            'project.me.delete' => [
                'label' => 'Delete ME',
                'routes' => [
                    'project::me.delete_item',
                    'me::admin.delete_item',
                ],
                'guide' => '<ul><li>Chức năng dành cho admin, xóa ME ở trang review</li></ul>',
            ],
            'project.me.reward.submit' => [
                'label' => 'OSDC reward submit',
                'routes' => [
                    'project::me.reward.export_data',
                    'project::me.reward.edit',
                    'project::me.reward.update_comment',
                    'project::me.reward.submit',
                    'project::me.reward.delete_item',
                    'project::me.reward.total_reward',
                    'project::me.reward.import_excel',
                    'project::me.reward.download_excel',
                    'project::me.reward.downloadFormatFile',

                ],
                'guide' => '<ul>'
                . '<li><b>Xem và submit danh sách thưởng OSDC</b></li>'
                . '<li><b>Quyền công ty: </b> Xem và submit được tất cả ME của nhân viên trong công ty</li>'
                . '<li><b>Quyền team và cá nhân: </b> Xem và submit được tất cả ME của nhân viên trong team</li>'
                . '</ul>',
            ],
            'project.me.reward.approve' => [
                'label' => 'OSDC reward review',
                'routes' => [
                    'project::me.reward.export_data',
                    'project::me.reward.review',
                    'project::me.reward.approve',
                    'project::me.reward.total_reward',
                ],
                'guide' => '<ul>'
                . '<li><b>Duyệt thưởng ME</b> để quyền công ty vì chỉ có COO duyệt thưởng</li>'
                . '<li><b>Quyền công ty: </b> Duyệt thưởng tất cả ME của nhân viên trong công ty</li>'
                . '</ul>'
            ],
            'project.me.reward.udpate_paid' => [
                'label' => 'Update paid status',
                'routes' => [
                    'project::me.reward.update_paid'
                ],
                'guide' => '<ul>'
                . '<li><b>Update trạng thái đã trả thưởng hay chưa:</b> Chỉ cần có quyền là update được</li>'
                . '</ul>'
            ],
            'project.me.config_data' => [
                'label' => 'Config data',
                'routes' => [
                    'project::me.config_data',
                    'project::me.config_data.save'
                ],
                'guide' => '<ul>'
                . '<li><b>Điều chỉnh mức thưởng ME: </b> Chỉ cần có quyền</li>'
                . '</ul>'
            ],
        ]
    ],

    'G-1' => [
        'label' => 'Test',
        'child' => [
            'test.manage' => [
                'label' => 'Tests manage',
                'routes' => [
                    'test::admin.test.*',
                ]
            ],
            'test.type.manage' => [
                'label' => 'Test type manage',
                'routes' => [
                    'test::admin.type.*'
                ]
            ],
            'test.manage.candidate' => [
                'label' => 'Candidate manage',
                'routes' => [
                    'test::candidate.admin.*'
                ]
            ],
            'test.manage.exam' => [
                'label' => 'Quản lý danh sách làm bài thi',
                'routes' => [
                    'test::test.exam_list'
                ]
            ]
        ]
    ],

    'S-1' => [
        'label' => 'Setting',
        'child' => [
            'edit.setting.team' => [
                'label' => 'Setting team / role',
                'routes' => [
                    'team::setting.team.*',
                    'team::setting.role.*',
                ]
            ],
            'edit.setting.menu' => [
                'label' => 'Setting menu',
                'routes' => [
                    'core::setting.menu.*',
                ]
            ],
            'edit.setting.acl' => [
                'label' => 'Setting acl',
                'routes' => [
                    'team::setting.acl.*',
                ]
            ],
            'edit.setting.system.data' => [
                'label' => 'Setting system data',
                'routes' => [
                    'core::setting.system.data.*',
                ]
            ],
            'view.db_log' => [
                'label' => 'View database logs',
                'routes' => [
                    'core::setting.system.db_logs',
                ]
            ],
            'view.email-queues' => [
                'label' => 'View email logs',
                'routes' => [
                    'core::email-queues',
                ]
            ],
            'edit.setting.api_token' => [
                'label' => 'Api access token setting',
                'routes' => [
                    'api-web::setting.tokens.*',
                ],
            ],
        ]
    ], //end S-1 setting

    'E-1' => [
        'label' => 'Education',
        'child' => [
            'education.request.team.hr' => [
                'label' => 'CRUD education request for Hr',
                'routes' => [
                    'education::education.request.hr.list',
                    'education::education.request.hr.create',
                    'education::education.request.hr.store',
                    'education::education.request.hr.edit',
                    'education::education.request.hr.update',
                    'education::education.request.hr.export',
                    'education::education.request.ajax-tag-list',
                    'education::education.request.ajax-title-list',
                    'education::education.request.ajax-person-assigned-list',
                    'education::education.request.ajax-course-list',
                ]
            ],
            'education.request.team.dlead' => [
                'label' => 'CRUD education request for Team Leader',
                'routes' => [
                    'education::education.request.list',
                    'education::education.request.create',
                    'education::education.request.store',
                    'education::education.request.edit',
                    'education::education.request.update',
                    'education::education.request.ajax-tag-list',
                    'education::education.request.ajax-title-list',
                    'education::education.request.ajax-person-assigned-list',
                    'education::education.request.ajax-course-list',
                ]
            ],
        ]
    ], //end E-1

    'EVENT-1' => [
        'label' => 'Event',
        'child' => [
            'event.create.send.mail' => [
                'label' => 'Send email event birthday company',
                'routes' => [
                    'event::brithday.*',
                ]
            ],
            /*'event.send.mail.employees' => [
                'label' => 'Send email employees',
                'routes' => [
                    'event::send.email.employees.*',
                ]
            ],*/
            'event.send.sabbatical.mail' => [
                'label' => 'Gửi mail thông báo ngày nghỉ phép',
                'routes' => [
                    'event::sabb.get_upload_file',
                    'event::sabb.post_upload_file'
                ]
            ],
            'event.send.mail.to.male' => [
                'label' => 'Gửi email cho nam',
                'routes' => [
                    'event::send.email.employees.to.male',
                    'event::send.email.employees.to.male.post'
                ]
            ],
            'event.send.mail.total.timekeeping' => [
                'label' => 'Gửi email tổng hợp chấm công',
                'routes' => [
                    'event::send.email.employees.total.timekeeping',
                    'event::send.email.employees.total.timekeeping.post'
                ],
                'guide' => '<ul>'
                    . '<li><b>Gửi mail tổng hợp chấm công, gửi mail phân tích'
                    . ' thời gian đi muộn từ bảng chấm công chi tiết</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'event.send.mail.tax' => [
                'label' => 'Gửi email thông tin thuế',
                'routes' => [
                    'event::send.email.employees.tax',
                    'event::send.email.employees.tax.post',
                    'event::send.email.employees.tax',
                    'event::send.email.employees.post.tax',
                    'event::send.email.employees.show.tax',
                    'event::send.email.employees.send_mail.tax',
                    'event::send.email.employees.delete_temp.tax',
                    'event::send.email.employees.tax.list_files',
                    'event::send.email.employees.tax.mail_detail',
                ]
            ],
            'event.send.mail.fines' => [
                'label' => 'Gửi email tiền phạt nội quy',
                'routes' => [
                    'event::send.email.employees.fines',
                    'event::send.email.employees.fines.post'
                ]
            ],
            'event.send.notification' => [
                'label' => 'Email noti',
                'routes' => [
                    'emailnoti::email.notification.*'
                ]
            ],
            'event.send.mail.bonuses.tet' => [
                'label' => 'Gửi email thưởng tết',
                'routes' => [
                    'event::send.email.employees.tet.bonuses',
                    'event::send.email.employees.tet.bonuses.post'
                ]
            ],
            'event.send.mail.salary' => [
                'label' => 'Gửi email thông tin lương',
                'routes' => [
                    'event::send.email.employees.salary',
                    'event::send.email.employees.post.salary',
                    'event::send.email.employees.show.salary',
                    'event::send.email.employees.send_mail.salary',
                    'event::send.email.employees.delete_temp.salary',
                    'event::send.email.employees.salary.list_files',
                    'event::send.email.employees.salary.mail_detail',
                    'event::send.email.employees.salary.send_pass',
                    'event::send.email.employees.salary.send_exists_pass',
                ]
            ],
            'event.send.mail.compose' => [
                'label' => 'Email compose',
                'routes' => [
                    'event::send.email.employees.compose',
                ],
                'guide' => '<ul>'
                . '<li><b>Soạn thư và gửi mail tới bất kỳ địa chỉ nào</b></li>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'event.eventday.company' => [
                'label' => 'Gửi mail sự kiên tới khách hàng',
                'routes' => [
                    'event::eventday.create',
                    'event::eventday.company.list',
                    'event::eventday.send.email',
                    'event::eventday.export',
                    'event::eventday.customer.create',
                    'event::eventday.customer.insert',
                    'event::eventday.customer.edit',
                    'event::eventday.customer.update',
                    'event::eventday.customer.delete',
                ],
                'guide' => '<ul>'
                . '<li><b>Soạn thư và gửi mail mời tham gia sự kiện tới khách hàng</b></li>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'event.event_birthday.company' => [
                'label' => 'Gửi mail sự kiện sinh nhật',
                'routes' => [
                    'event::brithday.create',
                    'event::brithday.company.list',
                    'event::brithday.send.email',
                    'event::brithday.company.email_cust.list',
                    'event::brithday.download_template',
                ],
                'guide' => '<ul>'
                . '<li><b>Soạn thư và gửi mail mời tham gia sự kiện sinh nhật</b></li>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'event.send.mail.forgot-turn-off' => [
                'label' => 'Gửi mail nhắc nhở không tắt máy',
                'routes' => [
                    'event::send.email.employees.turnoff.post',
                    'event::send.email.employees.turnoff',
                ],
                'guide' => '<ul>'
                    . '<li><b>Soạn thư và gửi mail nhắc nhở tới những nhân viên để máy tính qua đêm mà không tắt máy</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
        ]
    ], //end S-1 setting

    'slide-show-1' => [
        'label' => 'Slide Show',
        'child' => [
            'slide-show.setting' => [
                'label' => 'Slide Show Setting',
                'routes' => [
                    'slide_show::setting',
                ]
            ],
            'slide-show.list' => [
                'label' => 'Slide Show List',
                'routes' => [
                    'slide_show::list-slider',
                ]
            ],
            'slide-show.birthday' => [
                'label' => 'Slide Show Birthday Content',
                'routes' => [
                    'slide_show::admin.slide.birthday.*'
                ]
            ],
        ]
    ],

    'MN-1' => [
        'label' => 'Magazine',
        'child' => [
            'magazine.manage' => [
                'label' => 'Magazine manage',
                'routes' => [
                    'magazine::*',
                ]
            ],
        ]
    ],
    'NEWS-1' => [
        'label' => 'News',
        'child' => [
            'news.manage.category' => [
                'label' => 'Manage category',
                'routes' => [
                    'news::manage.category.*',
                ]
            ],
            'news.manage.post' => [
                'label' => 'Manage post',
                'routes' => [
                    'news::manage.post.*',
                ]
            ],
            'news.manage.send.email' => [
                'label' => 'Send email',
                'routes' => [
                    'news::manage.email.send.*',
                ]
            ],
            'news.manage.comment' => [
                'label' => 'Manage comment',
                'routes' => [
                    'news::manage.comment.*',
                    'news::post.approveComment',
                ]
            ],
            'news.manage.opinion' => [
                'label' => "Manage employee's opinions",
                'routes' => [
                    'news::opinions.index',
                    'news::opinions.edit',
                    'news::opinions.update',
                    'news::opinions.delete',
                ]
            ],
            'news.manage.poster' => [
                'label' => "Manage Poster",
                'routes' => [
                    'news::posters.*',
                ]
            ],
            'news.manage.featured_article' => [
                'label' => "Manage featured article",
                'routes' => [
                    'news::manage.featured_article.*',
                ]
            ],
        ]
    ],
    'Pro-12' => [
        'label' => 'Programming Languages',
        'child' => [
            'list.programminglanguages12' => [
                'label' => 'List programming Languages',
                'routes' => [
                    'resource::programminglanguages.list'
                ]
            ],
            'edit.programminglanguages12' => [
                'label' => 'Edit programming languages',
                'routes' => [
                    'resource::programminglanguages.create',
                    'resource::programminglanguages.edit',
                    'resource::programminglanguages.postCreate',
                    'resource::programminglanguages.delete',
                    'resource::programminglanguages.ajaxDelete',
                ]
            ]
        ]
    ],
    'Lang-12' => [
        'label' => 'Languages',
        'child' => [
            'list.languages12' => [
                'label' => 'Languages list',
                'routes' => [
                    'resource::languages.list'
                ]
            ],
            'edit.languages12' => [
                'label' => 'Languages edit',
                'routes' => [
                    'resource::languages.edit',
                    'resource::languages.postCreate',
                    'resource::languages.create',
                ]
            ],
            'create.languagelevel' => [
                'label' => 'Cretate language level',
                'routes' => [
                    'resource::languagelevel.postCreate',
                    'resource::languagelevel.create',
                ]
            ],
            'edit.languagelevel' => [
                'label' => 'Edit language level',
                'routes' => [
                    'resource::languagelevel.postCreate',
                    'resource::languagelevel.edit',
                ]
            ],
            'list.languagelevel' => [
                'label' => 'List language level',
                'routes' => [
                    'resource::languagelevel.list',
                ]
            ],
            'delete.languagelevel' => [
                'label' => 'Delete language level',
                'routes' => [
                    'resource::languagelevel.delete',
                    'resource::languagelevel.ajaxDelete',
                ]
            ]
        ]
    ],
    'education-13' => [
        'label' => 'Setting Education',
        'child' => [
            'crud.education12' => [
                'label' => 'Manager setting education',
                'routes' => [
                    'education::education.settings.types.index',
                    'education::education.settings.types.update',
                    'education::education.settings.types.store',
                    'education::education.settings.types.delete',
                    'education::education.settings.types.ajaxDelete',
                    'education::education.settings.types.create',
                    'education::education.settings.types.show',
                    'education::education.settings.types.show_detail',
                    'education::education.settings.types.check-exit-code',
                ]
            ],
            'template.mail.list.education12' => [
                'label' => 'Manager template mail',
                'routes' => [
                    'education::education.settings.index-template',
                    'education::education.settings.update-template',
                ]
            ],
            'branches.mail.list.education12' => [
                'label' => 'Manager branches',
                'routes' => [
                    'education::education.settings.branch-mail',
                    'education::education.settings.update-mail',
                    'education::education.settings.show-mail',
                ]
            ]
        ]
    ],
    'Report' => [
        'label' => 'Report statistic',
        'child' => [
            'report.risk' => [
                'label' => 'Risk',
                'routes' => [
                    'project::report.risk'
                ]
            ],
            [
                'label' => 'Risk detail',
                'routes' => [
                    'project::report.risk.detail'
                ]
            ],
            'report.opportunity' => [
                'label' => 'Opportunity list',
                'routes' => [
                    'project::report.opportunity',
                    'project::report.opportunity.detail'
                ],
                'guide' => '<ul>'
                    . '<li>Xem danh sách opportunity dự án</li>'
                    . '</ul>',
            ],
            // 'report.common-risk' => [
            //     'label' => 'Xem list Common Risk',
            //     'routes' => [
            //         'project::report.common-risk'
            //     ]
            // ],
            'report.common-risk-crud' => [
                'label' => 'Common Risk thêm sửa xóa',
                'routes' => [
                    'project::commonRisk.detail',
                    'project::commonRisk.save',
                    'project::commonRisk.delete',
                ],
                'guide' => '<ul>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ],
            'report.ncm' => [
                'label' => 'NCM',
                'routes' => [
                    'project::report.ncm',
                    'project::report.ncm.detail'
                ]
            ],
            'report.issues' => [
                'label' => 'Issue list',
                'routes' => [
                    'project::report.issue',
                    'project::report.issue.save.comment'
                ],
                'guide' => '<ul>'
                    . '<li>Xem danh sách issue dự án</li>'
                    . '</ul>',
            ],
            // 'report.common-issue' => [
            //     'label' => 'Xem list Common Issue',
            //     'routes' => [
            //         'project::report.common-issue'
            //     ]
            // ],
            'report.common-issue-crud' => [
                'label' => 'Common Issue thêm sửa xóa',
                'routes' => [
                    'project::commonIssue.detail',
                    'project::commonIssue.save',
                    'project::commonIssue.delete',
                ],
                'guide' => '<ul>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'report.gitlab' => [
                'label' => 'Gitlab',
                'routes' => [
                    'project::report.gitlab',
                ],
                'guide' => '<ul>'
                    . '<li>Xem danh sách dự án trên gitlab</li>'
                    . '</ul>',
            ],
            'report.kpi' => [
                'label' => 'KPI',
                'routes' => [
                    'project::report.kpi.index',
                ],
                'guide' => '<ul>'
                . '<li><b>Report kpi: nhân sự, project</b></li>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>',
            ]
        ]
    ],

    'File' => [
        'label' => 'Văn bản',
        'child' => [
            'view.list.file' => [
                'label' => 'Quản lý văn bản',
                'routes' => [
                    'file::file.index',
                    'file::file.add',
                    'file::file.postAddFile',
                    'file::file.get-leader-team',
                    'file::file.check-code-file-exist',
                    'file::file.get-ceo-company',
                    'file::file.delete',
                    'file::file.editApproval',
                    'file::file.postEditFile',
                    'file::file.list'
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem thông tin văn bản, công văn</b></li>'
                    . '<li>Quyền công ty: Xem được danh sách toàn bộ văn bản, công văn</li>'
                    . '<li>Quyền team: Xem được danh sách văn bản, công văn của thành viên trong team đó và team phụ trách(nếu có) tạo</li>'
                    . '<li>Quyền cá nhân: Xem được danh sách văn bản, công văn của cá nhân thành viên đó</li>'
                    . '<li>Không phân quyền: không truy cập được vào function</li>'
                    . '</ul>',
            ]
        ],
    ],

    'Fines Money' => [
        'label' => 'Fines Money',
        'child' => [
            'view.list.finesmoney' => [
                'label' => 'Quản lý phạt nội quy',
                'routes' => [
                    'fines-money::fines-money.manage.list',
                    'fines-money::fines-money.manage.list-fines-money',
                    'fines-money::fines-money.manage.history',
                    'fines-money::fines-money.manage.edit-money',
                    'fines-money::fines-money.manage.update_import',
                    'fines-money::fines-money.manage.export',
                ],
            ]
        ],
    ],
    // end A-1 file
//old request asset -> remove
    'T-1' => [
        'label' => 'Request IT',
        'child' => [
            'request.manage.team' => [
                'label' => 'Manage requests',
                'routes' => [
                    'ticket::it.manage.request.team',
                ]
            ],
            'request.view.request' => [
                'label' => 'View requests',
                'routes' => [
                    'ticket::it.view.request.team',
                ]
            ],
        ]
    ],

    'Manage-time' => [
        'label' => 'Manage time',
        'child' => [
            'manage.register' => [
                'label' => 'Register for late in early out, business trip, supplement, leave day, overtime',
                'routes' => [
                    'manage_time::profile.comelate.register',
                    'manage_time::profile.mission.register',
                    'manage_time::profile.supplement.register',
                    'manage_time::profile.leave.register',
                    'manage_time::profile.timekeeping',
                    'manage_time::profile.timekeeping-list',
                    'ot::ot.register',
                ]
            ],
            'manage.acquisition' => [
                'label' => '年次有給休暇取得状況一覧',
                'routes' => [
                    'manage_time::profile.leave.acquisition-status',
                ]
            ],
            'manage.view' => [
                'label' => 'Manage late in early out, business trip, supplement, leave day, overtime, report',
                'routes' => [
                    'manage_time::manage-time.manage.view',
                    'manage_time::timekeeping.manage.report',
                ],
                'guide'=>'Báo cáo lịch công tác: Chỉ xem được khi được phân quyền nhóm hoặc toàn công ty'
            ],
            'manage.comelate.approve' => [
                'label' => 'Approve late in early out',
                'routes' => [
                    'manage_time::manage-time.manage.comelate.approve',
                ]
            ],
            'manage.leave_day.approve' => [
                'label' => 'Duyệt đơn nghỉ phép',
                'routes' => [
                    'manage_time::manage-time.manage.leave_day.approve',
                ]
            ],
            'manage.supplement.approve' => [
                'label' => 'Duyệt đơn bổ sung công',
                'routes' => [
                    'manage_time::manage-time.manage.supplement.approve',
                ]
            ],
            'manage.ot.approve' => [
                'label' => 'Duyệt đơn OT',
                'routes' => [
                    'manage_time::manage-time.manage.ot.approve',
                ]
            ],
            'manage.mission.approve' => [
                'label' => 'Duyệt đơn đi công tác',
                'routes' => [
                    'manage_time::manage-time.manage.mission.approve',
                ]
            ],
            'manage.timekeeping' => [
                'label' => 'Quản lý bảng chấm công',
                'routes' => [
                    'manage_time::manage-time.manage.timekeeping',
                ],
                'guide' => '<ul>'
                . '<li><strong>Danh sách, thêm, sửa, xóa bảng chấm công: </strong></li>'
                . '<li><b>Quyền công ty:</b> bảng công toàn công ty</li>'
                . '<li><b>Quyền team:</b> bảng công trong team và team con</li>'
                . '</ul>'
            ],
            'manage.timekeeping.view' => [
                'label' => 'Xem chấm công',
                'routes' => [
                    'manage_time::manage-time.manage.timekeeping.view', // route virtual
                ],
                'guide' => '<ul>'
                . '<li><strong>Xem: danh sách, chi tiết công, bảng tổng hợp.</strong></li>'
                . '<li><b>Quyền công ty:</b> xem bảng công toàn công ty</li>'
                . '<li><b>Quyền team:</b> xem bảng công của team và team con</li>'
                . '<li>Người có quyền <b>Quản lý bảng chấm công</b> thì có quyền <b>Xem chấm công</b></li>'
                . '</ul>'
            ],
            'setting.timekeeping-management' => [
                'label' => 'Setting người nhận thông báo khi xóa account hệ thống',
                'routes' => [
                    'manage_time::admin.timekeeping-management.index',
                    'manage_time::admin.timekeeping-management.update',
                ]
            ],
            'view.team.timekeeping.aggregates' => [
                'label' => 'Bảng chấm công nhân viên',
                'routes' => [
                    'manage_time::division.list-tk-aggregates',
                ],
                'guide' => '<ul>'
                . '<li>Phân quyền cho nhân viên xem bảng công - D lead</li>'
                . '<li><b>Quyền team:</b>Xem bảng công của nhân viên theo team</li>'
                . '<li><b>Quyền công ty:</b> xem bảng công toàn công ty</li>'
                . '</ul>'
            ],
            'view.team.report.late_minute' => [
                'label' => 'Thống kê phút đi muộn và tiền phạt',
                'routes' => [
                    'manage_time::division.late-minute-report',
                ],
                'guide' => '<ul>'
                . '<li><b>Quyền cá nhân:</b> Xem nhân viên mà hiện tại mình là PM</li>'
                . '<li><b>Quyền team:</b> Xem nhân viên của team</li>'
                . '<li><b>Quyền công ty:</b> Xem nhân viên công toàn công ty</li>'
                . '</ul>'
            ],
            'list.staff-are-late' => [
                'label' => 'Quản lý nhân viên không đi muộn',
                'routes' => [
                    'manage_time::admin.staff-late.*',
                    'manage_time::admin.staff-late.not-late*',
                ],
                'guide' => '<ul>'
                . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                . '</ul>'
            ],
            'manage.create.other' => [
                'label' => 'Tạo, sửa và xóa đơn đăng ký cho nhân viên khác',
                'routes' => [
                    'manage_time::manage.create.other',
                ]
            ],
            'manage.working_times' => [
                'label' => 'Quản lý đăng ký thời gian làm việc',
                'routes' => [
                    'manage_time::permis.wktime.manage',
                    'manage_time::wktime.manage.list',
                    'manage_time::wktime.manage.list.logs'
                ]
            ],
            'working_time.register' => [
                'label' => 'Đăng ký thay đổi giờ làm',
                'routes' => [
                    'manage_time::permiss.wktime.register'
                ]
            ],
            'working_time.approve' => [
                'label' => 'Duyệt đơn đăng ký thời gian làm việc',
                'routes' => [
                    'manage_time::permis.wktime.approve'
                ]
            ],
            'working_time.log_times' => [
                'label' => 'Nhập giờ làm việc vào/ra',
                'routes' => [
                    'manage_time::permiss.log_time'
                ]
            ],
            'report.employee_onsite.list' => [
                'label' => 'Thống kê nhân viên onsite tròn năm',
                'routes' => [
                    'manage_time::hr.report-onsite',
                    'manage_time::hr.export-onsite',
                    'manage_time::hr.grateful-store',
                    'manage_time::hr.grateful-remove',
                ],
                'guide' => '<ul>'
                . '<li>Chỉ cần có quyền là thực hiện được mọi chức năng</li>'
                . '</ul>'
            ],
            'division.leave_day.list' => [
                'label' => 'list leave days',
                'routes' => [
                    'manage_time::division.list-day-of-leave',
                ],
                'guide' => '<ul>'
                    . '<li><b>Xem danh sách ngày phép</b></li>'
                    . '<li>Không phân quyền: không có quyền xem</li>'
                    . '<li>Quyền cá nhân: Thấy của nhân viên trong dự án mình là PM</li>'
                    . '<li>Quyền team: Thấy nhân viên thuộc bộ phận của mình + X</li>'
                    . '<li>Quyền công ty: Thấy all nhân viên</li>'
                    . '</ul>',
            ],
        ]
    ],

    'ADMIN-1' => [
        'label' => 'Admin',
        'child' => [
            'admin.mail.birthday.employee' => [
                'label' => 'Manage mail birthday employee',
                'routes' => [
                    'event::mail.birthday.employee.*'
                ]
            ],
            'admin.mail.membership.employee' => [
                'label' => 'Manage mail membership employee',
                'routes' => [
                    'event::mail.membership.employee.*'
                ]
            ],
            'manage.leave' => [
                'label' => 'Manage day of leave',
                'routes' => [
                    'manage_time::admin.manage-day-of-leave.*'
                ]
            ],
            'manage.reason.leave' => [
                'label' => 'Manage reason leave',
                'routes' => [
                    'manage_time::admin.manage-reason-leave.*'
                ]
            ],
            'admin.monthly.report' => [
                'label' => 'Monthly report',
                'routes' => [
                    'project::monthly.report.index',
                    'project::monthly.report.search',
                ]
            ],
            'admin.monthly.report.edit' => [
                'label' => 'Monthly report edit',
                'routes' => [
                    'project::monthly.report.update',
                    'project::monthly.report.import_billable',
                    'project::monthly.report.export_billable',
                    'project::monthly.report.team_config',
                    'project::monthly.report.save_config',
                ]
            ],
            'release.notes.manage' => [
                'label' => 'Release notes manage',
                'routes' => [
                    'notes::manage.release.notes',
                ]
            ],
            'it.sendmail.off' => [
                'label' => 'Send mail confirm delete emails leave off',
                'routes' => [
                    'event::mailoff.*'
                ],
                'guide' => '<ul>'
                . '<li><strong>Gửi mail cho leader xác nhận xóa email của nhân viên đã nghỉ việc</strong></li>'
                . '<li>Chỉ cần có quyền là thực hiện được</li>'
                . '</ul>'
            ],
            'admin.report.onsite' => [
                'label' => 'Thống kê onsite',
                'routes' => [
                    'manage_time::timekeeping.manage.report',
                    'manage_time::timekeeping.manage.export',
                ]
            ],
            'admin.report.business.trip' => [
                'label' => 'Báo cáo đơn công tác',
                'routes' => [
                    'manage_time::timekeeping.manage.report-business-trip',
                    'manage_time::timekeeping.manage.report-business-trip-export',
                ],
                'guide' => 'Chỉ cần phần quyền bất kỳ người sử dụng đều có quyền thao tác chức năng này',
            ],
            'admin.report.certificates' => [
                'label' => 'Thống kê chứng chỉ',
                'routes' => [
                    'team::team.report.certificates',
                ],
            ],
            'admin.report.project.timekeeping.systena' => [
                'label' => 'Theo dõi công Systena',
                'routes' => [
                    'manage_time::timekeeping.manage.report_project_timekeeping_systena',
                    'manage_time::timekeeping.manage.export_project_timekeeping_systena',
                ],
                'guide' => 'Chỉ cần phần quyền bất kỳ người sử dụng đều có quyền thao tác chức năng này',
            ],
            'admin.report.ot' => [
                'label' => 'Báo cáo đơn OT',
                'routes' => [
                    'ot::ot.manage.report_manage_ot',
                ],
                'guide' => 'Chỉ cần phần quyền bất kỳ người sử dụng đều có quyền thao tác chức năng này',
            ],
            'admin.setting.list-admin' => [
                'label' => 'List admin',
                'routes' => [
                    'admin_setting::index',
                ],
            ],
            'admin.setting.ot-admin' => [
                'label' => 'Danh sách nhân viên không được tính OT',
                'routes' => [
                    'admin::setting-ot.*',
                ],
            ],

        ]
    ],

    'Help' => [
        'label' => 'Help documents',
        'child' => [
            'manage.help.create' => [
                'label' => 'Manage help',
                'routes' => [
                    'help::manage.help.*',
                    'help::display.help.edit',
                ]
            ],
        ],
    ],

    'Music' => [
        'label' => 'Music',
        'child' => [
            'music.office.list' => [
                'label' => 'manage office',
                'routes' => [
                    'music::manage.offices',
                    'music::manage.offices.create',
                    'music::manage.offices.save',
                    'music::manage.offices.del',
                    'music::manage.offices.edit',
                    'music::manage.offices.checkName'
                ]
            ],
            'music.order.list' => [
                'label' => 'manage order',
                'routes' => [
                    'music::manage.order',
                    'music::manage.order.del',
                    'music::manage.order.delMany'
                ]
            ],
        ]
    ],


    /*'VOTE_NEW' => [
        'label' => 'Votes',
        'child' => [
            'vote_new.view' => [
                'label' => 'View votes',
                'routes' => [
                    'vote::manage.vote.index',
                    'vote::manage.vote.edit',
                    'vote::manage.nominee.load_data',
                    'vote::manage.nominator.load_data',
                    'vote::manage.vote_nominee.load_data',
                    'vote::manage.voter.load_data'
                ]
            ],
            'vote_new.manage' => [
                'label' => 'Manage votes',
                'routes' => [
                    'vote::manage.vote.create',
                    'vote::manage.vote.store',
                    'vote::manage.vote.update',
                    'vote::manage.vote.delete',
                    'vote::manage.vote.sendmail.nominate',
                    'vote::manage.vote.sendmail.vote',
                    'vote::manage.vote_nominee.update_desc',
                    'vote::manage.vote_nominee.list_employee',
                    'vote::manage.vote_nominee.store',
                    'vote::manage.vote_nominee.delete'
                ]
            ]
        ]
    ]*/
    'Knowledge-system' => [
        'label' => 'Knowledge system',
        'child' => [
            'kl.field.manage' => [
                'label' => 'Manage field',
                'routes' => [
                    'tag::field.manage.*'
                ]
            ],
            'kl.project.old.manage' => [
                'label' => 'Manage old project',
                'routes' => [
                    'tag::manage.project.old'
                ]
            ],
            'kl.view.project.tag.tagging' => [
                'label' => 'View project tagging',
                'routes' => [
                    'tag::view.proj.tagging'
                ]
            ],
            'kl.view.project.search' => [
                'label' => 'View project search',
                'routes' => [
                    'tag::view.proj.search'
                ]
            ],
            'kl.view.project.detail.popup' => [
                'label' => 'View project detail popup',
                'routes' => [
                    'tag::view.proj.detail'
                ]
            ],
            'kl.post.submit.project.tag' => [
                'label' => 'Submit project tag',
                'routes' => [
                    'tag::post.proj.submit.tag'
                ]
            ],
            'kl.post.approve.project.tag' => [
                'label' => 'Approve project tag',
                'routes' => [
                    'tag::post.proj.approve.tag'
                ]
            ],
        ]
    ],
    'Document' => [
        'label' => 'Quản lý tài liệu',
        'child' => [
            'doc.manage' => [
                'label' => 'Quản lý tài liệu',
                'routes' => [
                    'doc::permis.doc.manage',
                    'magazine::save',
                    'magazine::update',
                    'magazine::delete',
                ]
            ],
            'doc.review' => [
                'label' => 'Review tài liệu',
                'routes' => [
                    'doc::permiss.doc.review'
                ],
                'guide' => '<ul>'
                    . '<li><strong>Chỉ cần có quyền</strong>: mục đích chỉ để chọn ra danh sách người có quyền review tài liệu</li>'
                    . '</ul>'
            ],
            'doc.publish' => [
                'label' => 'Publish tài liệu',
                'routes' => [
                    'doc::permiss.doc.publish'
                ],
                'guide' => '<ul>'
                    . '<li><strong>Chỉ cần có quyền</strong>: mục đích chỉ để chọn ra danh sách người có quyền publish tài liệu</li>'
                    . '</ul>'
            ],
            'doc.type.manage' => [
                'label' => 'Quản lý loại tài liệu',
                'routes' => [
                    'doc::admin.type.*'
                ]
            ],
            'doc.request.manage' => [
                'label' => 'Quản lý yêu cầu tài liệu',
                'routes' => [
                    'doc::permis.request.manage'
                ]
            ]
        ]
    ],
    'Asset-management' => [
        'label' => 'Quản lý tài sản',
        'child' => [
            'management.asset.view.list' => [
                'label' => 'Xem danh sách tài sản',
                'routes' => [
                    'asset::asset.index',
                    'asset::asset.group.index',
                    'asset::asset.category.index',
                    'asset::asset.origin.index',
                    'asset::asset.supplier.index',
                    'asset::asset.attribute.index',
                    'asset::asset.warehouse.index',
                    'asset::asset.getAsset',
                    'asset::asset.getAssetProfile',
                    'asset::asset.export_asset',
                ]
            ],
            'management.asset.view.detail' => [
                'label' => 'Xem chi tiết tài sản',
                'routes' => [
                    'asset::asset.view',
                ]
            ],
            'management.asset.edit' => [
                'label' => 'Thêm và sửa thông tin tài sản',
                'routes' => [
                    'asset::asset.add',
                    'asset::asset.save',
                    'asset::asset.edit',
                    'asset::asset.ajax-get-attribute-and-code',
                    'asset::asset.group.save',
                    'asset::asset.group.view',
                    'asset::asset.group.check-exist-group-name',
                    'asset::asset.category.save',
                    'asset::asset.category.view',
                    'asset::asset.category.checkExist',
                    'asset::asset.origin.save',
                    'asset::asset.origin.view',
                    'asset::asset.origin.check-exist-origin-name',
                    'asset::asset.supplier.save',
                    'asset::asset.supplier.view',
                    'asset::asset.supplier.checkExist',
                    'asset::asset.attribute.save',
                    'asset::asset.attribute.view',
                    'asset::asset.attribute.check-exist-attribute-name',
                    'asset::asset.warehouse.check-exist',
                    'asset::asset.warehouse.save',
                    'asset::asset.importFile',
                    'asset::asset.group.importFile',
                    'asset::asset.category.importFile',
                    'asset::asset.supplier.importFile',
                ]
            ],
            'management.asset.delete' => [
                'label' => 'Xóa thông tin tài sản',
                'routes' => [
                    'asset::asset.index',
                    'asset::asset.delete',
                    'asset::asset.group.delete',
                    'asset::asset.category.delete',
                    'asset::asset.origin.delete',
                    'asset::asset.supplier.delete',
                    'asset::asset.attribute.delete',
                    'asset::asset.warehouse.delete',
                ]
            ],
            'management.asset.allocation' => [
                'label' => 'Cấp phát, thu hồi ,báo mất, báo hỏng, gửi trả khách hàng, đề nghị thanh lý, đề nghị sửa chữa và bảo dưỡng tài sản',
                'routes' => [
                    'asset::asset.asset-allocation',
                    'asset::asset.asset-retrieval',
                    'asset::asset.ajax-get-asset-information',
                    'asset::asset.asset-lost-notification',
                    'asset::asset.asset-broken-notification',
                    'asset::asset.asset-suggest-liquidate',
                    'asset::asset.asset-suggest-repair-maintenance',
                    'asset::asset.ajax-get-asset-information',
                    'asset::asset.asset-return',
                ]
            ],
            'management.asset.approve' => [
                'label' => 'Duyệt báo mất, báo hỏng, đề nghị thanh lý, đề nghị sửa chữa và bảo dưỡng tài sản',
                'routes' => [
                    'asset::asset.approve',
                    'asset::asset.confirm-repaired-maintained',
                    'asset::asset.ajax-get-asset-to-approve',
                ]
            ],
            'management.asset.report' => [
                'label' => 'Thống kê, tổng hợp báo cáo về quản lý tài sản',
                'routes' => [
                    'asset::asset.report',
                    'asset::asset.view-report',
                    'asset::asset.ajax-get-employee-to-report',
                    'asset::asset.ajax-get-asset-to-report',
                    'asset::asset.ajax-get-modal-report',
                ]
            ],
            'management.asset.inventory' => [
                'label' => 'Quản lý kiểm kê tài sản',
                'routes' => [
                    'asset::inventory.*'
                ]
            ],
            'management.asset.confirm' => [
                'label' => 'Xác nhận tài sản ',
                'routes' => [
                    'asset::asset.assetITProfile',
                    'asset::asset.itConfirm',
                    'asset::report.*',
                ]
            ],
            'mamagement.asset.setting' => [
                'label' => 'Cài đặt tài sản',
                'routes' => [
                    'asset::setting.index',
                    'core::setting.system.data.save',
                ],
            ],
            'management.asset.allocation_warehouse_for_it' => [
                'label' => 'Kho cấp phát tài sản cho IT',
                'routes' => [
                    'asset::asset.asset_to_warehouse',
                ]
            ],
        ]
    ],
    'Asset-request' => [
        'label' => 'Yêu cầu tài sản',
        'child' => [
            'request.asset.create' => [
                'label' => 'Tạo, sửa và xóa yêu cầu tài sản',
                'routes' => [
                    'asset::resource.request.create',
                    'asset::resource.request.edit',
                    'asset::resource.request.delete',
                    'asset::resource.request.delete-request',
                ],
                'guide' => '<ul>'
                . '<li><b>Chỉnh sửa, tạo mới, xóa yêu cầu tài sản</b></li>'
                . '<li>Tạo: chỉ cần có quyền là tạo được yêu cầu tài sản</li>'
                . '<li>Sửa, xóa: <ul>'
                    . '<li>Quyền công ty: sửa, xóa được tất cả yêu cầu</li>'
                    . '<li>Quyền team: sửa, xóa được các yêu cầu của người tạo/sửa dụng trong team</li>'
                    . '<li>Quyền cá nhân: sửa, xóa được các yêu cầu của mình tạo/sử dụng</li>'
                . '</ul></li>'
                . '</ul>'
            ],
            'request.asset.view.list' => [
                'label' => 'Xem danh sách yêu cầu tài sản',
                'routes' => [
                    'asset::resource.request.index',
                ],
                'guide' => '<ul>'
                . '<li><b>Xem danh sách yêu cầu tài sản</b></li>'
                . '<li>Quyền công ty: xem được tất cả yêu cầu</li>'
                . '<li>Quyền team: xem được các yêu cầu của người tạo/sử dụng cùng chi nhánh</li>'
                . '<li>Quyền cá nhân: xem được các yêu cầu của mình tạo/sử dụng</li>'
                . '</ul>'
            ],
            'request.asset.view.detail' => [
                'label' => 'Xem chi tiết yêu cầu tài sản',
                'routes' => [
                    'asset::resource.request.view',
                ],
                'guide' => '<ul><li>Tương tự quyền xem danh sách yêu cầu tài sản</li></ul>'
            ],
            'request.asset.review' => [
                'label' => 'Review yêu cầu tài sản',
                'routes' => [
                    'asset::resource.request.review',
                ],
                'guide' => '<ul>'
                . '<li><b>Review yêu cầu tài sản</b></li>'
                . '<li>Quyền công ty: review được tất cả yêu cầu</li>'
                . '<li>Quyền team: review được các yêu cầu có người review cùng chi nhánh</li>'
                . '<li>Quyền cá nhân: review được các yêu cầu có người review là chính mình</li>'
                . '</ul>'
            ],
            'request.asset.approve' => [
                'label' => 'Duyệt yêu cầu tài sản',
                'routes' => [
                    'asset::resource.request.approve',
                ],
                'guide' => '<ul>'
                . '<li><b>Duyệt yêu cầu tài sản</b></li>'
                . '<li>Quyền công ty: duyệt được tất cả yêu cầu</li>'
                . '<li>Quyền team: duyệt được các yêu cầu có người sử dụng cùng chi nhánh</li>'
                . '<li>Quyền cá nhân: không có quyền duyệt</li>'
                . '</ul>'
            ],
        ]
    ],
    'Welfare' => [
        'label' => 'Welfare',
        'child' => [
            'viewWelfare' => [
                'label' => 'Danh Sách Chương Trình Phúc Lợi',
                'routes' => [
                    'welfare::welfare.event.index',
                    'welfare::welfare.event.edit',
                    'welfare::welfare.RelativeAttach.data',
                    'welfare::welfare.WelFreMore.data',
                    'welfare::welfare.WelFreMore.data',
                    'welfare::welfare.RelativeAttach.data',
                    'welfare::welfare.WelFreMore.data',
                    'welfare::welfare.add.file',
                    'welfare::welfare.datatables.data',
                    'welfare::welfare.event.detailpost',
                    'welfare::welfare.formImplements.list',
                    'welfare::welfare.group.list',
                    'welfare::welfare.organizer.index',
                    'welfare::welfare.participant.index',
                    'welfare::welfare.partner.index',
                    'welfare::welfare.purpose.list',
                ]
            ],
            'editWelfare' => [
                'label' => 'Create\Edit chương trình phúc lợi',
                'routes' => [
                    'welfare::welfare.event.create',
                    'welfare::welfare.event.save',
                    'welfare::welfare.RelativeAttach.save',
                    'welfare::welfare.WelFreMore.delete',
                    'welfare::welfare.WelFreMore.save',
                    'welfare::welfare.WelFreMore.data',
                    'welfare::welfare.RelativeAttach.save',
                    'welfare::welfare.WelFreMore.delete',
                    'welfare::welfare.WelFreMore.save',
                    'welfare::welfare.add.file',
                    'welfare::welfare.event.delete',
                    'welfare::welfare.file.delete',
                    'welfare::welfare.formImplements.delete',
                    'welfare::welfare.formImplements.create',
                    'welfare::welfare.formImplements.save',
                    'welfare::welfare.formImplements.saveAjax',
                    'welfare::welfare.group.create',
                    'welfare::welfare.group.delete',
                    'welfare::welfare.group.save',
                    'welfare::welfare.group.saveAjax',
                    'welfare::welfare.organizer.create',
                    'welfare::welfare.partner.add',
                    'welfare::welfare.partner.create',
                    'welfare::welfare.partner.delete',
                    'welfare::welfare.partner.edit',
                    'welfare::welfare.partner.group.add',
                    'welfare::welfare.partner.group.delete',
                    'welfare::welfare.partner.group.edit',
                    'welfare::welfare.purpose.create',
                    'welfare::welfare.purpose.delete',
                    'welfare::welfare.purpose.save',
                    'welfare::welfare.purpose.saveAjax',
                    'welfare::welfare.relative.attach.add',
                    'welfare::welfare.relative.attach.delete',
                    'welfare::welfare.relative.attach.edit',
                    'welfare::welfare.datatables.save',
                    'welfare::welfare.send.mail',
                    'welfare::welfare.preview.mail',
                    'welfare::welfare.export.employee',
                    'welfare::welfare.export.employee.participate',
                    'welfare::welfare.export.employee.joined',
                    'welfare::welfare.export.employee.attached',
                    'welfare::welfare.export.fee',
                    'welfare::welfare.partner.list',
                    'welfare::welfare.partner.group.list',

                ]
            ],
            'viewRelationName' => [
                'label' => 'Danh sách tên mối quan hệ với người thân',
                'routes' => [
                    'welfare::welfare.relation.list',
                ]
            ],
            'editRelationName' => [
                'label' => 'Chỉnh sửa tên mối quan hệ với người thân',
                'routes' => [
                    'welfare::welfare.relation.delete',
                    'welfare::welfare.relation.edit',
                    'welfare::welfare.relation.save',
                    'welfare::welfare.relation.create',
                    'welfare::welfare.relation.check.name',
                ]
            ]
        ]
    ],
    'Moblie' => [
        'label' => 'Mobile',
        'child' => [
            'admin.home-message' => [
                'label' => 'Quản lý lời nhắn và banner',
                'routes' => [
                    'HomeMessage::home_message.*',
                ],
            ],
            'admin.notify' => [
                'label' => 'Quản lý thông báo',
                'routes' => [
                    'notify::admin.notify.*',
                ],
            ],
            'manage.proposed' => [
                'label' => 'Quản lý ý kiến xây dựng Rikkei',
                'routes' => [
                    'proposed::manage-proposed.index'
                ],
                'guide' => '<ul>'
                    . '<li>Chức năng không phân biệt loại quyền</li>'
                    . '</ul>',
            ],
            'admin.rank_point' => [
                'label' => 'Quản lý sự kiện, quà tặng và R-Point',
                'routes' => [
                    'RankPoint::rankPoint',
                ],
            ],
            'admin.confession' => [
                'label' => 'Quản lý confession',
                'routes' => [
                    'Confession::confession',
                ],
            ],
            'admin.market' => [
                'label' => 'Quản lý chợ',
                'routes' => [
                    'Market::market',
                ],
            ],
            'admin.game' => [
                'label' => 'Quản lý trò chơi',
                'routes' => [
                    'Game::game',
                ],
            ],
            'admin.statistics' => [
                'label' => 'Số liệu thống kê',
                'routes' => [
                    'Statistics::statistics',
                ],
            ],
            'admin.donates' => [
                'label' => 'Quản lý chương trình từ thiện',
                'routes' => [
                    'Donates::donates',
                ],
            ],
            'mobile.config' => [
                'label' => 'Quản lý cấu hình mobile',
                'routes' => [
                    'mobile-config::mobile-config.index'
                ],
                'guide' => '<ul>'
                    . '<li>Chức năng không phân biệt loại quyền</li>'
                    . '</ul>',
            ],
            'admin.app-version' => [
                'label' => 'Quản lý phiên bản ứng dụng',
                'routes' => [
                    'AppVersion::app-version',
                ],
            ],
        ]
    ],
    'education-12' => [
        'label' => 'Education Teachings',
        'child' => [
            'hr.teachings' => [
                'label' => 'Teachings registration management',
                'routes' => [
                    'education::education.teaching.hr.index',
                    'education::education.teaching.hr.update',
                    'education::education.teaching.hr.reject',
                    'education::education.teaching.ajax-course-id'
                ]
            ]
        ]
    ],
    'Api-revenue-1' => [
        'label' => 'API Revenue',
        'child' => [
            'api.project.revenue' => [
                'label' => 'API cho module Doanh Thu',
                'routes' => [
                    'api::project.revenue-list',
                    'api::team.owner-lists',
                ],
                'guide' => '<ul>'
                    . '<li><b>Thông tin dự án, members, effores, chi phí, thưởng...</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ],
            'api.operation' => [
                'label' => 'API cho module Operation',
                'routes' => [
                    'api::operations.operation-reports',
                    'api::operations.delete-operation-project',
                    'api::operations.create-operation-project',
                    'api::operations.project-future',
                    'api::operations.project-cost-update'
                ],
                'guide' => '<ul>'
                    . '<li><b>Thông tin về operations overview, project</b></li>'
                    . '<li>Chỉ cần có quyền là thực hiện được chức năng này</li>'
                    . '</ul>',
            ]
        ]
    ],

    'timesheet' => [
        'label' => 'Timesheet',
        'child' => [
            'timesheet.list' => [
                'label' => 'Danh sách time sheet',
                'routes' => [
                    'project::timesheets.index',
                ],
            ],
            'timesheet.edit' => [
                'label' => 'Tạo mới và chỉnh sửa time sheet',
                'routes' => [
                    'project::timesheets.create',
                    'project::timesheets.store',
                    'project::timesheets.edit',
                    'project::timesheets.update',
                    'project::timesheets.destroy',
                ],
            ]
        ]
    ],
];
