<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Model\CommonRisk;
use Lang;
use Rikkei\Project\View\View as Pview;
use Rikkei\Team\View\Config;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Illuminate\Http\Request;
use DB;

class CommonRiskController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('Report statistic');
        Breadcrumb::add(Lang::get('project::view.Report'));
        Breadcrumb::add(Lang::get('project::view.Risk'), route('project::report.common-risk'));
    }

    /**
     * Risk list page
     */
    public function listView()
    {
        Breadcrumb::add(Lang::get('project::view.List'));

        $route = 'project::commonRisk.detail';
        $checkPermissionEdit = Permission::getInstance()->isAllow($route);

        $pager = Config::getPagerData(null, ['order' => 'common_risk.id', 'dir' => 'desc']);
        $columns = [
            'common_risk.*',
        ];
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);

        $list = CommonRisk::getCommonRisks($columns, $conditions, $pager['order'], $pager['dir']);

        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }

        return View('project::common_risk.list', [
            'collectionModel' => $list,
            'permissionEdit' => $checkPermissionEdit,
        ]);
    }

    /**
     * Risk detail page
     *
     * @param int $riskId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function detail($riskId)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $commonRiskInfo = CommonRisk::getById($riskId);
        if (!$commonRiskInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Risk not found')]]);
        }
        return View('project::common_risk.detail',
            [
                'riskInfo' => $commonRiskInfo,
                'permissionEdit' => true,
            ]);
    }

    /**
     * Form edit risk
     *
     * @param Request $request
     */
    public function editCommonRisk(Request $request)
    {
        $data = $request->all();
        $permissionEdit = true;
        if (isset($data['isCreateNew'])) {
            $riskInfo = CommonRisk::getById($data['id']);
            $permissionEdit = false;
        } else {
            $riskInfo = null;
        }
        return View('project::components.common_risk_detail',
            [
                'riskInfo' => $riskInfo,
                'permissionEdit' => $permissionEdit,
                'redirectUrl' => $request->get('redirectUrl'),
            ]
        );
    }

    /**
     * Save common risk action
     *
     * @param Request $request
     */
    public function saveCommonRisk(Request $request)
    {
        $data = $request->all();
        $commonRisk = CommonRisk::store($data);
        if (!empty($commonRisk)) {
            $messages = [
                'success' => [
                    \Illuminate\Support\Facades\Lang::get('project::message.MES_SAVE_COMMON_RISK_SUCCESS'),
                ]
            ];
        } else {
            $messages = [
                'success' => [
                    Lang::get('project::message.Save risk error'),
                ]
            ];
        }
        if ($redirectUrl = $request->get('redirectUrl')) {
            return redirect()->to($redirectUrl)->with('messages', $messages);
        }
        if (isset($data['redirectDetailRisk'])) {
            return redirect()->to($data['redirectDetailRisk'])->with('messages', $messages);
        }
        return redirect()->route('project::commonRisk.detail', $commonRisk->id)->with('messages', $messages);
    }

    public function export()
    {
        $urlFilter = trim(URL::route('project::report.common-risk'), '/') . '/';
        $columns = ['common_risk.*'];
        $filter = Form::getFilterData(null, null, $urlFilter);
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $urlFilter = route('project::report.common-risk') . '/';
        $route = 'project::report.common-risk';

        $dataRisk = CommonRisk::getAllCommonRiskExport($columns, $conditions);
        if (count($dataRisk) > 0) {
            $dataRisk = CoreModel::filterGrid($dataRisk, [], $urlFilter, 'LIKE');
            $dataRisk = $dataRisk->get();
        }
        if (!$dataRisk) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.There are no risk currently ongoing  to now'),
                ]
            ]);
        }
        Excel::create('Danh sách common risk', function ($excel) use ($dataRisk) {
            $excel->sheet('sheet1', function ($sheet) use ($dataRisk) {
                $sheet->loadView('project::common_risk.include.export_common_risk', [
                    'data' => $dataRisk
                ]);
            });
        })->export('xlsx');
    }

    public function delete(Request $request)
    {
        $issue = CommonRisk::deleteCommonRisk($request);
        return response()->json($issue);
    }
}
