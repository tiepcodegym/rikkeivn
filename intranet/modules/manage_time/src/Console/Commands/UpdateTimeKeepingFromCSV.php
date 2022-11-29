<?php

namespace Rikkei\ManageTime\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\ManageTime\Model\Timekeeping;
use Excel;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\View\View;
use Rikkei\ManageTime\Model\TimekeepingNotLateTime;
use Carbon\Carbon;

class UpdateTimeKeepingFromCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timekeeping:update_from_csv {date=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật bảng manage_time_timekeepings thông qua file csv';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * calculate time working
     *
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            Log::useFiles(storage_path() . '/logs/timekeeping.log');
            $dateTime = $this->argument('date');
            $this->info('=== Start update manage_time_timekeepings table by csv with date = ' .$dateTime);
            Log::info('=== Start update manage_time_timekeepings table by csv with date = ' .$dateTime);

            if ($dateTime) {
                $arrDateTime = explode("-", $dateTime);
                if (count($arrDateTime) < 3) {
                    $this->info('-- The time is not a valid. --');
                    Log::info('-- The time is not a valid. --');
                    return false;
                }
                if (!is_numeric($arrDateTime[0]) || !is_numeric($arrDateTime[1]) || !is_numeric($arrDateTime[2])) {
                    $this->info('-- The time is not a valid. --');
                    Log::info('-- The time is not a valid. --');
                    return false;
                }
            }
            $year = isset($arrDateTime[0]) ? $arrDateTime[0] : date('Y');
            $month = isset($arrDateTime[1]) ? $arrDateTime[1] : date('m');
            $date = isset($arrDateTime[2]) ? $arrDateTime[2] : date('d');
            $dateText = $year.'_'.$month.'_'.$date;

            $pathBackUp = storage_path("app/time_in_out_backup/");
            $path = storage_path('app/time_in_out');
            $files = File::allFiles($path);

