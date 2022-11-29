@extends('layouts.default')
@section('title')
    {{ trans('admin_setting::view.List OT disallow') }}
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
?>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
    <style type="text/css">
        .displayNone {
            display: none;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title managetime-box-title">{{ trans('admin_setting::view.OT disallow') }}</h3>
                </div>
                <div class="box-body no-padding margin-top-20">
                    <div class="table-responsive" style="overflow-x: hidden">
                        <form method="post" action="{{ route('admin::setting-ot.save-employee-ot') }}">
                            {!! csrf_field() !!}
                            <tr>
                                <div class="row">
                                    <div class="col-sm-6 form-group form-group-select2">
                                        <label class="control-label required">{{ trans('admin_setting::view.division') }}<em> *</em></label>
                                        <div class="team-select-box">
                                            <div class="input-box">
                                                <select name="group-team" id="groupTeam"
                                                        class="form-control select-search"
                                                        autocomplete="off">
                                                            @if($isScopeCompany)
                                                                @foreach($teamsOptionAll as $option)
                                                                    <option value="{{ $option['value'] }}"
                                                                                    code="{{ $option['code'] }}" class="setBod">{{ $option['label'] }}</option>
                                                                @endforeach
                                                            @else
                                                                @foreach($teamsOptionAll as $option)
                                                                @if(in_array($option['value'],$teamIds))                                                                
                                                                    <option value="{{ $option['value'] }}"
                                                                    code="{{ $option['code'] }}" class="setBod">{{ $option['label'] }}</option>
                                                                @endif 
                                                                @endforeach
                                                            @endif
                                                </select>
                                                <label id="groupTeam-error" class="error displayNone">{{ trans('admin_setting::view.The field is required') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 form-group form-group-select2">
                                        <label class="control-label required">{{ trans('admin_setting::view.Employee') }}<em> *</em></label>
                                        <div class="input-box">
                                            <select name="employees[]" id="employees" class="form-control select-search-employee" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-ot-disallow') }}" multiple>
                                            </select>
                                            <p class="search-employee-error displayNone error">{{ trans('admin_setting::view.The field is required') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </tr>
                            <button type="submit" class="btn btn-primary" onclick="return checkForm();" style="margin-bottom: 10px"><i class="fa fa-floppy-o"></i> {{ trans('files::view.Save') }} </button>
                            <div class="modal fade" id="speaking-config">
                                <div class="modal-dialog modal-full-width">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span></button>
                                            <h4 class="modal-title">{{ trans('admin_setting::view.update') }}</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-6 form-group form-group-select2">
                                                    <label class="control-label required">{{ trans('admin_setting::view.division') }}<em> *</em></label>
                                                    <div class="team-select-box">
                                                        <div class="input-box">

                                                            <select style="user-select: none" name="groupTeam-edit" id="groupTeam-edit"
                                                                  class="form-control select-search"
                                                                    autocomplete="off" >
                                                                    @if($isScopeCompany)
                                                                        @foreach($teamsOptionAll as $option)
                                                                            <option value="{{ $option['value'] }}"
                                                                                    code="{{ $option['code'] }}" class="setBod">{{ $option['label'] }}</option>
                                                                        @endforeach
                                                                    @else
                                                                        @foreach($teamsOptionAll as $option)
                                                                        @if(in_array($option['value'],$teamIds))                                                                
                                                                                    <option value="{{ $option['value'] }}"
                                                                                    code="{{ $option['code'] }}" class="setBod">{{ $option['label'] }}</option>
                                                                            @endif 
                                                                        @endforeach
                                                                    @endif
                                                            </select>
                                                            <input type="hidden" id="groupTeam-edit-selected" />


                                                            <label id="groupTeam-edit-error" class="error displayNone">{{ trans('admin_setting::view.The field is required') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 form-group form-group-select2">
                                                    <label class="control-label required">{{ trans('admin_setting::view.Employee') }}<em> *</em></label>
                                                    <div class="input-box">
                                                        <select name="employees-edit[]" id="employees-edit" class="form-control" data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee-ot-disallow') }}" multiple>
                                                        </select>
                                                        <p id="employees-edit-error" class="displayNone error">{{ trans('admin_setting::view.The field is required') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <span class="btn btn-secondary" data-dismiss="modal">Cancel</span>
                                            <button type="submit" class="btn btn-primary" onclick="return checkFormEdit();"><i class="fa fa-floppy-o"></i> {{ trans('files::view.Save') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin_setting::view.division') }}</th>
                                    <th>{{ trans('admin_setting::view.Employee') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $item->division }} {{ $item->original['division'] }}</td>
                                    <td>{{ $item->employee_id }}</td>

                                    <td>
                                        <a class="btn-edit" title="Edit" onclick="showChangeDataModal({{$item->id}})"><i class="fa fa-edit"></i></a>
                                        <form action="{{route('admin::setting-ot.delete-ot')}}" method="post" class="form-inline">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="id" value="{{$item->id}}">
                                            <button class="btn-delete delete-confirm" title="Delete">
                                                <span><i class="fa fa-trash"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box-footer">
                    @include('files::include.pager')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdn.ckeditor.com/4.12.1/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#employees").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
            $("#groupTeam").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
            $("#employees-edit").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
            $("#groupTeam-edit").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
            $('.select-search-employee').change(function() {
                if ($('.select-search-employee').val() === null) {
                    $('.search-employee-error').show();
                } else {
                    $('.search-employee-error').hide();
                }
            });
            $('#groupTeam').change(function() {
                if ($('#groupTeam :selected').val() == 0) {
                    $('#groupTeam-error').show();
                } else {
                    $('#groupTeam-error').hide();
                }
            });
            $('#employees-edit').change(function() {
                if ($('#employees-edit').val() === null) {
                    $('#employees-edit-error').show();
                } else {
                    $('#employees-edit-error').hide();
                }
            });
            $('#groupTeam-edit').change(function() {
                if ($('#groupTeam-edit :selected').val() == 0) {
                    $('#groupTeam-edit-error').show();
                } else {
                    $('#groupTeam-edit-error').hide();
                }
            });
        });

        function checkForm() {
            var valid = true;
            if ($('#groupTeam :selected').val() == 0) {
                $('#groupTeam-error').show();
                valid = false;
            }
            if ($('.select-search-employee').val() === null) {
                $('.search-employee-error').show();
                valid = false;
            }
            return valid;
        }
        function checkFormEdit() {
            var valid = true;
            if ($('#groupTeam-edit :selected').val() == 0) {
                $('#groupTeam-edit-error').show();
                valid = false;
            }
            if ($('#employees-edit').val() === null) {
                $('#employees-edit-error').show();
                valid = false;
            }
            return valid;
        }

        $(function() {
            $('.select-search-employee').selectSearchEmployeeOtDisallow('groupTeam');
            $('#employees-edit').selectSearchEmployeeOtDisallow('groupTeam-edit-selected');
        });

        function showChangeDataModal(id) {
            $('#speaking-config').modal('show');
            var getData = '{{ route("admin::setting-ot.edit-ot") }}';
            $.ajax ({
                url: getData,
                method : 'POST',
                data: {id: id,
                    "_token": "{{ csrf_token() }}"},
                success: function(data) {                    
                    //$("#groupTeam-edit option[value='"+ data.division+"']").removeAttr('disabled', '');
                    $("#groupTeam-edit-selected").val(data.division);
                    $('#groupTeam-edit').val(data.division);
                  
                    //$("#groupTeam-edit option").prop("disabled",true);
                    //$("#groupTeam-edit>option[value='"+data.division+"']").removeAttr('disabled').removeProp('disabled');

                    $('#groupTeam-edit').find('option').prop('disabled',true);
                    //alert(data.division)
                    $("#groupTeam-edit option[value='"+ data.division+"']").removeAttr('disabled');

                    $('#groupTeam-edit').select2("destroy").select2();
                   
                    //$('#groupTeam-edit').trigger('change');

                    if (data.employee_id.length > 0) {
                        $('#employees-edit').html('');
                        $.each(data.employee_id, function(index, item) {
                            $('#employees-edit').append(new Option(item.name, item.id, true, true));
                        });
                    }
                }
            });
        }
    </script>
@endsection
