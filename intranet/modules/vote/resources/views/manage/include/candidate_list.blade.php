<?php
use Carbon\Carbon;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('vote::view.no.') }}</th>
                        <th>{{ trans('vote::view.avatar') }}</th>
                        <th>{{ trans('vote::view.candidate_name') }}</th>
                        <th>{{ trans('vote::view.candidate_email') }}</th>
                        <th>{{ trans('vote::view.description') }} <i class="fa fa-spin fa-refresh desc-loading hidden"></i></th>
                        <th>{{ trans('vote::view.count_vote') }}</th>
                        @if ($permissEdit && isset($allowDelete) && $allowDelete)
                        <th>{{ trans('vote::view.action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && !$collectionModel->isEmpty())
                        <?php
                        $perPage = $collectionModel->perPage();
                        $page = $collectionModel->currentPage();
                        ?>
                        @foreach($collectionModel as $order => $item)
                            <tr>
                                <td>{{ $order + ($page - 1) * $perPage + 1 }}</td>
                                <td>
                                    <?php 
                                    $avatarUrl = $item->avatar_url; 
                                    if (!$avatarUrl) {
                                        $avatarUrl = '/common/images/noavatar.png';
                                    }
                                    ?>
                                    <img width="50" src="{{ $avatarUrl }}" alt="rikkei.vn">
                                </td>
                                <td class="td-nominee-name">{{ $item->name }}</td>
                                <td>{{ $item->email }}</td>
                                <td class="edit-input" data-vote-nominee="{{ $item->vote_nominee_id }}" data-url="{{ route('vote::manage.vote_nominee.update_desc', ['id' => $item->vote_nominee_id]) }}">
                                    <div class="value white-space-pre">{{ $item->description }}</div>
                                    @if ($permissEdit)
                                    <textarea class="edit-value form-control no-resize" rows="4">{{ $item->description }}</textarea>
                                    <button type="button" class="btn edit_btn btn-sm btn-default"><i class="fa fa-pencil"></i></button>
                                    @endif
                                </td>
                                <td><a data-url="{{ route('vote::manage.voter.load_data', ['vote_nominee_id' => $item->vote_nominee_id]) }}" 
                                       href="#voter_modal" data-toggle="modal" class="btn btn-info min-w-50" 
                                       title="{{ trans('vote::view.click_to_view') }}">{{ $item->count_vote }}</button></a>
                                @if ($permissEdit && isset($allowDelete) && $allowDelete)
                                <td>
                                    {!! Form::open(['method' => 'delete', 'route' => ['vote::manage.vote_nominee.delete', $item->vote_nominee_id], 'class' => 'form-inline']) !!}
                                    <input type="hidden" name="tab_id" value="#candidate_list">
                                    <button type="submit" class="btn-delete delete-confirm" data-toggle="tooltip" data-placement="top" title="{{ trans('vote::view.delete') }}"><i class="fa fa-trash"></i></button>
                                    {!! Form::close() !!}
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ $permissEdit && isset($allowDelete) && $allowDelete ? '7' : '6' }}" class="text-center">
                                <h3 class="no-result-grid">{{trans('vote::message.not_found_item')}}</h3>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="box-body">
            @include('team::include.pager')
        </div>
    </div>
</div>