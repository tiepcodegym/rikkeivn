<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data th-middle">
        <thead>
            <tr>
                <th>{{ trans('vote::view.no.') }}</th>
                <th>{{ trans('vote::view.nominator_name') }}</th>
                <th>{{ trans('vote::view.nominator_email') }}</th>
                <th>{{ trans('vote::view.nominate_time') }}</th>
                <th>{{ trans('vote::view.type') }}</th>
                <th class="nominator-reason">{{ trans('vote::view.reason') }}</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($listNominators) && !$listNominators->isEmpty())
                <?php
                $perPage = $listNominators->perPage();
                $page = $listNominators->currentPage();
                ?>
                @foreach($listNominators as $order => $item)
                    <tr>
                        <td>{{ $order + ($page - 1) * $perPage + 1 }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->type == 1 ? trans('vote::view.candidate') : trans('vote::view.nominate') }}</td>
                        <td>
                            <div class="white-space-pre nominator-reason content-more">{{ $item->reason }}</div>
                        </td>
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
    @include('team::include.pager', ['collectionModel' => isset($listNominators) ? $listNominators : null])
</div>

