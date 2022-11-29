<?php

namespace Rikkei\Me\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Me\Model\ME;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Me\Model\Attribute;
use Rikkei\Me\Model\Point as MePoint;
use Rikkei\Me\Model\Comment as MeComment;
use Carbon\Carbon;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Validator;

class MeTeamController extends Controller
{
    public function __construct() {
        parent::__construct();
        Breadcrumb::add(trans('me::view.Monthly Evaluation'), route('me::team.edit'));
        Menu::setActive('team');
    }

    /*
     * render edit page
     */
    public function edit()
    {
        Breadcrumb::add(trans('me::view.Create'));

        //list months
        $selectMonths = [];
        $endMonth = Carbon::now()->startOfMonth();
        $startMonth = clone $endMonth;
        $startMonth->subMonthNoOverflow()->startOfMonth();
        while ($startMonth->lte($endMonth)) {
            array_unshift($selectMonths, ['string' => $startMonth->format('Y-m'), 'timestamp' => $startMonth->format('Y-m')]);
            $startMonth->addMonth();
        }
        return view('me::team.edit', [
            'evalTeamList' => ME::getInstance()->getTeamPermissOptions('me::team.edit'),
            'listEvalTeamMonths' => $selectMonths,
        ]);
    }

    /*
     * get member of team evaluation
     */
    public function getMembers(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'team_id' => 'required',
            'month' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $getFields = $request->get('fields');
        $month = $request->get('month');
        $teamId = $request->get('team_id');
        $team = Team::find($teamId);
        if (!$team) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $leader = Permission::getInstance()->getEmployee();
        $leaderId = $leader->id;

        $dataMembers = ME::getInstance()->getMembersOfEvalTeam($teamId, $month, []);
        $members = $dataMembers['members'];
        $rangeTime = $dataMembers['range_time'];

        $response = [
            'range_time' => array_map(function ($element) {
                return $element->format('Y-m-d');
            }, $rangeTime)
        ];
        if (isset($getFields['attributes']) && $getFields['attributes']) {
            $response['attributes'] = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_PERFORM, Attribute::GR_NEW_NORMAL]);
        }

        if (count($members) < 1) {
            return response()->json([
                'message' => trans('me::view.No result'),
                'status' => 0
            ], 404);
        }

        $items = ME::getInstance()->insertOrUpdateEvalTeam($members, $team, $month);
        if (!$items) {
            return response()->json(trans('me::view.An error occurred'), 500);
        }

        $evalIds = collect($items)->pluck('id')->toArray();
        $response['items'] = $items;
        $response['attrPoints'] = MePoint::getInstance()->getPointByEvalIds($evalIds);
        $response['commentClasses'] = MeComment::getInstance()->getEvalCommentClass($evalIds);
        $response['attrsCommented'] = MeComment::getInstance()->listAttrsCommented($evalIds);
        $response['leaderId'] = $leader->id;

        return $response;
    }

    /*
     * submit ME team
     */
    public function submit(Request $request)
    {
        $evalIds = $request->get('eval_ids');
        if ($evalIds) {
            $checkRequireComment = ME::getInstance()->checkEvalRequireComment($evalIds);
            if (!$checkRequireComment['check']) {
                return response()->json([
                    'eval_require_comment' => $checkRequireComment['eval_ids']
                ]);
            }
        }
        return (new \Rikkei\Project\Http\Controllers\MeTeamController)->submit($request);
    }
}
