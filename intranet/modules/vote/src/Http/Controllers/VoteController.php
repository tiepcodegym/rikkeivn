<?php

namespace Rikkei\Vote\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Vote\Model\Vote;
use Rikkei\Vote\Model\Nominee;
use Rikkei\Vote\Model\VoteNominee;
use Rikkei\Vote\Model\VoteResult;
use Rikkei\Vote\View\VoteConst;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Carbon\Carbon;
use Validator;
use DB;
use Log;

class VoteController extends Controller {
    
    /**
     * show view nominate
     * @param type $slug
     * @return type view
     */
    public function showNominate ($slug) {
        $isSelf = false;
        $vote = Vote::findBySlug($slug);
        if (!$vote || $vote->status == VoteConst::STT_DISABLE) {
            abort(404);
        }
        Breadcrumb::add(trans('vote::view.nominate'));
        $errorMess = null;
        //check time
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            $errorMess = trans('vote::message.expired_to_participate_nominate');
            return view('vote::nominate', compact('isSelf', 'vote', 'errorMess'));
        }
        
        //check max nominee
        $remaniNominee = Nominee::getRemainNominee($vote);
        if ($remaniNominee !== null && $remaniNominee < 1) {
            $errorMess = trans('vote::message.you_had_enough_nominated');
            return view('vote::nominate', compact('isSelf', 'vote', 'errorMess'));
        }
        
