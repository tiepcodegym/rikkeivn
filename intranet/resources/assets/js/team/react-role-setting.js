import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Helper from './react-helper';
import RoleEdit from './components/role/role-edit';
import TeamService from './react-team-service';

export default class ReactRoleSetting extends Component {

    constructor(props) {
        super(props);
        this.state = {
            roleAll: [],
            init: false,
            loading: false,
            seletingRole: false,
            roleModel: null,
            roleEdit: this.resetRoleEdit(),
            savingRole: false,
            deletingRole: false,
            errorSaveRole: '',
            currentRole: {
                id: '',
                role: '',
                description: ''
            },
        }

        this.selectRole = this.selectRole.bind(this);
        this.resetRole = this.resetRole.bind(this);
        this.addRole = this.addRole.bind(this);
        this.editRole = this.editRole.bind(this);
        this.saveRole = this.saveRole.bind(this);
        this.deleteRole = this.deleteRole.bind(this);
        this.handleChangeRoleField = this.handleChangeRoleField.bind(this);
    }

    resetRoleEdit() {
        return {
            id: '',
            role: '',
            description: ''
        };
    }

    renderRoleList() {
        let roleData = TeamService.getRoleListData();
        this.setState({
            roleAll: roleData.roleAll,
            init: true,
            loading: TeamService.getIniting(),
        });

        let that = this;
        setTimeout(function () {
            let currentRoleId = TeamService.getStoredItem('setting_role');
            if (currentRoleId) {
                let currentRole = Helper.findItemById(currentRoleId, roleData.roleAll);
                if (currentRole) {
                    that.processSelectRole(currentRole);
                }
            }
        }, 100);
    }

    /*
     * click select team
     */
    selectRole(e, role) {
        e.preventDefault();
        $('#btn_reset_team').click();
        this.processSelectRole(role);
    }

    processSelectRole(role) {
        TeamService.storeItem('setting_role', role.id);
        TeamService.storeItem('setting_team', '');

        let that = this;
        let {seletingRole} = that.state;
        if (seletingRole) {
            return;
        }
        this.setState({
            currentRole: $.extend({}, role)
        });

        $('#btn_init_rule_data').attr('data-team-id', role.id).attr('data-type', 'role');
        $('#btn_init_rule_data').click();
    }

    /*
     * reset empty role
     */
    resetRole(e) {
        e.preventDefault();
        this.setState({
            currentRole: {
                id: '',
                role: '',
                description: ''
            }
        })
    }

    /*
     * change role field
     */
    handleChangeRoleField(e, field) {
        let {roleEdit} = this.state;
        roleEdit[field] = e.target.value;
        this.setState({
            roleEdit: roleEdit
        });
    }

    /*
     * show modal add new role
     */
    addRole(e) {
        e.preventDefault();
        $('#role-edit-form').modal('show');
        this.setState({
            roleEdit: this.resetRoleEdit()
        });
    }

    /*
     * show modal edit role
     */
    editRole(e) {
        e.preventDefault();
        let {currentRole} = this.state;
        if (!currentRole.description) {
            currentRole.description = '';
        }
        this.setState({
            roleEdit: $.extend({}, currentRole)
        });
        $('#role-edit-form').modal('show');
    }

    /*
     * save role
     */
    saveRole(e) {
        e.preventDefault();
        let that = this;
        let {savingRole, roleEdit} = that.state;
        if (savingRole) {
            return false;
        }

        that.setState({savingRole: true});
        $.ajax({
            type: 'POST',
            url: teamParams.urlSaveRole,
            data: {
                _token: siteConfigGlobal.token,
                role: roleEdit,
            },
            success: function (data) {
                bootbox.alert({
                    className: 'modal-success',
                    message: data.message,
                });
                $('#role-edit-form').modal('hide');
                let {roleAll} = that.state;
                if (!roleEdit.id) {
                    roleEdit.id = data.id;
                    roleAll.push(roleEdit);
                } else {
                    let index = Helper.findIndexById(roleEdit.id, roleAll);
                    if (index > -1) {
                        roleAll[index] = roleEdit;
                    }
                }
                that.setState({
                    errorSaveRole: '',
                    roleAll: roleAll,
                    roleEdit: that.resetRoleEdit(),
                });
            },
            error: function (error) {
                that.setState({
                    errorSaveRole: error.responseJSON.message
                });
            },
            complete: function () {
                that.setState({savingRole: false});
            }
        });
    }

