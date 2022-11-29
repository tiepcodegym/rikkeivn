import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './react-helper';
import RuleList from './components/rule/rule-list';

export default class ReactPermissRule extends Component {

    constructor(props) {
        super(props);
        this.state = {
            type: 'team',
            permission: {},
            permissionAs: null,
            rolesPosition: [],
            team: null,
            teamPermissions: [],
            rolePermissions: [],
            roleModel: null,
            acl: [],
            transAcl: [],
            guides: [],
            scopeIcons: [],
            scopeOptions: [],
            teamOptions: [],
            initing: false,
            saving: false,
            retry: 0,
        };

        this.changeScope = this.changeScope.bind(this);
        this.savePermission = this.savePermission.bind(this);
        this.onChangeScopeTeam = this.onChangeScopeTeam.bind(this);
    }

    componentDidMount() {
        let that = this;
        $('body').on('change', '.select-team-scope', function (e) {
            let teamIds = $(this).val();
            let elAction = $(this).closest('tr');
            let actionId = elAction.attr('data-action');
            let roleId = $(this).attr('data-role');
            let key = 'a-' + actionId + '-r-' + roleId;
            let {permission, teamPermissions} = that.state;
            if (typeof permission[key] == 'undefined') {
                let scope = 0;
                if (typeof teamPermissions['a-' + actionId + '-r-' + roleId] != 'undefined') {
                    scope = teamPermissions['a-' + actionId + '-r-' + roleId].scope;
                }
                permission[key] = {
                    role_id: roleId,
                    action_id: actionId,
                    scope: scope
                };
            }
            permission[key].scope_team_ids = teamIds;
            that.setState({
                permission: permission
            });
        });
    }

    /*
     * on click team init data
     */
    initData(e) {
        e.preventDefault();
        let teamId = $(e.target).attr('data-team-id');
        let type = $(e.target).attr('data-type') || 'team';
        if (!teamId) {
            if (type == 'team') {
                this.setState({team: null});
            } else {
                this.setState({roleModel: null});
            }
            return false;
        }
        let that = this;
        if (that.state.initing) {
            return;
        }

        let urlLoading;
        if (type == 'team') {
            urlLoading = teamParams.urlViewTeam + '/' + teamId;
            that.setState({roleModel: null});
        } else if (type == 'role') {
            urlLoading = teamParams.urlViewRole + '/' + teamId; //role Id
            that.setState({team: null});
        } else {
            return;
        }

        that.setState({
            type: type,
            initing: true
        });
        let data = {};
        let getFields = ['acl', 'transAcl', 'guides', 'scopeIcons', 'scopeOptions', 'teamOptions'];
        for (let i in getFields) {
            if (!that.state[getFields[i]] || that.state[getFields[i]].length < 1) {
                data[getFields[i]] = 1;
            }
        }
        $.ajax({
            type: 'GET',
            url: urlLoading,
            data: data,
            success: function(data) {
                let arrayPermiss = [];
                if (type == 'team') {
                    if (data.teamPermissions && data.teamPermissions.length > 0) {
                        for (let i = 0; i < data.teamPermissions.length; i++) {
                            let item = data.teamPermissions[i];
                            arrayPermiss['a-' + item.action_id + '-r-' + item.role_id] = {scope: item.scope};
                        }
                    }
                } else {
                    if (data.rolePermissions && data.rolePermissions.length > 0) {
                        for (let i = 0; i < data.rolePermissions.length; i++) {
                            let item = data.rolePermissions[i];
                            arrayPermiss['a-' + item.action_id + '-r-' + teamId] = {
                                scope: item.scope,
                                scope_team_ids: item.scope_team_ids
                            }; //teamId = roleId
                        }
                    }
                    delete data.rolePermissions;
                }
                data.teamPermissions = arrayPermiss;
                data.permission = {};
                that.setState(data);
                setTimeout(function () {
                    that.colAclTooltip();
                }, 500);
            },
            error: function (error) {
                if (that.state.retry < 1) {
                    that.setState({retry: 1});
                    setTimeout(function () {
                        $('#btn_init_rule_data').click();
                    }, 1000);
                } else {
                    bootbox.alert({
                        className: 'modal-danger',
                        message: error.responseJSON.message,
                    });
                }
            },
            complete: function () {
                that.setState({
                    initing: false
                });
            },
        });
    }

    onChangeScopeTeam(optionChange) {
        let isSelected = optionChange.is(':selected');
        let optionVal = optionChange.attr('value') || 0;
        let elSelect = optionChange.closest('select');
        let selectVal = elSelect.val();
        if (!selectVal) {
            selectVal = [];
        }
        if (optionVal != teamParams.BOD_ID) {
            let childIds = this.collectOptTeamChilds(elSelect, isSelected, optionVal);
            for (let i = 0; i < childIds.length; i++) {
                let iVal = childIds[i];
                let iIndex = selectVal.indexOf(iVal);
                if (iIndex < 0) {
                    if (isSelected) {
                        selectVal.push(iVal);
                    }
                } else {
                    if (!isSelected) {
                        selectVal.splice(iIndex, 1);
                    }
                }
            }

            elSelect.val(selectVal);
            elSelect.multiselect('refresh');
        }
    }

    collectOptTeamChilds(elSelect, isSelected, parentId) {
        let elChilds = elSelect.find('option[data-parent="'+ parentId +'"]');
        let ids = [];
        if (elChilds.length < 1) {
            return ids;
        }
        let that = this;
        elChilds.each(function () {
            let iVal = $(this).attr('value') + '';
            if (ids.indexOf(iVal) < 0) {
                ids.push(iVal);
            }
            let childIds = that.collectOptTeamChilds(elSelect, isSelected, iVal);
            if (childIds.length > 0) {
                ids = ids.concat(childIds);
            }
        })
        return ids;
    }

