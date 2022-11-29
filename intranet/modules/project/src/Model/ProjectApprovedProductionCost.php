<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\User;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\Project;

class ProjectApprovedProductionCost extends CoreModel
{
    use SoftDeletes;

    protected $table = 'project_approved_production_cost';

    const UNIT_PRICE_VND = 1;
    const UNIT_PRICE_JPY = 2;

    const UNIT_PRICE_DEFAULT = 30000000;
    const UNIT_PRICE_INTERNAL_DEFAULT = 20000000;

    public static function getUnitPrices()
    {
        return [
            self::UNIT_PRICE_VND => 'VND',
            self::UNIT_PRICE_JPY => 'JPY',
        ];
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'approved_production_cost', 'month', 'year', 'team_id', 'note', 'price', 'unit_price', 'role', 'level', 'unapproved_price'];

    /**
     * insert or update project
     */
    public static function insertProjectProductionCost($input, $projectId, $isFlagCreate)
    {
        if ($isFlagCreate) {
            $result = self::insertApprovedProjectCost($input, $projectId);
            $result ? DB::commit() : DB::rollback();
        }
    }

    public static function insertApprovedProductionCost($projectId, $data, $isCOO = false)
    {
        $dataItem = [
            'project_id' => $projectId,
            'team_id' => isset($data['team_id']) ? $data['team_id'] : null,
            'approved_production_cost' => (isset($data['approved_production_cost']) && $data['approved_production_cost'] != '') ? $data['approved_production_cost'] : 0,
            // 'price' => isset($data['price']) ? $data['price'] : null,
            'unapproved_price' => isset($data['price']) ? $data['price'] : null,
            'unit_price' => isset($data['unit_price']) ? $data['unit_price'] : ProjectApprovedProductionCost::UNIT_PRICE_VND,
            'month' => $data['month'],
            'year' => $data['year'],
            'note' => isset($data['approve_cost_note']) ? $data['approve_cost_note'] : null,
            'level' => isset($data['level_id']) ? $data['level_id'] : null,
            'role' => isset($data['role_id']) ? $data['role_id'] : null,
            'id' => isset($data['id']) ? $data['id'] : null,
            'is_approve' => isset($data['is_approve']) ? $data['is_approve'] : 0,
        ];
        if ($isCOO && $dataItem['is_approve']) {
            $dataItem['price'] = isset($data['price']) ? $data['price'] : null;
            $dataItem['unapproved_price'] = null;
        }
        unset($dataItem['is_approve']);
        return $dataItem;
    }

    /**
     * get Project Approved Production Cost
     * @param id
     * @return array
     */
    public static function getProjectApprpveProductionCost($id, $isAllowUpdateApproveCostPrice)
    {
        $items = self::where('project_id', $id)->orderBy('year')->orderBy('month')->get()->toArray();

        return self::transFormerData($items, $isAllowUpdateApproveCostPrice);
    }

