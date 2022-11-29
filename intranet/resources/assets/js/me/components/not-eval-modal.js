import React, {Component} from 'react';
import Helper from './../../helper.js';

export default class NotEvalModal extends Component {
    render() {
        let {
            projNotEval,
            filterMonth,
            loadingProjNotEval,
        } = this.props;
        let projNotEvalKeys = Object.keys(projNotEval);

        return (
            <div className="modal fade" id="notEvaluate" tabIndex="-1">
                <div className="modal-dialog modal-lg" role="document">
                    <div className="modal-content">
                       <div className="modal-header">
                            <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 className="modal-title text-center">{Helper.trans('Not Evaluate', {month: filterMonth})}</h4>
                        </div>
                        <div className="modal-body">
                            {!loadingProjNotEval ? (
                            <div className="table-responsive">
                                <table className="edit-table table table-bordered table-condensed dataTable" id="table-not-eval">
                                    <thead>
                                        <tr>
                                            <th className="width-20-per-im">{Helper.trans('Project code')}</th>
                                            <th className="width-30-per-im">{Helper.trans('Project')}</th>
                                            <th className="width-50-per-im">{Helper.trans('Member')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {projNotEvalKeys.length > 0 ? (
                                            <React.Fragment>
                                            {projNotEvalKeys.map((projId, keyIndex) => {
                                                let memberList = projNotEval[projId];
                                                let firstItem = memberList[0];
                                                return (
                                                <tr key={keyIndex}>
                                                    <td><a href={pageParams.urlProjPoint + '/' + firstItem.project_id} target="_blank">{firstItem.project_code_auto}</a></td>
                                                    <td>
                                                        <div>{Helper.trans('Project name')}: <span>{firstItem.proj_name}</span></div>
                                                        <div>{Helper.trans('Project Manager')}: <span>{Helper.getNickName(firstItem.pm_email)}</span></div>
                                                        <div>{Helper.trans('Group')}: <span>{firstItem.group_names}</span></div>
                                                    </td>
                                                    <td>
                                                        <div className="box box-info collapsed-box box-solid" style={{marginBottom: '0px'}}>
                                                            <div className="box-header with-border">
                                                                <h3 className="box-title margin-right-30 proj-name nowrap"
                                                                    style={{fontSize: '14px', whiteSpace: 'nowrap', paddingRight: '25px'}}>{firstItem.proj_name} ({memberList.length})</h3>
                                                                {memberList.length > 0 ? (
                                                                <div className="box-tools pull-right">
                                                                    <button type="button" className="btn btn-box-tool" data-widget="collapse"><i className="fa fa-plus"></i></button>
                                                                </div>
                                                                ) : null}
                                                            </div>
                                                            <div className="box-body members-list" style={{display: 'none'}}>
                                                                {memberList.length > 0 ? (
                                                                    <ul style={{paddingLeft: '15px'}}>
                                                                    {memberList.map((mbItem, mbKey) => (
                                                                        <li key={mbKey} className="member-not-eval white-space-nowrap">{mbItem.emp_name + ' - ' + mbItem.emp_email}</li>
                                                                    ))}
                                                                    </ul>
                                                                ) : null }
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                )
                                            })}
                                            </React.Fragment>
                                        ) : (
                                        <tr>
                                            <td colSpan="3"><h5 className="text-center">{Helper.trans('No result')}</h5></td>
                                        </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                            ) : (
                                <div className="text-center"><i className="fa fa-spin fa-refresh"></i></div>
                            )}
                        </div>
                        <div className="modal-footer text-center">
                            <button type="button" className="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

