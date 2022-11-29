<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Lang;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Border;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Event\View\ViewEvent;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\View\View as ManageView;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\ProjectPermission;
use Rikkei\Project\Model\Project;
use Illuminate\Filesystem\Filesystem;
use Rikkei\Project\Model\ProjEmployeeSystena;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use ZipArchive;

class ProjectController extends Controller
{
    const FOLDER_SYSTENA = 'systena_export';
    const ACCESS_FOLDER = 0777;

    /*
     * constructer
     */
    public function _construct()
    {
        if (!ProjectPermission::isAllowReport()) {
           View::viewErrorPermission();
        }
    }

    public function index(Request $request)
    {
        if (!ProjectPermission::isAllowReport()) {
            View::viewErrorPermission();
        }

        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $month = $request->get('month');
        if ($month && !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $month)) {
            return redirect()->route('manage_time::timekeeping.manage.report_project_timekeeping_systena');
        }

        $empIds = Project::getEmpProjectSystena($dataFilter)->get()->lists('empId')->toArray();
        $empIds = array_unique(array_merge($empIds, ProjEmployeeSystena::getAllEmp()->lists('empId')->toArray()));
        $timeKeepingMax = Timekeeping::getMaxTimekeepingDate($empIds);
        if (!$timeKeepingMax) {
            return redirect()->back()->withErrors(Lang::get('manage_time::message.Not exist timekeeping table'));
        }
        $monthMax = Carbon::createFromFormat('Y-m-d', $timeKeepingMax->timekeeping_date)->format('Y-m');

        if (!$month) {
            $month = $monthMax;
        }
        $monthDay = $month;
        $collectionModel = Project::getEmployeeProjectSystena($dataFilter, $month);
        with(new Team())->setCacheholidaysCompensate();
        $params = [
            'month' => $month,
            'monthDay' => $monthDay,
            'monthNow' => $monthMax,
            'collectionModel' => $collectionModel
        ];

