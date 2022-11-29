import React, {Fragment, Component} from 'react';
import Helper from './../../react-helper';

export default class RuleList extends Component {

    findScope(collection, actionId, roleId) {
        if (typeof collection['a-' + actionId + '-r-' + roleId] != 'undefined') {
            return collection['a-' + actionId + '-r-' + roleId].scope;
        }
        return 0;
    }

    findScopeTeamIds(collection, actionId, roleId) {
        if (typeof collection['a-' + actionId + '-r-' + roleId] != 'undefined') {
            let strScope = collection['a-' + actionId + '-r-' + roleId].scope_team_ids;
            if (!strScope) {
                return null;
            }
            return JSON.parse(strScope);
        }
        return null;
    }

    getScopeIcon(scopeIcons, scope) {
        if (typeof scopeIcons[scope] != 'undefined') {
            return scopeIcons[scope];
        }
        return scopeIcons[0];
    }

    draftChangeTeamScope(e, actionId) {
        
    }

    componentDidMount() {
        let {onChangeScopeTeam} = this.props;
        RKfuncion.bootstapMultiSelect.init({
            numberDisplayed: 2,
            onChangeFunc: onChangeScopeTeam,
        });
        $('.select-scope-box').removeClass('hidden');
        $('.loading-scope-team').remove();
    }

