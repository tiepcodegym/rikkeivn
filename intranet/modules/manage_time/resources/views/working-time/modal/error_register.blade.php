<div id="modalError" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header btn-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@if ($dataErrors = session()->get('dataErrors'))
<div id="error-working-time-register" class="modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header btn-danger">
                <label>Nhân viên sau có thời gian đăng ký bị trùng</label>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table-register-exist">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Loại đơn đăng ký</th>
                            <th>Từ ngày</th>
                            <th>Đến ngày</th>
                            <th>Chú thích</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($dataErrors as $key => $registers)
                        @foreach ($registers as $register)
                        <tr>
                            <td>{{ $register['employee_name'] }}</td>
                            <td>{!! $register['type'] !!}</td>
                            <td>{!! Carbon\Carbon::parse($register['date_start'])->format('d-m-Y H:i') !!}</td>
                            <td>{!! Carbon\Carbon::parse($register['date_end'])->format('d-m-Y H:i') !!}</td>
                            <td>
                                @if (isset($register['note']))
                                    <div>{!! $register['note']['old_text'] !!}</div>
                                    <div>{!! $register['note']['new_text'] !!}</div>
                                @endif
                            </td>
                            <td><a target="_blank" href="{{ str_replace(':id', $register['id'], $register['url']) }}">chi tiết</a></td>
                        </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
