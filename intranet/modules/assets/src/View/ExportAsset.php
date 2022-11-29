<?php

namespace Rikkei\Assets\View;

use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Assets\Model\AssetOrigin;
use Illuminate\Support\Facades\DB;

class ExportAsset
{

    /*
     * define columns export
     */
    public static function columnsExport($options = [])
    {
        $assetTbl = AssetItem::getTableName();
        $categoryTbl = AssetCategory::getTableName();
        $warehouseTbl = AssetWarehouse::getTableName();
        $employeeTbl = Employee::getTableName();
        $assetOriginTable = AssetOrigin::getTableName();
        $roleTbl = Role::getTableName();
        return [
            $assetTbl . '.code' =>  ['tt' => trans('asset::export.Asset code (*)'), 'df' => 1],
            $assetTbl . '.name' =>  ['tt' => trans('asset::export.Asset name (*)'), 'df' => 1],
            $categoryTbl. '.name' => ['tt' => trans('asset::export.Asset category (*)'), 'df' => 1],
            $assetTbl . '.serial' =>  ['tt' => trans('asset::export.Number Seri')],
            $assetTbl . '.specification' =>  ['tt' => trans('asset::export.Specification')],
            $warehouseTbl . '.code' =>  ['tt' => trans('asset::export.Asset code warehouse (*)'), 'df' => 1],
            $assetTbl . '.supplier_id' =>  ['tt' => trans('asset::export.Supplier Id')],
            $assetTbl . '.purchase_date' =>  ['tt' => trans('asset::export.Purchase Date')],
            $assetTbl . '.warranty_priod' =>  ['tt' => trans('asset::export.Warranty Priod')],
            'team_unit.name' =>  ['tt' => trans('asset::export.Team Id')],
            $assetTbl . '.state' =>  ['tt' => trans('asset::export.Status (*)'),'sl_fc' => 'labelState', 'df' => 1],
            'tbl_employee_use.email' =>  ['tt' => trans('asset::export.User account'), 'df' => 1],
            'tbl_employee_use.employee_code' =>  ['tt' => trans('asset::export.Employee code'), 'df' => 1],
            'tbl_employee_use.name' =>  ['tt' => trans('asset::export.Asset name user')],
            'tbl_position'. '.role_name' =>  ['tt' => trans('asset::export.Division - Position')],
            $assetTbl . '.received_date' =>  ['tt' => trans('asset::export.Received date')],
            $assetTbl . '.note' =>  ['tt' => trans('asset::export.Note')],
            'tbl_employee_manage.email' =>  ['tt' => trans('asset::export.Manage asset account'), 'df' => 1],
            'tbl_employee_manage.name' =>  ['tt' => trans('asset::export.Manage asset person')],
            $assetOriginTable . '.name' => ['tt' => trans('asset::export.Origin')],
            $assetTbl . '.change_date' =>  ['tt' => trans('asset::export.Change Date')],
            // $warehouseTbl . '.name' =>  ['tt' => trans('asset::export.Address warehouse')],
            // 'tbl_employee_manage' . '.employee_code' =>  ['tt' => trans('asset::export.Manage asset person code')],
            // 'tbl_position'. '.role_name' =>  ['tt' => trans('asset::export.Position')],
             $assetTbl . '.allocation_confirm' =>  ['tt' => trans('asset::export.Had allocation'),'sl_fc' => 'labelConfirm'],
        ];
    }

    /*
     * get list columns heading
     */
    public static function getColsHeading($columns)
    {
        $results = [];
        $columnsExport = self::columnsExport();
        foreach ($columns as $key) {
            if (!isset($columnsExport[$key])) {
                continue;
            }
            $arrKeys = explode('.', $key);
            $results[implode('_', $arrKeys)] = [
                'tt' => $columnsExport[$key]['tt'],
                'wch' => isset($columnsExport[$key]['wch']) ? $columnsExport[$key]['wch'] : strlen($columnsExport[$key]['tt'])
            ];
        }
        $results['don_vi_cong_tac'] = ['tt' => trans('asset::export.Work unit'),'wch' => 21];
        $results['vi_tri_dia_ly'] = ['tt' => trans('asset::export.Geographical location'),'wch' => 22];
        $results['thoi_gian_hieu_luc'] = ['tt' => trans('asset::export.Effective time'),'wch' => 23];
        return $results;
    }

    /*
     * filter select post data columns
     */
    public static function filterSelectCols($columns)
    {
        $results = [];
        $colsExport = self::columnsExport();
        foreach ($columns as $col) {
            if (!isset($colsExport[$col])) {
                continue;
            }
            $arrKeys = explode('.', $col);
            if (isset($colsExport[$col]['sl'])) {
                $results[] = DB::raw($colsExport[$col]['sl'] . ' AS ' . implode('_', $arrKeys));
            } elseif (isset($colsExport[$col]['sl_fc'])) {
                $results[] = DB::raw(call_user_func(__NAMESPACE__.'\ExportAsset::'.$colsExport[$col]['sl_fc'], $col) . ' AS ' . implode('_', $arrKeys));
            } else {
                $results[] = $col . ' AS ' . implode('_', $arrKeys);
            }
        }
        $results[] = DB::raw('"" AS `don_vi_cong_tac`, "" AS `vi_tri_dia_ly`, "" AS `thoi_gian_hieu_luc`');
        return $results;
    }

