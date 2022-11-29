import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Helper from './react-helper';
import TeamService from './react-team-service';
import TeamList from './components/team/team-list';
import TeamEdit from './components/team/team-edit';

export default class ReactTeamSetting extends Component {
    constructor(props) {
        super(props);
        this.state = {
            teams: [],
            originTeams: [],
            loading: false,
            loadingEdit: false,
            init: false,
            currentTeam: {
                id: '',
                name: ''
            },
            teamEdit: this.resetTeamEdit(),
            selectingTeam: false,
            savingTeam: false,
            deletingTeam: false,
            movingTeam: false,
            errorSaveTeam: '',
        }

        this.renderTeamList = this.renderTeamList.bind(this);
        this.selectTeam = this.selectTeam.bind(this);
        this.editTeam = this.editTeam.bind(this);
        this.handleChangeTeamField = this.handleChangeTeamField.bind(this);
        this.saveTeam = this.saveTeam.bind(this);
        this.createTeam = this.createTeam.bind(this);
        this.removeTeam = this.removeTeam.bind(this);
        this.moveTeam = this.moveTeam.bind(this);
        this.resetTeam = this.resetTeam.bind(this);

        let that = this;
        $('body').on('change', '#form-team-edit .select-search', function () {
            let name = $(this).attr('data-field');
            if (name) {
                let {teamEdit} = that.state;
                teamEdit[name] = $(this).val();
                that.setState({
                    teamEdit: teamEdit
                });
            }
        });
    }

    /*
     * reset null teamEdit
     */
    resetTeamEdit() {
        return {
            id: '',
            name: '',
            mail_group: '',
            branch_code: '',
            code: '',
            is_branch: 0,
            permission_same: 0,
            follow_team_id: '',
            is_function: '',
            is_soft_dev: '',
            parent_id: '',
        };
    }

    /*
     * render team html list
     */
    renderTeamList(e) {
        e.preventDefault();
        if (this.state.loading) {
            return;
        }
        this.refreshTeamList();
    }

    /*
     * refresh teams list
     */
    refreshTeamList() {
        let that = this;
        that.setState({
            loading: true,
            deletingTeam: true,
            movingTeam: true,
        });
        $.ajax({
            type: 'GET',
            url: teamParams.urlInitTeamSetting,
            success: function (data) {
                let nestedTeams = that.toNestedList(data.teams);
                that.setState({
                    originTeams: data.teams,
                    teams: nestedTeams,
                    init: true,
                    errorSaveTeam: '',
                });
                //init roles list
                TeamService.setRoleListData({
                    roleAll: data.roleAll,
                    positionAll: data.positionAll
                });
                $('#btn_load_roles').click();
                $('#btn_load_positions').click();

                setTimeout(function () {
                    $('.hight-same').matchHeight({
                        child: '.box'
                    });

                    let currentTeamId = TeamService.getStoredItem('setting_team');
                    if (currentTeamId) {
                        that.processSelectTeam({id: currentTeamId});
                    }
                }, 100);
            },
            error: function () {
                
            },
            complete: function () {
                that.setState({
                    loading: false,
                    deletingTeam: false,
                    movingTeam: false,
                });
            }
        });
    }

    /*
     * convert to nested list
     */
    toNestedList(list, parentId = null, level = -1) {
        let result = [];
        level++;
        for (let i = 0; i < list.length; i++) {
            let item = list[i];
            if (typeof item.childs == 'undefined') {
                item.childs = [];
            }
            if (item.parent_id == parentId) {
                item.level = level;
                item.childs = this.toNestedList(list, item.id, level);
                result.push(item);
            }
        }
        return result;
    }

    /*
     * click select team
     */
    selectTeam(e, team) {
        e.preventDefault();
        $('#btn_reset_role').click();
        this.processSelectTeam(team);
    }

    resetTeam(e) {
        e.preventDefault();
        this.setState({
            currentTeam: {
                id: '',
                name: ''
            },
        });
    }