    public static function getTotalApproveCostByMonth($projectId)
    {
        return self::select(
            DB::raw("concat(year,'-' , LPAD(month, 2, 0)) as time"),
            DB::raw("sum(approved_production_cost) as total")
        )->where('project_id', $projectId)->groupBy('time')->orderBy('time', 'ASC')->get();


    }
    /**
     * Transformer data
     * @param $items
     * @param $isAllowUpdateApproveCostPrice
     * @return array
     */
    public static function transFormerData($items, $isAllowUpdateApproveCostPrice)
    {
        $arrNew = [];
        foreach ($items as $item) {
            if (!array_key_exists($item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT), $arrNew)) {
                $arrNew[$item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT)] = [
                    'id' => $item['id'],
                    'project_id' => $item['project_id'],
                    'approved_production_cost' => $item['approved_production_cost'],
                    'price' => $item['price'],
                    'unapproved_price' => $item['unapproved_price'],
                    'unit_price' => $isAllowUpdateApproveCostPrice ? $item['unit_price'] : '',
                    'year' => $item['year'],
                    'month' => str_pad($item['month'], 2, '0', STR_PAD_LEFT),
                    'team_id' => $item['team_id'],
                    'note' => $item['note'],
                    'role' => $item['role'],
                    'level' => $item['level'],
                    'detail' => []
                ];
            } else {
                $arrNew[$item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT)]['detail'][] =
                    [
                        'id' => $item['id'],
                        'approved_production_cost' => $item['approved_production_cost'],
                        'price' => $item['price'],
                        'unapproved_price' => $item['unapproved_price'],
                        'unit_price' => $isAllowUpdateApproveCostPrice ? $item['unit_price'] : '',
                        'team_id' => $item['team_id'],
                        'note' => $item['note'],
                        'role' => $item['role'],
                        'level' => $item['level'],
                    ];
            }

        }

        return $arrNew;
    }

    /***
     * Delete
     */
    public static function deleteProjectApprovedCost($id)
    {
        return self::findOrFail($id)->delete();
    }

    /**
     * update project
     */
    public static function updateProjectProductionCost($input, $project, $isAllowUpdateApproveCostPrice, $typeCOO = false)
    {
        $data = json_decode($input, true);
        $input = array_filter($data);
        DB::beginTransaction();
        $items = self::where('project_id', $project->id)->pluck( 'price', 'id')->toArray();

        //format val price
        foreach ($input as $key => $value) {
            if (isset($value['price'])) {
                $input[$key]['price'] = str_replace(',', '', $value['price']);
            }
            if (isset($value['price_main'])) {
                $input[$key]['price_main'] = str_replace(',', '', $value['price_main']);
            }

            if (isset($value['detail']) && count($value['detail']) > 0) {
                foreach ($value['detail'] as $subKey => $val) {
                    if (isset($val['price'])) {
                        $input[$key]['detail'][$subKey]['price'] = str_replace(',', '', $val['price']);
                    }
                    if (isset($val['price_main'])) {
                        $input[$key]['detail'][$subKey]['price_main'] = str_replace(',', '', $val['price_main']);
                    }
                }
            }
        }

        foreach ($input as $value) {
            if (!$isAllowUpdateApproveCostPrice) $value['price'] = -1;
            if (isset($value['id']) && isset($items[$value['id']]) && (!isset($value['price']) || $value['price'] < 0)) {
                $value['price'] = $items[$value['id']];
                if (count($value['detail']) > 0) {
                    foreach ($value['detail'] as $val) {
                        if (!$isAllowUpdateApproveCostPrice) $val['price'] = -1;
                        if (isset($val['id']) && isset($items[$val['id']]) && (!isset($val['price']) || $val['price'] < 0)) {
                            $val['price'] = $items[$val['id']];
                        }
                    }
                }
            }
        }

        // self::where('project_id', $projectId)->forceDelete();
        $result = self::insertApprovedProjectCost($input, $project->id, array_keys($items), $typeCOO, $project);
        is_array($result) && $result['status'] ? DB::commit() : DB::rollback();

        return $result;
    }

    public static function getProjectDraft($project)
    {
        return Project::where('parent_id', '=', $project->id)->first();
    }

    public static function insertApprovedProjectCost($input, $projectId, $arrItemIds = [], $typeCOO = false, $project = null)
    {
        $arrayInsert = [];
        $totalCostDetail = 0;
        $status = false;
        $arrIds = [];
        $arrTempIds = [];
        $arrDeletedIds = [];
        foreach ($input as $value) {
            if ((isset($value['approved_production_cost']) && $value['approved_production_cost'] != '') || (isset($value['price']) && $value['price'] != '')) {
                if (isset($value['id'])) {
                    $arrIds[] = $value['id'];
                }
                if (isset($value['id']) && $value['id_temp'] == 1) {
                    $arrTempIds[] = $value['id'];
                }
                $arrayInsert[] = self::insertApprovedProductionCost($projectId, $value, $typeCOO);
                $totalCostDetail += ((isset($value['approved_production_cost']) && $value['approved_production_cost'] != '') ? $value['approved_production_cost'] : 0);
            }
            
            foreach ($value['detail'] as $val) {
                if ((isset($val['approved_production_cost']) && $val['approved_production_cost'] != '') || (isset($val['price']) && $val['price'] != '')) {
                    if (isset($val['id'])) {
                        $arrIds[] = $val['id'];
                    }
                    if (isset($val['id']) && $val['id_temp'] == 1) {
                        $arrTempIds[] = $val['id'];
                    }
                    $val['month'] = $value['month'];
                    $val['year'] = $value['year'];
                    
                    $arrayInsert[] = self::insertApprovedProductionCost($projectId, $val, $typeCOO);
                    $totalCostDetail += ((isset($val['approved_production_cost']) && $val['approved_production_cost'] != '')  ? $val['approved_production_cost'] : 0);
                }
            }
        }

        if (count($arrItemIds) != count($arrTempIds) && count($arrTempIds) > 0) {
            $arrIdsNotUpdate = [];
            foreach ($arrItemIds as $item) {
                if (!in_array($item, $arrTempIds)) {
                    $arrIdsNotUpdate[] = $item;
                }
            }
            $sumItemNotUpdate = self::whereIn('id', $arrIdsNotUpdate)->sum('approved_production_cost');
            $sumItemNotUpdate = $sumItemNotUpdate ? $sumItemNotUpdate : 0;
            $totalCostDetail += $sumItemNotUpdate;
        }
        
        if ($arrayInsert) {
            $qualityDraftProdCost = ProjQuality::getQualityDraft($projectId, 'cost_approved_production');
            $quality = ProjQuality::getFollowProject($projectId);
            $project = Project::find($projectId);
            $projectDraft = self::getProjectDraft($project);
            $typeMM = $projectDraft ? $projectDraft->type_mm : $project->type_mm;
            $maxApprovedCost = (float) ($qualityDraftProdCost ? $qualityDraftProdCost->cost_approved_production : $quality->cost_approved_production);

            if ($typeMM == Project::MD_TYPE) {
                $maxApprovedCost = $maxApprovedCost / 20;
            }

            $maxApprovedCost = round($maxApprovedCost, 2);
            $totalCostDetail = round($totalCostDetail, 2);

            foreach ($arrItemIds as $itemArr) {
                if (!in_array($itemArr, $arrIds)) {
                    $arrDeletedIds[] = $itemArr;
                }
            }
            if ($maxApprovedCost >= $totalCostDetail) {
                $isChange = false;
                $currentUser = Permission::getInstance()->getEmployee();
                $allTeam = Project::getAllTeamOfProject($projectId);
                $hasPermissionViewCostPriceDetail = Project::hasPermissionViewCostPriceDetail($currentUser->id, $allTeam);
                foreach ($arrayInsert as $key => $item) {
                    $dataID = (int)$item['id'];
                    if ($item['id'] != ' ' && $dataID > 0 && $item['team_id']) {
                        $dataItem = self::where('id', $dataID)->first();
                        if ($dataItem) {
                            if ($dataItem->price != $item['unapproved_price'] && $item['unapproved_price'] != $dataItem->unapproved_price) {
                                $isChange = true;
                            } else {
                                if ($dataItem->price == $item['unapproved_price']) {
                                    $item['unapproved_price'] = null;
                                } else {
                                    if (!$typeCOO) {
                                        unset($item['unapproved_price']);
                                    }
                                }
                            }
                            if (!$hasPermissionViewCostPriceDetail) {
                                unset($item['unapproved_price']);
                            }
                            $dataItem->update($item);
                        }
                    } else {
                        if ($item['team_id']) {
                            $isChange = true;
                            unset($item['id']);
                            self::create($item);
                        }
                    }
                }
                self::whereIn('id', $arrDeletedIds)->delete();

                if (!$typeCOO && $isChange && $hasPermissionViewCostPriceDetail) {
                    //gui mail
                    $employeeCanApproves = self::getEmployeeCanApprove();
                    if ($employeeCanApproves) {
                        $dataMail = [
                            'projectName' => $project->name,
                            'link' => route('project::project.edit', ['id' => $project->id]),
                        ];
                        $subject = '[Rikkei.vn] Dự án '.$project->name.' vừa có thay đổi về đơn giá';
                        foreach ($employeeCanApproves as $key => $emp) {
                            $emailQueue = new EmailQueue();
                            $emailQueue->setTo($emp->employee_email, $emp->employee_name)
                                ->setSubject($subject)
                                ->setTemplate("project::emails.approved_production_cost", $dataMail)
                                ->save();
                        }
                    }
                }
                $status = true;
            }

            return [
                'status' => $status,
                'total_approve_production_cost' => $totalCostDetail,
                'type_mm' => $project->getLabelTypeMM($typeMM),
                'approve_production_cost' => $maxApprovedCost
            ];
        }
        return false;
    }

    public static function getEmployeeCanApprove() {
        $route = 'project::project.approved-production-cost';
        $tblAction = Action::getTableName();
        $tblPermission  = PermissionModel::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeRole = EmployeeRole::getTableName();
        $tblUser = User::getTableName();
        $now = Carbon::now();
        $employeesCanApproveByRole = PermissionModel::select("{$tblEmployeeRole}.employee_id", "{$tblEmployee}.name as employee_name", "{$tblEmployee}.email as employee_email", "{$tblUser}.avatar_url")
            ->join("{$tblAction}", "{$tblAction}.parent_id", "=", "{$tblPermission}.action_id")
            ->join("{$tblEmployeeRole}", "{$tblEmployeeRole}.role_id", "=", "{$tblPermission}.role_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblEmployeeRole}.employee_id")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->where("{$tblPermission}.scope", "!=", PermissionModel::SCOPE_NONE)
            ->where("{$tblAction}.route", $route)
            ->whereNull("{$tblPermission}.team_id")
            ->whereNull("{$tblEmployee}.deleted_at")
            ->where(function ($query) use ($tblEmployee, $now) {
                $query->orWhereNull("{$tblEmployee}.leave_date")
                    ->orWhereDate("{$tblEmployee}.leave_date", '>=', $now->format('Y-m-d'));
            });
        $employeesCanApproveByRole = $employeesCanApproveByRole->groupBy("{$tblEmployeeRole}.employee_id")
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->get();
        return $employeesCanApproveByRole;
    }

    /**
     * update project
     */
    public static function updatePointProjectApprovedCost($request)
    {
        DB::beginTransaction();
        if ($request->id) {
            $proCost = self::find($request->id);
            $proCost->approved_production_cost = $request->approved_production_cost;
        } else {
            $proCost = new ProjectApprovedProductionCost;
            $proCost->fill($request->except('id'));
        }
        $proCost->save();


        DB::commit();

        return [
            'status' => true,
            'data' => $proCost
        ];
    }

    /**
     * get Project Approved Production Cost
     * @param id
     * @return array
     */
    public function getAllByProjectIdYearThan($id, $year)
    {
        return self::where('project_id', $id)
        ->where('year', '>=', $year)
        ->orderBy('year')
        ->orderBy('month')
        ->get();
    }

    /**
     * @param int $cloneId
     * @param int $projectId
     * @return bool|null
     */
    public static function insertCloneApprovedCost($cloneId, $projectId)
    {
        $approvedCost = self::where('project_id', $cloneId)
            ->whereNull('deleted_at')
            ->get();
        if ($approvedCost) {
            $approvedCost->map(function ($item) use ($projectId) {
                unset($item->id);
                unset($item->created_at);
                unset($item->updated_at);
                $item->project_id = $projectId;
            });
            return self::insert($approvedCost->toArray());
        }
        return null;
    }
}