            // Lấy ds nhân viên được phép đi muộn
            $empLateList =  TimekeepingNotLateTime::select([
                    'employee_id',
                    'minute',
                ])->whereDate('start_date', '<=', $year.'-'.$month.'-'.$date)
                    ->whereDate('end_date', '>=', $year.'-'.$month.'-'.$date)
                    ->get();
            $arrEmpLate = [];
            $arrEmpIdLate = [];
            if ($empLateList) {
                foreach ($empLateList as $list) {
                    $arrEmpLate[$list->employee_id] = $list->minute;
                    $arrEmpIdLate[] = $list->employee_id;
                }
            }
            foreach ($files as $file) {
                $fileName = $file->getRelativePathname();
                $arrFileName = explode("-", $fileName);
                if (count($arrFileName) < 2) {
                    continue;
                }
                $arrFileNameDate = explode(".", $arrFileName[1]);
                if (count($arrFileNameDate) < 2 || $arrFileNameDate[0] != $dateText) {
                    continue;
                }

                $branch = $arrFileName[0];
                if ($branch && in_array($branch, View::getListBranch())) {
                    $branchText = $this->_detectBranch($branch);
                    $tables = DB::table('manage_time_timekeeping_tables')
                        ->select('manage_time_timekeeping_tables.*',
                            'teams.branch_code'
                        )
                        ->join('teams', 'manage_time_timekeeping_tables.team_id', '=', "teams.id")
                        ->where('teams.branch_code', $branch)
                        ->where('manage_time_timekeeping_tables.month', $month)
                        ->where('manage_time_timekeeping_tables.year', $year)
                        ->whereNull('manage_time_timekeeping_tables.deleted_at')
                        ->groupBy('manage_time_timekeeping_tables.id')
                        ->get();

                    if ($tables) {
                        foreach ($tables as $table) {
                            $empIds = Timekeeping::where('timekeeping_table_id', $table->id)->distinct()->pluck('employee_id')->toArray();
                            $dataExcel = Excel::load(storage_path('app/time_in_out/'.$fileName), function($reader) {})->get();
                            foreach ($dataExcel as $item) {
                                $empIdCard = $item['emp_ids'];
                                // Mã thẻ của NV về từ JP
                                // Mã này unique nên get nhân viên từ mã thẻ luôn
                                if ((int)$empIdCard >= 7000) {
                                    $employee = Employee::where('employee_card_id', $empIdCard)->first();
                                } else {
                                    if ((int)$empIdCard >= 5000 ) {
                                        $empCode = 'TTS_' . $empIdCard;
                                    } else {
                                        $empCode = $branchText . $this->_addZeroNumber(strlen($empIdCard)) . $empIdCard;
                                    }
                                    $employee = Employee::where('employee_code', $empCode)->first();
                                }
                                // Mã thẻ của TTS
                                
                                if (!$employee) {
                                    continue;
                                }
                                if (in_array($employee->id, $empIds)) {
                                    $empTimeKeeping = Timekeeping::where('timekeeping_table_id', $table->id)
                                        ->where('employee_id', $employee->id)
                                        ->where('timekeeping_date', $dateText)
                                        ->first();
                                    if ($empTimeKeeping) {
                                        if (strlen($item['time_in_out']) > 0) {
                                            Log::info('-- Start update manage_time_timekeepings: id = ' .$empTimeKeeping->id);
                                            $timeItem = explode("/", $item['time_in_out']);
                                            $min = null;
                                            $max = null;
                                            
                                            if (count($timeItem) == 1) {
                                                $min = date_format(date_create($timeItem[0]), "H:i");
                                            } else { // Get min & max time
                                                $min = date_format(date_create($timeItem[0]), "H:i");
                                                $max = date_format(date_create($timeItem[0]), "H:i");
                                                foreach ($timeItem as $key => $value) {
                                                    $timeItem[$key] = date_format(date_create($timeItem[$key]), "H:i");
                                                    if ($timeItem[$key] < $min) {
                                                        $min = $timeItem[$key];
                                                    }
                                                    if ($timeItem[$key] > $max) {
                                                        $max = $timeItem[$key];
                                                    }
                                                }
                                            }

                                            if ($min) {
                                                $fieldStart = $this->setRangeTime($min);
                                                $empTimeKeeping->$fieldStart = $min;
                                                if ($empTimeKeeping->$fieldStart) {
                                                    $empTimeKeeping->$fieldStart = $min < $empTimeKeeping->$fieldStart ? $min : $empTimeKeeping->$fieldStart;
                                                }
                                                if ($fieldStart == 'start_time_morning_shift' && $empTimeKeeping->start_time_morning_shift) {
                                                    $empTimeKeeping->start_time_morning_shift_real = $empTimeKeeping->start_time_morning_shift;
                                                }
                                            }

                                            if ($max) {
                                                $fieldEnd = $this->setRangeTime($max);
                                                // Nếu range của max khác min thì insert, nếu giống duration thì bỏ qua
                                                if ((isset($fieldStart) && $fieldEnd != $fieldStart) || !$this->isTimeStart($fieldEnd)) {
                                                    $empTimeKeeping->$fieldEnd = $max;
                                                    if ($empTimeKeeping->$fieldEnd) {
                                                        $empTimeKeeping->$fieldEnd = $max > $empTimeKeeping->$fieldEnd ? $max : $empTimeKeeping->$fieldEnd;
                                                    }
                                                }
                                            }

                                            // Kiểm tra nếu nhân viên được set có thể đi muộn thì set lại cột start_time_morning_shift
                                            if ($empTimeKeeping->start_time_morning_shift && in_array($empTimeKeeping->employee_id, $arrEmpIdLate)) {
                                                $hour = explode(':', $empTimeKeeping->start_time_morning_shift)[0];
                                                $minute = explode(':', $empTimeKeeping->start_time_morning_shift)[1];
                                                $tempDate = Carbon::now();
                                                $tempDate->hour = $hour;
                                                $tempDate->minute = $minute;
                                                $tempDate->subMinutes($arrEmpLate[$empTimeKeeping->employee_id]);
                                                $empTimeKeeping->start_time_morning_shift = $tempDate->hour . ':' . $tempDate->minute;
                                            }
                                            $empTimeKeeping->save();
                                            Log::info('--- End update manage_time_timekeepings: id = ' .$empTimeKeeping->id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $this->sendMailNoti(1, $fileName);
            }

            // move time keeping files to backup folder
            if (!file_exists($pathBackUp)) {
                mkdir($pathBackUp, 0777);
            }
            File::copyDirectory($path, $pathBackUp);
            File::cleanDirectory($path);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            $this->info($e->getMessage());
            $this->sendMailNoti(2, null, $e->getMessage());
        } finally {
            $this->info('=== End update manage_time_timekeepings table by csv with date = ' .$dateTime);
            Log::info('=== End update manage_time_timekeepings table by csv with date = ' .$dateTime);
        }
    }

    // private function sendMailNoti($type, $fileName, $error = null)
    // {
    //     $emailQueue = new EmailQueue();
    //     $data = [
    //         'fileName' => $fileName,
    //         'error' => $error,
    //     ];
        
    //     $emailQueue->setTo('hungnt2@rikkeisoft.com')
    //             ->setSubject($this->getSubject($type))
    //             ->setTemplate('manage_time::template.timekeeping.mail_noti_update_time_in_out', $data)
    //             ->save();
    // }

    private function getSubject($type)
    {
        if ($type === 1) {
            return '[Rikkei.vn] Cập nhật giờ vào/ra từ file csv Thành công';
        }
        return '[Rikkei.vn] Cập nhật giờ vào/ra từ file csv Lỗi';
    }

    /**
     * Xác định time truyền vào là giờ vào/ra sáng hay vào/ra chiều
     * Trả về field tương ứng trong bảng manage_time_timekeepings
     *
     * @param string $time  H:i
     * @return string   field in database
     */
    private function setRangeTime($time)
    {
        $startMorning = '10:30';
        $endMorning = '13:00';
        $startAfternoon = '16:00';
        if ($time > $startAfternoon) {
            return 'end_time_afternoon_shift';
        }
        if ($time < $startMorning) {
            return 'start_time_morning_shift';
        }
        if ($time < $endMorning) {
            return 'end_time_morning_shift';
        }
        return 'start_time_afternoon_shift';
    }

    private function isTimeStart($range)
    {
        return in_array($range, ['start_time_morning_shift', 'start_time_afternoon_shift']);
    }

    private function _detectBranch($branch){
        if ($branch == 'hanoi') {
            return 'NV';
        } elseif ($branch == 'hcm') {
            return 'HCM';
        } elseif ($branch == 'danang') {
            return 'DN';
        }
    }

    private function _addZeroNumber($empIdCard){
        switch ($empIdCard) {
            case 0:
                return '0000000';
                break;
            case 1:
                return '000000';
                break;
            case 2:
                return '00000';
                break;
            case 3:
                return '0000';
                break;
            case 4:
                return '000';
                break;
            case 5:
                return '00';
                break;
            case 6:
                return '0';
                break;
            default:
                return '';
                break;
        }
    }
}
