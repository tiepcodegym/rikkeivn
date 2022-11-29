<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\View\Permission;
use Rikkei\Event\Model\EventBirthday;
use Exception;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\View\OptionCore;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use DB;

class EventdayController extends Controller
{

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('sales');
    }

    /**
     * Display task list page
     */
    public function create()
    {
        $currentUser = Permission::getInstance()->getEmployee();
        Breadcrumb::add('Event', route('event::eventday.company.list'));
        return view('event::eventday.create', [
            'userCurrent' => $currentUser,
            'contentEmailVn' => CoreConfigData::getValueDb('event.eventday.company.content.vn'),
            'subjectEmailVn' => CoreConfigData::getValueDb('event.eventday.company.subject.vn'),
            'userName' => $currentUser->name ? $currentUser->name : 'Rikkeisoft Event',
            'userEmail' => $currentUser->email ? $currentUser->email : 'event@rikkeisoft.com',
        ]);
    }

    /**
     * send email for customer
     */
    public function sendEmail()
    {
        $config = Input::get('cc');
        $receive = Input::get('item');
        $sender = Input::get('exclude');
        $fileImport = Input::file('excel_file');

        $nameInputSubject = 'event.eventday.company.subject.vn';
        $nameInputContent = 'event.eventday.company.content.vn';

        // Validate
        $requestValidate = [
            'email_from_name' => 'required',
            'email_from' => 'required',
            $nameInputSubject => 'required',
            'excel_file' => 'required',
        ];
        
        $receive['excel_file'] = $fileImport;
        $validator = Validator::make(array_merge($config, $receive, $sender), $requestValidate);
        if ($validator->fails()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('event::message.Please fill information')]]);
        }
        $empInfo = Employee::where('email', $sender['email_from'])->first();
        if (!$empInfo) {
            return redirect()->back()->with('messages', ['errors' => ['Email gửi buộc phải tồn tại trong hệ thống rikkei.vn']]);
        }

        // Read data from excel
        $dataRecord = null;
        if ($fileImport) {
            try {
                $rowCount = 0;
                $dataReader = null;
                Excel::selectSheetsByIndex(0)->load($fileImport->getRealPath(), function ($reader) use (&$rowCount, &$dataReader){
                    $rowCount = $reader->get()->count();
                    $dataReader = $reader;
                });
    
                $errors = [];
                $count = 0;
                $this->excuteFile($dataReader, $errors, $count);
                if ($errors) {
                    return redirect()->back()->with('messages', ['errors' => $errors]);
                }
                $dataRecord = $dataReader->get();
            } catch (\Exception $ex) {
                Log::info($ex);
                return redirect()->back()->with('messages', ['errors' => 
                    ['Có lỗi xảy ra']
                ]);
            }
        }

        // Add mail to queue
        $queueData = [];
        foreach ($dataRecord as $key => $value) {
            $receive = [
                'company' => $value['company'],
                'name' => $value['customer'],
                'email' => $value['email'],
            ];
           
            $patternsArray = [
                '/\{\{\sreceiveCompanyName\s\}\}/',
                '/\{\{\sreceiveName\s\}\}/',
                '/\{\{\slinkRegister\s\}\}/',
                '/\{\{\slinkRefuse\s\}\}/',
                '/\{\{\surlSite\s\}\}/',
            ];

            $replacesArray = [
                $receive['company'],
                $receive['name'],
            ];
            $contentEmail = CoreConfigData::getValueDb($nameInputContent);
            $config[$nameInputContent] = preg_replace($patternsArray, $replacesArray, $contentEmail);

            $dataContent = Input::get('mail_content');
            try {
                // $data = [
                //     'cus_lang' => Input::get('customer_lang')
                // ];
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($receive['email'])
                    ->setFrom(config('mail.username'), $sender['email_from_name'])
                    ->addReply('contact@rikkeisoft.com')
                    ->setSubject($config[$nameInputSubject])
                    // ->setTemplate('event::eventday.email.general', [
                    //     'customer_name' => $receive['name'],
                    //     'company_name' => $receive['company'],
                    // ]);
                    // ->setTemplate('event::eventday.email.general_v3', $data); //Delete customer_lang o event::eventday.customer.create 
                    ->setTemplate('event::eventday.email.general_ckeditor', [
                        'content' => $dataContent
                    ]);
                $queueData[] = $emailQueue->getValue();
            } catch (Exception $ex) {
                 return redirect()->back()->with('messages', ['errors' => [Lang::get('event::message.Error save data')]]);
            }
        }

        DB::beginTransaction();
        try {
            if (count($queueData)) {
                EmailQueue::insert($queueData);
                DB::commit();
                return redirect()->back()->with('messages', ['success' => ['Send mail successful']]);
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return redirect()->back()->with('messages', ['errors' => 
                    ['Có lỗi xảy ra. Tất cả email sẽ không được gửi đi.']
                ]);
        }
        
        
    }
    
    private function excuteFile($reader, &$errors, &$count){
        $dataRecord = $reader->get();        
        $data = [];
        foreach ($dataRecord as $key => $itemRow) {
            $keyItem = $key + 2;
            if (strlen($itemRow->email) == 0) {
                $errors[] = 'Row '.$keyItem. ' missing Email';
                continue;
            }
            //Email
            // $regex = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+\.[A-Za-z]{2,6}$/";
            // if(preg_match($regex, $itemRow->email) != 1) { 
            //     $errors[] = 'Row '.$keyItem. ' - (email) format is invalid.';
            //     continue;
            // }
        }
    }

    public function downloadTemplate() {
        $pathToFile = public_path('event/files/Mail_Template_Nagoya.xlsx');
        return response()->download($pathToFile);
    }

    /**
     * preview send email
     */
    public function previewSendEmail($receive, $config, $arrayLang)
    {
        $data = [
            'content' => $config[$arrayLang['content']],
            'type' => $arrayLang['type']
        ];
        $htmlView = view('event::eventday.email.general', $data)->render();
        $htmlView = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $htmlView);
        $response = [];
        $response['message'] = 'success';
        $response['success'] = 1;
        $response['html'] = $htmlView;
        $response['popup'] = 1;
        return response()->json($response);
    }

    /**
     * register
     */
    public function register($token)
    {
        $item = EventBirthday::findItemFollowToken($token);
        if (!$item) {
            return redirect('/');
        }
        return view('event::eventday.register', [
            'customerEvent' => $item,
            'languageView' => View::getKeyOfOptions($item->lang, EventBirthday::toOptionLang())
        ]);
    }

    /**
     * submit register event eventday
     */
    public function registerPost($token)
    {
        $response = [];
        $item = EventBirthday::findItemFollowToken($token);
        if (!$item) {
            $response['message'] = Lang::get('event::message.Error register, please try again');
            $response['error'] = 1;
            return $response;
        }
        $dataItem = Input::get('item');
        unset($dataItem['name']);
        unset($dataItem['email']);
        unset($dataItem['token']);
        unset($dataItem['lang']);
        if ($item->lang == 'vi') {
            $rules = [
                'address' => 'required'
            ];
        } else {
            $rules = [];
        }
        $validator = Validator::make($dataItem, $rules);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Please fill information');
            return response()->json($response);
        }
        $item->setData($dataItem);
        $item->status = EventBirthday::STATUS_YES;
        try {
            $item->save();
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Error system, please try again');
            return response()->json($response);
        }
        $response['message'] = Lang::get('event::message.Register success');
        $response['success'] = 1;
        $response['popup'] = 1;
        $response['refresh'] = URL::route('event::eventday.register.success', ['token' => $token]);
        return response()->json($response);
    }

    /**
     * register success event
     */
    public function registerSuccess($token)
    {
        $item = EventBirthday::findItemFollowToken($token);
        if (!$item) {
            return redirect('/');
        }
        return view('event::eventday.register_success', [
            'customerEvent' => $item,
            'languageView' => View::getKeyOfOptions($item->lang, EventBirthday::toOptionLang())
        ]);
    }

    /**
     * refuse event
     */
    public function refuse($token)
    {
        $item = EventBirthday::findItemFollowToken($token);
        if (!$item) {
            return redirect('/');
        }
        $item->status = EventBirthday::STATUS_NO;
        try {
            $item->save();
        } catch (Exception $ex) {
            
        }
        return view('event::eventday.refuse', [
            'customerEvent' => $item,
            'languageView' => View::getKeyOfOptions($item->lang, EventBirthday::toOptionLang())
        ]);
    }

    /**
     * list
     */
    public function listInvi()
    {
        Breadcrumb::add('Event');
        return view('event::eventday.list', [
            'collectionModel' => EventBirthday::getGridData(),
            'statusOptions' => EventBirthday::toOptionLabelStatus(),
            'yesNoOption' => OptionCore::yesNo(true, false)
        ]);
    }

    public function export()
    {
        set_time_limit(0);

        $collectionModel = EventBirthday::whereNull('deleted_at')->get();
        $statusOptions = EventBirthday::toOptionLabelStatus();
        Excel::create('eventday' . \Carbon\Carbon::now()->now()->format('Y_m_d'), function ($excel) use ($collectionModel, $statusOptions) {
            $excel->sheet('Sheet1', function ($sheet) use ($collectionModel, $statusOptions) {
                $data = [];

                $data[1] = [
                    trans('project::view.No.'),
                    trans('event::view.Customer name'),
                    trans('event::view.Customer mail'),
                    trans('event::view.Company customer'),
                    trans('event::view.Status'),
                    trans('event::view.Attacher'),
                    trans('event::view.Sender name'),
                    trans('event::view.Sender email'),
                    trans('event::view.Note'),
                    trans('event::view.Send at'),
                ];

                $sheet->cells('A1:M1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $currentLine = 1;
                $order = 1;
                $teamName = '';
                foreach ($collectionModel as $item) {
                    if (isset($item->team_name) && (string) $teamName !== (string) $item->team_name) {
                        $order = 1;
                        $data[] = [$item->team_name . " (" . trans('manage_time::view.Total user business trip:') . " {$item->count_employee})"];
                        //merge cells group team
                        $sheet->mergeCells("A{$currentLine}:K{$currentLine}");
                        $sheet->cells("A{$currentLine}:K{$currentLine}", function ($cells) {
                            $cells->setFontWeight('bold');
                            $cells->setAlignment('left');
                            $cells->setValignment('center');
                        });
                        $currentLine ++;
                    }

                    $data[] = [
                        $order ++,
                        $item->name,
                        $item->email,
                        $item->company,
                        $item->getStatus($statusOptions),
                        View::nl2br($item->attacher),
                        $item->sender_name,
                        $item->sender_email,
                        View::nl2br($item->note),
                        $item->created_at,
                    ];
                    $currentLine ++;
                    $teamName = $item->team_name;
                }

                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->setHeight([
                    1 => 25
                ]);
                $sheet->setWidth(array(
                    'A' => 10,
                    'B' => 30,
                    'C' => 30,
                    'D' => 30,
                    'E' => 30,
                    'F' => 30,
                    'G' => 30,
                    'H' => 30,
                    'I' => 50,
                    'J' => 30,
                ));
                $countData = count($data);
                $sheet->setBorder("A1:K{$countData}", 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
        return ['succes' => 1];
    }

    public function createCustomer()
    {
        Breadcrumb::add('Event', route('event::eventday.company.list'));
        Breadcrumb::add('Add new Customer');
        return view('event::eventday.customer.create', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
        ]);
    }

    public function insertCustomer()
    {
        $receive = Input::get('item');
        $sender = Input::get('exclude');
        $validator = Validator::make(array_merge($receive, $sender), [
                    'company' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                    'email_from_name' => 'required',
                    'email_from' => 'required',
                    'status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors(trans('event::message.Please fill information'));
        }
        $empInfo = Employee::where('email', $sender['email_from'])->first();
        if (!$empInfo) {
            return redirect()->back()->withInput()->withErrors(trans('Email gửi buộc phải tồn tại trong hệ thống rikkei.vn'));
        }
        $eventBirthday = EventBirthday::where('email', $receive['email'])->first();
        if ($eventBirthday) {
            return redirect()->back()->withInput()->withErrors(trans('event::message.Customer is exists'));
        }
        $item = EventBirthday::findItemFollowEmail($receive['email']);
        if ($item->token) {
            $token = $item->token;
        } else {
            while (1) {
                $token = md5($receive['name'] . $receive['email'] . 'rk') .
                        md5(time() . mt_srand());
                if (!EventBirthday::findItemFollowToken($token, $item)) {
                    break;
                }
            }
        }
        $item->setData($receive);
        $item->token = $token;
        $item->sender_name = $sender['email_from_name'];
        $item->sender_email = $sender['email_from'];
        $item->created_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $item->customer_type = 1;
        $item->attacher = isset($receive['attacher']) ? $receive['attacher'] : '';
        $item->note = isset($receive['note']) ? $receive['note'] : '';

        $item->status = (int) $receive['status'];
        try {
            $item->save();
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            return redirect()->back()->withInput()->withErrors(trans('event::message.Create customer failed'));
        }
        return redirect(route('event::eventday.company.list'))->with('success', trans('event::message.Create customer success'));
    }

    public function editCustomer($id)
    {
        $eventInfo = EventBirthday::where('id', $id)->first();
        if (!$eventInfo) {
            return abort(404);
        }
        Breadcrumb::add('Event', route('event::eventday.company.list'));
        Breadcrumb::add('Add new Customer');
        return view('event::eventday.customer.edit', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'eventInfo' => $eventInfo
        ]);
    }

    public function updateCustomer($id)
    {
        $receive = Input::get('item');
        $sender = Input::get('exclude');
        $validator = Validator::make(array_merge($receive, $sender), [
                    'company' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                    'email_from_name' => 'required',
                    'email_from' => 'required',
                    'status' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors(trans('event::message.Please fill information'));
        }
        $empInfo = Employee::where('email', $sender['email_from'])->first();
        if (!$empInfo) {
            return redirect()->back()->withInput()->withErrors(trans('Email gửi buộc phải tồn tại trong hệ thống rikkei.vn'));
        }
        $item = EventBirthday::where('email', $receive['email'])->where('id', '!=', $id)->first();
        if ($item) {
            return redirect()->back()->withInput()->withErrors(trans('event::message.Customer is exists'));
        }

        $item = EventBirthday::findItemFollowEmail($receive['email']);
        $token = md5($receive['name'] . $receive['email'] . 'rk') .
                md5(time() . mt_srand());

        $item->setData($receive);
        $item->token = $token;
        $item->sender_name = $sender['email_from_name'];
        $item->sender_email = $sender['email_from'];
        $item->created_at = \Carbon\Carbon::now()->format('Y-m-d H:i:s');        
        $item->status = (int) $receive['status'];
        $item->attacher = isset($receive['attacher']) ? $receive['attacher'] : '';
        $item->note = isset($receive['note']) ? $receive['note'] : '';
        try {
            $item->save();
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            return redirect()->back()->withInput()->withErrors(trans('event::message.Edit customer failed'));
        }
        return redirect(route('event::eventday.company.list'))->with('success', trans('event::message.Edit customer success'));
    }

    public function deleteCustomer($id)
    {
        $eventBirthday = EventBirthday::whereNull('deleted_at')->where('id', $id)->first();
        if (!$eventBirthday) {
            return response('Customer not found', 404);
        }
        $eventBirthday->email = uniqid().'$$'.$eventBirthday->email;
        $eventBirthday->delete();
        return response('Deleted successfully');
    }

}