        return view('manage_time::report.project_timekeeping_systena', $params);
    }

    /**
     * export excel timekeeping project systena cron 5'
     * @param  Request $request
     * @return [type]
     */
    public static function exportProjectSystenaCron($file)
    {
        $resultMore = '';
        $result = preg_match('/[0-9]+.*$/', $file, $resultMore);
        if (!$result || !$resultMore) {
            return true;
        }
        $resultMore = substr($resultMore[0], 0, strrpos($resultMore[0], '.'));
        $info = explode('_', $resultMore);
        $month = $info[2];
        $userCurrent = Employee::getEmpById($info[0]);
        $emailQueue = new EmailQueue();
        if (!$month || !$userCurrent) {
            $emailQueue->setTo($userCurrent->email)
            ->setSubject(Lang::get('manage_time::view.File salary systena :time', ['time' => $month . '-' . $year]))
            ->setTemplate('event::send_email.email.systena', [
                'emailData' => $userCurrent->email,
                'userCurrent' => $userCurrent,
                'month' =>  $month . '-' . $year,
                'error' => Log::info('Tên file export công systena sai đinh dạng hoặc không tìm thấy nhân viên'),
            ])
            ->save();
            return;
        }
        $excel = Excel::selectSheetsByIndex(0)->load(storage_path('app/' . $file), function ($reader) {
        })->get()->toArray();
        if (count($excel)) {
            $arrEmpId = array_unique(explode(',', $excel[0]['emp_ids']));
        } else {
            $arrEmpId = [];
        }
        ///===========
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $collectionModel = Project::getEmployeeProjectSystenaExport($dataFilter, $month)->get();

        $proj = [];
        if ($collectionModel) {
            foreach ($collectionModel as $item) {
                $proj[$item->projId][$item->empId] = [
                    "projId" => $item->projMemId,
                    "projName" => $item->projName,
                    "projStart" => $item->projStart,
                    "projEnd" => $item->projEnd,
                    "empStart" => $item->empStart,
                    "empEnd" => $item->empEnd,
                    "empName" => $item->empName,
                    "empCode" => $item->empCode,
                ];
            }
        }
        // get time holiday and weekday
        $empIds = $collectionModel->lists('empId')->toArray();
        $teamEmp = [];
        $inforEmp = [];
        $employees = Employee::whereIn('id', $empIds)->get();
        foreach ($employees as $employee) {
            $inforEmp[$employee->id] = $employee;
            if (!$employee) {
                continue;
            } else {
                $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
            }
            $teamEmp[$employee->id] = $teamCodePrefix;
        }
        $holidayWeekday = with(new Team())->getHolidaysCompensate();
        $date = Carbon::createFromFormat('Y-m', $month);
        $start = $date->startOfMonth()->toDateString();
        $end = $date->endOfMonth()->toDateString();

        $workingEmps = [];
        $arrNameEmp = [];
        list($year, $month) = explode('-', $month);
        if (count($proj)) {
            foreach ($proj as $projId => $valProjs) {
                foreach ($valProjs as $empId => $valEmp) {
                    $autoTimeOT = false;
                    if (in_array($empId, $arrEmpId)) {
                        $autoTimeOT = true;
                    }
                    $startTimekeeping = $start;
                    $endTimeKeeping = $end;

                    if ($startTimekeeping < $valEmp['empStart']) {
                        $startTimekeeping = $valEmp['empStart'];
                    }

                    if ($endTimeKeeping > $valEmp['empEnd']) {
                        $endTimeKeeping = $valEmp['empEnd'];
                    }
                    $datas = Timekeeping::getTimeworkSystena($startTimekeeping, $endTimeKeeping, $inforEmp[$empId], $autoTimeOT, $teamEmp[$empId], $holidayWeekday[$teamEmp[$empId]]);
                    if (count($datas)) {
                        foreach ($datas as $key => $data) {
                            foreach ($data as $date => $value) {
                                if (isset($workingEmps[$empId]['date'][$date])) {
                                    if (!empty((float)$value["timeWork"])) {
                                        $workingEmps[$empId]['date'][$date] = $value;
                                    }
                                } else {
                                    $workingEmps[$empId]['date'][$date] = $value;
                                }
                            }
                            ksort($workingEmps[$empId]['date']);
                        }
                        $workingEmps[$empId]['projName'] = $valEmp['projName'];
                        $workingEmps[$empId]['empName'] = $valEmp['empName'];
                        $workingEmps[$empId]['year'] = $year;
                        $workingEmps[$empId]['mont'] = $month;
                        $workingEmps[$empId]['number'] = 1;
                        $workingEmps[$empId]['empNameId'] = $valEmp['empName'] . '_' . $valEmp["empCode"];
                        if (!array_key_exists($empId, $arrNameEmp)) {
                            if (in_array($valEmp['empName'], $arrNameEmp)) {
                                foreach ($arrNameEmp as $keyEmpId => $nameEmp) {
                                    if ($keyEmpId != $empId && $nameEmp == $valEmp['empName']) {
                                        $workingEmps[$keyEmpId]['number']++;
                                    }
                                }
                                $workingEmps[$empId]['number']++;
                            }
                            $arrNameEmp[$empId] = $valEmp['empName'];
                        }
                    }
                }
            }
        }
        if (!count($workingEmps)) {
            $emailQueue->setTo($userCurrent->email)
            ->setSubject(Lang::get('manage_time::view.File salary systena :time', ['time' => $month . '-' . $year]))
            ->setTemplate('event::send_email.email.systena', [
                'emailData' => $userCurrent->email,
                'userCurrent' => $userCurrent,
                'month' =>  $month . '-' . $year,
                'error' => Lang::get('manage_time::message.No employee'),
            ])
            ->save();
            return;
        }
        try {
            foreach ($workingEmps as $key => $workEmp) {
                if ($workEmp['number'] == 1) {
                    $keyName = 'empName';
                } else {
                    $keyName = 'empNameId';
                }
                Excel::create($year . $month . View::convertString($workEmp[$keyName]), function ($excel) use ($workEmp, $year, $month) {
                $excel->sheet('Sheet 1', function ($sheet) use ($workEmp, $year, $month) {
                    $name = $workEmp['empName'];
                    $data = [];
                    $data[0] = ['勤務表'];
                    $data[1] = ["{$year}", '', '年', "{$month}", '月', '', '承認', '担当'];
                    $data[2] = ['会社名', '', '', 'Rikkeisoft', '','', '', ''];
                    $data[3] = ['氏名', '', '', "{$name}", '','', '', ''];
                    $data[4] = ['日付', '曜日', '時間', '', '', '備考(作業内容)','', ''];
                    $data[5] = ['', '', '開始時間', '終了時間', '実働時間', '','', ''];

                    $i = 0;
                    $total = 0;
                    $j = 7;
                    foreach ($workEmp["date"] as $date => $item) {
                        $total = $total + round((float)$item['timeWork'], 2);
                        if (empty($item['timeWork']) && (empty($item['timeIn']) || empty($item['timeOut']))) {
                            $time = '';
                        } else {
                            $time = round($item['timeWork'], 2);
                        }
                        $data[] = [
                            (int)$date,
                            $item['dayOfWeek'],
                            $item['timeIn'],
                            $item['timeOut'],
                            $time,
                            $item['note'],
                        ];
                        $sheet->mergeCells("F{$j}:H{$j}");
                        $j++;
                    }
                    $data[count($data) + 1] = ['合計', '', '', '', "{$total}",'', ''];
                    $countData = count($data);
                    $sheet->fromArray($data, null, 'A1', true, false);
                    $sheet->mergeCells('A1:H1');
                    $sheet->mergeCells('A2:B2');
                    $sheet->mergeCells('A3:C3');
                    $sheet->mergeCells('D3:F3');
                    $sheet->mergeCells('A4:C4');
                    $sheet->mergeCells('D4:F4');
                    $sheet->mergeCells('C5:E5');
                    $sheet->mergeCells('F5:H5');
                    $sheet->mergeCells("F6:H6");
                    $sheet->mergeCells("A{$countData}:D{$countData}");

                    $sheet->setMergeColumn(array(
                        'columns' => array('A', 'B','F', 'H'),
                        'rows' =>array(array(5,6), array(5,6))
                    ));

                    $sheet->cells('A1:H4', function ($cells) {
                        $cells->setFontWeight('bold');
                    });
                    $sheet->cells('A1:H6', function ($cells) {
                        $cells->setBackground('#CEEBC3');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });

                    $array = array('A2:B2', 'D2:D2', 'F2:F2', 'G3:H4');
                    $numArray = count($array);

                    for ($i=0; $i < $numArray ; $i++) {
                        $sheet->cells("{$array[$i]}", function ($cells) {
                            $cells->setFontWeight('bold');
                            $cells->setBackground('#FFFFFF');
                            $cells->setAlignment('center');
                            $cells->setValignment('center');
                        });
                    }
                    $sheet->cells("A7:B{$countData}", function ($cells) {
                        $cells->setBackground('#E2EFD9');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });

                    $sheet->cells("A7:A{$countData}", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });
                    $sheet->cells("A{$countData}:D{$countData}", function ($cells) {
                        $cells->setBackground('#CEEBC3');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });
                    $sheet->cells("E{$countData}:E{$countData}", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });
                    $countDa = $countData -1;
                    $sheet->cells("C7:H{$countDa}", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });
                    $sheet->mergeCells("F{$countData}:H{$countData}");
                    $sheet->setHeight([
                        1     =>  25,
                        2     =>  25
                    ]);
                    $sheet->setWidth([
                        'A'     =>  7,
                        'B'     =>  18,
                        'C'     =>  15,
                        'D'     =>  15,
                        'E'     =>  15,
                    ]);

                    $styleArray = array(
                      'borders' => array(
                        'allborders' => array(
                          'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                      )
                    );
                    $sheet->setBorder("A1:H{$countData}", 'thin');
                    $sheet->getStyle("A1:H{$countData}")->applyFromArray($styleArray);

                });
                $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
                })->store('xlsx');
            }
            $folderTemp = 'systena';
            ViewEvent::createDir($folderTemp);
            $zipFile = storage_path('app/' . $folderTemp . '/' . $userCurrent->id  . '_' .  $year. $month . '_exports_systena.zip');
            @chmod($zipFile, ViewEvent::ACCESS_FOLDER);
            $zip = new ZipArchive();
            if (Storage::exists($zipFile)) {
                return response()->json([
                    'success'=> 0,
                    'message'=> Lang::get('manage_time::message.Processing export file before, please try again!'),
                ]);
            }
            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $path = storage_path('exports');
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
            foreach ($files as $name => $file)
            {
                // We're skipping all subfolders
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // extracting filename with substr/strlen
                    $relativePath = 'exports/' . substr($filePath, strlen($path) + 1);

                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            Storage::disk('base')->deleteDirectory('exports');
            //==========
            $emailQueue->setTo($userCurrent->email)
            ->setSubject(Lang::get('manage_time::view.File salary systena :time', ['time' => $month . '-' . $year]))
            ->setTemplate('event::send_email.email.systena', [
                'emailData' => $userCurrent->email,
                'userCurrent' => $userCurrent,
                'month' =>  $month . '-' . $year,
            ])
            ->addAttachment($zipFile)
            ->save();
            //============
            return;
        } catch (Exception $ex) {
            Storage::disk('base')->deleteDirectory('exports');
            DB::rollback();
            Log::info($ex);
            return response()->json('Error!', 500);
        }
    }

    /**
     *  create employee for calculation timekeekping
     * @param Request $request [description]
     */
    public static function addProjectSystena(Request $request)
    {
        $idEmps = $request->related_persons_list;
        if (!count($idEmps)) {
            return redirect()->back()->withErrors(Lang::get('manage_time::view.No results found'));
        }
        $projs = Project::getProjectSystena();
        $empProjs = ProjEmployeeSystena::getAllEmp();
        $check = false;
        if ($empProjs) {
            foreach ($empProjs as $key => $emp) {
                if (in_array($emp->empId, $idEmps)) {
                    $check = $emp->empName;
                    break;
                }
            }
        }

        if ($projs && !$check) {
            foreach ($projs as $key => $emp) {
                if (in_array($emp->empId, $idEmps)) {
                    $check = $emp->empName;
                    break;
                }
            }
        }
        if ($check) {
            return redirect()->back()->withErrors(Lang::get('manage_time::message.Employee: :employee exists', ["employee" => $emp->empName]));
        }
        foreach ($idEmps as $key => $item) {
            ProjEmployeeSystena::create(['employee_id' => $item]);
        }
        return redirect()->back()->with('flash_success', Lang::get('manage_time::view.Save success message'));
    }

    /**
     * [removeEmpProjSystena description]
     * @param  Request $request
     * @return [type]
     */
    public static function removeEmpProjSystena(Request $request)
    {
        $emp = ProjEmployeeSystena::findOrFail($request->id);
        $emp->delete();
        return redirect()->back()->with('messages', ['success' => [trans('asset::message.Delete data success')]]);
    }


    /**
     * export excel timekeeping project systena
     * @param  Request $request
     * @return [type]
     */
    public function exportProjectSystena(Request $request)
    {
        if (!ProjectPermission::isAllowReport()) {
           View::viewErrorPermission();
        }
        $month = $request->month;
        if (!$month) {
            $month = Carbon::now()->format('Y-m');
        }

        // check exits timekeeping number
        if (!Timekeeping::isTimekeepingDate($month)) {
            return response()->json([
                'success'=> 0,
                'message'=> Lang::get('manage_time::message.Not exist timekeeping table'),
            ]);
        }

        try {
            if (!Storage::exists(ManageView::FOLDER_EXP_SYSTENA)) {
                Storage::makeDirectory(ManageView::FOLDER_EXP_SYSTENA, ManageView::ACCESS_FOLDER);
            }
            $userCurrent = Permission::getInstance()->getEmployee();
            $fileName = 'systena_emp_' . $userCurrent->id . '_thang_' . $month;
            $folderPath = storage_path('app/' . ManageView::FOLDER_EXP_SYSTENA);

            $files = Storage::files(ManageView::FOLDER_EXP_SYSTENA);
            if ($files) {
                foreach ($files as $file) {
                   if ($file === ManageView::FOLDER_EXP_SYSTENA . '/' . $fileName . '.csv') {
                        return response()->json([
                            'success'=> 0,
                            'message'=> Lang::get('manage_time::message.Processing export, please try again'),
                        ]);
                   }
                }
            }

            Excel::create($fileName, function($excel) use ($request) {
                $excel->sheet('Sheet 1', function($sheet) use ($request) {
                    $data[0] = ['emp_ids'];
                    $data[1] = [$request->empIds];
                    $sheet->fromArray($data, null, 'A1', true, false);
                });
            })->store('csv', $folderPath);
            @chmod($folderPath . '/'. $fileName, ManageView::ACCESS_FOLDER);
            return response()->json([
                'success'=> 1,
                'message'=> Lang::get('manage_time::message.Salary systena file will be sent via gmail in a few minutes'),
            ]);
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            return response()->json([
                'success'=> 0,
                'message'=> $ex->getMessage(),
            ]);
        }
    }
}
