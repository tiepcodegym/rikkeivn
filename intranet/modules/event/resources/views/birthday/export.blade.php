<?php
    use Rikkei\Core\View\View;
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        table tr{
            text-align: left;
        }
        table tr td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td>STT</td>
            <td>Tên</td>
            <td>Email</td>
            <td>Công ty</td>
            <td>Trạng thái</td>
            <td>Người tham gia</td>
            <td>Tham gia tuor</td>
            <td>Đăng ký khách sạn</td>
            <td>Tên người gửi</td>
            <td>Email người gửi</td>
            <td>Note</td>
            <td>Gửi lúc</td>
        </tr>

        @if(count($dataCollection) > 0)
            @foreach($dataCollection as $i => $item)
                @php
                    $attachers = json_decode($item->attacher, true);
                    $cRow = count($attachers);
                @endphp
                @if (count($attachers))
                    @foreach(json_decode($item->attacher, true) as $key => $attach)
                    <tr>
                        @if ($key == 0)
                            <td rowspan="{{ $cRow }}">{{ $i+1 }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->name }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->email }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->company }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->getStatus($statusOptions) }}</td>
                        @endif
                        <td>
                            {{ $attach['name'] }} <br>
                            {{ $attach['alphabet'] }} <br>
                            {{ $attach['company'] }} <br>
                            {{ $attach['email'] }} <br>
                            @if (isset($attach['tour']))
                                Tham gia tour {{ $item->getJoinTour() }}
                            @endif
                        </td>
                        @if ($key == 0)
                            <td rowspan="{{ $cRow }}">{{ $item->getJoinTour() }}</td>   
                            <td rowspan="{{ $cRow }}">{{ is_null($item->booking_room) ? '' : ($item->booking_room == 0 ? 'Không' : 'Có') }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->sender_name }}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->sender_email }}</td>
                            <td rowspan="{{ $cRow }}">{!! View::nl2br($item->note) !!}</td>
                            <td rowspan="{{ $cRow }}">{{ $item->updated_at }}</td>                                         
                        @endif
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->company }}</td>
                        <td>{{ $item->getStatus($statusOptions) }}</td>
                        <td>{{ $item->getJoinTour() }}</td>
                        <td></td>
                        <td>{{ is_null($item->booking_room) ? '' : ($item->booking_room == 0 ? 'Không' : 'Có') }}</td>
                        <td>{{ $item->sender_name }}</td>
                        <td>{{ $item->sender_email }}</td>
                        <td>{!! View::nl2br($item->note) !!}</td>
                        <td>{{ $item->updated_at }}</td>
                    </tr>
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="12" class="text-center">
                    {{ trans('project::view.No results found') }}
                </td>
            </tr>
        @endif
    </table>
</body>
</html>