        return view('vote::nominate', compact('isSelf', 'vote', 'remaniNominee', 'errorMess'));
    }
    
    /**
     * search employee not in nominee
     */
    public function searchNomineeEmployees (Request $request) {
        $list = Nominee::getEmployeesExcerpt($request->all());
        return response()->json($list);
    }
    
    /**
     * insert nominee
     * @param type $voteId
     * @param Request $request
     * @return type
     */
    public function addNominate ($voteId, Request $request) {
        $valid = Validator::make($request->all(), [
            'nominee_id' => 'required',
            'reason' => 'required'
        ], [
            'nominee_id.required' => trans('vote::message.validate_required', ['field' => trans('vote::view.nominee')]),
            'reason.required' => trans('vote::message.validate_required', ['field' => trans('vote::view.reason')]) 
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        //check employee
        $nomineeId = $request->get('nominee_id');
        $employee = Employee::find($nomineeId, ['id', 'name', 'email']);
        if (!$employee) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.not_found_nominee')]]);
        }
        
        $vote = Vote::find($voteId);
        if (!$vote) {
            abort(404);
        }
        //check time
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.expired_to_participate_nominate')]])
                    ->with('dup-error', true);
        }
        //check max nominee
        $remaniNominee = Nominee::getRemainNominee($vote);
        if ($remaniNominee !== null && $remaniNominee < 0) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.you_had_enough_nominated')]])
                    ->with('dup-error', true);
        }
        //check exits
        if (Nominee::checkExists($voteId, $nomineeId)) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.you_had_nominated_this_nominee', ['name' => $employee->name])]]);
        }
        DB::beginTransaction();
        try {
            //insert nominee
            Nominee::create([
                'vote_id' => $voteId,
                'nominee_id' => $nomineeId,
                'nominator_id' => auth()->id(),
                'reason' => $request->get('reason')
            ]);
            
            //insert vote nominee
            $nomineeKey = VoteNominee::generateKey();
            $voteNominee = VoteNominee::insertData([
                'nominee_employee_id' => $nomineeId,
                'vote_id' => $voteId,
                'key' => $nomineeKey,
                'confirm' => null,
                'update' => false
            ]);

            if (!$voteNominee->is_update) {
                $dataEmail = [
                    'employee_name' => $employee->name,
                    'vote_title' => $vote->title,
                    'confirm_key' => $nomineeKey,
                    'detail_link' => route('vote::detail', ['slug' => $vote->slug])
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($employee->email, $employee->name)
                        ->setSubject(trans('vote::email.confirm_nominate_from_vote', ['vote_title' => $vote->title]))
                        ->setTemplate('vote::email.confirm_nominate', $dataEmail)
                        ->save();
            }
            
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('vote::message.you_had_nominated_success', ['name' => $employee->name])]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.na_error')]]);
        }        
    }
    
    /**
     * confirm nomination email
     * @param type $key
     * @param Request $request
     */
    public function nomineeConfirmEmail ($key, Request $request) {
        Breadcrumb::add(trans('vote::view.confirm_join_vote'));
        
        $voteNominee = VoteNominee::where('key', $key)->first();
        $ans = $request->get('ans');
        $arrAns = [
            VoteConst::CONFIRM_YES => VoteConst::TEXT_YES, 
            VoteConst::CONFIRM_NO => VoteConst::TEXT_NO
        ];
        //check validate
        if (!$voteNominee || !$ans || !in_array($ans, $arrAns)) {
            abort(404);
        }
        $vote = Vote::find($voteNominee->vote_id);
        //check time nominate_end_at
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            return view('vote::confirm_result', ['vote' => $vote, 'errorMess' => trans('vote::message.expired_to_participate_nominate')]);
        }
        
        $confirm = array_search($ans, $arrAns);
        $voteNominee->confirm = $confirm;
        $voteNominee->save();
        
        return view('vote::confirm_result', compact('voteNominee', 'vote'));
    }
    
    /**
     * show view self nominate
     * @param type $slug
     * @return type view
     */
    public function showSelfNominate ($slug) {
        $isSelf = true;
        $vote = Vote::findBySlug($slug);
        if (!$vote || $vote->status == VoteConst::STT_DISABLE) {
            abort(404);
        }
        Breadcrumb::add(trans('vote::view.self_nominate'));
        $errorMess = null;
        //check time
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            $errorMess = trans('vote::message.expired_to_participate_nominate');
            return view('vote::nominate', compact('isSelf', 'vote', 'errorMess'));
        }
        //check self nominate
        if (Nominee::checkExists($vote->id, auth()->id(), true)) {
            $errorMess = trans('vote::message.you_had_self_nominate_before');
            return view('vote::nominate', compact('isSelf', 'vote', 'errorMess'));
        }
        
        return view('vote::nominate', compact('isSelf', 'vote', 'errorMess'));
    }
    
    public function addSelfNominate ($voteId, Request $request) {
        $vote = Vote::find($voteId);
        if (!$vote) {
            abort(404);
        }
        $valid = Validator::make($request->all(), [
            'reason' => 'required'
        ], [
            'reason.required' => trans('vote::message.validate_required', ['field' => trans('vote::view.reason')]) 
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        //check time
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.expired_to_participate_nominate')]])
                    ->with('dup-error', true);
        }
        $nomineeId = auth()->id();
        //check exits
        if (Nominee::checkExists($voteId, $nomineeId, true)) {
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.you_had_self_nominate_before')]])
                    ->with('dup-error', true);;
        }
        DB::beginTransaction();
        try {
            //insert nominee
            Nominee::create([
                'vote_id' => $voteId,
                'nominee_id' => $nomineeId,
                'nominator_id' => null,
                'reason' => $request->get('reason')
            ]);
            
            //insert vote nominee
            VoteNominee::insertData([
                'nominee_employee_id' => $nomineeId,
                'vote_id' => $voteId,
                'key' => null,
                'confirm' => VoteConst::CONFIRM_YES
            ], true);
            
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('vote::message.you_had_self_nominated_success')]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.na_error')]]);
        }  
    }
    
    /**
     * show view vote
     * @param type $slug
     * @return type view
     */
    public function showVote ($slug) {
        Breadcrumb::add(trans('vote::view.vote'));
        
        $vote = Vote::findBySlug($slug);
        if (!$vote || $vote->status == VoteConst::STT_DISABLE) {
            abort(404);
        }
        $errorMess = null;
        //get remain vote
        $remainVote = VoteResult::getRemainVote($vote);
        //check time
        $timeNow = Carbon::now();
        if ($timeNow->lt($vote->vote_start_at) || $timeNow->gt($vote->vote_end_at)) {
            $errorMess = trans('vote::message.expired_to_participate_vote');
            return view('vote::vote', compact('vote', 'remainVote', 'errorMess'));
        }
        $nominees = VoteNominee::getByVoteId($vote->id, false);

        return view('vote::vote', compact('vote', 'nominees', 'remainVote', 'errorMess'));
    }
    
    /**
     * add vote result
     * @param type $voteNomineeId
     * @return type
     */
    public function addVote ($voteNomineeId) {
        $voteNominee = VoteNominee::find($voteNomineeId);
        if (!$voteNominee) {
            return response()->json(trans('vote::message.not_found_item'), 404);
        }
        //check remain vote
        $checkVoted = VoteResult::checkVoted($voteNominee->id);
        $remainVote = VoteResult::getRemainVote($voteNominee->vote_id);
        if (!$checkVoted && $remainVote !== null && $remainVote < 1) {
            return response()->json(trans('vote::message.you_had_vote_enough'), 404);
        }
        $addVote = VoteResult::addOrRemoveVote($voteNomineeId);
        return response()->json(['add_vote' => $addVote]);
    }
    
    /**
     * show detail vote
     * @param type $slug
     * @return type
     */
    public function detail ($slug) {
        $vote = Vote::findBySlug($slug);
        if (!$vote || $vote->status == VoteConst::STT_DISABLE) {
            abort(404);
        }
        return view('vote::detail', compact('vote'));
    }
    
}

