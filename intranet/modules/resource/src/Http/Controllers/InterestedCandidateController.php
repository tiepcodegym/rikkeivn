<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Recruitment\Model\CddMailSent;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;
use Lang;
use Rikkei\Team\Model\Permission as TeamPermission;

class InterestedCandidateController extends Controller
{
    /**
     * get interested candidates list
     */
    public function interested()
    {
        $type = Input::get('type');
        Breadcrumb::add(Lang::get('resource::view.Candidate.List.Interested candidate list'));

        $isScopeTeam = Permission::getInstance()->isScopeCompany() || Permission::getInstance()->isScopeTeam();
        $teamPermission = new TeamPermission();
        $recruiters = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $recruiters = $isScopeTeam ? $recruiters : null;
        $positionOptions = getOptions::getInstance()->getRoles();
        $programList = Programs::all();

        return view('resource::candidate.interested', [
            'collectionModel' => (new Candidate())->getInterestedList($type, $isScopeTeam),
            'positionOptions' => $positionOptions,
            'programList' => $programList,
            'recruiters' => $recruiters,
            'isScopeTeam' => $isScopeTeam,
            'type' => $type,
        ]);
    }

    /**
     * remove interested candidate from the list
     */
    public function removeInterested(Request $request)
    {
        $candidate = Candidate::findInterestedCandidateById($request->id);
        if (!$candidate) {
            return redirect()->back()->withErrors(Lang::get('resource::message.Not found item'));
        }

        $isScopeTeam = Permission::getInstance()->isScopeCompany() || Permission::getInstance()->isScopeTeam();
        // not permission remove interested
        if (!$isScopeTeam && $candidate->recruiter !== Permission::getInstance()->getEmployee()->email) {
            return redirect()->back()->withErrors(Lang::get('resource::message.You don\'t permission to remove interested this candidate'));
        }

        $candidate->update(['interested' => getOptions::INTERESTED_NOT]);
        $messages = [
            'success' => [
                Lang::get('resource::message.Remove interested item success'),
            ]
        ];
        return redirect()->back()->with('messages', $messages);
    }

    /**
     * preview content mail interested
     */
    public function previewMail(Request $request)
    {
        $data = [
            'content' => $request->get('content'),
            'type' => (int)$request->get('type'),
        ];
        $contentMail = view('resource::candidate.mail.interested', compact('data'))->render();
        return response()->json([
            'success' => 1,
            'content' => $contentMail,
            'subject' => $request->get('subject'),
        ]);
    }

    /**
     * send mail interested
     */
    public function sendMail(Request $request)
    {
        $errorDefault = [
            'success' => 0,
            'message' => Lang::get('core::message.Error input data!'),
        ];
        $type = (int)$request->get('type');
        $candidateIds = (array)$request->get('candidateIds');
        $now = Carbon::now();

        $validate = Validator::make($request->all(), [
            'subject' => 'required|max:255',
            'content' => 'required',
            'app_pass' => 'required',
        ]);
        // validate form fail
        if ($validate->fails() || !in_array($type, [CddMailSent::TYPE_MAIL_FOLLOW, CddMailSent::TYPE_MAIL_BIRTHDAY])) {
            return response()->json($errorDefault);
        }

        $collectionCandidate = (new Candidate())->getInterestedListByIds($candidateIds, $type);
        // not found candidates
        if (count($collectionCandidate) === 0) {
            $errorDefault['message'] = Lang::get('core::view.Not found item');
            return response()->json($errorDefault);
        }
        // convert to array has key is candidate id for search
        $candidates = [];
        foreach ($collectionCandidate as $item) {
            $candidates[$item->id] = $item;
        }
        // candidate id does not match
        foreach ($candidateIds as $id) {
            if (!isset($candidates[$id])) {
                return response()->json($errorDefault);
            }
        }

        $emailQueue = new EmailQueue();
        $curEmp = Permission::getInstance()->getEmployee();
        $appPass = $request->get('app_pass');
        // verify mail of current employee
        if (!$emailQueue->verifyMail($curEmp->email, $appPass)) {
            $errorDefault['message'] = Lang::get('resource::message.App password is not correct!');
            return response()->json($errorDefault);
        }
        DB::beginTransaction();
        try {
            // save new app password if different
            $curEmp->app_password = $appPass;
            $curEmp->save();

            $queueData = [];
            $dataSentInsert = [];
            foreach ($candidates as $cdd) {
                $dataSentInsert[] = [
                    'candidate_id' => $cdd->id,
                    'sent_date' => $now->toDateTimeString(),
                    'type' => $type,
                ];
                $content = preg_replace('/\{\{\sname\s\}\}/', $cdd->fullname, $request->get('content'));
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($cdd->email)
                    ->setFrom($curEmp->email, $curEmp->name)
                    ->setSubject($request->get('subject'))
                    ->setTemplate('resource::candidate.mail.interested', [
                        'content' => $content,
                        'type' => $type,
                    ]);
                $queueData[] = $emailQueue->getValue();
            }
            EmailQueue::insert($queueData);
            CddMailSent::insert($dataSentInsert);
            DB::commit();
            return response()->json([
                'success' => 1,
                'sent_date' => $now->toDateString(),
                'message' => Lang::get('recruitment::message.Send email success'),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json([
                'success' => 0,
                'message' => Lang::get('core::message.Error system, please try later!'),
            ]);
        }
    }
}
