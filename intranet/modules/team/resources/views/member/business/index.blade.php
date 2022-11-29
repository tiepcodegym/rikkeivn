<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Config as TeamConfig;

$urlEdit = route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'business']);
$urlDelete = route('team::member.profile.item.relate.delete', ['employeeId' => $employeeModelItem->id, 'type' => 'business']);
$isShow = true;
?>
@extends('team::member.profile_row_layout')

@section('content_profile')
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover" id="business_trip_tbl">
        <thead>
            <tr>
                <th style="width:40px;">{!!trans('core::view.NO.')!!}</th>
                <th class="sorting {!!TeamConfig::getDirClass('work_place')!!}" data-order="work_place" data-dir="{!!TeamConfig::getDirOrder('work_place')!!}">{{ trans('team::profile.Work place') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('start_at')!!}" data-order="start_at" data-dir="{!!TeamConfig::getDirOrder('start_at')!!}">{{ trans('team::profile.From') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('end_at')!!}" data-order="end_at" data-dir="{!!TeamConfig::getDirOrder('end_at')!!}">{{ trans('team::profile.To') }}</th>
                <th class="sorting {!!TeamConfig::getDirClass('position')!!}" data-order="position" data-dir="{!!TeamConfig::getDirOrder('position')!!}">{{ trans('team::profile.Position staff') }}</th>
                <th class="col-action col-a2" style="width: 50px;"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="text" name="filter[work_place]" value="{{ Form::getFilterData('work_place') }}"
                           placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                </td>
                <td>
                    <input type="text" name="filter[start_at]" value="{{ Form::getFilterData('start_at') }}"
                           placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                </td>
                <td>
                    <input type="text" name="filter[end_at]" value="{{ Form::getFilterData('end_at') }}"
                           placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                </td>
                <td>
                    <input type="text" name="filter[position]" value="{{ Form::getFilterData('position') }}"
                           placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                </td>
                <td></td>
            </tr>
            <tr class="hidden new-item" id="tr_business_tpl">
                <td class="col-stt"></td>
                <td class="col-work_place">
                    <input type="text" name="work_place" class="form-control field required">
                </td>
                <td class="col-start_at">
                    <input type="text" name="start_at" class="form-control field date-picker required">
                </td>
                <td class="col-end_at">
                    <input type="text" name="end_at" class="form-control field date-picker">
                </td>
                <td class="col-position">
                    <input type="text" name="position" class="form-control field required">
                </td>
                <td class="col-actions white-space-nowrap">
                    @include('team::member.business.buttons')
                </td>
            </tr>
            @if (!$collectionModel->isEmpty())
                <?php
                $currentPage = $collectionModel->currentPage();
                $perPage = $collectionModel->perPage();
                ?>
                @foreach ($collectionModel as $order => $item)
                <tr>
                    <td class="col-stt">{{ $order + 1 + ($currentPage - 1) * $perPage }}</td>
                    <td class="col-work_place">{{ $item->work_place }}</td>
                    <td class="col-start_at">{{ $item->start_at }}</td>
                    <td class="col-end_at">{{ $item->end_at != '0000-00-00' ? $item->end_at : '' }}</td>
                    <td class="col-position">{{ $item->position }}</td>
                    <td class="white-space-nowrap col-actions">
                        <input type="hidden" name="id" value="{{ $item->id }}" class="field">
                        @include('team::member.business.buttons', ['isTpl' => false])
                    </td>
                </tr>
                @endforeach
            @else
                <tr class="none-row">
                    <td colspan="6" class="text-center">{{ trans('team::messages.No data') }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"></td>
                <td>
                    <button type="button" title="{{ trans('team::view.Add') }}" class="btn btn-primary" id="btn_add_business"><i class="fa fa-plus"></i></button>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection

@section('extra_script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    var textErrorRequired = '<?php echo trans('team::messages.This field is required') ?>';
    var textErrorEndDate = '<?php echo trans('team::messages.This field must be greater or equal the start date') ?>';
</script>
@stop
