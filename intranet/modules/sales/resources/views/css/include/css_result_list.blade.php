@if ($result && count($result))
    @foreach ($result as $item)
    <tr class="bg-gray" data-id="{{$itemId}}">
        <td rowspan="1" colspan="1" class="text-align-center">{{ $item->id }}</td>
        <td rowspan="1" colspan="1" ></td>
        <td rowspan="1" colspan="1" ></td>
        <td rowspan="1" colspan="1" >{{ number_format($item->avg_point,2) }}</td>
        <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
        <td>{{ $item->analyze_status }}</td>
        <td>
            <a title="{{trans('sales::view.CSS.List.View detail')}}" href="{{route('sales::css.detail', ['id' => $item->id])}}">{{ trans('sales::view.View') }}</a>
        </td>
    </tr>
    @endforeach
@endif

