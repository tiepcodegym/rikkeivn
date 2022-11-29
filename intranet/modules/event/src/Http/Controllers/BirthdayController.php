<?php

namespace Rikkei\Event\Http\Controllers;

use Carbon\Carbon;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\View\Permission;
use Rikkei\Event\Model\EventBirthday;
use PHPMailer;
use Exception;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\View\OptionCore;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Event\Model\EventBirthCustEmail;

class BirthdayController extends Controller
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
        Breadcrumb::add('Event');
        return view('event::birthday.create', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'genderOptions' => EventBirthday::toOptionGender(),
            'contentEmail' => CoreConfigData::getValueDb('event.bitrhday.company.content'),
            'contentEmailJa' => CoreConfigData::getValueDb('event.bitrhday.company.content.ja'),
            'subjectEmail' => CoreConfigData::getValueDb('event.bitrhday.company.subject'),
            'subjectEmailJa' => CoreConfigData::getValueDb('event.bitrhday.company.subject.ja'),
            'langOptions' => EventBirthday::toOptionLang()
        ]);
    }
    
    public function sendEmailCountFile()
    {
        ini_set('max_execution_time', 300);
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $config = Input::get('cc');
        $receive = Input::get('item');
        $sender = Input::get('exclude');
        $fileImport = Input::file('excel_file');
        if (Input::get('item.lang') == 'ja') {
            $nameInputSubject = 'event.bitrhday.company.subject.ja';
        } else {
            $nameInputSubject = 'event.bitrhday.company.subject';
        }
        $receive['excel_file'] = $fileImport;
        $validator = Validator::make(array_merge($config, $receive, $sender), [
            'email_from_name' => 'required',
            'email_from' => 'required',
            'email_from_pass' => 'required',
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            $nameInputSubject => 'required',
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Please fill information');
            return response()->json($response);
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
                    $response = [];
                    $response['message'] = 'Có lỗi xảy ra!';
                    $response['status'] = -1;
                    $response['errors'] = $errors;
                    return response()->json($response);
                }
                $dataRecord = $dataReader->get();
            } catch (\Exception $ex) {
                Log::info($ex);
                $response = [];
                $response['message'] = 'Có lỗi xảy ra!';
                $response['status'] = -1;
                $response['errors'] = ['Có lỗi xảy ra'];
                return response()->json($response);
            }
        }

        $response = [];
        $response['message'] = 'Successfully';
        $response['status'] = 1;
        $response['total_line'] = count($dataRecord);
        return response()->json($response);
    }

    public function sendEmailProcessFile()
    {
        ini_set('max_execution_time', 300);
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $sender = Input::get('exclude');

        $userCurrent = Permission::getInstance()->getEmployee();
        $number = EventBirthCustEmail::where('sale_email', $sender['email_from'])
            ->where('email_sender', $userCurrent->email)
            ->where('status', EventBirthCustEmail::STATUS_YES)
            ->where('is_sending', EventBirthCustEmail::IS_SENDING)
            ->get();

        $response = [];
        $response['message'] = 'Successfully';
        $response['status'] = 1;
        $response['number'] = count($number);
        return response()->json($response);
    }

    public function sendEmailResetMail()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $sender = Input::get('exclude');
        $userCurrent = Permission::getInstance()->getEmployee();
        EventBirthCustEmail::where('sale_email', $sender['email_from'])
            ->where('email_sender', $userCurrent->email)
            ->update([
                'is_sending' => EventBirthCustEmail::NOT_SENDING
            ]);

        $response = [];
        $response['message'] = 'Successfully';
        $response['status'] = 1;
        return response()->json($response);
    }

    /**
     * send email for customer
     */
    public function sendEmail()
    {
        ini_set('max_execution_time', 0);
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        
        $config = Input::get('cc');
        $receive = Input::get('item');
        $sender = Input::get('exclude');
        $fileImport = Input::file('excel_file');
        if (Input::get('item.lang') == 'ja') {
            $nameInputSubject = 'event.bitrhday.company.subject.ja';
        } else {
            $nameInputSubject = 'event.bitrhday.company.subject';
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
                    $response = [];
                    $response['message'] = 'Có lỗi xảy ra';
                    $response['status'] = -1;
                    $response['errors'] = $errors;
                    return response()->json($response);
                }
                $dataRecord = $dataReader->get();
            } catch (\Exception $ex) {
                Log::info($ex);
                $response = [];
                $response['message'] = 'Có lỗi xảy ra';
                $response['status'] = -1;
                $response['errors'] = ['Có lỗi xảy ra'];
                return response()->json($response);
            }
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        EventBirthCustEmail::where('sale_email', $sender['email_from'])->where('email_sender', $userCurrent->email)->update([
            'is_sending' => EventBirthCustEmail::NOT_SENDING
        ]);
        //save to event_birth_email_cust
        $this->saveToBirthMailCust($dataRecord, $sender['email_from'], $userCurrent);

        // $configItem = CoreConfigData::getItem($nameInputSubject);
        // $configItem->value = $config[$nameInputSubject];
        // $configItem->save();

        foreach ($dataRecord as $cust) {
            $item = EventBirthday::findItemFollowEmail($cust->email);
            if ($item->token) {
                $token = $item->token;
            } else {
                while (1) {
                    $token = md5($cust->customer . $cust->email . 'rk') . md5(time() . mt_srand());
                    if (!EventBirthday::findItemFollowToken($token, $item)) {
                        break;
                    }
                }
            }
            $dataReceive = [
                'company' => $cust->company,
                'name' => $cust->customer,
                'email' => $cust->email
            ];
            $item->setData($dataReceive);
            //if (!isset($receive['show_tour']) || 
                //$receive['show_tour'] != EventBirthday::SHOW_TOUR;
           // ) {
                $item->show_tour = EventBirthday::SHOW_TOUR;
            //}
            $item->lang = $receive["lang"];
            $item->token = $token;
            $item->sender_name = $sender['email_from_name'];
            $item->sender_email = $sender['email_from'];
            
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $sender['email_from'];
            $mail->Password = $sender['email_from_pass'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom($sender['email_from'], $sender['email_from_name']);
            $mail->addAddress($cust->email, $cust->customer);
            $mail->addBCC($sender['email_from'], $sender['email_from_name']);
            $mail->isHTML(true);
            $mail->Subject = $config[$nameInputSubject];
            $data = [
                'receiveName' => $cust->customer,
                'receiveCompanyName' => $cust->company,
                'receivePosition' => $cust->position,
                'linkRegister' => URL::route('event::brithday.register', ['token' => $token]),
                'linkRefuse' => URL::route('event::brithday.refuse', ['token' => $token]),
                'urlSite' => URL::to('/'),
                'senderName' => $sender['email_from_name'],
            ];
            $viewEmail = 'event::birthday.email.birthday_10years';
            if ($receive["lang"] == "en") {
                $viewEmail = 'event::birthday.email.birthday_10years_en';
            }
            $htmlView = view($viewEmail, ['data' => $data])->render();
            $mail->Body = $htmlView;
            $mail->AltBody = '';
            try {
                if(!$mail->send()) {
                    $response = [];
                    $response['message'] = Lang::get('event::message.Error save data');
                    $response['status'] = -1;
                    return response()->json($response);
                }
                EventBirthCustEmail::where('email', $cust->email)->update([
                    'status' => EventBirthCustEmail::STATUS_YES
                ]);
            } catch (Exception $ex) {
                Log::error($ex);
                $response = [];
                $response['message'] = Lang::get('event::message.Error save data');
                $response['status'] = -1;
                return response()->json($response);
            }
            $item->save();
        }
        
        $response = [];
        $response['message'] = Lang::get('event::message.Mail sent success');
        $response['status'] = 1;
        return response()->json($response);
    }

    private function excuteFile($reader, &$errors, &$count)
    {
        $dataRecord = $reader->get();        
        foreach ($dataRecord as $key => $itemRow) {
            $keyItem = $key + 2;
            if (strlen($itemRow->email) == 0 || strlen($itemRow->company) == 0 ||
            strlen($itemRow->customer) == 0) {
                $errors[] = 'Row '.$keyItem. ' missing Email, Company or Customer name';
                continue;
            }
        }
    }

    private function saveToBirthMailCust($dataRecord, $emailSender, $userCurrent)
    {
        foreach ($dataRecord as $item) {
            EventBirthCustEmail::create([
                'sale_email' => $emailSender,
                'email' => $item->email,
                'email_sender' => $userCurrent->email,
                'is_sending' => EventBirthCustEmail::IS_SENDING,
            ]);
        }
    }
    
    public function checkEmailExcel()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        
        $fileImport = Input::file('excel_file');
        $sender = Input::get('exclude');
        $email_from = $sender['email_from'];
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
                    $response = [];
                    $response['message'] = 'Có lỗi xảy ra';
                    $response['status'] = -1;
                    $response['errors'] = $errors;
                    return response()->json($response);
                }
                $dataRecord = $dataReader->get();
            } catch (\Exception $ex) {
                Log::info($ex);
                $response = [];
                $response['message'] = 'Có lỗi xảy ra';
                $response['status'] = -1;
                $response['errors'] = ['Có lỗi xảy ra'];
                return response()->json($response);
            }

            $userCurrent = Permission::getInstance()->getEmployee();
            $emailSents = EventBirthCustEmail::where('sale_email', $email_from)
                ->where('email_sender', $userCurrent->email)
                ->where('status', EventBirthCustEmail::STATUS_YES)
                ->pluck('email')->toArray();
            if (count($emailSents) <= 0) {
                $response = [];
                $response['message'] = 'File check successful!';
                $response['status'] = 1;
                return response()->json($response);
            }

            $arrEmail = [];
            foreach ($dataRecord as $item) {
                if (in_array($item->email, $emailSents)) {
                    $arrEmail[] = $item->email;
                }
            }
            if ($arrEmail) {
                $response = [];
                $response['message'] = 'Trong danh sách email này đã có một số người đã được gửi. Bạn có muốn tiếp tục gửi?';
                $response['status'] = -1;
                $response['errors'] = $arrEmail;
                return response()->json($response);
            }
        }
    }

    /**
     * preview send email
     */
    public function previewSendEmail($receive, $config, $arrayLang)
    {
        $data = [
            'content' => $config[$arrayLang['content']]
        ];
        $htmlView = view('event::birthday.email.general', $data)->render();
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
        $langView = View::getKeyOfOptions($item->lang, EventBirthday::toOptionLang());
        $viewTemplate = 'event::birthday.register';
        if ($langView == "en") {
            $viewTemplate = 'event::birthday.register_en';
        }
        $receiveGender = View::getLabelOfOptions($item->gender, 
            EventBirthday::toOptionGender());
        return view($viewTemplate, [
            'customerEvent' => $item,
            'receiveGender' => $receiveGender,
            'languageView' => $langView
        ]);
    }
    
    /**
     * submit register event birthday
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
        
        if (empty($dataItem['attacher']) || !count($dataItem['attacher'])) {
            $response['error'] = 1;
            $response['message'] = 'The number of participants must be at least 1 person';
            return response()->json($response);
        }

        if (!in_array($dataItem['join_tour'], [EventBirthday::TOUR_GOLF, EventBirthday::TOUR_DU_THUYEN])) {
            for ($i =0; $i < count($dataItem['attacher']); $i++) {
                unset($dataItem['attacher'][$i]['tour']);
            }
        } else {
            $countkPersonJoinTour = 0;
            for ($i =0; $i < count($dataItem['attacher']); $i++) {
                if (isset($dataItem['attacher'][$i]['tour'])) {
                    $countkPersonJoinTour++;
                }
            }
            if (!$countkPersonJoinTour) {
                $response['error'] = 1;
                $tourName = $dataItem['join_tour'] == EventBirthday::TOUR_GOLF ? 'Rikkeisoft 10周年記念ゴルフコンペ' : '世界遺産ハロン湾クルーズ';
                $response['message'] = $tourName . 'の参加者を選んでください';
                return response()->json($response);
            }
        }
        $dataItem['attacher'] = json_encode($dataItem['attacher']);

        $item->setData($dataItem);
        $item->status = EventBirthday::STATUS_YES;
        try {
            $item->save();
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Error system, please try again');
            \Log::error($ex);
            return response()->json($response);
        }
        $response['message'] = Lang::get('event::message.Register success');
        $response['success'] = 1;
        $response['popup'] = 1;
        $response['refresh'] = URL::route('event::brithday.register.success', 
            ['token' => $token]);
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
        $receiveGender = View::getLabelOfOptions($item->gender, 
            EventBirthday::toOptionGender());
        return view('event::birthday.register_success', [
            'customerEvent' => $item,
            'receiveGender' => $receiveGender,
            'languageView' => View::getKeyOfOptions($item->lang, 
                EventBirthday::toOptionLang())
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
        $receiveGender = View::getLabelOfOptions($item->gender, 
            EventBirthday::toOptionGender());
        return view('event::birthday.refuse', [
            'customerEvent' => $item,
            'receiveGender' => $receiveGender,
            'languageView' => View::getKeyOfOptions($item->lang, 
                EventBirthday::toOptionLang())
        ]);
    }
    
    /**
     * list
     */
    public function listInvi()
    {
        Breadcrumb::add('Event');
        return view('event::birthday.list', [
            'collectionModel' => EventBirthday::getGridData(),
            'statusOptions' => EventBirthday::toOptionLabelStatus(),
            'bookingOptions' => EventBirthday::toOptionBooking(),
            'joinTourOptions' => EventBirthday::toOptionJoinTour(),
            'yesNoOption' => OptionCore::yesNo(true, false)
        ]);
    }

    public function export()
    {
        $dataCollection = EventBirthday::getGridData(false);
        $statusOptions = EventBirthday::toOptionLabelStatus();
        $bookingOptions = EventBirthday::toOptionBooking();
        $joinTourOptions = EventBirthday::toOptionLabelStatus();
        $yesNoOption = EventBirthday::toOptionLabelStatus();

        $c = 0;
        foreach ($dataCollection as $item) {
            $attachers = json_decode($item->attacher, true);
            if (count($attachers)) {
                $c += count($attachers);
            } else {
                $c++;
            }
        }
        Excel::create('Event birthday', function ($excel) use ($dataCollection, $c, $statusOptions, $bookingOptions,  $joinTourOptions, $yesNoOption) {
            $excel->sheet('Sheet1', function ($sheet) use ($dataCollection, $c, $statusOptions, $bookingOptions,  $joinTourOptions, $yesNoOption) {
                $sheet->setWidth('A', 5);
                $sheet->setWidth('B', 25);
                $sheet->setWidth('C', 30);
                $sheet->setWidth('D', 30);
                $sheet->setWidth('E', 15);
                $sheet->setWidth('F', 70);
                $sheet->setWidth('G', 20);
                $sheet->setWidth('H', 20);
                $sheet->setWidth('I', 25);
                $sheet->setWidth('J', 30);
                $sheet->setWidth('K', 50);
                $sheet->setWidth('L', 20);
                $sheet->getStyle('A2:L' . ($c + 1))->getAlignment()->setWrapText(true);
                $arrCol = [
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'
                ];
                foreach ($arrCol as $col) {
                    $sheet->getStyle($col.'1:'.$col.($c+1))->applyFromArray(
                        [
                            'borders' => [
                                'allborders' => [
                                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                                ]
                            ],
                        ]
                    );
                }
                $sheet->loadView('event::birthday.export', [
                    'dataCollection' => $dataCollection,
                    'statusOptions' => $statusOptions,
                    'bookingOptions' => $bookingOptions,
                    'joinTourOptions' => $joinTourOptions,
                    'yesNoOption' => $yesNoOption,
                ]);
            });
        })->export('xlsx');
    }

    public function listEmailCust()
    {
        Breadcrumb::add('Event');
        return view('event::birthday.mail_cust_list', [
            'collectionModel' => EventBirthCustEmail::getListBy(),
            'statusOptions' => EventBirthCustEmail::toOptionStatus(),
        ]);
    }

    public function downloadTemplate()
    {
        $pathToFile = public_path('event/files/Mail_Template_Birthday.xlsx');
        return response()->download($pathToFile);
    }
}
