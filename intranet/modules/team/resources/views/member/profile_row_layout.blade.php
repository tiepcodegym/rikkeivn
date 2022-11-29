@extends('layouts.default')
<?php
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\Model\Candidate;

$candidateAs = Candidate::getCandidate($employeeModelItem->id);
$isAccessDelete = Permission::getInstance()->isScopeCompany(null, 'team::team.member.delete');
if (isset($buttonActionMore)) {
    $filterActions = ['buttons' => $buttonActionMore];
} else {
    $filterActions = [];
}
?>

@section('title')
{{ trans('team::view.Profile of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
@if (isset($candidateAs) && $candidateAs->id)
<div class="row">
    <a href="{!!route('resource::candidate.detail', ['id'=>$candidateAs->id])!!}" target="_blank" style="float: right; padding: 0px 15px 0px 10px;">Candidate detail</a><i class="fa fa-yelp" style="float: right;"></i>
</div>
@endif
<div class="row member-profile">
        <div class="col-lg-2 col-md-3">
            @include('team::member.left_menu',['active' => $tabType])
        </div>
        <div class="col-lg-10 col-md-9 tab-content">
            <div class="box box-info tab-pane active">
                <div class="row">
                    <div class="col-md-8">
                        <div class="box-header with-border">
                            <h2 class="box-title">{!!$tabTitle!!}</h2>
                            @if (isset($helpLink) && $helpLink)
                            <a href="{!!$helpLink!!}" target="_blank" title="Help">
                                    <i class="fa fa-fw fa-question-circle" style="font-size: 18px;"></i>
                                </a>
                            @endif
                            @if (isset($tabTitleSub) && $tabTitleSub)
                                <p class="margin-top-10">{!!$tabTitleSub!!}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box-header pull-right">
                            @include('team::include.filter', $filterActions)
                        </div>
                    </div>

                </div>
                <div class="box-body">
                   @yield('content_profile')
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
</div>
@endsection

@section('script')
@yield('extra_script')
<script>
    var globalPassModule = {
        urlViewProfile: '{!!route('team::member.profile.index', ['id' => 0])!!}',
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        trans: {
            offical_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Join date')])!!}',
            leave_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::view.Offical date')])!!}',
            work_japan_to_greater: '{!!trans('validation.Must be greater or equal than :attribute.', ['attribute' => trans('team::profile.From')])!!}'
        }
    };
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/localization/messages_vi.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/script.js') }}"></script>
@endsection
