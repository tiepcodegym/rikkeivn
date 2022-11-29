<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\View\HrWeeklyReport;
use Rikkei\Resource\Model\HrWeeklyReportNote;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\View\getOptions;
use Illuminate\Http\Request;
use Rikkei\Team\Model\Permission as TeamPermission;
use Validator;

class HrWeeklyReportController extends Controller
{
    /**
     * list data
     * @return type
     */
    public function index()
    {
        Breadcrumb::add(trans('resource::view.Hr weekly report'), route('resource::hr_wr.index'));
        Menu::setActive('resource');

        $paramWiths = [];
        $collectionModel = HrWeeklyReport::getGridData($paramWiths);
        $programs = Programs::getListOption();
        $programs[-1] = 'others';
        $position = getOptions::getInstance()->getRoles();
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $arrayNotes = HrWeeklyReportNote::getNoteByWeeks($collectionModel);
        return view(
            'resource::hr_weekly_report.index',
            compact(
                'collectionModel',
                'programs',
                'hrAccounts',
                'position',
                'paramWiths',
                'arrayNotes'
            )
        );
    }

    public function saveNote(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'week' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('resource::message.Invalid input data'), 422);
        }
        $result = HrWeeklyReportNote::insertOrUpdate(
            $request->get('week'),
            $request->get('note'),
            $request->get('email')
        );
        if (isset($result['error'])) {
            return response()->json($result['message'], 500);
        }
        return $result;
    }
}

