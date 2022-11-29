<?php

namespace Rikkei\Vote\Http\Controllers\Admin;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Vote\Model\Vote;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Vote\View\VoteConst;
use Carbon\Carbon;
use Validator;
use DB;
use URL;
use Log;

class VoteController extends Controller {
    
    /**
     * _construct
     */
    public function _construct() {
        Breadcrumb::add(trans('vote::view.list_votes'), route('vote::manage.vote.index'));
        Menu::setActive('hr');
    }
    
    /**
     * validate data
     * @return type
     */
    public function validData($data, $voteId = null) {
        $valid = Validator::make($data, [
            'title' => 'required|max:255|unique:votes,title' . ($voteId ? ',' . $voteId : ''),
            'status' => 'required',
            'nominate_start_at' => 'date_format:Y-m-d H:i',
            'nominate_end_at' => 'date_format:Y-m-d H:i',
            'vote_start_at' => 'required|date_format:Y-m-d H:i',
            'vote_end_at' => 'required|date_format:Y-m-d H:i',
            'nominee_max' => 'numeric|min:0',
            'vote_max' => 'numeric|min:0',
            'content' => 'required'
        ], [
            'title.unique' => trans('vote::message.validate_unique', ['field' => trans('vote::view.title')]),
            'title.max' => trans('vote:message.validate_text_max', ['field' => trans('vote::view.title'), 'max' => 255]),
            'content.required' => trans('vote::message.validate_required', ['field' => trans('vote::view.content')])
        ]);
        return $valid;
    }
    
    /**
     * show view list votes
     * @return type
     */
    public function index () {
        $collectionModel = Vote::getGridData();
        $permissEdit = VoteConst::hasPermissEdit();

        return view('vote::manage.vote.index', compact('collectionModel', 'permissEdit'));
    }
    
    /**
     * create vote
     * @return type
     */
    public function create() {
        Breadcrumb::add(trans('vote::view.create_vote'));
        
        $permissEdit = VoteConst::hasPermissEdit();
        return view('vote::manage.vote.create', compact('permissEdit'));
    }
    
    /**
     * save vote
     * @param Request $request
     * @return type
     */
    public function store(Request $request) {
        $data = $request->all();
        $valid = $this->validData($data);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $data['created_by'] = auth()->id();
        if ($request->has('nominate_start_at')) {
            $data['nominate_start_at'] .= ':00';
        }
        if ($request->has('nominate_end_at')) {
            $data['nominate_end_at'] .= ':00';
        }
        $data['vote_start_at'] .= ':00';
        $data['vote_end_at'] .= ':00';
        $data['slug'] = str_slug($data['title']);
        $vote = Vote::create($data);
        
        return redirect()->route('vote::manage.vote.edit', ['id' => $vote->id])
                ->with('messages', ['success' => [trans('vote::message.add_new_success')]]);
    }
    
    /**
     * view edit vote
     * @param type $id
     * @return type
     */
    public function edit($id) {
        $vote = Vote::find($id);
        if (!$vote) {
            abort(404);
        }
        Breadcrumb::add(trans('vote::view.vote_detail'));
        
        $formatDay = trans('vote::view.format_day');
        $teamList = TeamList::toCheckbox();
        //get config email nomination
        $subjectNominateEmail = trans('vote::email.nominate_email_subject', [
            'vote_title' => $vote->title
        ]);
        $contentNominateEmail = trans('vote::email.nominate_email_content', [
            'vote_title' => $vote->title,
            'self_nominate_link' => route('vote::show_self_nominate', ['slug' => $vote->slug]),
            'nominate_link' => route('vote::show_nominate', ['slug' => $vote->slug]),
            'nominee_max' => $vote->nominee_max ? $vote->nominee_max : trans('vote::view.unlimited_num'),
            'start_content' => $vote->nominate_start_at ? trans('vote::email.start_nominate_content', ['nominate_start' => $vote->nominate_start_at->format('H\hi '. $formatDay .' d/m/Y')]) : '',
            'end_content' => $vote->nominate_end_at ? trans('vote::email.end_nominate_content', ['nominate_end' => $vote->nominate_end_at->format('H\hi '. $formatDay .' d/m/Y')]) : ''
        ]);

        //get config email vote
        $subjectVoteEmail = trans('vote::email.vote_email_subject', [
            'vote_title' => $vote->title
        ]);
        $contentVoteEmail = trans('vote::email.vote_email_content', [
            'vote_title' => $vote->title,
            'vote_link' => route('vote::show_vote', ['slug' => $vote->slug]),
            'vote_start' => $vote->vote_start_at->format('H\hi '. $formatDay .' d/m/Y'),
            'vote_end' => $vote->vote_end_at->format('H\hi '. $formatDay .' d/m/Y'),
            'vote_max' => $vote->vote_max ? $vote->vote_max : trans('vote::view.unlimited_num')
        ]);
        //get permission edit
        $permissEdit = VoteConst::hasPermissEdit($vote, 'vote::manage.vote.update');
        
        return view('vote::manage.vote.edit', compact('vote', 'teamList', 'subjectNominateEmail', 'contentNominateEmail', 
                'subjectVoteEmail', 'contentVoteEmail', 'permissEdit'));
    }
    
