<?php

namespace Rikkei\Proposed\Http\Controllers;

use App;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use Rikkei\Api\Sync\BaseSync;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Jobs\PushNotifyPoint;
use Rikkei\Core\Jobs\PushNotifyToDevices;
use Rikkei\Core\View\View;
use Rikkei\Proposed\Model\Proposed;
use Rikkei\Proposed\View\ProposedPermission;
use Rikkei\Team\View\Permission;
use Rikkei\Notify\Classes\RkNotify;


/**
 * Description of ContactController
 *
 * @author ngochv
 */
class ProposedController extends Controller
{
    /**
     * list proposed category
     * @return [type]
     */
    public function index($id = null)
    {
        if (!ProposedPermission::isAllow()) {
            View::viewErrorPermission();
        }

        $proposed = new Proposed();
        return view('proposed::proposed.manage.list', [
            'collectionModel' => $proposed->index($id),
            'teamIdsAvailable' => $id,
        ]);
    }

    /**
     * edit
     * @param  [type] $id
     * @return [type]
     */
    public function edit($id)
    {
        if (!ProposedPermission::isAllow()) {
            View::viewErrorPermission();
        }

        $proposed = Proposed::find($id);
        if (!$proposed) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }

        if (!empty($proposed->answer_emp_id)) {
            $empAnswer = $proposed->employeesAnswers;
        } else {
            $empAnswer = Permission::getInstance()->getEmployee();
        }

        return view('proposed::proposed.manage.edit', [
            'proposed' => $proposed,
            'empAnswer' => $empAnswer,
        ]);
    }

    /**
     * [update description]
     * @param Request $request
     * @param  [int]  $id
     * @return [type]
     */
    public function update(Request $request, $id)
    {
        if (!ProposedPermission::isAllow()) {
            View::viewErrorPermission();
        }
        $proposedRequest = $request->get('proposed');
        if(isset($proposedRequest['feedback']) && $proposedRequest['feedback'] == Proposed::RESPONDED){
             $validator = Validator::make($request->all(), [
                     'proposed.answer_content' => 'required',
                 ],
                 [
                     'proposed.answer_content.required' => Lang::get('proposed::message.Answer content'),
                 ]
             );
             if ($validator->fails()) {
                 return redirect()->back()->withErrors($validator);
             }
        }

        $proposed = Proposed::find($id);
        if (!$proposed) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        $dataProposed = $request->proposed;
        $dataProposed['updated_by'] = $userCurrent->id;

        if (empty($proposed->answer_content)) {
            $dataProposed['created_at_answer'] = Carbon::now()->format('Y-m-d H:i');
        }
        if (!empty($request->proposed['answer_content']) && $request->proposed['feedback'] == Proposed::NO_RESPONSE_YET) {
            $dataProposed['feedback'] = Proposed::RESPONDED;
        }
        if (!empty($request->get('proposed')['level'])) {
            $level = $request->get('proposed')['level'];
        }
        DB::beginTransaction();
        try {
            $statusOld = $proposed->level;
            $proposed->setData($dataProposed);
            $proposed->save();
            if ($statusOld == Proposed::STATUS_LEVEL_ && !empty($level) && $level != Proposed::STATUS_LEVEL_) {
                $data = [
                    'user_id' => $proposed->created_by,
                    'reward_point_id' => Proposed::PROPOSED_POINT_ID,
                    'level' => $level
                ];
                $url = config('services.point.add');
                $res = BaseSync::callApi($url, 'post', $data)['data'];
                $res['receiver_ids'] = [$proposed->created_by];
                $res['category_id'] = RkNotify::CATEGORY_OTHER;
                switch ($level) {
                    case Proposed::RECORD:
                        $res['content'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_5_POINT]);
                        $res['content_en'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_5_POINT], 'en');
                        break;
                    case Proposed::USEFUL:
                        $res['content'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_10_POINT]);
                        $res['content_en'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_10_POINT], 'en');
                        break;
                    case Proposed::VERRY_HELPFUL:
                        $res['content'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_20_POINT]);
                        $res['content_en'] = Lang::get('proposed::view.Add proposed point', ['points' => Proposed::PROPOSE_20_POINT], 'en');
                        break;
                    default:
                        break;
                }
                dispatch((new PushNotifyPoint($res)));
            }
            DB::commit();
            return redirect()->back()->with('flash_success', Lang::get('proposed::message.Update success'));
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * [delete description]
     * @param  [int] $id
     * @return [type]
     */
    public function delete($id)
    {
        if (!ProposedPermission::isAllow()) {
            View::viewErrorPermission();
        }

        $proposed = Proposed::find($id);
        if (!$proposed) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }

        $userCurrent = Permission::getInstance()->getEmployee();
        // if (!empty($proposed->updated_by) && $userCurrent->id != $proposed->updated_by) {
        //     return redirect()->back()->withErrors(Lang::get('proposed::message.Not permission'));
        // }
        // insert employee delete proposed when proposed not answer
        if (empty($proposed->updated_by) && empty($proposed->answer_emp_id)) {
            $dataProposed = [
                'updated_by' => $userCurrent->id,
            ];
            $proposed->setData($dataProposed);
            $proposed->save();
        }
        $proposed->delete();
        return redirect()->route('proposed::manage-proposed.index')
            ->with('flash_success', Lang::get('proposed::message.Delete success'));
    }
}
