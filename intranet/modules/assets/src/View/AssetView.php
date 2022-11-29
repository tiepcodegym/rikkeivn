<?php

namespace Rikkei\Assets\View;

use Carbon\Carbon;
use DB;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\Model\AssetHistory;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetOrigin;
use Rikkei\Assets\Model\AssetSupplier;
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Core\View\View;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmployeeContact;

class AssetView
{
    /**
     * Auto generate code
     * @param  [string] $prefixCode
     * @param  [int|null] $suffixCode
     * @return [string]
     */
    public static function generateCode($prefixCode, $suffixCode)
    {
        $code = '';
        $suffixCode += 1;
        $countNumber = 1;
        $divNumber = $suffixCode;
        while ($divNumber >= 10) {
            $divNumber = $divNumber / 10;
            $countNumber++;
        }
        switch ($countNumber) {
            case 1:
                $code = $prefixCode . '00000' . $suffixCode;
                break;
            case 2:
                $code = $prefixCode . '0000' . $suffixCode;
                break;
            case 3:
                $code = $prefixCode . '000' . $suffixCode;
                break;
            case 4:
                $code = $prefixCode . '00' . $suffixCode;
                break;
            case 5:
                $code = $prefixCode . '0' . $suffixCode;
                break;
            default:
                $code = $prefixCode . $suffixCode;
                break;
        }
        return $code;
    }

    /**
     * Get team child recursive
     * @param array $teamPaths
     * @param null|int $teamId
     */
    public static function getTeamChildRecursive(&$teamPaths = [], $teamId = null)
    {
        if (! $teamId) {
            return;
        }
        $teamChildren = Team::select('id', 'parent_id')
            ->where('parent_id', $teamId)
            ->get();
        if (! count($teamChildren)) {
            return;
        }
        foreach ($teamChildren as $item) {
            $teamPaths[] = (int) $item->id;
            self::getTeamChildRecursive($teamPaths, $item->id);
        }
    }

