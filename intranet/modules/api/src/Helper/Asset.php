<?php

namespace Rikkei\Api\Helper;

use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetOrigin;
use Rikkei\Assets\Model\AssetSupplier;
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Team\Model\Employee;
use Rikkei\Api\Helper\Base as BaseHelper;
use DB;
use Exception;
use Carbon\Carbon;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Assets\Model\RequestAssetItem;
use Rikkei\Assets\Model\RequestAssetTeam;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Assets\View\RequestView;
use Rikkei\Assets\View\AssetView;

class Asset extends BaseHelper
{
    public function __construct()
    {
        $this->model = AssetItem::class;
    }

    /**
     * get asset info
     * @param string $assetCode
     * @return mixed
     */
    public function getInfo($assetCode)
    {
        $tblAssetItem = AssetItem::getTableName();
        $collection = $this->getCollection();
        $collection->where("{$tblAssetItem}.code", $assetCode);
        return $collection->first();
    }

    /**
     * get all assets
     * @return array
     */
    public function getAll()
    {
        $collection = $this->getCollection();
        return $collection->get();
    }

    /**
     * get list assets by array asset codes
     * @param array $assetCodes
     * @return array
     */
    public function getAssetsList($assetCodes)
    {
        $tblAssetItem = AssetItem::getTableName();
        $collection = $this->getCollection();
        $collection->whereIn("{$tblAssetItem}.code", $assetCodes);
        return $collection->get();
    }

    /**
     * get list assets of employee
     * @param string $employeeCode
     * @return array
     */
    public function getAssetsOfEmployee($employeeCode)
    {
        $tblEmployee = Employee::getTableName();
        $tblAssetItem = AssetItem::getTableName();
        $collection = $this->getCollection();
        $collection->where("{$tblEmployee}.employee_code", $employeeCode)
            ->where("{$tblAssetItem}.state", AssetItem::STATE_USING);
        return $collection->get();
    }

    /**
     * get collection asset
     * @return mixed
     */
    public function getCollection()
    {
        $tblAssetItem = AssetItem::getTableName();
        $tblAssetCategory = AssetCategory::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblEmployeeAsManager = 'employees_as_manager';
        $tblAssetWarehouse = AssetWarehouse::getTableName();
        $tblAssetOrigin = AssetOrigin::getTableName();
        $tblAssetSupplier = AssetSupplier::getTableName();
        $selectedFields = [
            "{$tblAssetItem}.code",
            "{$tblAssetItem}.name",
            "{$tblAssetItem}.serial",
            "{$tblAssetItem}.purchase_date",
            "{$tblAssetItem}.warranty_priod AS warranty_period",
            "{$tblAssetItem}.warranty_exp_date",
            "{$tblAssetItem}.specification",
            "{$tblAssetItem}.note",
            "{$tblAssetItem}.state",
            "{$tblAssetItem}.received_date",
            "{$tblAssetItem}.allocation_confirm",
            "{$tblAssetCategory}.name AS category_name",
            "{$tblEmployee}.email AS employee_email",
            "{$tblEmployee}.name AS employee_name",
            "{$tblEmployee}.employee_code AS employee_code",
            "{$tblEmployeeAsManager}.email AS manager_email",
            "{$tblEmployeeAsManager}.name AS manager_name",
            "{$tblEmployeeAsManager}.employee_code AS manager_code",
            "{$tblAssetWarehouse}.name AS warehouse_name",
            "{$tblAssetSupplier}.name AS supplier_name",
            "{$tblAssetOrigin}.name AS origin_name",
            DB::raw('(' . $this->selectNameStateSql() . ') AS state_name'),
        ];

        return AssetItem::select($selectedFields)
            ->leftJoin($tblAssetCategory, "{$tblAssetCategory}.id", "=", "{$tblAssetItem}.category_id")
            ->leftJoin($tblAssetWarehouse, "{$tblAssetWarehouse}.id", "=", "{$tblAssetItem}.warehouse_id")
            ->leftJoin($tblEmployee, "{$tblEmployee}.id", "=", "{$tblAssetItem}.employee_id")
            ->leftJoin("{$tblEmployee} AS {$tblEmployeeAsManager}", "{$tblEmployeeAsManager}.id", "=", "{$tblAssetItem}.manager_id")
            ->leftJoin($tblAssetSupplier, "{$tblAssetSupplier}.id", "=", "{$tblAssetItem}.supplier_id")
            ->leftJoin($tblAssetOrigin, "{$tblAssetOrigin}.id", "=", "{$tblAssetItem}.origin_id");
    }