    /*
     * get data to export excel
     */
    public static function getDataExport($data)
    {
        $assetTbl = AssetItem::getTableName();
        $categoryTbl = AssetCategory::getTableName();
        $warehouseTbl = AssetWarehouse::getTableName();
        $employeeTbl = Employee::getTableName();
        $employeeTblAsManage = 'tbl_employee_manage';
        $employeeTblAsUse = 'tbl_employee_use';
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $assetOriginTable = AssetOrigin::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $tblPosition = 'tbl_position';
        $collection = AssetItem::select(self::filterSelectCols($data['columns']))
            ->leftJoin("{$categoryTbl}", "{$categoryTbl}".'.id', '=', "{$assetTbl}".'.category_id')
            ->leftJoin("{$warehouseTbl}", "{$warehouseTbl}".'.id', '=', "{$assetTbl}".'.warehouse_id')
            ->leftJoin("{$assetOriginTable}", "{$assetTbl}".'.origin_id', '=', "{$assetOriginTable}".'.id')
            ->leftJoin("{$employeeTbl} AS {$employeeTblAsManage}", "{$employeeTblAsManage}".'.id', '=', "{$assetTbl}".'.manager_id')
            ->leftJoin("{$employeeTbl} AS {$employeeTblAsUse}", "{$employeeTblAsUse}".'.id', '=', "{$assetTbl}".'.employee_id')
            ->leftJoin(DB::raw("(
                SELECT {$teamMemberTable}.employee_id as employee_id,
                    GROUP_CONCAT(DISTINCT CONCAT({$roleTable}.role, ' - ', {$teamTable}.name) ORDER BY {$roleTable}.role DESC SEPARATOR '; ') as role_name
                FROM {$teamMemberTable}
                JOIN {$employeeTbl} ON {$teamMemberTable}.employee_id = {$employeeTbl}.id
                JOIN {$teamTable} ON {$teamMemberTable}.team_id = {$teamTable}.id
                JOIN {$roleTable} ON {$teamMemberTable}.role_id = {$roleTable}.id
                GROUP BY {$employeeTbl}.id) AS {$tblPosition}"),
                "{$tblPosition}.employee_id", '=', "{$assetTbl}.employee_id")
            ->leftJoin("{$teamTable} as team_unit", "team_unit.id", '=', "{$assetTbl}.team_id");
        $exportAll = true;
        if (isset($data['export_all'])) {
           $exportAll = $data['export_all'];
           $options = Form::getFilterData('filter', null, route('asset::asset.index') . '/');
            if (!empty($options['asset_name'])) {
                $collection->where("$assetTbl.name", 'Like', '%'.trim($options['asset_name']).'%');
            }
            if (!empty($options['asset_code'])) {
                $collection->where("$assetTbl.code", 'Like', '%'.trim($options['asset_code']).'%');
            }
            if (!empty($options['category_name'])) {
                $collection->where('category_id', $options['category_name']);
            }
            if (!empty($options['warehouse_name'])) {
                $collection->where('warehouse_id', $options['warehouse_name']);
            }
            if (!empty($options['manager_name'])) {
                $collection->where("$employeeTblAsManage.name", 'Like', '%'.trim($options['manager_name']).'%')
                    ->orWhere("$employeeTblAsManage.email", 'Like', '%'.trim($options['manager_name']).'%');
            }
            if (!empty($options['user_name'])) {
                $collection->where(function ($query) use ($employeeTblAsUse, $options) {
                    $query->where("$employeeTblAsUse.name", 'Like', '%'.trim($options['user_name']).'%')
                           ->orWhere("$employeeTblAsUse.email", 'Like', '%'.trim($options['user_name']).'%');
                });
            }
            if (is_numeric($options['state'])) {
                $collection->where("$assetTbl.state", $options['state']);
            }
            if (is_numeric($options['allocation_confirm'])) {
                $collection->where('allocation_confirm', $options['allocation_confirm']);
            }
        }
        if (!$exportAll) {
            $itemIds = isset($data['itemsChecked']) ? $data['itemsChecked'] : '';
            $collection->whereIn($assetTbl . '.id', explode(',', $itemIds));
        }
        $collection = $collection->get();
        foreach ($collection as $key => $value) {
            if ($value->tbl_employee_manage_email != null) {
               $collection[$key]->tbl_employee_manage_email = substr($value->tbl_employee_manage_email, 0, strpos($value->tbl_employee_manage_email, '@'));
            }
            if ($value->tbl_employee_use_email != null) {
               $collection[$key]->tbl_employee_use_email = substr($value->tbl_employee_use_email, 0, strpos($value->tbl_employee_use_email, '@'));
            }
        }
        return $collection;
    }

    /*
     * change view label state
     */
    public static function labelState($col = null)
    {
        if (!$col) {
            $col = AssetItem::getTableName() . '.state';
        }
        $listState = AssetItem::labelStates();
        return self::selectCase($col, $listState);
    }

    /*
     * change view label confirm
     */
    public static function labelConfirm($col = null)
    {
        if (!$col) {
            $col = AssetItem::getTableName() . '.allocation_confirm';
        }
        $labelConfirm = AssetItem::labelAllocationConfirm();
        return self::selectCase($col, $labelConfirm);
    }

    /*
     * sql switch case by array
     */
    public static function selectCase($col, $list)
    {
        $sql = 'CASE ';
        foreach ($list as $key => $label) {
            $sql .= 'WHEN ' . $col . ' = "' . $key . '" THEN "' . $label .'" ';
        }
        return $sql . ' END';
    }
}
