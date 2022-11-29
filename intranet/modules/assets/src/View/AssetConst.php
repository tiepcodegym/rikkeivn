<?php

namespace Rikkei\Assets\View;

use Rikkei\Team\View\TeamConst;

use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

class AssetConst
{
    const MODAL_ALLOCATION = 1;
    const MODAL_RETRIEVAL = 2;
    const MODAL_LOST_NOTIFICATION = 3;
    const MODAL_BROKEN_NOTIFICATION = 4;
    const MODAL_SUGGEST_LIQUIDATE = 5;
    const MODAL_SUGGEST_REPAIR_MAINTENACE = 6;
    const MODAL_RETURN_CUSTOMER = 7;

    const REPORT_TYPE_LOST_AND_BROKEN = 1;
    const REPORT_TYPE_DETAIL_BY_EMPLOYEE = 2;
    const REPORT_TYPE_DETAIL_ON_ASSET_USE_PROCESS = 3;

    const PRINT_REPORT_BY_EMPLOYEE = 1;

    const DISAPPROVE_REQUEST = 0;
    const APPROVE_REQUEST = 1;

    const MAX_CHARACTER_QUANTITY_ASSET_REQUEST = 5;

    const KEY_SUBMIT_REPORT_MANAGE_ASSET = 'key_data_submit_report_manage_asset';

    const INV_RS_NOT_YET = 1;
    const INV_RS_ENOUGH = 2;
    const INV_RS_NOT_ENOUGH = 3;
    const INV_RS_EXCESS = 4;

    const INV_STT_OPEN = 1;
    const INV_STT_CLOSE = 2;

    const ASSET_CONFIRM = 1;
    const ASSET_NOT_CONFIRM = 0;

    const PREFIX_HN = 'HN';
    const PREFIX_DN = 'DN';
    const PREFIX_JP = 'JP';
    const PREFIX_HCM = 'SG';
    const PREFIX_AI = 'AI';
    const PREFIX_RS = 'RS';
    const PREFIX_ALL = '_ALL';

    /**
     * Const profile asset
     */

    const TYPE_HANDING_OVER = 1;
    const TYPE_LOST_NOTIFY = 2;
    const TYPE_BROKEN_NOTIFY = 3;
    const TYPE_APPROVALS = 4;

    const STT_RP_PROCESSING = 1;
    const STT_RP_CONFIRMED = 2;
    const STT_RP_REJECTED = 3;
    const STT_RP_APPROVALS = 4;

    //key config database alert out of date days before
    const KEY_DB_DAYS_ALERT_OOD = 'asset_out_of_date_alert_days_before';

    /**
     * to nested list check box
     */
    public static function toNestedCheckbox(
        $collection,
        $checked = [],
        $name = 'team_ids[]',
        $parent = 1,
        $depth = 0
    )
    {
        if ($collection->isEmpty()) {
            return '';
        }
        $html = '';
        $indent = str_repeat("&nbsp;", $depth * 8);
        foreach ($collection as $item) {
            if ($item->parent_id == $parent) {
                $html .= '<li class="parent-'. $parent .'" data-parent="'.$parent.'" data-id="'. $item->id .'" data-depth="'. $depth .'">'
                        . $indent . '<label>'
                        . '<input type="checkbox" name="'. $name .'" value="'. $item->id .'"'
                        . (in_array($item->id, $checked) ? ' checked' : '') .'> '
                        . e($item->name)
                        . '</label>'
                        . '</li>';
                $html .= self::toNestedCheckbox($collection, $checked, $name, $item->id, $depth + 1);
            }
        }
        return $html;
    }

    public static function listInventoryStatus()
    {
        return [
            self::INV_RS_NOT_YET => trans('asset::view.Not done'),
            self::INV_RS_ENOUGH => trans('asset::view.True'),
            self::INV_RS_NOT_ENOUGH => trans('asset::view.False'),
            self::INV_RS_EXCESS => trans('asset::view.False')
        ];
    }

