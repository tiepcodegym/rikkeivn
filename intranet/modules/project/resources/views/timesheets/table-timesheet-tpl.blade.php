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
        'class' => 'input-float',
    ],
    'ot_hour' => [
        'label' => trans('project::timesheet.ot_time'),
        'class' => 'input-float',
    ],
    'overnight' => [
        'label' => trans('project::timesheet.overnight'),
        'class' => 'input-float',
    ],

    'note' => [
        'label' => trans('project::timesheet.note'),
    ],
    'holiday' => [
        'label' => trans('project::timesheet.holiday'),
    ]
]
?>

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
                        $class = 'is-weekend';
                    @endphp
                @else
                    @php
                        $bg = '';
                        $class = '';
                    @endphp
                @endif

                @if($name == 'note')
                    <td style="{{$bg}}">
                        <a href="#note-modal" class="show-note" data-id="{{$lineId}}" data-date="{{$key}}">{{ trans('project::timesheet.show_note') }}</a>
                        <textarea class="note-{{$key}}" name="line_item[{{$lineId}}][details][{{$key}}][{{$name}}]" style="display: none"></textarea>
                    </td>
                @elseif($name == 'holiday')
                    <td style="{{$bg}}">
                        {{ Form::checkbox("line_item[{$lineId}][details][{$key}][{$name}]", 1, (array_get($data, $key.'.'.$name) == 1)) }}
                    </td>
                @else
                    <?php
                    if(!empty(array_get($data, $key.'.ct'))) {
                        $class .= ' is_ct';
                    }

                    switch (array_get($data, $key.'.p')){
                        case 1:
                            $tdClass = 'leave-1';
                            $inputClass = 'leave-day leave-day-1';
                            break;
                        case 0.5:
                            $tdClass = 'leave-05';
                            $inputClass = 'leave-day leave-day-05';
                            break;
                        case 0.25:
                            $tdClass = 'leave-025';
                            $inputClass = 'leave-day leave-day-025';
                            break;
                        default;
                            $tdClass = '';
                            $inputClass = '';
                    }

                    ?>
                    <td style="{{$bg}}" class="{{ $tdClass }}">
                        <input name="line_item[{{$lineId}}][details][{{$key}}][{{$name}}]" type="text"
                               value="{{ array_get($data, $key.'.'.$name) }}" class="input-timesheet input_{{$name}} {{$item['class']}} {{ $class }} {{ $inputClass }}">
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