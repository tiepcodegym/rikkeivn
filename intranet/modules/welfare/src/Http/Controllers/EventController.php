<?php
namespace Rikkei\Welfare\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\View;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Form;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Welfare\Model\Organizer;
use Rikkei\Welfare\Model\WelFeeMore;
use Rikkei\Welfare\Model\FormImplements;
use Rikkei\Welfare\Model\GroupEvent;
use Rikkei\Welfare\Model\Purposes;
use Rikkei\Welfare\Model\Event;
use Rikkei\Welfare\Model\WelfareFile;
use Rikkei\Welfare\Model\WelfareParticipantPosition;
use Rikkei\Welfare\Model\WelfarePartner;
use Rikkei\Welfare\Model\WelfareParticipantTeam;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\View\TeamList;
use Rikkei\Welfare\Model\WelfareFee;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Welfare\Model\WelAttachFee;
use Rikkei\Team\Model\Employee;

class EventController extends Controller
{

    /**
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.event.index');
        if (!$permision) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        return view('welfare::event.index', [
            'collectionModel' => Event::getGridData(),
            'status' => Event::getOptionStatus(),
            'titleHeadPage' => Lang::get('welfare::view.Event'),
        ]);
    }

    /**
     *
     * @return Event $event
     */
    public function detail(Request $req)
    {
        $event = Event::getItem($req->id);

        $event->status = htmlentities(Event::getOptionStatus()[$event->status]);
        $event->name = htmlentities($event->name);
        $event->groupName = htmlentities($event->groupName);
        $event->namePur = htmlentities($event->namePur);
        $event->description = str_limit($event->description, 250);
        $event->nameOrg = htmlentities($event->nameOrg);
        $event->convert_end_at_register = $event->end_at_register != null ? $event->end_at_register : "";
        if ($event->namePart == null) {
            $event->namePart = '';
        }

        // Get only date, remove time
        $dateFields = [
            'start_at_exec',
            'end_at_exec',
            'end_at_register',
            'convert_end_at_register',
        ];
        foreach ($dateFields as $field) {
            if (!empty($event->$field)) {
                $event->$field = date('Y-m-d', strtotime($event->$field));
            }
        }

        return $event;
    }

    /**
     * get all common filed
     * @return array
     */
    public function getCommon()
    {
        $filed = array();
        $filed['group_event'] = GroupEvent::getAllItem();
        $filed['purposes'] = Purposes::all();
        $filed['form_imp'] = FormImplements::all();
        return $filed;
    }