    /**
     * save edit vote
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function update($id, Request $request) {
        $vote = Vote::find($id);
        if (!$vote) {
            return redirect()->back()->withInput()->with('message', ['errors' => [trans('vote::message.not_found_item')]]);
        }
        $valid = $this->validData($request->all(), $id);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $data = array_only($request->all(), $vote->getFillable());
        if ($request->has('nominate_start_at')) {
            $data['nominate_start_at'] .= ':00';
        }
        if ($request->has('nominate_end_at')) {
            $data['nominate_end_at'] .= ':00';
        }
        $data['vote_start_at'] .= ':00';
        $data['vote_end_at'] .= ':00';
        $data['slug'] = str_slug($data['title']);
        $vote->update($data);
        $vote->save();
        
        return redirect()->back()->with('messages', ['success' => [trans('vote::message.save_success')]]);
    }
    
    /**
     * send vote email
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function sendNominateEmail($id, Request $request) {
        $vote = Vote::find($id);
        if (!$vote) {
            abort(404);
        }
        if ($request->get('change_data')) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.you_must_save_data_before_send_email')]]);
        }
        //check status
        if ($vote->status == VoteConst::STT_DISABLE) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.cannot_send_mail_while_status_disabled')]]);
        }
        //check time
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->gt($vote->nominate_end_at)) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.cannot_send_nominate_mail_while_nominate_finished')]]);
        }
        //validate data
        $valid = Validator::make($request->all(), [
            'mail_team_ids' => 'required_without:mail_bcc',
            'mail_bcc' => 'required_without:mail_team_ids',
            'mail_subject' => 'required|max:255',
            'mail_content' => 'required'
        ], [
            'mail_team_ids.required_without' => trans('validation.required', ['attribute' => 'mail team']),
            'mail_bcc.required_without' => trans('validation.required', ['attribute' => 'other mail']),
            'mail_subject.required' => trans('validation.required', ['attribute' => 'mail subject']),
            'mail_content.required' => trans('validation.required', ['attribute' => 'mail content'])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->with('mail-error', true)->withErrors($valid->errors());
        }
        $teamIds = $request->get('mail_team_ids');
        $mailSubject = $request->get('mail_subject');
        $mailContent = $request->get('mail_content');
        
        // get other accounts
        $bccAccountIds = $request->get('mail_bcc');
        $otherEmps = null;
        if ($bccAccountIds) {
            $otherEmps = Employee::whereIn('id', $bccAccountIds)
                            ->select('id', 'name', 'email')
                            ->get();
        }
        //get all employee of team ids
        $employees = Employee::getAllEmployeesOfTeam($teamIds);
        if ($otherEmps && !$otherEmps->isEmpty()) {
            $employees = $employees->merge($otherEmps);
        }
        if ($employees->isEmpty()) {
            return redirect()->back()->withInput()->with('mail-error', true)
                    ->with('messages', ['errors' => [trans('vote::message.no_account_to_send_mail')]]);
        }
            
        DB::beginTransaction();
        try {
            //save email queue
            $arrEmailQueues = [];
            foreach ($employees as $emp) {
                $dataMail = [
                    'content' => $mailContent
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setSubject($mailSubject)
                        ->setTemplate('vote::email.vote', $dataMail)
                        ->setTo($emp->email, $emp->name);
                $arrEmailQueues[] = $emailQueue->getValue();
            }
            EmailQueue::insert($arrEmailQueues);

            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('vote::message.send_nominate_mail_success', ['count' => $employees->count()])]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->with('mail-error', true)->withInput()->with('messages', ['errors' => [trans('vote::message.na_error')]]);
        }
    }
    
    /**
     * send notify vote email
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function sendVoteEmail($id, Request $request) {
        $vote = Vote::find($id);
        $tabId = '#candidate_list';
        if (!$vote) {
            abort(404);
        }
        //check status
        if ($vote->status == VoteConst::STT_DISABLE) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('vote::message.cannot_send_mail_while_status_disabled')]]);
        }
        //check time valid to send mail
        $timeNow = Carbon::now();
        if ($vote->nominate_end_at && $timeNow->lt($vote->nominate_end_at)) {
            return redirect()->to(URL::previous() . $tabId)->withInput()
                    ->with('messages', ['errors' => [trans('vote::message.cannot_send_vote_mail_while_nominate_not_finished')]]);
        }
        //validate data
        $valid = Validator::make($request->all(), [
            'mail_vote_team_ids' => 'required_without:mail_vote_bcc',
            'mail_vote_bcc' => 'required_without:mail_vote_team_ids',
            'mail_vote_subject' => 'required|max:255',
            'mail_vote_content' => 'required'
        ], [
            'mail_vote_team_ids.required_without' => trans('validation.required', ['attribute' => 'mail team']),
            'mail_vote_bcc.required_without' => trans('validation.required', ['attribute' => 'other email']),
            'mail_vote_subject.required' => trans('validation.required', ['attribute' => 'mail subject']),
            'mail_vote_content.required' => trans('validation.required', ['attribute' => 'mail content'])
        ]);
        if ($valid->fails()) {
            return redirect()->to(URL::previous() . $tabId)->withInput()->with('mail-vote-error', true)->withErrors($valid->errors());
        }
        
        $teamIds = $request->get('mail_vote_team_ids');
        $mailSubject = $request->get('mail_vote_subject');
        $mailContent = $request->get('mail_vote_content');

        // get other accounts
        $bccAccountIds = $request->get('mail_vote_bcc');
        $otherEmps = null;
        if ($bccAccountIds) {
            $otherEmps = Employee::whereIn('id', $bccAccountIds)
                            ->select('id', 'name', 'email')
                            ->get();
        }
        //get all employee of team ids
        $employees = Employee::getAllEmployeesOfTeam($teamIds);
        if ($otherEmps && !$otherEmps->isEmpty()) {
            $employees = $employees->merge($otherEmps);
        }
        if ($employees->isEmpty()) {
            return redirect()->to(URL::previous() . $tabId)->withInput()->with('mail-vote-error', true)
                    ->with('messages', ['errors' => [trans('vote::message.no_account_to_send_mail')]]);
        }
 
        DB::beginTransaction();
        try {
            //save email queue
            $arrEmailQueues = [];
            foreach ($employees as $emp) {
                $dataMail = [
                    'content' => $mailContent
                ];
                $emailQueue = new EmailQueue();
                $emailQueue->setSubject($mailSubject)
                        ->setTemplate('vote::email.vote', $dataMail)
                        ->setTo($emp->email, $emp->name);
                
                $arrEmailQueues[] = $emailQueue->getValue();
            }
            EmailQueue::insert($arrEmailQueues);

            DB::commit();
            return redirect()->to(URL::previous() . $tabId)->with('messages', ['success' => [trans('vote::message.send_vote_mail_success', ['count' => $employees->count()])]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->to(URL::previous() . $tabId)->with('mail-vote-error', true)->withInput()->with('messages', ['errors' => [trans('vote::message.na_error')]]);
        }
    }
    
    /**
     * delete vote
     * @param type $id
     * @return type
     */
    public function delete($id) {
        $vote = Vote::find($id);
        if (!$vote) {
            abort(404);
        }
        $vote->delete();
        return redirect()->back()->with('messages', ['success' => [trans('vote::message.remove_success')]]);
    }
    
}

