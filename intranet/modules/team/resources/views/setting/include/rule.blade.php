<?php

use Rikkei\Team\View\Acl;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Permission;

$acl = Acl::getAclList();
$i = 0;
$scopeIcon = Permission::scopeIconArray();
$guides = Acl::getGuideAcl();
?>
<form action="{{ URL::route('team::setting.team.rule.save') }}" method="post">
    {!! csrf_field() !!}
    <input type="hidden" name="team[id]" value="{{ Form::getData('id') }}" />
    
    <div class="rule-noti">
        {!! Permission::getScopeIconGuide() !!}
    </div>
    
    <div class="actions">
        <button type="submit" class="btn-add btn-large">
            <span>{{ trans('team::view.Save') }}</span>
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-team-rule">
            <thead>
                <tr>
                    <th class="col-screen">{{ trans('team::view.Screen') }}</th>
                    <th class="col-function">{{ trans('team::view.Function') }}</th>
                    @foreach ($rolesPosition as $role)
                        <th class="col-team">{{ $role->role }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                  @if (! count($acl))
                    <p class="alert alert-warning">{{ trans('team::view.Not found function') }}</p>
                  @else
                    @foreach ($acl as $aclKey => $aclValue)
                        @if (! isset($aclValue['child']) || ! count($aclValue['child']))
                            <?php continue; ?>
                        @endif
                        <tr class="tr-col-screen">
                            <td class="col-screen">
                                @if (Lang::has('acl.' . $aclValue['description']) && trim(trans('acl.' . $aclValue['description'])))
                                    {{ trans('acl.' . $aclValue['description']) }}
                                @else
                                    {{ $aclValue['description'] }}
                                @endif
                            </td>
                            <td>&nbsp;</td>
                            @foreach ($rolesPosition as $role)
                                <td>&nbsp;</td>
                            @endforeach
                        </tr>
                        
                        @foreach ($aclValue['child'] as $aclItemKey => $aclItem)
                            <tr>
                                <td class="col-screen-empty">
                                    @if (isset($aclItem['name']) && isset($guides[$aclItem['name']]))
                                    <a href="javascript:void(0)" class="acl-guide">
                                        <i class="fa fa-question-circle acl-guide-icon col-hover-tooltip">
                                            <div class="hidden tooltip-content">
                                                <div class="help-acl">
                                                    {!!$guides[$aclItem['name']]!!}
                                                </div>
                                            </div>
                                        </i>
                                    </a>
                                    @endif
                                </td> 
                                <td>
                                    @if (Lang::has('acl.' . $aclItem['description']) && trim(trans('acl.' . $aclItem['description'])))
                                        {{ trans('acl.' . $aclItem['description']) }}
                                    @else
                                        {{ $aclItem['description'] }}
                                    @endif
                                </td>
                                @foreach ($rolesPosition as $role)
                                    <?php $scopeAction = Acl::findScope($teamPermissions, $aclItemKey, $role->id); ?>
                                    <td class="col-team form-drop-wrapper">
                                        <input type="hidden" name="permission[{{ $i }}][role_id]" value="{{ $role->id }}" disabled />
                                        <input type="hidden" name="permission[{{ $i }}][action_id]" value="{{ $aclItemKey }}" disabled />
                                        <div class="btn-group form-input-dropdown">
                                            <input type="hidden" name="permission[{{ $i }}][scope]" disabled
                                                value="{{ $scopeAction }}" class="input" />
                                            <button type="button" class="btn btn-default dropdown-toggle input-show-data" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span>{!! $scopeIcon[$scopeAction] !!}</span>
                                            </button>
                                            <ul class="dropdown-menu input-menu">
                                                @foreach (Permission::toOption() as $option)
                                                    <li>
                                                        <a href="#" data-value="{{ $option['value'] }}">{!! $option['label'] !!}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <?php $i++; ?>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                  @endif
            </tbody>
        </table>
    </div>
    
    <div class="actions">
        <button type="submit" class="btn-add btn-large">
            <span>{{ trans('team::view.Save') }}</span>
        </button>
    </div>
</form>

