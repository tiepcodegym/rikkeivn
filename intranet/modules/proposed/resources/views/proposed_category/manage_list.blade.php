@extends('layouts.default')


<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    use Rikkei\Proposed\Model\ProposedCategory;

    $perPage = $collectionModel->perPage();
    $perPage = $perPage ? (int)$perPage : 10;
    $currentPage = $collectionModel->currentPage();
    $currentPage = $currentPage ? (int)$currentPage : 1;
    $arrStatus = ProposedCategory::getStatus();
?>

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/reason_list.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-2">
            @include('proposed::nav_left')
        </div>
        <div class="col-sm-10">
            @include('proposed::message')
            <div class="box box-info">
                <div class="box-body">
                    <div class="col-sm-8">
                        <button id="modal-add" class="btn btn-success">
                            <i class="fa fa-plus" aria-hidden="true"></i> {{ trans('proposed::view.Add new') }}
                        </button>
                    </div>
                    <div class="col-sm-4">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table dataTable table-striped table-grid-data table-hover table-bordered list-ot-table">
                    <thead class="list-head">
                        <tr>
                            <th class="col-id width-10">{{ trans('manage_time::view.No.') }}</th>
                            <th>{{ trans('proposed::view.Name category') }}</th>
                            <th>{{ trans('proposed::view.Creator') }}</th>
                            <th>{{ trans('proposed::view.Status') }}</th>
                            <th class="managetime-col-85 width-150 text-center"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @for($i=0; $i< $collectionModel->count();$i++)
                            <tr
                                row-id='{{$collectionModel[$i]->id}}'
                                row-name-vi='{{ $collectionModel[$i]->name_vi }}'
                                row-name-en='{{ $collectionModel[$i]->name_en }}'
                                row-name-ja='{{ $collectionModel[$i]->name_ja }}'
                                row-status='{{ $collectionModel[$i]->status }}'
                                >
                                <td>{{$perPage * ($currentPage -1) + $i + 1}}</td>
                                <td>
                                    VI: {{$collectionModel[$i]->name_vi}}<br>
                                    EN: {{$collectionModel[$i]->name_en}}<br>
                                    JA: {{$collectionModel[$i]->name_ja}}
                                </td>
                                <td>{{ $collectionModel[$i]->nameEmp}}</td>
                                <td>{{ $arrStatus[$collectionModel[$i]->status] }}</td>
                                <td>
                                    <button class="btn-edit edit-row" edit-id="{{$collectionModel[$i]->id}}">
                                        <i class="fa fa-pencil-square-o" aria-hidden="true" ></i>
                                    </button>
                                    <form class="form-delete" action="{{URL::route('proposed::manage-proposed.category.delete', $collectionModel[$i]->id)}}" method="POST">
                                        {{ csrf_field() }}
                                        <button class="btn-delete delete-confirm" type="submit"><i class="fa fa-remove" aria-hidden="true"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>

        <!-- modal form to add and edit proposed category -->
        <div id="modal-create-edit" class="modal fade" role="dialog" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-center">{{ trans('proposed::view.Add proposed category') }}</h4>
                    </div>
                    <form method="POST" action="{{URL::route('proposed::manage-proposed.category.store')}}" id="form-modal-submit">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="" id="id_cat">
                        <div class="row">
                            <div class="col-sm-8 col-sm-offset-1">
                                <div class="modal-body">
                                    <div class="row form-group">
                                        <div class="col-md-3 text-right">
                                            <label for="name category">VI:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" name="name[vi]" class="form-control" id="name_vi">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-md-3 text-right">
                                            <label for="name category">EN:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="name[en]" id="name_en">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <div class="col-md-3 text-right">
                                            <label for="name category">JA:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="name[ja]" id="name_ja">
                                        </div>
                                    </div>
                                    
                                    <div class="row form-group">
                                        <div class="col-md-3 text-right">
                                            <label for="slug">{{ trans('proposed::view.Status') }}:</label>
                                        </div>
                                        <div class="col-md-9">
                                            <select name="status" id="status" class="form-control">
                                                @foreach($arrStatus as $key => $status)
                                                    <option value="{{$key}}"
                                                    @if ($key == ProposedCategory::STATUS_ACTIVE)
                                                        selected 
                                                    @endif
                                                    >{{$status}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="text-align: center !important;">
                            <button type="button" class="btn btn-default" id="close_form">{{ trans('manage_time::view.Close') }}</button>
                            <button type="submit" class="btn btn-primary" id="add_submit">{{ trans('manage_time::view.Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="{{ CoreUrl::asset('proposed/js/proposed_category.js') }}"></script>
    <script>
        var titleCreate = "<?php echo trans('proposed::view.Add proposed category') ?>";
        var titleUpdate = "<?php echo trans('proposed::view.Update proposed category') ?>";
        var urlCreate = "{{URL::route('proposed::manage-proposed.category.store')}}";
        var urlUpdate = "{{URL::route('proposed::manage-proposed.category.update')}}";
        var statusActive = "{{ ProposedCategory::STATUS_ACTIVE }}";
    </script>
    <script>

    </script>
@endsection

