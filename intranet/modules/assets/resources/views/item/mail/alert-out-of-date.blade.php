<?php 
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();

    extract($data);
?>

@extends($layout)

@section('css')
<style>
    table._asset_table {
        border-collapse: collapse;
        width: 100%;
    }
    table._asset_table tr td, table._asset_table tr th {
        padding: 7px 12px;
        border: 1px solid #ddd;
    }
</style>
@endsection

@section('content')
    <p>Xin chào {!! isset($empName) ? '<strong>' . $empName . '</strong>' : 'Anh/Chị' !!},</p>
    <p></p>

    <p>(Những) Tài sản sau sắp hết hạn sử dụng, vui lòng bàn giao tài sản!</p>
    <table class="_asset_table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Mã tài sản</th>
                <th>Tên tài sản</th>
                <th>Ngày hết hạn</th>
                <th>Tên người sử dụng</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listAssets as $order => $item)
                <tr>
                    <td>{{ $order + 1 }}</td>
                    <td>{{ $item['code'] }}</td>
                    @if ($isAdmin)
                    <td><a href="{{ route('asset::asset.view', ['id' => $item['id']]) }}" target="_blank">{{ $item['name'] }}</a></td>
                    @else
                    <td>{{ $item['name'] }}</td>
                    @endif
                    <td>{{ $item['out_of_date'] }}</td>
                    <td>
                        {{ $item['emp_name'] }} <br />
                        ({{ $item['emp_email'] }})
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!$isAdmin)
    <p></p>
    <p><a href="{{ route('asset::profile.view-personal-asset') }}">{{ trans('asset::view.View detail') }}</a></p>
    @endif
@endsection