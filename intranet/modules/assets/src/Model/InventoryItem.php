<?php

namespace Rikkei\Assets\Model;

use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\Form as CoreForm;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Config;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskHistory;
use Rikkei\Project\Model\TaskComment;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Assets\Model\InventoryItemHistory;
use Carbon\Carbon;

class InventoryItem extends CoreModel
{
    protected $table = 'inventory_asset_items';
    protected $fillable = ['inventory_id', 'employee_id', 'task_id', 'status', 'note', 'is_notify', 'num_alert'];

    /*
     * insert items
     */
    public static function insertItem($inventory, $teamIds = [], $mailData = [])
    {
        //insert new item;
        $tblEmp = Employee::getTableName();
        $timeEnd = Carbon::parse($inventory->time);
        $employees = Employee::select($tblEmp . '.id', $tblEmp . '.name', $tblEmp . '.email')
                ->join(TeamMember::getTableName() . ' as tmb', $tblEmp . '.id', '=', 'tmb.employee_id')
                ->whereIn('tmb.team_id', $teamIds)
                ->where(function ($query) use ($timeEnd, $tblEmp) {
                    $query->whereNull($tblEmp . '.leave_date')
                            ->orWhere($tblEmp . '.leave_date', '>', $timeEnd->toDateString());
                })
                ->where($tblEmp . '.join_date', '<=', $timeEnd->toDateString())
                ->groupBy($tblEmp . '.id')
                ->get();
        if ($employees->isEmpty()) {
            return;
        }
        $inventoryAssetEmployee = self::where("inventory_id", $inventory->id)->get()->pluck("employee_id")->toArray();
        $isSendMail = isset($mailData['is_send']) && $mailData['is_send'];
        $toNotifyIds = [];
        $dataEmailQueue = [];
        $dataInventoryItem = [];
        $mailSubject = isset($mailData['subject']) ? $mailData['subject'] : $inventory->name;
        $detailLink = route('asset::profile.view-personal-asset');
        $timeNow = Carbon::now()->toDateTimeString();
        //insert task assignee

        $dataTaskAssignee = [];
        $duedate = Carbon::parse($inventory->time)->toDateTimeString();
        $insertEmpIds = [];
        foreach ($employees as $emp) {
            $insertEmpIds[] = $emp->id;
            $inventoryItem = self::where('inventory_id', $inventory->id)
                    ->where('employee_id', $emp->id)
                    ->first();
            $dataItem = [
                'updated_at' => $timeNow,
                'is_notify' => $isSendMail
            ];
            $mailSent = false;
            if ($inventoryItem) {
                if ($inventoryItem->is_notify) {
                    $mailSent = true;
                }
                $inventoryItem->update($dataItem);
                $assigneTaskId = $inventoryItem->task_id;
            } else {
                $taskItem = Task::create([
                    'type' => Task::TYPE_GENERAL,
                    'title' => $inventory->name,
                    'priority' => Task::PRIORITY_NORMAL,
                    'duedate' => $duedate,
                    'status' => Task::STATUS_NEW
                ]);

                $dataItem['task_id'] = $taskItem->id;
                $dataItem['inventory_id'] = $inventory->id;
                $dataItem['employee_id'] = $emp->id;
                $dataItem['created_at'] = $timeNow;
                $dataInventoryItem[] = $dataItem;
                $assigneTaskId = $taskItem->id;
            }
            //check task assigne
            if (!TaskAssign::where('task_id', $assigneTaskId)->where('employee_id', $emp->id)->first()) {
                $dataTaskAssignee[] = [
                    'task_id' => $assigneTaskId,
                    'employee_id' => $emp->id,
                    'role' => TaskAssign::ROLE_OWNER
                ];
            }
            if (!$isSendMail || $mailSent) {
                continue;
            }
            $toNotifyIds[] = $emp->id;
            //sent mail
            $dataMailItem = [
                'dearName' => $emp->name,
                'detailLink' => $detailLink,
                'content' => preg_replace(['/\{\{\sname\s\}\}/'], [$emp->name], $mailData['content'])
            ];
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($emp->email, $emp->name)
                    ->setSubject($mailSubject)
                    ->setTemplate('asset::inventory.mail.notify-employee', $dataMailItem);
            $dataEmailQueue[] = $emailQueue->getValue();
            $toNotifyIds[] = $emp->id;
        }
        if ($dataInventoryItem) {
            InventoryItem::insert($dataInventoryItem);
        }
        TaskAssign::insert($dataTaskAssignee);
        //delete invalid item
        $oldTaskIds = $inventory->items()->whereNotIn('employee_id', $insertEmpIds)->lists('task_id')->toArray();
        if ($oldTaskIds) {
            TaskAssign::whereIn('task_id', $oldTaskIds)->delete();
            TaskHistory::whereIn('task_id', $oldTaskIds)->delete();
            TaskComment::whereIn('task_id', $oldTaskIds)->delete();
            Task::whereIn('id', $oldTaskIds)->forceDelete();
        }
        // insert information asset to table inventory_asset_item_histories
        $inventoryEmployeeDiff = array_diff($insertEmpIds, $inventoryAssetEmployee);
        $inventoryItemTbl = self::getTableName();
        $assetIteamTbl = AssetItem::getTableName();
        $inventoryAssetHisTbl = InventoryItemHistory::getTableName();
        $assets = AssetItem::select(
            "{$inventoryItemTbl}.id as inventory_asset_item_id",
            "{$assetIteamTbl}.id as asset_id",
            "{$assetIteamTbl}.code as asset_code",
            "{$assetIteamTbl}.name as asset_name",
            "{$assetIteamTbl}.allocation_confirm as status",
            "{$assetIteamTbl}.employee_note as note",
            "{$inventoryItemTbl}.created_at",
            "{$inventoryItemTbl}.updated_at"
            )
            ->leftJoin("{$inventoryItemTbl}", "{$inventoryItemTbl}.employee_id", '=', "{$assetIteamTbl}.employee_id")
            ->where("{$inventoryItemTbl}.inventory_id", '=', $inventory->id)
            ->whereIn("{$assetIteamTbl}.employee_id", $inventoryEmployeeDiff)
            ->orderBy("inventory_asset_item_id")
            ->get()
            ->toArray();
        if ($assets) {
            InventoryItemHistory::insert($assets);
        }

        $inventory->items()->whereNotIn('employee_id', $insertEmpIds)->delete();
        //check send mail
        if ($isSendMail) {
            EmailQueue::insert($dataEmailQueue);
            \RkNotify::put($toNotifyIds, $mailSubject, $detailLink, [
                'icon' => 'asset.png',
                'category_id' => RkNotify::CATEGORY_PERIODIC
            ]);
        }
    }

