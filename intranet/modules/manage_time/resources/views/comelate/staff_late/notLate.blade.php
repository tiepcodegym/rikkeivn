@extends('layouts.default')

@section('title')
    {{ trans('manage_time::view.Employee managers do not go late') }}
@endsection

<?php
use Rikkei\ManageTime\Model\TimekeepingNotLate;
use Rikkei\Core\View\CoreUrl;

    $objNotLate = new TimekeepingNotLate();
    $dayWeeks = $objNotLate->getLabelDayOfWeek();
    $strDayWeek = [];
    foreach($dayWeeks as $key => $dayWeek) {
        $strDayWeek[] = '<option value="' . $key . '">' . $dayWeek . '</option>';
    }
?>

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<style>
    .staff-late .w-70 {
        width: 50px;
    }

    .select2 {
        width: 100% !important;
    }

    .col-week {
        width: 210px;
    }
    .w-350 {
        width: 350px;
    }

    .col-week .label {
        margin-bottom: 4px;
        display: inline-block;
    }
    .mt-5 {
        margin-top: 5px;
    }
    @media only screen and (min-width: 1200px)  {
        .w-table {
            width: 80%;
            margin: 0px auto !important;
        }
        .ct_btn-group {
            width: 120px;
            text-align: center
        }
    }
</style>
@endsection

@section('content')
<div class="hidden">
    <select name="empid" class="form-control select-search-employee "
        data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
    </select>
</div>
<div class="row staff-late">
    <div class="col-md-3">
        @include('manage_time::comelate.staff_late.menu_left')
    </div>
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header text-center">
                <h3>{{ trans('manage_time::view.List of employees allowed to late') }}</h3>
            </div>
            <!-- /.box-header -->
        
            <div class="box-body table-responsive w-table">
                <?php $i = 0;?>
                <table class="table table-striped table-bordered table-hover table-grid-data" id="not-late">
                    <thead>
                    <tr class="info">
                        <th class="w-70 text-center">STT</th>
                        <th class="w-350">{{ trans('manage_time::view.Employee email') }}</th>
                        <th>{{ trans('ot::view.Employee Name') }}</th>
                        <th>{{ trans('manage_time::view.Weekdays') }}</th>
                        <th class="ct_btn-group"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @if (isset($empNotLate) && $empNotLate)
                        @foreach($empNotLate as $item)
                            <tr data-row-id="{{$item->id}}">
                                <td class="text-center">{{++$i}}</td>
                                <td class="max-w-350">
                                    <div class="text email">
                                        {{ $item->emp_email }}
                                    </div>
                                    <div class="input hidden">
                                    </div>
                                </td>
                                <td>
                                    <div class="text name">
                                        {{$item->emp_name}}
                                    </div>
                                    <div class="input input-box hidden">
                                        <select name="empid" class="form-control select-search-employee"
                                            data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}
                                            ">
                                            <option value="{{ $item->emp_id }}">{{ $item->emp_name }}</option>
                                        </select>
                                    </div>
                                    <div class="error empid-error"></div>
                                </td>
                                <td class="col-week">
                                    <?php
                                    $arrDay = $objNotLate->getDayWeek($item->weekdays);
                                    ?>
                                    <div class="text week">
                                        @foreach($arrDay as $day)
                                            <span class="label label-default">{{ $day }}</span>
                                        @endforeach
                                    </div>
                                    <div class="input hidden">
                                        <select name="weekdays[]" class="form-control weekdays" multiple="multiple">
                                            @foreach($dayWeeks as $key => $dayWeek)
                                                <option value="{{ $key }}"
                                                        @if (in_array($dayWeek, $arrDay))
                                                        selected
                                                        @endif
                                                >{{$dayWeek}}</option>
                                            @endforeach
                                        </select>
                                        <div class="error weekdays-error"></div>
                                    </div>
                                </td>
                                <td class="ct_btn-group">
                                    <div class="text">
                                        <button class="btn btn-primary btn-ss-action" data-btn-action="edit" type="button"><i class="fa fa-pencil"></i></button>
                                        <button class="btn btn-danger btn-ss-action" data-btn-action="delete"type="button"><i class="fa fa-trash"></i></button>
                                    </div>
                                    <div class="input hidden">
                                        <button class="btn btn-success btn-ss-action" data-btn-action="update" type="button"
                                        ><i class="fa fa-floppy-o"><i class="fa fa-refresh fa-spin margin-left-10 hidden"hiddenaria-hidden="true"></i></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <button class="btn-add mt-5" data-table-id="not-late" data-table-row="notLateRow">
                    <i class="fa fa-plus"></i></button>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
<script>
    $(function () {
        $('.select-search-employee').selectSearchEmployee();
    });
</script>
<script>
    var numberNotlate = "{{$i}}";
    var token = '{{ csrf_token() }}';
    var messageDelete = '<?php echo trans('manage_time::message.Are you sure you want to delete') ?>';
    var strDayWeek = <?php echo json_encode($strDayWeek) ?>;
    var dataDayWeeks = <?php echo json_encode($dayWeeks) ?>;
    var urlSearchEmp = "{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}";
    var urlSaveNotLate = "{{ route('manage_time::admin.staff-late.not-late.create-not-late') }}";
    var urlUpdateNotLate = "{{ route('manage_time::admin.staff-late.not-late.update-not-late') }}";
    var urlDeleteNotLate = "{{ route('manage_time::admin.staff-late.not-late.delete-not-late') }}";
</script>
<script src="{{ CoreUrl::asset('asset_managetime/js/admin_comelate_not_late.js') }}"></script>
@endsection
