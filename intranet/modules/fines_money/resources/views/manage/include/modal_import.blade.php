<div id="import_fines_money" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{ route('fines-money::fines-money.manage.import') }}" enctype="multipart/form-data" method="post" id="form_import_fines_money">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="input-box">
                            <input type="file" name="file" class="form-control">
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-left" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary" >{{ trans('fines_money::view.Submit') }}</button>
                    </div>
                </form>
                <hr>
                <div class="form-group">
                    <ul>
                        <li>Trường bắt buộc: Mã NV</li>
                        <li>Các cột Họ tên, ID, Mã NV vị trí phải cố định, các cột khác có thể thay đổi thứ tự, có thể thêm cột dữ liệu tiền với định dạng header cột là (mm/YYYY)</li>
                        <li>Dữ liệu sẽ update theo từng nhân viên, từng năm, từng tháng. Nếu chưa có dữ liệu được thêm mới</li>
                    </ul>

                    <div>
                        <strong>Mẫu file excel: </strong>
                        <a href="{{ asset('fines_money/files/DS_còn_nợ_tiền_đi_muộn_từ_T3.2017_đến_T3.2018.xlsx') }}">Tải xuống <i class="fa fa-download"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>