    public function task()
    {
        return $this->belongsTo('\Rikkei\Project\Model\Task', 'task_id', 'id');
    }

    /*
     * list items
     */
    public static function getGridData($inventoryId, $urlFilter = null, $getAll = false)
    {
        $collection = self::from(self::getTableName() . ' as item')
                ->join(Employee::getTableName() . ' as emp', 'item.employee_id', '=', 'emp.id')
                ->leftJoin(TeamMember::getTableName() . ' as tmb', 'emp.id', '=', 'tmb.employee_id')
                ->leftJoin(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                ->whereNull('emp.deleted_at')
                ->where('item.inventory_id', $inventoryId);
        self::filterGrid($collection, [], $urlFilter);
        if ($filterTeamId = CoreForm::getFilterData('excerpt', 'team_id', $urlFilter)) {
            $collection->leftJoin(TeamMember::getTableName() . ' as tmb_filter', 'emp.id', '=', 'tmb_filter.employee_id')
                    ->where('tmb_filter.team_id', $filterTeamId);
        }
        if ($filterStatus = CoreForm::getFilterData('excerpt', 'status', $urlFilter)) {
            $statuses = [$filterStatus];
            if ($filterStatus == AssetConst::INV_RS_NOT_ENOUGH) {
                $statuses[] = AssetConst::INV_RS_EXCESS;
            }
            $collection->whereIn('item.status', $statuses);
        }
        $pager = Config::getPagerData($urlFilter);
        //sort order
        if (CoreForm::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('emp.name', 'asc');
        }
        if ($getAll) {
            $tblItemHistory = InventoryItemHistory::getTableName();
            $listConfirmLabels = AssetItem::labelAllocationConfirm();
            $collection->join("{$tblItemHistory}", "{$tblItemHistory}.inventory_asset_item_id", '=', 'item.id')
                ->join(AssetItem::getTableName() . ' as asset', "{$tblItemHistory}.asset_id", '=', 'asset.id')
                ->leftJoin(AssetCategory::getTableName() . ' as as_cat', 'asset.category_id', '=', 'as_cat.id')
                ->select(
                    'emp.employee_code',
                    'emp.name',
                    DB::raw(AssetConst::selectCase('item.status', AssetConst::listInventoryStatus()) . ' AS status'),
                    "{$tblItemHistory}.asset_code",
                    "{$tblItemHistory}.asset_name",
                    'as_cat.name as asset_type',
                    DB::raw(AssetConst::selectCase("{$tblItemHistory}.status", $listConfirmLabels) . ' AS asset_status'),
                    'item.note',
                    "{$tblItemHistory}.note as employee_note",
                    DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names')
                )
                ->groupBy("{$tblItemHistory}.id");
            return $collection->get();
        }
        $collection->select(
            'item.id',
            'emp.id as emp_id',
            'emp.name',
            'emp.email',
            'item.status',
            'item.note',
            'item.created_at',
            'item.updated_at',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names')
        )
        ->groupBy('emp.id');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * list employee notes
     */
    public function employeeNotes()
    {
        return $this->hasMany('\Rikkei\Assets\Model\AssetItem', 'employee_id', 'employee_id');
    }

    /*
     * get list status labels
     */
    public function getStatusLabel($listStatus)
    {
        if (isset($listStatus[$this->status])) {
            return $listStatus[$this->status];
        }
        return trans('asset::view.False');
    }

    /*
     * find item by employee id
     */
    public static function findByEmployeeId($inventoryId, $employeeId)
    {
        return self::where('inventory_id', $inventoryId)
                ->where('employee_id', $employeeId)
                ->first();
    }

    /**
     * close task
     */
    public function closeTask()
    {
        $taskId = $this->task_id;
        $task = Task::find($taskId);
        if ($task) {
            $task->status = Task::STATUS_CLOSED;
            $task->save();
        }
    }

    /**
     * alert do inventory
     * @param type $inventory
     * @return type
     */
    public static function alertDoInventory($inventory)
    {
        $inventoryId = $inventory->id;
        $collection = self::select('emp.id', 'emp.name', 'emp.email', 'inv_item.id as item_id', 'inv_item.num_alert')
                ->from(self::getTableName() . ' as inv_item')
                ->join(Employee::getTableName() . ' as emp', 'inv_item.employee_id', '=', 'emp.id')
                ->where('inv_item.inventory_id', $inventoryId)
                ->where('inv_item.status', AssetConst::INV_RS_NOT_YET)
                ->whereNull('emp.deleted_at')
                ->where(function ($query) {
                    $query->whereNull('leave_date')
                            ->orWhereRaw('DATE(leave_date) > CURDATE()');
                })
                ->get();
        if ($collection->isEmpty()) {
            return ['status' => 0, 'message' => trans('asset::message.None do not inventory')];
        }
        $dataMail = [
            'inventoryName' => $inventory->name,
            'detailLink' => route('asset::profile.view-personal-asset')
        ];
        $subject = $inventory->name;

        DB::beginTransaction();
        try {
            foreach ($collection->chunk(500) as $dataChunk) {
                $dataInsert = [];
                foreach ($dataChunk as $emp) {
                    $dataMail['dearName'] = $emp->name;
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($emp->email, $emp->name)
                            ->setSubject(trans('asset::view.Alert number time', ['num' => $emp->num_alert + 1]) . ' ' . $subject)
                            ->setTemplate('asset::inventory.mail.alert-employee', $dataMail);
                    $dataInsert[] = $emailQueue->getValue();
                }
                EmailQueue::insert($dataInsert);
            }
            self::whereIn('id', $collection->lists('item_id')->toArray())->update(['num_alert' => DB::raw('num_alert + 1')]);
            \RkNotify::put($collection->lists('id')->toArray(), '[' . trans('asset::view.Alert inventory') . '] ' . $subject, $dataMail['detailLink'], ['icon' => 'asset.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]);

            DB::commit();
            return ['status' => 1, 'message' => trans('asset::message.Sent email alert successful')];
        } catch (\Exception $ex) {
            return ['status' => 0, 'message' => trans('core::message.Error system, please try later!')];
        }
    }
}
