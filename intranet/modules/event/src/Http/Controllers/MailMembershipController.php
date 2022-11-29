<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\View\Permission;
use Exception;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Rikkei\Team\Model\Employee;
use Rikkei\Event\View\MailEmployee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailMembershipController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('admin');
    }

    /**
     * Display task list page
     */
    public function index()
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Manage send mail membership employee');
        return view('event::employee_noti.membership_manage', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('event.mail.membership.employee.content'),
            'subjectEmail' => CoreConfigData::getValueDb('event.mail.membership.employee.subject'),
        ]);
    }
    
    /**
     * send email for customer
     */
    public function save()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $config = (array) Input::get('cc');
        $receive = Input::get('demo');
        $nameInputContent = 'event.mail.membership.employee.content';
        $nameInputSubject = 'event.mail.membership.employee.subject';
        $validator = Validator::make($config, [
            $nameInputContent => 'required',
            $nameInputSubject => 'required',
        ]);
        $response = [];
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Please fill information');
            return response()->json($response);
        }
        // save data subject and content 
        $configItem = CoreConfigData::getItem($nameInputSubject);
        $configItem->value = $config[$nameInputSubject];
        $configItem->save();
        $configItem = CoreConfigData::getItem($nameInputContent);
        $configItem->value = $config[$nameInputContent];
        $configItem->save();
        // action 
        switch (Input::get('preview')) {
            case 2: // send demo
                return $this->sendDemo();
            case 1: // preview
                return $this->preview();
        }
        //save response
        $response['message'] = Lang::get('event::message.Save successful');
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * preview content
     */
    protected function preview()
    {
        $employeeId = Input::get('demo')['employee'];
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Not found employee');
            return response()->json($response);
        }
        $htmlView = view('event::employee_noti.mail.membership',[
            'data' => [
                'employee' => $employee
            ]
        ])->render();
        $dataSubject = MailEmployee::patternsNotiMembership($employee, [
            'subject' => CoreConfigData::getValueDb('event.mail.membership.employee.subject')
        ]);
        $response = [];
        $response['message'] = 'success';
        $response['success'] = 1;
        $response['popup'] = 1;
        $response['html'] = $htmlView;
        $response['subject'] = $dataSubject['subject'];
        return response()->json($response);
    }
    
    /**
     * send mail demo
     */
    protected function sendDemo()
    {
        $employeeId = Input::get('demo')['employee'];
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Not found employee');
            return response()->json($response);
        }
        $dataSubject = MailEmployee::patternsNotiMembership($employee, [
            'subject' => CoreConfigData::getValueDb('event.mail.membership.employee.subject')
        ]);
        $configSenderEmail = CoreConfigData::getEmailAddress();
        $response = [];
        try {
            Mail::send('event::employee_noti.mail.membership', [
                'data' => [
                    'employee' => $employee
                ]
            ], function ($message) use ($employee, $dataSubject, $configSenderEmail)
            {
                $message->from($configSenderEmail['email'], $configSenderEmail['name']);
                $message->to($employee->email, $employee->name);
                $message->subject($dataSubject['subject']);
            });
            $response['message'] = Lang::get('event::message.Mail sent success');
            $response['success'] = 1;
            return response()->json($response);
        } catch (Exception $ex) {
            $response['message'] = Lang::get('event::message.Can not send mail');
            $response['error'] = 1;
            Log::info($ex);
            return $response;
        }
    }
}
