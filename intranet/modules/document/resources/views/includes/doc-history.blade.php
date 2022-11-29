<div class="box box-primary box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('doc::view.History') }}</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        @if (!$histories->isEmpty())
        <div id="box_history" class="list-history">
            <ul class="padding-left-15" id="list_histories">
            @foreach ($histories as $history)
                @include('doc::includes.doc-history-item')
            @endforeach
            </ul>
        </div>
        
        <div class="history-paginate text-center">
            {!! $histories->links() !!}
        </div>
        @endif
    </div>
</div>

