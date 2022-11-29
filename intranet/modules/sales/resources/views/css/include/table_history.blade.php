<?php
    use Rikkei\Sales\Model\Css;
    use Illuminate\Support\Facades\Config as SupportConfig;

    $lang = SupportConfig::get('langs.'.$css->lang_id);
    if ($lang == null) {
        $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
    }
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<div class="table-responsive">
    <table class="table dataTable" role="grid">
        <thead>
            <tr role="row">
                <th>{{trans('sales::view.Date work css',[],'',$lang)}}</th>
                <th>{{trans('sales::view.Make name jp history',[],'',$lang)}}</th>
                <th>{{trans('sales::view.table total point',[],'',$lang)}}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($listWork as $valueList)
            <tr>
                <td><a href="{{route('sales::detailCustomer',['cssResult'=>$valueList->id,'c'=>$valueList->code])}}" target="_blank">{{$valueList->created_at}}</a></td>
                <td>{{$valueList->name}} @if($lang == "ja")様 @endif</td>
                <td>{{$valueList->avg_point}}</td>
                <td><a class="button btn-success btn-sm pull-right" href="{{route('sales::detailCustomer',['cssResult'=>$valueList->id,'c'=>$valueList->code])}}" target="_blank"><i class="fa fa-info-circle"></i></a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>