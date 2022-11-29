<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Permission;

$currentUser = Permission::getInstance()->getEmployee();
$team = Team::getTeamByEmp($currentUser->id, true);
if (empty($team)) {
    $team = Team::getTeamByEmp($currentUser->id);
}
$mailTitle = $team ? trans('resource::view.[:team][recruit month :month] recruit request - Candidate: :name',
                            ['team' => $team->name, 'month' => date('m/Y'), 'name' => $candidate->fullname])
                        : trans('resource::view.[recruit month :month] recruit request - Candidate: :name',
                            ['month' => date('m/Y'), 'name' => $candidate->fullname]);
?>
<div class="modal fade" id="modal-recruiter_content" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail title') }}</h4>
                <input type="text" class="form-control" id="mail-title" 
                       value="{{ $mailTitle }}"/>
                <h4 class="modal-title">{{ trans('resource::view.Related receiver') }}</h4>
                <div>
                    <select class="select-search form-control" multiple id="recruit_related"
                            data-remote-url="{{ route('team::employee.list.search.ajax') }}"></select>
                </div>
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        <textarea id="recruiter_content">
                            
                        </textarea>
                    </div>
                    <input type="hidden" id="recruiter_email" value="{{$candidate->recruiter}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-left" data-dismiss="modal">{{ trans('resource::view.Close') }}</button>
                <button type="button" class="btn btn-primary btn-send-mail-test" onclick="sendMail(this, {{ Candidate::MAIL_RECRUITER }});">
                    <span>
                        {{ Lang::get('resource::view.Send') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="mail-recruiter_content hidden">
    <p>{!! trans('resource::view.Hello <b>:name</b>,', ['name' => CoreView::getNickName($candidate->recruiter)]) !!}</p>
    <p>{{ trans('resource::view.Has a candidate pass interview and recruit requested') }}</p>
    <p>{!! trans('resource::view.<b>Requester infomation</b>') !!}</p>
    <p>{{ trans('resource::view.Full name: :name', ['name' => $currentUser->name]) }}</p>
    <p>{{ trans('resource::view.Email: :email', ['email' => $currentUser->email]) }}</p>
    <p>{!! trans('resource::view.<b>Candidate infomation</b>') !!}</p>
    <p>{{ trans('resource::view.Full name: :name', ['name' => $candidate->fullname]) }}</p>
    <p>{{ trans('resource::view.Email: :email', ['email' => $candidate->email]) }}</p>
    <?php
    if ($resultTest->isEmpty()) {
        $resultTest = collect([
            ['name' => 'GMAT', 'value' => $candidate->test_mark],
            ['name' => trans('resource::view.Expertise'), 'value' => $candidate->test_mark_specialize]
        ]);
    }
    ?>
    @foreach ($resultTest as $result)
    <p>Test {{ !is_array($result) ? $result->name : $result['name'] }}: 
        {{ !is_array($result) ? $result->total_corrects . '/'. $result->total_questions : $result['value'] }}</p>
    @endforeach
    
    <p>{{ trans('resource::view.Start working: :date', ['date' => $candidate->start_working_date]) }}</p>
    <p>{{ trans('resource::view.Contract length: :length', ['length' => $candidate->contract_length]) }}</p>
    <p>{{ trans('resource::view.Contract type: :type', ['type' => getOptions::getContractTypeByType($candidate->working_type)]) }}</p>
    <p>{{ trans('resource::view.Vacancies: ') }}</p>
    <p>{{ trans('resource::view.Salary(gross): ') }}</p>
    <p>{{ trans('resource::view.Bonus: ') }}</p>
    <p>
        {{ trans('resource::view.Comment:') }}
        <br>
        {!! nl2br($candidate->interview_note) !!}
    </p>
    <p>
        <a href="{{route('resource::candidate.detail', ['id'=>$candidate->id]) }}">{{ trans('resource::view.Request.asset.create.view detail') }}</a>
    </p>
</div>
