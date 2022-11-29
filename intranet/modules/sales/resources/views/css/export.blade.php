@extends('layouts.default')
@section('title')
    {{ trans('sales::view.export_css', ['year' => $year]) }}
@endsection
@section('content')

    <div class="row export-css-page">
        <div class="box box-primary">
            <div class="box-body">
                <div class="col-lg-4 col-md-12">
                    @php
                        $cty = [];
                        $teams = [];
                        $quarter = 0;
                    @endphp
                    @foreach($result as $item)
                        @php
                            $cty[$item->quarter][] =  $item->avg_point;
                            $teams[$item->team_name][$item->quarter] = round($item->avg_point, 2);
                            if ($item->quarter > $quarter) {
                                $quarter = $item->quarter;
                            }
                        @endphp
                    @endforeach

                    <table class="table">
                        <tr class="bg-info">
                            <th scope="col">{{ trans('sales::view.team') }}</th>
                            @for($i=1; $i <= $quarter; $i++)
                                <th scope="col">{{ trans('sales::view.quarter') }} {{ $i }}</th>
                            @endfor
                        </tr>
                        @foreach($teams as $teamName => $data)
                            @php
                                asort($data)
                            @endphp
                            <tr>
                                <td>{{ $teamName }}</td>
                                @for($i=1; $i <= $quarter; $i++)
                                    <td>{{ data_get($data, $i, 0) }}</td>
                                @endfor
                            </tr>
                        @endforeach
                    </table>


                </div>
                <div class="col-lg-4 col-md-12">
                    <table class="table">
                        <tr class="bg-info">
                            <th scope="col" colspan="4"
                                style="text-align: center !important;">{{ trans('sales::view.export_css_all', ['year' => $year]) }}</th>
                        </tr>
                        <tr>
                            @for($i=1; $i <= $quarter; $i++)
                                <th scope="col">{{ trans('sales::view.quarter') }} {{ $i }}</th>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 1; $i <= $quarter; $i++)
                                @php
                                    $data = data_get($cty, $i, []);
                                    $point = 0;
                                    if (!empty($data)) {
                                        $point = array_sum($data)/count($data);
                                    }
                                @endphp
                                <td>{{ round($point, 2) }}</td>
                            @endfor
                        </tr>
                    </table>
                </div>

                <div class="col-md-12">
                    <table class="table">
                        <tr class="bg-info">
                            <th scope="col">#</th>
                            <th scope="col">{{ trans('sales::view.project') }}</th>
                            <th scope="col">{{ trans('sales::view.team') }}</th>
                            <th scope="col">{{ trans('sales::view.comment') }}</th>
                            <th scope="col">{{ trans('sales::view.feedback') }}</th>
                        </tr>

                        @php
                            $i = 0;
                        @endphp
                        @foreach($resultComment as $k => $item)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $item->proj_name }}</td>
                                <td>{{ $item->team_name }}</td>
                                <td>{!! str_replace("\n", "<br>", $item->css_comment) !!}</td>
                                <td>{!! str_replace("\n", "<br>", $item->css_analysis) !!}</td>
                            </tr>
                        @endforeach
                    </table>

                </div>
            </div>
        </div>

    </div>

@endsection
