<?php

//Route::get('send/email/employees/tet-bonuses', 'SendEmailController@tetBonuses')
//    ->name('send.email.employees.tet.bonuses');
//Route::post('tet-bonuses/post', 'SendEmailController@tetBonusesPost')
//    ->name('send.email.employees.tet.bonuses.post');
Route::group(['middleware' => 'localization', 'prefix' => Session::get('app.locale')], function() {
    Route::group([
        'prefix' => 'event'
    ], function () {

        //Route::get('send/email/employees/tet-bonuses', 'SendEmailController@tetBonuses')
        //    ->name('send.email.employees.tet.bonuses');
        //Route::post('tet-bonuses/post', 'SendEmailController@tetBonusesPost')
        //    ->name('send.email.employees.tet.bonuses.post');

        Route::get('eventday/company/register/{token}', 'EventdayController@register')
            ->name('eventday.register');
        Route::post('eventday/company/register/post/{token}', 'EventdayController@registerPost')
            ->name('eventday.register.post');
        Route::get('eventday/company/register/success/{token}', 'EventdayController@registerSuccess')
            ->name('eventday.register.success');
        Route::get('eventday/company/refuse/{token}', 'EventdayController@refuse')
            ->name('eventday.refuse');

        Route::get('eventday/download-template', 'EventdayController@downloadTemplate')->name('eventday.download_template');
        Route::group([
            'middleware' => 'auth',
        ], function () {
            Route::get('eventday/company', 'EventdayController@create')
                ->name('eventday.create');
            Route::get('eventday/company/list', 'EventdayController@listInvi')
                ->name('eventday.company.list');
            Route::post('eventday/company/send/email', 'EventdayController@sendEmail')
                ->name('eventday.send.email');
            Route::get('eventday/export', 'EventdayController@export')
                ->name('eventday.export');

            Route::get('eventday/customer/create', 'EventdayController@createCustomer')
                ->name('eventday.customer.create');
            Route::post('eventday/customer/insert', 'EventdayController@insertCustomer')
                ->name('eventday.customer.insert');
            Route::get('eventday/customer/edit/{id}', 'EventdayController@editCustomer')
                ->name('eventday.customer.edit')->where('id', '[0-9]+');
            Route::post('eventday/customer/update/{id}', 'EventdayController@updateCustomer')
                ->name('eventday.customer.update')->where('id', '[0-9]+');
            Route::delete('eventday/customer/delete/{id}', 'EventdayController@deleteCustomer')
                ->name('eventday.customer.delete')->where('id', '[0-9]+');


            Route::get('brithday/company', 'BirthdayController@create')
                ->name('brithday.create');
            Route::get('brithday/company/list', 'BirthdayController@listInvi')
                ->name('brithday.company.list');
            Route::post('brithday/company/send/email', 'BirthdayController@sendEmail')
                ->name('brithday.send.email');
            Route::get('brithday/company/cust/list', 'BirthdayController@listEmailCust')->name('brithday.company.email_cust.list');
            Route::get('brithday/company/download-template', 'BirthdayController@downloadTemplate')->name('brithday.download_template');
            Route::post('brithday/company/list/export', 'BirthdayController@export')->name('brithday.company_list.export');

            // send email to employee event
            Route::group([
                'as' => 'send.email.employees.',
                'prefix' => 'send/email/employees',
            ], function () {
                Route::get('tet-bonuses', 'SendEmailController@tetBonuses')
                    ->name('tet.bonuses');
                Route::post('tet-bonuses/post', 'SendEmailController@tetBonusesPost')
                    ->name('tet.bonuses.post');
                Route::get('to-male', 'SendEmailController@toMale')
                    ->name('to.male');
                Route::post('to.male/post', 'SendEmailController@toMalePost')
                    ->name('to.male.post');

                Route::get('total-timekeeping', 'SendEmailController@totalTimekeeping')
                    ->name('total.timekeeping');
                Route::post('total-timekeeping/post', 'SendEmailController@totalTimekeepingPost')
                    ->name('total.timekeeping.post');

                Route::get('tax', 'MailTaxController@tax')->name('tax');
                Route::post('tax', 'MailTaxController@postTax')->name('post.tax');
                Route::get('tax/show-data', 'MailTaxController@showTaxData')->name('show.tax');
                Route::post('tax/send-email', 'MailTaxController@sendTaxEmail')->name('send_mail.tax');
                Route::delete('tax/delete-temp-data', 'MailTaxController@deleteTaxTempData')->name('delete_temp.tax');
                Route::get('tax-files', 'MailTaxController@listFiles')->name('tax.list_files');
                Route::get('tax-files/{id}/detail', 'MailTaxController@mailSentDetail')
                    ->where('id', '[0-9]+')
                    ->name('tax.mail_detail');

                Route::get('fines', 'SendEmailController@fines')
                    ->name('fines');
                Route::post('fines/post', 'SendEmailController@finesPost')
                    ->name('fines.post');

                Route::get('salary', 'MailSalaryController@salary')->name('salary');
                Route::post('salary', 'MailSalaryController@postSalary')->name('post.salary');
                Route::get('salary/show-data', 'MailSalaryController@showSalaryData')->name('show.salary');
                Route::post('salary/send-email', 'MailSalaryController@sendSalaryEmail')->name('send_mail.salary');
                Route::delete('salary/delete-temp-data', 'MailSalaryController@deleteSalaryTempData')->name('delete_temp.salary');
                Route::get('salary-files', 'MailSalaryController@listFiles')->name('salary.list_files');
                Route::get('salary-files/{id}/detail', 'MailSalaryController@mailSentDetail')
                    ->where('id', '[0-9]+')
                    ->name('salary.mail_detail');
                Route::post('salary/send-password', 'MailSalaryController@sendPassword')->name('salary.send_pass');
                Route::post('salary/send-exists-password', 'MailSalaryController@sendExistsPassword')->name('salary.send_exists_pass');

                // mail custom to any
                Route::match(['get', 'post'], 'compose', 'SendController@compose')
                    ->name('compose');

                // mail forgot turn-off
                Route::get('forgot-turn-off', 'SendEmailController@forgotTurnOff')
                    ->name('forgotTurnOff');
                Route::post('forgot-turn-off/post', 'SendEmailController@forgotTurnOffPost')
                    ->name('turnoff.post');
            });

            // email ngay nghi nhan vien
            Route::get('mail-sabbatical-days', 'SendEmailController@getUploadSabbFile')
                ->name('sabb.get_upload_file');
            Route::post('mail-sabbatical-days/post', 'SendEmailController@postUploadSabbFile')
                ->name('sabb.post_upload_file');

            // mail manage birth employee event::mail.birthday.employee.*
            Route::group([
                'as' => 'mail.birthday.employee.',
                'prefix' => 'mail/birth/employee',
            ], function () {
                Route::get('/', 'MailBirthEmployeeController@index')
                    ->name('index');
                Route::post('save', 'MailBirthEmployeeController@save')
                    ->name('save');
            });
            // mail manage membership employee event::mail.membership.employee.*
            Route::group([
                'as' => 'mail.membership.employee.',
                'prefix' => 'mail/membership/employee',
            ], function () {
                Route::get('/', 'MailMembershipController@index')
                    ->name('index');
                Route::post('save', 'MailMembershipController@save')
                    ->name('save');
            });
        });

        Route::get('brithday/company/register/{token}', 'BirthdayController@register')
            ->name('brithday.register');
        Route::post('brithday/company/register/post/{token}', 'BirthdayController@registerPost')
            ->name('brithday.register.post');
        Route::get('brithday/company/register/success/{token}', 'BirthdayController@registerSuccess')
            ->name('brithday.register.success');
        Route::get('brithday/company/refuse/{token}', 'BirthdayController@refuse')
            ->name('brithday.refuse');
        Route::post('brithday/company/check-email-excel', 'BirthdayController@checkEmailExcel')->name('brithday.check_email_excel');
        Route::post('brithday/company/send/email/count-file', 'BirthdayController@sendEmailCountFile')->name('brithday.send.email.count_file');
        Route::post('brithday/company/send/email/process-file', 'BirthdayController@sendEmailProcessFile')->name('brithday.send.email.process_file');
        Route::post('brithday/company/send/email/reset-mail', 'BirthdayController@sendEmailResetMail')->name('brithday.send.email.reset_mail');

        // not auth
        Route::group([
            'middleware' => 'logged',
            'as' => 'send.email.employees.',
            'prefix' => 'send/email/employees',
        ], function () {
            //Route::match(['get', 'post'], 'timesheet/to/fines', 'SendEmailController@tsToFines')
            //    ->name('ts.to.fines');

            Route::get('passwords', 'MailSalaryController@getPasswords')->name('get_passwords');
            Route::post('passwords', 'MailSalaryController@showPasswords')->name('show_passwords');
            Route::post('reset-password', 'MailSalaryController@resetPassword')->name('reset_password');
        });
    });

    Route::group([
        'prefix' => 'mail-off',
        'middleware' => 'auth',
        'as' => 'mailoff.'
    ], function () {
        Route::get('upload', 'MailOffController@upload')->name('upload');
        Route::post('confirm-send-mail', 'MailOffController@confirmMail')->name('confirm_mail');
        Route::post('send-mail', 'MailOffController@sendMail')->name('sendmail');
    });
});