    /**
     * Get employees by team and child teams
     * @param  [int] $teamId
     * @return [type]
     */
    public static function getEmployeesByTeam($teamId = null)
    {
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblRole = Role::getTableName();
        $teamIds = [];
        $teamIds[] = (int) $teamId;
        self::getTeamChildRecursive($teamIds, $teamId);
        DB::statement("SET @duplicateEmpl:=-1");
        return Employee::select([
            "{$tblEmployee}.id as employee_id",
            "{$tblEmployee}.employee_code",
            "{$tblEmployee}.name as employee_name",
            DB::raw("CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) as role_name"),
        ])
        ->leftJoin(
            DB::raw("(select employee_id, team_id, start_at, role_id, is_duplicate, end_at
                    FROM (SELECT employee_id, team_id, start_at, role_id, end_at, IF(@duplicateEmpl = employee_id, 1, 2) as is_duplicate, @duplicateEmpl := employee_id FROM `employee_team_history` as t_eth
                    WHERE end_at IS NULL
                    ORDER BY employee_id asc, start_at DESC) as ttt) as tmp_teh"), function ($join) {
            $join->on('employees.id', '=', 'tmp_teh.employee_id')
                ->where('is_duplicate', '=', 2);
            }
        )
        ->leftjoin($tblTeam, "$tblTeam.id", '=', 'tmp_teh.team_id')
        ->leftjoin($tblRole, "$tblRole.id", '=', 'tmp_teh.role_id')
        ->whereNull("{$tblEmployee}.leave_date")
        ->whereIn("{$tblTeam}.id", $teamIds)
        ->get();
    }

    /**
     * Get employee information
     * @param [int] $employeeId
     * @return [type]
     */
    public static function getEmployeeInformation($employeeId)
    {
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblRole = Role::getTableName();

        return Employee::select("{$tblEmployee}.id as employee_id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name as employee_name", DB::raw("GROUP_CONCAT(DISTINCT CONCAT({$tblRole}.role, ' - ', {$tblTeam}.name) ORDER BY {$tblRole}.role DESC SEPARATOR '; ') as role_name"))
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", "=", "{$tblEmployee}.id")
            ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
            ->join("{$tblRole}", "{$tblRole}.id", "=", "{$tblTeamMember}.role_id")
            ->where("{$tblRole}.special_flg", DB::raw(Role::FLAG_POSITION))
            ->where("{$tblEmployee}.id", $employeeId)
            ->first();
    }

    /**
     * Get nick name from email
     * @param string $name
     * @return string
     */
    public static function getNickName($name, $ucfirst = false)
    {
        $nickName = strtolower(preg_replace('/@.*/', '', $name));
        if ($ucfirst) {
            $nickName = ucfirst($nickName);
        }
        return $nickName;
    }

    /**
     * Push email to queue
     * @return boolean
     */
    public static function pushEmailToQueue($data, $template, $notify = true)
    {
        $emailQueue = new EmailQueue();

        $emailQueue->setTo($data['mail_to'])
            ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft intranet')
            ->setSubject($data['mail_title'])
            ->setTemplate($template, $data);
        if ($notify && isset($data['to_id'])) {
            $emailQueue->setNotify(
                $data['to_id'],
                isset($data['noti_content']) ? $data['noti_content'] : $data['mail_title'], $data['href'], [
                    'content_detail' => RkNotify::renderSections($template, $data)
                ]
            );
        }
        try {
            $emailQueue->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check format sheet import
     *
     * @param array $headingDefine
     * @param array $headingSheet
     * @return bool
     */
    public static function checkHeading($headingDefine, $headingSheet)
    {
        $check = true;
        // $arrGeneral = array_intersect_assoc($headingDefine, $headingSheet);
        $arrGeneral = array_intersect($headingSheet, $headingDefine);
        if (count($arrGeneral) != count($headingDefine)) {
            $check = false;
        }
        return $check;
    }

    public static function importFile($dataRow)
    {
        $supplierExcel = [];
        $originExcel = [];
        $teamExcel = [];
        $empExcel = [];
        $cateExcel = [];
        $warehouseExcel = [];
        $managerExcel = [];
        $errors = [];
        $dataHistories = [];
        $listCodeAssetUsing = [];
        $userExcel = [];
        $allState = AssetItem::labelStates();
        foreach ($dataRow as $key => $row) {
            if (!$row['ma_tai_san'] &&
                !$row['ten_tai_san'] &&
                !$row['loai_tai_san'] &&
                !$row['tinh_trang']) {
                continue;
            }
            if (!$row['ma_tai_san'] ||
                !$row['ma_kho'] ||
                !$row['ten_tai_san'] ||
                !$row['loai_tai_san'] ||
                !$row['tinh_trang']
            ) {
                $errors[] = trans('asset::message.Row :row: required information to be filled', ['row' => $key + 2]);
            }
            $state = array_search($row['tinh_trang'], $allState);
            if ($state == AssetItem::STATE_USING) {
                if (!isset($row['account_su_dung']) || !$row['account_su_dung']) {
                    $errors[] = trans('asset::message.Row :row: account not null when state use', ['row' => $key + 2]);
                }
            } elseif($state == AssetItem::STATE_NOT_USED) {
                if (isset($row['account_su_dung']) && $row['account_su_dung']) {
                    $errors[] = trans('asset::message.Row :row: state user then account null', ['row' => $key + 2]);
                }
            }
            if (isset($row['account_su_dung']) &&
                $row['account_su_dung'] &&
                !in_array($row['account_su_dung'], $userExcel)
            ) {
                $userExcel[] = strtolower($row['account_su_dung']);
            }
            if (isset($row['account_qlts']) &&
                $row['account_qlts'] &&
                !in_array($row['account_qlts'], $managerExcel)
            ) {
                $managerExcel[] = strtolower($row['account_qlts']);
            }
            $prefix = substr(trim($row['ma_tai_san']), 0, 2);
            if (!in_array(trim($prefix), AssetConst::listPrefix())) {
                $errors[] = trans('asset::message.Row :row: invalid code assets false', ['row' => $key + 2]);
                continue;
            }

            if (isset($row['ma_nha_cung_cap']) &&
                $row['ma_nha_cung_cap'] &&
                !in_array($row['ma_nha_cung_cap'], $supplierExcel)) {
                $supplierExcel[] = $row['ma_nha_cung_cap'];
            }

            if (isset($row['nguon_goc']) &&
                $row['nguon_goc'] &&
                !in_array($row['nguon_goc'], $originExcel)) {
                $originExcel[] = $row['nguon_goc'];
            }

            if (isset($row['thuoc_don_vi']) &&
                $row['thuoc_don_vi'] &&
                !in_array($row['thuoc_don_vi'], $teamExcel)) {
                $teamExcel[] = $row['thuoc_don_vi'];
            }

            if (isset($row['ma_nhan_vien']) &&
                $row['ma_nhan_vien'] &&
                !in_array($row['ma_nhan_vien'], $empExcel)) {
                $empExcel[] = $row['ma_nhan_vien'];
            }

            if (isset($row['loai_tai_san']) &&
                $row['loai_tai_san'] &&
                !in_array($row['loai_tai_san'], $cateExcel)) {
                $cateExcel[] = $row['loai_tai_san'];
            }

            if (isset($row['ma_kho']) &&
                $row['ma_kho'] &&
                !in_array($row['ma_kho'], $warehouseExcel)
            ) {
                $warehouseExcel[] = $row['ma_kho'];
            }
        }
        $listManager = Employee::whereIn(DB::raw('SUBSTRING(email, 1, LOCATE("@", email) - 1)'), $managerExcel)->lists('id', DB::raw('SUBSTRING(email, 1, LOCATE("@", email) - 1) as member'))->toArray();
        $listUser = Employee::select("id", "name", DB::raw("SUBSTRING(email, 1, LOCATE('@', email) - 1) as email"))->whereIn(DB::raw('SUBSTRING(email, 1, LOCATE("@", email) - 1)'), $userExcel)->get();
        $dataInsert = [];
        $emp = Permission::getInstance()->getEmployee();
        $prefixPerson = AssetView::getRegionByEmp($emp->id);
        if (count($listManager) !== count($managerExcel)) {
            $accoutManager = [];
            foreach ($listManager as $key => $value) {
                $accoutManager[] = $key;
            }
            $temporary = array_diff($managerExcel, $accoutManager);
            $accountNot = null;
            foreach ($temporary as $key => $value) {
                $accountNot .= " ".$value;
            }
            $errors[] = trans('asset::message.Accout manager is not in the system: :account', ['account' => $accountNot]);
        }
        if (count($listUser) !== count($userExcel)) {
            $userExist = [];
            foreach ($listUser as $key => $value) {
                $userExist[] = $value->email;
            }
            $temporary = array_diff($userExcel, $userExist);
            $accountNot = null;
            foreach ($temporary as $key => $value) {
                $accountNot .= " ".$value;
            }
            $errors[] = trans('asset::message.Account is not in the system: :account', ['account' => $accountNot]);
        }
        if (!empty($errors)) {
            return $errors;
        }
        $listCate = AssetCategory::whereIn('name', $cateExcel)->pluck('id', 'name')->toArray();
        $listSupplier = AssetSupplier::whereIn('code', $supplierExcel)->pluck('id', 'code')->toArray();
        $listOrigin = AssetOrigin::whereIn('name', $originExcel)->pluck('id', 'name')->toArray();
        $listTeam = Team::whereIn('name', $teamExcel)->pluck('id', 'name')->toArray();
        $listWarehouse = AssetWarehouse::whereIn('code', $warehouseExcel)->pluck('id', 'code')->toArray();
        foreach ($dataRow as $key => $row) {
            if (!$row['ma_tai_san'] ||
                !$row['ten_tai_san'] ||
                !$row['loai_tai_san'] ||
                !$row['tinh_trang']
            ) {
                continue;
            }
            if (Permission::getInstance()->isScopeCompany()) {
                // nothing
            } else {
                if (isset($prefixPerson) && $prefixPerson && $prefixPerson !== $prefix) {
                    continue;
                } else {
                    //nothing
                }
            }
            if (isset($row['ngay_mua']) && $row['ngay_mua'] && isset($row['thoi_gian_bao_hanh']) && $row['thoi_gian_bao_hanh']) {
                $warrantyExpDate = AssetItem::genWarrantyExpDate($row['ngay_mua'], $row['thoi_gian_bao_hanh']);
            }
            $state = array_search($row['tinh_trang'], $allState);
            $data = [
                'code' => trim($row['ma_tai_san']),
                'name' => $row['ten_tai_san'],
                'serial' => (isset($row['so_seri']) && $row['so_seri']) ? $row['so_seri'] : null,
                'category_id' => null,
                'supplier_id' => null,
                'origin_id' => null,
                'manager_id' => null,
                'warehouse_id' => null,
                'team_id' => 0,
                'purchase_date' => isset($row['ngay_mua']) && $row['ngay_mua'] ? $row['ngay_mua'] : null,
                'warranty_priod' => isset($row['thoi_gian_bao_hanh']) && $row['thoi_gian_bao_hanh'] ? $row['thoi_gian_bao_hanh'] : null,
                'warranty_exp_date' => (isset($warrantyExpDate) && $warrantyExpDate) ? $warrantyExpDate : null ,
                'employee_id' => null,
                'received_date' => (isset($row['ngay_nhan']) && $row['ngay_nhan']) ? $row['ngay_nhan'] : null,
                'state' => $state,
                'note' => (isset($row['ghi_chu']) && $row['ghi_chu']) ? $row['ghi_chu'] : null,
                'prefix' => $prefix,
                'specification' =>  (isset($row['quy_cach_tai_san']) && $row['quy_cach_tai_san']) ? $row['quy_cach_tai_san'] : null,
                'configure' =>  (isset($row['cau_hinh_tai_san']) && $row['cau_hinh_tai_san']) ? $row['cau_hinh_tai_san'] : null,
                'allocation_confirm' => null,
                'change_date' => null,
                'created_by' => $emp->id,
                'note_of_emp' => null
            ];

            if (isset($row['ma_nha_cung_cap']) && $row['ma_nha_cung_cap']) {
                if (isset($listSupplier[$row['ma_nha_cung_cap']]) &&
                    $listSupplier[$row['ma_nha_cung_cap']]) {
                    $data['supplier_id'] = $listSupplier[$row['ma_nha_cung_cap']];
                }
            }

            if (isset($row['nguon_goc']) && $row['nguon_goc']) {
                if (isset($listOrigin[$row['nguon_goc']]) &&
                    $listOrigin[$row['nguon_goc']]
                ) {
                    $data['origin_id'] = $listOrigin[$row['nguon_goc']];
                }
            }

            if (isset($row['loai_tai_san']) && $row['loai_tai_san']) {
                if (isset($listCate[$row['loai_tai_san']]) &&
                    $listCate[$row['loai_tai_san']]) {
                    $data['category_id'] = $listCate[$row['loai_tai_san']];
                } else {
                    continue;
                }
            }

            if (isset($row['ma_kho']) && $row['ma_kho']) {
                if (trim($row['ma_kho'])) {
                    if (isset($listWarehouse[$row['ma_kho']]) &&
                        $listWarehouse[$row['ma_kho']]) {
                        $data['warehouse_id'] = $listWarehouse[$row['ma_kho']];
                    }
                }
            }

            if (isset($row['thuoc_don_vi']) && $row['thuoc_don_vi']) {
                $data['team_id'] = (isset($listTeam[$row['thuoc_don_vi']]) && $listTeam[$row['thuoc_don_vi']]) ? $listTeam[$row['thuoc_don_vi']] : 0;
            }
            $temporary = (isset($row['ngay_nhan']) && $row['ngay_nhan']) ? $row['ngay_nhan'] : null;
            $asset = AssetItem::where('code', $row['ma_tai_san'])->first();
            if (isset($row['account_su_dung']) && $row['account_su_dung']) {
                $accountUser = $row['account_su_dung'];
                $employee = $listUser->filter(function ($item) use ($accountUser) {
                    return $item->email == $accountUser;
                })->values();
                if (isset($employee) && count($employee) > 0) {
                    if ($state != AssetItem::STATE_NOT_USED) {
                        if (isset($asset) && !empty($asset)) {
                            $assetHistory = AssetHistory::where('asset_id', '=', $asset->id)
                                ->where('employee_id', '=', $employee[0]->id)
                                ->where('state', '=', $state)
                                ->first();
                        }
                        if (!isset($assetHistory) || empty($assetHistory)) {
                            $dataHistories[] = [
                                'note' => \Lang::get('asset::view.Allocation asset to: :name', ['name' => $employee[0]->name]),
                                'state' => $state,
                                'change_date' => (isset($row['ngay_thay_doi']) && $row['ngay_thay_doi']) ? $row['ngay_thay_doi'] : $temporary,
                                'created_by' => $emp->id,
                                'asset_id' => $row['ma_tai_san'],
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'employee_id' => $employee[0]->id,
                            ];
                            $listCodeAssetUsing[] = $row['ma_tai_san'];
                        } else {
                            $assetHistory->change_date = (isset($row['ngay_thay_doi']) && $row['ngay_thay_doi']) ? $row['ngay_thay_doi'] : $temporary;
                            $assetHistory->save();
                        }
                    }
                    $data['employee_id'] = $employee[0]->id;
                    $data['allocation_confirm'] = AssetItem::ALLOCATION_CONFIRM_TRUE;
                    $data['note_of_emp'] = (isset($row['ghi_chu_cua_nhan_vien']) && $row['ghi_chu_cua_nhan_vien']) ? $row['ghi_chu_cua_nhan_vien'] : null;
                }
            }
            if (isset($row['account_qlts']) && $row['account_qlts']) {
                if (isset($listManager[strtolower($row['account_qlts'])]) && $listManager[strtolower($row['account_qlts'])]) {
                    $data['manager_id'] = $listManager[strtolower($row['account_qlts'])];
                }
            }
            $data['change_date'] = (isset($row['ngay_thay_doi']) && $row['ngay_thay_doi']) ? $row['ngay_thay_doi'] : $temporary;
            $asset = AssetItem::where('code', trim($row['ma_tai_san']))->first();
            if (isset($asset) && $data['code'] == $asset->code) {
                foreach ($data as $key => $items) {
                    if (!$items) {
                        unset($data[$key]);
                    }
                }
            }
            if (!$asset) {
                $dataInsert[] = $data;
            } else {
                $asset->fill($data);
                $asset->save();
            }
        }
        if (!empty($dataInsert)) {
            AssetItem::insert($dataInsert);
        }
        $listAssetIdUsing = AssetItem::whereIn('code', $listCodeAssetUsing)->pluck('id', 'code')->toArray();
        $dataHis = [];
        if ($listAssetIdUsing && !empty($dataHistories)) {
            foreach ($dataHistories as $item) {
                $tmp = $item['asset_id'];
                $item['asset_id'] = $listAssetIdUsing[$tmp];
                $dataHis[$tmp] = $item;
            }
            AssetHistory::insert($dataHis);
        }
    }

    /**
     * Get prefix branch of employee
     *
     * @param int $employeeId
     *
     * @return string
     */
    public static function getRegionByEmp($employeeId, $returnRegion = false)
    {
        $teamNew = Employee::getTeamNewByEmpId($employeeId);
        $employee = Employee::getEmpById($employeeId);
        $region = Team::getOnlyOneTeamCodePrefix($employee);
        if ($returnRegion) {
            return $region;
        }
        return static::getPrefixPerson($region);
    }

    /**
     * Get prefix of branch from branch code
     * @param string $branchCode
     * @return string
     */
    public static function getPrefixPerson($branchCode)
    {
        switch (strtolower($branchCode)) {
            case Team::CODE_PREFIX_HN:
                return AssetConst::PREFIX_HN;
            case Team::CODE_PREFIX_DN:
                return AssetConst::PREFIX_DN;
            case Team::CODE_PREFIX_JP:
                return AssetConst::PREFIX_JP;
            case Team::CODE_PREFIX_HCM:
                return AssetConst::PREFIX_HCM;
            case Team::CODE_PREFIX_AI:
                return AssetConst::PREFIX_AI;
            case Team::CODE_PREFIX_RS:
                return AssetConst::PREFIX_RS;
            default:
                return AssetConst::PREFIX_HN;
        }
    }

    /**
     * @param $state
     * @return mixed
     */
    public static function countAssetByState($state)
    {
        return AssetItem::where('state', $state)->count();
    }

    public static function getContactOfRequestUser($empId)
    {
        $contactOfEmp = EmployeeContact::getByEmp($empId);
        if (!$contactOfEmp) {
            $contactOfEmp = new EmployeeContact();
        }
        return $contactOfEmp;
    }

    /**
     * synchronized skype of request user into profile
     *
     * @param int $empId
     * @param string $newSkype
     *
     * @return boolean
     */
    public static function synchronizedSkype($empId, $newSkype)
    {
        $contactOfEmp = EmployeeContact::getByEmp($empId);
        if (!$contactOfEmp) {
            $contactOfEmp = new EmployeeContact();
            $contactOfEmp->employee_id = $empId;
        }
        $contactOfEmp->skype = $newSkype;
        return $contactOfEmp->save();
    }

    //start (issue 4075)
    /**
     * Get employeeIds by asset (issue 4075)
     * @param array $assetIds
     *
     * @return array
     */
    public static function getDataSendMail($assetIds)
    {
        return AssetItem::select('prefix', 'employee_id', 'manage_asset_items.id as asset_id',
            'code', 'manage_asset_items.name as asset_name', 'manage_asset_items.state as asset_state', 'employees.email')
            ->leftJoin('employees', 'employees.id', '=', 'manage_asset_items.employee_id')
            ->whereIn('manage_asset_items.id', $assetIds)
            ->whereNotNull('employee_id')->get();
    }

    /**
     * @param array $assets
     * @param array $data
     * @param null|string $template
     */
    public static function sendMailToEmpUseAsset($assets, $data = [], $template = null)
    {
        $dataSendMail = [];
        if (!$assets) return;
        if ($data['isApproved']) {
            $data['labelApprove'] = trans('asset::view.has approved');
        } else {
            $data['labelApprove'] = trans('asset::view.reject');
        }
        $header = trans('asset::view.Bellow here are asset has IT :labelApprove :stateLabel', [
            'labelApprove' => $data['labelApprove'],
            'stateLabel' => $data['state'],
        ]);

        foreach ($assets as $item) {
            if (!isset($dataSendMail[$item->employee_id])) {
                $dataSendMail[$item->employee_id] = [
                    'to_id' => $item->employee_id,
                    'mail_to' => $item->email,
                    'mail_title' => trans('asset::view.IT approve asset.'),
                    'noti_content' => trans('asset::view.IT approve asset. Use mail to view detail'),
                    'receiver_name' => View::getNickName($item->email),
                    'href' => null,
                    'header' => $header,
                    'assets' => [],
                ];
            }
            if ($dataSendMail[$item->employee_id]['to_id'] == $item->employee_id) {
                $dataSendMail[$item->employee_id]['assets'][] = [
                    'id' => $item->asset_id,
                    'code' => $item->code,
                    'name' => $item->asset_name,
                ];
            }
        }
        foreach ($dataSendMail as $tmp) {
            self::pushEmailToQueue($tmp, $template, true);
        }
    }

    public static function getAssetBranch()
    {
        return [
            'hanoi',
            'danang',
            'hcm',
            'japan'
        ];
    }
}

