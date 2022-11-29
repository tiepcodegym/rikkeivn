<?php

namespace Rikkei\Assets\Model;

use DB;
use Dompdf\Exception;
use Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\View\View;
use Carbon\Carbon;

class AssetHistory extends CoreModel
{
    const STATE_CREATE  = 0;
    const STATE_ALLOCATION = 1;
    const STATE_BROKEN_NOTIFICATION = 2;
    const STATE_BROKEN = 3;
    const STATE_SUGGEST_REPAIR_MAINTENANCE = 4;
    const STATE_REPAIRED_MAINTAINED = 5;
    const STATE_LOST_NOTIFICATION = 6;
    const STATE_LOST = 7;
    const STATE_SUGGEST_LIQUIDATE = 8;
    const STATE_LIQUIDATE = 9;
    const STATE_CANCELLED = 10;
    const STATE_RETRIEVAL = 11;
    const STATE_UPDATE = 12;
    const STATE_UNAPPROVE = 13;
    const STATE_HAD_REPAIRED_MAINTAINED = 14;
    const STATE_RETURN_CUSTOMER = 15;
    const STATE_UPDATE_SERIAL = 16;

    protected $table = 'manage_asset_histories';

    protected $fillable = [
        'asset_id', 'employee_id', 'note', 'state', 'change_date', 'change_reason', 'created_by'
    ];



    /**
     * Get asset histories by asset id
     * @param  [int] $assetId
     * @return [array]
     */
    public static function getHistoriesByAssetId($assetId)
    {
        $tblAssetHistory = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        $tblEmployee = Employee::getTableName();

        return self::select("{$tblEmployee}.name as creator_name", "{$tblEmployee}.email as creator_email", "{$tblAssetHistory}.note", "{$tblAssetHistory}.change_date", "{$tblAssetHistory}.change_reason", "{$tblAssetHistory}.created_at")
            ->join("{$tblAssetItem}", "{$tblAssetItem}.id", "=", "{$tblAssetHistory}.asset_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblAssetHistory}.created_by")
            ->where("{$tblAssetHistory}.asset_id", $assetId)
            ->orderBy("{$tblAssetHistory}.created_at", 'DESC')
            ->get();
    }

    public static function getProcessUsingAsset($assetId, $states = [self::STATE_CREATE, self::STATE_CANCELLED, self::STATE_UPDATE, self::STATE_UNAPPROVE])
    {
        $tblAssetHistory = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        $tblEmployee = Employee::getTableName();
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $tblPosition = 'tbl_position';

        $results = self::select("{$tblEmployee}.employee_code", "{$tblEmployee}.name as employee_name", "{$tblAssetHistory}.state", "{$tblAssetHistory}.note", "{$tblAssetHistory}.change_date", "{$tblAssetHistory}.change_reason", "{$tblAssetHistory}.created_at", "{$tblPosition}.role_name")
            ->join("{$tblAssetItem}", "{$tblAssetItem}.id", "=", "{$tblAssetHistory}.asset_id")
            ->leftJoin("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblAssetHistory}.employee_id")
            ->leftJoin(DB::raw("(SELECT {$teamMemberTable}.employee_id as employee_id, GROUP_CONCAT(DISTINCT CONCAT({$roleTable}.role, ' - ', {$teamTable}.name) ORDER BY {$roleTable}.role DESC SEPARATOR '; ') as role_name FROM {$teamMemberTable} JOIN {$tblEmployee} ON {$teamMemberTable}.employee_id = {$tblEmployee}.id JOIN {$teamTable} ON {$teamMemberTable}.team_id = {$teamTable}.id JOIN {$roleTable} ON {$teamMemberTable}.role_id = {$roleTable}.id GROUP BY {$tblEmployee}.id) AS {$tblPosition}"), "{$tblPosition}.employee_id", '=', "{$tblAssetHistory}.employee_id")
            ->where("{$tblAssetHistory}.asset_id", $assetId);
        if ($states) {
            $results = $results->whereNotIn("{$tblAssetHistory}.state", $states);
        }

        return $results->get();
    }