    /*
     * delete role
     */
    deleteRole(e) {
        e.preventDefault();
        let that = this;
        let {deletingRole, currentRole, roleAll} = that.state;
        if (deletingRole) {
            return false;
        }

        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Are you sure delete this role and all link this role with employee?'),
            callback: function (result) {
                if (result) {
                    that.setState({deletingRole: true});
                    $.ajax({
                        type: 'DELETE',
                        url: teamParams.urlDeleteRole,
                        data: {
                            _token: siteConfigGlobal.token,
                            id: currentRole.id,
                        },
                        success: function (data) {
                            bootbox.alert({
                                className: 'modal-success',
                                message: data.message
                            });
                            let index = Helper.findIndexById(currentRole.id, roleAll);
                            if (index > -1) {
                                delete roleAll[index];
                            }
                            that.setState({
                                roleAll: roleAll,
                                roleModel: null
                            });
                            TeamService.storeItem('setting_role', null);

                            $('#btn_init_rule_data').attr('data-team-id', null).attr('data-type', 'role');
                            $('#btn_init_rule_data').click();
                        },
                        error: function (error) {
                            bootbox.alert({
                                className: 'modal-danger',
                                message: error.responseJSON.message
                            });
                        },
                        complete: function () {
                            that.setState({deletingRole: false});
                        }
                    });
                }
            }
        });
    }

    render () {
        let {
            roleAll,
            init,
            currentRole,
            roleEdit,
            savingRole,
            deletingRole,
            errorSaveRole,
        } = this.state;

        return (
            <React.Fragment>
                {roleAll.length > 0 ? (
                    <React.Fragment>
                        <div className="col-md-7 col-sm-7 position-list table-list-scroll">
                            <table className="table table-bordered">
                                <tbody>
                                    {roleAll.map((roleItem, roleIndex) => (
                                       <tr key={roleIndex}>
                                            <td>
                                                <a href="team::setting.role.view"
                                                    className={currentRole.id == roleItem.id ? 'active' : ''}
                                                    onClick={(e) => this.selectRole(e, roleItem)}>
                                                    {roleItem.role}
                                                </a>
                                            </td>
                                        </tr> 
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="col-md-5 col-sm-5 team-action">
                            <p>
                                <button type="button" className="btn-add btn-action"
                                    onClick={(e) => this.addRole(e)}>
                                    <span>{Helper.trans('Add')}</span>
                                </button>
                            </p>
                            <p>
                                <button type="button" className="btn-edit btn-action"
                                    disabled={!currentRole.id}
                                    onClick={(e) => this.editRole(e)}>
                                    <span>{Helper.trans('Edit')}</span>
                                </button>
                            </p>
                            <p>
                                <button type="submit" className="btn-delete btn-action" 
                                    data-noti={Helper.trans('Are you sure delete this role and all link this role with employee?')}
                                    disabled={!currentRole.id}
                                    onClick={(e) => this.deleteRole(e)}>
                                    <span>
                                        {Helper.trans('Remove')} {deletingRole ? (<i className="fa fa-spin fa-refresh"></i>) : null}
                                    </span>
                                </button>
                            </p>
                        </div>

                        <RoleEdit
                            roleEdit={roleEdit}
                            handleChangeRoleField={this.handleChangeRoleField}
                            saveRole={this.saveRole}
                            savingRole={savingRole}
                            errorSaveRole={errorSaveRole}
                        />
                    </React.Fragment>
                ) : null}

                {roleAll.length < 1 && init ? (
                    <div>
                        <p className="alert alert-warning">{Helper.trans('Not found role special')}</p>
                    </div>
                ) : null}

                <button id="btn_load_roles" className="hidden" onClick={(e) => this.renderRoleList(e)}></button>
                <button id="btn_reset_role" className="hidden" onClick={(e) => this.resetRole(e)}></button>
            </React.Fragment>
        )
    }
}

if (document.getElementById('role_setting_container')) {
    ReactDOM.render(<ReactRoleSetting />, document.getElementById('role_setting_container'));
}


