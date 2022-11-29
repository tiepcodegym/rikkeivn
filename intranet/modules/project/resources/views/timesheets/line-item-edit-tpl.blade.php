<?php
$type = [
    'checkin' => [
        'label' => trans('project::timesheet.checkin'),
        'class' => 'input-time',
    ],
    'checkout' =>  [
        'label' => trans('project::timesheet.checkout'),
        'class' => 'input-time',
    ],
    'break_time' => [
        'label' => trans('project::timesheet.break_time'),
        'class' => 'input-time',
    ],

    'working_hour' => [
        'label' => trans('project::timesheet.working_time'),
        'class' => 'input-float'
    ],
    'ot_hour' => [
        'label' => trans('project::timesheet.ot_time'),
        'class' => 'input-float'
    ],
    'overnight' => [
        'label' => trans('project::timesheet.overnight'),
        'class' => 'input-float'
    ],

    'note' => [
        'label' => trans('project::timesheet.note'),
    ],
    'holiday' => [
        'label' => trans('project::timesheet.holiday'),
    ]
]
?>

<div style="margin-bottom: 10px" class="line-item" data-id="{{$data['id']}}">
    <div class="item-header bg-info {{$data['line_item_id']}}">
        <button type="button" class="btn btn-primary btn-sm btn-sync-timesheet" data-line-id="{{$data['line_item_id']}}"> {{ trans('project::timesheet.sync_timesheet') }}</button>&nbsp;&nbsp; - &nbsp;&nbsp;
        <div class="form-group form-inline" style="margin-bottom: 0">
            <strong>{{ trans('project::timesheet.division') }}</strong>
            {{ Form::select("line_item[{$data['line_item_id']}][division_id]", ['' => '--- Select ---'] + $teams, $data['division_id'], ['class' => "form-control division-{$data['line_item_id']}", 'data-select2-dom' => '1','data-select2-search' => '1']) }}
        </div>
        &nbsp;&nbsp; - &nbsp;&nbsp;
        <strong>{{ $data['name'] }}</strong>
        <a href="#" class="toogle-item" data-toggle="collapse" data-target="#collapse-{{$data['line_item_id']}}">[ - ]</a>
        <button style="float: right" type="button" class="btn btn-danger btn-remove-item"><i class="fa fa-trash"></i></button>
        <span class="message-item"></span>
        {{Form::hidden("line_item[{$data['line_item_id']}][roles]", $data['roles'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][name]", $data['name'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][level]", $data['level'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][working_from]", $data['working_from'], ['class' => 'working_from'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][working_to]", $data['working_to'], ['class' => 'working_to'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][id]", $data['id'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][employee_id]", $data['employee_id'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][min_hour]", $data['min_hour'])}}
        {{Form::hidden("line_item[{$data['line_item_id']}][max_hour]", $data['max_hour'])}}
    </div>

    <div id="collapse-{{$data['line_item_id']}}" class="collapse in">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered" style="margin-bottom: 0">
                    <tr>
                        <td>{{ trans('project::timesheet.min_hour') }}: {{ $data['min_hour'] }} (h)</td>
                        <td>{{ trans('project::timesheet.max_hour') }}: {{ $data['max_hour'] }} (h)</td>
                        <td>{{ trans('project::timesheet.working_from') }}: {{ $data['working_from'] }}</td>
                        <td>{{ trans('project::timesheet.working_to') }}: {{ $data['working_to'] }}</td>
                    </tr>
                    <tr>
                        <td>{{ trans('project::timesheet.total_woking_hour') }}: <span class="total-working-{{ $data['line_item_id'] }}"></span></td>
                        <td>{{ trans('project::timesheet.total_ot_hour') }}: <span class="total-ot-{{ $data['line_item_id'] }}"></span></td>
                        <td>{{ trans('project::timesheet.total_overnight') }}: <span class="total-overnight-{{ $data['line_item_id'] }}"></span></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            {{ trans('project::timesheet.day_of_leave') }}:
                            <input name="line_item[{{ $data['line_item_id'] }}][day_of_leave]" value="{{ $data['day_of_leave'] }}" class="input_day_of_leave" type="text" >
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="{{$data['line_item_id']}}" class="table-responsive tbl-line-item">
            <table class="table table-hover table-striped table-bordered">
                <thead>
                <tr class="success">
                    <th style="width:100px"></th>
                    @foreach($rangeDate as $key => $day)
                        <th>{{ $day }}</th>
                    @endforeach
                    <th></th>
                </tr>
                </thead>
                <tbody>

                @foreach($type as $name => $item)
                    <tr class="row-{{ $name }}">
                        <td>{{ $item['label'] }}</td>
                        @foreach($rangeDate as $key => $day)
                            @php
                                $details = array_first($data['details'], function ($k, $v) use ($key) {
                                    return $key == $v['date'];
                                })
                            @endphp

                            @if(in_array($name, ['checkin', 'checkout', 'breack_time']) && $details[$name] == 0)
                                @php
                                    $details[$name] = ''
                                @endphp
                            @endif

                            @if(in_array($key, $weekends))
                                @php $bg = 'background-color: #ecc489'; $class = 'is-weekend'; @endphp
                            @else
                                @php $bg = ''; $class = ''; @endphp
                            @endif

                            @if($name == 'note')
                                <td style="{{$bg}}">
                                    <a href="#note-modal" class="show-note" data-id="{{$data['line_item_id']}}" data-date="{{$key}}">{{ trans('project::timesheet.show_note') }}</a>
                                    <textarea class="note-{{$key}} input-{{$name}}" name="line_item[{{$data['line_item_id']}}][details][{{$key}}][{{$name}}]" style="display: none">{{ $details[$name] }}</textarea>
                                </td>
                            @elseif($name == 'holiday')
                                <td style="{{$bg}}">
                                    {{ Form::checkbox("line_item[{$data['line_item_id']}][details][{$key}][{$name}]", 1, ($details[$name] == 1)) }}
                                </td>
                            @else
                                <td style="{{$bg}}">
                                    <input name="line_item[{{$data['line_item_id']}}][details][{{$key}}][{{$name}}]" type="text"
                                           value="{{$details[$name]}}" class="input-timesheet input_{{$name}} {{$item['class']}} {{ $class }}">
                                </td>
                            @endif
                        @endforeach
                        <td>
                            @if(!in_array($name, ['note', 'holiday']))
                                <button type="button" class="btn btn-info edit-row" data-row="{{ $name }}" data-type="{{ $item['class'] }}"><i class="fa fa-edit"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <span class="valid-time"></span>
        </div>
    </div>
</div>
