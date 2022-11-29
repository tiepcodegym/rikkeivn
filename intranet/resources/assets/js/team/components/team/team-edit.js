import React, {Component} from 'react';
import Helper from './../../react-helper';
import TeamOptions from './team-options';

export default class TeamEdit extends Component {

    render() {
        let {
            teamEdit,
            handleChangeTeamField,
            teams,
            saveTeam,
            savingTeam,
            errorSaveTeam
        } = this.props;

        return (
            <div className="modal fade" id="team-edit-form">
                <div className="modal-dialog modal-dialog-team" role="document">
                    <div className="modal-content">
                        <form className="form" method="post" action="" id="form-team-edit"
                            onSubmit={saveTeam}>
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 className="modal-title" id="myModalLabel">
                                    {teamEdit.id ? Helper.trans('Edit team') : Helper.trans('Create team')}
                                </h4>
                            </div>
                            <div className="modal-body">
                                {errorSaveTeam ? (
                                    <div className="margin-bottom-15">{typeof errorSaveTeam == 'object' ? (
                                        <div>
                                            {Object.keys(errorSaveTeam).map((field, index) => (
                                                <ul key={index}>
                                                    {errorSaveTeam[field].map((mess, key) => (
                                                        <li key={key} className="error">{mess}</li>
                                                    ))}
                                                </ul>
                                            ))}
                                        </div>
                                    ) : (
                                        <ul>
                                            <li className="error">{errorSaveTeam}</li>
                                        </ul>
                                    )}</div>
                                ) : null}

                                <div className="form-group row">
                                    <label htmlFor="team-name" className="col-sm-3 required"><b>{Helper.trans('Team name')}</b> <em>*</em></label>
                                    <div className="col-sm-8">
                                        <input type="text" className="form-control" id="team-name" name="item[name]" 
                                            value={teamEdit.name} required
                                            onChange={(e) => handleChangeTeamField(e, 'name')} />
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3 required"><b>{Helper.trans('Mail group')}</b></label>
                                    <div className="col-sm-8">
                                        <input type="text" className="form-control" name="item[mail_group]"
                                            value={teamEdit.mail_group ? teamEdit.mail_group : ''}
                                            onChange={(e) => handleChangeTeamField(e, 'mail_group')} />
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3 required"><b>{Helper.trans('Branch code')}</b> <em>*</em></label>
                                    <div className="col-sm-8">
                                        <input type="text" className="form-control" name="item[branch_code]"
                                            value={teamEdit.branch_code ? teamEdit.branch_code : ''} required
                                            onChange={(e) => handleChangeTeamField(e, 'branch_code')} />
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3 required"><b>{Helper.trans('Team code')}</b> <em>*</em></label>
                                    <div className="col-sm-8">
                                        <input type="text" className="form-control" name="item[code]" 
                                            value={teamEdit.code ? teamEdit.code : ''} required
                                            onChange={(e) => handleChangeTeamField(e, 'code')} />
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3"><b>{Helper.trans('Is branch')}</b></label>
                                    <div className="col-sm-8">
                                        <input type="checkbox" name="item[is_branch]" value="1"
                                            checked={teamEdit.is_branch == 1}
                                            onChange={(e) => handleChangeTeamField(e, 'is_branch', 'checked')}/>
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3"><b>{Helper.trans('Functional unit')} </b></label>
                                </div>
                                <div className="form-group row row-sub">
                                    <div className="col-sm-3">
                                        <input type="checkbox" name="item[is_function]" id="is-function" className="input-is-function" data-id="group-"
                                            checked={teamEdit.is_function == 1}
                                            value="1" onChange={(e) => handleChangeTeamField(e, 'is_function', 'checked')} />
                                            <span>&nbsp;</span>
                                        <label htmlFor="is-function">{Helper.trans('Is function unit')}</label>
                                    </div>
                                    {parseInt(teamEdit.is_function) ? (
                                        <div className="col-sm-8 team-group-function">
                                            <p>
                                                <label>
                                                    <input type="radio" checked={!teamEdit.permission_same || teamEdit.permission_same == 0} value="0"
                                                        onChange={(e) => handleChangeTeamField(e, 'permission_same')} />
                                                    <span>&nbsp; {Helper.trans('New')}</span>
                                                </label>
                                            </p>
                                            <div className="row">
                                                <p className="col-md-6">
                                                    <label>
                                                        <input type="radio" checked={teamEdit.permission_same == 1} value="1"
                                                            onChange={(e) => handleChangeTeamField(e, 'permission_same')} />
                                                        <span>&nbsp; {Helper.trans('Permission following function unit')}</span>
                                                    </label>
                                                </p>
                                                <p className="col-md-6">
                                                    <TeamOptions
                                                        teams={teams}
                                                        className={'input-select select-search'}
                                                        selected={teamEdit.follow_team_id}
                                                        fieldName={'follow_team_id'}
                                                        handleChangeTeamField={handleChangeTeamField}
                                                    />
                                                </p>
                                            </div>
                                        </div>
                                    ) : null}
                                    <div className="clearfix"></div>
                                </div>

                                <div className="form-group row">
                                    <label htmlFor="team-parent" className="col-sm-3"><b>{Helper.trans('Team parent')}</b></label>
                                    <div className="col-sm-8">
                                        <TeamOptions
                                            teams={teams}
                                            className={'input-select select-search'}
                                            selected={teamEdit.parent_id}
                                            fieldName={'parent_id'}
                                            handleChangeTeamField={handleChangeTeamField}
                                        />
                                    </div>
                                </div>
                                <div className="form-group row">
                                    <label className="col-sm-3" htmlFor="is_soft_dev"><b>{Helper.trans('Is software development')}</b></label>
                                    <div className="col-sm-8">
                                        <input type="checkbox" name="item[is_soft_dev]" id="is_soft_dev" 
                                            checked={teamEdit.is_soft_dev == 1}
                                            onChange={(e) => handleChangeTeamField(e, 'is_soft_dev', 'checked')} />
                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="submit" className="btn-add btn-large" disabled={savingTeam}>
                                    {Helper.trans('Save')} {savingTeam ? (<i className="fa fa-spin fa-refresh"></i>) : null}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        )
    }

}

