<?php

use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;

$emailTemplate = EmailQueue::layouts();
?>
<div class="col-md-12">
    <div class="box box-info">
        <div class="box-body">
            <?php
            $accountToEmail = CoreConfigData::getAccountToEmail();
            ?>

            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_to_email" class="col-sm-2 control-label">{{trans('core::view.Username to Email address')}}</label>
                            <div class="col-md-9">
                                <textarea name="item[project.account_to_email]" class="form-control input-field" type="text" 
                                          id="project.account_to_email" rows="5">{{ $accountToEmail }}</textarea>
                                <p class="hint">{{ trans('core::view.split by break line') }}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php /* tam thoi ko dung cai nay nua
            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="project.account_sqa" class="col-sm-2 control-label">Email HCTH</label>
                            <div class="col-md-9">
                                <input name="item[email_hcth]" class="form-control input-field" type="text" 
                                       id="email_hcth" value="{{ CoreConfigData::getValueDb('email_hcth') }}" />
                                <p class="hint">Gửi file số phút đi muộn khi upload bảng chấm công, Format: dung.phan@rikeisoft.com,huybq@rikkeisoft.com</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form> */?>
            
            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label class="col-sm-2 control-label">{{trans('core::view.Email accountant')}}</label>
                            <div class="col-md-9">
                                <?php 
                                $emailAccountant = CoreConfigData::getValueDb('email_accountant');
                                if (!$emailAccountant && config('app.env') == 'production') {
                                    $emailAccountantItem = CoreConfigData::getItem('email_accountant');
                                    $emailAccountantItem->value = 'manhlk@rikkeisoft.com';
                                    $emailAccountantItem->save();
                                    $emailAccountant = $emailAccountantItem->value;
                                }
                                ?>
                                <input name="item[email_accountant]" class="form-control input-field" type="text" 
                                       id="email_hcth" value="{{ $emailAccountant }}" />
                                <p class="hint">{{trans('core::view.format email accountant')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <form id="form-system-email-template" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <?php $emailLayoutValue = CoreConfigData::getEmailLayout(); ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row form-group-select2">
                            <label for="core-email-layout" class="col-sm-2 control-label">{{trans('core::view.Email layout')}}</label>
                            <div class="col-md-9">
                                <select name="item[core.email.layout]" id="core-email-layout" class="select-search">
                                    @foreach ($emailTemplate as $key => $item)
                                    <option value="{{ $key }}"{{ $key == $emailLayoutValue ? ' selected' : '' }}>{{ $item['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="project.account_to_email" class="col-sm-2 control-label">{{trans('core::view.Website URL')}}</label>
                                <div class="col-md-9">
                                    <input name="item[web_url]" class="form-control input-field" type="text"
                                           id="web_url" value="{{ CoreConfigData::getValueDb('web_url') }}" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="face_url" class="col-sm-2 control-label">{{trans('core::view.Facebook URL')}}</label>
                                <div class="col-md-9">
                                    <input name="item[face_url]" class="form-control input-field" type="text"
                                           id="face_url" value="{{ CoreConfigData::getValueDb('face_url') }}" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="youtube_url" class="col-sm-2 control-label">{{trans('core::view.Youtube URL')}}</label>
                                <div class="col-md-9">
                                    <input name="item[youtube_url]" class="form-control input-field" type="text"
                                           id="youtube_url" value="{{ CoreConfigData::getValueDb('youtube_url') }}" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="email_contact" class="col-sm-2 control-label">{{trans('core::view.Email contact')}}</label>
                                <div class="col-md-9">
                                    <input name="item[email_contact]" class="form-control input-field" type="text"
                                           id="email_contact" value="{{ CoreConfigData::getValueDb('email_contact') }}" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="address_company" class="col-sm-2 control-label">{{trans('core::view.Company Address')}}</label>
                                <div class="col-md-9">
                                     <textarea name="item[address_company]" class="form-control input-field" type="text"
                                               id="" rows="5">{{ CoreConfigData::getValueDb('address_company') }}</textarea>
                                    <p class="hint">{{ trans('core::view.Multiple addresses, separate each one with a new line') }}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php 
                $autoOptions = CoreConfigData::autoOptions();
                $value = CoreConfigData::getAccountToEmail(1, 'auto_approve_comment');
                ?>
                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="" class="col-sm-2 control-label">{{trans('core::view.Automatic approve comment')}}</label>
                                <div class="col-md-9">
                                    @foreach ($autoOptions as $k => $v)
                                    <label for="{{CoreConfigData::AUTO_APPROVE_COMMNENT_KEY}}">
                                        <input type="radio" name="item[{{CoreConfigData::AUTO_APPROVE_COMMNENT_KEY}}]" value="{{$k}}"
                                               @if ($k == $value) checked @endif   />
                                               {{$v}}
                                    </label>
                                    &nbsp;&nbsp;&nbsp;
                                    @endforeach
                                    <p class="hint">{{trans('core::view.Comment approval settings')}}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            
                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="address_company" class="col-sm-2 control-label">{{trans('core::view.Group email register leave')}}</label>
                                <div class="col-md-9">
                                     <textarea name="item[group_email_register_leave]" class="form-control input-field" type="text"
                                               id="" rows="5">{{ CoreConfigData::getValueDb('group_email_register_leave') }}</textarea>
                                    <p class="hint">{{trans('core::view.Multiple addresses, separate each one with a new line')}} {{ trans('core::view.split by break line') }}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add margin-top-50" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate project-ot" autocomplete="off">
                    {!! csrf_field() !!}
                    <?php $itemKey = 'branch_time_1/4'; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left row">
                                <label for="branch_time_14" class="col-md-2 control-label">{{trans('core::view.Branch register time 1/4')}}</label>
                                <div class="col-md-9">
                                    <select name="item[{{$itemKey}}][]" id="branch_time" style="width: 100%; height: 34px;" data-remote-url="{{ URL::route('team::team.list.search.ajax.origin') }}" multiple="multiple">
                                    @if ($branchTime)
                                        @foreach($branchTime as $value)
                                            <option value="{{ $value->id }}" selected>{{ $value->name }}</option>
                                        @endforeach
                                    @endif
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label class="col-sm-2 control-label">{{trans('core::view.Clear cache')}}</label>
                            <div class="col-md-1">
                                <button class="btn-delete post-ajax" data-url-ajax="{{ route('core::setting.system.data.clear.cache') }}" 
                                        type="button">{{trans('core::view.Clear')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
