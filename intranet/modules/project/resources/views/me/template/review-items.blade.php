<?php
use Rikkei\Project\View\MeView;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeComment;
use Rikkei\Team\View\Permission;


$currentUser = Permission::getInstance()->getEmployee();
$hasPermissDelete = Permission::getInstance()->isAllow('project::me.delete_item');

if ($filterEmployee) {
    $countEmployee = 0;
    $sumPoint = 0;
    $sumDay = 0;
}
?>
@if (!$collectionModel->isEmpty())
    @foreach($collectionModel as $item)
        <?php
        if ($filterEmployee) {
            $countEmployee ++;
        }
        $projectPoint = $item->proj_point;
        //get work dates from concat dates
        $realTime = $item->effort;

        $projIndex = $item->getFactorProjectType($item->project_type);
        //list attr point from concat attr
        $listPoint = MeView::getListPoint($item->point_attrs);
        //list comment class from cancat attr
        $listCommentClass = MeView::getCommentClass($item->cmt_attrs);
        $commentNoteClass = isset($listCommentClass[-1]) ? 'has_comment ' . implode(' ', $listCommentClass[-1]) : '';
        ?>

        <tr data-eval="{{$item->id}}" data-project="{{$item->project_id}}" data-email="{{$item->email}}" data-time="{{$item->eval_time}}">
            <td class="fixed-col text-center">
                @if ($item->status == MeEvaluation::STT_SUBMITED || $item->status == MeEvaluation::STT_CLOSED)
                <input type="checkbox" class="_check_item" value="{{ $item->id }}">
                @endif
            </td>
            <td class="_break_word fixed-col _nowwrap">{{$item->employee_code}}</td>
            <td class="_nowwrap fixed-col date-tooltip">
                {{ $item->eval_month }}
                @if (isset($listRangeMonths[$item->eval_month]))
                <i data-toggle="tooltip" data-placement="bottom" class="fa fa-question-circle"
                   title="{{ $listRangeMonths[$item->eval_month]['start'] . ' : ' . $listRangeMonths[$item->eval_month]['end'] }}" ></i>
                @endif
            </td>
            <td class="_break_word fixed-col employee">{{ ucfirst(preg_replace('/@.*/', '', $item->email)) }}</td>
            <td>
                @if ($item->project_id)
                <a href="{{ route('project::point.edit', ['id' => $item->project_id]) }}" target="_blank" class="project_code_auto">{{ $item->project_name }}</a>
                @else 
                {{ $item->team_name }}
                @endif
            </td>
            <td>{{ MeView::getProjectTypeLabel($item->project_type, $arrayTypeLabel) }}</td>
            @if (!$normalAttrs->isEmpty())
                @foreach($normalAttrs as $attr)
                    <?php 
                    $attr_point = $item->getAttrPoint($listPoint, $attr->id, $attr->default);
                    $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                    ?>
                    <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">
                        <span class="_me_attr_point" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attr_point != MeAttribute::NA ? $attr_point : 'N/A' }}</span>
                        @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->project_id, 'is_leader' => true])
                    </td>
                @endforeach
            @endif
            <td class="_avg_rules auto_fill _none"></td>
            @if (!$performAttrs->isEmpty())
                @foreach($performAttrs as $attr)
                    <?php 
                    $attr_point = isset($listPoint[$attr->id]) ? $listPoint[$attr->id] : round($attr->default);
                    $comment_class = isset($listCommentClass[$attr->id]) ? 'has_comment '.implode(' ', $listCommentClass[$attr->id]) : '';
                    ?>
                    <td class="point_group {{ $comment_class }}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}" title="{{ trans('project::me.Right click to comment') }}">
                        <span class="_me_attr_point _none" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}">{{ $attr_point }}</span>
                        <span>{{ $item->getLabelPerformPoint($attr_point, $attr->has_na) }}</span>

                        @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => $item->project_id, 'is_leader' => true])
                    </td>
                @endforeach
            @endif
            <td class="_pf_person_avg auto_fill _none"></td>
            <td class="_project_point auto_fill">{{$projectPoint}}</td>
            <td class="_project_type auto_fill">{{ $projIndex }}</td>
            <td class="auto_fill _none"><strong class="_perform_value"></strong></td>
            <td class="auto_fill"><strong>{{$item->avg_point}}</strong></td>
            <td class="auto_fill">{{ $realTime }}</td>
            <?php
                if ($filterEmployee) {
                    $realTime = $realTime == 0 ? 1 : $realTime;
                    $sumPoint += $item->avg_point * $realTime;
                    $sumDay += $realTime;
                }
            ?>
            <td class="_contribute_val _break_word auto_fill">
                {{$item->contribute_label}}
            </td>
            <td class="note_group {{ $commentNoteClass }}">
                @include('project::me.template.comments', ['user' => $currentUser, 'item_id' => $item->id, 'attr_id' => null, 'project_id' => $item->project_id, 'is_leader' => true, 'comment_type' => MeComment::TYPE_NOTE])
            </td>
            <td class="_break_word auto_fill status_label">{{ $item->status_label }}</td>
            <td class="dropdown _action_btns _nowwrap">
                @if ($item->status == MeEvaluation::STT_SUBMITED || $item->status == MeEvaluation::STT_CLOSED)
                    {!! Form::open(['route' => ['project::project.eval.leader_update', $item->id], 'method' => 'put', 'class' => 'form-inline no-validate form_item_confirm form_after_submit']) !!}
                        <input type="hidden" name="status" value="{{ MeEvaluation::STT_FEEDBACK }}" />
                        <button type="submit" class="btn-delete _btn_feedback {{ $item->htfb_ids ? '' : 'is-disabled'}}" 
                                data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Feedback')])}}" 
                                data-warning="{{trans('project::me.You must comment before feedback')}}">{{trans('project::me.Feedback')}}</button>
                    {!! Form::close() !!}

                    @if ($item->status != MeEvaluation::STT_CLOSED)
                    {!! Form::open(['route' => ['project::project.eval.leader_update', $item->id], 'method' => 'put', 'class' => 'form-inline no-validate form_item_confirm form_after_submit']) !!}
                        <input type="hidden" name="status" value="{{ MeEvaluation::STT_APPROVED }}" />
                        <button type="submit" class="btn-add _btn_accept" 
                                data-noti="{{trans('project::me.Are you sure you want to do this action', ['action' => trans('project::me.Approve')])}}">{{trans('project::me.Approve')}}</button>
                    {!! Form::close() !!}
                    @endif
                @endif
                <!--delete-->
                @if ($hasPermissDelete)
                {!! Form::open(['route' => ['project::me.delete_item', $item->id], 'method' => 'delete', 'class' => 'form-inline no-validate']) !!}
                    <button type="submit" class="btn-delete delete-confirm _btn_delete" title="{{ trans('project::me.Delete') }}"><i class="fa fa-trash"></i></button>
                {!! Form::close() !!}
                @endif
            </td>
        </tr>
    @endforeach
    @if(isset($countEmployee) && $countEmployee > 1)
    <tr class="month_sumary">
        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 4}}" class="text-right">{{trans('project::me.Summary')}} </td>
        <td colspan="2" class="auto_fill">{{ round($sumPoint/$sumDay, 2) }}</td>
        <td colspan="1" class="auto_fill">{{ MeEvaluation::getContributeLabel(round($sumPoint/$sumDay, 2)) }}</td>
        <td colspan="5"></td>
    </tr>
    @endif
@else
    <tr>
        <td colspan="{{ $normalAttrs->count() + $performAttrs->count() + 14 }}">
            <h3>{{trans('project::me.No result')}}</h3>
        </td>
    </tr>
@endif


