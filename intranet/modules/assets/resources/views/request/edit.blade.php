<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\Form;
    use Rikkei\Assets\Model\RequestAsset;
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Assets\View\AssetView;
    use Rikkei\Assets\View\RequestAssetPermission;

    $labelRequestAsset = RequestAsset::labelStates();
    $isScopeCompanyCreateRequest = RequestAssetPermission::isScopeCompanyCreateRequest();
    $isScopeTeamCreateRequest = RequestAssetPermission::isScopeTeamCreateRequest();

    $allowEdit = false;
    if ($requestAsset && $requestAsset->status == RequestAsset::STATUS_INPROGRESS) {
        $allowEdit = true;
    }
    $allowUpdate = false;
    if ($requestAsset && $requestAsset->status == RequestAsset::STATUS_REJECT) {
        $allowUpdate = true;
    }
    $disabled = 'disabled';
    if (!empty($isCreate) || $allowEdit || $allowUpdate) {
        $disabled = '';
    }
    $isPopup = request()->get('is_popup');
    $layoutExtend = 'layouts.default';
    if ($isPopup) {
        $layoutExtend = 'layouts.popup';
    }
?>

@extends($layoutExtend)

@section('title')
    @if ($requestAsset)
        {{ trans('asset::view.Request asset detail') }}
    @else
        {{ trans('asset::view.Create request asset') }}
    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/request.css') }}" />
    <style type="text/css">
        .asset-callout {
            background-color: #00a65a!important;
            border-color: #0097bc;
            color: #fff;
            border-radius: 3px;
            margin: 0 0 20px;
            padding: 15px;
        }
        .asset-callout p:last-child {
            margin-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title request-box-title">{{ trans('asset::view.Request information') }}</h3>
            <div class="pull-right">
                @if ($requestAsset)
                    <a class="request-link" target="_blank" href="{{ route('asset::resource.request.view', ['id' => $requestAsset->id]) }}">
                        <span>
                            <i class="fa fa-hand-pointer-o"></i>
                            {{ trans('asset::view.View detail') }}
                        </span>
                    </a>
                @endif
            </div>
        </div>
        <!-- /.box-header -->

        <form method="post" action="{{ route('asset::resource.request.save') }}" autocomplete="off" id="form_request_asset" class="no-validate">
            {!! csrf_field() !!}
            <input type="hidden" name="id" value="{{ $requestAsset ? $requestAsset->id : null }}" />
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                        @if ($requestAsset)
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    {!! RequestAsset::renderStatusHtml($requestAsset->status, $labelRequestAsset) !!}
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-sm-6 request-form-group">
                                <div class="row">
                                    <div class="col-sm-6 request-form-group">
                                        <label class="control-label required">{{ trans('asset::view.Petitioner') }} <em>*</em></label>
                                        <div class="input-box">
                                            @if ($isScopeCompanyCreateRequest || $isScopeTeamCreateRequest)
                                                <select name="request[employee_id]" id="employee_id" class="form-control select-search-employee"
                                                        data-remote-url="{{ URL::route('asset::resource.request.ajax-search-employee') }}" style="width: 100%;"
                                                        {{ $disabled }}>
                                                    @if ($petitionerInfo)
                                                        <option value="{{ $petitionerInfo->employee_id }}" selected>{{ $petitionerInfo->employee_name . ' (' . AssetView::getNickName($petitionerInfo->employee_email) . ')' }}</option>
                                                    @endif
                                                </select>
                                            @else
                                                <input type="text" name="" class="form-control" value="{{ $petitionerInfo->employee_name . ' (' . AssetView::getNickName($petitionerInfo->employee_email) . ')' }}" disabled />
                                                <input type="hidden" name="request[employee_id]" id="employee_id" value="{{ $petitionerInfo->employee_id }}">
                                            @endif
                                            <label class="asset-error" id="employee_id-error">{{ trans('asset::message.The field is required') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 request-form-group">
                                        <label class="control-label required">{{ trans('asset::view.Skype of user') }} <em>*</em></label>
                                        <div class="input-box">
                                            <input type="text" class="form-control"
                                                   name="request[skype]"
                                                   id="skype"
                                                   placeholder="{{ trans('asset::view.Skype of user') }}"
                                                   value="{{ $requestAsset ? $requestAsset->skype : $contactOfEmp->skype }}" />
                                            <label class="asset-error" id="skype-error">{{ trans('asset::message.The field is required') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 request-form-group">
                                <label class="control-label required">{{ trans('asset::view.Reviewer') }} <em>*</em></label>
                                <div class="input-box">
                                    <select name="request[reviewer]" id="box_reviewer" class="form-control select-search-employee-review"
                                            data-remote-url-review="{{ URL::route('asset::resource.request.ajax-search-employee-review') }}" style="width: 100%;"
                                            {{ $disabled }}>
                                            @if ($requestAsset)
                                                <option value="{{ $requestAsset->reviewer }}">{{ $requestAsset->name . ' (' . preg_replace('/@.*/', '',$requestAsset->email) . ')' }}</option>
                                            @elseif ($leaderReview)
                                                <option value="{{ $leaderReview->id }}">{{ $leaderReview->name . ' (' . preg_replace('/@.*/', '',$leaderReview->email) . ')' }}</option>
                                            @endif
                                    </select>
                                    <label class="asset-error" id="reviewer-error">{{ trans('asset::message.The field is required') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 request-form-group">
                                <label class="control-label required">{{ trans('asset::view.Request name') }} <em>*</em></label>
                                <div class="input-box">
                                    <input type="text" name="request[request_name]" id="request_name" class="form-control"
                                           value="{{ $requestAsset ? $requestAsset->request_name : (isset($newDefaultParams) ? $newDefaultParams : null) }}" {{ $disabled }} />
                                    <label class="asset-error" id="request_name-error">{{ trans('asset::message.The field is required') }}</label>
                                    <label class="asset-error" id="request_name_long-error">{{ trans('asset::message.The field not be greater than :number characters', ['number' => 250]) }}</label>
                                </div>
                            </div>
                            <div class="col-sm-6 request-form-group">
                                <label class="control-label required">{{ trans('asset::view.Request date') }} <em>*</em></label>
                                <div class="input-group date date-picker" id="request_date_picker">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <?php
                                    if ($requestAsset) {
                                        $requestDate = $requestAsset->request_date;
                                    } elseif (isset($defaultParams['request_date'])) {
                                        $requestDate = $defaultParams['request_date'];
                                    } else {
                                        $requestDate = \Carbon\Carbon::now()->format('d-m-Y');
                                    }
                                    ?>
                                    <input type='text' id="request_date" class="form-control" name="request[request_date]" placeholder="dd-mm-yyyy"
                                           value="{{ $requestDate }}" {{ $disabled }} />
                                </div>
                                <label class="asset-error" id="request_date-error">{{ trans('asset::message.The field is required') }}</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label required">{{ trans('asset::view.Request note') }} <em>*</em></label>
                            <div class="input-box">
                                <textarea name="request[request_reason]" id="request_reason" class="form-control request-textarea-100" {{ $disabled }}
                                          >{{ $requestAsset ? $requestAsset->request_reason : (isset($defaultParams['request_reason']) ? $defaultParams['request_reason'] : null) }}</textarea>
                                <label class="asset-error" id="request_reason-error">{{ trans('asset::message.The field is required') }}</label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <p><b>{{ trans('asset::view.Asset information') }}</b> <em class="error">*</em></p>
                            </div>
                            <div id="duplicate_asset_category">
                                @if (isset($requestAsssetItem) && count($requestAsssetItem))
                                    <?php
                                        $index = 0;
                                    ?>
                                    @foreach ($requestAsssetItem as $request)
                                        <?php
                                            $index++;
                                        ?>
                                        @if ($index == 1)
                                            <div class="box-number col-sm-12">
                                                <div class="row">
                                                    <div class="col-sm-6 request-form-group">
                                                        <label class="control-label required">{{ trans('asset::view.Asset category request') }} <em>*</em></label>
                                                        <div class="input-box">
                                                            <select type="text" name="asset[{{ $index }}][name]" id="selectAsset" class="form-control request-select-2 request-category" style="width: 100%;" {{ $disabled }}>
                                                                @if (count($assetCategoriesList))
                                                                    @foreach ($assetCategoriesList as $item)
                                                                        <option value="{{ $item->id }}" {{ $item->id == $request->asset_category_id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <label class="asset-error request-category-error">{{ trans('asset::message.The field is required') }}</label>
                                                            <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 request-form-group">
                                                        <label class="control-label required">{{ trans('asset::view.Quantity') }} <em>*</em></label>
                                                        <div class="input-box">
                                                            <input type="number" min="1" name="asset[{{ $index }}][number]" class="form-control request-quantity" value="{{ $request->quantity }}" {{ $disabled }} />
                                                            <label class="asset-error request-quantity-error">{{ trans('asset::message.The field is required') }}</label>
                                                            <label class="asset-error request-max-quantity-error">{{ trans('asset::message.The field not be greater than :number characters', ['number' => AssetConst::MAX_CHARACTER_QUANTITY_ASSET_REQUEST]) }}</label>
                                                            <label class="asset-error request-min-quantity-error">{{ trans('asset::message.The request asset quantity can not be less than :number', ['number' => 1]) }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="box-number col-sm-12" style="margin-top: 15px;">
                                                <div class="row">
                                                    <div class="col-sm-6 request-form-group">
                                                        <div class="input-box">
                                                            <select id="selectAsset" type="text" name="asset[{{ $index }}][name]" class="form-control request-select-2 request-category" style="width: 100%;" {{ $disabled }}>
                                                                @if (count($assetCategoriesList))
                                                                    @foreach ($assetCategoriesList as $item)
                                                                        <option value="{{ $item->id }}" {{ $item->id == $request->asset_category_id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                            <label class="asset-error request-category-error">{{ trans('asset::message.The field is required') }}</label>
                                                            <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3 request-form-group">
                                                        <div class="input-box">
                                                            <input type="number" min="1" name="asset[{{ $index }}][number]" class="form-control request-quantity" value="{{ $request->quantity }}" {{ $disabled }} />
                                                            <label class="asset-error request-quantity-error">{{ trans('asset::message.The field is required') }}</label>
                                                            <label class="asset-error request-max-quantity-error">{{ trans('asset::message.The request asset quantity can not be greater than :number', ['number' => AssetConst::MAX_CHARACTER_QUANTITY_ASSET_REQUEST]) }}</label>
                                                            <label class="asset-error request-min-quantity-error">{{ trans('asset::message.The request asset quantity can not be less than :number', ['number' => 1]) }}</label>
                                                        </div>
                                                    </div>
                                                    @if (!empty($isCreate) || $allowEdit)
                                                        <div class="col-sm-3 request-form-group">
                                                            <a class="btn-delete btn-delete-category-box">
                                                                <i class="fa fa-minus"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @elseif (!$disabled)
                                    <div class="box-number col-sm-12">
                                        <div class="row">
                                            <div class="col-sm-6 request-form-group">
                                                <label class="control-label required">{{ trans('asset::view.Asset category request') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <select type="text" name="asset[1][name]" id="selectAsset" class="form-control request-select-2 request-category" style="width: 100%;" {{ $disabled }}>
                                                        @if (count($assetCategoriesList))
                                                            @foreach ($assetCategoriesList as $item)
                                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <label class="asset-error request-category-error">{{ trans('asset::message.The field is required') }}</label>
                                                    <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 request-form-group">
                                                <label class="control-label required">{{ trans('asset::view.Quantity') }} <em>*</em></label>
                                                <div class="input-box">
                                                    <input type="number" min="1" name="asset[1][number]" class="form-control request-quantity" value="1" {{ $disabled }} />
                                                    <label class="asset-error request-quantity-error">{{ trans('asset::message.The field is required') }}</label>
                                                    <label class="asset-error request-max-quantity-error">{{ trans('asset::message.The field not be greater than :number characters', ['number' => AssetConst::MAX_CHARACTER_QUANTITY_ASSET_REQUEST]) }}</label>
                                                    <label class="asset-error request-min-quantity-error">{{ trans('asset::message.The request asset quantity can not be less than :number', ['number' => 1]) }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div id="box_duplicate_asset_category" style="display: none;">
                                <div class="box-number col-sm-12" style="margin-top: 15px;">
                                    <div class="row">
                                        <div class="col-sm-6 request-form-group">
                                            <div class="input-box">
                                                <select type="text" name="" id="selectAsset" class="form-control request-select-2-new input-name request-category" style="width: 100%;">
                                                    @if (count($assetCategoriesList))
                                                        @foreach ($assetCategoriesList as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <label class="asset-error request-category-error">{{ trans('asset::message.The field is required') }}</label>
                                                <label class="asset-error request-category-duplicate-error">{{ trans('asset::message.The request asset category can not be duplicated') }}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3 request-form-group">
                                            <div class="input-box">
                                                <input type="number" min="1" name="" class="form-control input-number request-quantity" value="1" />
                                                <label class="asset-error request-quantity-error">{{ trans('asset::message.The field is required') }}</label>
                                                <label class="asset-error request-max-quantity-error">{{ trans('asset::message.The field not be greater than :number characters', ['number' => AssetConst::MAX_CHARACTER_QUANTITY_ASSET_REQUEST]) }}</label>
                                                <label class="asset-error request-min-quantity-error">{{ trans('asset::message.The request asset quantity can not be less than :number', ['number' => 1]) }}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3 request-form-group">
                                            <a class="btn-delete btn-delete-category-box">
                                                <i class="fa fa-minus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if (!empty($isCreate) || $allowEdit)
                                <div class="col-sm-12">
                                    <a class="btn-add btn-add-category-box">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
                        <?php
                        $candidateId = request()->get('cdd_id');
                        $urlPrevious = url()->previous();
                         if ($urlPrevious == request()->url() || request()->url() == route('asset::resource.request.index').'/edit') {
                            $urlPrevious = route('asset::profile.my_request_asset');
                        }
                        ?>
                        @if (!$isPopup)
                        <a href="{{ $urlPrevious }}" class="btn btn-warning"><i class="fa fa-long-arrow-left"></i> {{ trans('asset::view.Back to list') }}</a>
                        @endif

                        @if (!empty($isCreate) || $allowEdit || $allowUpdate)
                            @if ($candidateId)
                                <input type="hidden" name="candidate_id" value="{{ $candidateId }}">
                            @endif
                        @endif
                        @if (!empty($isCreate) || $allowEdit)
                            <button type="submit" class="btn btn-primary" onclick="return validateSubmitRequest()"><i class="fa fa-floppy-o"></i> {{ trans('asset::view.Send request') }}</button>
                        @endif
                        @if ($allowUpdate)
                            <button type="submit" class="btn btn-primary" onclick="return validateSubmitRequest()"><i class="fa fa-floppy-o"></i> {{ trans('asset::view.Update request') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
        <!-- /.box-body -->
    </div>
    <!-- /. box -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/request_asset.js') }}"></script>
    <script>
        const MAX_CHARACTER_QUANTITY_ASSET_REQUEST = '{{ AssetConst::MAX_CHARACTER_QUANTITY_ASSET_REQUEST }}';
        var urlLeaderReview = '{{ route('asset::resource.request.ajax-search-leader-review') }}';
        var isCreate = "{{json_encode($isCreate)}}";
        (function($) {
            $.fn.selectSearchEmployee = function () {
                $(this).select2({
                    id: function(response){
                        return response.id;
                    },
                    ajax: {
                        url: $(this).data('remote-url'),
                        dataType: "JSON",
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                page: params.page,
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 5) < data.total_count
                                }
                            };
                        },
                        cache: true,
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    },
                    minimumInputLength: 2,
                    templateResult: formatReponse,
                    templateSelection: formatReponseSelection,
                });
            };
            var employee_id = $(".select-search-employee").val();
            $('.select-search-employee').on('change', function() {
                employee_id = $(this).val();
                $("#box_reviewer").html('');
                $.ajax ({
                    url: urlLeaderReview,
                    data: {employee_id: employee_id},
                    success: function(data)
                    {
                        if (data.reviewer) {
                            var option = '<option value="' + data.reviewer.id + '">' + data.reviewer.name + '(' + data.reviewer.email + ')</option>';
                            $("#box_reviewer").html(option);
                        }
                        if (data.contact && data.contact.skype !== 'undefined') {
                            $('#skype').val(data.contact.skype);
                        } else {
                            $('#skype').val('');
                        }
                    }
                });
            });
            $.fn.selectSearchEmployeeReview = function () {
                $(this).select2({
                    id: function(response){
                        return response.id;
                    },
                    ajax: {
                        url: $(this).data('remote-url-review'),
                        dataType: "JSON",
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                page: params.page,
                                employee: employee_id,
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 5) < data.total_count
                                }
                            };
                        },
                        cache: true,
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    },
                    minimumInputLength: 2,
                    templateResult: formatReponse,
                    templateSelection: formatReponseSelection,
                });
            };
            $('.select-search-employee').selectSearchEmployee();
            $('.select-search-employee-review').selectSearchEmployeeReview();

            $('#box_reviewer').on('change', function() {
                $('#reviewer-error').hide();
            });
            $('#request_name').on('keypress', function() {
                $('#request_name-error').hide();
            });
            $('#request_name').on('keyup', function() {
                var requestName = $('#request_name').val();
                if (requestName.length <= 250) {
                    $('#request_name_long-error').hide();
                }
            });
            $('#request_reason').on('keypress', function() {
                $('#request_reason-error').hide();
            });
            $('#skype').on('keypress', function() {
                $('#skype-error').hide();
            });
            $('#request_date_picker').on('change', function() {
                $('#request_date-error').hide();
            });
            $(document).on('keypress', '.request-quantity', function(e) {
                $(this).parent().find('.request-max-quantity-error').hide();
                $(this).parent().find('.request-min-quantity-error').hide();
                $(this).parent().find('.request-quantity-error').hide();
            });
            $(document).on('change', '.request-category', function(e) {
                $(this).parent().find('.request-category-error').hide();
                $(this).parent().find('.request-category-duplicate-error').hide();
            });
            $('.request-quantity').on('keydown', function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && (e.which < 96 || e.which > 105)) {
                    return false;
                }
            });
        })(jQuery);
        function formatReponse (response) {
            if (response.loading) {
                return response.text;
            }

            return markup = (response.avatar_url) ?
                "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" +
                        "<img style=\"margin-right:8px;max-width: 32px;max-height: 32px;border-radius: 50%;\" src=\""+
                        response.avatar_url+"\">" + response.text +
                    "</div>" +
                "</div>"
                :
                "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" +
                        "<i style='margin-right:8px' class='fa fa-user-circle fa-2x' aria-hidden='true'></i>" +
                        response.text +
                    "</div>" +
                "</div>";
        }

        function formatReponseSelection (response, domSpan) {
            if (typeof response.dataMore === 'object') {
                var domSelect = domSpan.closest('.select2.select2-container')
                    .siblings('select').first();
                $.each(response.dataMore, function (key, value) {
                    domSelect.data('select2-more-' + key, value);
                });
            }
            return  response.text;
        }

        function validateSubmitRequest() {
            var status = 0;
            $('.asset-error').hide();
            $('#duplicate_asset_category .request-quantity').each(function() {
                if ($(this).val() === '' || $(this).val() === null) {
                    status = 1;
                    $(this).parent().find('.request-max-quantity-error').hide();
                    $(this).parent().find('.request-min-quantity-error').hide();
                    $(this).parent().find('.request-quantity-error').show();
                } else if ($(this).val().length > MAX_CHARACTER_QUANTITY_ASSET_REQUEST) {
                    status = 1;
                    $(this).parent().find('.request-quantity-error').hide();
                    $(this).parent().find('.request-min-quantity-error').hide();
                    $(this).parent().find('.request-max-quantity-error').show();
                } else {
                    if ($(this).val() < 1) {
                        status = 1;
                        $(this).parent().find('.request-quantity-error').hide();
                        $(this).parent().find('.request-max-quantity-error').hide();
                        $(this).parent().find('.request-min-quantity-error').show();
                    }
                }
            });
            var categoryDuplicate = [];
            $('#duplicate_asset_category .request-category').each(function() {
                $(this).parent().find('.request-category-error').hide();
                $(this).parent().find('.request-category-duplicate-error').hide();
                if ($(this).val() === '' || $(this).val() === null) {
                    status = 1;
                    $(this).parent().find('.request-category-error').show();
                } else {
                    if (jQuery.inArray($(this).val(), categoryDuplicate) >= 0) {
                        status = 1;
                        $(this).parent().find('.request-category-duplicate-error').show();
                    } else {
                        categoryDuplicate[$(this).val()] = $(this).val();
                    }
                }
            });

            var employeeId = $('#employee_id').val();
            if (employeeId === '' || employeeId === null) {
                status = 1;
                $('#employee_id-error').show();
            }
            var reviewer = $('#box_reviewer').val();
            if (reviewer === '' || reviewer === null) {
                status = 1;
                $('#reviewer-error').show();
            }
            var requestName = $('#request_name').val();
            if (requestName === '' || requestName === null) {
                status = 1;
                $('#request_name-error').show();
            } else {
                if (requestName.length > 250) {
                    status = 1;
                    $('#request_name_long-error').show();
                } else {
                    $('#request_name_long-error').hide();
                }
            }
            var requestDate = $('#request_date').val();
            if (requestDate === '' || requestDate === null) {
                status = 1;
                $('#request_date-error').show();
            }
            var requestReason = $('#request_reason').val().trim();
            if (requestReason === '' || requestReason === null) {
                status = 1;
                $('#request_reason-error').show();
            }
            var skype = $('#skype').val().trim();
            if (skype === '' || skype === null) {
                status = 1;
                $('#skype-error').show();
            }
            if (status === 1) {
                return false;
            }
            return true;
        }

        function changeRequestNameAsset() {
            select = $('#duplicate_asset_category').find('select option:selected');
            text = '<?php echo $newDefaultParams.' - '.trans('asset::view.Property request').": "?>';
            select.each(function(index){
                    if (index === 1 || index === 2 ) {
                        text = text + ', ' + $(this).text();
                    }
                    if (index === 0) {
                        text = text + $(this).text();
                    }
                })
            if (select.length > 3) {
                text = text + '...'; 
            }
            $('#request_name').val(text);
        }
        var countBoxAssetCategory = $('#duplicate_asset_category').children('.box-number').length;
        if (isCreate === true || isCreate === 'true') {

            changeRequestNameAsset();
        }
        
        $(document).on('click', '.btn-add-category-box', function(e) {
            e.preventDefault();
            var html = $('#box_duplicate_asset_category').html();
            countBoxAssetCategory++;
            $('#duplicate_asset_category').append(html);
            $('#duplicate_asset_category').find('.input-name').last().attr('name','asset['+countBoxAssetCategory+'][name]');
            $('#duplicate_asset_category').find('.input-number').last().attr('name','asset['+countBoxAssetCategory+'][number]');
            $('#duplicate_asset_category .request-select-2-new').select2({
                minimumResultsForSearch: 5,
            });

            $('.request-quantity').on('keydown', function (e) {
                if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                    return false;
                }
            });
           changeRequestNameAsset();
        });
        $(document).on('click', '.btn-delete-category-box', function(e) {
            e.preventDefault();
            $(this).parent().parent().parent().remove();
            changeRequestNameAsset();
        });
    </script>
    <script>
        $(document).on('change', "#selectAsset", function() {
            changeRequestNameAsset();
        });
    </script>
    @if ($windowScript = Session::get('window_script'))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
        <script>
            bootbox.confirm({
                message: '<?php echo trans('asset::message.You need reload page to view changed') ?>',
                className: 'modal-warning',
                callback: function (result) {
                    if (result) {
                        parent.location.reload();
                    }
                }
            });
        </script>
    @endif
@endsection
