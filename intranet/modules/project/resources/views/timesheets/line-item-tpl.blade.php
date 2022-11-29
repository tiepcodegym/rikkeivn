<?php
$type = [
    'checkin' => [
        'label' => trans('project::timesheet.checkin'),
        'class' => 'input-time',
        'default' => !empty($checkin) ? $checkin : '8:00',
    ],
    'checkout' =>  [
        'label' => trans('project::timesheet.checkout'),
        'class' => 'input-time',
        'default' => !empty($checkout) ? $checkout : '17:30',
    ],
    'break_time' => [
        'label' => trans('project::timesheet.break_time'),
        'class' => 'input-time',
        'default' => '01:30',
    ],

    'working_hour' => [
        'label' => trans('project::timesheet.working_time'),
        'class' => 'input-float',
        'default' => '8',
    ],
    'ot_hour' => [
        'label' => trans('project::timesheet.ot_time'),
        'class' => 'input-float',
        'default' => '0',
    ],
    'overnight' => [
        'label' => trans('project::timesheet.overnight'),
        'class' => 'input-float',
        'default' => '0',
    ],

    'note' => [
        'label' => trans('project::timesheet.note'),
        'default' => '',
    ],
    'holiday' => [
        'label' => trans('project::timesheet.holiday'),
        'default' => '1',
    ]
]
?>

<div style="margin-bottom: 10px" class="line-item">
    <div class="item-header bg-info {{ $data['id'] }}">
        <button type="button" class="btn btn-primary btn-sm btn-sync-timesheet" data-line-id="{{$data['id']}}"> {{ trans('project::timesheet.sync_timesheet') }}</button>&nbsp;&nbsp; - &nbsp;&nbsp;
        <div class="form-group form-inline" style="margin-bottom: 0">
            <strong>{{ trans('project::timesheet.division') }}</strong>
            {{ Form::select("line_item[{$data['id']}][division_id]", ['' => '--- Select ---'] + $teams, null, ['class' => "form-control division-{$data['id']}", 'data-select2-dom' => '1','data-select2-search' => '1']) }}
        </div>
        &nbsp;&nbsp; - &nbsp;&nbsp;
        <strong>{{ $data['name'] }}</strong>

        <a href="#" class="toogle-item" data-toggle="collapse" data-target="#collapse-{{$data['id']}}">[ - ]</a>
        <button style="float: right" type="button" class="btn btn-danger btn-remove-item"><i class="fa fa-trash"></i></button>
        <span class="message-item"></span>
        {{Form::hidden("line_item[{$data['id']}][roles]", $data['roles'])}}
        {{Form::hidden("line_item[{$data['id']}][name]", $data['name'])}}
        {{Form::hidden("line_item[{$data['id']}][level]", $data['level'])}}
        {{Form::hidden("line_item[{$data['id']}][working_from]", $data['working_from'], ['class' => 'working_from'])}}
        {{Form::hidden("line_item[{$data['id']}][working_to]", $data['working_to'], ['class' => 'working_to'])}}
        {{Form::hidden("line_item[{$data['id']}][min_hour]", $data['min_hour'])}}
        {{Form::hidden("line_item[{$data['id']}][max_hour]", $data['max_hour'])}}
        {{Form::hidden("line_item[{$data['id']}][employee_id]", null)}}
    </div>

    <div id="collapse-{{$data['id']}}" class="collapse in">
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
                        <td>{{ trans('project::timesheet.total_woking_hour') }}: <span class="total-working-{{ $data['id'] }}"></span></td>
                        <td>{{ trans('project::timesheet.total_ot_hour') }}: <span class="total-ot-{{ $data['id'] }}"></span></td>
                        <td>{{ trans('project::timesheet.total_overnight') }}: <span class="total-overnight-{{ $data['id'] }}"></span></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            {{ trans('project::timesheet.day_of_leave') }}:
                            <input name="line_item[{{ $data['id'] }}][day_of_leave]" value="0" class="input_day_of_leave" type="text" >
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="{{$data['id']}}" class="table-responsive tbl-line-item">
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
                            @if(in_array($key, $weekends))
                                @php
                                    $bg = 'background-color: #ecc489';
                                    $default = '';
                                    $class = 'is-weekend';
                                @endphp
                            @else
                                @php
                                    $default = $item['default'];
                                    $bg = '';
                                    $class = '';
                                @endphp
                            @endif

                            @if($name == 'note')
                                <td style="{{$bg}}">
                                    <a href="#note-modal" class="show-note" data-id="{{$data['id']}}" data-date="{{$key}}">{{ trans('project::timesheet.show_note') }}</a>
                                    <textarea class="note-{{$key}} input_{{$name}}" name="line_item[{{$data['id']}}][details][{{$key}}][{{$name}}]" style="display: none"></textarea>
                                </td>
                            @elseif($name == 'holiday')
                                <td style="{{$bg}}">
                                    {{ Form::checkbox("line_item[{$data['id']}][details][{$key}][{$name}]", 1, false) }}
                                </td>
                            @else
                                <td style="{{$bg}}">
                                    <input name="line_item[{{$data['id']}}][details][{{$key}}][{{$name}}]" type="text"
                                           value="{{$default}}" class="input-timesheet input_{{$name}} {{ $item['class'] }} {{$class}}">
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
