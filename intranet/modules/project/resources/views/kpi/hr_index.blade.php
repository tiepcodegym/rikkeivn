<div class="table-responsive freeze-wrapper">
    {!!trans('project::view.guide kpi')!!}
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th tbl-kpi"
        d-dom-tbl="hrindex" data-xml-ws-name="HR data">
        <colgroup>
            <col data-width="77.25" />
            <col data-width="62.25" />
        </colgroup>
        <tbody>
            <!-- employee leave ko tinh thu viec -->
            <tr data-height="22.5">
                <td colspan="5" class="tr-guide" d-hr-dom="td-all" data-xml-style-i-d="hr_title"><strong>Chỉ số nhân viên nghỉ việc (không tính thử việc)</strong></td>
            </tr>
            <tr>
                <td class="freeze-col td-shead block-bottom block-right" data-free-width="40" rowspan="2" data-xml-style-i-d="hr_cw">Team</td>
                <td rowspan="2" class="td-shead block-bottom block-right block-left" data-xml-style-i-d="hr_cw">Turnoverate<br/>%</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-out block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_1">Số nhân viên nghỉ trong tháng (không tính thử việc)</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-join block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_2">Số nhân viên gia nhập</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-sum block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_3">Số nhân viên của team</td>
            </tr>
            <tr d-hr-out="title">
                <td d-hr-month="out-tried" class="td-shead hr-index-out{class}" data-xml-style-i-d="hr_block_1" {attrcss}>{month}</td>
                <td d-hr-month="join" class="td-shead hr-index-join{class}" data-xml-style-i-d="hr_block_2">{month}</td>
                <td d-hr-month="sum" class="td-shead hr-index-sum{class}" data-xml-style-i-d="hr_block_3">{month}</td>
            </tr>
            <tr d-hr-out="body">
                <td class="block-right" data-xml-style-i-d="{hrbodystyle}">{team}</td>
                <td class="number block-right block-left" data-xml-style-i-d="{hrbodystyle}">{turnoverate}</td>
                <td class="number{class}" d-hr-count="out-tried" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="join" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="sum" data-xml-style-i-d="{hrbodystyle}">{count}</td>
            </tr>
            <!-- end employee leave ko tinh thu viec -->
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>

            <!-- employee leave chi thu viec -->
            <tr data-height="22.5">
                <td colspan="5" class="tr-guide" d-hr-dom="td-all" data-xml-style-i-d="hr_title"><strong>Chỉ số nhân viên nghỉ việc (chỉ thử việc)</strong></td>
            </tr>
            <tr>
                <td class="freeze-col td-shead block-bottom block-right" data-free-width="40" rowspan="2" data-xml-style-i-d="hr_cw">Team</td>
                <td rowspan="2" class="td-shead block-bottom block-right block-left" data-xml-style-i-d="hr_cw">Turnoverate<br/>%</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-out block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_1">Số nhân viên nghỉ trong tháng (chỉ thử việc)</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-join block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_2">Số nhân viên gia nhập</td>
                <td colspan="" d-hr-colspan="count-month" class="td-shead hr-index-sum block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_3">Số nhân viên của team</td>
            </tr>
            <tr d-hr-out-try="title">
                <td d-hr-month="out-tried" class="td-shead hr-index-out{class}" data-xml-style-i-d="hr_block_1" {attrcss}>{month}</td>
                <td d-hr-month="join" class="td-shead hr-index-join{class}" data-xml-style-i-d="hr_block_2">{month}</td>
                <td d-hr-month="sum" class="td-shead hr-index-sum{class}" data-xml-style-i-d="hr_block_3">{month}</td>
            </tr>
            <tr d-hr-out-try="body">
                <td class="block-right" data-xml-style-i-d="{hrbodystyle}">{team}</td>
                <td class="number block-right block-left" data-xml-style-i-d="{hrbodystyle}">{turnoverate}</td>
                <td class="number{class}" d-hr-count="out-try" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="join" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="sum" data-xml-style-i-d="{hrbodystyle}">{count}</td>
            </tr>
            <!-- end employee leave chi thu viec -->
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>

            <tr data-height="22.5">
                <td colspan="5" class="tr-guide" d-hr-dom="td-all" data-xml-style-i-d="hr_title"><strong>Chỉ số nhân viên nghỉ việc (tính cả thử việc)</strong></td>
            </tr>
            <tr>
                <td class="freeze-col td-shead block-bottom block-right" data-free-width="40" rowspan="2" data-xml-style-i-d="hr_cw">Team</td>
                <td rowspan="2" class="td-shead block-bottom block-right block-left" data-xml-style-i-d="hr_cw">Turnoverate<br/>%</td>
                <td colspan="{count_month}" d-hr-colspan="count-month" class="td-shead hr-index-out block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_1">Số nhân viên nghỉ trong tháng</td>
                <td colspan="{count_month}" d-hr-colspan="count-month" class="td-shead hr-index-join block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_2">Số nhân viên gia nhập</td>
                <td colspan="{count_month}" d-hr-colspan="count-month" class="td-shead hr-index-sum block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_3">Số nhân viên của team</td>
                <td colspan="{count_month}" d-hr-colspan="count-month" class="td-shead hr-index-diff block-right block-left block-bottom-1" data-xml-style-i-d="hr_block_4">Số nhân viên tăng</td>
            </tr>
            <tr d-hr-out-all="title">
                <td d-hr-month="out-tried" class="td-shead hr-index-out{class}" data-xml-style-i-d="hr_block_1" {attrcss}>{month}</td>
                <td d-hr-month="join" class="td-shead hr-index-join{class}" data-xml-style-i-d="hr_block_2">{month}</td>
                <td d-hr-month="sum" class="td-shead hr-index-sum{class}" data-xml-style-i-d="hr_block_3">{month}</td>
                <td d-hr-month="diff" class="td-shead hr-index-diff{class}" data-xml-style-i-d="hr_block_4">{month}</td>
            </tr>
            <tr d-hr-out-all="body">
                <td class="block-right" data-xml-style-i-d="{hrbodystyle}">{team}</td>
                <td class="number block-right block-left" data-xml-style-i-d="{hrbodystyle}">{turnoverate}</td>
                <td class="number{class}" d-hr-count="out-all" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="join" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="sum" data-xml-style-i-d="{hrbodystyle}">{count}</td>
                <td class="number{class}" d-hr-count="diff" data-xml-style-i-d="{hrbodystyle}">{count}</td>
            </tr>
            
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>

            <tr data-height="22.5">
                <td colspan="5" class="tr-guide" d-hr-dom="td-all" data-xml-style-i-d="hr_title"><strong>Chi tiết từng nhân viên nghỉ của team</strong></td>
            </tr>
            <tr d-hr-title="empl-out">
                <td class="freeze-col td-shead2 block-bottom block-right" data-free-width="40" data-xml-style-i-d="hr_detail_title">Team</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Tên</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Email</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Ngày nghỉ</td>
                <td colspan="{colspan}" class="td-shead2 block-bottom block-left" d-hr-empl="title-reason" data-xml-style-i-d="hr_detail_title">Lý do nghỉ</td>
            </tr>
            <tr d-hr-employee="out">
                <td class="block-right block-bottom" rowspan="{rowspan}" d-hr-empl="team" data-xml-style-i-d="hr_detail_team">{team}</td>
                <td colspan="3" class="block-right block-left{class}" {attrcss} data-xml-style-i-d="{hrbodystyle}">{name}</td>
                <td colspan="3" class="block-right block-left{class}" data-xml-style-i-d="{hrbodystyle}">{email}</td>
                <td colspan="3" class="block-right block-left{class}" data-xml-style-i-d="{hrbodystyle}">{date}</td>
                <td colspan="{colspan}" class="block-right block-left{class} wrap-break-text" data-xml-style-i-d="{hrbodystyle}">{reason}</td>
            </tr>
            
            
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>

            <tr data-height="22.5">
                <td colspan="5" class="tr-guide" d-hr-dom="td-all" data-xml-style-i-d="hr_title"><strong>Chi tiết từng nhân viên gia nhập của team</strong></td>
            </tr>
            <tr d-hr-title="empl-join">
                <td class="freeze-col td-shead2 block-bottom block-right" data-free-width="40" data-xml-style-i-d="hr_detail_title">Team</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Tên</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Email</td>
                <td colspan="3" class="td-shead2 block-bottom block-right block-left" data-xml-style-i-d="hr_detail_title">Ngày gia nhập</td>
            </tr>
            <tr d-hr-employee="join">
                <td colspan="3" class="block-right block-left{class}" data-xml-style-i-d="{hrbodystyle}" {attrcss}>{name}</td>
                <td colspan="3" class="block-right block-left{class}" data-xml-style-i-d="{hrbodystyle}">{email}</td>
                <td colspan="3" class="block-right block-left{class}" data-xml-style-i-d="{hrbodystyle}">{date}</td>
            </tr>
        </tbody>
    </table>
</div>
