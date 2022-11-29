<!-- Project log -->
<style>
    .create-wraper{
        padding: 20px;
        border: 1px solid #ddd;
        margin-top: 75px;
    }
    .fc-row table{
        border-left: inherit;
        border-right: inherit;
        border-bottom: inherit;
        border-top: inherit;
    }
    .fc-widget-content td:first-child, .fc-widget-header th:first-child {
        border-left: 1px solid #ddd;
    }
    .fc-widget-content td:last-child, .fc-widget-header th:last-child {
        border-right: 1px solid #ddd;
    }
    .create-wraper.blurry{
        color: transparent;
        text-shadow: 0 0 5px rgba(0,0,0,0.5);
    }
    .create-wraper.blurry input, .create-wraper.blurry textarea {
        -webkit-filter: blur(3px);
        filter: blur(3px);
        border: 0;
    }
</style>
<?php
$disableEditCalendar = '';
$classHideSubmit = '';
if (!$permissionEditCalendar) {
    $disableEditCalendar = 'disabled';
    $classHideSubmit = 'hide';
}
?>
<div class="row">
    <div class="col-md-8 col-md-offset-2 project-report-left">
        <div id="project_calendar"></div>
    </div>
    <div class="col-md-4 project-report-right hide">
        <div class="create-wraper">
            {{ Form::open(['url' => '/', 'method' => 'POST', 'id' => 'form-calendar-report']) }}
                {{ Form::hidden('employee_id', $currentUser->id) }}
                {{ Form::hidden('project_id', $project->id) }}
                <div class="form-group">
                    <label><strong>{{ trans('project::view.title') }}</strong></label>
                    {{ Form::text('title', null, ['id' => 'report-title', 'class' => 'form-control', 'required', 'maxlength' => '100', $disableEditCalendar]) }}
                </div>
                <div class="form-group">
                    <label><strong>{{ trans('project::view.Date') }}</strong></label>
                    {{ Form::text('date', null, ['id' => 'report-date', 'class' => 'form-control', 'readonly', 'required']) }}
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1"><strong>{{ trans('project::view.Descriptions') }}</strong></label>
                    {{ Form::textarea('description', null, ['id' => 'report-description', 'class' => 'form-control',
                    'rows' => '6', 'required', 'maxlength' => '1000', $disableEditCalendar]) }}
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1"><strong>{{ trans('project::view.project_signal') }}</strong></label>
                    <div class="radio">
                        <label>
                            {{ Form::radio('signal', '1' , true, [$disableEditCalendar]) }}
                            {{ trans('project::view.fine') }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            {{ Form::radio('signal', '2' , false, [$disableEditCalendar]) }}
                            {{ trans('project::view.usually') }}
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            {{ Form::radio('signal', '3' , false, [$disableEditCalendar]) }}
                            {{ trans('project::view.bad') }}
                        </label>
                    </div>
                </div>
                @if($permissionEditCalendar)
                <div class="form-group group-btn-publish">
                    <button type="submit" class="btn btn-primary" id="btn-event-public">{{ trans('project::view.publish') }}</button>
                    <button type="button" class="btn btn-default" id="btn-event-cancel">{{ trans('project::view.cancel') }}</button>
                    <button type="button" class="btn btn-danger hide" id="btn-event-remove">{{ trans('project::view.remove_report') }}</button>
                    <br>
                    <br>
                    <div id="event-alert" class="alert alert-dismissible fade in hide">
                        <span class="close" data-dismiss="alert" aria-label="close">&times;</span>
                        <span class="message"></span>
                    </div>
                </div>
                @endif
            {{ Form::close() }}
        </div>
        <div class="report-overlay"></div>
    </div>
</div>

@section('script')
@parent
<script>
    // Render Calendar report
    var calendarInit = false;
    var calendarEl = document.getElementById('project_calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [ 'interaction', 'dayGrid' ],
        timeZone: 'Asia/Ho_Chi_Minh',
        validRange: {
            start: '{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $project->start_at)->format('Y-m-d') }}',
            end: '{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $project->end_at)->addDay(1)->format('Y-m-d') }}'
        },
        dateClick: function(arg) {
            @if($permissionEditCalendar)
            selectDay(arg.dateStr);
            @endif
        },
        eventClick: function (info) {
            selectEvent(info.event.id);
        },
        // editable: true,
        eventLimit: true, // allow "more" link when too many events
        events: {
            url: '{{ route('project::project.get-calendar-report', ['id' => $project->id]) }}',
            failure: function() {
                // document.getElementById('script-warning').style.display = 'block'
            }
        },
        customRender: true,
        eventRender: function( info ) {
            var el = info.el;
            el.title = info.event.title;
        },
        loading: function(bool) {
            // document.getElementById('loading').style.display = bool ? 'block' : 'none';
        }
    });
    calendar.render();
    $('#menu-tab a').on('shown.bs.tab', function (e) {
        if($(e.target).attr('href') == '#calendar-report' && !calendarInit) {
            calendar.render();
            calendarInit = true;
        }
    });

    var form_calendar = $('#form-calendar-report');
    form_calendar.validate();

    function selectEvent(reportId) {
        $('.create-wraper').addClass('blurry');
        $('#btn-event-remove').addClass('hide');
        $('#btn-event-remove').data('report-id', reportId);
        //display detail
        $('.project-report-left').removeClass('col-md-offset-2', {duration:500});
        $('.project-report-right').removeClass('hide', {duration:500});

        $('#report-title').val('');
        $('#report-description').val('');
        $('#event-alert').addClass('hide');

        $.ajax({
            url: '/project/project-calendar-report/'+ reportId,
            type: 'GET',
            success: function (rs) {
                if (rs.status_code == 200) {

                    if (rs.data.employee_id != {{ \Auth::user()->employee_id }}) {
                        $('#report-title').attr('disabled', 'disabled');
                        $('#report-description').attr('disabled', 'disabled');
                        $('.group-btn-publish').addClass('hide');
                    } else {
                        $('#report-title').removeAttr('disabled', 'disabled');
                        $('#report-description').removeAttr('disabled', 'disabled');
                        $('.group-btn-publish').removeClass('hide');
                    }

                    $('#form-calendar-report').attr('action', '/project/project-calendar-report/update/' + reportId);
                    $('#report-title').val(rs.data.title);
                    $('#report-description').val(rs.data.description);
                    $('#report-date').val(rs.data.date);
                    $('input[name="signal"]').each(function(index) {
                        $(this).removeAttr('checked');
                        var signal = rs.data.signal,
                            this_signl = $(this).val();
                        if( signal === this_signl){
                            $(this).prop('checked', true);
                        }
                    })
                    $('#btn-event-remove').removeClass('hide');
                    form_calendar.valid();
                }
                $('.create-wraper').removeClass('blurry');
            }
        });
    }

    @if($permissionEditCalendar)
    function selectDay(date) {
        $('#report-title').removeAttr('disabled', 'disabled');
        $('#report-description').removeAttr('disabled', 'disabled');
        $('.group-btn-publish').removeClass('hide');

        $('#btn-event-remove').addClass('hide');
        //display detail
        $('.project-report-left').removeClass('col-md-offset-2', {duration:500});
        $('.project-report-right').removeClass('hide', {duration:500});

        $('#report-date').val(date);
        $('#report-title').val('');
        $('#report-description').val('');
        $('#event-alert').addClass('hide');
        $('#form-calendar-report').attr('action', '/project/project-calendar-report/create/{{ $project->id }}')
    }
    $('#form-calendar-report').on('submit', function(e){
        e.preventDefault();
        var valid = form_calendar.valid();
        if(!valid) {
            return;
        }

        $('#event-alert').removeClass('alert-success');
        $('#event-alert').removeClass('alert-danger');
        $('#event-alert').addClass('hide');

        var date = $(this).find('#report-date').val();
        var action = $(this).attr('action');
        var data =  $(this).serialize();
        var signal = $('input[name="signal"]:checked').val();
        var title =  $(this).find('#report-title').val();

        $.ajax({
            url: action,
            type: 'POST',
            data: data,
            success: function (rs) {
                if (rs.status_code == 200) {
                    var colors = {
                        1: 'blue',
                        2: 'orange',
                        3: 'red'
                    }

                    var event = calendar.getEventById(rs.id);
                    if (event !== null) {
                        event.setProp('title', title);
                        event.setProp('color', colors[signal]);
                    } else {
                        calendar.addEvent({
                            'id': rs.id,
                            'title': title,
                            'start': date,
                            'color': colors[signal]
                        })

                        $('#form-calendar-report').attr('action', '/project/project-calendar-report/update/' + rs.id);
                    }
                }
                $('#event-alert .message').html(rs.message)
                $('#event-alert').removeClass('hide');
                $('#event-alert').addClass('alert-'+ rs.status);
                $('#btn-event-public').removeAttr('disabled');
            }
        });
    })
    $('#btn-event-cancel').on('click', function () {
        $('.project-report-left').addClass('col-md-offset-2', {duration:500});
        $('.project-report-right').addClass('hide', {duration:500});
    })
    $('#btn-event-remove').on('click', function() {
        $.confirm({
            title: 'Are you sure you want to delete this report?',
            content: '',
            buttons: {
                confirm: {
                    text: 'Delete',
                    btnClass: 'btn-danger',
                    action: function () {
                        var reportId = $('#btn-event-remove').data('report-id');
                        var event = calendar.getEventById(reportId);
                        $.ajax({
                            url: '/project/delete-report/' + reportId,
                            type: 'DELETE',
                            data: {project_id: projectId},
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function (rs) {
                                if (rs.status_code == 200) {
                                    event.remove();
                                    form_calendar.trigger('reset');
                                    var signal = $('input:radio[name=signal]');
                                    signal.filter('[value=1]').prop('checked', true);
                                    $('#btn-event-remove').addClass('hide');
                                    $('#report-title').val('');
                                    $('#report-description').val('');
                                }

                                $('#event-alert .message').html(rs.message)
                                $('#event-alert').removeClass('hide');
                                $('#event-alert').addClass('alert-'+ rs.status);
                            }
                        });
                    }
                },
                cancel: function () {

                }
            }
        })
    })
    @endif
</script>
@endsection
