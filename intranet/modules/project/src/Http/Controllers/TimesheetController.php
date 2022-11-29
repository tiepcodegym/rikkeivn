<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Contracts\Validation\UnauthorizedException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Http\Requests\CreateTimesheetRequest;
use Rikkei\Project\Model\Timesheet;
use Rikkei\Project\View\TimesheetHelper;

class TimesheetController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        Breadcrumb::add(trans('project::timesheet.project'));
    }

    /**
     * Get list Timesheet
     * @param Request $request
     * @return Factory|View
     */
    public function index(Request $request)
    {
        Breadcrumb::add(trans('project::timesheet.timesheet'));
        $projects = TimesheetHelper::instance()->getListProject();
        $collectionModel = Timesheet::instance()->getListTimeSheet($projects);
        $status = Timesheet::getStatus();

        return view('project::timesheets.index', compact('collectionModel', 'status', 'projects'));
    }

    public function create(Request $request)
    {
        Breadcrumb::add(trans('project::timesheet.timesheet'), route('project::timesheets.index'));
        Breadcrumb::add(trans('project::timesheet.create_timesheet'));

        // Get danh sách dự án user đang quản lý
        $projects = TimesheetHelper::instance()->getListProject();
        $status = Timesheet::getStatus();

        return view('project::timesheets.create', compact('projects', 'status'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $request->session()->forget('timesheet');
        // validate data
        $validator = $this->validateParam($data);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            \Session::flash('messages', ['errors' => $errors] );
            $request->session()->put('timesheet', $data);

            return redirect()->back()->withInput(array_merge($data, ['has_data' => 1]));
        }

        try {
            $service = TimesheetHelper::instance();
            $service->checkAccess($request->project_id);

            $data = $request->except('_token');
            $service->storeTimesheet($data);
            \Session::flash('messages', ['success'=> [trans('project::timesheet.create_success')]]);
        } catch (\Exception $e) {
            \Session::flash('messages', ['errors' => [trans('project::timesheet.create_error')]] );
        } catch (UnauthorizedException $e) {
            \Session::flash('messages', ['errors' => [$e->getMessage()]] );
        }

        return redirect(route('project::timesheets.index'));
    }

    /**
     * Edit Timesheet
     *
     * @param Request $request
     * @param $id
     * @return Factory|View
     */
    public function edit(Request $request, $id)
    {
        Breadcrumb::add(trans('project::timesheet.timesheet'), route('project::timesheets.index'));
        Breadcrumb::add(trans('project::timesheet.edit_timesheet'));

        try {
            $timesheet = Timesheet::find($id);
            $timesheetHelper = TimesheetHelper::instance();

            if(!$timesheet) {
                throw new \Exception(trans('project::timesheet.timesheet_not_found'));
            }

            // Get danh sách dự án user đang quản lý
            $projects = $timesheetHelper->getListProject();

            // Check access edit timesheets
            $timesheetHelper->checkAccess($timesheet->project_id, $projects);

            //get PO list
            $po = $timesheetHelper->apiGetPoByProjectId($timesheet->project_id);

            $poList = $period =[];
            foreach ($po['data'] as $poItem) {
                if($timesheet->po_id == $poItem['id']) {
                    $poList[$poItem['id']] = $poItem['name'];
                }

                if ($timesheet->po_id == $poItem['id']) {
                    foreach ($poItem['period'] as $item) {
                        $period[$item['start'] . '->' . $item['end']] = $item['start'] . ' -> ' . $item['end'];
                    }
                }
            }

            $periodSelected = $timesheet->start_date.'->'.$timesheet->end_date;
            $status = Timesheet::getStatus();

            return view('project::timesheets.edit', compact('projects', 'status', 'timesheet', 'poList', 'period', 'periodSelected'));
        } catch (\Exception $e) {
            \Log::error($e);
            \Session::flash('messages', ['errors'=> [$e->getMessage()]]);

            return redirect(route('project::timesheets.index'));
        }
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        $data = $request->except('_token');
        $request->session()->forget('timesheet');
        // validate data
        $validator = $this->validateParam($data);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            \Session::flash('messages', ['errors' => $errors] );
            $request->session()->put('timesheet', $data);

            return redirect()->back();
        }

        try {
            $timesheetHelper = TimesheetHelper::instance();
            $timesheetHelper->checkAccess($data['project_id']);

            $timesheetHelper->updateTimesheet($timesheet, $data);
            \Session::flash('messages', ['success'=> [trans('project::timesheet.update_success')]]);

        } catch (UnauthorizedException $e) {
            \Session::flash('messages', ['errors' => [$e->getMessage()]] );
        } catch (\Exception $e) {
            \Session::flash('messages', ['errors'=> [trans('project::timesheet.update_error')]]);
        }

        return redirect(route('project::timesheets.index'));
    }

    public function destroy(Request $request, Timesheet $timesheet)
    {
        try {
            TimesheetHelper::instance()->delete($timesheet);
            \Session::flash('messages', ['success'=> [trans('project::timesheet.delete_success')]]);
        } catch (\Exception $e) {
            \Session::flash('messages', ['errors'=> [$e->getMessage()]]);
        }

        return redirect(route('project::timesheets.index'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPO(Request $request)
    {
        try {
            $projectId = $request->project_id;
            $po = TimesheetHelper::instance()->apiGetPoByProjectId($projectId);

            return response()->json([
                'status' => 'success',
                'data' => $po['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function getLineItem(Request $request)
    {
        try {
            $lineItems = TimesheetHelper::instance()->apiGetLineItem($request->all());
            $request->session()->forget('timesheet');
            return response()->json([
                'status' => 'success',
                'html' => $lineItems,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function syncTimesheet(Request $request)
    {
        try {
            $data = TimesheetHelper::instance()->syncTimesheet($request->all());
            return response()->json([
                'status' => 'success',
                'line_item_id' => $request->line_item_id,
                'team_id' => $data['team_id'],
                'html' => $data['html'],
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'line_item_id' => $request->line_item_id,
                'message' => $e->getMessage(),
            ]);
        }

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function reloadPeriod(Request $request)
    {
        try {
            $lineItems = TimesheetHelper::instance()->apiGetLineItem($request->all());
            return response()->json([
                'status' => 'success',
                'html' => $lineItems,
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate data
     *
     * @param $request
     * @return mixed
     */
    private function validateParam($request)
    {
        return Validator::make($request, [
            'line_item.*.details.*.working_hour' => 'numeric|max:24',
            'line_item.*.details.*.ot_hour' => 'numeric|max:24',
            'line_item.*.details.*.overnight_hour' => 'numeric|max:24',
            'line_item.*.details.*.note' => 'string|max:255',
            'status' => 'required|integer|min:1|max:2'
        ]);
    }
}
