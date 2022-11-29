import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import Helper from './react-helper';
import TeamService from './react-team-service';
import PositionEdit from './components/position/position-edit';

export default class PositionSetting extends Component {

    constructor(props) {
        super(props);
        this.state = {
            positionAll: [],
            init: false,
            loading: false,
            posEdit: this.resetPosEdit(),
            currentPos: {
                id: '',
                role: '',
            },
            savingPosition: false,
            deletingPos: false,
            movingPos: false,
            errorSavePos: '',
        }

        this.selectPosition = this.selectPosition.bind(this);
        this.addPosition = this.addPosition.bind(this);
        this.editPosition = this.editPosition.bind(this);
        this.savePosition = this.savePosition.bind(this);
        this.removePosition = this.removePosition.bind(this);
        this.movePosition = this.movePosition.bind(this);
        this.handleChangePosField = this.handleChangePosField.bind(this);
    }

    resetPosEdit() {
        return {
            id: '',
            role: '',
        };
    }

    renderPositionList() {
        let posData = TeamService.getRoleListData();
        this.setState({
            positionAll: posData.positionAll,
            init: true,
            loading: TeamService.getIniting(),
        });
    }

    selectPosition(e, pos) {
        e.preventDefault();
        this.setState({
            currentPos: $.extend({}, pos)
        });
    }

    addPosition(e) {
        e.preventDefault();
        $('#position-edit-form').modal('show');
        this.setState({
            posEdit: this.resetPosEdit(),
            errorSavePos: '',
        });
    }

    handleChangePosField(e, field) {
        let {posEdit} = this.state;
        posEdit[field] = e.target.value;
        this.setState({
            posEdit: posEdit
        });
    }

    editPosition(e) {
        e.preventDefault();
        $('#position-edit-form').modal('show');
        let {currentPos} = this.state;
        this.setState({
            posEdit: $.extend({}, currentPos),
            errorSavePos: '',
        });
    }

    savePosition(e) {
        e.preventDefault();
        let {posEdit, savingPosition, positionAll} = this.state;
        let that = this;
        if (savingPosition) {
            return;
        }
        that.setState({
            savingPosition: true,
            errorSavePos: '',
        });

        $.ajax({
            url: teamParams.urlSavePosition,
            type: 'POST',
            data: {
                _token: siteConfigGlobal.token,
                position: posEdit,
            },
            success: function(data) {
                $('#position-edit-form').modal('hide');
                bootbox.alert({
                    className: 'modal-success',
                    message: data.message
                });
                if (posEdit.id) {
                    let index = Helper.findIndexById(posEdit.id, positionAll);
                    if (index > -1) {
                        positionAll[index] = posEdit;
                    }
                    that.setState({
                        positionAll: positionAll
                    });
                } else {
                    $('#btn_load_teams').click();
                }
            },
            error: function (error) {
                that.setState({
                    errorSavePos: error.responseJSON.message
                });
            },
            complete: function () {
                that.setState({
                    savingPosition: false,
                });
            },
        });
    }

    removePosition(e) {
        e.preventDefault();
        let {currentPos, deletingPos} = this.state;
        let that = this;
        if (!currentPos.id || deletingPos) {
            return;
        }

        bootbox.confirm({
            className: 'modal-danger',
            message: Helper.trans('Are you sure delete postion team?'),
            callback: function (result) {
                if (result) {
                    that.setState({
                        deletingPos: true
                    });
                    $.ajax({
                        type: 'DELETE',
                        url: teamParams.urlDeletePosition,
                        data: {
                            _token: siteConfigGlobal.token,
                            id: currentPos.id
                        },
                        success: function (data) {
                            bootbox.alert({
                                className: 'modal-success',
                                message: data.message
                            });
                            that.setState({
                                currentPos: that.resetPosEdit()
                            });
                            $('#btn_load_teams').click();
                        },
                        error: function (error) {
                            bootbox.alert({
                                className: 'modal-danger',
                                message: error.responseJSON.message
                            });
                        },
                        complete: function () {
                            that.setState({
                                deletingPos: false
                            });
                        },
                    });
                }
            }
        })
    }

