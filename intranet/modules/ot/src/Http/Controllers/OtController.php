<?php

namespace Rikkei\Ot\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Ot\View\OtPermission;
use Rikkei\Team\View\Permission;

class OtController extends Controller
{
    /**
     * get add register ot approved
     * @param int $id [id team]
     */
    public function reportOTApproved($id = null)
    {
        if (!OtPermission::isAllowReportOt())
        {
            return View::viewErrorPermission();
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $dataFilter["status"] = OtRegister::DONE;

        $teamIdsAvailable = true;
        $teamTreeAvailable = [];
        $optionStatus = [];
        $collectionModel = OtRegister::getListManageRegisters($userCurrent->id, $id, $dataFilter);

        $params = [
            'collectionModel' => $collectionModel,
            'optionStatus' => $optionStatus,
            'pageType' => 'company_list',
            'teamIdCurrent'     => $id,
            'teamIdsAvailable'  => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
            'reportOT' => true,
        ];
        return view('ot::ot.manage', $params);
    }
}