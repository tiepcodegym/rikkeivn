<?php
use Rikkei\Vote\View\VoteConst;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th>{{ trans('vote::view.no.') }}</th>
                        <th>{{ trans('vote::view.avatar') }}</th>
                        <th>{{ trans('vote::view.name') }}</th>
                        <th>{{ trans('vote::view.email') }}</th>
                        <th>{{ trans('vote::view.confirm') }}</th>
                        <th>{{ trans('vote::view.count_nominate') }}</th>
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
                                <td>{{ VoteConst::getConfirmLabel($item->confirm) }}</td>
                                <td><a data-url="{{ route('vote::manage.nominator.load_data', ['vote_id' => $item->vote_id, 'nominee_id' => $item->nominee_id]) }}" 
                                       href="#nominator_modal" data-toggle="modal" class="btn btn-info min-w-50" 
                                       title="{{ trans('vote::view.click_to_view') }}">{{ $item->count_nominate }}</button></a>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">
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