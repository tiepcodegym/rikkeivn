@extends('layouts.default')
@section('title')
    {{ trans('admin_setting::view.config management') }}
@endsection
<?php
use Rikkei\Core\View\CoreUrl;
$confessionEmployees = $confessionEmployees ? $confessionEmployees->toArray() : [];
$marketEmployees = $marketEmployees ? $marketEmployees->toArray() : [];
$giftEmployees = $giftEmployees ? $giftEmployees->toArray() : [];
$proposedEmployees = $proposedEmployees ? $proposedEmployees->toArray() : [];
?>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}"/>
    <style type="text/css">
        .displayNone {
            display: none;
        }
        .bold-label {
            font-weight: bold;!important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
{{--                <div class="box-header">--}}
{{--                    <h3 class="box-title managetime-box-title">{{ trans('admin_setting::view.description') }}</h3>--}}
{{--                </div>--}}
                <div class="box-body no-padding margin-top-20">
                    <div class="table-responsive" style="overflow-x: hidden">
                        <form method="post"
                              action="{{ route('admin::mobile.config.store')}}"
                              enctype="multipart/form-data">
                            {!! csrf_field() !!}
                            <tr>
                                <div class="row">
                                    <div class="col-sm-12 form-group form-group-select2">
                                        <label class="control-label bold-label required">{{ trans('admin_setting::view.avatar') }}</label>
                                        <div class="team-select-box" style="margin-bottom: 10px">
                                            <div class="input-box">
                                                <input type="file" id="avatar_url" accept="image/*" name="avatar_url" class="form-control">
                                            </div>
                                        </div>
                                        @if($collectionModel && $collectionModel->avatar_url)
                                            <img src="{{'/asset_notify/image/' . $collectionModel->avatar_url}}"
                                                 style="max-height: 120px; max-width: 100%; border: solid 1px #3333;"/>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 form-group form-group-select2">
                                        <label class="control-label bold-label required"
                                               for="confessionEmployees">{{ trans('admin_setting::view.admin confession') }}</label>
                                        <div class="input-box">
                                            <select name="confessionEmployees[]" id="confessionEmployees"
                                                    class="form-control select-search-employee-confession"
                                                    data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}"
                                                    multiple>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group form-group-select2">
                                        <label class="control-label bold-label required"
                                               for="employees">{{ trans('admin_setting::view.admin market') }}</label>
                                        <div class="input-box">
                                            <select name="marketEmployees[]" id="marketEmployees"
                                                    class="form-control select-search-employee-market"
                                                    data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}"
                                                    multiple>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group form-group-select2">
                                        <label class="control-label bold-label required"
                                               for="employees">{{ trans('admin_setting::view.admin gift') }}</label>
                                        <div class="input-box">
                                            <select name="giftEmployees[]" id="giftEmployees"
                                                    class="form-control select-search-employee-gift"
                                                    data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}"
                                                    multiple>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 form-group form-group-select2">
                                        <label class="control-label bold-label required"
                                               for="employees">{{ trans('admin_setting::view.admin proposed') }}</label>
                                        <div class="input-box">
                                            <select name="proposedEmployees[]" id="proposedEmployees"
                                                    class="form-control select-search-employee-proposed"
                                                    data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}"
                                                    multiple>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary" style="margin-bottom: 10px"><i class="fa fa-floppy-o"></i> {{ trans('files::view.Save') }} </button>
                                </div>
                            </tr>
                            <tr>
                            </tr>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#employees").select2({
                tags: true,
                placeholder: "<?php echo trans('files::view.Group email') ?>",
            });
            var confessionEmployees = <?php echo json_encode($confessionEmployees)?>;
        });

        $(function () {
            $('.select-search-employee-confession').selectSearchEmployee();
            $('.select-search-employee-market').selectSearchEmployee();
            $('.select-search-employee-gift').selectSearchEmployee();
            $('.select-search-employee-proposed').selectSearchEmployee();
            var confessionEmployees = <?php echo json_encode($confessionEmployees)?>;
            var marketEmployees = <?php echo json_encode($marketEmployees)?>;
            var giftEmployees = <?php echo json_encode($giftEmployees)?>;
            var proposedEmployees = <?php echo json_encode($proposedEmployees)?>;
            if (confessionEmployees.length > 0) {
                $.each(confessionEmployees, function (index, item) {
                    var emailCutting = item.email.substring(0, item.email.lastIndexOf("@"));
                    var full_name = item.name + ' (' + emailCutting + ')';
                    $('#confessionEmployees').append(new Option(full_name, item.id, true, true));
                });
            }
            if (marketEmployees.length > 0) {
                $.each(marketEmployees, function (index, item) {
                    var emailCutting = item.email.substring(0, item.email.lastIndexOf("@"));
                    var full_name = item.name + ' (' + emailCutting + ')';
                    $('#marketEmployees').append(new Option(full_name, item.id, true, true));
                });
            }
            if (giftEmployees.length > 0) {
                $.each(giftEmployees, function (index, item) {
                    var emailCutting = item.email.substring(0, item.email.lastIndexOf("@"));
                    var full_name = item.name + ' (' + emailCutting + ')';
                    $('#giftEmployees').append(new Option(full_name, item.id, true, true));
                });
            }
            if (proposedEmployees.length > 0) {
                $.each(proposedEmployees, function (index, item) {
                    var emailCutting = item.email.substring(0, item.email.lastIndexOf("@"));
                    var full_name = item.name + ' (' + emailCutting + ')';
                    $('#proposedEmployees').append(new Option(full_name, item.id, true, true));
                });
            }
        });
    </script>
@endsection