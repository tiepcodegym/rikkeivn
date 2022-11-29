<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Model\MonthlyReport;
use Rikkei\Team\Model\Team;
use Illuminate\Http\Request;
use Rikkei\Project\Model\MrBillable;
use Rikkei\Project\Model\MrBillableTime;
use Rikkei\Project\Model\Project;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Project\View\MRExcel;
use Maatwebsite\Excel\Collections\SheetCollection;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\TeamEffort;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CookieCore;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\Opportunity;
use Carbon\Carbon;
use Validator;

class MonthlyReportController extends Controller
{
    protected $indexUrl;
    protected $keyConfig = 'monthly_report_dconfig';
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('Project', 'monthly-report/index');
        $this->indexUrl = route('project::monthly.report.index') . '/';
    }
    
    /**
     * @return monthly report page
     */
    protected function index()
    {
        Breadcrumb::add(Lang::get('project::view.Monthly report'));
        $filterTime = MonthlyReport::getDefaultTime();
        //check if has import data
        if (Session::has('data_billable_import')) {
            //get billable data
            $cookieTime = CookieCore::getRaw('filter.' . $this->indexUrl);
            if ($cookieTime && count($cookieTime) == 3) {
                $filterTime = array_values($cookieTime);
            }
        }
        list($startMonth, $endMonth, $year) = $filterTime;

        $yearFilter = MonthlyReport::getAll();
        $values = [];

        $teamsByPermission = MonthlyReport::getTeam();
        $allTeam = Team::getTeamsChildest();
        $notAvailable = MonthlyReport::NOT_AVAILABLE;
        //Business
        $valueDb = MonthlyReport::findData($year);
        if ($valueDb && is_array(json_decode($valueDb->values, true))) {
            $values = json_decode($valueDb->values, true);
        } else {
            $values = [];
        }
        $curYear = date('Y');
        return view('project::monthly_report.index',
            compact(
                'values',
                'teamsByPermission',
                'notAvailable',
                'startMonth',
                'endMonth',
                'yearFilter',
                'curYear',
                'allTeam',
                'year'
            )
        );
    }

    /**
     * Update data when fill data from NA, Budget (cash Man), Cost (cash Man), Budget (MM)
     * 
     * @param Request $request
     * @return void
     */
    public function update(Request $request)
    {
        $data = $request->all();
        $year = $data['year'];
        $changeValues = json_decode($data['values'], true);
        //if data has changes
        if (count($changeValues)) {
            //Check update fields in array
            $keyValues = [
                MonthlyReport::ROW_PLAN_REVENUE,
                MonthlyReport::ROW_PLAN,
                MonthlyReport::ROW_BILLACTUAL,
                MonthlyReport::ROW_COST,
                MonthlyReport::ROW_BUSINESS_EFFECTIVE,
                MonthlyReport::ROW_COMPLETED_PLAN,
                //MonthlyReport::ROW_BILL_STAFF_PLAN,
                //MonthlyReport::ROW_BILL_STAFF_ACTUAL,
                MonthlyReport::ROW_ALLO_STAFF_ACTUAL,
                MonthlyReport::ROW_PROD_STAFF_ACTUAL,
                MonthlyReport::ROW_TRAINING_PLAN,
            ];
            $valueDb = MonthlyReport::findData($year);
            if ($valueDb) {
                $values = json_decode($valueDb->values, true);
                foreach ($values as $teamId => $value) {
                    for ($month=1; $month<=12; $month++) {
                        foreach ($keyValues as $k => $v) {
                            if (isset($changeValues[$teamId][$month][$v]['value']) 
                                && (!isset($values[$teamId][$month][$v]['value'])
                                    || $changeValues[$teamId][$month][$v]['value'] != $values[$teamId][$month][$v]['value'] )
                            ) {
                                $values[$teamId][$month][$v]['value'] = $changeValues[$teamId][$month][$v]['value'];
                            }
                        }
                    }
                }
                $values = MonthlyReport::setPoint($values);
                $valueDb->values = json_encode($values);
                $valueDb->save();
            }
        }
    }
    
    public function search(Request $request)
    {
        $data = $request->all();
        $startMonth = $data['startMonthFilter'];
        $endMonth = $data['endMonthFilter'];
        $yearFilter = $data['yearFilter'];
        CookieCore::setRaw('filter.'. $this->indexUrl, $request->except('_token'));

        $values = [];
        $teamsByPermission = MonthlyReport::getTeam();
        $notAvailable = MonthlyReport::NOT_AVAILABLE;
        
        $valueDb = MonthlyReport::findData($yearFilter);
        if ($valueDb && is_array(json_decode($valueDb->values, true))) {
            $values = json_decode($valueDb->values, true);
        } else {
            $values = [];
        }
        
        $html = view('project::monthly_report.include.data', 
            compact(
                'values',
                'teamsByPermission',
                'notAvailable',
                'startMonth',
                'endMonth',
                'yearFilter'
            )
        )->render();
        
        return response()->json([
            'html' => $html,
            'values' => $values,
        ]);
    }

    /**
     * export billable
     * @param Request $request
     * @return type
     * @throws \Exception
     */
    public function exportBillable(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'team_id' => 'required',
            'from_month' => 'required',
            'to_month' => 'required'
        ], [
            'team_id.required' => trans('validation.required', ['attribute' => 'Team']),
            'from_month.required' => trans('validation.required', ['attribute' => 'From month']),
            'to_month.required' => trans('validation.required', ['attribute' => 'To month']),
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::message.Please input valid data'), 422);
        }
        $teamId = $request->get('team_id');
        $team = Team::findOrFail($teamId);
        try {
            $fromMonth = Carbon::createFromFormat('m-Y', $request->get('from_month'))->startOfMonth();
            $toMonth = Carbon::createFromFormat('m-Y', $request->get('to_month'))->endOfMonth();
            if ($toMonth->lt($fromMonth)) {
                throw new \Exception(trans('project::message.Error date format'));
            }
        } catch (\Exception $ex) {
            return response()->json(trans('project::message.Error date format'), 422);
        }
        //check sample data
        $data = [
            'team_id' => $teamId,
            'from_month' => $fromMonth,
            'to_month' => $toMonth
        ];
        $listRoles = ProjectMember::getTypeWithOT();
        $collectAllowcate = Opportunity::getDataExport($data);
        $collectBudget = Opportunity::getDataExport($data, Opportunity::STATUS_OPPORTUNITY);

        $arrayMonths = MRExcel::monthColumns($fromMonth, $toMonth);
        $idxStatusCol = ord(MRExcel::OFFSET_COL) + count($arrayMonths) * MRExcel::NUM_PER_MONTH;
        $statusColumn = MRExcel::getColNameByIndex($idxStatusCol);
        $companyColumn = MRExcel::getColNameByIndex($idxStatusCol + 6);
        $projectColumn = MRExcel::getColNameByIndex($idxStatusCol + 7);
        $projCodeColumn = MRExcel::getColNameByIndex($idxStatusCol + 8);
        $memberColumn = MRExcel::getColNameByIndex($idxStatusCol + 9);

        $arrayTypeLabels = Opportunity::labelTypeProject();
        //implode data
        $listProjStatuses = Project::lablelState();
        //sumary rows
        $headTotalRows = array_merge(['Revenue(USD)', 'Total'], $arrayTypeLabels);
        //offset row begin data insert
        $offsetRow = count($headTotalRows) + 4;

        $templates = [];
        //overview
        $hrData = MrBillable::getListHrData($data);
        $teams = MonthlyReport::getTeam();
        $currTeamName = MRExcel::shortName($team->name);
        $sheetSummary = $currTeamName . '_Summary';
        $teamBillables = MrBillable::getTotalBillableOfTeam($data);
        $templates['Overview'] = view(
            'project::monthly_report.template.export-team-effort',
            compact('arrayMonths', 'hrData', 'teams', 'teamId', 'sheetSummary', 'teamBillables')
        )->render();
        //sheet running
        $collection = $collectAllowcate;
        $projRunning = true;
        $endColumn = MRExcel::getColNameByIndex(ord(MRExcel::OFFSET_COL) + count($arrayMonths) * MRExcel::NUM_PER_MONTH + 4);
        $collectCount = $collection->count() + MRExcel::MORE_COL;
        $sheetRunning = $currTeamName . '_Running';
        $templates[$sheetRunning] = view(
            'project::monthly_report.template.export-billable',
            compact(
                'collection',
                'fromMonth',
                'toMonth',
                'arrayTypeLabels',
                'headTotalRows',
                'arrayMonths',
                'projRunning',
                'currTeamName',
                'teams'
            )
        )->render();
        //sheet opportunity
        $collection = $collectBudget;
        $projRunning = false;
        $sheetOpportunity = $currTeamName . '_Opportunity';
        $templates[$sheetOpportunity] = view(
            'project::monthly_report.template.export-billable',
            compact(
                'collection',
                'fromMonth',
                'toMonth',
                'arrayTypeLabels',
                'headTotalRows',
                'arrayMonths',
                'projRunning',
                'currTeamName',
                'teams'
            )
        )->render();
        //summary
        $templates[$sheetSummary] = view(
            'project::monthly_report.template.export-summary',
            compact('headTotalRows', 'arrayMonths', 'arrayTypeLabels', 'sheetRunning', 'sheetOpportunity')
        )->render();
        //member
        $collection = $collectAllowcate;
        $projRunning = true;
        if (!$collectBudget->isEmpty()) {
            $collection = $collectBudget;
            $projRunning = false;
        }
        $groupMember = true;
        $sheetMember = $currTeamName . '_Member';
        $templates[$sheetMember] = view(
            'project::monthly_report.template.export-billable',
            compact(
                'collection',
                'fromMonth',
                'toMonth',
                'arrayTypeLabels',
                'headTotalRows',
                'arrayMonths',
                'groupMember',
                'projRunning',
                'currTeamName',
                'teams'
            )
        )->render();
        //free resorce
        $freeData = TeamEffort::getFreeEffortData($data);
        $templates['Free_Resource'] = view(
            'project::monthly_report.template.export-free-resource',
            compact('arrayMonths', 'freeData', 'teams')
        )->render();

        $fileName = $currTeamName . ' Operation template - ' . $fromMonth->format('Ym') . '_' . $toMonth->format('Ym');
        $colFormulas = [];
        return [
            'templates' => $templates,
            'fileName' => $fileName,
            'statusRange' => $statusColumn . $offsetRow . ':' . $statusColumn . $collectCount,
            'listProjStatuses' => array_values($listProjStatuses),
            'listStatusLabels' => MRExcel::listStatusLabels(),
            'offsetRow' => $offsetRow,
            'collectCount' => $collectCount,
            'colFomulas' => $colFormulas,
            'sheetRunning' => $sheetRunning,
            'sheetOpportunity' => $sheetOpportunity,
            'sheetMember' => $sheetMember
        ];
    }

    /**
     * import billable
     * @param Request $request
     * @return string
     */
    public function importBillable(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'team_id' => 'required'
        ], [
            'team_id.required' => trans('validation.required', ['attribute' => 'Team'])
        ]);
        if ($valid->fails()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($valid->errors())
                ->with('modal_error', '#modal_import_billable');
        }
        $teamId = $request->get('team_id');
        $team = Team::findOrFail($teamId);
        //get file
        $excelFile = $request->file('excel_file');
        if (!$excelFile) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('validation.required', ['attribute' => 'File import'])]]);
        }
        if (!in_array($excelFile->getClientOriginalExtension(), ['xlsx', 'xls'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('validation.mimes', ['attribute' => 'File import', 'values' => 'xlsx, xls'])]]);
        }
        //read file
        $reader = Excel::load($excelFile, function ($reader) {
            $reader->formatDates(false);
            $reader->calculate(true);
            $reader->noHeading();
            $reader->skipRows(1);
        })->get();
        if ($reader->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('project::message.Not found item.')]]);
        }
        $collectRunning = collect();
        $collectOpportunity = collect();
        $sheetRunning = MRExcel::shortName($team->name) . '_Running';
        $sheetOpportunity = MRExcel::shortName($team->name) . '_Opportunity';
        $hasRunning = false;
        $hasOpportunity = false;
        $slugRunning = str_slug($sheetRunning);
        $slugOpportunity = str_slug($sheetOpportunity);
        //if is sheet collection (multiple sheet)
        if ($reader instanceof SheetCollection) {
            foreach ($reader as $sheet) {
                if (str_slug($sheet->getTitle()) === $slugRunning) {
                    $collectRunning = $sheet;
                    $hasRunning = true;
                }
                if (str_slug($sheet->getTitle()) === $slugOpportunity) {
                    $collectOpportunity = $sheet;
                    $hasOpportunity = true;
                }
            }
        } else { //else if is row collection (1 sheet)
            if (str_slug($reader->getTitle()) == $slugRunning) {
                $collectRunning = $reader;
                $hasRunning = true;
            }
            if (str_slug($reader->getTitle()) == $slugOpportunity) {
                $collectOpportunity = $reader;
                $hasOpportunity = true;
            }
        }
        if (!$hasRunning && !$hasOpportunity) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', [
                    'errors' => [
                        trans('project::message.Not found sheet', [
                            'sheet' => MRExcel::shortName($team->name) . '_Running or '. MRExcel::shortName($team->name) .'_Opportunity'
                        ])
                    ]
                ]);
        }
        //get offest column data
        $offsetColData = 0;
        if (!$collectRunning->isEmpty()) {
            foreach ($collectRunning as $offset => $row) {
                $firstVal = strtolower(trim($row[0]));
                if ($firstVal === 'no') {
                    $offsetColData = $offset + 2;
                    break;
                } else {
                    unset($collectRunning[$offset]);
                    if (isset($collectOpportunity[$offset])) {
                        unset($collectOpportunity[$offset]);
                    }
                }
            }
        } elseif (!$collectOpportunity->isEmpty()) {
            foreach ($collectOpportunity as $offset => $row) {
                $firstVal = strtolower(trim($row[0]));
                if ($firstVal === 'no') {
                    $offsetColData = $offset + 2;
                    break;
                } else {
                    unset($collectOpportunity[$offset]);
                }
            }
        } else {
            //keep 0
        }

        if ($collectRunning->isEmpty() && $collectOpportunity->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('project::message.None item read or error file format')]]);
        }
        if (!$collectRunning->isEmpty()) {
            $heading = $collectRunning->first();
        } else {
            $heading = $collectOpportunity->first();
        }
        //get index next month
        $idxLastMonth = MRExcel::IDX_FIRST_MONTH + 1;
        for ($idx = count($heading) - 1; $idx > MRExcel::IDX_FIRST_MONTH; $idx--) {
            $rowName = $heading[$idx];
            if (strtolower($rowName) === 'status') {
                $idxLastMonth = $idx - MRExcel::NUM_PER_MONTH;
                break;
            }
        }

        $fromMonth = $request->get('from_month');
        $toMonth = $request->get('to_month');
        try {
            $firstMonth = trim($heading[MRExcel::IDX_FIRST_MONTH]);
            $firstMonth = Carbon::createFromFormat('m/Y', $firstMonth)->startOfMonth();
            $lastMonth = trim($heading[$idxLastMonth]);
            $lastMonth = Carbon::createFromFormat('m/Y', $lastMonth)->startOfMonth();
            //check if has from month and to month
            if ($fromMonth) {
                $fromMonth = Carbon::createFromFormat('m-Y', $fromMonth)->startOfMonth();
            }
            if ($toMonth) {
                $toMonth = Carbon::createFromFormat('m-Y', $toMonth)->startOfMonth();
            }
        } catch (\Exception $ex) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Error date format')]]);
        }

        //check validate from month and to month
        if ($fromMonth && $toMonth) {
            if ($fromMonth->gt($toMonth)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('project::message.The from month must be before to month')]]);
            }
        }
        if (($fromMonth && $fromMonth->gt($lastMonth)) || ($toMonth && $toMonth->lt($firstMonth))) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('project::message.From month or to month is not in range months in file')]]);
        }
        if ($fromMonth && $firstMonth->lt($fromMonth)) {
            $firstMonth = $fromMonth;
        }
        if ($toMonth && $lastMonth->gt($toMonth)) {
            $lastMonth = $toMonth;
        }
        //get list array month
        $arrayMonths = [];
        $condMonth = clone $firstMonth;
        $idxMonth = MRExcel::IDX_FIRST_MONTH;
        while ($condMonth->lte($lastMonth)) {
            //idx allowcate
            $arrayMonths[$idxMonth] = $condMonth->format('Y-m-d');
            $condMonth->addMonthNoOverflow();
            $idxMonth += MRExcel::NUM_PER_MONTH;
            //idx + 1 is billable (not set)
        }

        //unset heading row
        for ($i = $offsetColData - 2; $i < $offsetColData; $i++) {
            unset($collectRunning[$i]);
            unset($collectOpportunity[$i]);
        }
        $collectRunning->map(function ($item) {
            $item->is_running = 1;
        });
        $collectProjects = $collectRunning->merge($collectOpportunity);
        if ($collectProjects->isEmpty()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('project::message.None item read or error file format')]]);
        }
        $availbleData = MrBillable::getAvailbleData()->groupBy('project_name');
        $typesMember = ProjectMember::getTypeWithOT();
        $arrayTypeLabels = Project::labelTypeProject();
        $lableStates = Project::lablelState();
        //collect import data
        DB::beginTransaction();
        try {
            $idxAfterMonth = MRExcel::IDX_FIRST_MONTH + count($arrayMonths) * MRExcel::NUM_PER_MONTH;
            $tempRow = [
                'parent_id' => null,
                'code' => null,
                'customer_company' => null,
                'project_name' => null,
                'project_code' => null,
                'project_type' => null,
                'status' => null,
                'released_date' => null,
                'price' => null,
                'saleman' => null
            ];
            $tempRowTime = [];
            foreach ($collectProjects as $row) {
                $member = trim($row[MRExcel::IDX_MEMBER]);
                $projectName = trim($row[MRExcel::IDX_PROJ_NAME]);
                if (!$member && !$projectName) {
                    continue;
                }
                $code = trim($row[MRExcel::IDX_CODE]);
                $company = trim($row[MRExcel::IDX_COMPANY]);
                $projCode = trim($row[MRExcel::IDX_PROJ_CODE]);
                $projType = trim($row[MRExcel::IDX_PROJ_TYPE]);
                if (strtolower($projType) === 'project') {
                    $projType = 'Base';
                }
                $status = trim($row[$idxAfterMonth]);
                $relatedDate = trim($row[$idxAfterMonth + 1]);
                $price = trim($row[$idxAfterMonth + 2]);
                $saleman = trim($row[$idxAfterMonth + 3]);
                $rowData = [
                    'code' => $code,
                    'customer_company' => $company,
                    'project_name' => $projectName,
                    'project_code' => $projCode,
                    'project_type' => $projType,
                    'team_id' => $teamId,
                    'estimated' => trim($row[MRExcel::IDX_ESTIMATED]),
                    'member' => $member,
                    'role' => trim($row[MRExcel::IDX_ROLE]),
                    'effort' => trim($row[MRExcel::IDX_EFFORT]),
                    'start_at' => trim($row[MRExcel::IDX_START_AT]),
                    'end_at' => trim($row[MRExcel::IDX_END_AT]),
                    'status' => $status,
                    'released_date' => $relatedDate,
                    'price' => $price,
                    'saleman' => $saleman,
                    'is_running' => $row->is_running ? 1 : 0
                ];
                if (!$projectName && $tempRow['project_name']) {
                    $rowData = array_merge($rowData, $tempRow);
                } else {
                    $tempRow = array_merge(
                        $tempRow,
                        [
                            'code' => $code,
                            'customer_company' => $company,
                            'project_name' => $projectName,
                            'project_code' => $projCode,
                            'project_type' => $projType,
                            'status' => $status,
                            'released_date' => $relatedDate,
                            'price' => $price,
                            'saleman' => $saleman
                        ]
                    );
                }

                if ($row->is_running) {
                    if (!isset($availbleData[$rowData['project_name']])) {
                        continue;
                    }
                    $availbleMembers = $availbleData[$rowData['project_name']];
                    $hasMember = false;
                    foreach ($availbleMembers as $member) {
                        if (strtolower($member->member) === strtolower($rowData['member'])) {
                            $hasMember = true;
                            $rowData['customer_company'] = $member->customer_company;
                            $rowData['project_code'] = $member->project_code;
                            $rowData['released_date'] = $member->released_date;
                            $rowData['start_at'] = $member->start_at;
                            $rowData['end_at'] = $member->end_at;
                            if (!$rowData['role']) {
                                $rowData['role'] = ProjectMember::getType($member->role, $typesMember);
                            }
                            if (!$rowData['project_type']) {
                                $rowData['project_type'] = Project::getLabelState($member->project_type, $arrayTypeLabels);
                            }
                            if (!$rowData['estimated']) {
                                $rowData['estimated'] = $member->estimated;
                            }
                            if (!$rowData['effort']) {
                                $rowData['effort'] = $member->effort;
                            }
                            if (!$rowData['status']) {
                                $rowData['status'] = Project::getLabelState($member->status, $lableStates);
                            }
                            break;
                        }
                    }
                    if (!$hasMember) {
                        continue;
                    }
                }
                $billableReport = MrBillable::insertOrUpdate($rowData);
                if ($projectName) {
                    $tempRow['parent_id'] = $billableReport->id;
                }
                foreach ($arrayMonths as $idx => $month) {
                    $idxBudget = $idx + 1;
                    if (!isset($row[$idx]) || !isset($row[$idxBudget])
                            || !isset($row[$idx + 2]) || !isset($row[$idx + 3])) {
                        continue;
                    }
                    $allocate = trim($row[$idx]);
                    $billable = trim($row[$idxBudget]);
                    $approveCost = trim($row[$idx + 2]);
                    $note = trim($row[$idx + 3]);
                    if (!$projectName && $tempRow['project_name']) {
                        $productCost = isset($tempRowTime[$month]['billable']) ? $tempRowTime[$month]['billable'] : null;
                        $approveCost = isset($tempRowTime[$month]['approved_cost']) ? $tempRowTime[$month]['approved_cost'] : null;
                        $note = isset($tempRowTime[$month]['note']) ? $tempRowTime[$month]['note'] : null;
                    } else {
                        $tempRowTime[$month] = [
                            'billable' => $billable,
                            'approved_cost' => $approveCost,
                            'note' => $note
                        ];
                    }
                    $dataBillableTime = [
                        'report_id' => $billableReport->id,
                        'time' => $month
                    ];
                    if ($billable) {
                        $dataBillableTime['billable'] = $billable;
                    }
                    if ($allocate) {
                        $dataBillableTime['allocate'] = $allocate;
                    }
                    if ($approveCost) {
                        $dataBillableTime['approved_cost'] = $approveCost;
                    }
                    if ($note) {
                        $dataBillableTime['note'] = $note;
                    }
                    MrBillableTime::insertOrUpdate($dataBillableTime);
                }
            }
            DB::commit();
            return redirect()
                ->back()
                ->with('messages', ['success' => [trans('project::message.Import successful')]])
                ->with('data_billable_import', true);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Error system')]]);
        }
    }

    /**
     * team config factor
     * @return type
     */
    public function teamConfig()
    {
        Breadcrumb::add('D config');

        $keyConfig = $this->keyConfig;
        $dConfig = CoreConfigData::where('key', $keyConfig)->first();
        $teamsPermiss = MonthlyReport::getTeam();
        $typeMembers = ProjectMember::getTypeWithOT();
        if ($dConfig) {
            $dConfig = json_decode($dConfig->value, true);
        } else {
            $dConfig = [];
            if (!$teamsPermiss->isEmpty()) {
                foreach ($teamsPermiss as $team) {
                    foreach ($typeMembers as $key => $label) {
                        $dConfig[$team->id][$key] = 1;
                    }
                }
                CoreConfigData::saveItem($keyConfig, json_encode($dConfig));
            }
        }
        return view('project::monthly_report.dconfig', compact('teamsPermiss', 'typeMembers', 'dConfig'));
    }

    /**
     * save team config
     * @param Request $request
     */
    public function saveTeamConfig(Request $request)
    {
        $item = CoreConfigData::getItem($this->keyConfig);
        $item->value = json_encode($request->get('config'));
        $item->save();
        return redirect()
            ->back()
            ->with('messages', ['success' => [trans('project::message.Save successful')]]);
    }

    /**
     * help page
     */
    public function help()
    {
        Breadcrumb::add(trans('project::view.Monthly report'), route('project::monthly.report.index'));
        Breadcrumb::add('Help');
        return view('project::monthly_report.help');
    }
}
