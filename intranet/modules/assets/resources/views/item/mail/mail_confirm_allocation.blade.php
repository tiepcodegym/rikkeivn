<?php 
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    <p>{{ trans('asset::view.Bellow here are the newly allocated asset for you:') }}</p>
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" id="table_asset">
        <thead>
        <tr>
            <th class="width-25">{{ trans('core::view.NO.') }}</th>
            <th class="width-70 sorting" data-order="asset_code" >{{ trans('asset::view.Asset code') }}</th>
            <th class="width-100 sorting" data-order="asset_name">{{ trans('asset::view.Asset name') }}</th>
            <th class="width-90 sorting" data-order="category_name">{{ trans('asset::view.Asset category') }}</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 0; ?>
        @foreach($data['list_asset'] as $item)
            <?php $i++; ?>
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category_name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p>{!! trans('asset::view.Please click on the link below to confirm that the above asset are true for the asset you received.') !!}</p>
    <p><a href="{{ $data['href'] }}">{{ trans('asset::view.View detail') }}</a></p>
@endsection