    public static function listInventoryState()
    {
        return [
            self::INV_STT_OPEN => trans('asset::view.Open'),
            self::INV_STT_CLOSE => trans('asset::view.Close')
        ];
    }

    /*
     * get asset prefix by team code
     */
    public static function getAssetPrefixByCode($teamCode)
    {
        if (!$teamCode) {
            return null;
        }
        $teamCode = explode('_', $teamCode)[0];
        switch ($teamCode) {
            case TeamConst::CODE_HANOI:
                return self::PREFIX_HN;
            case TeamConst::CODE_DANANG:
                return self::PREFIX_DN;
            case TeamConst::CODE_JAPAN:
                return self::PREFIX_JP;
            case TeamConst::CODE_HCM:
                return self::PREFIX_HCM;
            case TeamConst::CODE_AI:
                return self::PREFIX_AI;
            case TeamConst::CODE_RS:
                return self::PREFIX_RS;
            default:
                return self::PREFIX_HN;
        }
    }

    public static function listPrefix()
    {
        return [
            self::PREFIX_HN,
            self::PREFIX_DN,
            self::PREFIX_JP,
            self::PREFIX_HCM,
            self::PREFIX_AI,
            self::PREFIX_RS,
        ];
    }

    public static function selectCasePrefix($col)
    {
        $teamCodes = [
            self::PREFIX_HN => TeamConst::CODE_HANOI,
            self::PREFIX_DN => TeamConst::CODE_DANANG,
            self::PREFIX_JP => TeamConst::CODE_JAPAN,
            self::PREFIX_HCM => TeamConst::CODE_HCM,
            self::PREFIX_AI => TeamConst::CODE_AI,
            self::PREFIX_RS => TeamConst::CODE_RS,
        ];
        $sql = 'CASE ';
        foreach ($teamCodes as $prefix => $code) {
            $sql .= 'WHEN ' . $col . ' = "' . $code . '" THEN "' . $prefix . '" ';
        }
        return $sql . ' ELSE "'. self::PREFIX_HN .'" END';
    }

    /**
     * @param $subject
     * @param $template
     * @param $data
     * @param null $type
     */
    public static function sendEmailToIT($subject, $template, $data, $type = null)
    {
        $itEmployees = RequestAssetPermission::getEmployeesCanApproveRequest();
        foreach ($itEmployees as $item) {
            $dataEmail = [
                'mail_to' => $item->employee_email,
                'receiver_name' => View::getNickName($item->employee_email),
                'to_id' => $item->employee_id,
                'mail_title' => $subject,
                'href' => $data['href'],
            ];
            AssetView::pushEmailToQueue($dataEmail, $template, true);
        }

    }

    /*
     * export inventory columns
     */
    public static function exportInventoryCols()
    {
        return [
            'employee_code' => ['tt' => trans('asset::view.Employee code'), 'wch' => 15],
            'name' => ['tt' => trans('asset::view.Fullname'), 'wch' => 20],
            'asset_status' => ['tt' => trans('asset::view.Status'), 'wch' => 20],
            'asset_code' => ['tt' => trans('asset::view.Asset code'), 'wch' => 15],
            'asset_name' => ['tt' => trans('asset::view.Asset name'), 'wch' => 20],
            'asset_type' => ['tt' => trans('asset::view.Asset category'), 'wch' => 20],
            'status' => ['tt' => trans('asset::view.Inventory'), 'wch' => 15],
            'employee_note' => ['tt' => trans('asset::view.Note'), 'wch' => 30],
            'note' => ['tt' => trans('asset::view.Addtional'), 'wch' => 30],
            'team_names' => ['tt' => trans('asset::view.Department'), 'wch' => 20],
        ];
    }

    /*
     * select case
     */
    public static function selectCase($col, $list)
    {
        $sql = 'CASE ';
        foreach ($list as $key => $label) {
            $sql .= 'WHEN ' . $col . ' = "' . $key . '" THEN "' . $label .'" ';
        }
        return $sql .' END';
    }

