<div id="update_fines_money" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form action="{{ route('fines-money::fines-money.manage.update_import') }}"
                      enctype="multipart/form-data" method="post" id="form_update_fines_money">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="input-box">
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger pull-left"
                                data-dismiss="modal">{{ trans('core::view.Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('fines_money::view.Submit') }}</button>
                    </div>
                </form>
                <hr>
                <div class="form-group">
                    <ul>
                        <li>Dữ liệu sẽ update theo từng nhân viên, từng năm, từng tháng.</li>
                        <li>Trạng thái bao gồm: Đã đóng tiền, Chưa đóng tiền, Cập nhật số tiền</li>
                        <li>Trạng thái cập nhập số tiền khi tiến hành ấn tổng hợp công thì số tiền sẽ không được cập nhật tự động</li>
                        <li>Template import sử dụng template của file sau khi export</li>
                        <li>Lưu ý: Import chỉ thực hiện update dữ liệu. Khi chỉnh sửa dữ liệu update về số tiền thì chỉ chỉnh sửa được số tiền đi muộn, không sửa và cập nhật được số tiền để máy qua đêm</li>
                    </ul>

                    <div>
                        <strong>Mẫu file excel: </strong>
                        <a href="{{ asset('fines_money/files/template.xlsx') }}">Tải
                            xuống <i class="fa fa-download"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>