    movePosition(e, moveUp = false) {
        e.preventDefault();
        let {currentPos, movingPos} = this.state;
        let that = this;
        if (!currentPos.id || movingPos) {
            return;
        }

        that.setState({movingPos: true});
        $.ajax({
            type: 'POST',
            url: teamParams.urlMovePosition,
            data: {
                _token: siteConfigGlobal.token,
                id: currentPos.id,
                move_up: moveUp ? 1 : 0
            },
            success: function() {
                $('#btn_load_teams').click();
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON.message
                });
            },
            complete: function () {
                that.setState({
                    movingPos: false,
                });
            },
        })
    }

    render () {
        let {
            init,
            positionAll,
            currentPos,
            posEdit,
            loading,
            savingPosition,
            errorSavePos,
            deletingPos,
            movingPos,
        } = this.state;

        return (
            <React.Fragment>
                {positionAll.length > 0 ? (
                    <React.Fragment>
                        <div className="col-md-7 col-sm-7 position-list">
                            <table className="table table-bordered">
                                <tbody>
                                    {positionAll.map((posItem, posIndex) => {
                                        return (
                                            <tr key={posIndex}>
                                                <td><a href="#" onClick={(e) => this.selectPosition(e, posItem)}
                                                    className={currentPos.id == posItem.id ? 'active' : ''}>{posItem.role}</a></td>
                                            </tr>
                                        )
                                    })}
                                </tbody>
                            </table>
                        </div>
                        <div className="col-md-5 col-sm-5 team-action">
                            <p>
                                <button type="button" className="btn-add btn-action"
                                    onClick={(e) => this.addPosition(e)}>
                                    <span>{Helper.trans('Add')}</span>
                                </button>
                            </p>
                            <p>
                                <button type="button" className="btn-edit btn-action"
                                    onClick={(e) => this.editPosition(e)}
                                    disabled={!currentPos.id || savingPosition}>
                                    <span>{Helper.trans('Edit')}</span>
                                </button>
                            </p>
                            <p>
                                <button type="submit" className="btn-delete btn-action"
                                    data-noti={Helper.trans('Are you sure delete postion team?')}
                                    onClick={(e) => this.removePosition(e)}
                                    disabled={!currentPos.id || deletingPos}>
                                    <span>{Helper.trans('Remove')}</span> {deletingPos ? (<i className="fa fa-spin fa-refresh"></i>) : null}
                                </button>
                            </p>
                            <p>
                                <button type="button" name="move_up"
                                    className="btn-move btn-action no-disabled"
                                    onClick={(e) => this.movePosition(e, true)}
                                    disabled={!currentPos.id || movingPos}>
                                    {Helper.trans('Move up')}
                                </button>
                            </p>
                            <p>
                                <button type="button" name="move_down"
                                    className="btn-move btn-action no-disabled"
                                    onClick={(e) => this.movePosition(e)}
                                    disabled={!currentPos.id || movingPos}>
                                    {Helper.trans('Move down')}
                                </button>
                            </p>
                        </div>
                    </React.Fragment>
                ) : null}
        
                <PositionEdit
                    posEdit={posEdit}
                    handleChangePosField={this.handleChangePosField}
                    savePosition={this.savePosition}
                    savingPosition={savingPosition}
                    errorSavePos={errorSavePos}
                />

                {positionAll.length < 1 && init ? (
                    <div>
                        <p className="alert alert-warning">{Helper.trans('Not found position')}</p>
                    </div>
                ) : null}
        
                {loading ? (
                    <p className="text-center"><i className="fa fa-spin fa-refresh"></i></p>
                ) : null}

                <button id="btn_load_positions" className="hidden" onClick={(e) => this.renderPositionList(e)}></button>
            </React.Fragment>
        )
    }
}

if (document.getElementById('team_position_container')) {
    ReactDOM.render(<PositionSetting />, document.getElementById('team_position_container'));
}


