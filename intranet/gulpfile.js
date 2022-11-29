/*
 * disable notification when gulp run success
 */
process.env.DISABLE_NOTIFIER = true;

var elixir = require('laravel-elixir');
require('es6-promise').polyfill();
elixir.config.sourcemaps = false;
/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    // core
    /*
     * mix.sass('common/style.scss', 'public/common/css');
    mix.sass('common/login.scss', 'public/common/css');
    mix.sass('common/errors.scss', 'public/common/css');
    mix.sass('common/guest.scss', 'public/common/css');
    mix.scripts('common/script.js', 'public/common/js');
    mix.scripts('common/box.email.js', 'public/common/js');
    mix.scripts('common/methods.validate.js', 'public/common/js');
    mix.scripts('common/external.js', 'public/common/js');
    mix.scripts('common/check_item.js', 'public/common/js');
    */

    // team
    /*
    mix.sass('team/style.scss', 'public/team/css');
    mix.sass('team/cv.scss', 'public/team/css');
    mix.sass('team/ss.scss', 'public/team/css');
    mix.scripts('team/script.js', 'public/team/js');
    mix.scripts('team/ss.js', 'public/team/js');
    mix.scripts('team/view.js', 'public/team/js');
    mix.scripts('team/checkpoint/create.js', 'public/team/js/checkpoint');
    mix.scripts('team/checkpoint/make.js', 'public/team/js/checkpoint');
    mix.scripts('team/checkpoint/detail.js', 'public/team/js/checkpoint');
    mix.scripts('team/checkpoint/list.js', 'public/team/js/checkpoint');
    mix.scripts('team/checkpoint/preview.js', 'public/team/js/checkpoint');
    mix.scripts('team/xlsx-func.js', 'public/team/js');
    */

    // sale
    /*mix.sass('sales/sales.scss', 'public/sales/css');
    mix.sass('sales/css_customer.scss', 'public/sales/css');
    mix.sass('sales/tracking.scss', 'public/sales/css');
    mix.sass('sales/customer_create.scss', 'public/sales/css');
    mix.sass('sales/customer_index.scss', 'public/sales/css');
    mix.sass('sales/opportunity.scss', 'public/sales/css');
    mix.scripts('sales/css/analyze.js', 'public/sales/js/css');
    mix.scripts('sales/css/create.js', 'public/sales/js/css');
    mix.scripts('sales/css/dataTables.js', 'public/sales/js/css');
    mix.scripts('sales/css/customer.js', 'public/sales/js/css');
    mix.scripts('sales/css/list.js', 'public/sales/js/css');
    mix.scripts('sales/css/preview.js', 'public/sales/js/css');
    mix.scripts('sales/css/make.js', 'public/sales/js/css');
    mix.scripts('sales/css/welcome.js', 'public/sales/js/css');
    mix.scripts('sales/css/success.js', 'public/sales/js/css');
    mix.scripts('sales/css/listMake.js', 'public/sales/js/css');
    mix.scripts('sales/tracking/index.js', 'public/sales/js/tracking');
    */

    // test
    /*
    mix.sass('test/upload.scss', 'public/tests/css');
    mix.sass('test_old/main.scss', 'public/tests_old/css');
    mix.sass('test/main.scss', 'public/tests/css');
    mix.sass('test/upload.scss', 'public/tests/css');
    mix.scripts('test/main.js', 'public/tests/js');
    mix.scripts('test/audio.js', 'public/tests/js');
    mix.scripts('test/upload_image.js', 'public/tests/js');
    mix.scripts('test/ad_src/main.js', 'public/tests/ad_src');
    mix.scripts('test/scripts.js', 'public/tests/js');
    */

    // project
    // mix.sass('project/edit.scss', 'public/project/css');
    // mix.sass('project/print.scss', 'public/project/css');
    // mix.sass('project/me_style.scss', 'public/project/css');
    // mix.sass('project/me_team_style.scss', 'public/project/css');
    // mix.sass('project/operation.scss', 'public/project/css');
    // mix.sass('project/plan.scss', 'public/project/css');
    // mix.scripts('project/script.js', 'public/project/js');
    // mix.scripts('project/edit.js', 'public/project/js');
    // mix.scripts('project/me_script.js', 'public/project/js');
    // mix.scripts('project/review_script.js', 'public/project/js');
    // mix.scripts('project/me_reward.js', 'public/project/js');
    // mix.scripts('project/wo-allocation.js', 'public/project/js');
    // mix.scripts('project/kpi.js', 'public/project/js');
    // mix.scripts('project/operation_member.js', 'public/project/js');
    // mix.scripts('project/operation_overview.js', 'public/project/js');
    // mix.scripts('project/operation_project.js', 'public/project/js');
    // mix.scripts('project/plan.js', 'public/project/js');
    // mix.scripts('project/timesheet.js', 'public/project/js');


    // resource
    /*mix.sass('resource/candidate/list.scss', 'public/resource/css/candidate');
    mix.sass('resource/candidate/search.scss', 'public/resource/css/candidate');
    mix.sass('resource/resource.scss', 'public/resource/css');
    mix.sass('resource/recruit.scss', 'public/resource/css');
    mix.sass('resource/hr_weekly_report.scss', 'public/resource/css');
    mix.sass('resource/monthly_report.scss', 'public/resource/css');
    mix.sass('resource/general.scss', 'public/resource/css');
    mix.sass('resource/statistics.scss', 'public/resource/css');
    mix.scripts('resource/hr_weekly_report/index.js', 'public/resource/js/hr_weekly_report');
    mix.scripts('resource/recruit/monthly_report.js', 'public/resource/js/recruit');
    mix.scripts('resource/request/create.js', 'public/resource/js/request');
    mix.scripts('resource/request/list.js', 'public/resource/js/request');
    mix.scripts('resource/request/detail.js', 'public/resource/js/request');
    mix.scripts('resource/candidate/create.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/list.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/detail.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/interested.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/checkexist.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/common.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/select2.common.js', 'public/resource/js/candidate');
    mix.scripts('resource/candidate/google_calendar.js', 'public/resource/js/candidate');
    mix.scripts('resource/dashboard/utilization.js', 'public/resource/js/dashboard');
    mix.scripts('resource/dashboard/dashboard.js', 'public/resource/js/dashboard');
    mix.scripts('resource/requetsasset/create.js', 'public/resource/js/requetsasset');
    mix.scripts('resource/requetsasset/detail.js', 'public/resource/js/requetsasset');
    mix.scripts('resource/program/program.js', 'public/resource/js/program');
    mix.scripts('resource/language/create.js', 'public/resource/js/language');
    mix.scripts('resource/recruit/index.js', 'public/resource/js/recruit');
    mix.scripts('resource/busy/busy.js', 'public/resource/js/busy');
    mix.scripts('resource/candidate/search.js', 'public/resource/js/candidate');
    mix.scripts('resource/staff/statistics.js', 'public/resource/js/staff');
    */


    //slide show
    /*mix.sass('slide_show/edit.scss', 'public/slide_show/css');
    mix.sass('slide_show/index.scss', 'public/slide_show/css');
    mix.sass('slide_show/styles.scss', 'public/slide_show/css');
    mix.scripts('slide_show/edit.js', 'public/slide_show/js');
    mix.scripts('slide_show/index.js', 'public/slide_show/js');
    mix.scripts('slide_show/video_default.js', 'public/slide_show/js');
    mix.scripts('slide_show/setting.js', 'public/slide_show/js');
     */

    // news
    /*mix.sass('news/news.scss', 'public/asset_news/css');
    mix.sass('news/post_news.scss', 'public/asset_news/css');
    mix.sass('news/comment.scss', 'public/asset_news/css');
    mix.sass('news/detail.scss', 'public/asset_news/css');

    mix.sass('news/external.scss', 'public/asset_news/css');
    mix.sass('news/home.scss', 'public/asset_news/css');
    mix.sass('news/common.scss', 'public/asset_news/css');
    mix.sass('news/opinion.scss', 'public/asset_news/css');
    mix.sass('news/poster.scss', 'public/asset_news/css');

    mix.scripts('news/emojiicons.js','public/asset_news/js');
    mix.scripts('news/comment_post.js','public/asset_news/js');
    mix.scripts('news/news.js', 'public/asset_news/js');
    mix.scripts('news/post_new.js', 'public/asset_news/js');

    mix.scripts('news/home.js', 'public/asset_news/js');
    mix.scripts('news/common.js', 'public/asset_news/js');
    mix.scripts('news/detail.js', 'public/asset_news/js');
    mix.scripts('news/poster.js', 'public/asset_news/js');


    // magazine
    /*mix.scripts('magazine/create.js', 'public/magazine/js');
    mix.scripts('magazine/magazine.js', 'public/magazine/js');
    mix.scripts('magazine/main.js', 'public/magazine/js');
     */

    // ticket
    /*mix.sass('ticket/ticket.css', 'public/asset_ticket/css');
    mix.scripts('ticket/ticket.js', 'public/asset_ticket/js');
    mix.scripts('ticket/ticket_check.js', 'public/asset_ticket/js');
    mix.scripts('ticket/ticket_menu.js', 'public/asset_ticket/js');
     */

    // help
    /*mix.sass('help/help.scss', 'public/asset_help/css');
    mix.sass('help/style.scss', 'public/asset_help/css');
    mix.sass('help/seed.scss', 'public/asset_help/css');
    mix.scripts('help/help.js', 'public/asset_help/js');
     */

    // manage time
    /*mix.sass('managetime/common.scss', 'public/asset_managetime/css');
    mix.sass('managetime/jquery.fileuploader.scss', 'public/asset_managetime/css');
    mix.sass('managetime/day_list.scss', 'public/asset_managetime/css');
    mix.sass('managetime/reason_list.scss', 'public/asset_managetime/css');
    mix.sass('managetime/personal.scss', 'public/asset_managetime/css');
    mix.sass('managetime/timekeeping.scss', 'public/asset_managetime/css');
    mix.sass('managetime/working-time.scss', 'public/asset_managetime/css');
    mix.sass('managetime/timekeepinglock.scss', 'public/asset_managetime/css');

    mix.scripts('managetime/approve.list.js', 'public/asset_managetime/js');
    mix.scripts('managetime/common.js', 'public/asset_managetime/js');
    mix.scripts('managetime/jquery.fileuploader.js', 'public/asset_managetime/js');
    mix.scripts('managetime/manage.list.js', 'public/asset_managetime/js');
    mix.scripts('managetime/register.js', 'public/asset_managetime/js');
    mix.scripts('managetime/register.list.js', 'public/asset_managetime/js');
    mix.scripts('managetime/comelate.register.js', 'public/asset_managetime/js');
    mix.scripts('managetime/admin_comelate.js', 'public/asset_managetime/js');
    mix.scripts('managetime/admin_comelate_not_late.js', 'public/asset_managetime/js');
    mix.scripts('managetime/comelate.register.edit.js', 'public/asset_managetime/js');
    mix.scripts('managetime/leave.register.js', 'public/asset_managetime/js');
    mix.scripts('managetime/jquery.shorten.js', 'public/asset_managetime/js');
    mix.scripts('managetime/leave.js', 'public/asset_managetime/js');
    mix.scripts('managetime/leave_day.js', 'public/asset_managetime/js');
    mix.scripts('managetime/timekeeping.js', 'public/asset_managetime/js');
    mix.scripts('managetime/personal.js', 'public/asset_managetime/js');
    mix.scripts('managetime/working-time.js', 'public/asset_managetime/js');
    mix.scripts('managetime/comelate.fileupload.js', 'public/asset_managetime/js');
    mix.scripts('managetime/report.list.js', 'public/asset_managetime/js');
    mix.scripts('managetime/project_timekeeping.js', 'public/asset_managetime/js');
    mix.scripts('managetime/working-time-register.js', 'public/asset_managetime/js');
    mix.scripts('managetime/report_onsite.js', 'public/asset_managetime/js');
    mix.scripts('managetime/late_minute_report.js', 'public/asset_managetime/js');
    */
    mix.scripts('managetime/leave.register.js', 'public/asset_managetime/js');

    // ot
    /*mix.sass('ot/register_ot.scss', 'public/asset_ot/css');
    mix.sass('ot/list_ot.scss', 'public/asset_ot/css');
    mix.scripts('ot/otregister.js', 'public/asset_ot/js');
    mix.scripts('ot/otlist.js', 'public/asset_ot/js');
    mix.scripts('ot/admin_register.js', 'public/asset_ot/js');
    */

    //music
    /*mix.sass('music/music.scss', 'public/asset_music/css');
    mix.sass('music/order_list.scss', 'public/asset_music/css');
    mix.scripts('music/music.js', 'public/asset_music/js');
    mix.scripts('music/music_frontend.js', 'public/asset_music/js');
    mix.scripts('music/order_mn.js', 'public/asset_music/js');
     */

    // tag
    /*mix.sass('tag/styles.scss', 'public/asset_tag/css');
    mix.scripts('tag/general.js', 'public/asset_tag/js');
    mix.scripts('tag/indexed-search.js', 'public/asset_tag/js');
     */

    // Q and A
    /*mix.sass('qa/styles.scss', 'public/asset_qa/css');
     */

    //Monthly report
    /*mix.sass('project/monthly_report.scss', 'public/project/css');
    mix.scripts('project/monthly_report.js', 'public/project/js');
     */

    // request asset
    /*mix.sass('request_asset/create.scss', 'public/request_asset/css');
     */

    // Asset management
    /*mix.sass('manage_asset/style.scss', 'public/manage_asset/css');
    mix.sass('manage_asset/request.scss', 'public/manage_asset/css');
    mix.scripts('manage_asset/manage_asset.approve.js', 'public/manage_asset/js');
    mix.scripts('manage_asset/manage_asset.report.js', 'public/manage_asset/js');
    mix.scripts('manage_asset/manage_asset.script.js', 'public/manage_asset/js');
    mix.scripts('manage_asset/manage_asset.shorten.js', 'public/manage_asset/js');
    mix.scripts('manage_asset/category/index.js', 'public/manage_asset/js/category');
    mix.scripts('manage_asset/group/index.js', 'public/manage_asset/js/group');
    mix.scripts('manage_asset/origin/index.js', 'public/manage_asset/js/origin');
    mix.scripts('manage_asset/supplier/index.js', 'public/manage_asset/js/supplier');
    mix.scripts('manage_asset/warehouse/index.js', 'public/manage_asset/js/warehouse');
    mix.scripts('manage_asset/common_asset.js', 'public/manage_asset/js/');
    mix.scripts('manage_asset/asset/report_pdf.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/create.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/report_process.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/index.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/profile.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/report_by_asset_lost.js', 'public/manage_asset/js/asset');
    mix.scripts('manage_asset/asset/xlsx-func.js', 'public/manage_asset/js/asset');
    */

    //notify
    // mix.sass('notify/notify.scss', 'public/asset_notify/css');
    // mix.scripts('notify/notify.js', 'public/asset_notify/js');
    // mix.scripts('notify/create-edit.js', 'public/asset_notify/js');


    //event mail
    /*mix.sass('event/event_mail.scss', 'public/event/css');
    mix.sass('event/salary.scss', 'public/event/css');
    mix.scripts('event/salary.js', 'public/event/js');
    mix.scripts('sales/common.js', 'public/sales/js');
    mix.scripts('event/script.js', 'public/event/js');*/

    //document

    // mix.sass('document/style.scss', 'public/asset_doc/css');
    // mix.scripts('document/main.js', 'public/asset_doc/js');
    // mix.scripts('common/setting-data.js', 'public/common/js');


    //statistic
    /*mix.sass('statistic/statistic.scss', 'public/assets/statistic/css');
    mix.scripts('statistic/proj_activity.js', 'public/assets/statistic/js');*/
    // contact
    /*mix.sass('contact/contact.scss', 'public/assets/contact/css');
    mix.scripts('contact/contact.js', 'public/assets/contact/js');
     */

    // release notes
    /*mix.sass('notes/notes.scss', 'public/assets/notes/css');
    mix.scripts('notes/notes.js', 'public/assets/notes/js');
    mix.sass('welfare/style.scss', 'public/asset_welfare/css');
    mix.scripts('welfare/postlink.js', 'public/asset_welfare/js');
    mix.scripts('welfare/script.js', 'public/asset_welfare/js');
    mix.scripts('welfare/confirm.js', 'public/asset_welfare/js');
    mix.scripts('welfare/tab_participants_employee.js', 'public/asset_welfare/js');
    mix.scripts('welfare/relation.js', 'public/asset_welfare/js');
    mix.scripts('welfare/script_attach_employee.js', 'public/asset_welfare/js');
    */

    // proposed
    // mix.scripts('proposed/proposed_category.js', 'public/proposed/js');
    // mix.scripts('proposed/proposed_manage.js', 'public/proposed/js');

    //home_message
    // mix.scripts('home_message/home_msg.js', 'public/asset_home_message/js');

    // fines money
    /*
    mix.scripts('fines_money/index.js', 'public/fines_money/js');
    mix.scripts('fines_money/xlsx-func.js', 'public/fines_money/js');
     */

    // Education
    // mix.sass('education/education-request.scss', 'public/education/css');
    // mix.sass('education/education.scss', 'public/education/css');
    // mix.sass('education/register-teaching.scss', 'public/education/css');
    // mix.sass('education/education-ot-style.scss', 'public/education/css');
    // mix.scripts('education/team_scope.js', 'public/education/js');
    // mix.scripts('education/education_request_create.js', 'public/education/js');
    // mix.scripts('education/education_request_list.js', 'public/education/js');
    // mix.sass('education/education.scss', 'public/education/css');
    // mix.scripts('education/education.js', 'public/education/js');
    // manager employee js
    // mix.scripts('education/manager-employee-detail.js', 'public/education/js');
    // mix.scripts('education/manager-employee.js', 'public/education/js');
    // mix.scripts('education/team_scope_search.js', 'public/education/js');
    // mix.scripts('education/education.js', 'public/education/js');
    // mix.scripts('education/register-teaching.js', 'public/education/js');
    // mix.scripts('education/register-teaching-render.js', 'public/education/js');
    // mix.sass('education/education-manager.scss', 'public/education/css');

    //build react js
    //mix.webpack('source', 'dest');

    //mix.webpack('team/react-team-setting.js', 'public/team/js');
    //mix.webpack('team/react-permiss-rule.js', 'public/team/js');
    //mix.webpack('team/react-role-setting.js', 'public/team/js');
    //mix.webpack('team/react-position-setting.js', 'public/team/js');

    //mix.webpack('me/me-edit.js', 'public/me/js');
    //mix.webpack('me/me-team-edit.js', 'public/me/js');
    //mix.webpack('me/me-review.js', 'public/me/js');
    //mix.webpack('me/me-confirm-list.js', 'public/me/js');
    //mix.webpack('me/me-view-member.js', 'public/me/js');

    //mix.webpack('recruitment/react-email-marketing.js', 'public/recruitment/js/email-marketing.js');
});

