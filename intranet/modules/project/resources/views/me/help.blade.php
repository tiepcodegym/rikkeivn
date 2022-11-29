@extends('layouts.default')

@section('title', trans('project::me.Monthly Evaluation Help'))

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/edit.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('project/css/me_style.css') }}" />
@endsection

@section('content')

<?php
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Me\Model\Attribute;
use Rikkei\Me\View\View as MeView;

$sepMonth = config('project.me_sep_month');
$textSumary = trans('me::view.Summary');
?>

<div class="box box-rikkei">
    <div class="box-body">
        <div class="row">
            <div class="col-md-5 col-sm-6">
                <h3 class="_me_title">{{trans('project::me.Monthly Evaluation (ME) Process')}}</h3>
                <ol>
                    <li>{{trans('project::me.PM evaluates project members')}}</li>
                    <li>{{trans('project::me.Group Leader review ME')}}</li>
                </ol>
                <div class="me_chart text-center">
                    <img class="img-responsive" alt="char" src="{{ config('project.me_char_image') }}">
                </div>
                <h3 class="_me_title">{{trans('project::me.Contribution level')}}</h3>
                <div><strong>{{ trans('me::view.After month') . ' ' . $sepMonth }}</strong></div>
                <ul class="_table_list">
                    <li><span>S: </span> <span>{{ MeView::TYPE_S }} &nbsp; <= &nbsp; {{ $textSumary }}</span></li>
                    <li><span>A: </span> <span>{{ MeView::TYPE_A }} &nbsp; <= &nbsp; {{ $textSumary }} &nbsp; < &nbsp; {{ MeView::TYPE_S }}</span></li>
                    <li><span>B: </span> <span>{{ MeView::TYPE_B }} &nbsp; <= &nbsp; {{ $textSumary }} &nbsp; < &nbsp; {{ MeView::TYPE_A }}</span></li>
                    <li><span>C: </span> <span>{{ MeView::TYPE_C }} &nbsp; <= &nbsp; {{ $textSumary }} &nbsp; < &nbsp; {{ MeView::TYPE_B }}</span></li>
                </ul>

                <div><strong>{{ trans('me::view.From before to month') . ' ' . $sepMonth }}</strong></div>
                <ul class="_table_list">
                    <li><span>{{trans('project::me.Excellent')}}:</span> <span>{{ MeEvaluation::TH_EXCELLENT }} <= {{ $textSumary }} </span></li>
                    <li><span>{{trans('project::me.Good')}}:</span> <span>{{ MeEvaluation::TH_GOOD }} <= {{ $textSumary }} < {{ MeEvaluation::TH_EXCELLENT }}</span></li>
                    <li><span>{{trans('project::me.Fair')}}:</span> <span>{{ MeEvaluation::TH_FAIR }} <= {{ $textSumary }} < {{ MeEvaluation::TH_GOOD }}</span></li>
                    <li><span>{{trans('project::me.Satisfactory')}}:</span> <span>{{ MeEvaluation::TH_SATIS }} <= {{ $textSumary }} < {{ MeEvaluation::TH_FAIR }}</span></li>
                    <li><span>{{trans('project::me.Unsatisfactory')}}:</span> <span>{{ $textSumary }} < {{ MeEvaluation::TH_SATIS }}</span></li>
                </ul>
            </div>
            <div class="col-md-7 col-sm-6">
                <h3 class="_me_title">{{trans('project::me.Instructions for making ME')}}</h3>

                <h4><strong>{{ trans('me::view.ME points caculating') }}:</strong></h4>
                <div class="nav-tabs-custom nav-tabs-rikkei">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#point_after_month" data-toggle="tab"><b>{{ trans('me::view.After month') . ' ' . $sepMonth }}</b></a>
                        </li>
                        <li role="presentation" class="">
                            <a href="#point_before_month" data-toggle="tab"><b>{{ trans('me::view.From before to month') . ' ' . $sepMonth }}</b></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="point_after_month">
                            <h4><strong>{{ trans('me::view.ME points are calculated based on 5 point attributes (using a 10-point scale)') }}:</strong></h4>
                            <?php
                            $newAttributes = Attribute::getInstance()->getAttrsByGroup([Attribute::GR_NEW_NORMAL, Attribute::GR_NEW_PERFORM]);
                            ?>
                            @if (!$newAttributes->isEmpty())
                            <div>
                                @foreach ($newAttributes as $attr)
                                <strong>{{ $attr->label . ' ('. $attr->weight .'%)' }}: </strong> <span>{{ $attr->name }}</span>
                                {!! $attr->description !!}
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="tab-pane" id="point_before_month">
                            <h4><strong>{{trans('project::me.ME points are calculated based on 3 point blocks (using a 5-point scale)')}}:</strong></h4>
                            <div>
                                <strong>{{trans('project::me.Rule & activities')}} (30%):</strong> {{trans('project::me.The average of 4 criteria')}}
                                <ul>
                                    <li>{{trans('project::me.Ensure following working time regulations Automatically collect data from attendance system. Substract 1 point for each time arriving late at work')}}</li>
                                    <li>{{trans('project::me.Ensure following regulations at work')}};</li>
                                    <li>{{trans('project::me.Contribute to professional activities')}};</li>
                                    <li>{{trans('project::me.Contribute to social activities')}}</li>
                                </ul>
                            </div>
                            <div>
                                <strong>{{trans('project::me.Individual Performance In Project')}}</strong> {{trans('project::me.The average of the individual indices in the project')}}
                                <ul>
                                    <li>{{trans('project::me.Feedbacks from clients')}}</li>
                                    <li>{{trans('project::me.Ensure performance')}}</li>
                                    <li>{{trans('project::me.Ensure progress')}}</li>
                                    <li>{{trans('project::me.Complying with procedures')}}</li>
                                    <li>{{trans('project::me.Teamwork skills')}}</li>
                                </ul>
                            </div>
                            <div>
                                <strong>{{trans('project::me.Project point (20%):')}}</strong> {{trans('project::me.Product of project point and project index')}}
                                <ul>
                                    <li>{{trans('project::me.Formula to convert')}} <b>{{trans('project::me.MIN(project point * project index * 5 / 28, 5)')}}</b> {{trans('project::me.28 is the maximum point of the project point')}}</li>
                                </ul>
                            </div>

                            <h4><strong>{{trans('project::me.Ensure following regulations at work')}}</strong></h4>
                            {!! trans('project::me.Subtract points according to the violations') !!}

                            <h4><strong>{{trans('project::me.Contribute to professional activities')}}</strong></h4>
                            {!! trans('project::me.Attend training courses, seminars, workshops...') !!}

                            <h4><strong>{{trans('project::me.Contribute to social activities')}}</strong></h4>
                            {!! trans('project::me.Participate in spiritual activities, associations, journals, extracurricular, charity, social movements ...') !!}

                            <h4><strong>{{trans('project::me.The criteria related to the project')}}</strong></h4>
                            <p>{{trans('project::me.Feedbacks from clients')}}, {{trans('project::me.Ensure performance')}}, {{trans('project::me.Ensure progress')}}, {{trans('project::me.Complying with procedures')}}, {{trans('project::me.Teamwork skills')}}.<br>
                                {{trans('project::me.PM selects the range of points from Unsatisfactory to Excellent')}}:</p>
                            <ul style="padding-left: 20px;">
                                {!! trans('project::me.The criteria related to the project help') !!}
                            </ul>
                            <p> {{trans('project::me.As for Customer Feedback, if there is no response leave the value as "N / A" and will not count towards the overall score')}}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <br>
                    <h4><i>{{trans('project::me.Attention:')}} {{trans('project::me.employees participate in many projects')}}</i></h4>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="box box-info collapsed-box box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title">{{trans('project::me.Instructions for PM')}}</h3>

                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body" style="display: none;">
                                    <p>Link: <a href="{{ route('project::project.eval.index') }}">{{ route('project::project.eval.index') }}</a></p>
                                    <ol>
                                        {!! trans('project::me.Instructions for PM help') !!}
                                    </ol>
                                </div>
                                <!-- /.box-body -->
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="box box-info collapsed-box box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title">{{trans('project::me.Instructions for Group Leader')}}</h3>

                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body" style="display: none;">
                                    <p>Link: <a href="{{ route('project::project.eval.list_by_leader') }}">{{ route('project::project.eval.list_by_leader') }}</a></p>
                                    <p>{{trans('project::me.After PM submit, Group Leader reviews ME.')}}</p>
                                    <ol>
                                        <li>{{trans('project::me.Leave feedback for PM')}}<br>
                                            {{trans('project::me.Group Leader right click at the score need feedback to comment')}}.<br>
                                            {{trans('project::me.Then click Feedback button to send feedback to PM.')}}
                                        </li>
                                        <li>{{trans('project::me.To accept ME')}}<br>
                                            {{trans('project::me.Then click Feedback button to send feedback to PM.')}}.</li>
                                    </ol>
                                </div>
                                <!-- /.box-body -->
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="box box-info collapsed-box box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title">{{trans('project::me.Instructions for Member')}}</h3>

                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body" style="display: none;">
                                    <p>Link: <a href="{{ route('project::project.profile.confirm') }}">{{ route('project::project.profile.confirm') }}</a></p>
                                    <p>{{trans('project::me.After the Group Leader accepts ME, the Member reviews ME')}}.</p>
                                    <ol>
                                        <li>{{trans('project::me.To feedback')}}<br>
                                            {{trans('project::me.Member right-click at the points need feedback to comment')}}.<br>
                                            {{trans('project::me.Then press the Feedback button to send feedback')}}.</li>
                                        <li>{{trans('project::me.To accept ME')}}<br>
                                            {{trans('project::me.Member press the Accept button')}}.</li>
                                    </ol>
                                </div>
                                <!-- /.box-body -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3 class="_me_title">{{trans('project::me.Instructions for making ME Reward')}}</h3>
                <div class="row">
                    <div class="col-md-8">
                        <div class="box box-info collapsed-box box-solid">
                            <div class="box-header with-border">
                                <h4 class="box-title">{{trans('project::me.Instructions for Group Leader')}}</h4>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                                <p>Link: <a href="{{ route('project::me.reward.edit') }}">{{ route('project::me.reward.edit') }}</a></p>
                                <ul>
                                    <li>{{trans('project::me.Select team, month and project type as OSDC to fill the bonus for members')}}{{trans('project::me.(Discuss with Leader, related PM)')}}</li>
                                    <li>{{trans('project::me.Click "Submit" when finished to submit to COO review')}}</li>
                                    <li>{{trans('project::me.When coo complete review, it will have the status of "Approved", You can then export the data to an excel file')}}</li>
                                </ul>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="box box-info collapsed-box box-solid">
                            <div class="box-header with-border">
                                <h4 class="box-title">{{trans('project::me.Instructions for reviewers')}}</h4>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <!-- /.box-tools -->
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                                <p>Link: <a href="{{ route('project::me.reward.review') }}">{{ route('project::me.reward.review') }}</a></p>
                                <p>{{trans('project::me.After the Leader Submit is complete, COO reviews')}}</p>
                                <ul>
                                    <li>{{trans('project::me.Select team, month and project type as OSDC to Approve')}}</li>
                                    <li>{{trans('project::me.Click "Approve" when done and can export the data to excel file')}}</li>
                                </ul>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="{{ asset('project/js/script.js') }}"></script>
@endsection
