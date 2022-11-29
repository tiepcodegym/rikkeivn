<?php

namespace Rikkei\Vote\Http\Controllers\Admin;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Vote\Model\Vote;
use Rikkei\Vote\Model\Nominee;
use Rikkei\Vote\Model\VoteNominee;
use Rikkei\Vote\Model\VoteResult;
use Rikkei\Vote\View\VoteConst;
use Carbon\Carbon;
use Validator;
use URL;

class NominationController extends Controller {
    
    /**
     * list ajax nominees of vote
     * @param type $voteId
     * @return type
     */
    public function getNomineesAjaxData ($voteId) {
        $collectionModel = Nominee::getNomineesByVoteId($voteId);
        $response['html'] = view('vote::manage.include.nominee_list', compact('collectionModel'))->render();
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * list ajax nominator of nominee in vote
     * @param type $voteId
     * @param type $nomineeId
     * @return type
     */
    public function getNominatorsAjaxData ($voteId, $nomineeId) {
        $listNominators = Nominee::getNominatorsByVoteId($voteId, $nomineeId);
        $response['html'] = view('vote::manage.include.nominator_list', compact('listNominators'))->render();
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * list ajax nominee confirmed
     * @param type $voteId
     * @return type
     */
    public function getVoteNomineesAjaxData ($voteId) {
        $vote = Vote::find($voteId);
        if (!$vote) {
            return response()->json([
                'success' => 0,
                'message' => trans('vote::message.not_found_item')
            ]);
        }
        //check time
        $timeNow = Carbon::now();
        $allowDelete = true;
        if ($timeNow->gte($vote->vote_start_at)) {
            $allowDelete = false;
        }
        //check edit permission
        $permissEdit = VoteConst::hasPermissEdit($vote, 'vote::manage.vote_nominee.update_desc');
        
        $collectionModel = VoteNominee::getByVoteId($voteId);
        $response['html'] = view('vote::manage.include.candidate_list', compact('collectionModel', 'allowDelete', 'permissEdit'))->render();
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * list ajax voters
     * @param type $voteId
     * @param type $nomineeId
     * @return type
     */
    public function getVotersAjaxData ($voteNomineeId) {
        $listVoters = VoteResult::getVotersByVoteNomineeId($voteNomineeId);
        $response['html'] = view('vote::manage.include.voter_list', compact('listVoters'))->render();
        $response['success'] = 1;
        return response()->json($response);
    }
    
    /**
     * update nominee description
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function updateVoteNomineeDesc ($id, Request $request) {
        $voteNominee = VoteNominee::find($id);
        if (!$voteNominee) {
            return response()->json([
                'success' => 0,
                'message' => trans('vote::message.not_found_item')
            ]);
        }
        //check edit permission
        $permissEdit = VoteConst::hasPermissEdit(Vote::find($voteNominee->vote_id), 'vote::manage.vote_nominee.update_desc');
        if (!$permissEdit) {
            return response()->json([
                'success' => 0,
                'message' => trans('vote::message.you_dont_have_permission')
            ]);
        }
        
        $voteNominee->description = $request->get('description');
        $voteNominee->save();
        return response()->json([
            'success' => 1,
            'description' => $voteNominee->description
        ]);
    }
    
    /**
     * delete vote nominee
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function deleteVoteNominee ($id, Request $request) {
        $tabId = $request->get('tab_id');
        $voteNominee = VoteNominee::find($id);
        if (!$voteNominee) {
            abort(404);
        }
        //check time
        $timeNow = Carbon::now();
        $vote = Vote::find($voteNominee->vote_id);
        if ($vote && $timeNow->gte($vote->vote_start_at)) {
            return redirect()->to(URL::previous().$tabId)->with('messages', ['errors' => [trans('vote::message.cannot_delete_nominee_while_vote_started')]]);
        }
        $voteNominee->delete();
        return redirect()->to(URL::previous().$tabId)->with('messages', ['success' => [trans('vote::message.remove_success')]]);
    }
    
    /**
     * search select2 ajax
     * @param Request $request
     * @return type
     */
    public function searchEmployees(Request $request) {
        $list = VoteNominee::getEmployeesExcerptNominees($request->all());
        return response()->json($list);
    }
    
    /**
     * insert vote nominee
     * @param Request $request
     * @return type
     */
    public function storeVoteNominee(Request $request) {
        $tabId = '#candidate_list';
        $valid = Validator::make($request->all(), [
            'nominee_employee_id' => 'required',
            'vote_id' => 'required'
        ], [
            'nominee_employee_id.required' => trans('validation.required', ['attribute' => 'Email nominee']),
            'vote_id.required' => trans('validation.required', ['attribute' => 'vote'])
        ]);
        if ($valid->fails()) {
            return redirect()->to(URL::previous() . $tabId)->withInput()
                    ->with('nominee-error', true)->withErrors($valid->errors());
        }
        $vote = Vote::find($request->get('vote_id'));
        if (!$vote) {
            abort(404);
        }
        $timeNow = Carbon::now();
        if ($timeNow->gt($vote->vote_start_at)) {
            return redirect()->to(URL::previous() . $tabId)->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.cannot_add_nominee_while_vote_started')]]);
        }
        $data = $request->all();
        $data['confirm'] = VoteConst::CONFIRM_YES;
        VoteNominee::insertData($data);
        
        return redirect()->to(URL::previous() . $tabId)->with('messages', ['success' => [trans('vote::message.add_nominee_success')]]);
    }
    
}