    /**
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.event.create');
        if (!$permision) {
            View::viewErrorPermission();
        }

        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.create'));
        Breadcrumb::add('Create');

        $teamTreeHtml = TeamList::getTreeHtml(Form::getData('id'));
        return view('welfare::event.edit', [
            'teamTreeHtml' => $teamTreeHtml,
        ]);
    }

    /**
     *
     * @param int $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function edit($id)
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.event.edit');
        if (!$permision) {
            View::viewErrorPermission();
        }
        Breadcrumb::add('welfare', URL::route('welfare::welfare.event.index'));
        Breadcrumb::add('edit', URL::route('welfare::welfare.event.edit', ['id' => $id]));
        $item = Event::find($id);
        if (!$item) {
            return redirect()->route('welfare::welfare.event.index')->withErrors(Lang::get('welfare::view.No results found'));
        }
        $teamTreeHtml = TeamList::getTreeHtml(Form::getData('id'));
        return view('welfare::event.edit', [
            'item' => $item,
            'welFee' => WelfareFee::getFeeByWelfare($item->id),
            'totalWelFeeMore' => WelFeeMore::totalFeeWelfare($item->id),
            'teamTreeHtml' => $teamTreeHtml,
            'participantPosition' => WelfareParticipantPosition::getParticipantPosition($item->id),
            'welfarePartner' => WelfarePartner::getWelPartner($item->id),
            'partinipantTeam' => WelfareParticipantTeam::getParticipantTeam($item->id),
            'organizer' => DB::table('wel_organizers')->where('wel_id', $item->id)->first(),
            'idEmployees' => WelEmployee::getIdEmployee($item->id),
            'checkEmplOfWelfare' => WelEmployee::checkWel($item->id),
            'FeeAttachRelative' => WelAttachFee::getDataByEventId($item->id),
        ]);
    }

    /**
     * Save infomation of Welfare
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function save(Request $request)
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.event.save');
        if (!$permision) {
            View::viewErrorPermission();
        }
        $eventRe = $request->event;
        $request->flash();
        $messages = [
            'required' => trans('welfare::view.Validate requite message'),
            'max' => trans('welfare::view.Validate max charactor message'),
            'event.end_at_exec.after' => trans('welfare::view.Validate after end_at_exec date message'),
            'event.end_at_register.after' => trans('welfare::view.Validate after end_at_register date message'),
            'event.start_at_register.before' => trans('welfare::view.Validate before date message start end'),
            'event.end_at_register.before' => trans('welfare::view.Validate before date message end'),
            'event.name.unique' => trans('welfare::view.Validate unique message'),
            'event.join_number_plan.numeric' => trans('welfare::view.Please enter a valid number'),
            'event.join_number_plan.min' => trans('welfare::view.Please enter a valid number >0'),
        ];
        $rules = [
            'event.name' => 'required|unique:welfares,name,'.$request->event['id'].',id,deleted_at,NULL|max:255',
            'event.start_at_exec' => 'required',
            'event.end_at_exec' => 'required|after:event.start_at_exec',
            'event.start_at_register' => 'before:event.end_at_register',
            'event.end_at_register' => 'before:event.end_at_exec',
            'event.join_number_plan' => 'numeric|min:0',
        ];
        $validations = Validator::make($request->all(), $rules, $messages);
        if ($validations->fails()) {
            return redirect()->back()->withInput()->withErrors($validations->messages());
        }

        if (isset($eventRe['is_same_fee']) && $eventRe['is_same_fee'] == 1) {
            $eventRe['empl_trial_fee'] = $eventRe['empl_offical_fee'];
            $eventRe['empl_trial_company_fee'] = $eventRe['empl_offical_company_fee'];
        }

        // Save general information of the event
        if (isset($request->event['id']) && $request->event['id'] != '') {
            $welId = $request->event['id'];
            $event = Event::find($welId);
            if (!$request->is_register_online) {
                $event->is_register_online = Event::NOT_REISTER_ONLINE;
            } else {
                $event->is_register_online = Event::IS_REGISTER_ONLINE;
            }
            $edit = true;
        } else {
            $event = new Event();
            $event->is_register_online = Event::IS_REGISTER_ONLINE;
            $edit = false;
        }
        $event->fill($eventRe);
        if (isset($request->event['is_allow_attachments']) && $request->event['is_allow_attachments']) {
            $event->is_allow_attachments = Event::IS_ATTACHED;
            $isAttached = true;
        } else {
            $event->is_allow_attachments = Event::NOT_ATTACHED;
            $isAttached = false;
        }

        try {
            $event->save();
        } catch (Exception $ex) {
            return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
        }

        $welid = $event->id;
        // Check value employee offical after date
        if ($edit) {
            $oldDate = WelfareFee::getEmplOfficalAfterDate($welid) != null ?
                Carbon::createFromFormat('Y-m-d', WelfareFee::getEmplOfficalAfterDate($welid))->setTime(00, 00, 00) :
                Carbon::createFromFormat('Y-m-d', '2000-01-01')->setTime(00, 00, 00);
            $requestDate = $request->wel_fee['empl_offical_after_date'] != "" ?
                Carbon::createFromFormat('Y-m-d', $request->wel_fee['empl_offical_after_date'])->setTime(00, 00, 00) :
                Carbon::createFromFormat('Y-m-d', '2000-01-01')->setTime(00, 00, 00);
            if ($oldDate->ne($requestDate)) {
                DB::beginTransaction();
                try {
                    WelEmployee::where('wel_id', $welid)->update([
                        'is_confirm' => WelEmployee::UN_CONFIRM,
                        'cost_employee' => 0,
                        'cost_company' => 0,
                    ]);
                    DB::commit();
                } catch (Exception $ex) {
                    throw $ex;
                    DB::rollback();
                }
            }
        }

        if (isset($request->wel_fee) && $request->wel_fee) {
            foreach ($request->wel_fee as $key => $item) {
                $welFee[$key] = str_replace(',', '', $item);
            }
            WelfareFee::saveWelFee($welid, $welFee, $isAttached);
        }

        if (isset($request->event_team_id) && $request->event_team_id) {
            WelfareParticipantTeam::saveEvent($welid, [$request->event_team_id]);
        }

        if (isset($request->event['id']) && $request->event['id'] != '') {

            // Add extra items
            if (isset($request->wel_fee_more) && $request->wel_fee_more) {
                foreach ($request->wel_fee_more as $key => $val) {
                    if ($val['cost'] != '' || $val['name'] != '') {
                        $welfeemore = new WelFeeMore();
                        $val['cost'] = filter_var($val['cost'], FILTER_SANITIZE_NUMBER_FLOAT);
                        $val['wel_id'] = $welid;
                        $welfeemore->fill($val);
                        $welfeemore->save();
                    }
                }
            }

            // Add partner implementation for the event
            if (isset($request->wel_partner['partner_id']) && $request->wel_partner['partner_id'] != '') {
                $welfarePartner = WelfarePartner::getWelPartner($event->id);

                if (!$welfarePartner) {
                    $welfarePartner = new WelfarePartner();
                    $welfarePartner->wel_id = $event->id;
                }

                $welfarePartner->fill($request->wel_partner);
                $welfarePartner->email = $request->wel_partner['rep_email'];
                $welfarePartner->fee_return = str_replace(',', '', $request->wel_partner['fee_return']);
                try {
                    $welfarePartner->save();
                } catch (Exception $ex) {
                    return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
                }
            }

            // Add the organizer to the event
            $wel_organizer = $request->wel_organizer;
            $wel_organizer['wel_id'] = $event['id'];

            if ($wel_organizer['id'] != '') {
                $organizer = Organizer::find($wel_organizer['id']);
            } else {
                $organizer = new Organizer();
            }
            $organizer->fill($wel_organizer);
            try {
                $organizer->save();
            } catch (Exception $ex) {
                return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
            }

        }
        
        //save attach employee fee
       
        if(isset($request->event['is_allow_attachments']) 
            && $request->event['is_allow_attachments'] == Event::IS_ATTACHED) {
            $saveWelAttachFee = WelAttachFee::FeeAttach($request->wel_fee_att,$event->id); 
        }
        $messageSuccess = [
            'success' => [
                Lang::get('team::messages.Save data success!'),
            ]
        ];
        return redirect()->route('welfare::welfare.event.edit', $event->id)->with('messages', $messageSuccess);

    }

    /**
     * Upload file for Welfare
     *
     * @param Request $request
     * @return array
     */
    public function uploadFile(Request $request)
    {

        $extension = ['doc', 'docx', 'jpeg', 'png', 'pdf', 'zip', 'jpg'];
        $totalSizeFile = 0;
        if ($request->hasFile('wel_file')) {
            foreach ($request->file('wel_file') as $file) {
                if (!in_array(strtolower($file->getClientOriginalExtension()), $extension)) {
                    return [
                        'status' => false,
                        'msg' => trans('welfare::view.Please upload valid file formats'),
                    ];
                }
                $totalSizeFile = $totalSizeFile + filesize($file);
            }
        }
        $oldFile = WelfareFile::where('wel_id', $request->wel_id)->get();
        $count = 0;
        foreach ($oldFile as $item) {
            $count += Storage::size(WelfareFile::ACCESS_FILE . '/' . $item->files);
        }

        $event = Event::find($request->wel_id);

        if (!$event) {
            return [
                'status' => false,
                'msg' => trans('welfare::view.Not Found Welfare'),
            ];
        }

        if ($request->hasFile('wel_file')) {
            if($totalSizeFile + $count < Event::MAX_FILE_SIZE_UPLOAD) {
                foreach ($request->file('wel_file') as $file) {

                    $welFile = new WelfareFile();
                    if (isset($file)) {
                        $filename = sprintf('%s_%s', uniqid(), $file->getClientOriginalName());
                        Storage::put(WelfareFile::ACCESS_FILE . '/' . $filename, file_get_contents($file->getRealPath()));
                        $welFile->event()->associate($event->id);
                        $welFile->files = $filename;
                        try {
                            $welFile->save();
                        } catch (Exception $ex) {
                            Storage::delete(WelfareFile::ACCESS_FILE . '/' . $filename, file_get_contents($file->getRealPath()));
                            return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
                        }
                    }
                }
                return [
                    'status' => true,
                ];
            } else {
                return ['status'=>null];
            }
        }
    }

