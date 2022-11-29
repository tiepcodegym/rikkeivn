<div class="row">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width: 10%;text-align: center"  >{{ trans('contract::vi.NO.') }}</th>
                <th style="width: 60%" >{{ trans('contract::view.File name') }}</th>
                <th style="width: 30%" >{{ trans('contract::view.Created at') }}</th>
            </tr>
        </thead>
        <?php $i = 1; ?>
        @foreach ($arrFile as $file)
        <tr>
            <td style="text-align: center" >{{$i}}</td>
            <td><a href="{{route('contract::manage.contract.download',['fileName'=>$file['fileName']])}}">{{$file['fileName']}}</a></td>
            <td>{{$file['created_at']}}</td>
        </tr>
        <?php $i++ ?>
        @endforeach
    </table>
</div>