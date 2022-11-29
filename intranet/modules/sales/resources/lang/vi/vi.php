<?php

return [
    'Import customer' => 'Import customer',
    'Select file to import' => 'Chọn file import',
    'Not found item' => 'Không tìm thấy file upload',
    'Limit max import contract support 1000 row'=>'Hệ thống hỗ trợ tối đa là 1000 bản ghi /1 lần import',
    'Import customer successfully' => 'Import dữ liệu khách hàng thành công',
    'File is not formatted correctly [.xls,.xlsx]!' => 'Tệp không đúng định dạng [.xls, .xlsx]!',
    'Format excel file' => 'Format excel file',
    'help-import-excel' => "<h4>Ghi chú</h4>
                        <ol>
                            <li><b>Hệ thống chỉ hỗ trợ import file excel [.xls|.xlsx]</b></li>
                            <li>
                                <b>Quy định vị trí dữ liệu trong file import:</b><br/>
                                <i>
                                + Dữ liệu import được bắt đầu từ dòng thứ hai của mỗi sheet <br/>
                                + Quy định cột dữ liệu trong file import:<br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;- A: id <br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;- B: name crm<br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;- C: crm_id<br/>
                                &nbsp;&nbsp;&nbsp;&nbsp;- D: crm_account_id
                                </i>
                            </li>
                            <li><b>Hệ thống hỗ trợ tối đa là 1000 bản ghi /1 lần import. Danh sách customer import phải được đặt tại Sheet đầu tiên của file import</b></li>
                        </ol>
                    ",
];