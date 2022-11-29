<div class="box box-primary box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('doc::view.List versions') }}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-version" id="list_versions">
                <thead>
                    <tr>
                        <th>{{ trans('doc::view.Version') }}</th>
                        <th>{{ trans('doc::view.File name') }}</th>
                        <th>{{ trans('doc::view.Author') }}</th>
                        <th>{{ trans('doc::view.Created time') }}</th>
                        <th></th>
                    </tr>
                </thead>
                @if (!$listFiles->isEmpty())
                    @foreach ($listFiles as $file)
                        @include('doc::includes.doc-version-item')
                    @endforeach
                @endif
            </table>
        </div>
        
        <div class="history-paginate text-center">
            {!! $listFiles->links() !!}
        </div>
    </div>
</div>