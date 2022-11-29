<?php

namespace Rikkei\Project\View;

use Illuminate\Support\Facades\Storage;
use Rikkei\Core\View\View as ViewCore;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\MeTimeSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Rikkei\Team\Model\Employee;

class OLD_TimekeepingSplit
{
    const FOLDER_UPLOAD = 'timekeeping';
    const FOLDER_PROCESS = 'timekeeping/process';
    const FOLDER_APP = 'app';
    const SUFFIX_EMAIL = '@rikkeisoft.com';
    const EXTENSION_FILE = 'xlsx';
    const FOLDER_PROCESS_EMAIL = 'process';
    const FILE_PROCESS_EMAIL = 'timekeeping_email';
    const FOLDER_UPLOAD_FINES = 'tk_fines';
    const FOLDER_CORE_PROCESS = 'process';
    const FILE_PROCESS_SPLIT = 'tkf_process';
    const FOLDER_EMAIL_LATE = 'late';
    
    const RUN_WAIT = 1;
    const RUN_PROCESS = 2;
    const RUN_ERROR = 3;

    const ACCESS_FOLDER = 0777;
    const ACCESS_FILE = 'public';
    
    /**
     *  upload file excel to system
     */
    public static function uploadFile($file = null, $fines = null)
    {
        self::createFiles();
        if ($file) {
            self::uploadFileTimeAndFines($file, self::FOLDER_UPLOAD);
        }
        if ($fines) {
            self::uploadFileTimeAndFines($fines, self::FOLDER_UPLOAD_FINES);
        }
        self::deleteProcessSplit();
    }
    
