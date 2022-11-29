@extends('layouts.default')

<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Assets\View\AssetConst;
use Carbon\Carbon;

$listState = AssetConst::listInventoryState();
$minDate = $item ? $item->time : Carbon::now()->format('Y-m-d H:i');
?>

@section('title', trans('asset::view.Inventory assets') . ' - ' . ($item ? trans('asset::view.Edit') : trans('asset::view.Create new')))

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('manage_asset/css/style.css') }}" />
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-body">
            
            {!! Form::open(['method' => 'post', 'route' => 'asset::inventory.save', 'class' => 'no-validate']) !!}
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group" style="margin-bottom: 27px;"></div>
                    <div class="form-group">
                        <label>{{ trans('asset::view.Name') }} <em class="text-red">*</em></label>
                        <input type="text" max="255" class="form-control" name="name" value="{{ old('name') ? old('name') : ($item ? $item->name : null) }}">
                    </div>
                    
                    <div class="form-group">
                        <label>{{ trans('asset::view.Status') }} <em class="text-red">*</em></label>
                        <?php $status = old('status') ? old('status') : ($item ? $item->status : null) ?>
                        <select class="form-control select-search" name="status">
                            @foreach ($listState as $value => $label)
                            <option value="{{ $value }}" {{ $status == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>{{ trans('asset::view.Time end') }} <em class="text-red">*</em></label>
                        <div class="input-group datetime-picker">
                            <input type="text" class="form-control" name="time" autocomplete="off"
                                   value="{{ old('time') ? old('time') : ($item ? $item->time : null) }}">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-container">
                        <label><input type="checkbox" class="checkbox-all"> {{ trans('asset::view.Department') }} <em class="text-red">*</em></label>
                        <?php
                        $currTeamIds = [];
                        if ($teams && !$teams->isEmpty()) {
                            $currTeamIds = $teams->lists('id')->toArray();
                            ?>
                            <span>: ({{ $teams->implode('name', ', ') }})</span>
                            <?php
                        }
                        ?>
                        <div class="checkbox-group">
                            <ul class="list-unstyled">
                            {!! AssetConst::toNestedCheckbox($teamList, old('team_ids') ? old('team_ids') : $currTeamIds) !!}
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6">
                    <div>
                        <label><input type="checkbox" id="check_mail_send" name="mail[is_send]" value="1"> {{ trans('asset::view.Send mail') }}</label>
                    </div>
                    
                    <div class="form-group">
                        <label>{{ trans('asset::view.Email subject') }}</label>
                        <input type="text" name="mail[subject]" value="{{ old('mail.subject') ? old('mail.subject') : ($item ? $item->name : null) }}" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>{{ trans('asset::view.Email content') }}</label>
                        <textarea class="form-control" rows="30" id="inventory_mail_content" name="mail[content]">{!! old('mail.content') ? old('mail.content') : $mailContent !!}</textarea>
                    </div>
                    
                    <div class="hint-note">
                        <p>&#123;&#123; name &#125;&#125;: Name</p>
                        <p>&#123;&#123; account &#125;&#125;: Account</p>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                @if ($item)
                <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <a href="{{ route('asset::inventory.index') }}" class="btn btn-warning"><i class="fa fa-long-arrow-left"></i> {{ trans('asset::view.Back to list') }}</a>
                <button type="submit" class="btn btn-primary" id="inventory-submit-btn"
                        data-noti="{{ trans('asset::message.Save change will send mail, are you sure?') }}"><i class="fa fa-save"></i> {{ trans('asset::view.Save') }}</button>
            </div>
            
            {!! Form::close() !!}
            
        </div>
    </div>
    <!-- /. box -->
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
    <script src="{{ CoreUrl::asset('manage_asset/js/request_asset.js') }}"></script>
    <script>
        (function ($) {
            $('.datetime-picker').datetimepicker({
                format: 'YYYY-MM-DD HH:mm',
                showClose: true,
                minDate: '{{ $minDate }}',
            });
            
            $('.checkbox-container .checkbox-all').click(function () {
                $('.checkbox-container .checkbox-group input').prop('checked', $(this).is(':checked'));
            });
            $('.checkbox-container .checkbox-group input').click(function () {
                var inputLen = $('.checkbox-container .checkbox-group input').length;
                var checkedLen = $('.checkbox-container .checkbox-group input:checked').length;
                $('.checkbox-container .checkbox-all').prop('checked', inputLen === checkedLen);
            });

            $(document).ready(function () {
                RKfuncion.CKEditor.init(['inventory_mail_content']);
            });
        })(jQuery);
    </script>

    <script>
        $('li').change(function () {
            var status = $('input[type="checkbox"]', this).is(':checked');
            var parent = $(this).data('parent');
            var className =  'parent-' + parent;
            var depth = $(this).data('depth');
            var id = $(this).data('id');
            var nextChks = $(this).nextUntil('.' + className);
            if (nextChks && nextChks.length > 0) {
            for (var i = 0; i < nextChks.length; i++) {
                if ($(nextChks[i]).data('parent') == 1) break;
                if (depth == 2 && ($(nextChks[i]).data('parent') != id)) break;
                if (depth == 3 && ($(nextChks[i]).data('parent') != id)) break;
                $('input[type="checkbox"]', nextChks[i]).attr('checked', status).prop('checked', status);
                }
            }
        });
    </script>
@endsection