    /**
     * hover to show tooltip acl
     */
    colAclTooltip() {
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

    triggerTeam(e, team) {
        e.preventDefault();
    }

    /*
     * change permission scope
     */
    changeScope(e, actionId, roleId, scope) {
        e.preventDefault();
        if (!scope) {
            scope = 0;
        }
        this.redirectChangeScope(actionId, roleId, scope);

        let elSelectScopeTeam = $(e.target).closest('tr').find('.select-team-scope');
        if (elSelectScopeTeam.length > 0) {
            if (scope == teamParams.scopeTeam) {
                elSelectScopeTeam.prop('disabled', false).multiselect('enable');
            } else {
                elSelectScopeTeam.val(null).prop('disabled', true);
                elSelectScopeTeam.multiselect('refresh');
                elSelectScopeTeam.multiselect('disable');
            }
        }
    }

    redirectChangeScope(actionId, roleId, scope) {
        let {teamPermissions, permission} = this.state;
        let key = 'a-' + actionId + '-r-' + roleId;
        if (typeof teamPermissions[key] == 'undefined') {
            teamPermissions[key] = {};
        }
        teamPermissions[key].scope = scope;
        permission[key] = $.extend(permission[key], {
            role_id: roleId,
            action_id: actionId,
            scope: scope
        });
        this.setState({
            teamPermissions: teamPermissions,
            permission: permission
        });
    }

    /*
     * save permisison
     */
    savePermission(e) {
        e.preventDefault();
        let that = this;
        let {permission, team, roleModel, type, saving} = that.state;
        if (saving) {
            return false;
        }

        that.setState({
            saving: true,
        });
        let urlSave, dataRequest = {
            _token: siteConfigGlobal.token,
            permission: permission,
        };
        if (type == 'team') {
            urlSave = teamParams.urlSavePermission;
            dataRequest.team = {
                id: team.id
            }
        } else {
            urlSave = teamParams.urlSaveRolePermission;
            dataRequest.role = {
                id: roleModel.id
            }
        }
        $.ajax({
            type: 'POST',
            url: urlSave,
            data: dataRequest,
            success: function(data) {
                bootbox.alert({
                   className: 'modal-success',
                   message: data.message,
                });
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON.message,
                });
            },
            complete: function () {
                that.setState({
                   saving: false, 
                });
            },
        });
    }

    render() {
        let {
            initing,
            team,
            roleModel,
            type,
            rolesPosition,
            permissionAs,
            teamPermissions,
            acl,
            transAcl,
            guides,
            scopeIcons,
            scopeOptions,
            saving,
            teamOptions,
        } = this.state;
        let renderHtml = null;
        if (!team && !roleModel) {
            renderHtml = 
                <p className="alert alert-warning">{Helper.trans('Please choose team or role to set permission function')}</p>
            
        } else {
            if (team) {
                if (!team.is_function || !parseInt(team.is_function)) {
                    renderHtml = 
                        <p className="alert alert-warning">{Helper.trans('Team is not function')}</p>
                    
                } else if (permissionAs) {
                    renderHtml = 
                        <p className="alert alert-warning">
                            {Helper.trans('Team permisstion as team')}
                            <b> {permissionAs.name}</b>
                        </p>
                    
                } else if (!rolesPosition || rolesPosition.length < 1) {
                    renderHtml = 
                        <p className="alert alert-warning">{Helper.trans('Not found position to set permission function')}</p>
                    
                } else {
                    renderHtml = <RuleList
                        rolesPosition={rolesPosition}
                        teamPermissions={teamPermissions}
                        acl={acl}
                        transAcl={transAcl}
                        guides={guides}
                        scopeIcons={scopeIcons}
                        scopeOptions={scopeOptions}
                        changeScope={this.changeScope}
                        onChangeScopeTeam={this.onChangeScopeTeam}
                        savePermission={this.savePermission}
                        saving={saving}
                        type={type}
                    />
                }
            } else if (roleModel) {
                renderHtml = <RuleList
                    rolesPosition={rolesPosition}
                    teamPermissions={teamPermissions}
                    acl={acl}
                    transAcl={transAcl}
                    guides={guides}
                    scopeIcons={scopeIcons}
                    scopeOptions={scopeOptions}
                    changeScope={this.changeScope}
                    onChangeScopeTeam={this.onChangeScopeTeam}
                    savePermission={this.savePermission}
                    saving={saving}
                    type={type}
                    teamOptions={teamOptions}
                    roleModel={roleModel}
                />
            }
        }
        return (
            <React.Fragment>
                <div className="box-header with-border">
                    {!team && !roleModel ? (
                        <h2 className="box-title">{Helper.trans('Permission function')}</h2>
                    ) : (
                    <React.Fragment>
                        {team ? (
                            <h2 className="box-title">{Helper.trans('Permission function of team')} <b>{team.name}</b></h2>
                        ) : (
                            <h2 className="box-title">{Helper.trans('Permission function of role')} <b>{roleModel.role}</b></h2>
                        )}
                    </React.Fragment>
                    )}
                </div>
                <div className="box-body">
                    {initing ? (
                        <p className="text-center"><i className="fa fa-spin fa-refresh"></i></p>
                    ) : (
                        renderHtml
                    )}
                    <button id="btn_init_rule_data" type="button" className="hidden" onClick={(e) => this.initData(e)}></button>
                </div>
            </React.Fragment>
        )
    }
}

if (document.getElementById('rule_permission_container')) {
    ReactDOM.render(<ReactPermissRule />, document.getElementById('rule_permission_container'));
}