/**
 * angular
 */
var gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    minifyHTML = require('gulp-htmlmin'),
    ngAnnotate = require('gulp-ng-annotate'),
    plumber = require('gulp-plumber'),
    concat = require('gulp-concat'),
    watch = require('gulp-watch');
var sourceJs = './resources/assets/js/',
    sourceTemplate = './resources/assets/template/',
    destTemplate = './public/';
gulp.task('ng-template', function() {
    RKGulp.template({
        //core
        'common/general.html': 'common/template',

        //tag
        'tag/field-edit.html': 'asset_tag/template',
        'tag/field-manage-tree.html': 'asset_tag/template',
        'tag/field-tag.html': 'asset_tag/template',
        'tag/project-edit.html': 'asset_tag/template',
        'tag/project/basic-info.html': 'asset_tag/template/project',
        'tag/project/team.html': 'asset_tag/template/project',
        'tag/project/scope.html': 'asset_tag/template/project',
        'project/list.html': 'asset_tag/template/project',
        'project/edit-tag.html': 'asset_tag/template/project',
        'project/edit-assignee.html': 'asset_tag/template/project',
        'include/filter.html': 'asset_tag/template',
        'include/pager.html': 'asset_tag/template',
        'tag/project/member-edit.html': 'asset_tag/template/project',
        'tag/project/member-view.html': 'asset_tag/template/project',
        'tag/project/tags-tab.html': 'asset_tag/template/project',

        // qa
        'qa/general.html': 'asset_qa/template'
    });
});
gulp.task('ng-js', function() {
    RKGulp.js('tag/app.js', 'asset_tag/js');
    RKGulp.js('tag/field-manage.js', 'asset_tag/js');
    RKGulp.js('tag/project.js', 'asset_tag/js');
    RKGulp.js('tag/search.js', 'asset_tag/js');

    //core
    RKGulp.js('common/angular/app.js', 'common/js/angular');

    // q and a
    RKGulp.js('qa/scripts.js', 'asset_qa/js');
});
gulp.task('angular', function() {
    gulp.start('ng-template');
    gulp.start('ng-js');
});
var RKGulp = {
    /**
     * mifify template html
     *
     * @param {json} source
     */
    template: function(source) {
        var index;
        for (index in source) {
            var g = gulp.src(
                sourceTemplate + index
            );
            if (RKGulp.arguments.has('--watch')) {
                g = g.pipe(watch(sourceTemplate + index, ['build:html']));
            }
            if (!RKGulp.arguments.has('--dev')) {
                g = g.pipe(minifyHTML({ collapseWhitespace: true }));
            }
            g.pipe(gulp.dest(destTemplate + source[index]));
        }
    },
    /**
     * minify and concat js
     *
     * @param {type} source
     * @param {type} dest
     * @returns {undefined}
     */
    js: function(source, dest, newFile) {
        var index;
        var sourceFullPath = [];
        if (typeof source === 'string') {
            sourceFullPath[0] = sourceJs + source;
        } else {
            for (index in source) {
                sourceFullPath[index] = sourceJs + source[index];
            }
        }
        var g = gulp.src(sourceFullPath);
        if (RKGulp.arguments.has('--watch')) {
            g = g.pipe(watch(sourceFullPath));
        }
        g = g.pipe(plumber());
        if (typeof source !== 'string') {
            g = g.pipe(concat(newFile, { newLine: ';' }));
        }
        g = g.pipe(ngAnnotate({ add: true }))
            .pipe(plumber.stop());
        if (!RKGulp.arguments.has('--dev')) {
            g = g.pipe(uglify({ mangle: true }));
        }
        return g.pipe(gulp.dest(destTemplate + dest));
    },
    arguments: {
        has: function(arg) {
            var arguments = process.argv.slice(3);
            if (arguments.length && arguments.indexOf(arg) >= 0) {
                return true;
            }
            return false;
        }
    }
};