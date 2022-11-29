@extends('layouts.default')
<?php
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\CoreUrl;
?>

@section('title')
Team Setting
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
@endsection

@section('content')
<div class="row team-setting">
    <!-- team manage -->
    <div class="col-md-4 team-wrapper hight-same">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('team::view.List team') }}</h3>
            </div>
            <div class="row team-list-action box-body" id="team_setting_container"></div>
        </div>
    </div> <!-- end team manage -->
    
    <!-- team position manage -->
    <div class="col-md-4 team-position-wrapper hight-same">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('team::view.Position of team') }}</h3>
            </div>
            <div class="row team-list-action box-body" id="team_position_container"></div>
        </div>
    </div> <!-- end team position manage -->
    
    <!-- roles manage -->
    <div class="col-md-4 team-position-wrapper hight-same">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('team::view.Role Special') }}</h3>
            </div>
            <div class="row team-list-action box-body" id="role_setting_container"></div>
        </div>
    </div> <!-- end role manage -->
    
    <!-- rule permission -->
    <div class="col-sm-12 team-rule-wrapper">
        <div class="box box-warning" id="rule_permission_container"></div>
    </div> <!-- end rule permission -->
</div>

@endsection


@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.match.height.addtional.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="{{ URL::asset('team/js/script.js') }}"></script>
<script>
    jQuery(document).ready(function ($) {
        selectSearchReload();
        var messages = {
            'item[name]': {
                required: '<?php echo trans('core::view.Please enter') . ' ' . trans('team::view.team name') ; ?>',
                rangelength: '<?php echo trans('team::view.Team name') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 255]) ; ?>',
              },
            'item[mail_group]': {
                email: '<?php echo trans('core::view.Please enter') . ' ' . trans('team::view.Mail group') ?>',
                rangelength: '<?php echo trans('team::view.Mail group') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'position[role]': {
                required: '<?php echo trans('core::view.Please enter') . ' ' . trans('team::view.position name') ; ?>',
                rangelength: '<?php echo trans('team::view.Position name') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'role[role]': {
                required: '<?php echo trans('core::view.Please enter') . ' ' . trans('team::view.role name') ; ?>',
                rangelength: '<?php echo trans('team::view.Role name') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 255]) ; ?>',
            },
            'item[code]': {
                required: '<?php echo trans('core::view.Please enter') . ' ' . trans('team::view.team code') ; ?>',
                rangelength: '<?php echo trans('team::view.team code') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 20]) ; ?>',
            },
            'item[branch_code]': {
                rangelength: '<?php echo trans('team::view.Branch code') . ' ' . trans('core::view.not be greater than :number characters', ['number' => 20]) ; ?>',
            }
        }
        var rules = {
            'item[name]': {
                required: true,
                rangelength: [1, 255]
            },
            'item[mail_group]': {
                email: true,
                rangelength: [0, 255],
            },
            'position[role]': {
                required: true,
                rangelength: [1, 255]
            },
            'role[role]': {
                required: true,
                rangelength: [1, 255]
            },
            'item[code]': {
                required: true,
                rangelength: [1, 20]
            },
            'item[branch_code]': {
                required: true,
                rangelength: [1, 20]
            },
        };
        var formSettingTeam = [];
        formSettingTeam[0] = $('#form-team-add').validate({
            rules: rules,
            messages: messages
        });
        formSettingTeam[1] = $('#form-team-edit').validate({
            rules: rules,
            messages: messages
        });
        
        formSettingTeam[2] = $('#form-position-add').validate({
            rules: rules,
            messages: messages
        });
        formSettingTeam[3] = $('#form-position-edit').validate({
            rules: rules,
            messages: messages
        });
        formSettingTeam[4] = $('#form-role-add').validate({
            rules: rules,
            messages: messages
        });
        formSettingTeam[5] = $('#form-role-edit').validate({
            rules: rules,
            messages: messages
        });

        $('.modal').on('hidden.bs.modal', function (e) {
            $().formResetVaidation(formSettingTeam);
        });
        function colTooltip() {
            if (!$('.col-hover-tooltip').length) {
                return false;
            }
            var optionTooltipTask = {
                position: {
                    my: 'top center',
                    at: 'center',
                    viewport: $(window),
                    adjust: {
                        y: 10,
                    },
                },
                hide: {
                    fixed: true,
                    delay: 100,
                },
                style: {
                    classes: 'custom-tooltip',
                },
                content: {
                    text: null,
                },
            };
            $('.col-hover-tooltip').each(function () {
                var _thisTooltip = $(this), optionTooltipItem;
                if (!_thisTooltip.find('.tooltip-content').length) {
                    return true;
                }
                var text = _thisTooltip.find('.tooltip-content').html().trim();
                if (!text) {
                    return true;
                }
                optionTooltipItem = $.extend(optionTooltipTask, {
                    content: {
                        text: text,
                    },
                });
                _thisTooltip.qtip(optionTooltipItem);
            });
        }
        colTooltip();

        setTimeout(function () {
            $('#btn_load_teams').click();
        }, 500);
    });

    var textTrans = {
        'Add': '{!! trans("team::view.Add") !!}',
        'Edit': '{!! trans("team::view.Edit") !!}',
        'Remove': '{!! trans("team::view.Remove") !!}',
        'Move up': '{!! trans("team::view.Move up") !!}',
        'Move down': '{!! trans("team::view.Move down") !!}',
        'Save': '{!! trans("team::view.Save") !!}',
        'Edit team': '{!! trans("team::view.Edit team") !!}',
        'Create team': '{!! trans("team::view.Create team") !!}',
        'Mail group': '{!! trans("team::view.Mail group") !!}',
        'Team name': '{!! trans("team::view.Team name") !!}',
        'Branch code': '{!! trans("team::view.Branch code") !!}',
        'Team code': '{!! trans("team::view.Team code") !!}',
        'Functional unit': '{!! trans("team::view.Functional unit") !!}',
        'Is function unit': '{!! trans("team::view.Is function unit") !!}',
        'New': '{!! trans("team::view.New") !!}',
        'Permission following function unit': '{!! trans("team::view.Permission following function unit") !!}',
        'Team parent': '{!! trans("team::view.Team parent") !!}',
        'Is software development': '{!! trans("team::view.Is software development") !!}',
        'Are you sure delete this team and children of this team?': '{!! trans("team::view.Are you sure delete this team and children of this team?") !!}',
        'Please choose team or role to set permission function': '{!! trans("team::view.Please choose team or role to set permission function") !!}',
        'Team is not function': '{!! trans("team::view.Team is not function") !!}',
        'Team permisstion as team': '{!! trans("team::view.Team permisstion as team") !!}',
        'Not found position to set permission function': '{!! trans("team::view.Not found position to set permission function") !!}',
        'Screen': '{!! trans("team::view.Screen") !!}',
        'Function': '{!! trans("team::view.Function") !!}',
        'Are you sure delete this role and all link this role with employee?': '{!! trans("team::view.Are you sure delete this role and all link this role with employee?") !!}',
        'Not found role special': '{!! trans("team::view.Not found role special") !!}',
        'Permission': '{!! trans("team::view.Permission") !!}',
        'Edit role': '{!! trans("team::view.Edit role") !!}',
        'Create role': '{!! trans("team::view.Create role") !!}',
        'Name': '{!! trans("team::view.Name") !!}',
        'Description': '{!! trans("team::view.Description") !!}',
        'Permission function': '{!! trans("team::view.Permission function") !!}',
        'Permission function of team': '{!! trans("team::view.Permission function of team") !!}',
        'Permission function of role': '{!! trans("team::view.Permission function of role") !!}',
        'Is branch': '{!! trans("team::view.Is branch") !!}',
        'Are you sure delete postion team?': '{!! trans("team::view.Are you sure delete postion team?") !!}',
        'Not found position': '{!! trans("team::view.Not found position") !!}',
        'Edit team position': '{!! trans("team::view.Edit team position") !!}',
        'Create team position': '{!! trans("team::view.Create team position") !!}',
        'Position name': '{!! trans("team::view.Position name") !!}',
        'Scope apply': '{!! trans("team::view.Scope apply") !!}',
    };
    var teamParams = {
        urlInitTeamSetting: '{{ route("team::setting.team.init") }}',
        urlViewTeam: '{{ route("team::setting.team.view", ["id" => null]) }}',
        urlEditTeam: '{{ route("team::setting.team.edit", ["id" => null]) }}',
        urlSaveTeam: '{{ route("team::setting.team.save") }}',
        urlDeleteTeam: '{{ route("team::setting.team.delete") }}',
        urlMoveTeam: '{{ route("team::setting.team.move") }}',
        urlSavePermission: '{{ route("team::setting.team.rule.save") }}',
        urlViewRole: '{{ route("team::setting.role.view", ["id" => null]) }}',
        urlSaveRolePermission: '{{ route("team::setting.role.rule.save") }}',
        urlSaveRole: '{{ route("team::setting.role.save") }}',
        urlDeleteRole: '{{ route("team::setting.role.delete") }}',
        urlSavePosition: '{{ route("team::setting.team.position.save") }}',
        urlDeletePosition: '{{ route("team::setting.team.position.delete") }}',
        urlMovePosition: '{{ route("team::setting.team.position.move") }}',
        scopeIconGuide: '{!! \Rikkei\Team\Model\Permission::getScopeIconGuide() !!}',
        scopes: JSON.parse('{!! json_encode(\Rikkei\Team\Model\Permission::getScopes()) !!}'),
        scopeTeam: '{{ \Rikkei\Team\Model\Permission::SCOPE_TEAM }}',
        BOD_ID: '{{ \Rikkei\Team\Model\Team::TEAM_BOD_ID }}',
    };
</script>
<script src="{{ CoreUrl::asset('team/js/react-team-setting.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/react-permiss-rule.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/react-role-setting.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/react-position-setting.js') }}"></script>
@endsection