    /**
     * Get asset histories to report
     * @param  [int] $assetId
     * @return [array]
     */
    public static function reportAssetByUseProcess($assetIds)
    {
        $tblAssetHistory = self::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblRole = Role::getTableName();
        $tblTeam = Team::getTableName();
        DB::statement("SET @duplicateEmpl:=-1");
        $data = self::select(
            "{$tblEmployee}.id as employee_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            "{$tblAssetHistory}.state as state_history",
            "{$tblAssetHistory}.note",
            "{$tblAssetHistory}.change_date",
            "{$tblAssetHistory}.change_reason",
            "{$tblAssetHistory}.created_at",
            DB::raw("CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) as role_name"),
            "{$tblAssetItem}.id as asset_id",
            "{$tblAssetItem}.code as asset_code",
            "{$tblAssetItem}.name as asset_name",
            "{$tblAssetItem}.id as asset_id"
            )
            ->join("{$tblAssetItem}", "{$tblAssetItem}.id", "=", "{$tblAssetHistory}.asset_id")
            ->leftJoin("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblAssetHistory}.employee_id")
            ->leftJoin(
                DB::raw("(select employee_id, team_id, start_at, role_id, is_duplicate, end_at
                FROM (SELECT employee_id, team_id, start_at, role_id, end_at, IF(@duplicateEmpl = employee_id, 1, 2) as is_duplicate, @duplicateEmpl := employee_id FROM `employee_team_history` as t_eth
                WHERE end_at IS NULL
                ORDER BY employee_id asc, start_at DESC) as ttt) as tmp_teh"), function ($join) {
                $join->on('tmp_teh.employee_id', '=', 'employees.id')
                    ->where('is_duplicate', '=', 2);
                }
            )
            ->leftJoin($tblTeam, "$tblTeam.id", '=', 'tmp_teh.team_id')
            ->leftjoin($tblRole, "$tblRole.id", '=', 'tmp_teh.role_id')
            ->whereNotIn("{$tblAssetHistory}.state", [self::STATE_UPDATE, self::STATE_UNAPPROVE, self::STATE_RETRIEVAL, self::STATE_LOST_NOTIFICATION, self::STATE_BROKEN_NOTIFICATION])
            ->whereIn("{$tblAssetItem}.id", $assetIds)
            ->whereNotNull("{$tblAssetHistory}.employee_id")
            ->get();
        $labelStates = AssetHistory::labelStates();
        $output = [];
        if ($data[0]->employee_id === null) {
            unset($data[0]);
        }
        foreach ($data as $key => $item) {
            if (!isset($output[$item->asset_id])) {
                $output[$item->asset_id] = [
                    'asset_id' => $item->asset_id,
                    'asset_code' => $item->asset_code,
                    'asset_name' => $item->asset_name,
                    'history' => [],
                ];
            }
            if ($output[$item->asset_id]['asset_id'] == $item->asset_id) {
                $temporary = [
                    'employee_id' => $item->employee_id,
                    'employee_code' => $item->employee_code,
                    'employee_name' => $item->employee_name,
                    'state_history' => $labelStates[$item->state_history],
                    'role_name' => $item->role_name,
                    'note' => $item->note,
                    'change_date' => $item->change_date,
                    'change_reason' => $item->change_reason,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                ];
                /*if ($item->state_history == self::STATE_ALLOCATION) {
                    for ($i = $key; $i > 0; --$i) {
                        if ($data[$i]->state_history == self::STATE_CREATE) {
                            $array = $data[$i];
                            break;
                        }
                    }
                    if (isset($array)) {
                        $temporary['state_history'] = $labelStates[$item->state_history]."\n".$labelStates[$array->state_history];
                        $temporary['change_date'] = $item->change_date."\n".$array->change_date;
                    }
                }*/
                $output[$item->asset_id]['history'][] =  $temporary;
                unset($temporary);
            }
        }
        return array_values($output);
    }

    /**
     * Insert asset history
     * @param  [model] $assetItem
     * @param  [int] $createdBy
     * @param  [string] $note
     */
    public static function insertHistory($assetItem, $createdBy, $note = null, $state = null)
    {
        $dataHistory = [];
        $dataHistory['asset_id'] = $assetItem->id;
        $dataHistory['employee_id'] = $assetItem->employee_id;
        $dataHistory['state'] = $assetItem->state;
        $dataHistory['change_date'] = $assetItem->change_date;
        $dataHistory['change_reason'] = $assetItem->reason;
        $dataHistory['created_by'] = $createdBy;
        $dataHistory['created_at'] = date('Y-m-d H:i:s');
        $dataHistory['updated_at'] = date('Y-m-d H:i:s');
        if ($note) {
            $dataHistory['note'] = $note;
        }
        if ($state) {
            $dataHistory['state'] = $state;
        }
        self::insert($dataHistory);
    }

    /**
     * Get label state
     * @return array
     */
    public static function labelStates()
    {
        return [
            self::STATE_CREATE => Lang::get('asset::view.Retrievalled'),
            self::STATE_ALLOCATION => Lang::get('asset::view.Allocated'),
            self::STATE_BROKEN_NOTIFICATION => Lang::get('asset::view.Broken notification'),
            self::STATE_BROKEN => Lang::get('asset::view.Broken'),
            self::STATE_SUGGEST_REPAIR_MAINTENANCE => Lang::get('asset::view.Suggest repair, maintenance'),
            self::STATE_REPAIRED_MAINTAINED => Lang::get('asset::view.Repaired, maintained'),
            self::STATE_LOST_NOTIFICATION => Lang::get('asset::view.Lost notification'),
            self::STATE_LOST => Lang::get('asset::view.Lost'),
            self::STATE_SUGGEST_LIQUIDATE => Lang::get('asset::view.Suggest liquidate'),
            self::STATE_LIQUIDATE => Lang::get('asset::view.Liquidated'),
            self::STATE_CANCELLED => Lang::get('asset::view.Cancelled'),
            self::STATE_RETRIEVAL => Lang::get('asset::view.Handing over'),
            self::STATE_UPDATE => Lang::get('asset::view.Update'),
            self::STATE_UNAPPROVE => Lang::get('asset::view.Unapprove'),
            self::STATE_HAD_REPAIRED_MAINTAINED => Lang::get('asset::view.Had repaired, maintained'),
        ];
    }

    public static function saveMultiHistory($listAsset, $createdBy, $note, $state, $assetProfile = false)
    {
        $dataHistory = [];
        foreach ($listAsset as $key => $assetItem) {
            if ($assetItem->employee_id && ($employeeAllocated = AssetItem::getEmployeeAllocated($assetItem->employee_id))) {
                $historyNote = Lang::get($note, [
                    'name' => $employeeAllocated->name . ' (' .View::getNickName($employeeAllocated->email) . ')'
                ]);
            } else {
                $historyNote = Lang::get($note);
            }

            $dataHistory[$key] = [
                'asset_id' =>  $assetItem->id,
                'state' => $state,
                'employee_id' => $assetItem->employee_id,
                'change_date' => $assetItem->change_date,
                'change_reason' => $assetItem->reason,
                'note' => $historyNote,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($assetProfile) {
                $dataHistory[$key]['change_reason'] = null;
            }
        }
        self::insert($dataHistory);
    }

    /*
    save data history
     */
    public static function saveDataHistory($listAsset, $createdBy, $note, $state, $changeDate, $changeReason)
    {
        $dataHistory = [];
        foreach ($listAsset as $key => $assetItem) {
            if ($assetItem->employee_id && ($employeeAllocated = AssetItem::getEmployeeAllocated($assetItem->employee_id))) {
                $historyNote = Lang::get($note, [
                    'name' => $employeeAllocated->name . ' (' .View::getNickName($employeeAllocated->email) . ')'
                ]);
            } else {
                $historyNote = Lang::get($note);
            }

            $dataHistory[$key] = [
                'asset_id' =>  $assetItem->id,
                'state' => $state,
                'employee_id' => $assetItem->employee_id,
                'change_date' => Carbon::createFromFormat('d-m-Y', $changeDate)->format('Y-m-d'),
                'change_reason' => $changeReason,
                'note' => $historyNote,
                'created_by' => $createdBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }
        self::insert($dataHistory);
    }

    /**
     * Update Serial History
     *
     * @param $dataCodeAssetUpdate
     * @param $userCurrent
     */
    public function updateHistorySerial($dataCodeAssetUpdate, $userCurrent)
    {
        $arrayCode = array_keys($dataCodeAssetUpdate);
        $assetItem = AssetItem::select('id', 'code', 'serial')
            ->whereIn('code', $arrayCode)
            ->whereNull('deleted_at')
            ->get()
            ->toArray();

        $dataHistory = [];
        foreach ($assetItem as $items => $item) {
            if (isset($dataCodeAssetUpdate[$item['code']])) {
                if ($item['serial'] !== $dataCodeAssetUpdate[$item['code']]) {
                    $dataHistory[$item['code']] = [
                        'asset_id' => $item['id'],
                        'employee_id' => null,
                        'note' => (($item['serial'] ? $item['serial'] : '" "') . ' => ' . $dataCodeAssetUpdate[$item['code']]),
                        'state' => self::STATE_UPDATE_SERIAL,
                        'change_date' => Carbon::now()->format('Y-m-d'),
                        'created_by' => $userCurrent->id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        self::insert($dataHistory);
    }
}