    /*
     * check has asset by str count asset
     */
    public static function hasCatAsset($strQty, $strCount)
    {
        if (!$strCount) {
            return false;
        }
        $arrCount = explode(',', $strCount);
        $arrCountAsset = [];
        foreach ($arrCount as $strCat) {
            $arrCat = explode('-', $strCat);
            if (!$arrCat) {
                continue;
            }
            if (!isset($arrCountAsset[$arrCat[0]])) {
                $arrCountAsset[$arrCat[0]] = 0;
            }
            $arrCountAsset[$arrCat[0]] += 1;
        }

        if (!$strQty) {
            return true;
        }
        $arrQty = explode(',', $strQty);
        foreach ($arrQty as $strCatQty) {
            $arrCatQty = explode('-', $strCatQty);
            if (!$arrCatQty) {
                return true;
            }
            $numAsset = 0;
            if (isset($arrCountAsset[$arrCatQty[0]])) {
                $numAsset = $arrCountAsset[$arrCatQty[0]];
            }
            if ($numAsset < $arrCatQty[1]) {
                return false;
            }
        }
        return true;
    }

    /*
     * list asset actions label
     */
    public static function assetActionsList()
    {
        return [
            self::TYPE_HANDING_OVER => trans('asset::view.Asset handover'),
            self::TYPE_BROKEN_NOTIFY => trans('asset::view.Asset broken notification'),
            self::TYPE_LOST_NOTIFY => trans('asset::view.Asset lost notification')
        ];
    }

    public static function listStatusReport()
    {
        return [
            self::STT_RP_PROCESSING => trans('asset::view.Processing'),
            self::STT_RP_REJECTED => trans('asset::view.Confirm reject'),
            self::STT_RP_CONFIRMED => trans('asset::view.Confirmed'),
            self::STT_RP_APPROVALS => trans('asset::view.Approved'),
        ];
    }

    /**
     * get action label
     * @param type $action
     * @param type $lists
     * @return type
     */
    public static function getAssetActionLabel($action, $lists = null)
    {
        if (!$lists) {
            $lists = self::assetActionsList();
        }
        if (isset($lists[$action])) {
            return $lists[$action];
        }
        return null;
    }

    /**
     * Get label by type
     *
     * @param int $type
     * @return string
     */
    public static function getLabelByType($type)
    {
        switch ($type) {
            case self::TYPE_HANDING_OVER:
                return trans('asset::view.Asset handover');
            case self::TYPE_BROKEN_NOTIFY:
                return trans('asset::view.Asset broken notification');
            case self::TYPE_LOST_NOTIFY:
                return trans('asset::view.Asset lost notification');
        }

    }

    /**
     * get config days before alert asset out of date
     *
     * @return array
     */
    public static function getConfigDaysOOD()
    {
        $config = CoreConfigData::getValueDb(self::KEY_DB_DAYS_ALERT_OOD);
        $branchs = Team::listPrefixBranch();
        $config = $config ? unserialize($config) : [];
        $results = [];
        foreach ($branchs as $code => $name) {
            $results[$code] = isset($config[$code]) ? $config[$code] : 0;
        }
        return $results;
    }

    /**
     * convert asset prefix code to branch code, ex: HN -> hanoi 
     *
     * @return array
     */
    public static function convertAssetPrefixToBranch()
    {
        return [
            self::PREFIX_HN => TeamConst::CODE_HANOI,
            self::PREFIX_AI => TeamConst::CODE_HANOI,
            self::PREFIX_DN => TeamConst::CODE_DANANG,
            self::PREFIX_HCM => TeamConst::CODE_HCM,
            self::PREFIX_RS => TeamConst::CODE_HCM,
            self::PREFIX_JP => TeamConst::CODE_JAPAN,
        ];
    }
}