    /**
     * select state name of asset
     * @return string
     */
    public function selectNameStateSql()
    {
        $sql = 'CASE';
        $tblAssetItem = AssetItem::getTableName();
        $labelStates = AssetItem::labelStates();
        foreach ($labelStates as $state => $label) {
            $sql .= " WHEN {$tblAssetItem}.state = {$state} THEN '{$label}'";
        }
        return $sql . " ELSE '' END";
    }

    public function requestAssetCandidate($data)
    {
        $employee = Employee::findByEmail($data['employee_email'], true);
        if (!$employee) {
            throw new Exception('Employee not found');
        }
        $reviewer = Employee::checkStatusEmployeeIsWorking($data['reviewer_email']);
        if (!$reviewer) {
            throw new Exception('Reviewer not found');
        }
        $candidate = Candidate::getCandidateByEmployee($employee->id);
        if (!$candidate) {
            throw new Exception('Candidate not found');
        }
        $candidateId = $candidate->id;

        DB::beginTransaction();
        try {
            $dataRequest = [
                "employee_id" => $employee->id,
                "skype" => $data['employee_skype'],
                "reviewer" => $reviewer->id,
                "request_name" => $data['request_name'],
                "request_date" => $data['request_date'],
                "request_reason" => $data['request_reason']
            ];
            $dataHistory = [];
            $userCurrent = Employee::checkStatusEmployeeIsWorking($data['creator']);
            if (!$userCurrent) {
                throw new Exception('Creator not found');
            }
            $requestId = null;
            $isCreate = false;
            $isUpdate = false;
            if ($requestId) {
                $requestAsset = RequestAsset::find($requestId);
                if (!$requestAsset) {
                    //TH requests bi xoa
                    $isCreate = true;
                    $requestAsset = new RequestAsset();
                    $dataHistory['action'] = RequestAssetHistory::ACTION_CREATE;
                    $dataRequest['created_by'] = $userCurrent->id;
                } else {
                    if (in_array($requestAsset->status, [RequestAsset::STATUS_REVIEWED, RequestAsset::STATUS_APPROVED, RequestAsset::STATUS_CLOSE])) {
                        throw new Exception("Can't create request");
                    }
                    $requestAsset->status = RequestAsset::STATUS_INPROGRESS;
                    $dataHistory['action'] = RequestAssetHistory::ACTION_UPDATE;
                    $isUpdate = true;
                }
            } else {
                $isCreate = true;
                $requestAsset = new RequestAsset();
                $dataHistory['action'] = RequestAssetHistory::ACTION_CREATE;
                $dataRequest['created_by'] = $userCurrent->id;
            }
            
            $teamCode = Employee::getNewestTeamCode($employee->id);
            $prefix = AssetConst::getAssetPrefixByCode($teamCode);
            $dataRequest['team_prefix'] = $prefix;
            $requestAsset->setData(array_map('trim', $dataRequest));

            $dataAsset = $data['asset'];
            if ($requestAsset->save()) {
                if ($requestId) {
                    RequestAssetItem::where('request_id', $requestId)->delete();
                    RequestAssetTeam::where('request_id', $requestId)->delete();
                }
                foreach ($dataAsset as $item) {
                    $requestItem = new RequestAssetItem;
                    $requestItem->request_id = $requestAsset->id;
                    $requestItem->asset_category_id = $item['name'];
                    $requestItem->quantity = $item['number'];
                    $requestItem->save();
                }
                $teamsByEmployee = RequestView::getTeamsByEmployee($requestAsset->employee_id);
                if (count($teamsByEmployee)) {
                    $dataRequestTeam = [];
                    foreach ($teamsByEmployee as $item) {
                        $dataRequestTeam[] = [
                            'request_id' => $requestAsset->id,
                            'team_id' => $item->team_id,
                        ];
                    }
                    RequestAssetTeam::insert($dataRequestTeam);
                }
                $now = Carbon::now();
                $dataHistory['request_id'] = $requestAsset->id;
                $dataHistory['employee_id'] = $userCurrent->id;
                $dataHistory['created_at'] = $now;
                $dataHistory['updated_at'] = $now;
                RequestAssetHistory::insert($dataHistory);

                if ($isCreate) {
                    RequestAsset::afterCreateRequest($requestAsset);
                    //update candidate request
                    if ($candidateId) {
                        $candidate = Candidate::find($candidateId);
                        if ($candidate) {
                            $candidate->request_asset_id = $requestAsset->id;
                            $candidate->save();
                        }
                    }
                }
                if ($isUpdate) {
                    RequestAsset::afterUpdateRequest($requestAsset);
                }
                // synchronized skype
                AssetView::synchronizedSkype($requestAsset->employee_id, $requestAsset->skype);
            }
            DB::commit();
            return [
                'success' => 1,
                'message' => 'Tạo yêu cầu tài sản thành công!',
            ];
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}
