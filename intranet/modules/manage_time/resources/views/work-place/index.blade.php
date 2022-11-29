@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info filter-wrapper">
                <div class="box-body filter-mobile-left">
                    <div class="row">
                        <div class="col-sm-7"></div>
                        <div class="col-sm-5">
                            <div class="filter-action">
                                <button id="export_list" class="btn btn-success" data-url="{{ route('manage_time::profile.wpmanagement.export') }}">
                                    <i class="fa fa-download"></i>
                                    {{ trans('manage_time::view.Export') }}
                                </button>
                                <button class="btn btn-primary import-file" data-toggle="modal" data-target="#importFile">{{ trans('manage_time::view.Import') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="importFile" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <form action="{{ route('manage_time::profile.wpmanagement.import') }}" method="post" enctype="multipart/form-data" id="form-import-supplier">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="control-label">{{ trans('asset::view.Choose file import') }}</label>
                                    <div class="input-box">
                                        <input type="file" name="file" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" placeholder="{{ trans('asset::view.Add file') }}" />
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                                    <button type="submit" class="btn btn-primary pull-right">{{ trans('asset::view.Import') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);

            // Export Excel
            $('#export_list').click(function (e) {
                e.preventDefault();
                var form = document.createElement('form');
                form.setAttribute('method', 'get');
                form.setAttribute('action', $(this).data('url'));
                var params = {
                    _token: siteConfigGlobal.token,
                };

                document.body.appendChild(form);
                form.submit();
                form.remove();
            });

        });
    </script>
@endsection
