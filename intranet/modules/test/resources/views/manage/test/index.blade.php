@extends('layouts.default')

@section('title', trans('test::test.test'))

@section('css')

@include('test::template.css')

@endsection

@section('content')

<?php
use Rikkei\Test\Models\Test;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Config;
use Rikkei\Test\View\ViewTest;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Test\Models\Type;

$allLangs = Rikkei\Core\View\CoreLang::allLang();
$aryTypes = $types->pluck('name', 'id')->toArray();
?>

<div class="box box-info">

    <div class="box-body">
        <div class="table_nav">
            <div class="pull-left">   
                <div class="btn_actions">
                    <a href="{{route('test::admin.test.create')}}" class="create-btn btn-add" data-toggle="tooltip" title="{{trans('test::test.add_new')}}" data-placement="top">
                        <i class="fa fa-plus"></i> <span class="">{{trans('test::test.add_new')}}</span>
                    </a>
                    @if(!$collectionModel->isEmpty())
                    <a href="{{route('test::admin.test.m_action')}}" action="delete" class="m_action_btn btn btn-danger"
                       data-noti="{{ trans('test::validate.Are you sure want to delete') }}"
                       data-toggle="tooltip" title="{{trans('test::test.delete')}}" data-placement="top">
                        <i class="fa fa-trash"></i> <span class="">{{trans('test::test.delete')}}</span>
                    </a>
                    <button type="button" class="btn btn-primary btn-reset-random"
                            data-toggle="tooltip" title="{{trans('test::test.reset_random_description')}}" data-placement="top"
                            data-noti="{{ trans('test::validate.Are you sure want to reset') }}"
                            data-url="{{ route('test::admin.test.reset_random') }}">{{ trans('test::test.reset_random') }}</button>
                    @endif
                </div>
            </div>
            
            @include('team::include.filter')
            
            <div class="clearfix"></div>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="list_test_tbl" class="table table-hover table-striped dataTable table-bordered">
            <thead>
                <tr>
                    <th width="30"><input type="checkbox" name="massdel" class="check_all" style="vertical-align: text-top; margin-top: 3px;" /></th>
                    <th>No.</th>
                    <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('test::test.name') }}</th>
                    <th>{{ trans('test::test.link') }}</th>
                    <th class="sorting {{ Config::getDirClass('is_auth') }} col-name" data-order="is_auth" data-dir="{{ Config::getDirOrder('is_auth') }}">{{ trans('test::test.publish') }}</th>
                    <th class="sorting {{ Config::getDirClass('count_questions') }} col-name" data-order="count_questions" data-dir="{{ Config::getDirOrder('count_questions') }}">{{ trans('test::test.questions_number') }}</th>
                    <th class="sorting {{ Config::getDirClass('display_question') }} col-name" data-order="display_question" data-dir="{{ Config::getDirOrder('display_question') }}">{{ trans('test::test.questions_number_test') }}</th>
                    <th class="sorting {{ Config::getDirClass('type_id') }} col-name" data-order="type_id" data-dir="{{ Config::getDirOrder('type_id') }}">{{ trans('test::test.test_type') }}</th>
                    <th class="sorting {{ Config::getDirClass('time') }} col-name" data-order="time" data-dir="{{ Config::getDirOrder('time') }}">{{ trans('test::test.end time')}} ({{trans('test::test.minute') }})</th>
                    <th class="sorting {{ Config::getDirClass('created_at') }} col-name" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('test::test.time_created') }}</th>
                    <th class="sorting {{ Config::getDirClass('email') }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email') }}">{{ trans('test::test.creator') }}</th>
                    <th>{{ trans('test::test.Language') }}</th>
                    <th class="sorting {{ Config::getDirClass('count_result') }} col-name" data-order="count_result" data-dir="{{ Config::getDirOrder('count_result') }}">{{ trans('test::test.result') }}</th>
                    <th class="sorting {{ Config::getDirClass('count_result_pl') }} col-name" data-order="count_result_pl" data-dir="{{ Config::getDirOrder('count_result_pl') }}">{{ trans('test::test.result_public') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="text" name="filter[test.name]" value="{{ FormView::getFilterData('test.name') }}" 
                               placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td></td>
                    <td>
                        <?php 
                        $filterAuth = FormView::getFilterData('excerpt', 'is_auth');
                        ?>
                        <select name="filter[excerpt][is_auth]" class="form-control select-grid filter-grid select-search"
                                style="min-width: 100px;">
                            <option value="">&nbsp;</option>
                            <option value="0" {{ $filterAuth === '0' ? 'selected' : '' }}>{{ trans('test::test.yes') }}</option>
                            <option value="1" {{ $filterAuth === '1' ? 'selected' : '' }}>{{ trans('test::test.no') }}</option>
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <?php
                        $filterType = FormView::getFilterData('excerpt', 'type_id');
                        $typesOptions = Type::toNestedOptions($types, $filterType);
                        ?>
                        <select name="filter[excerpt][type_id]" class="form-control select-grid filter-grid select-search">
                            <option value="">&nbsp;</option>
                            {!! $typesOptions !!}
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="text" name="filter[emp.email]" class="form-control filter-grid" 
                               value="{{ FormView::getFilterData('emp.email') }}"
                               placeholder="{{ trans('team::view.Search') }}...">
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @if(!$collectionModel->isEmpty())
                    <?php
                    $perPage = $collectionModel->perPage();
                    $currentPage = $collectionModel->currentPage();
                    ?>
                    @foreach($collectionModel as $order => $item)
                    <tr>
                        <td><input type="checkbox" name="check_items[]" class="check_item" value="{{$item->id}}"></td>
                        <td>{{ $perPage * ($currentPage - 1) + $order + 1 }}</td>
                        <td class="_break_all">{{$item->name}}</td>
                        <td class="td-link">
                            <?php $testUrl = route('test::view_test', ['code' => $item->url_code]); ?>
                            <a class="link" target="_blank" href="{{ $testUrl }}">
                                {{ ViewTest::shortLink($testUrl) }}</a>
                            <div class="link-box">{{ $testUrl }}</div>
                        </td>
                        <td class="text-center">
                            <i class="fa {{ $item->is_auth ? 'fa-square-o' : 'fa-check-square-o' }}"></i>
                        </td>
                        <td>{{ $item->count_questions }}</td>
                        <td>{{ $item->display_question }}</td>
                        <td>{{ isset($aryTypes[$item->type_id]) ? $aryTypes[$item->type_id] : null }}</td>
                        <td>{{ $item->time }}</td>
                        <td class="_nowwrap">{{ $item->created_at->format('d-m-Y') }}</td>
                        <td>{{ $item->email ? CoreView::getNickName($item->email) : '' }}</td>
                        <td class="_nowwrap">{{ $allLangs[$item->lang_code] }}</td>
                        <td>
                            <a target="_blank" href="{{ route('test::admin.test.results', ['id' => $item->id]) }}" 
                               class="btn btn-primary" data-toggle="tooltip" title="{{ trans('test::test.view_results') }}">
                                <i class="fa fa-list"></i> ({{ $item->count_result }})
                            </a>
                        </td>
                        <td>
                            <a target="_blank" href="{{ route('test::admin.test.results', ['id' => $item->id, 'tester_type' => ViewTest::TESTER_PUBLISH]) }}" class="btn btn-info" 
                               data-toggle="tooltip" title="{{ trans('test::test.view_results_public') }}">
                                <i class="fa fa-th-list"></i> ({{ $item->count_result_pl }})
                            </a>
                        </td>
                        <td class="_nowwrap">
                            <a href="{{route('test::admin.test.show', ['id' => $item->id])}}" data-toggle="tooltip" title="{{trans('test::test.view')}}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                            <a href="{{route('test::admin.test.edit', ['id' => $item->id, 'lang' => $item->lang_code])}}" data-toggle="tooltip" title="{{trans('test::test.edit')}}" class="btn-edit btn-edit-test"><i class="fa fa-edit"></i></a>
                            {!! Form::open(['class' => 'form-inline', 'method' => 'delete', 'route' => ['test::admin.test.destroy', $item->id]]) !!}
                            <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" title="{{trans('test::test.delete')}}"><i class="fa fa-trash"></i></button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="15" class="text-center">{{trans('test::test.no_item')}}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="cleafix"></div>
    
    <div class="box-body">
        @include('team::include.pager')
    </div>

</div>

@stop

@section('script')

@include('test::template.script')

@stop

