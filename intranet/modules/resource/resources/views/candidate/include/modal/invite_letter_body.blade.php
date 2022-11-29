<?php 
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View;
use Rikkei\Core\View\View as CoreView;
?>
<div class="modal fade" id="modal-invite-letter" tabindex="-1" role="dialog"  data-keyboard="false" >
    <div class="modal-dialog" style="width: 800px">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Mail content') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="invite-letter-body box box-body">
                        <textarea id="invite_letter_editor">
                            
                        </textarea>
                     </div>
                    <input type="hidden" id="candidate_email" value="{{$candidate->email}}" />
                    <input type="hidden" id="candidate_id" value="{{$candidate->id}}" />
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-left" onclick="closeInviteBody();">{{ trans('resource::view.Close') }}</button>
                <button type="button" class="btn btn-primary btn-create-pdf" onclick="createPdf({{Candidate::MAIL_OFFER}});">
                    <span>
                        {{ Lang::get('resource::view.Create new invite letter') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </span>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<div class="invite-letter-content-vn hidden">
    <div class="body">
        <style>
            .infor-company {
                display: flex;
                align-items: center;
                border: 1px solid black;
            }
            .infor-company .left {
                padding: 10px;
            }
            .infor-company .right {
                border-left: 1px solid black;
                font-weight: bold;
                padding-left: 10px;
            }
            .infor-company .right h4 {
                font-weight: bold;
            }
            .tb_candidate {
                margin-top: 40px;
                font-weight: bold;
            }
            .tb_candidate tr td {
                padding: 5px;
            }
            .tb_candidate tr td:first-child {
                min-width: 10em;
            }
            .tb_candidate tr td:last-child {
                width: 100%
            }
            .invite-letter table,
            .invite-letter th,
            .invite-letter td {
                border: none;
                border-collapse: collapse;
            }
            .margin-top-10 {
                margin-top: 10px;
            }
            .invite-letter p {
                line-height: 1.5em;
                margin-top: 10px;
                text-align: justify;
            }
            .text-center {
                text-align: center;
            }
            .color-red {
                color: red
            }
            .invite-letter .table-collapse-boder table,
            .invite-letter .table-collapse-boder th,
            .invite-letter .table-collapse-boder td {
                border: 1px solid black;
                border-collapse: collapse;
                padding: 5px;
            }
        </style>
        <?php
            if ($candidate->start_working_date) {
                $day = substr($candidate->start_working_date, 8, 2);
                $month = substr($candidate->start_working_date, 5, 2);
                $year = substr($candidate->start_working_date, 0, 4);
            } else {
                $day = '...';
                $month = '...';
                $year = '...';
            }
        ?>
        {{-- ====================== --}}
        <div class="invite-letter">
            <div class="row margin-top-20">
                <div class="col-md-12 align-center text-uppercase" style="text-align: center; font-size: 23px"><b>{{ trans('resource::view.INVITE LETTER') }}</b></div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12"><b>{{ trans('resource::view.fullname :name', [ 'name' => $candidate->fullname ]) }}</b></div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12"><b>{{ trans('resource::view.birthday :date', [ 'date' => $candidate->birthday ]) }}</b></div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12">
                    <b>{{ trans('resource::view.I. Job information') }}</b>
                    <br/>
                    {!! trans('resource::view.infor working send candidate', [
                        'name' => $candidate->fullname,
                        'position' => getOptions::getInstance()->getRole($candidate->position_apply) ? getOptions::getInstance()->getRole($candidate->position_apply) : '...',
                        'team' => $teamName ? $teamName : '...',
                        'day' => $day,
                        'month' => $month,
                        'year' => $year,
                    ]) !!}
                    <p>- {{ trans('resource::view.Manager: :leader', [ 'leader' =>  $leader ? $leader->name : ''])}}</p>
                    <p>- {{ trans('resource::view.Contract Type') }}</p>
                    <p>- {{ trans('resource::view.Contract Length') }}</p>
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12">
                    <b>II. {{ trans('resource::view.General information') }}</b>
                </div>
                <div class="col-md-12">
                    <br/>
                    <table class="table-collapse-boder">
                        <thead>
                            <tr>
                                <th class="text-center">Nội dung</th>
                                <th class="text-center">Thử việc (...%lương)(VNĐ)</th>
                                <th class="text-center">Chính thức (VNĐ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b>1. Mức lương tháng (Gross)</b></td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>(a) Mức lương cơ bản đóng BHXH</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>2. Thưởng hiệu suất</b></td>
                                <td class="text-center"><i>Theo tháng</i></td>
                                <td class="text-center"><i>Theo tháng</i></td>
                            </tr>
                            <tr>
                                <td><b>3. Phụ cấp</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Phụ cấp thâm niên (<i>từ 2 năm trở nên</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Phụ cấp chứng chỉ (<i>nếu có</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Phụ cấp tiếng Anh, Nhật (<i>nếu có</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Phụ cấp trách nhiệm (<i>nếu có</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Phụ cấp khác (<i>nếu có</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>4. Tổng lương, thưởng, phụ cấp (1+2+3)</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>5. Các khoản giảm trừ</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>BHXH,BHYT, BHTN, CĐP (<i>10,5% BHXH + 1% CĐP</i>) trên lương cơ bản</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Thuế TNCN thời gian thử việc (<i>10%</i>) </td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Thuế TNCN làm việc chính thức (<i>tạm tính</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>6. Tổng lương thực nhận (3-4) (<i>tạm tính</i>)</b></td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="color-red">(<i>Tất cả những thông tin bên dưới là tạm tính khi ký hợp đồng ban đầu, có thể review lại sau thời gian thử việc</i>)</p>
                </div>
            </div>
            <div class="row margin-top-10">
                <div class="col-md-12">
                    <b>III. {{ trans('resource::view.Regime') }}</b>
                </div>
                <div class="col-md-12">
                    {!! trans('resource::view.invite letter body regime') !!}
                </div>
            </div>
            <div class="row margin-top-10" style="position: relative;">
                <div class="col-md-12">
                    <b>IV. {{ trans('resource::view.Information security') }}</b>
                </div>
                <div class="col-md-12">
                    <p>{{ trans('resource::view.Security content') }}</p>
                </div>
                <div class="col-md-12">
                    <p>{!! trans('resource::view.Please confirm invitation to work before date') !!}</p>
                </div>
                <div class="col-md-12 margin-top-10">
                    {{ trans('resource::view.Welcome :name to Rikkeisoft', ['name' => CoreView::getLastWord($candidate->fullname)]) }}
                </div>
                <div class="col-md-12 margin-top-10">
                    {{ trans('resource::view.Thanks & best regards') }}
                </div>
            </div>
        </div>
        {{-- ================================================= --}}
    </div>
</div>

<div class="invite-letter-content-jp hidden">
    <div class="body">
        <div class="row">
            <div class="col-md-12 pull-right italic"><em>{{ trans('resource::view.Hanoi, day :day month :month year :year', [ 'day' => date('d'), 'month' => date('m'), 'year' => date('Y')]) }}</em></div>
        </div>
        <div class="row margin-top-20">
            <div class="col-md-12 align-center text-uppercase font-size-23px"><b>{{ trans('resource::view.Invite letter') }}</b></div>
        </div>
        <div class="row margin-top-10">
            <div class="col-md-12 align-center"><b>{{ trans('resource::view.Dear: :name', [ 'name' => $candidate->fullname ]) }}</b></div>
        </div>
        <div class="row margin-top-10">
            <div class="col-md-12">
                <p>Đầu tiên, chúng tôi hết sức cám ơn anh/ chị đã dành thời gian quan tâm và ứng tuyển cho vị trí <strong>{{ getOptions::getInstance()->getRole($candidate->position_apply) }}</strong> tại công ty của chúng tôi.</p>
                <p>Qua thời gian gặp gỡ trao đổi, chúng tôi hết sức ấn tượng với kinh nghiệm cũng như những gì bạn đã thể hiện.</p>
                <p>Chính vì vậy, chúng tôi trân trọng được mời bạn vào làm việc tại công ty của chúng tôi với vị trí <strong>{{ getOptions::getInstance()->getRole($candidate->position_apply) }}</strong>.</p>
                <p>Ở vị trí này, anh/ chị sẽ làm việc <strong>[toàn thời gian/bán thời gian]</strong> từ <strong>[thời gian và số ngày làm việc trong tuần]</strong>, trực tiếp nằm dưới sự quản lý của anh/chị <strong>{{ $leader ? $leader->name : '' }}</strong> thuộc phòng <strong>{{ $teamName ? $teamName : '' }}</strong>.</p>
            </div>
        </div>
        <div class="row margin-top-10">
            <div class="col-md-12">
                <p>Như đã thỏa thuận trong buổi phỏng vấn, bạn sẽ được nhận mức lương và chính sách như sau:</p>
                <p>Mức lương sau thuế: </p>
                <p>Chính sách hỗ trợ khác như: </p>
                <ul>
                    <li>- Tiền nhà:</li>
                    <li>- Bảo hiểm y tế, nenkin:</li>
                    <li>- Tiền đi lại:</li>
                    <li>- Các ngày nghỉ: </li>
                </ul>
            </div>
        </div>
        <div class="row" style="position: relative; top: 90px">
            <div class="col-md-12">
                <b>Bảo mật thông tin</b>
            </div>
            <div class="col-md-12">
                {{ trans('resource::view.Security content') }}
            </div>
            <div class="col-md-12 margin-top-10">
                <?php
                $name = $recruiter ? $recruiter->name : '';
                $email = $recruiter ? $recruiter->email : '';
                $phone = $recruiter ? $recruiter->mobile_phone : '';
                $skype = $recruiter ? $recruiter->skype : '';
                ?>
                <p>[Bạn có thể tham khảo trước hợp đồng/chính sách hỗ trợ chi tiết được đính kèm theo email này]</p>
                <p>Thời gian làm việc của bạn sẽ bắt đầu từ <b>[ngày/giờ]</b></p>
                <p>Chúng ta sẽ cùng thống nhất thêm một số vấn đề như <b>[thời hạn hợp đồng, các chính sách công ty...]</b> trong ngày làm việc đầu tiên này.</p>
                <p>Để xác nhận đề nghị này, anh/ chị vui lòng trả lời lại email cho chúng tôi trước <b>[ngày/giờ]</b>. Trong thời gian đó, hãy liên hệ với chúng tôi qua <b>{{ $phone }}</b> hoặc <b>{{ $email }}</b> nếu như anh/ chị có bất kì thắc mắc nào cần giải đáp.</p>
                <p>Chúng tôi rất háo hức được có anh/ chị trong đội ngũ của chúng tôi, và chúc anh/ chị sẽ có những trải nghiệm tuyệt vời tại đây.</p>
            </div>
            <div class="col-md-12 margin-top-10">
                {{ trans('resource::view.Welcome :name to Rikkeisoft', ['name' => CoreView::getLastWord($candidate->fullname)]) }}
            </div>
            <div class="col-md-12 margin-top-10">
                <p>{{ trans('resource::view.Thanks & best regards') }}</p>
                <p>何卒宜しくお願い申し上げます。</p>
            </div>
        </div>
    </div>
</div>