    render() {
        let {
            acl,
            type,
            rolesPosition,
            teamPermissions,
            transAcl,
            guides,
            scopeIcons,
            scopeOptions,
            changeScope,
            savePermission,
            saving,
            teamOptions,
            roleModel,
        } = this.props;

        return(
            <form action="" method="post"
                onSubmit={(e) => savePermission(e)}>
                <div className="rule-noti"
                    dangerouslySetInnerHTML={{__html: teamParams.scopeIconGuide}}>
                </div>

                <div className="actions">
                    <button type="submit" className="btn-add btn-large">
                        <span>{Helper.trans('Save')}</span>
                        {saving ? (
                            <span>
                                <span>&nbsp;</span>
                                <span className="fa fa-spin fa-refresh"></span>
                            </span>
                        ) : null}
                    </button>
                </div>
                <div className="table-responsive">
                    <table className="table table-bordered table-striped table-team-rule">
                        <thead>
                            <tr>
                                <th className="col-screen">{Helper.trans('Screen')}</th>
                                <th className="col-function">{Helper.trans('Function')}</th>
                                {rolesPosition.map((role, index) => (
                                    <th className="col-team" key={index}>{type == 'team' ? role.role : Helper.trans('Permission')}</th>
                                ))}
                                {type == 'role' ? (
                                    <th className="team">{Helper.trans('Scope apply')}</th>
                                ) : null}
                            </tr>
                        </thead>
                        {!acl ? (
                            <tr className="alert alert-warning">
                                <td colspan={rolesPosition.length + (type == 'role' ? 3 : 2)}>{Helper.trans('team::view.Not found function')}</td>
                            </tr>
                        ) : (
                            <tbody>
                            {Object.keys(acl).map((aclKey, aclIdx) => {
                                let aclValue = acl[aclKey];
                                return (
                                <React.Fragment key={aclIdx}>
                                    {aclValue.child ? (
                                        <React.Fragment>
                                            <tr key={'parent' + aclIdx} className="tr-col-screen">
                                                <td className="col-screen">
                                                    {typeof transAcl[aclValue.description] != 'undefined' ? (
                                                        <span>{transAcl[aclValue.description]}</span>
                                                    ) : (
                                                        <span>{aclValue.description}</span>
                                                    )}
                                                </td>
                                                <td>&nbsp;</td>
                                                {rolesPosition.map((role, roleIndex) => (
                                                    <td key={roleIndex}>&nbsp;</td>
                                                ))}
                                                {type == 'role' ? (
                                                    <td>&nbsp;</td>
                                                ) : null}
                                            </tr>

                                            {Object.keys(aclValue.child).map((aclItemKey, aclItemIdx) => {
                                                let aclItem = aclValue.child[aclItemKey];
                                                let scopeTeamIds = [];
                                                let roleScopeVal = 0;
                                                if (type == 'role') {
                                                    scopeTeamIds = this.findScopeTeamIds(teamPermissions, aclItemKey, roleModel.id);
                                                    scopeTeamIds = !scopeTeamIds ? [] : scopeTeamIds;
                                                }
                                                return (
                                                <tr key={aclItemKey} data-action={aclItemKey}>
                                                    <td className="col-screen-empty">
                                                        {typeof aclItem.name != 'undefined' && typeof guides[aclItem.name] != 'undefined' ? (
                                                        <a className="acl-guide">
                                                            <i className="fa fa-question-circle acl-guide-icon col-hover-tooltip">
                                                                <div className="hidden tooltip-content">
                                                                    <div className="help-acl"
                                                                        dangerouslySetInnerHTML={{__html: guides[aclItem.name]}}>
                                                                    </div>
                                                                </div>
                                                            </i>
                                                        </a>
                                                        ) : null}
                                                    </td> 
                                                    <td>
                                                        {typeof transAcl[aclItem.description] != 'undefined' && transAcl[aclItem.description].trim() ? (
                                                            <span>{transAcl[aclItem.description]}</span>
                                                        ) : (
                                                            <span>{aclItem.description}</span>
                                                        )}
                                                    </td>
                                                    {rolesPosition.map((role, keyRole) => {
                                                        let scopeVal = this.findScope(teamPermissions, aclItemKey, role.id);
                                                        roleScopeVal = scopeVal;
                                                        return (
                                                        <td key={keyRole} className="col-team form-drop-wrapper">
                                                            <div className="btn-group form-input-dropdown">
                                                                <button type="button" className="btn btn-default dropdown-toggle input-show-data" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span dangerouslySetInnerHTML={{__html: this.getScopeIcon(scopeIcons, scopeVal)}}></span>
                                                                </button>
                                                                <ul className="dropdown-menu input-menu">
                                                                    {scopeOptions.map((option, keyOption) => (
                                                                        <li key={keyOption}>
                                                                            <a href="#" data-value={option.value}
                                                                                dangerouslySetInnerHTML={{__html: option.label}}
                                                                                onClick={(e) => changeScope(e, aclItemKey, role.id, option.value)}></a>
                                                                        </li>
                                                                    ))}
                                                                </ul>
                                                            </div>
                                                        </td>
                                                        )
                                                    })}
                                                    {type == 'role' ? (
                                                        <td className="col-scope-apply text-center">
                                                            <div className="select-scope-box hidden">
                                                                <select onChange={(e) => this.draftChangeTeamScope(e, aclItemKey)}
                                                                        data-role={roleModel.id} defaultValue={scopeTeamIds}
                                                                        disabled={roleScopeVal != teamParams.scopeTeam}
                                                                        className="hidden form-control bootstrap-multiselect select-team-scope" multiple>
                                                                    {teamOptions.map((teamOpt, keyTeamOpt) => {
                                                                        return (
                                                                            <option value={teamOpt.value} key={keyTeamOpt}
                                                                                dangerouslySetInnerHTML={{__html: teamOpt.label}}
                                                                                data-parent={teamOpt.parent_id}></option>
                                                                        )
                                                                     })}
                                                                </select>
                                                            </div>
                                                            <span className="fa fa-spin fa-refresh loading-scope-team"></span>
                                                        </td>
                                                    ) : null}
                                                </tr>
                                                )
                                            })}
                                        </React.Fragment>
                                    ) : null}
                                </React.Fragment>
                                )
                            })}
                            </tbody>
                        )}
                    </table>
                </div>

                <div className="actions">
                    <button type="submit" className="btn-add btn-large">
                        <span>{Helper.trans('Save')}</span>
                        {saving ? (
                            <span>
                                <span>&nbsp;</span>
                                <span className="fa fa-spin fa-refresh"></span>
                            </span>
                        ) : null}
                    </button>
                </div>
            </form>
        )
    }
}