    /**
     * upload file, analyze name file get month and year
     * 
     * @param object $file
     * @param string $folder folder upload file in storage app
     * @return boolean
     * @throws Exception
     */
    protected static function uploadFileTimeAndFines($file, $folder)
    {
        $name = $file->getClientOriginalName();
        preg_match_all('/[0-9]+/', $name, $nameSplit);
        if($nameSplit && count($nameSplit)) {
            $nameTmp = reset($nameSplit);
            if (is_string($nameTmp)) {
                $name = $nameTmp;
            } else if (is_array($nameTmp)) {
                $name = implode('_', $nameTmp);
            }
        }
        try {
            // delete folder file in timepeeker
            Storage::deleteDirectory($folder);
            self::createFiles();
            $fileName = ViewCore::uploadFile(
                $file,
                $folder,
                [],
                null,
                $name
            );
            @chmod(storage_path(self::FOLDER_APP . '/' . $folder . 
                '/' . $fileName), self::ACCESS_FOLDER);
            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * convert date
     *
     * @param type $date
     * @return type
     */
    public static function convertDate($date)
    {
        $formats = [
            '/^([0-9]{1,2})\D+([0-9]{1,2})\D+([0-9]{4})\D*$/i' => 'd-m-Y',
            '/^([0-9]{1,2})\D+([0-9]{1,2})\D+([0-9]{2})\D*$/i' => 'd-m-y',
        ];
        $format = null;
        foreach ($formats as $pattern => $v) {
            $matches = null;
            if (preg_match($pattern, $date, $matches)) {
                if (!$matches || count($matches) < 4) {
                    continue;
                }
                $format = $v;
                $date = $matches[1] .'-'. $matches[2] .'-'. $matches[3];
                break;
            }
        }
        if (!$format) {
            return null;
        }
        return Carbon::createFromFormat($format, $date);
    }

    /**
     * update me time sheet when upload timekeeping
     * 
     * @param array $data
     * @param string $email
     * @param array $accountEmails
     * @return type
     */
    public static function updateMeEvalTimeSheet(
            $data, 
            $email, 
            $accountEmails
    ) {
        if (!isset($data['account']) ||
            !isset($data['ngay']) ||
            !$data['id_cham_cong'] ||
            !$data['ngay']
        ) {
            return;
        }
        $lateTime = null;
        if (isset($data['di_muon']) && $data['di_muon']) {
            $lateTime = trim($data['di_muon']);
        }
        if (isset($accountEmails[$email])) {
            $email = $accountEmails[$email];
        } else {
            $email = $email.self::SUFFIX_EMAIL;
        }
        $dateTimesheet = self::convertDate($data['ngay']);
        if (!$dateTimesheet) {
            return true;
        }
        $item = MeTimeSheet::whereDate('date', '=', $dateTimesheet->format('Y-m-d'))
            ->where('employee_email', $email)
            ->where('shift', trim($data['ca_lam_viec']))
            ->first();
        if ($item) {
            if (!$lateTime) {
                return $item->delete();
            } else {
                if (preg_replace('/\:[^\:]+$/', '', $item->late_time) != $lateTime) {
                    $item->late_time = $lateTime;
                    return $item->save();
                }
            }
        } else {
            if ($lateTime) {
                return MeTimeSheet::create([
                    'employee_email' => $email,
                    'date' => $dateTimesheet->format('Y-m-d'),
                    'late_time' => $lateTime,
                    'shift' => trim($data['ca_lam_viec'])
                ]);
            }
            return true;
        }
    }
    
    /**
     * split and send mail each employee for: time keeping, fines
     */
    public static function splitEachEmployee()
    {
        if (self::isProcessSplit()) {
            return true;
        }      
        $files = Storage::files(self::FOLDER_UPLOAD);
        if (!$files || !count($files)) {
            self::deleteProcessEmail();
            self::deleteProcessSplit();
            return false;
        }  
        DB::beginTransaction();
        try {
            Storage::put(self::FOLDER_CORE_PROCESS . '/' . self::FILE_PROCESS_SPLIT, 
                1, self::ACCESS_FILE);
            @chmod(storage_path('app/' . self::FOLDER_CORE_PROCESS . '/' . self::FILE_PROCESS_SPLIT),
                self::ACCESS_FOLDER);
//            self::splitTimekeeping();
            self::deleteProcessEmail();
            self::deleteProcessSplit();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * split employee from file excel
     * 
     * @return boolean
     */
    public static function splitTimekeeping($file, $date, $isSendMail = true)
    {
        if (!$file) {
            return false;
        }
        $count = 0;
        try {
            //set time out 180 seconds
            set_time_limit(180);
            
            $data = Excel::load($file->getRealPath())->get();
            $dataGroup = $data->groupBy('ma_nvien')->toArray();
            if ($dataGroup && array_keys($dataGroup)[0]) {
                $count = count($dataGroup);
            }
        } catch (Exception $ex) {
            throw new Exception(trans('event::message.Canot read file'), 422);
        }
        
        if (!$count) {
            throw new Exception(trans('event::message.None item read'), 422);
        }
        
        self::createFiles();
        
        $folderMonth = self::FOLDER_UPLOAD . '/' . $date;
        $folderPath = storage_path('app/' . $folderMonth);
        //create new folder separator of month
        if (!Storage::exists($folderMonth)) {
            Storage::makeDirectory($folderMonth, self::ACCESS_FOLDER);
        } else {
            //delete all file in folder
            $files = Storage::files($folderMonth);
            if ($files) {
                Storage::delete($files);
            }
        }
        @chmod($folderPath, self::ACCESS_FOLDER);
        //move file to folder
        $file->move($folderPath, $file->getClientOriginalName());
        @chmod($folderPath . '/'. $file->getClientOriginalName(), self::ACCESS_FOLDER);
        //save temp month
        $configTemp = CoreConfigData::getItem('hr.timesheet.temp_month');
        $configTemp->value = serialize(['date' => $date, 'send_email' => $isSendMail]);
        $configTemp->save();
        
        return $count;
    }
    
    /**
     * cronjob split file (timesheet) to send employee
     * @return boolean|int
     * @throws Exception
     */
    public static function doSplitTimesheetFiles()
    {
        $configDate = CoreConfigData::getValueDb('hr.timesheet.temp_month');
        if (!$configDate) {
            return false;
        }
        $configDate = unserialize($configDate);
        $date = isset($configDate['date']) ? $configDate['date'] : null;
        $isSendEmail = true;
        if (isset($configDate['send_email'])) {
            $isSendEmail = $configDate['send_email'];
        }
        $employeesData = [];
        $folder = self::FOLDER_UPLOAD . '/' . $date;
        try {
            $files = Storage::files($folder);
            if (!$files) {
                return false;
            }
        } catch (Exception $ex) {
            \Log::info($ex);
            return false;
        }
        $file = storage_path('app/' . $files[0]);
        $accountEmails = CoreConfigData::getAccountToEmail(2);
        Excel::selectSheetsByIndex(0)->load($file,  function ($reader) use (
            &$employeesData, $accountEmails
        ){
            $dataRecord = $reader->get();
            foreach ($dataRecord as $row) {
                $data = $row->toArray();
                if ((!isset($data['ma_nvien']) || !$data['ma_nvien']) &&
                    (!isset($data['account']) || !$data['account'])) {
                    continue;
                }
                $account = isset($data['ma_nvien']) ? $data['ma_nvien'] : $data['account'];
                $account = preg_replace('/\s|/', '', $account);
                $data['account'] = $account;
                unset($data['ma_nvien']);
                $data = [
                    'id_cham_cong' => $data['id_cham_cong'],
                    'account' => $data['account'],
                    'ho_ten' => $data['ho_ten'],
                    'ngay' => $data['ngay'],
                    'ca_lam_viec' => $data['ca_lam_viec'],
                    'vao_luc' => $data['vao_luc'],
                    'ra_luc' => $data['ra_luc'],
                    'di_muon' => $data['di_muon'],
                    've_som' => $data['ve_som'],
                ];
                $email = strtolower($account);
                $email = preg_replace('/\s|/', '', $email);
                //update ME time sheet
                //use manage time, not update to this
                //self::updateMeEvalTimeSheet($data, $email, $accountEmails);
                $employeesData[$email][] = $data;
            }
        });
        //check sending email
        if (!$isSendEmail) {
            //delete temp month
            CoreConfigData::delByKey('hr.timesheet.temp_month');
            //delete temp file
            Storage::deleteDir($folder);
            return false;
        }
        if (!$employeesData || !count($employeesData)) {
            throw new Exception(trans('event::message.None item read'), 422);
        }
        
        // create folder split file excel
        $arrayHeading = self::headingTitleFile();
        $dataTemplate = [
            'monthTimeKeeping' => $date,
        ];
        $subject = CoreConfigData::getValueDb('hr.email_subject.timekeeping');
        $patternsArray = [
            '/\{\{\saccount\s\}\}/',
            '/\{\{\sname\s\}\}/',
        ];
        $dataEmail = [];
        $count = 0;
        //list employee ids
        $arrayEmails = array_map(function ($item) {
            return $item .= self::SUFFIX_EMAIL;
        }, array_keys($employeesData));
        $employeeIds = Employee::whereIn('email', $arrayEmails)->lists('id', 'email')->toArray();
        foreach ($employeesData as $email => $data) {
            // send email to all employee
            $replacesArray = [
                $data[0]['account'],
                $data[0]['ho_ten']
            ];
            $subjectMail = preg_replace($patternsArray, $replacesArray, $subject);
            $fileName = Str::slug($subjectMail, '_') . '_' . $data[0]['id_cham_cong'];
            // create excel files
            Excel::create($fileName, function ($excel) use ($email, $data, $arrayHeading) {
                $excel->setTitle($email);
                $excel->sheet('time', function($sheet) use ($data, $arrayHeading) {
                    $sheet->fromArray($data, null, 'A1', false, false);
                    $sheet->prependRow(1, $arrayHeading);
                    $sheet->cells('A1:I1', function($cells) {
                        $cells->setBackground('#9CC2E5');
                        $cells->setFontWeight('bold');
                    });
                });
            })->store(self::EXTENSION_FILE, storage_path(self::FOLDER_APP . '/' .
                    self::FOLDER_PROCESS));
            if (!Storage::exists(self::FOLDER_PROCESS . '/' . $fileName . 
                    '.' . self::EXTENSION_FILE)) {
                continue;
            }
            // send email queue
            $fileAttatch = storage_path(self::FOLDER_APP . '/' . 
                self::FOLDER_PROCESS . '/' . $fileName .  '.' . self::EXTENSION_FILE);
            $emailQueue = new EmailQueue();
            if (isset($accountEmails[$email])) {
                $email = $accountEmails[$email];
            } else {
                $email = $email.self::SUFFIX_EMAIL;
            }
            $dataTemplateEmail = array_merge($dataTemplate, [
                'ho_ten' => $data[0]['ho_ten'],
                'account' => $data[0]['account']
            ]);
            $emailQueue->setTo($email, $data[0]['ho_ten'])
                ->setSubject($subjectMail)
                ->setTemplate('project::emails.timekeeping_employee', $dataTemplateEmail)
                ->addAttachment($fileAttatch);
            $dataEmail[] = $emailQueue->getValue();
            $count++;
            //calculator minute late
            $totalMinute = 0;
            foreach($data as $keyData => $valueData) {
                if($valueData['di_muon']) {
                    $arrayTime = explode(':',$valueData['di_muon']);
                    $minute = $arrayTime[0]*60 + $arrayTime[1];
                    $roundingMinute = ceil($minute/10)*10;
                    if($roundingMinute < 120) {
                        $totalMinute = $totalMinute + $roundingMinute;
                    }

                }
            }
            $infoMinuteLate[] = [
                'ID chấm công' => $valueData['id_cham_cong'],
                'Account' => $valueData['account'],
                'Họ tên' => $valueData['ho_ten'],
                'Số phút đi muộn' => $totalMinute,

            ];
        }
        DB::beginTransaction();
        try {
            EmailQueue::insert($dataEmail);
            $subjectLate = "Tính số phút đi muộn của nhân viên tháng " . $date;
            $fileNameLate = Str::slug($subjectLate, '_');
            Excel::create($fileNameLate, function ($excel) use ($infoMinuteLate) {
                $excel->setTitle('time');
                $excel->sheet('time', function($sheet) use ($infoMinuteLate) {
                    $sheet->fromArray($infoMinuteLate);

                });
            })->store(self::EXTENSION_FILE, storage_path(self::FOLDER_APP . '/' .
                    self::FOLDER_EMAIL_LATE));
            self::sendMailMinuteLate($subjectLate,$fileNameLate);
            //delete temp month
            CoreConfigData::delByKey('hr.timesheet.temp_month');
            //delete temp file
            Storage::deleteDir($folder);
            //set notify
            \RkNotify::put(
                $employeeIds,
                trans('project::message.hr.email_subject.timekeeping', ['month' => $date]),
                route('manage_time::profile.timekeeping'),
                ['actor_id' => null, 'icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );
            
            DB::commit();
            return $count;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * split finde to send email
     */
    public static function splitFines()
    {
        $files = Storage::files(self::FOLDER_UPLOAD_FINES);
        if (!$files || !count($files)) {
            return;
        }
        if (!self::checkProcessSendEmail()) {
            return true;
        }
        $pathFile = reset($files);
        $file = storage_path(self::FOLDER_APP . '/' . $pathFile);
        $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
        $titleIndex = self::getHeadingIndexFines();
        //get month and year from path file
        $i = strrpos($pathFile, '/');
        $monthYearTimekeeping = substr($pathFile, $i+1);
        $i = strrpos($monthYearTimekeeping, '.');
        $monthYearTimekeeping = substr($monthYearTimekeeping, 0, $i);
        $monthYearTimekeeping = preg_replace('/[_-]+/', '/', $monthYearTimekeeping);
        $timeLimit = Carbon::now();
        $timeLimit->modify('+7 days');
        $dataTemplate = [
            'time' => $monthYearTimekeeping,
            'time_limit_date' => $timeLimit->format('d/m/Y'),
            'time_limit_dow' => $timeLimit->format('w') + 1
        ];
        Excel::selectSheetsByIndex(0)->load($file,  function ($reader) use (
            $accountEmailsReplace, $titleIndex, $monthYearTimekeeping, $dataTemplate
        ) {
            $reader->noHeading();
            $dataRecord = $reader->get();
            $preSubject = 'Tiền phạt nội quy tháng ' . $monthYearTimekeeping;
            //collect email to set notify
            $arrayEmails = [];
            foreach ($dataRecord as $row) {
                $data = $row->toArray();
                if (!isset($data[$titleIndex['ma_nv']]) || 
                    !$data[$titleIndex['ma_nv']] ||
                    !isset($data[$titleIndex['ho_ten']]) || 
                    !$data[$titleIndex['ho_ten']] ||
                    !isset($data[$titleIndex['id']]) || 
                    !$data[$titleIndex['id']] ||
                    !isset($data[$titleIndex['tien_di_muon']]) || 
                    !isset($data[$titleIndex['tien_quen_cham_cong']]) ||
                    !isset($data[$titleIndex['tong']])
                ) {
                    continue;
                }
                $email = strtolower($data[$titleIndex['ma_nv']]);
                $email = preg_replace('/\s|/', '', $email);
                $accountEmployee = preg_replace('/\s/', '', $data[$titleIndex['ma_nv']]);
                $subjectMail = $preSubject . ' - ' . $accountEmployee;
                if (isset($accountEmailsReplace[$email])) {
                    $email = $accountEmailsReplace[$email];
                } else {
                    $email = $email.self::SUFFIX_EMAIL;
                }
                $dataTemplateEmail = array_merge($dataTemplate, [
                    'employee' => $data
                ]);
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($email)
                    ->setSubject($subjectMail)
                    ->setTemplate('project::emails.fines_employee', $dataTemplateEmail)
                    ->save();
                $arrayEmails[] = $email;
            }
            //set notify
            $employeeIds = Employee::whereIn('email', $arrayEmails)->lists('id')->toArray();
            if ($employeeIds) {
                \RkNotify::put($employeeIds, $preSubject, 'https://mail.google.com', ['actor_id' => null, 'icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]);
            }
        });
        Storage::delete($pathFile);
    }
    
    /**
     * create and chmod files use timekeeping
     */
    public static function createFiles()
    {
        if (!Storage::exists(self::FOLDER_UPLOAD)) {
            Storage::makeDirectory(self::FOLDER_UPLOAD, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_UPLOAD), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_PROCESS)) {
            Storage::makeDirectory(self::FOLDER_PROCESS, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_PROCESS), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_PROCESS_EMAIL)) {
            Storage::makeDirectory(self::FOLDER_PROCESS_EMAIL, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_PROCESS_EMAIL), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_UPLOAD_FINES)) {
            Storage::makeDirectory(self::FOLDER_UPLOAD_FINES, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_UPLOAD_FINES), self::ACCESS_FOLDER);
        if (!Storage::exists(self::FOLDER_CORE_PROCESS)) {
            Storage::makeDirectory(self::FOLDER_CORE_PROCESS, self::ACCESS_FOLDER);
        }
        @chmod(storage_path('app/' . self::FOLDER_CORE_PROCESS), self::ACCESS_FOLDER);
    }
    
    /**
     * get heading each column of file excel
     */
    protected static function headingTitleFile()
    {
        return [
            'Id chấm công',
            'Account',
            'Họ tên',
            'Ngày',
            'Ca làm việc',
            'Vào lúc',
            'Ra lúc',
            'Đi muộn',
            'Về sớm'
        ];
    }
    
    /**
     * get heading each column of file fines
     * fron code to index
     */
    public static function getHeadingIndexFines()
    {
        return [
            'ho_ten' => 0,
            'id' => 1,
            'ma_nv' => 2,
            'phut_di_muon' => 3,
            'tien_di_muon' => 4,
            'lan_quen_cham_cong' => 5,
            'tien_quen_cham_cong' => 6,
            'lan_dong_phuc' => 7,
            'tien_dong_phuc' => 8,
            'tong' => 9
        ];
    }
    
    
    
    /**
     * create file process email
     */
    public static function createProcessSendEmail()
    {
        Storage::put(self::FOLDER_PROCESS_EMAIL . '/' . self::FILE_PROCESS_EMAIL, 1, self::ACCESS_FILE);
        @chmod(storage_path('app/' . self::FOLDER_PROCESS_EMAIL . '/' . self::FILE_PROCESS_EMAIL),
            self::ACCESS_FOLDER);
    }
    
    /**
     * delete process send email timekeeping
     */
    public static function deleteProcessEmail()
    {
        Storage::delete(self::FOLDER_PROCESS_EMAIL . '/' . self::FILE_PROCESS_EMAIL);
    }
    
    /**
     * check send email timekeeping
     * 
     * @return boolean
     */
    public static function checkProcessSendEmail()
    {
        if (Storage::exists(self::FOLDER_PROCESS_EMAIL . '/' . self::FILE_PROCESS_EMAIL)) {
            return true;
        }
        return false;
    }
    
    /**
     * get content send email timekeeping
     * 
     * @return boolean
     */
    public static function getContentProcessSendEmail()
    {
        return (int) Storage::get(self::FOLDER_PROCESS_EMAIL . '/' . 
            self::FILE_PROCESS_EMAIL);
    }
    
    /**
     * check is process split timekeeping and fines
     * 
     * @return boolean
     */
    public static function isProcessSplit()
    {
        if (Storage::exists(self::FOLDER_CORE_PROCESS . '/' . self::FILE_PROCESS_SPLIT)) {
            return true;
        }
        return false;
    }
    
    /**
     * delete process split
     */
    public static function deleteProcessSplit()
    {
        Storage::delete(self::FOLDER_CORE_PROCESS . '/' . self::FILE_PROCESS_SPLIT);
    }

    /**
     * send mail minute late
    */
    public static function sendMailMinuteLate($subjectLate,$nameFileLate) {
        $email = explode(',',CoreConfigData::getValueDb('email_hcth'));
        if(!empty($email)) {
            $fileAttatch = storage_path(self::FOLDER_APP . '/' . 
                    self::FOLDER_EMAIL_LATE . '/' . $nameFileLate .  '.' . self::EXTENSION_FILE);
            $arrayEmail = array();
            foreach ($email as $value) {
                array_push($arrayEmail, trim($value));
            }
            $arrayEmail = Employee::whereIn('email',$arrayEmail)->get();
            if(!empty($arrayEmail)) {
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($arrayEmail[0]['email']);
                foreach($arrayEmail as $valueEmail) {
                    $emailQueue->addCc($valueEmail['email']);
                }
                $emailQueue->setSubject($subjectLate);
                $emailQueue->setTemplate('project::emails.timekeeping_minute_late');
                $emailQueue->addAttachment($fileAttatch);
                $emailQueue->save();
                //set notify
                \RkNotify::put($arrayEmail->lists('id')->toArray(), $subjectLate, 'https://mail.google.com', ['actor_id' => null, 'icon' => 'resource.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]);
            }
        }
    }
}
