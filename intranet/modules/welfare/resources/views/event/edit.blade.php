@extends('layouts.default')
<?php
use Rikkei\Core\View\CoreUrl;

?>
@section('title')
    @if(isset($item) && $item)
        {{ trans('welfare::view.Edit Welfare') }}
    @else
        {{ trans('welfare::view.Create Welfare') }}
    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_news/css/news.css') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker3.min.css" />
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_welfare/css/style.css') }}"/>
@endsection

@section('content')
    <?php

    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Welfare\Model\FormImplements;
    use Rikkei\Welfare\Model\GroupEvent;
    use Rikkei\Welfare\Model\PurposeEvent;
    use Rikkei\Welfare\Model\Event;
    use Rikkei\Team\View\Permission;
    use Carbon\Carbon;

    $groupevent = GroupEvent::orderBy('created_at', 'desc')->get();
    $purposes = PurposeEvent::orderBy('created_at', 'desc')->get();
    $formimp = FormImplements::optionWelImple();
    $status = Event::getOptionStatus();
    $permision = Permission::getInstance()->isAllow('welfare::welfare.event.save');
    if (isset($item)) {
        $links = '<p>' . trans("welfare::view.Link to join the event") . ': <a href="' . route("welfare::welfare.confirm.welfare", $item->id) . '" target="_blank" >' . route("welfare::welfare.confirm.welfare", $item['id']) . '</a></p>';
    }
    ?>
    <div class="row button-top event-header">
        <div class="col-md-6">
            <div class="btn-group event-status-header">
                <button type="button" class="btn btn-success" >&nbsp;</button>
                <button type="button" class="btn btn-success dropdown-toggle"
                        data-toggle="dropdown" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    @if(isset($status))
                    @foreach( $status as $key => $value )
                    <li><a href="javascript:void(0)">{{$value}}</a></li>
                    @endforeach
                    @endif
                </ul>
            </div>
        </div>
        <div class="align-right col-md-6">
            <button id="btn-submit-fake" type="" class="btn btn-primary" name="submit" @if(isset($item)) style="display: none" @endif>
                {{ trans('welfare::view.Save') }}
                <span class="_uploading hidden" id="uploading"><i class="fa fa-spin fa-refresh"></i></span>
            </button>
        </div>
    </div>
    <div class="welfare-content">
        <form action="{{ URL::route('welfare::welfare.event.save') }}" method="post" id="form-event-info"
              enctype="multipart/form-data" autocomplete="off" novalidate="novalidate">
              {{ csrf_field() }}
            <div class="text-right col-md-12">
                <div class="pull-right">
                    <button id="btn-submit" type="submit" class="btn btn-primary" name="submit">
                        {{ trans('welfare::view.Save') }}
                        <span class="_uploading hidden" id="uploading"><i class="fa fa-spin fa-refresh"></i></span>
                    </button>
                </div>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="event[id]" @if(isset($item)) value="{{$item['id']}}" @endif>
            <div class="container nav-tabs-custom" id="event_tab">
                <ul class="nav nav-tabs" id="myTab">
                    <li class="active">
                        <a data-toggle="tab" href="#general">{{ trans('welfare::view.General Information') }}</a>
                    </li>
                    @if(isset($item))
                        <li>
                            <a data-toggle="tab" href="#participants"
                            @if(isset($item)) dataurl="{!! route('welfare::welfare.datatables.data') !!}/{{$item['id']}}" @endif>
                            {{ trans('welfare::view.Participants') }}</a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#organizer">{{ trans('welfare::view.Organizer') }}</a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#partner">{{ trans('welfare::view.Partner implementation') }}</a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#wel-file">{{ trans('welfare::view.Attachments') }}</a>
                        </li>
                        <li>
                            <a class="users-table-emp" data-toggle="pill" href="#employee"
                               @if(isset($item)) dataurl="{!! route('welfare::welfare.datatables.data') !!}/{{$item['id']}}" @endif>{{ trans('welfare::view.Welfare Employee') }}</a>
                        </li>
                        @if (isset($item) && $item->is_allow_attachments == Event::IS_ATTACHED)
                        <li>
                            <a class="users-table-emp-att" data-toggle="pill" href="#employeeAtt"
                               @if(isset($item)) dataurl="{!! route('welfare::welfare.RelativeAttach.data')!!}/{{$item['id']}}" @endif>{{ trans('welfare::view.Welfare Employee Attach') }}</a>
                        </li>
                        @endif
                        <li>
                            <a class="users-table-emp-att" data-toggle="pill"
                               href="#wel_fee_more">{{ trans('welfare::view.Extra_payments') }}</a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#send_mail">{{ trans('welfare::view.Send Mail') }}</a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#export">{{ trans('welfare::view.Export') }}</a>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div id="general" class="tab-pane fade in active @if(!$permision) disabledbutton @endif">
                        @include('welfare::event.include.index')
                    </div>
                    @if(isset($item))
                        <div id="participants" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::participant.index')
                        </div>
                        <div id="organizer" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::organizer.index')
                        </div>
                        <div id="partner" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::partner.index')
                        </div>
                        <div id="wel-file" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::event.include.file')
                        </div>
                        <div id="employee" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::wel_employee.index ')
                        </div>
                        @if (isset($item) && $item->is_allow_attachments == Event::IS_ATTACHED)
                        <div id="employeeAtt" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::welRelativeAttachs.index')
                        </div>
                        @endif
                        <div id="send_mail" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::event.mail.index')
                        </div>
                        <div id="export" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::export.index')
                        </div>
                        <div id="wel_fee_more" class="tab-pane fade @if(!$permision) disabledbutton @endif">
                            @include('welfare::wel_fee_more.index')
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
    @include('welfare::event.include.group_event')
    @include('welfare::event.include.wel_purposes')
    @include('welfare::event.include.wel_form_implements')
    @if(isset($item))
        @include('welfare::partner.create')
        @include('welfare::partner.group.index')
        @if ($item->is_allow_attachments == Event::IS_ATTACHED)
            @include('welfare::welRelativeAttachs.edit')
        @endif
    @endif
    <div class="modal fade modal-danger" id="select-null-data" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">{{ trans('welfare::view.warning modal title') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-delete-confirm">
                    <p class="modal-title"
                       id="exampleModalLabel">{{ trans('welfare::view.warning modal not choose') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline"
                            data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @include('welfare::event.include.popup')
@endsection

@section('script')
    <script type="text/javascript">
        var textRequired = '{{ trans('core::view.This field is required') }}';
        var errValidNumber = '{{ trans('core::view.Please enter a valid number') }}';
        var errValidEmail = '{{ trans('core::view.Please enter a valid email address') }}';
        var errValidDate = '{{ trans('welfare::view.The value is not a valid date') }}';
        var errAfterEndAtExec = '{{ trans('welfare::view.Validate after end_at_exec date message') }}';
        var errBeforeEndAtExec = '{{ trans('welfare::view.Validate before date message end') }}';
        var errValidPhone = '{{ trans('welfare::view.Please enter a valid phone number') }}';
        var msgConfirmSendMail = '{{ trans('welfare::view.You want to send a reminder email to the unregistered members of the event?') }}';
        var errLengthInput = {
            'length5' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 5]) }}',
            'length12' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 12]) }}',
            'length15' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 15]) }}',
            'length19' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 19]) }}',
            'length50' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 50]) }}',
            'length255' : '{{ trans('core::view.This field not be greater than :number characters', ['number' => 255]) }}',
        };
        var titleModal = '{{ trans('sales::view.Notification') }}';
        var allowRegisOn = '{{ trans('welfare::view.Register Online') }}';
        var notAllowRegisOn = '{{ trans('welfare::view.Events do not allow online registration') }}';
        var saveSuccess = '{{ trans('core::message.Save success') }}';
        var _token = '{{ csrf_token() }}';
        var textConfirm = {
            'confirm': '{{ trans('welfare::message.Are you sure you want to confirm this employee?') }}',
            'cancelConfirm': '{{ trans('welfare::message.Are you sure you want to delete confirmation of this employee?') }}',
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.15/pagination/input.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_welfare/js/script.js') }}"></script>
    <script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_welfare/js/script_attach_employee.js') }}"></script>
    <script type="text/javascript">
        var Extra = [];
        var dataLang = {
            'sProcessing': '{{ trans('welfare::view.sProcessing') }}',
            'sLengthMenu': '{{ trans('welfare::view.sLengthMenu') }}',
            'sZeroRecords': '{{ trans('welfare::view.sZeroRecords') }}',
            'sInfo': '{{ trans('welfare::view.sInfo') }}',
            'sInfoEmpty': '{{ trans('welfare::view.sInfoEmpty') }}',
            'sInfoFiltered': '',
            'sInfoPostFix': '',
            'sSearch': '{{ trans('welfare::view.sSearch') }}',
            'sUrl': '',
            'oPaginate': {
                'sFirst': '<i class="fa fa-angle-double-left"></i>',
                'sPrevious': '<i class="fa fa-arrow-left"></i>',
                'sNext': '<i class="fa fa-arrow-right"></i>',
                'sLast': '<i class="fa fa-angle-double-right"></i>',
            }
        };

        jQuery(document).ready(function () {
            CKEDITOR.config.height = 182;
            RKfuncion.CKEditor.init(['description']);
            var counter = 1;
            $('#users-table-emp thead').append($('#users-table-emp_2 thead').html());

            @if(isset($item))
            function tableUserTable() {
                var empTable = $('#users-table-emp').DataTable({
                    processing: false,
                    retrieve: true,
                    "bLengthChange": false,
                    "oLanguage": dataLang,
                    pagingType: "full_numbers",
                    "pagingType": "input",
                    ajax: '{!! route('welfare::welfare.datatables.data') !!}/{{$item['id']}}',
                    columns: [
                        {data: 'empcCode', name: 'empcCode'},
                        {data: 'empname', name: 'empname'},
                        {data: 'role', name: 'role'},
                        {data: 'depname', name: 'depname'},
                        {data: 'confirm', name: 'confirm', "orderable": false},
                        {data: 'joined', name: 'joined', "orderable": false},
                        {data: 'empFee', name: 'empFee'},
                        {data: 'comFee', name: 'comFee'},
                        {data: 'action', name: 'action'}
                    ],
                    "footerCallback": function (row, data, start, end, display) {
                        var api = this.api(), data;
                        // Remove the formatting to get integer data for summation
                        var intVal = function (i) {
                            return typeof i === 'string' ?
                                i.replace(/[\$,]/g, '') * 1 :
                                typeof i === 'number' ?
                                    i : 0;
                        };

                        // Total over all pages
                        total = api
                            .column(8)
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Total over this page
                        pageTotal = api
                            .column(8, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Update footer
                        $(api.column(8).footer()).html(
                            pageTotal.toLocaleString("en-US") + ' / (' + total.toLocaleString("en-US") + ' VND total)'
                        );
                    }
                });
                function getUrlFilterEmp() {
                    var url = '{!! route('welfare::welfare.datatables.data') !!}/{{$item['id']}}';
                    url += '?emp_code=' + $('#users-table-emp .filter-empcCode').val();
                    url += '&emp_name=' + $('#users-table-emp .filter-empname').val();
                    url += '&is_confirm=' + $('#users-table-emp .filter-confirm').val();
                    url += '&is_joined=' + $('#users-table-emp .filter-joined').val();
                    return url;
                }
                $('#users-table-emp thead tr.row-filter input[type=text]').keyup(function (e) {
                    var code = e.keyCode || e.which;
                    if(code === 13) {
                        empTable.ajax.url( getUrlFilterEmp() ).load();
                    }
                });
                $('#users-table-emp thead tr.row-filter select').change(function (e) {
                    empTable.ajax.url( getUrlFilterEmp() ).load()
                });
            };

            tableUserTable();

            $(document).on('click', '#addRow', function () {
                $('#table_wel_fee_more .dataTables_empty').addClass('hidden');
                $('#table_wel_fee_more').append('<tr role="row" class="odd row' + counter + '" id="row' + counter + '">' +
                    '<td class="sorting_1">'+
                        '<input type="text" name="wel_fee_more[' + counter + '][name]" fieldname="Extra_payments_name" class="form-control" placeholder="{{ trans('welfare::view.Name of expenditure') }}">'+
                        '<label id="error-extra-name" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>' +
                        '<label id="error-extra-name-unique" class="error hidden" style="color: red;"></label>' +
                    '</td>' +
                    '<td>'+
                        '<input type="text" name="wel_fee_more[' + counter + '][source]" fieldname="Extra_payments_src" class="form-control" placeholder="{{ trans('welfare::view.Source') }}">'+
                    '</td>' +
                    '<td>'+
                        '<input type="text" name="wel_fee_more[' + counter + '][cost]" fieldname="Extra_payments_budget" class="form-control" placeholder="0.00" style="text-align: right;" onkeyup="onKeyUp(this)" maxlength="19" style="text-align: right;">'+
                        '<label id="error-extra-budget" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>' +
                        '<label id="error-extra-budget-number" class="error hidden" style="color: red;">{{ trans('core::view.This field not be greater than :number characters', ['number' => 19]) }}</label>' +
                    '</td>' +
                    '<td><input type="hidden" fieldname="wel_id" value="{{$item['id']}}">' +
                    '<button type="button" route="{!! route('welfare::welfare.WelFreMore.save') !!}" class="btn btn-info save-welfee-more" id="save-welfee-more">' +
                    '<i class="fa fa-check-square-o" aria-hidden="true"></i></button>' + '' +
                    '<button type="button" class="delete-row-btn btn-delete" rowid="' + counter + '"><span><i class="fa fa-times"></i></span></button>' +
                    '</td>' +
                    '</tr>');
                counter++;
                $(this).hide();
            });

            $(document).on('click','.btn_wel_fee_more_update', function () {
                $(this).closest('tr').removeClass('disableMouse');
                var route = $(this).attr('route');
                var id = $(this).closest('tr').attr('id');
                var wel_id = $(this).closest('tr').attr('wel_id');
                Extra[id] = {
                    Extra_payments_name : $(this).closest('tr').children()[0].innerText,
                    Extra_payments_src : $(this).closest('tr').children()[1].innerText,
                    Extra_payments_budget : $(this).closest('tr').children()[2].innerText,
                }
                $(this).closest('tr').empty().append(
                    '<td class="sorting_1">'+
                        '<input type="text" fieldname="Extra_payments_name" class="form-control" placeholder="Tên khoản chi" value="' + Extra[id].Extra_payments_name + '">' +
                        '<label id="error-extra-name" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>' +
                        '<label id="error-extra-name-unique" class="error hidden" style="color: red;"></label>' +
                    '</td>' +
                    '<td><input type="text"  fieldname="Extra_payments_src" value="' + Extra[id].Extra_payments_src + '" class="form-control" placeholder="Nguồn chi"></td>' +
                    '<td>'+
                        '<input type="text" fieldname="Extra_payments_budget" value="' + Extra[id].Extra_payments_budget + '" class="form-control" style="text-align: right;" placeholder="0.00" onkeyup="onKeyUp(this)" maxlength="19">'+
                        '<label id="error-extra-budget" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</label>' +
                        '<label id="error-extra-budget-number" class="error hidden" style="color: red;">{{ trans('core::view.This field not be greater than :number characters', ['number' => 19]) }}</label>' +
                    '</td>' +
                    '<td>'+
                    '<input type="hidden" fieldname="wel_id" value="' + wel_id + '">' +
                    '<button type="button" route="' + route + '" class="btn btn-info save-welfee-more" id="save-welfee-more">' +
                    '<i class="fa fa-check-square-o" aria-hidden="true"></i></button>' + '' +
                    '<button type="button" class="btn-destroy-edit btn-delete"><span><i class="fa fa-times"></i></span></button>' +
                    '</td>'+
                    '<td>'+
                    '<input type="hidden" fieldname="id" value="' + id + '">'+
                    '</td>');
            });
            $(document).on('click', '.btn-destroy-edit', function() {
                $('#table_wel_fee_more thead, tbody').find('tr').removeClass('disableMouse');
                $('#wel_fee_more').find('#addRow').show();
                var route = $(this).closest('td').find('button.save-welfee-more').attr('route');
                var id = $(this).closest('tr').attr('id');
                var wel_id = $(this).closest('td').find('input[fieldname="wel_id"]').val();
                var deleteUrl = "{{ route('welfare::welfare.WelFreMore.delete') }}" + "/" + id;
                $(this).closest('tr').empty().append(
                        '<td class="sorting_1">'+ Extra[id].Extra_payments_name +'</td>'+
                        '<td>'+ Extra[id].Extra_payments_src +'</td>' +
                        '<td>'+ Extra[id].Extra_payments_budget + '</td>'+
                        '<td>'+
                            '<button type="button" id="btn_wel_fee_more_update" class="btn btn-info btn-edit btn_wel_fee_more_update" style="margin-right: 4px;" route="{{ route('welfare::welfare.WelFreMore.save') }}" data-id="'+ id +'">'+
                            '<span><i class="fa fa-edit"></i></span ></button>'+
                            '<button type="button" class="delete-modal-item btn btn-danger btn-delete" modal="modal-delete-wel-fee-more" data-id="' + id +'" route="'+ deleteUrl +'">'+
                            '<span><i class="fa fa-trash"></i></span ></button>'+
                        '</td>'
                );
            });


            editor = CKEDITOR.replace('content');
            CKEDITOR.config.title = false;
            CKEDITOR.config.enterMode = CKEDITOR.ENTER_BR;
            CKEDITOR.instances.content.setData('{!! $links !!}');
            editor.on('change', function () {
                $('#error-content').addClass('hidden');
            });
            $('.preview-email-event').on('click', function () {
                var content = CKEDITOR.instances['content'].getData();
                $.ajax({
                    type: "POST",
                    url: '/welfare/preview-mail',
                    data: {
                        'content': content,
                        '_token': $('input[name=_token]').val(),
                    },
                    success: function (data) {
                        $('#modal-preview-email').on('show.bs.modal', function () {
                            var frame = document.getElementById("ifr-preview-mail"),
                                frameDoc = frame.contentDocument || frame.contentWindow.document;
                            frameDoc.documentElement.innerHTML = "";
                            $('#ifr-preview-mail').contents().find('body').append(data.html);
                        }).modal('show');
                    }
                });
            });

            //tab participants---------------------------------------------------------------------
            $(document).ready(function(){
                url = '{{route("welfare::welfare.edit.getEmployee")}}'+'/1'+'/{{ $item->id }}';
                initTable();
            });

            //get employee by team
            $(document).on('click','.team-item',function(e){
                e.preventDefault();
                id = $(this).find('a').data('id');
                url = '{{route("welfare::welfare.edit.getEmployee")}}'+'/'+id+'/{{ $item->id }}';
                tableActions();
                initTable();
                $('.check-all-team').attr('value',id);
            });

            function initTable() {
                return  $('#table-employee').DataTable({
                    serverSide: true,
                    ajax: url,
                    retrieve: true,
                    "bLengthChange": false,
                    "oLanguage": dataLang,
                    pagingType: "full_numbers",
                    "pagingType": "input",
                    columns: [
                        {data: 'action', name: 'action',searchable: false, sortable: false},
                        {data: 'employee_code', name: 'employee_code'},
                        {data: 'name', name: 'name'},
                        {data: 'role', name: 'roles.role'},
                        {data: 'mobile_phone', name: 'mobile_phone'},
                        {data: 'email', name: 'email'},
                    ]
                });
            }

            function tableActions () {
                table = initTable();
                table.destroy();
            }
            data_employee = {!! json_encode($idEmployees,JSON_NUMERIC_CHECK) !!};

            // check all record on table
            $('.check_all').on('change', function () {

                if ($(this).is(':checked')) {
                    $('.check_item').prop('checked', true);
                    $('.check_item').each(function(){
                        var value = $(this).data('id');
                        if(jQuery.inArray(value,data_employee) == -1) {
                            addValue($(this));
                        }
                    });
                } else {
                    $('.check_item').prop('checked', false);
                     $('.check_item').each(function(){
                        var value = $(this).data('id');
                        remove(data_employee,parseInt(value));
                    });
                }
            });

            //is check all checked?
            $('body').on('change', '.check_item', function(){
                checkAll();
            });

            //add or remove one row
            $(document).on('click','.check_item',function() {
                addValue($(this));
            });

            // check employee checked after load data
            $('#table-employee').on('draw.dt', function () {
                checkedEmployee();
                checkAll();
            });

            //check all team action
            $(document).on('click','.check-all-team',function(e){
                e.preventDefault();
                var idTeam = $(this).attr("value");
                var type = $(this).data("type");
                if(type == true ) {
                    $('.check_item').prop('checked', true);
                    $('.check_all').prop('checked',true);
                } else {
                    $('.check_item').prop('checked', false);
                    $('.check_all').prop('checked',false);
                }
                $.ajax({
                    headers: {
                          'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type:"post",
                    url: '{{route("welfare::welfare.get.Team.Employee")}}',
                    data:{
                        'idTeam': idTeam,
                        'type': type,
                    },
                    success:function(data){
                        if(data['type'] == "true") {
                            $.each(data['employee'], function( index, value ) {
                                if(jQuery.inArray(parseInt(value),data_employee) == -1) {
                                    data_employee.push(parseInt(value));
                                }
                            });
                        } else {
                            $.each(data['employee'], function( index, value ) {
                                remove(data_employee,parseInt(value));
                            });
                        }
                    },
                    cache:false,
                    dataType: 'json'
                });
            });

            //function add value
            function addValue(thisValue) {
                var value = thisValue.data('id');
                if (thisValue.is(':checked')) {
                    data_employee.push(parseInt(value));
                } else {
                    remove(data_employee,parseInt(value));
                }
            }

            //function remove value
            function remove(array, element) {
                const index = array.indexOf(element);
                if (index !== -1) {
                    array.splice(index, 1);
                }
            }

            function checkAll() {
                var item_length = $('.check_item').length;
                if($('.check_item:checked').length === item_length
                    && item_length !== 0){
                    $('.check_all').prop('checked', true);
                } else{
                    $('.check_all').prop('checked', false);
                }
            }

            function checkedEmployee(){
                $('.check_item').each(function(){
                    var idEmployee = $(this).data('id');
                    checked = jQuery.inArray(idEmployee,data_employee);
                    if(checked != -1) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked',false);
                    }
                });
            }

            //save employee join
            $(document).on('click','.btn-save-employee',function(e){
                e.preventDefault();
                $('#disable-btn-save-employee').removeClass('hidden');
                $(this).attr('disabled','disabled');
                $.ajax({
                    headers: {
                          'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type:"post",
                    url: '{{route("welfare::welfare.save.getEmployee")}}',
                    data:{
                        'data':data_employee,
                        'event': {{$item->id}}
                    },
                    success:function(data){
                        var table = $('#users-table-emp').DataTable();
                        table.destroy();
                        tableUserTable();
                        $('.btn-save-employee').removeAttr('disabled');
                        $('#disable-btn-save-employee').addClass('hidden');
                        $('#modal-success-notification .modal-title').html(titleModal);
                        $('#modal-success-notification .text-default').text(saveSuccess);
                        $('#modal-success-notification .text-default').css("padding","10px");
                        $('#modal-success-notification').modal('show');
                        $('#users-table-emp-att').DataTable().ajax.reload();
                        $('#btn-send-mail').data('allow', data.allow);
                    },
                    cache:false,
                    dataType: 'json'
                });
            });
            //change color team pick
            $(document).on('click','.team-item',function(){
                $(".team-tree").find("label.pick-team").removeClass("pick-team");
                $(this).addClass('pick-team');
            });
            $(document).on('ready',function(){
                $('a[data-id="1"]').parent().addClass('pick-team');
            });
            //rigister online
            $(document).on('change','#is_register_online',function() {
                if($(this).is(':checked')) {
                    is_register_online = 1;
                } else {
                    is_register_online = 0;
                }
                $.ajax({
                    headers: {
                          'X-CSRF-Token': $('input[name="_token"]').val()
                    },
                    type: 'post',
                    url: '{{ route("welfare::welfare.register.online") }}',
                    data: {'is_register_online': is_register_online,
                        'welfare_id': {{$item->id}}
                    },
                    success: function (data) {
                        $('#modal-success-notification .modal-title').html(titleModal);
                        $('#modal-success-notification .text-default').css('padding','10px');
                        if(data['status'] == true) {
                            if(data['type'] == "1") {
                                $('#modal-success-notification .text-default').html(allowRegisOn);
                            } else {
                                $('#modal-success-notification .text-default').html(notAllowRegisOn);
                            }
                        } else {
                                $('#modal-success-notification .text-default').text('Có lỗi xảy ra vui lòng thử lại');
                        }
                        $('#modal-success-notification').modal('show');
                    }
                });

            });
            // tab employee add review delete --------------------------
            // event click mouse right menu
            $(function() {
                var $contextMenu = $("#contextMenu");
                $("body").on("contextmenu", "#users-table-emp tbody tr", function(e) {
                $('#users-table-emp tbody tr').css('background-color', '#fff');
                idEmployee =  $(this).attr('employee_id');
                nameEmployee = $(this).find("td:nth-child(2)").text();
                $(this).css('background-color', '#ccc');
                    $contextMenu.css({
                        display: "block",
                        left: e.pageX,
                        top: e.pageY
                    });
                    return false;
                });

                $contextMenu.on("click", function() {
                    $contextMenu.hide();
                });
                // show popup add new employee attach
                $('#add-person').on('click',function() {

                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type: 'post',
                        url: '{{ route("welfare::welfare.edit.AttachEmployee") }}',
                        data: {'idEmployee': idEmployee,
                                'welfare_id': {{$item->id}}
                        },
                        success: function (data) {
                            $('#modal-content-attach-employee').html(data);
                            $('#date-attach-employee').datepicker({
                                autoclose: true,
                                format: 'yyyy-mm-dd',
                                weekStart: 1,
                                todayHighlight: true,
                            });
                            $('#modal-add-attach-person').modal('show');

                        }
                    });

                });
                //delete employee join event
                var urlDeleteEmployeeJoin;
                $('#cancel-person').on('click',function() {
                    urlDeleteEmployeeJoin =
                        '{!! route("welfare::welfare.delete.getEmployee") !!}'+'/'+{{$item->id}}+'/'+idEmployee;
                    $('.modal-noti-dange').attr('id','modal-cancel-employee');
                    $('.modal-noti-dange #content-noti').text('Xác nhận xóa');
                    $('.modal-noti-dange').modal('show');
                });

                $(document).on('click','#modal-cancel-employee .btn-cancel-employee',function() {
                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type:"get",
                        url: urlDeleteEmployeeJoin,
                        success:function(data){
                            if(data.status == true) {
                                $('#modal-cancel-employee').modal('hide');
                                $('#users-table-emp').DataTable().ajax.reload(null,false);
                                $('#users-table-emp-att').DataTable().ajax.reload();

                            } else {
                                $('#modal-cancel-employee').modal('hide');
                                $('#modal-cancel-employee').find('#content-noti').text('Có lỗi xảy ra vui lòng thử lại');
                                $('#modal-cancel-employee').modal('show');

                            }
                        },
                        cache:false,
                        dataType: 'json'
                    });
                });

                // review person attach employee
                $(document).on('click','#review-person',function(){
                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type:"post",
                        url: '{{ route("welfare::welfare.review.EmployeeAttach") }}',
                        data:{
                            'idEmployee': idEmployee,
                            'event': {{$item->id}}
                        },
                        success:function(data){
                            $('#content-table-employee-attach').html(data);
                            $('#modal-review-person').modal('show');
                        },
                        cache:false,
                        dataType: 'json'
                    });
                });
                // dele person attach employee
                $(document).on('click','.btn-delete-employee-attach',function(){
                    $('.modal-noti-dange').modal('show');
                    $('.modal-noti-dange').attr('id','delete-employee-attach');
                    $('.modal-noti-dange #content-noti').html('<span>Bạn muốn xóa người đính kèm ?</span>')
                    idDeletePersonAttach = $(this).data('id');
                    thisDelete = $(this).parent().parent();
                });
                //Edit show cost employee view edit
                $(document).on('click','#edit-person',function() {
                    $('#id-employee').attr('value',idEmployee);
                    $('#name-employee').attr('value',nameEmployee);
                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type:"post",
                        url: '{{ route("welfare::welfare.show.employee.cost") }}',
                        data:{
                            'idEmployee': idEmployee,
                            'event': {{$item->id}}
                        },
                        success:function(data){
                            if($.isEmptyObject(data)) {
                                $('#modal-success-notification .text-default').text('Nhân viên chưa xác nhận tham gia');
                                $('#modal-success-notification .modal-title').text(titleModal);
                                $('#modal-success-notification .text-default').css('padding','5px');
                                $('#modal-success-notification').modal('show');
                            } else {
                                $('#welfare-content-popup').html(data);
                                $('#modal-edit-person').modal('show');
                                $('.convert_format_number').on('keyup',function(){
                                    var test = $(this).val();
                                    $(this).val(formatNumber(test));
                                });
                            }
                        },
                        cache:false,
                        dataType: 'json'
                    });
                });
                //Save cost employee event
                $(document).on('click','#save-cost-event',function(){
                    var emFee = $('#emFee').val();
                    var comFee = $('#comFee').val();
                    if(emFee.length <= 11 && comFee.length <= 11) {
                        $.ajax({
                            headers: {
                                  'X-CSRF-Token': $('input[name="_token"]').val()
                            },
                            type:"post",
                            url: '{{ route("welfare::welfare.save.employee.cost") }}',
                            data:{
                                'idEmployee': idEmployee,
                                'event': {{$item->id}},
                                'emFee':emFee,
                                'comFee':comFee
                            },
                            success:function(data){
                                $('#modal-edit-person').modal('hide');
                                $('#users-table-emp').DataTable().ajax.reload(null,false);
                            },
                            cache:false,
                            dataType: 'json'
                        });
                    } else {
                        if(emFee.length > 11) {
                            $('#error-money-emfee-length-9').removeClass('hidden');
                        } else {
                            $('#error-money-emfee-length-9').addClass('hidden');
                        }
                        if(comFee.length > 11) {
                            $('#error-money-comfee-length-9').removeClass('hidden');
                        } else {
                            $('#error-money-comfee-length-9').addClass('hidden');
                        }
                    }
                });

                $(document).on('click','#delete-employee-attach .btn-cancel-employee',function(){

                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type:"post",
                        url: '{{ route("welfare::welfare.relative.attach.delete") }}',
                        data:{
                            'welid': idDeletePersonAttach,
                        },
                        success:function(data){
                            $('.modal-noti-dange').modal('hide');
                            thisDelete.remove();
                            $('#users-table-emp-att').DataTable().ajax.reload(null, false);

                        },
                        cache:false,
                        dataType: 'json'
                    });
                });

                // Show popup edit employee attach
                $(document).on('click','.btn-edit-employee-attach',function(){
                    editEmployeeAttach = $(this).data('id');
                    thisEditEmployeeAttach = $(this);
                    var url = '{{ route("welfare::welfare.edit.AttachEmployee") }}';

                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type: 'post',
                        url: url,
                        data: {'id': editEmployeeAttach},
                        success: function (data) {
                            $('#modal-content-attach-employee').html(data);
                            $('#date-attach-employee').datepicker({
                                autoclose: true,
                                format: 'yyyy-mm-dd',
                                weekStart: 1,
                                todayHighlight: true,
                            });
                            $('#modal-add-attach-person').modal('show');
                        }
                    });

                });

                //Save data employee attach
                $(document).on('submit','#form-submit-add-person',function(e) {
                    e.preventDefault();
                    var form = $('#form-submit-add-person')[0];
                    var formData = new FormData(form);
                    $('#name-error').attr('hidden');
                    var dataName = $(this).find('#name').val();
                    var dataPhone = $(this).find('#tab-employee-phone').val();
                    var dataCMTND = $(this).find('#tab-employee-CMTND').val();
                    var isCmt = $(this).find('input[name="check-cmt"]:checked').val();
                    var feeFavorable = $('#fee_favorable_employee_attach').val();
                    var birthday = $('#date-attach-employee').val();
                    var relativeAttach = $('#show-relative-favorable').val();
                    var checkRelativeAttach = relativeAttach.length;
                    var checkeBirthday = birthday.length;
                    var checkFeeFavorable = feeFavorable.length;
                    var check = dataName.trim().length;
                    var checkPhone = dataPhone.length;
                    var checkCMTND = dataCMTND.length;
                    if(check > 0 && checkPhone < 13 && checkCMTND < 13
                        && checkFeeFavorable > 0 && checkeBirthday > 0
                        && checkRelativeAttach > 0
                        && ((isCmt == 1 && checkCMTND > 0) || isCmt != 1)) {
                        $('#name-error').css('display','none');
                        $(this).find('.btn-save-form').prop('disabled', 'disabled');
                        $.ajax({
                            headers: {
                                  'X-CSRF-Token': $('input[name="_token"]').val()
                            },
                            type:"post",
                            url:'{{route("welfare::welfare.save.Edit.AttachEmployee")}}',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success:function(data){
                                if(data['type'] == 1) {
                                    if (data['status'] == true) {
                                        $('#modal-add-attach-person').modal('hide');
                                        var td = thisEditEmployeeAttach.parent();
                                        td.prev().html(data['data']['phone']);
                                        if(data['data']['gender'] == 1) {
                                            td.prev().prev().html('Nữ');
                                        } else {
                                            td.prev().prev().html('Nam');
                                        }
                                        td.prev().prev().prev().html(data['data']['birthday']);
                                        td.prev().prev().prev().prev().html(data['data']['nameRelation']);
                                        td.prev().prev().prev().prev().prev().html(data['data']['name']);
                                        $('#users-table-emp-att').DataTable().ajax.reload(null, false);

                                    } else {
                                        $('#modal-add-attach-person').modal('hide');
                                    }
                                }
                                if (data['type'] == 2) {
                                    $('#modal-add-attach-person').modal('hide');
                                    $('#users-table-emp-att').DataTable().ajax.reload(null, false);
                                }
                                if(data['type'] == 3) {
                                    $('#modal-success-notification').find('.text-default').text('Chế độ ưu tiên này đã hết vui lòng chọn chế độ khác');
                                    $('#modal-success-notification').find('.modal-title').text(titleModal);
                                    $('#modal-success-notification').find('.text-default').css('padding','10px');
                                    $('#btn-add-employee-attach').removeAttr('disabled');
                                    $('#modal-success-notification').modal('show');
                                }
                                $(this).find('.btn-save-form').removeAttr('disabled');
                            },
                            dataType:"json"
                        });
                    } else {
                        if (checkCMTND <= 13) {
                            $('.error-CMTND-employee').addClass('hidden');
                        } else {
                            $('.error-CMTND-employee').removeClass('hidden');
                        }
                        if (check > 0) {
                            $('#name-error').css('display','none');
                        } else {
                            $('#name-error').css('display','block');
                        }
                        if (checkPhone <= 13) {
                            $('.error-phone-employee').addClass('hidden');
                        } else {
                            $('.error-phone-employee').removeClass('hidden');
                        }
                        if (((isCmt == 1 && checkCMTND > 0) || isCmt != 1)) {
                            $('.error-CMTND-employee-require').addClass('hidden');
                        } else {
                            $('.error-CMTND-employee-require').removeClass('hidden');
                        }
                        if(checkFeeFavorable > 0) {
                            $('#favorable-require-aa').css('display','none');
                        } else {
                            $('#favorable-require-aa').css('display','block');
                        }
                        if(checkeBirthday > 0) {
                            $('.birthday-attach-employee-require').addClass('hidden');
                        } else {
                            $('.birthday-attach-employee-require').removeClass('hidden');
                        }
                        if(checkRelativeAttach > 0) {
                            $('#relative-attach-require').css('display','none');
                        } else {
                            $('#relative-attach-require').css('display','block');
                        }
                    }
                });
            });

            $(document).click(function (e) {
                var $contextMenu = $("#contextMenu");
                $('#users-table-emp tbody tr').css('background-color', '#fff');
                $contextMenu.hide();
                $contextMenu.css({
                    overflow: 'hidden'
                });
            });
            //Function hide botton submit on tab
            $(document).on('ready',function(){
                var hasTagUrl = window.location.hash;
                arrayUrl = ["#participants","#employee","#employeeAtt","#organizer"];
                if(jQuery.inArray(hasTagUrl,arrayUrl) == -1) {
                    $('#btn-submit-fake').css('display','inline');
                } else {
                    $('#btn-submit-fake').css('display','none');
                }
            });

            $(window).bind('hashchange', function() {
                var hasTagUrl = window.location.hash;
                if(jQuery.inArray(hasTagUrl,arrayUrl) == -1) {
                    $('#btn-submit-fake').css('display','inline');
                } else {
                    $('#btn-submit-fake').css('display','none');
                }
            });
            //End hide botton submit on tab
            function formatNumber (num) {
                return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
            }
            $('.convert_format_number').on('keyup',function(){
                var test = $(this).val();
                $(this).val(formatNumber(test));
            });
            //End tab employee add review delete ----------------
            $(document).on('click','#check-all-employee-join',function(e){

                e.preventDefault();
                $('.modal-noti-dange').attr('id','check-all-team-join');
                $('#check-all-team-join').find('#content-noti').text('Xác nhận tất cả nhân viên tham gia ?');
                $('.modal-noti-dange').modal('show');

            });

            $(document).on('click','#check-all-team-join .btn-cancel-employee',function(){
                var urlCheckEmpJoin = '{{route("welfare::welfare.save.all.employee.join")}}';
                $.ajax({
                    headers: {
                        'X-CSRF-Token': $('input[name="_token"]').val()
                    },
                    type:"post",
                    url:urlCheckEmpJoin,
                    data: {'id': {{$item->id}}},
                    success:function(data){
                        if(data = "true") {
                            $('#users-table-emp').DataTable().ajax.reload();
                        }
                        $('.modal-noti-dange').modal('hide');
                    },
                    dataType:"json"
                });
            });
            //endparticipants employee ----------------------------------------------------------------------

            //tab organizer ---------------------------------------------------------------------------------

            $(document).on('ready',function() {
                var urlEmployee = "{{route('welfare::welfare.show.data.Employee')}}";
                $('#table-employee-organizer').dataTable({
                    pagingType: "full_numbers",
                    serverSide: true,
                    autoWidth: false,
                    "bLengthChange": false,
                    "oLanguage": dataLang,
                    ajax: urlEmployee,
                    "pagingType": "input"
                });
            });
            //fill data when chose employee

            $(document).on('click','.show-popup-list-employee',function() {
                $('#modal-list-employee').modal('show');
            });

            $(document).on('click','#table-employee-organizer tbody tr',function() {
                var emPick = $(this).attr('emp_id');
                $('#table-employee-organizer tbody tr').css('background-color', '#fff');
                $(this).css('background-color', '#ccc');
                dataEmployeeChose = {
                    "name" : $(this).find('td:nth-child(1)').text(),
                    "phone" : $(this).find('td:nth-child(2)').text(),
                    "dep" : $(this).find('td:nth-child(3)').text(),
                    "role" : $(this).find('td:nth-child(4)').text(),
                    "email" : $(this).find('td:nth-child(5)').text(),
                };
            });

            $(document).on('click','#btn-choose-employee',function() {
                $('#modal-list-employee').modal('hide');
                $('.error-name-organizer').addClass('hidden');
                $('#wel_organizer_name').val(dataEmployeeChose['name']);
                $('#wel_organizer_phone').val(dataEmployeeChose['phone']);
                $('#wel_organizer_role').val(dataEmployeeChose['role']);
                $('#wel_organizer_dep').val(dataEmployeeChose['dep']);
                $('#wel_organizer_email').val(dataEmployeeChose['email']);
                $('#wel_organizer_name').attr('readonly','readonly');
                $('#wel_organizer_phone').attr('readonly','readonly');
                $('#wel_organizer_role').attr('readonly','readonly');
                $('#wel_organizer_email').attr('readonly','readonly');
                $('#wel_organizer_dep').attr('readonly','readonly');
                $('#cancel-chose-employee').css('display','block');

            });
            //save tab organizer
            $(document).on('click','#btn-save-organizer',function(e) {
                e.preventDefault();
                var data = {};
                data['name'] = $('#wel_organizer_name').val();
                data['phone'] = $('#wel_organizer_phone').val();
                data['position'] = $('#wel_organizer_role').val();
                data['company'] = $('#wel_organizer_dep').val();
                data['email_company'] = $('#wel_organizer_email').val();
                data['wel_id'] = '{{ $item->id }}';
                data['note'] = $('#wel_organizer_note').val();

                if(data['name'].length > 0 && data['phone'].length < 13) {
                    $('.error-name-organizer').addClass('hidden');
                    $('.error-phone-organizer').addClass('hidden');
                    $('#disable-btn-save-organizer').removeClass('hidden');
                    $(this).attr('disabled','disabled');
                    $.ajax({
                        headers: {
                              'X-CSRF-Token': $('input[name="_token"]').val()
                        },
                        type:"post",
                        url:'{{route("welfare::welfare.save.data.organizer")}}',
                        data: data,
                        success:function(data){
                            $('#disable-btn-save-organizer').addClass('hidden');
                            $('#btn-save-organizer').removeAttr("disabled");
                            if(data == true) {
                                $('#modal-success-notification .modal-title').text(titleModal);
                                $('#modal-success-notification .text-default').text(saveSuccess);
                                $('#modal-success-notification .text-default').css("padding","10px");
                                $('#modal-success-notification').modal('show');
                            } else {
                                $('#modal-success-notification .modal-title').text(titleModal);
                                $('#modal-success-notification .text-default').text("Có lỗi xảy ra vui lòng thử lại");
                                $('#modal-success-notification .text-default').css("padding","10px");
                                $('#modal-success-notification').modal('show');
                            }
                        },
                        dataType:"json"
                    });
                } else {
                    if(data['name'].length > 0) {
                        $('.error-name-organizer').addClass('hidden');
                    } else {
                        $('.error-name-organizer').removeClass('hidden');
                    }
                    if(data['phone'].length < 13) {
                        $('.error-phone-organizer').addClass('hidden');
                    } else {
                        $('.error-phone-organizer').removeClass('hidden');
                    }
                }
            });

            //option click position hold
            $(document).on('click','#cancel-chose-employee',function(){
                $(this).css('display','none');
                $('#wel_organizer_name').removeAttr('readonly').val("");
                $('#wel_organizer_phone').removeAttr('readonly').val("");
                $('#wel_organizer_role').removeAttr('readonly').val("");
                $('#wel_organizer_email').removeAttr('readonly').val("");
                $('#wel_organizer_dep').removeAttr('readonly').val("");

            });

            $(document).on('click','.check-cmt',function(){
                var checkCmt = $(this).val();
                if(checkCmt == 1){
                    $('#form-input-cmt').removeClass('hidden');
                } else {
                    $('#form-input-cmt').addClass('hidden');
                    $('#tab-employee-CMTND').val("");
                }
            });
            @endif
        });
        jQuery.extend(jQuery.validator.messages, {
            min: jQuery.validator.format('{{ trans('welfare::view.Please enter a valid number >0') }}'),
            number: errValidNumber,
        });
    </script>
@endsection

