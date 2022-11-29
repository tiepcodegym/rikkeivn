<?php

namespace Rikkei\Event\View;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Str;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\OptionCore;

class TimekeepingHelper
{
    const FOLDER_UPLOAD = 'timekeeping/';
    const FOLDER_APP = 'app/';
    const FOLDER_PROCESS = 'timekeeping/process/';
    const FOLDER_DATA = 'timekeeping/data/';
    const FOLDER_LATE = 'timekeeping/late/';
    const EXTENSION_FILE = 'xlsx';
    const FILE_PROCESS = 'timekeeping/process/ts_process';
    const ACCESS_FILE = 'public';
    protected static $instance;
    /**
     *  upload file excel to system
     */
    public function uploadFile($file, $employee, $date = null)
    {
        $this->createFiles();
        $accountReceive = preg_replace('/@.*/', '', $employee->email);
        $dataWriteFile = [
            'file' => $accountReceive . '.' . $file->getClientOriginalExtension(),
            'dear_name' => $employee->name,
            'date' => $date,
            'email' => $employee->email,
        ];
        //move file to folder
        $file->move(storage_path(self::FOLDER_APP . self::FOLDER_UPLOAD), $dataWriteFile['file']);
        CoreForm::chmodFile(self::FOLDER_UPLOAD . $dataWriteFile['file']);
        // create data file
        Storage::put(self::FOLDER_DATA . $accountReceive, json_encode($dataWriteFile));
        CoreForm::chmodFile(self::FOLDER_DATA . $accountReceive);
        return true;
    }

    /**
     * cronjob split file (timesheet) to send employee
     * @return boolean|int
     * @throws Exception
     */
    public function tsToFines()
    {
        OptionCore::setMemoryMax();
        if (Storage::exists(self::FILE_PROCESS)) {
            return true;
        }
        try {
            $this->tsToFinesExecFile();
        } catch (Exception $ex) {
            $this->removeProcess();
            throw $ex;
        } finally {
            $this->removeProcess();
        }
    }

    /**
     * cronjob split file (timesheet) to send employee
     * @return boolean|int
     * @throws Exception
     */
    public function tsToFinesExecFile()
    {
        $this->createFiles();
        $filePath = Storage::files(self::FOLDER_DATA);
        if (!$filePath) {
            return true;
        }
        $filePath = reset($filePath);
        $fileData = Storage::get($filePath);
        $fileData = json_decode($fileData, true);
        if (!isset($fileData['file']) || !isset($fileData['email'])) {
            return true;
        }
        if (!Storage::exists(self::FOLDER_UPLOAD.$fileData['file'])) {
            return true;
        }
        $this->createProcess();
        $dataRecord = Excel::selectSheetsByIndex(0)
            ->load(storage_path(self::FOLDER_APP . self::FOLDER_UPLOAD.$fileData['file']))
            ->get();
        if (!count($dataRecord)) {
            $this->removeProcess();
            return false;
        }
        $employeesData = [];
        foreach ($dataRecord as $row) {
            $data = $row->toArray();
            if (((!isset($data['ma_nvien']) || !$data['ma_nvien']) &&
                (!isset($data['account']) || !$data['account'])) ||
                !isset($data['di_muon']) || !$data['di_muon']
            ) {
                continue;
            }
            $account = isset($data['ma_nvien']) ? $data['ma_nvien'] : $data['account'];
            $account = preg_replace('/\s|/', '', $account);
            $data['account'] = $account;
            $email = strtolower($account);
            if (!isset($employeesData[$email])) {
                $employeesData[$email] = [
                    'id' => $data['id_cham_cong'],
                    'account' => $data['account'],
                    'name' => $data['ho_ten'],
                    'minute' => 0,
                ];
            }
            $arrayTime = explode(':', $data['di_muon']);
            if (!isset($arrayTime[1])) {
                $arrayTime[1] = 0;
            }
            $minute = $arrayTime[0]*60 + $arrayTime[1];
            $roundingMinute = ceil($minute/10)*10;
            if($roundingMinute < 120) {
                $employeesData[$email]['minute'] += $roundingMinute;
            }
        }
        $infoMinuteLate = [];
        foreach ($employeesData as $data) {
            if ($data['minute'] > 0) {
                $infoMinuteLate[] = $data;
            }
        }
        if (!$infoMinuteLate) {
            $this->removeProcess();
            return false;
        }
        $subjectLate = 'Số phút đi muộn của nhân viên tháng '
            . (isset($fileData['date']) ? $fileData['date'] : '');
        $fileNameLate = Str::slug($subjectLate, '_') . '_' . preg_replace('/@.*/', '', $fileData['email']);
        $fullPathFile = storage_path(self::FOLDER_APP . self::FOLDER_LATE);
        Excel::create($fileNameLate, function ($excel) use ($infoMinuteLate) {
            $excel->setTitle('time');
            $excel->sheet('time', function($sheet) use ($infoMinuteLate) {
                // insert title
                $sheet->fromArray([[
                    'ID chấm công',
                    'Account',
                    'Họ tên',
                    'Số phút đi muộn'
                ]], null,'A1', false, false);
                // insert data
                $sheet->fromArray($infoMinuteLate, null,'A1', false, false);
            });
        })->store(self::EXTENSION_FILE, $fullPathFile);
        $emailQueue = new EmailQueue();
        $emailQueue->setSubject($subjectLate)
            ->setTo($fileData['email'])
            ->setTemplate('event::send_email.email.ts_minute_late', $fileData)
            ->addAttachment($fullPathFile . $fileNameLate . '.' . self::EXTENSION_FILE)
            ->save();
        $this->removeProcess();
        Storage::delete($filePath); // file data
        Storage::delete(self::FOLDER_UPLOAD . $fileData['file']); // file excel upload
    }

    /**
     * create and chmod files use timekeeping
     */
    public function createFiles()
    {
        CoreForm::makeDir(self::FOLDER_UPLOAD);
        CoreForm::makeDir(self::FOLDER_PROCESS);
        CoreForm::makeDir(self::FOLDER_DATA);
        CoreForm::makeDir(self::FOLDER_LATE);
    }

    /**
     * create process flag
     */
    public function createProcess()
    {
        Storage::put(self::FILE_PROCESS, 1);
        CoreForm::chmodFile(self::FILE_PROCESS);
    }

    /**
     * check is process analyze timesheet to fines
     *
     * @return type
     */
    public function isProcess()
    {
        return Storage::exists(self::FILE_PROCESS);
    }

    /**
     * remove process
     */
    public function removeProcess()
    {
        Storage::delete(self::FILE_PROCESS);
    }

    /**
     * @return \self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static;
        }
        return self::$instance;
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
}
