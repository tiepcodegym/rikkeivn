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
                                <th class="text-center">N???i dung</th>
                                <th class="text-center">Th??? vi???c (...%l????ng)(VN??)</th>
                                <th class="text-center">Ch??nh th???c (VN??)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><b>1. M???c l????ng th??ng (Gross)</b></td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>(a) M???c l????ng c?? b???n ????ng BHXH</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>2. Th?????ng hi???u su???t</b></td>
                                <td class="text-center"><i>Theo th??ng</i></td>
                                <td class="text-center"><i>Theo th??ng</i></td>
                            </tr>
                            <tr>
                                <td><b>3. Ph??? c???p</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ph??? c???p th??m ni??n (<i>t??? 2 n??m tr??? n??n</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Ph??? c???p ch???ng ch??? (<i>n???u c??</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Ph??? c???p ti???ng Anh, Nh???t (<i>n???u c??</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Ph??? c???p tr??ch nhi???m (<i>n???u c??</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Ph??? c???p kh??c (<i>n???u c??</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>4. T???ng l????ng, th?????ng, ph??? c???p (1+2+3)</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><b>5. C??c kho???n gi???m tr???</b></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>BHXH,BHYT, BHTN, C??P (<i>10,5% BHXH + 1% C??P</i>) tr??n l????ng c?? b???n</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Thu??? TNCN th???i gian th??? vi???c (<i>10%</i>) </td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td>Thu??? TNCN l??m vi???c ch??nh th???c (<i>t???m t??nh</i>)</td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                            <tr>
                                <td><b>6. T???ng l????ng th???c nh???n (3-4) (<i>t???m t??nh</i>)</b></td>
                                <td class="text-center">.........</td>
                                <td class="text-center">.........</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="color-red">(<i>T???t c??? nh???ng th??ng tin b??n d?????i l?? t???m t??nh khi k?? h???p ?????ng ban ?????u, c?? th??? review l???i sau th???i gian th??? vi???c</i>)</p>
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
                <p>?????u ti??n, ch??ng t??i h???t s???c c??m ??n anh/ ch??? ???? d??nh th???i gian quan t??m v?? ???ng tuy???n cho v??? tr?? <strong>{{ getOptions::getInstance()->getRole($candidate->position_apply) }}</strong> t???i c??ng ty c???a ch??ng t??i.</p>
                <p>Qua th???i gian g???p g??? trao ?????i, ch??ng t??i h???t s???c ???n t?????ng v???i kinh nghi???m c??ng nh?? nh???ng g?? b???n ???? th??? hi???n.</p>
                <p>Ch??nh v?? v???y, ch??ng t??i tr??n tr???ng ???????c m???i b???n v??o l??m vi???c t???i c??ng ty c???a ch??ng t??i v???i v??? tr?? <strong>{{ getOptions::getInstance()->getRole($candidate->position_apply) }}</strong>.</p>
                <p>??? v??? tr?? n??y, anh/ ch??? s??? l??m vi???c <strong>[to??n th???i gian/b??n th???i gian]</strong> t??? <strong>[th???i gian v?? s??? ng??y l??m vi???c trong tu???n]</strong>, tr???c ti???p n???m d?????i s??? qu???n l?? c???a anh/ch??? <strong>{{ $leader ? $leader->name : '' }}</strong> thu???c ph??ng <strong>{{ $teamName ? $teamName : '' }}</strong>.</p>
            </div>
        </div>
        <div class="row margin-top-10">
            <div class="col-md-12">
                <p>Nh?? ???? th???a thu???n trong bu???i ph???ng v???n, b???n s??? ???????c nh???n m???c l????ng v?? ch??nh s??ch nh?? sau:</p>
                <p>M???c l????ng sau thu???: </p>
                <p>Ch??nh s??ch h??? tr??? kh??c nh??: </p>
                <ul>
                    <li>- Ti???n nh??:</li>
                    <li>- B???o hi???m y t???, nenkin:</li>
                    <li>- Ti???n ??i l???i:</li>
                    <li>- C??c ng??y ngh???: </li>
                </ul>
            </div>
        </div>
        <div class="row" style="position: relative; top: 90px">
            <div class="col-md-12">
                <b>B???o m???t th??ng tin</b>
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
                <p>[B???n c?? th??? tham kh???o tr?????c h???p ?????ng/ch??nh s??ch h??? tr??? chi ti???t ???????c ????nh k??m theo email n??y]</p>
                <p>Th???i gian l??m vi???c c???a b???n s??? b???t ?????u t??? <b>[ng??y/gi???]</b></p>
                <p>Ch??ng ta s??? c??ng th???ng nh???t th??m m???t s??? v???n ????? nh?? <b>[th???i h???n h???p ?????ng, c??c ch??nh s??ch c??ng ty...]</b> trong ng??y l??m vi???c ?????u ti??n n??y.</p>
                <p>????? x??c nh???n ????? ngh??? n??y, anh/ ch??? vui l??ng tr??? l???i l???i email cho ch??ng t??i tr?????c <b>[ng??y/gi???]</b>. Trong th???i gian ????, h??y li??n h??? v???i ch??ng t??i qua <b>{{ $phone }}</b> ho???c <b>{{ $email }}</b> n???u nh?? anh/ ch??? c?? b???t k?? th???c m???c n??o c???n gi???i ????p.</p>
                <p>Ch??ng t??i r???t h??o h???c ???????c c?? anh/ ch??? trong ?????i ng?? c???a ch??ng t??i, v?? ch??c anh/ ch??? s??? c?? nh???ng tr???i nghi???m tuy???t v???i t???i ????y.</p>
            </div>
            <div class="col-md-12 margin-top-10">
                {{ trans('resource::view.Welcome :name to Rikkeisoft', ['name' => CoreView::getLastWord($candidate->fullname)]) }}
            </div>
            <div class="col-md-12 margin-top-10">
                <p>{{ trans('resource::view.Thanks & best regards') }}</p>
                <p>?????????????????????????????????????????????</p>
            </div>
        </div>
    </div>
</div>

