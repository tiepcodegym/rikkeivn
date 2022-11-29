<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data th-middle">
        <thead>
            <tr>
                <th>{{ trans('vote::view.no.') }}</th>
                <th>{{ trans('vote::view.nominator_name') }}</th>
                <th>{{ trans('vote::view.nominator_email') }}</th>
                <th>{{ trans('vote::view.vote_time') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($listVoters) && !$listVoters->isEmpty())
                <?php
                $perPage = $listVoters->perPage();
                $page = $listVoters->currentPage();
                ?>
                @foreach($listVoters as $order => $item)
                    <tr>
                        <td>{{ $order + ($page - 1) * $perPage + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">
                        <h3 class="no-result-grid">{{trans('vote::message.not_found_item')}}</h3>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="box-body">
    @include('team::include.pager', ['collectionModel' => isset($listVoters) ? $listVoters : null])
</div>