    processSelectTeam(team) {
        TeamService.storeItem('setting_role', '');
        TeamService.storeItem('setting_team', team.id);

        let that = this;
        let {seletingTeam} = that.state;
        if (seletingTeam) {
            return;
        }
        this.setState({
            currentTeam: team
        });

        $('#btn_init_rule_data').attr('data-team-id', team.id).attr('data-type', 'team');
        $('#btn_init_rule_data').click();
    }

    /*
     * click btn add
     */
    createTeam(e) {
        e.preventDefault();
        let modalEdit = $('#team-edit-form');
        modalEdit.modal('show');
        this.setState({
            teamEdit: this.resetTeamEdit(),
        });
        selectSearchReload();
    }

    /*
     * click btn edit
     */
    editTeam(e) {
        e.preventDefault();
        let that = this;
        let {currentTeam, loadingEdit, teamEdit} = that.state;
        if (loadingEdit || !currentTeam.id) {
            return;
        }
        let modalEdit = $('#team-edit-form');
        modalEdit.modal('show');
        that.setState({
            loadingEdit: true,
            errorSaveTeam: '',
        });
        $.ajax({
            url: teamParams.urlEditTeam + '/' + currentTeam.id,
            type: 'GET',
            success: function (data) {
                for (let key in teamEdit) {
                    teamEdit[key] = data.team[key];
                }
                teamEdit.permission_same = parseInt(teamEdit.follow_team_id) ? 1 : 0;
                that.setState({
                    teamEdit: teamEdit,
                });
                selectSearchReload();
            },
            error: function (error) {
                modalEdit.modal('hide');
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON.message
                });
            },
            complete: function () {
                that.setState({loadingEdit: false});
            },
        });
    }

    /*
     * onchange fields of team
     */
    handleChangeTeamField(e, field, type = 'text') {
        let {teamEdit} = this.state;
        if (type == 'checked') {
            let newVal = 1;
            if (parseInt(teamEdit[field]) == 1) {
                newVal = 0;
            } else {
                newVal = 1;
            }
            teamEdit[field] = newVal;
        } else {
            teamEdit[field] = e.target.value;
        }
        this.setState({
            teamEdit: teamEdit
        });
        if (field == 'is_function') {
            setTimeout(function () {
                selectSearchReload();
            }, 100);
        }
    }

    /*
     * save team
     */
    saveTeam(e) {
        e.preventDefault();
        let that = this;
        if (that.state.savingTeam) {
            return;
        }
        that.setState({
            savingTeam: true
        });
        let {teamEdit} = that.state;
        let dataEdit = $.extend({}, teamEdit);
        delete dataEdit.permission_same;
        $.ajax({
            url: teamParams.urlSaveTeam,
            type: 'POST',
            data: {
                _token: siteConfigGlobal.token,
                item: dataEdit,
                permission_same: teamEdit.permission_same,
            },
            success: function (data) {
                $('#team-edit-form').modal('hide');
                bootbox.alert({
                    className: 'modal-success',
                    message: data.message,
                });
               that.refreshTeamList();
               if (teamEdit.id) {
                   that.processSelectTeam({id: teamEdit.id, name: teamEdit.name});
               }
            },
            error: function (error) {
                that.setState({
                    errorSaveTeam: error.responseJSON.message
                });
            },
            complete: function () {
                that.setState({savingTeam: false});
            }
        });
    }

    /*
     * remove team
     */
    removeTeam(e) {
        e.preventDefault();
        let that = this;
        let {currentTeam, deletingTeam} = that.state;
        if (deletingTeam || !currentTeam.id) {
            return;
        }
        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Are you sure delete this team and children of this team?'),
            callback: function (result) {
                if (result) {
                    that.setState({deletingTeam: true});
                    $.ajax({
                        type: 'delete',
                        url: teamParams.urlDeleteTeam,
                        data: {
                            _token: siteConfigGlobal.token,
                            id: currentTeam.id
                        },
                        success: function (data) {
                            bootbox.alert({
                                className: 'modal-success',
                                message: data.message,
                            });
                            that.refreshTeamList();
                            TeamService.storeItem('setting_team', null);

                            $('#btn_init_rule_data').attr('data-team-id', null).attr('data-type', 'team');
                            $('#btn_init_rule_data').click();
                        },
                        error: function (error) {
                            bootbox.alert({
                                className: 'modal-danger',
                                message: error.responseJSON.message,
                            });
                        },
                        complete: function () {
                            that.setState({deletingTeam: false});
                        },
                    });
                }
            },
        });
    }

    /*
     * move team up/down (sort order)
     */
    moveTeam(e, moveType) {
        e.preventDefault();
        let that = this;
        let {currentTeam, movingTeam} = that.state;
        if (movingTeam || !currentTeam.id) {
            return;
        }

        let data = {
            _token: siteConfigGlobal.token,
            id: currentTeam.id
        };
        if (moveType == 'move_up') {
            data.move_up = 1;
        }
        that.setState({movingTeam: true});
        $.ajax({
            type: 'post',
            url: teamParams.urlMoveTeam,
            data: data,
            success: function (data) {
                that.refreshTeamList();
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON.message,
                })
            },
            complete: function () {
                that.setState({movingTeam: false});
            },
        });
    }

    render () {
        let that = this;
        let {
            teams,
            loading,
            init,
            currentTeam,
            teamEdit,
            savingTeam,
            errorSaveTeam,
            deletingTeam,
            movingTeam,
        } = this.state;

        return (
            <div>
                {teams.length > 0 ? (
                <React.Fragment>
                    <div className="col-md-8 col-sm-8 team-list table-list-scroll">
                        <TeamList
                            teams={teams}
                            selectTeam={this.selectTeam}
                            currentTeam={this.state.currentTeam}
                            editTeam={this.editTeam}
                        />
                    </div>
                    <div className="col-md-4 col-sm-4 team-action">
                        <p>
                            <button type="button" className="btn-add btn-action"
                                onClick={(e) => that.createTeam(e)}>
                                <span>{Helper.trans('Add')}</span>
                            </button>
                        </p>
                        <p>
                            <button type="button" className="btn-edit btn-action" disabled={!currentTeam.id}
                                onClick={(e) => that.editTeam(e)}>
                                <span>{Helper.trans('Edit')}</span>
                            </button>
                        </p>
                        <p>
                            <button type="button" className="btn btn-danger btn-action" 
                                disabled={deletingTeam || !currentTeam.id}
                                onClick={(e) => that.removeTeam(e)}>
                                <span>{Helper.trans('Remove')}</span>
                            </button>
                        </p>
                        <p>
                            <button type="button" className="btn-move btn-action no-disabled"
                                disabled={movingTeam || !currentTeam.id}
                                onClick={(e) => that.moveTeam(e, 'move_up')}>{Helper.trans('Move up')}</button>
                        </p>
                        <p>
                            <button type="button" className="btn-move btn-action no-disabled"
                                disabled={movingTeam || !currentTeam.id}
                                onClick={(e) => that.moveTeam(e, 'move_down')}>{Helper.trans('Move down')}</button>
                        </p>
                    </div>

                    <TeamEdit
                        teamEdit={teamEdit}
                        handleChangeTeamField={this.handleChangeTeamField}
                        teams={teams}
                        saveTeam={this.saveTeam}
                        savingTeam={savingTeam}
                        errorSaveTeam={errorSaveTeam}
                    />
                </React.Fragment>
                ) : null}

                {teams.length < 1 && init ? (
                    <div>
                        <p className="alert alert-warning">{Helper.trans('Not found team')}</p>
                    </div>
                ) : null}

                {loading ? (
                    <p className="text-center"><i className="fa fa-spin fa-refresh"></i></p>
                ) : null}

                <button id="btn_load_teams" className="hidden" onClick={(e) => this.renderTeamList(e)}></button>
                <button id="btn_reset_team" className="hidden" onClick={(e) => this.resetTeam(e)}></button>
            </div>
        )
    }
}

if (document.getElementById('team_setting_container')) {
    ReactDOM.render(<ReactTeamSetting />, document.getElementById('team_setting_container'));
}


