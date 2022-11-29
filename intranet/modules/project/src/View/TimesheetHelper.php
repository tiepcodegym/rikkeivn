<?php

namespace Rikkei\Project\View;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Validation\UnauthorizedException;
use Illuminate\Support\Facades\Storage;
use Rikkei\Core\View\CurlHelper;
use Rikkei\Core\View\CurlHelper as Curl;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Timesheet;
use Rikkei\Project\Model\TimesheetItem;
use Rikkei\Project\Model\TimesheetItemDetail;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class TimesheetHelper
{
    const URI_GET_TOKEN = '/Api/access_token';
    const URI_GET_PO = '/Api/V8/custom/project/purchase-orders/list';
    const URI_GET_LINE_ITEM = '/Api/V8/custom/line-items/list';

    /**
     * TimesheetHelper constructor.
     */
    public function __construct()
    {

    }

    /**
     * Get Instance class
     * @return TimesheetHelper
     */
    public static function instance()
    {
        return new self();
    }

    /**
     * Get token API sales.rikkei.vn
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getToken()
    {
        $config = config('sales');
        $fileToken = 'sale_token.json';
        if (!Storage::exists($fileToken)) {
            Storage::put($fileToken, '');
        }

        $content = Storage::get($fileToken);
        $token = '';
        if (!empty($content)) {
            $token = json_decode($content, true);
        }

        if (!empty($token['access_token']) && $token['expires'] > date('Y-m-d H:i:s')) {
            return $token['access_token'];
        }

        $token = '';
        $count = 0;
        do {
            $url = $config['api_base_url'] . self::URI_GET_TOKEN;
            $data = [
                'grant_type' => 'client_credentials',
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ];

            $response = Curl::httpPost($url, $data);
            if (!empty($response)) {
                $response = json_decode($response, true);
                $tokenData = [
                    'access_token' => $response['access_token'],
                    'expires' => Carbon::now()->addMinutes(58)->format('Y-m-d H:i:s'),
                ];

                if (!empty($response)) {
                    Storage::put('sale_token.json', json_encode($tokenData));
                }

                $token = $response['access_token'];
            }

            $count++;
        } while (empty($token) && $count < 3);

        if (empty($token)) {
            throw new \Exception('Không lấy được token Sale CRM');
        }

        return $token;
    }

    /**
     * @param $projectId
     * @return mixed
     * @throws \Exception
     */
    public function apiGetPoByProjectId($projectId)
    {
        try {
            $count = 0;
            do {
                $token = $this->getToken();
                $header = [
                    "Authorization: Bearer {$token}",
                    "Content-Type: application/x-www-form-urlencoded",
                ];

                $url = config('sales.api_base_url') . self::URI_GET_PO;
                $data = [
                    'project_id' => $projectId,
                ];
                $response = CurlHelper::httpPost($url, $data, $header);
                $response = json_decode($response, true);
                if (!isset($response['data'])) {
                    // Nếu không có data trả về thì get lại token
                    // Remove token cũ
                    Storage::put('sale_token.json', '');
                    \Log::info('Không có data');
                    \Log::info(print_r($response, true));
                }
                $count++;

            } while (!isset($response['data']) && $count < 3);

            return $response;

        } catch (\Exception $e) {
            \Log::error($e);
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $request
     * @return string
     * @throws \Throwable
     */
    public function apiGetLineItem($request)
    {
        try {
            if (!empty(session('timesheet'))) {
                $response = $this->buildDataFromSession(session('timesheet'));
            } else {
                $response = $this->getDataItem($request);
            }

            $teams = Team::getAllTeam();
            $teams = $teams->pluck('name', 'id')->toArray();
            $html = '';

            foreach ($response['data'] as $data) {
                $start = ($data['working_from'] >= $request['start_date']) ? $data['working_from'] : $request['start_date'];
                $end = ($data['working_to'] >= $request['end_date']) ? $request['end_date'] : $data['working_to'];
                $html .= $this->buildHtmlLineItem($data, $start, $end, $teams, array_get($response, 'checkin_standard'), array_get($response, 'checkout_standard'));
            }
            return $html;
        } catch (\Exception $e) {
            \Log::error($e);
            throw $e;
        }
    }

    /**
     * @param $request
     * @return array|mixed
     * @throws \Exception
     */
    private function getDataItem($request)
    {
        $timesheet = Timesheet::with('items.details')
            ->where('po_id', $request['po_id'])
            ->where('project_id', $request['project_id'])
            ->where('start_date', $request['start_date'])
            ->where('end_date', $request['end_date'])
            ->first();

        if (!empty($timesheet) && isset($request['timesheet_id'])) {
            //Kiểm tra xem đã tồn tại timesheet tương ứng với project và khoảng thời gian này chưa
            if ($request['timesheet_id'] != $timesheet->id) {
                throw new \Exception(trans('project::timesheet.timesheet_exists', ['link' => route('project::timesheets.edit', $timesheet->id)]));
            }

            $timesheet = $timesheet->toArray();
            foreach ($timesheet['items'] as $item) {
                $data[] = $item;
            }
        } else {
            // Get ra những timesheet item của Project khác để loại bỏ đi
            $timesheetExists = Timesheet::with('items.details')
                ->where('po_id', $request['po_id'])
                ->where('project_id', '!=', $request['project_id'])
                ->where('start_date', $request['start_date'])
                ->where('end_date', $request['end_date'])
                ->get();

            $poDetails = $this->getPOdetails($request);
            $data = $poDetails['data'];

            if (!empty($timesheetExists)) {
                //Nếu có tồn tại item của project khác thì loại bỏ
                $existLineItem = [];
                foreach ($timesheetExists as $ts) {
                    $existLineItem = array_merge($existLineItem, $ts->items->pluck('line_item_id')->toArray());
                }

                $data = array_where($data, function ($key, $item) use ($existLineItem) {
                    return !in_array($item['id'], $existLineItem);
                });
            }

            if (!empty($timesheet)) {
                //Merge các item của project hiện tại vào
                $curentItem = $timesheet->items->pluck('line_item_id')->toArray();
                $data = array_where($data, function ($key, $item) use ($curentItem) {
                    return !in_array($item['id'], $curentItem);
                });

                $timesheet = $timesheet->toArray();
                $data = array_merge($timesheet['items'], $data);
            }
        }

        $poDetails['data'] = $data;

        return $poDetails;
    }

    /**
     * @param $request
     * @return mixed
     * @throws \Exception
     */
    public function getPOdetails($request)
    {
        $count = 0;
        do {
            $token = $this->getToken();
            $header = [
                "Authorization: Bearer {$token}",
                "Content-Type: application/x-www-form-urlencoded",
            ];

            $url = config('sales.api_base_url') . self::URI_GET_LINE_ITEM;
            $param = [
                'purchase_order_id' => $request['po_id'],
                'start_date' => $request['start_date'],
                'end_date' => $request['end_date'],
            ];

            $response = CurlHelper::httpPost($url, $param, $header);
            $response = json_decode($response, true);

            if (!isset($response['data'])) {
                // Nếu không có data trả về thì get lại token
                // Remove token cũ
                Storage::put('sale_token.json', '');
                \Log::info('Không có data');
                \Log::info(print_r($response, true));
            }

            $count++;

            $data['data'] = $response['data'];
        } while (!isset($response['data']) && $count < 3);

        $data['checkin_standard'] = array_get($request, 'checkin_standard');
        $data['checkout_standard'] = array_get($request, 'checkout_standard');

        return $data;
    }

    /**
     * Get list Project
     *
     * @return mixed
     */
    public function getListProject()
    {
        $projectModel = new Project();
        $permission = Permission::getInstance();
        //Get current user
        $user = $permission->getEmployee();

        $projects = [];
        // Check user có quyền cty => Get toàn bộ danh sách Project
        if ($permission->isScopeCompany()) {
            $projects = $projectModel->getAllProject();
        } elseif ($permission->isScopeTeam()) {
            // Check User có quyền Team => get danh sách Project của team
            $projects = $projectModel->getProjectByTeam();
        } elseif ($permission->isScopeSelf()) {
            // Check user có quyền cá nhân => Get danh sách Project mà user đang là PM
            $projects = $projectModel->getProjectByEmployee($user->id);
        }

        return $projects;
    }

    /**
     * Building HTML item
     *
     * @param $data
     * @param $startDate
     * @param $endDate
     * @param $teams
     * @param null $checkin
     * @param null $checkout
     * @return string
     * @throws \Throwable
     */
    public function buildHtmlLineItem($data, $startDate, $endDate, $teams, $checkin = null, $checkout = null)
    {
        $range = CarbonPeriod::create($startDate, $endDate);
        $rangeDate = [];
        foreach ($range as $date) {
            $rangeDate[$date->format('Y-m-d')] = $date->format('m/d');
        }

        $weekends = $this->getWeekend($startDate, $endDate);

        if (!empty($data['details'])) {
            $template = 'project::timesheets.line-item-edit-tpl';
        } else {
            $template = 'project::timesheets.line-item-tpl';
        }

        return view($template, compact('rangeDate', 'data', 'startDate', 'endDate', 'weekends', 'teams', 'checkin', 'checkout'))->render();
    }

    public function getWeekend($startDate, $endDate)
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $weekend = [];
        foreach ($period as $date) {
            if ($date->isWeekend()) {
                $weekend[] = $date->format('Y-m-d');
            }
        }

        return $weekend;
    }

    /**
     * Store timesheet
     *
     * @param $data
     * @throws \Exception
     */
    public function storeTimesheet($data)
    {
        \DB::beginTransaction();
        try {
            //Get current user
            $permission = Permission::getInstance();
            $user = $permission->getEmployee();
            $dataTimesheet = [
                'title' => trans('project::timesheet.timesheet_title',
                    [
                        'project' => $data['project_name'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]),
                'project_id' => $data['project_id'],
                'po_id' => $data['po_id'],
                'po_title' => $data['po_title'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'creator_id' => $user->id,
            ];

            $timesheet = Timesheet::create($dataTimesheet);

            if (!empty($timesheet)) {
                //create Timesheet items & detail
                $this->createTimesheetItem($data['line_item'], $timesheet->id);
            }

            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('Create timesshet errors');
            \Log::error($e);
            \DB::rollBack();
            throw new \Exception(trans('project::timesheet.create_timesheet_error'));
        }
    }

    /**
     * @param $timesheet
     * @param $data
     * @throws \Exception
     */
    public function updateTimesheet($timesheet, $data)
    {
        \DB::beginTransaction();
        try {
            //Get current user
            $permission = Permission::getInstance();
            $user = $permission->getEmployee();

            $isNewPeriod = false;
            if ($data['start_date'] != $timesheet->start_date && $data['end_date'] != $timesheet->end_date) {
                $isNewPeriod = true;
            }
            $dataTimesheet = [
                'project_id' => $data['project_id'],
                'po_id' => $data['po_id'],
                'po_title' => $data['po_title'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'creator_id' => $user->id,
            ];

            if (!empty($data['project_name'])) {
                $dataTimesheet['title'] = trans('project::timesheet.timesheet_title',
                    [
                        'project' => $data['project_name'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'],
                    ]);
            }

            $timesheet->update($dataTimesheet);

            //Nếu update vào khoảng thời gian mới, xóa hết các Item cũ để insert item mới
            if ($isNewPeriod) {
                $lineItem = TimesheetItem::where('timesheet_id', $timesheet->id)->pluck('id')->toArray();

                //delete Item detail
                TimesheetItemDetail::whereIn('timesheet_item_id', $lineItem)->delete();

                //Delete Item
                TimesheetItem::where('timesheet_id', $timesheet->id)->delete();
                //create Timesheet items

                $this->createTimesheetItem($data['line_item'], $timesheet->id);
            } else {
                $itemIdDeleted = array_filter(explode(',', $data['item_id_deleted']));

                if (!empty($itemIdDeleted)) {
                    // Xóa các item đã bị delete
                    TimesheetItem::whereIn('id', $itemIdDeleted)
                        ->where('timesheet_id', $timesheet->id)
                        ->delete();
                }

                if (!empty($data['line_item'])) {
                    foreach ($data['line_item'] as $lineId => $item) {
                        if (!empty($item['id'])) {
                            $details = $item['details'];
                            unset($item['details']);
                            TimesheetItem::find($item['id'])->update($item);

                            //update item details
                            foreach ($details as $date => $detail) {
                                if (!isset($detail['holiday'])) {
                                    $detail['holiday'] = 0;
                                }
                                if ($detail['working_hour'] === '') {
                                    $detail['working_hour'] = null;
                                }
                                if ($detail['ot_hour'] === '') {
                                    $detail['ot_hour'] = null;
                                }
                                if ($detail['overnight'] === '') {
                                    $detail['overnight'] = null;
                                }

                                TimesheetItemDetail::where('timesheet_item_id', $item['id'])
                                    ->where('date', $date)
                                    ->update($detail);
                            }
                        } else {
                            $this->createTimesheetItem([$lineId => $item], $timesheet->id);
                        }
                    }
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('Update timesshet errors');
            \Log::error($e);
            \DB::rollBack();
            throw new \Exception(trans('project::timesheet.update_timesheet_error'));
        }
    }

    /**
     * Delete timesheet
     *
     * @param Timesheet $timesheet
     * @return bool|void|null
     * @throws \Exception
     */
    public function delete(Timesheet $timesheet)
    {
        if ($timesheet->status == Timesheet::STATUS_PUBLISHED) {
            throw new \Exception(trans('project::timesheet.dont_delete_publish'));
        }

        try {
            //Trước khi xóa nó sẽ tìm các relation liên quan để xóa trước
            // Do đã add cascade vào khóa ngoại nên MYSQL sẽ tự động xóa các relation liên quan
            $timesheet->delete($timesheet);

        } catch (\Exception $e) {
            \Log::error('Delete timesshet error');
            \Log::error($e);
            throw new \Exception(trans('project::timesheet.delete_timesheet_error'));
        }
    }

    /**
     * Create timesheet Item & detail
     *
     * @param array $data
     * @param $timesheetId
     */
    private function createTimesheetItem($data, $timesheetId)
    {
        foreach ($data as $lineId => $item) {
            $details = $item['details'];
            unset($item['details']);
            $dataItem = array_merge([
                'timesheet_id' => $timesheetId,
                'line_item_id' => $lineId,
            ], $item);

            $timeSheetItem = TimesheetItem::create($dataItem);

            //update item details
            $dataDetail = [];
            foreach ($details as $date => $detail) {
                if ($detail['working_hour'] === '') {
                    $detail['working_hour'] = null;
                }
                if ($detail['ot_hour'] === '') {
                    $detail['ot_hour'] = null;
                }
                if ($detail['overnight'] === '') {
                    $detail['overnight'] = null;
                }

                $dataDetail[] = array_merge([
                    'timesheet_item_id' => $timeSheetItem->id,
                    'date' => $date,
                    'holiday' => '0'
                ], $detail);
            }

            TimesheetItemDetail::insert($dataDetail);
        }
    }

    /**
     * Đồng bộ timesheet
     *
     * @param $request
     * @return array
     * @throws \Throwable
     */
    public function syncTimesheet($request)
    {
        try {
            $empId = $request['employee_id'];
            $startDate = $request['start_date'];
            $endDate = $request['end_date'];
            $lineItemId = $request['line_item_id'];
            $checkin = $request['checkin'];
            $checkout = $request['checkout'];
            $timeKeeping = new ViewTimeKeeping();
            $timeesheet = $timeKeeping->getTimeWorkByEmpId($startDate, $endDate, [$empId], $checkin, $checkout);

            if (empty($timeesheet)) {
                throw new \Exception('Không tìm thấy timesheet phù hợp');
            }

            foreach ($timeesheet[$empId] as $date => $item) {
                $data[$date] = [
                    'working_hour' => $item['timeWork'],
                    'ot_hour' => ($item['timeOT'] != 0) ? $item['timeOT'] : '',
                    'overnight' => '',
                    'break_time' => $item['break_time'],
                    'checkin' => $item['timeIn'],
                    'checkout' => $item['timeOut'],
                    'note' => '',
                    'holiday' => $item['holiday'],
                    'ct' => $item['ct'],
                    'p' => $item['p'],
                ];
            }

            //Get team is working
            $team = EmployeeTeamHistory::select('team_id')
                ->where('employee_id', $empId)
                ->whereNull('deleted_at')
                ->where('is_working', EmployeeTeamHistory::IS_WORKING)
                ->first();

            return [
                'team_id' => $team->team_id,
                'html' => $this->buildTableTimesheet($lineItemId, $data, $startDate, $endDate, $checkin, $checkout)
            ];
        } catch (\Exception $e) {
            \Log::error($e);
            throw $e;
        }
    }

    /**
     * Build table timesheet
     *
     * @param $lineId
     * @param $data
     * @param $startDate
     * @param $endDate
     * @param string $checkin
     * @param string $checkout
     * @return string
     * @throws \Throwable
     */
    private function buildTableTimesheet($lineId, $data, $startDate, $endDate, $checkin = '', $checkout = '')
    {
        $range = CarbonPeriod::create($startDate, $endDate);
        $rangeDate = [];
        foreach ($range as $date) {
            $rangeDate[$date->format('Y-m-d')] = $date->format('m/d');
        }

        $weekends = $this->getWeekend($startDate, $endDate);

        return view('project::timesheets.table-timesheet-tpl', compact('lineId', 'rangeDate', 'data', 'startDate', 'endDate', 'weekends', 'checkin', 'checkout'))->render();
    }

    /**
     * Check quyền của user đối với project
     *
     * @param $projectId
     * @param array $projectList
     * @return bool
     */
    public function checkAccess($projectId, $projectList = [])
    {
        if (empty($projectList)) {
            $projectList = $this->getListProject();
        }

        if (!key_exists($projectId, $projectList)) {
            throw new UnauthorizedException(trans('project::timesheet.permission_denied'));
        }

        return true;
    }

    /**
     * Check is time
     *
     * @param $time (H:m)
     * @return bool
     */
    public function checkIsTime($time)
    {
        return (bool)preg_match('/^\s*(2[0-3]|[01]?[0-9]):([0-5]?[0-9])\s*$/', $time);
    }

    /**
     * Convert time (H:m) to int
     *
     * @param $time
     * @return float|int
     */
    public function convertTimeToInt($time)
    {
        $time = explode(':', $time);
        return $time[0] * 60 + $time[1];
    }

    /**
     * Calculator diff time
     *
     * @param $startTime
     * @param $endTime
     * @param bool $isFloat
     * @return false|float|int|string
     */
    public function calculateWorkingHour($startTime, $endTime)
    {
        if (!$this->checkIsTime($startTime) && !$this->checkIsTime($endTime)) {
            return '';
        }

        $startTime = $this->convertTimeToInt($startTime);
        $endTime = $this->convertTimeToInt($endTime);
        $breakTime = max(($endTime - $startTime - 8 * 60), 0);

        return ($endTime - $startTime - $breakTime) / 60;
    }

    /**
     * @param $startTime
     * @param $endTime
     * @return false|string
     */
    public function calculateBreakTime($startTime, $endTime)
    {
        if (!$this->checkIsTime($startTime) && !$this->checkIsTime($endTime)) {
            return '';
        }

        $startTime = $this->convertTimeToInt($startTime);
        $endTime = $this->convertTimeToInt($endTime);
        $timeWorking = 8 * 60;

        $seconds = max(($endTime - $startTime - $timeWorking) * 60, 0);

        if ($seconds <= 0) {
            return '0:00';
        }

        return gmdate("H:i", $seconds);
    }

    /**
     * @param $data
     * @return array
     */
    private function buildDataFromSession($data)
    {
        try {
            $arr = [];
            foreach (array_get($data, 'line_item') as $lineId => $item) {
                $details = [];
                foreach ($item['details'] as $date => $value) {
                    $details[] = [
                        "timesheet_item_id" => "116",
                        "date" => $date,
                        "checkin" => $value['checkin'],
                        "checkout" => $value['checkout'],
                        "working_hour" => $value['working_hour'],
                        "break_time" => $value['break_time'],
                        "ot_hour" => $value['ot_hour'],
                        "overnight" => $value['overnight'],
                        "holiday" => array_get($value, 'overnight'),
                        "note" => array_get($value, 'note'),
                    ];
                }

                $arr[] = [
                    "id" => array_get($item, 'id'),
                    'line_item_id' => $lineId,
                    'division_id' => array_get($item, 'division_id'),
                    'employee_id' => array_get($item, 'employee_id'),
                    'day_of_leave' => array_get($item, 'day_of_leave'),
                    "name" => $item['name'],
                    "roles" => $item['roles'],
                    "level" => $item['level'],
                    "working_from" => $item['working_from'],
                    "working_to" => $item['working_to'],
                    "min_hour" => $item['min_hour'],
                    "max_hour" => $item['max_hour'],
                    'details' => $details
                ];
            }

            return ['data' => $arr];
        } catch (\Exception $e) {
            \Log::error($e->getTraceAsString());
            return ['data' => []];
        }
    }
}