    /**
     * Send mail register Event
     *
     * @param Request $request
     * @return array
     */
    public function sendMail(Request $request)
    {
        $welId = $request->email['wel_id'];
        $event = Event::find($welId);

        if (!$event) {
            return redirect()->route('welfare::welfare.event.index')->withErrors(trans('welfare::view.Not Found Welfare'));
        }
        $endAtExec = isset($event->end_at_register) ?  Carbon::createFromFormat('Y-m-d H:i:s', $event->end_at_register)
                        : Carbon::createFromFormat('Y-m-d H:i:s', '0000-00-00 00:00:00');

        if ($endAtExec->lte(Carbon::now())) {
            return response()->json([
                'success' => 0,
                'message' => Lang::get('welfare::view.Event registration time has expired'),
            ]);
        }
        $validator = Validator::make($request->email, [
                'subject' => 'required',
                'content' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->route('welfare::welfare.event.edit', ['id' => $welId])->withErrors($validator);
        }

        $data = [];
        $data['welId'] = $welId;
        $data['subject'] = $request->email['subject'];
        $data['content'] = $request->email['content'];
        $welFile = WelfareFile::getFileByEvent($welId);
        if (isset($welFile) && count($welFile)) {
            foreach ($welFile as $key) {
                $path = storage_path('app/' . WelfareFile::ACCESS_FILE . '/' . $key->files);
                if (isset($path)) {
                    $data['attachment'][] = $path;
                }
            }
        }
        $listEmail = array_unique(WelEmployee::getEmailEmplByWelId($welId));
        $numberMunute = (int) CEIL(count($listEmail)/30);

        if (1 <= count($listEmail) && count($listEmail) <= 15) {
            $data['email'] = $listEmail[0];
            unset($listEmail[0]);

            foreach ($listEmail as $key) {
                $data['email_bcc'][] = $key;
            }

            $this->pushEmailToQueue($data);
        } elseif (count($listEmail) > 15) {
            $array = [];
            $emailNotify = [];
            $emailGr = array_chunk($listEmail, 15);

            foreach ($emailGr as $key => $value) {
                $data['email'] = $value[0];
                unset($value[0]);
                $data['email_bcc'] = $value;
                $array[] = $this->pushEmailToArray($data);
            }

            foreach ($emailGr as $value) {
                foreach ($value as $item) {
                    $emailNotify[] = $item;
                }
            }
            $listEmpSendNotify = [];
            $listEmpSendNotify = Employee::getEmpByEmails($emailNotify);
            \RkNotify::put($listEmpSendNotify->lists('id')->toArray(), $data['subject'], route("welfare::welfare.confirm.welfare", ['id' => $data['welId']]), ['category_id' => RkNotify::CATEGORY_PROJECT]);
            EmailQueue::insert($array);
        }

        return [
            'ok' => 'ok',
            'message' => Lang::get('welfare::message.Email sent in :number minutes', ['number' => $numberMunute])
        ];
    }

    /**
     * Delete Attachments of Welfare
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteFile(Request $request)
    {
        $id = $request->id;
        $wel_file = WelfareFile::find($id);
        $pathFile = WelfareFile::ACCESS_FILE . '/' . $wel_file->files;

        $wel_file->delete();
        Storage::delete($pathFile);
        return [
            'status' => true,
        ];
    }

    /*
     * Function delete event
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete()
    {
        $permision = Permission::getInstance()->isAllow(null, 'welfare::welfare.event.delete');
        if (!$permision) {
            View::viewErrorPermission();
        }
        $id = Input::get('id');
        $model = Event::find($id);
        if (!$model) {
            return redirect()->route('welfare::welfare.event.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        DB::beginTransaction();
        try {
            $model->delete();
            WelEmployee::where('wel_id',$id)->delete();
            WelfareFee::where('wel_id',$id)->delete();
            WelFeeMore::where('wel_id',$id)->delete();
            WelfareFile::where('wel_id',$id)->delete();
            Organizer::where('wel_id',$id)->delete();
            WelfarePartner::where('wel_id',$id)->delete();
            WelEmployeeAttachs::where('welfare_id',$id)->delete();
            DB::commit();
        } catch(Exception $ex) {
            throw $ex;
            DB::rollback();
        }

        $messages = [
            'success' => [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->route('welfare::welfare.event.index')->with('messages', $messages);
    }

    /**
     *
     * @param array $data
     * @param string $template
     * @return boolean
     */
    public function pushEmailToQueue($data, $template = 'welfare::template.mail')
    {
        $subject = $data['subject'];
        $emailNotify = [];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['email'])
            ->setSubject($subject)
            ->setTemplate($template, $data);
        $emailNotify[] = $data['email'];
        if (isset($data['email_bcc'])) {
            foreach ($data['email_bcc'] as $key) {
                $emailQueue->addBcc($key);
                $emailNotify[] = $key;
            }
        }
        if (isset($data['attachment'])) {
            foreach ($data['attachment'] as $key) {
                $emailQueue->addAttachment($key, false);
            }
        }

        // get employee send mail follow list email.
        $listEmp = Employee::getEmpByEmails($emailNotify);
        try {
            $emailQueue->save();
            \RkNotify::put($listEmp->lists('id')->toArray(), $subject, route("welfare::welfare.confirm.welfare", ['id' => $data['welId']]), ['category_id' => RkNotify::CATEGORY_PROJECT]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function pushEmailToArray($data)
    {
        $template = 'welfare::template.mail';
        $subject = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['email'])
            ->setSubject($subject)
            ->setTemplate($template, $data);
        if (isset($data['email_bcc'])) {
            foreach ($data['email_bcc'] as $key) {
                $emailQueue->addBcc($key);
            }
        }
        if (isset($data['attachment'])) {
            foreach ($data['attachment'] as $key) {
                $emailQueue->addAttachment($key, false);
            }
        }

        return $emailQueue->getValue();
    }

    /**
     * Preview content email event welfare
     *
     * @param Request $request
     * @return array
     */
    public function previewMail(Request $request)
    {
        $data['content'] = $request->content;
        $html = view('welfare::template.mail', compact('data'))->render();
        return [
            'html' => $html,
        ];
    }

    public function registerOnline(Request $request) {
        $event = Event::find($request->welfare_id);
        $data['type'] = $request->is_register_online;
        if($event) {
            $event->is_register_online = $request->is_register_online;
            $event->save();
            $data['status'] = true;
            return response()->json($data);
        }
        $data['status'] = false;
        return response()->json($data);
    }

    /**
     * Send an email reminding event registration
     * int $id
     */
    public function sendMailNotify($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return redirect()->route('welfare::welfare.event.index')->withErrors(trans('welfare::view.Not Found Welfare'));
        }
        $endAtExec = isset($event->end_at_register) ?  Carbon::createFromFormat('Y-m-d H:i:s', $event->end_at_register)
            : Carbon::createFromFormat('Y-m-d H:i:s', '0000-00-00 00:00:00');

        if ($endAtExec->lte(Carbon::now())) {
            return response()->json([
                'status' => false,
                'message' => Lang::get('welfare::view.Event registration time has expired'),
            ]);
        }

        $data = [];
        $data['welId'] = $id;
        $data['subject'] = trans('welfare::view.Remind event registration');
        $data['title'] = trans('welfare::view.The event below is still available for registration. If you want to register for the event, please visit the link below and confirm.');
        $data['link'] = route("welfare::welfare.confirm.welfare", ['id' => $data['welId']]);
        $listEmail = WelEmployee::getEmpUnConfirm($id);
        $numberMinute = (int) CEIL(count($listEmail)/30);

        if (1 <= count($listEmail) && count($listEmail) <= 15) {
            $data['email'] = $listEmail[0];
            unset($listEmail[0]);


            foreach ($listEmail as $key) {
                $data['email_bcc'][] = $key;
            }

            $this->pushEmailToQueue($data, 'welfare::template.mail_confirm');
        } elseif (count($listEmail) > 15) {
            $array = [];
            $emailNotify = [];
            $emailGr = array_chunk($listEmail, 15);

            foreach ($emailGr as $key => $value) {
                $data['email'] = $value[0];
                unset($value[0]);
                $data['email_bcc'] = $value;
                $array[] = $this->pushEmailToArray($data);
            }

            foreach ($emailGr as $value) {
                foreach ($value as $item) {
                    $emailNotify[] = $item;
                }
            }
            $listEmpSendNotify = [];
            $listEmpSendNotify = Employee::getEmpByEmails($emailNotify);
            \RkNotify::put($listEmpSendNotify->lists('id')->toArray(), $data['subject'], route("welfare::welfare.confirm.welfare", ['id' => $data['welId']]), ['category_id' => RkNotify::CATEGORY_PERIODIC]);
            EmailQueue::insert($array);
        }

        return [
            'status' => true,
            'message' => Lang::get('welfare::message.Email sent in :number minutes', ['number' => $numberMinute])
        ];
    }

}
