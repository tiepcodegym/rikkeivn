<?php
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Task;
use Rikkei\Team\View\Permission;

$isAccessNumber = Permission::getInstance()->isAllow('project::reward.base.actual.edit');
?>
 <div class="box-body">
    <p>{{ trans('project::view.Currency unit') }}: VND</p>
    @if ($isAccessNumber)
        <div>
            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#modal-reward-number">
                <i class="fa fa-edit"></i> {!!trans('project::view.edit reward budget and bug')!!}
            </button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
            <thead>
                <tr>
                    <th class="text-center">{{ trans('project::view.Level') }}</th>
                    <th class="text-center">{{ trans('project::view.Billable') }} (MM)</th>
                    <th class="text-center">{{ trans('project::view.Budget reward') }}</th>
                    <th class="text-center">{{ trans('project::view.Number of IT/ST defects') }}</th>
                    <th class="text-center">{{ trans('project::view.Defect of final inspection') }}</th>
                    <th class="text-center">{{ trans('project::view.Number of leakage') }}</th>
                    <th class="text-center">{{ trans('project::view.Actual reward') }}</th>
                    <th class="text-center">{{ trans('project::view.Reward for QAs') }}</th>
                    <th class="text-center">{{ trans('project::view.Reward for PQA') }}</th>
                    <th class="text-center">{{ trans('project::view.Reward for Dev team') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">
                        @if (isset($evaluationLabel[$rewardMeta->evaluation]))
                            {{ $evaluationLabel[$rewardMeta->evaluation] }}
                        @endif
                    </td>
                    <td class="text-right">
                        {{ number_format($rewardMeta->billable, 2) }}
                    </td>
                    <td class="text-right" data-ra-number="reward_budget">
                        {{ number_format($rewardMeta->reward_budget) }}
                    </td>
                    <td class="text-right" data-ra-number="count_defect">
                        {{ number_format($rewardMeta->count_defect) }}
                    </td>
                    <td class="text-right" data-ra-number="count_defect_pqa">
                        {{ number_format($rewardMeta->count_defect_pqa) }}
                    </td>
                    <td class="text-right" data-ra-number="count_leakage">
                        {{ number_format($rewardMeta->count_leakage) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($rewardMetaInfor['reward_actual']) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($rewardMetaInfor['reward_qa']) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($rewardMetaInfor['reward_pqa']) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($rewardMetaInfor['reward_pm_dev']) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#modal-reward-formula">
            {!!trans('project::view.reward actual formula')!!}
        </button>
    </div>
</div>
@if ($isAccessNumber)
<div class="modal fade" tabindex="-1" role="dialog" id="modal-reward-number">
    <div class="modal-dialog" role="document">
        <form method="post" action="{!!route('project::reward.actual.edit.number')!!}" class="form-horizontal has-valid" autocomplete="off"
            data-form-submit="ajax" data-flag-valid="1" id="form-rae">
            <div class="modal-content">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id" value="{!!$taskItem->id!!}" />
                    <div class="row form-group">
                        <label for="#input-reward_budget" class="col-md-5 control-label">{!!trans('project::view.Budget reward')!!}</label>
                        <div class="col-md-7">
                            <input type="text" name="i[reward_budget]" data-input-number="format" class="form-control" id="input-reward_budget" placeholder="{!!trans('project::view.Budget reward')!!}">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="#input-count_defect" class="col-md-5 control-label">{!!trans('project::view.Number of IT/ST defects')!!}</label>
                        <div class="col-md-7">
                            <input type="text" name="i[count_defect]" data-input-number="format" class="form-control" id="input-count_defect" placeholder="{!!trans('project::view.Number of IT/ST defects')!!}">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="#input-count_defect_pqa" class="col-md-5 control-label">{!!trans('project::view.Defect of final inspection')!!}</label>
                        <div class="col-md-7">
                            <input type="text" name="i[count_defect_pqa]" data-input-number="format" class="form-control" id="input-count_defect_pqa" placeholder="{!!trans('project::view.Defect of final inspection')!!}">
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="#input-count_leakage" class="col-md-5 control-label">{!!trans('project::view.Number of leakage')!!}</label>
                        <div class="col-md-7">
                            <input type="text" name="i[count_leakage]" data-input-number="format" class="form-control" id="input-count_leakage" placeholder="{!!trans('project::view.Number of leakage')!!}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save
                        <i class="loading-submit fa fa-spin fa-refresh hidden"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
<div class="modal fade" tabindex="-1" role="dialog" id="modal-reward-formula">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{!!trans('project::view.reward actual formula')!!}</h4>
            </div>
            <div class="modal-body">
                <ul>
                    <li>Budget reward: S??? ti???n th?????ng t???i ??a c???a d??? ??n, l???y t??? tab rewards trong project point report detail, c?? th??? edit b???i COO</li>
                    <li>Number of IT/ST defects: S??? bug l???y t??? tab Quality trong project point report detail, c?? th??? edit b???i COO</li>
                    <li>Defect of final inspection: S??? bug ???????c t??m t??? PQA, hi???n ch??a c?? c??ng th???c l???y, default l?? 0, c?? th??? edit b???i COO</li>
                    <li>Number of leakage: S??? bug leakage, l???y t??? tab Quality trong project point report detail, c?? th??? edit b???i COO</li>
                    <li>
                        <p>Actual reward: T???ng s??? ti???n th???c nh???n c???a c??? d??? ??n, sau khi tr??? ??i h??? s??? v???i s?? bug t??m ???????c</p>
                        <p><code>Actual reward = Budget reward - Number of leakage * {!!number_format($rewardMeta->unit_reward_leakage_actual)!!}</code></p>
                    </li>
                    <li>
                        <p>Reward for QAs: S??? ti???n m?? ?????i QA nh???n ???????c</p>
                        <p><code>Reward for QAs = Number of IT/ST defects * {!!number_format($rewardMeta->unit_reward_defect)!!}
                        - Number of leakage * {!!number_format($rewardMeta->unit_reward_leakage_qa)!!}</code></p>
                    </li>
                    <li>
                        <p>Reward for PQA: S??? ti???n m?? ?????i PQA nh???n ???????c</p>
                        <p><code>Reward for PQA = Defect of final inspection * {!!number_format($rewardMeta->unit_reward_defect_pqa)!!}</code></p>
                    </li>
                    <li>
                        <p>Reward for Dev team: S??? ti???n m?? ?????i dev nh???n ???????c (dev, PM, BrSE, ...)</p>
                        <p><code>Reward for Dev team = Actual reward -  Reward for QAs - Reward for PQA</code></p>
                    </li>
                    <li>
                        <p>Point cho t???ng th??nh vi??n, point s??? t??? l??? thu???n v???i s??? ti???n th?????ng c???a t???ng th??nh vi??n</p>
                        <p><code>Point = sum(ME * Effort)</code></p>
                        <?php /*<p>H??? s??? Point: PM l?? {!!number_format($rewardMeta->factor_reward_pm)!!}, c??c v??? tr?? c??n l???i l?? 1</p>*/ ?>
                    </li>
                    <li>
                        <p>Norm: S??? ti???n th?????ng c???a t???ng th??nh vi??n do h??? th???ng t??nh to??n, nh??ng PM, Group Leader v?? COO c?? th??? thay ?????i</p>
                        <p><code>Reward member dev = (Reward for Dev team / t???ng point dev) * point member dev</code></p>
                        <p>C??ch t??nh reward c???a QA v?? PQA t????ng t???</p>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>