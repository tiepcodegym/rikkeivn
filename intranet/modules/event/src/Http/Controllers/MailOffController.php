<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Employee;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Event\View\MailEmployee;
use Illuminate\Support\Facades\DB;
use Session;

class MailOffController extends Controller
{

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('admin');
        Breadcrumb::add(trans('event::view.send_mail_off_title'));
    }

    /**
     * view upload emails file
     * @return view
     */
    public function upload()
    {
        Session::forget(ViewEvent::KEY_CACHE_MAILOFF . Session::getId());
        return view('event::mail-off.index', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('it.email_content.mailoff'),
            'subjectEmail' => CoreConfigData::getValueDb('it.email_subject.mailoff')
        ]);
    }

    /**
     * show data in file
     * @param Request $request
     * @return type
     */
    public function confirmMail(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'csv_file' => 'required|file|max:5120',
            'content' => 'required',
            'subject' => 'required'
        ]);

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = $request->file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, ['csv', 'xlsx', 'xls'])) {
            return redirect()->back()
                ->withErrors(trans('core::message.Only allow file excel'));
        }

        $content = $request->get('content');
        $subject = $request->get('subject');
        //save subject and title
        $configContent = CoreConfigData::getItem('it.email_content.mailoff');
        $configContent->value = $content;
        $configContent->save();
        $configSubject = CoreConfigData::getItem('it.email_subject.mailoff');
        $configSubject->value = $subject;
        $configSubject->save();

        try {
            $dataCollect = [];
            $arrayAvailidEmails = Employee::select(DB::raw('LOWER(email) as email'))->lists('email')->toArray();
            $errors = [];
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader)  use (
                &$dataCollect,
                &$errors,
                $arrayAvailidEmails
            ) {
                $data = $reader->get();
                if ($data->isEmpty()) {
                    throw new Exception(trans('event::message.None item read'), 422);
                }
                foreach ($data as $idx => $row) {
                    $rowEmail = strtolower(trim($row->email));
                    if (!$row->email) {
                        continue;
                    }
                    if (!in_array($rowEmail, $arrayAvailidEmails)) {
                        $errors[] = trans('event::message.email_not_exists_in_system', ['row' => $idx + 1, 'email' => $rowEmail]);
                    }
                    if (isset($dataCollect[$rowEmail])) {
                        $errors[] = trans('event::message.email_duplicate_in_file', ['row' => $idx + 1, 'email' => $rowEmail]);
                    }
                    $rowPass = trim($row->password);
                    $dataCollect[$rowEmail] = [
                        'password' => $rowPass
                    ];
                }
            });
            if ($errors) {
                return redirect()->back()
                        ->withInput()
                        ->with('messages', ['errors' => $errors]);
            }

            $dataEmployees = MailEmployee::getEmpWithLeaderByEmails(array_keys($dataCollect));
            $emailByLeaders = [];
            if (!$dataEmployees->isEmpty()) {
                $dataEmployees = $dataEmployees->sortByDesc('ld_id');
                foreach ($dataEmployees as $emp) {
                    if (!isset($emailByLeaders[$emp->team_id])) {
                        $emailByLeaders[$emp->team_id] = [];
                    }
                    $aryEmp = $emp->toArray();
                    $leaderIds = explode(',', $aryEmp['ld_id']);
                    $leaderName = $aryEmp['ld_name'];
                    $leaderEmail = $aryEmp['ld_email'];
                    if (count($leaderIds) > 1) {
                        $aryEmp['ld_id'] = $leaderIds[0];
                        $aryEmp['ld_name'] = explode(',', $leaderName)[0];
                        $aryEmp['ld_email'] = explode(',', $leaderEmail)[0];
                    }
                    $aryEmp['password'] = isset($dataCollect[$emp->email]) ? $dataCollect[$emp->email]['password'] : null;
                    $emailByLeaders[$emp->team_id][] = $aryEmp;
                }
            }
            Session::put(ViewEvent::KEY_CACHE_MAILOFF . Session::getId(), $emailByLeaders);

            return view('event::mail-off.confirm-mail', compact('emailByLeaders', 'dataCollect'));
        } catch (Exception $ex) {
            Log::info($ex);
            $message = trans('event::message.Error system, please try again');
            if ($ex->getCode() == 422) {
                $message = $ex->getMessage();
            }
            return redirect()->back()
                    ->withInput()
                    ->with('messages', ['errors' => [$message]]);
        }
    }

    /**
     * send email
     * @return void
     */
    public function sendMail()
    {
        $emailByLeaders = Session::get(ViewEvent::KEY_CACHE_MAILOFF . Session::getId());
        if (isset($emailByLeaders[''])) {
            unset($emailByLeaders['']);
        }
        if (!$emailByLeaders) {
            return redirect()->route('event::mailoff.upload')
                    ->with('messages', ['errors' => [trans('event::message.None item read')]]);
        }

        DB::beginTransaction();
        $emailSubject = CoreConfigData::getValueDb('it.email_subject.mailoff');
        try {
            foreach ($emailByLeaders as $dataLeader) {
                if (!$dataLeader[0]['ld_id']) {
                    continue;
                }
                $emailData = [
                    'leaderName' => $dataLeader[0]['ld_name'],
                    'leaderEmail' => $dataLeader[0]['ld_email'],
                    'empEmails' => $dataLeader
                ];

                $emailQueue = new EmailQueue();
                $emailQueue->setTo($dataLeader[0]['ld_email'])
                        ->setTemplate('event::mail-off.mail-leader', $emailData)
                        ->setSubject($emailSubject)
                        ->setNotify($dataLeader[0]['ld_id'], $emailSubject . ' (' . trans('notify::view.Detail in mail') . ')', null, ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE])
                        ->save();
            }
            Session::forget(ViewEvent::KEY_CACHE_MAILOFF . Session::getId());
            DB::commit();

            return redirect()->route('event::mailoff.upload')
                    ->with('messages', ['success' => [trans('event::message.Mail sent success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()->route('event::mailoff.upload')
                    ->with('messages', ['errors' => [trans('event::message.Error system, please try again')]]);
        }
    }

}