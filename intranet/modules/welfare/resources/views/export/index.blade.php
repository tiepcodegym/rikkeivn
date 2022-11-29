<?php
    use Rikkei\Welfare\Model\Event;
?>
<div class="row export-welfare">
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-body">
                <h4>{{ trans('welfare::view.Statistics') }}</h4>
                <div class="form-group">
                    <ul>
                        <li><a href="{{ route('welfare::welfare.export.employee', ['id' => $item['id']]) }}">{{ trans('welfare::view.Participants') }}</a></li>
                        <li><a href="{{ route('welfare::welfare.export.employee.participate', ['id' => $item['id']]) }}">{{ trans('welfare::view.Registered Employees') }}</a></li>
                        <li><a href="{{ route('welfare::welfare.export.employee.joined', ['id' => $item['id']]) }}">{{ trans('welfare::view.Number of Employees registered but did not show up') }}</a></li>
                        @if (isset($item) && $item['is_allow_attachments'] == Event::IS_ATTACHED)
                        <li><a href="{{ route('welfare::welfare.export.employee.attached', ['id' => $item['id']]) }}">{{ trans('welfare::view.Accompanied Person(s)') }}</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-body">
                <h4>{{ trans('welfare::view.Costs') }}</h4>
                <div class="form-group">
                    <ul>
                        <li><a href="{{ route('welfare::welfare.export.fee', ['id' => $item['id'], 'filter' => 'expected']) }}">{{ trans('welfare::view.Estimated Costs') }}</a></li>
                        <li><a href="{{ route('welfare::welfare.export.fee', ['id' => $item['id'], 'filter' => 'actual']) }}">{{ trans('welfare::view.Actual Costs') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
