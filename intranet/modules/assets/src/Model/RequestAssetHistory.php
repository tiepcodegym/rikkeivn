<?php

namespace Rikkei\Assets\Model;

use Lang;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class RequestAssetHistory extends CoreModel
{
    protected $table = 'request_asset_histories';
    protected $fillable = ['request_id', 'employee_id', 'action', 'note'];

    const ACTION_CANCEL = 0;
    const ACTION_CREATE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_REJECT = 3;
    const ACTION_REVIEW = 4;
    const ACTION_APPROVE = 5;
    const ACTION_ALLOCATE = 10;
    const ACTION_ADD_CAT = 11;
    const ACTION_DEL_CAT = 12;
    const ACTION_UPDATE_CAT = 13;

    /**
     * Get asset histories by asset id
     * @param  [int] $requestId
     * @return [array]
     */
    public static function getHistoriesByRequestId($requestId)
    {
        $tblRequestAssetHistory = self::getTableName();
        $tblRequestAsset = RequestAsset::getTableName();
        $tblEmployee = Employee::getTableName();

        return self::select("{$tblRequestAssetHistory}.id", "{$tblEmployee}.name as creator_name", "{$tblEmployee}.email as creator_email", "{$tblRequestAssetHistory}.action", "{$tblRequestAssetHistory}.note", "{$tblRequestAssetHistory}.created_at")
            ->join("{$tblRequestAsset}", "{$tblRequestAsset}.id", "=", "{$tblRequestAssetHistory}.request_id")
            ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblRequestAssetHistory}.employee_id")
            ->where("{$tblRequestAssetHistory}.request_id", $requestId)
            ->orderBy($tblRequestAssetHistory . '.created_at', 'desc')
            ->get();
    }

    /**
     * Show content history
    */
    public static function getContentHistory($action)
    {
        switch ($action) {
            case self::ACTION_CANCEL:
                return Lang::get('asset::view.Changed status to: :status', ['status' => 'Cancel']);
            case self::ACTION_REJECT:
                return Lang::get('asset::view.Changed status to: :status', ['status'=> 'Reject']);
            case self::ACTION_REVIEW:
                return Lang::get('asset::view.Changed status to: :status', ['status'=> 'Reviewed']);
            case self::ACTION_APPROVE:
                return Lang::get('asset::view.Changed status to: :status', ['status'=> 'Approved']);
            case self::ACTION_CREATE:
                return Lang::get('asset::view.Created request asset');
            case self::ACTION_ALLOCATE:
                return Lang::get('asset::view.Allocated asset');
            case self::ACTION_ADD_CAT:
                return Lang::get('asset::view.Update add asset category');
            case self::ACTION_DEL_CAT:
                return Lang::get('asset::view.Update delete asset category');
            case self::ACTION_UPDATE_CAT:
                return Lang::get('asset::view.Update asset category');
            default:
                return Lang::get('asset::view.Updated request asset');
        }
    }
}
