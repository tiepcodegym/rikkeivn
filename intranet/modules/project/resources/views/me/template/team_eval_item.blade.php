<?php 
use Rikkei\Team\View\Permission;
use Rikkei\Project\Model\MeComment;
use Rikkei\Project\Model\MeAttribute;

$employee = $member;
$projectPoint = $item->proj_point;
$canChangePoint = $item->canChangePoint($currUser);
?>

<tr data-eval="{{$item->id}}" data-project="" data-time="{{$item->eval_time}}" data-edit="{{ $canChangePoint }}">
    <td class="fixed-col text-center">
        @if ($canChangePoint)
        <input type="checkbox" class="_check_item" value="{{ $item->id }}">
        @endif
    </td>
    <td class="_employee_id _break_word fixed-col">{{$employee->employee_code}}</td>
    <td class="_break_word fixed-col">{{ ucfirst(preg_replace('/@.*/', '', $employee->email)) }}</td>
    @if (!$attributes->isEmpty())
        <?php
        $checkGroup = MeAttribute::GR_NORMAL;
        $listPoints = $item->listPoints();
        ?>
        @foreach($attributes as $attr)
            @if ($checkGroup != $attr->group)
                <?php
                $checkGroup = $attr->group;
                ?>
                <td class="_avg_rules auto_fill _none"></td>
            @endif
            <td class="point_group {{$item->hasComments($attr->id)}}" data-group="{{$attr->group}}" data-attr="{{$attr->id}}">
                <?php 
                $attrPoint = $item->getAttrPoint($listPoints, $attr->id, $attr->default);         
                $range_min = $attr->range_min;
                $range_max = $attr->range_max;
                $range_step = $attr->range_step;
                ?>
                <div class="input_select">
                    @if (!$canChangePoint)
                        @if ($attr->group == MeAttribute::GR_NORMAL)
                            @if ($attrPoint == MeAttribute::NA)
                            <input type="text" class="form-control _me_attr_point" value="N/A" disabled>
                            @else
                            <input type="number" class="form-control _me_attr_point" data-value="{{ $attrPoint }}"
                                   disabled value="{{ !$attrPoint ?  0 : $attrPoint }}" autocomplete="off"
                                   data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}" min="{{$range_min}}"
                                   max="{{$range_max}}" step="{{$range_step}}" style="padding: 6px;">
                            @endif
                        @else
                            <select disabled class="form-control select-search _me_attr_point minw-110" data-integer="true"
                                    data-value="{{ $attrPoint }}" data-attr="{{$attr->id}}"
                                    data-weight="{{$attr->weight}}" style="padding: 6px;">
                                {!! $item->optionsPoint($attrPoint, $attr->has_na) !!}
                            </select>
                        @endif
                    @else
                        @if ($range_step > 1)
                            <select class="form-control select-search _me_attr_point" data-integer="true"
                                    data-value="{{ $attrPoint }}" data-attr="{{$attr->id}}"
                                    data-weight="{{$attr->weight}}" style="padding: 6px;">
                                @for ($i = $range_min; $i <= $range_max; $i += $range_step)
                                <option value="{{ round($i, 0) }}" @if ($i == $attrPoint) selected @endif >{{ round($i, 0) }}</option>
                                @endfor
                            </select>
                        @elseif ($attr->group != MeAttribute::GR_NORMAL)
                            <select class="form-control select-search _me_attr_point minw-110"
                                    data-value="{{ $attrPoint }}" data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}"
                                    min="{{$range_min}}" max="{{$range_max}}" style="padding: 6px;">
                                {!! $item->optionsPoint($attrPoint, $attr->has_na) !!}
                            </select>
                        @elseif ($attrPoint == MeAttribute::NA)
                            <input type="text" class="form-control minw-50" value="N/A" disabled>
                        @else
                            <input type="number" data-value="{{ $attrPoint }}"
                                    class="form-control _round_value _me_attr_point {{$attr->can_fill ? '' : '_me_attr_time' }}"
                                    @if(filter_var($range_step, FILTER_VALIDATE_INT))
                                        onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" data-integer="true"
                                    @endif
                                    {{ (!$attr->can_fill && $existsMonth) ? 'disabled' : '' }} 
                                    value="{{$attrPoint}}" autocomplete="off"
                                    data-attr="{{$attr->id}}" data-weight="{{$attr->weight}}" min="{{$range_min}}"
                                    max="{{$range_max}}" step="{{$range_step}}" style="padding: 6px;">
                        @endif
                    @endif
                </div>
                @include('project::me.template.comments', ['user' => $currUser, 'item_id' => $item->id, 'attr_id' => $attr->id, 'project_id' => null])
            </td>
        @endforeach
    @endif
    <td class="_pf_person_avg auto_fill _none"></td>
    <td class="_project_point auto_fill">
        <?php
        $arrProjName = array_slice(explode(',', $employee->arr_proj_name), 0, 15);
        $htmlProjName = '';
        if ($arrProjName) {
            foreach ($arrProjName as $name) {
                $htmlProjName .= '<span>'. htmlentities($name) .'</span>, ';
            }
        }
        ?>
        <span @if ($htmlProjName) 
               data-toggle="tooltip" title="{!! trim($htmlProjName, ', ') !!}" data-html="true"
               @endif>{{ $projectPoint }}</span>
    </td>
    <td class="_project_type auto_fill _none">1</td>
    <td class="auto_fill _none"><strong class="_perform_value"></strong></td>
    <td class="_point_avg auto_fill">
        <strong class="_value">{{$item->avg_point ? $item->avg_point : 1}}</strong>
    </td>
    <td class="auto_fill">{{ number_format($item->effort, 1, '.', ',') }}</td>
    <td class="_contribute_val _break_word auto_fill">
        {{$item->contribute_label}}
    </td>
    <td class="note_group {{$item->hasComments()}}">
        @include('project::me.template.comments', ['user' => $currUser, 'item_id' => $item->id, 'attr_id' => null, 'project_id' => null, 'comment_type' => MeComment::TYPE_NOTE])
    </td>
    <td  class="_break_word _status_text auto_fill">{{ $item->status_label }}</td>
</tr>



