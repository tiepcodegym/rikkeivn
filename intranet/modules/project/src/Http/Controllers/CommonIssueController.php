<?php

namespace Rikkei\Project\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewLaravel;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Risk;
use Rikkei\Project\Model\SaleProject;
use Rikkei\Project\Model\Task;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TaskNcmRequest;
use Rikkei\Project\Model\TaskRisk;
use Rikkei\Project\View\TaskHelp;
use Rikkei\Project\View\ValidatorExtend;
use Rikkei\Project\View\View as Pview;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\CommonIssue;

class CommonIssueController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }

    public function export()
    {
        $urlFilter = trim(URL::route('project::report.common-issue'), '/') . '/';
        $columns = ['common_issue.*'];
        $filter = Form::getFilterData(null, null, $urlFilter);
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);
        $urlFilter = route('project::report.common-issue') . '/';

        $data = CommonIssue::getAllCommonIssueExport($columns, $conditions);

        if (count($data) > 0) {
            $data = CoreModel::filterGrid($data, [], $urlFilter, 'LIKE');
            $data = $data->get();
        }
        if (!$data) {
            return back()->with('messages', [
                'errors' => [
                    trans('project::view.There are no issue currently ongoing  to now'),
                ]
            ]);
        }
        Excel::create('Danh sách common issue', function ($excel) use ($data) {
            $excel->sheet('sheet1', function ($sheet) use ($data) {
                $sheet->loadView('project::common_issue.include.export_common_issue', [
                    'data' => $data,
                ]);
            });
        })->export('xlsx');
    }

    public function detail($issueId)
    {
        Breadcrumb::add(Lang::get('project::view.Detail'));
        $issueInfo = CommonIssue::getById($issueId);
        if (!$issueInfo) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::message.Issue not found')]]);
        }
        $permissionEdit = true;
        return View('project::common_issue.detail',
            [
                'issueInfo' => $issueInfo,
                'permissionEdit' => $permissionEdit,
            ]);
    }

    /**
     * Form edit risk
     *
     * @param Request $request
     */
    public function editCommonIssue(Request $request)
    {
        $data = $request->all();
        $view = 'project::components.common_issue_detail';
        $permissionEdit = true;
        if (isset($data['isCreateNew'])) {
            $issueInfo = CommonIssue::getById($data['id']);
            $permissionEdit = false;
        } else {
            $issueInfo = null;
        }
        return View($view,
            [
                'issueInfo' => $issueInfo,
                'permissionEdit' => $permissionEdit,
                'redirectUrl' => $request->get('redirectUrl'),
            ]
        );
    }

    public function listView()
    {
        Breadcrumb::add(Lang::get('project::view.List'));
        $route = 'project::commonIssue.detail';
        $checkPermissionEdit = Permission::getInstance()->isAllow($route);

        $columns = [
            'common_issue.*',
        ];
        $pager = Config::getPagerData(null, ['order' => 'id', 'dir' => 'desc']);
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $conditions = [];
        $conditions = array_merge($dataFilter, $conditions);

        $typeIssue = Task::typeLabelForIssue();

        $list = CommonIssue::getAllCommonIssue($columns, $conditions, $pager['order'], $pager['dir']);
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);
        }
        return View('project::common_issue.list', [
            'collectionModel' => $list,
            'typeIssue' => $typeIssue,
            'permissionEdit' => $checkPermissionEdit,
        ]);
    }

    public function saveCommonIssue(Request $request)
    {
        $data = $request->all();
        $commonIssue = CommonIssue::store($data);
        if (!empty($commonIssue)) {
            $messages = [
                'success' => [
                    Lang::get('project::message.MES_SAVE_COMMON_ISSUE_SUCCESS'),
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
        if (isset($data['redirectDetailIssue'])) {
            return redirect()->to($data['redirectDetailIssue'])->with('messages', $messages);
        }
        return redirect()->route('project::commonIssue.detail', $commonIssue->id)->with('messages', $messages);
    }

    public function delete(Request $request)
    {
        $issue = CommonIssue::deleteCommonIssue($request);
        return response()->json($issue);
    }
}
