import React, {Component} from 'react';
import Helper from './../../helper';
import MeTHead from './me-thead';
import MeTFoot from './me-tfoot';
import MeService from './../me-service';

export default class MeItem extends Component {

    constructor(props) {
        super(props);
    }

    /*
     * render attirubte point view/edit
     */
    renderAttrCell(evalItem, attr) {
        let {
            handleChangePoint,
            evalPoints,
            submiting,
            pointMustComment,
            currCommentAttr,
        } = this.props;
        let canChangePoint = evalItem.can_change_point;
        let attrPoint = MeService.getPoint(evalPoints, evalItem.id, attr.id);
        let isCommenting = !(evalItem.id == currCommentAttr.evalId && attr.id == currCommentAttr.attrId)
                && Object.keys(pointMustComment).length > 0;
        return (
            <div className="input_select">
                <input type="number" data-value={attrPoint} disabled={submiting || !canChangePoint || isCommenting}
                    onChange={(e) => handleChangePoint(e, evalItem.id, attr.id)}
                    className="form-control _round_value _me_attr_point" value={attrPoint}
                    autoComplete="off" data-attr={attr.id} data-weight={attr.weight}
                    min={attr.range_min} max={attr.range_max} step={attr.range_step} style={{padding: '6px'}} />
            </div>
        )
    }

    renderArrProjNames(strProjNames) {
        let arrProjNames = typeof strProjNames != 'undefined'
            ? strProjNames.split(',').slice(0, 15) : null;
        if (!arrProjNames) {
            return '';
        }
        return arrProjNames.join(', ');
    }

    render() {
        let {
            items,
            attributes,
            evalPoints,
            avgAttrPoints,
            commentClasses,
            loadingMember,
            currProj,
            currMonth,
            createForTeam,
        } = this.props;

        if (loadingMember) {
            return (<h4 className="text-center" style={{paddingBottom: '30px'}}><i className="fa fa-spin fa-refresh"></i></h4>)
        } else {
            return (
                <React.Fragment>
                {items.length > 0 ? (
                <div className="pdh-10">
                    <div className="table-responsive _me_table_responsive fixed-table-container" style={{overflow: 'auto'}}>
                        <table id="_me_table" className="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">
                            <MeTHead
                                checkbox={true}
                                staffView={false}
                                hasMonth={false}
                                createTeam={createForTeam}
                                isLeaderReview={false}
                                isReviewTeam={false}
                                attributes={attributes}
                                sortContri={false}
                                actionCol={false}
                            />

                            <tbody>
                                {items.map((item, key) => {
                                    let canChangePoint = item.can_change_point;
                                    let itemCommentClasses = typeof commentClasses[item.id] != 'undefined' ? commentClasses[item.id] : {};
                                    return (
                                        <tr key={key} data-eval={item.id} data-project={item.project_id} data-time={item.eval_time} data-edit={canChangePoint}>
                                            <td className="fixed-col text-center">
                                                {canChangePoint ? (
                                                    <input type="checkbox" className="check-item" value={item.id} />
                                                ) : null}
                                            </td>
                                            <td className="_employee_id _break_word fixed-col">{item.emp_code}</td>
                                            <td className="_break_word _account_col fixed-col">{Helper.getNickName(item.emp_email)}</td>
                                            {!createForTeam ? (
                                            <td className="minw-100"><a href={pageParams.urlProjPoint + '/' + item.project_id} target="_blank">{item.proj_name}</a></td>
                                            ) : null}
                                            {attributes.length > 0 ? (
                                                <React.Fragment>
                                                {attributes.map((attr, attrKey) => {
                                                    let attrClass = typeof itemCommentClasses[attr.id] != 'undefined' ? itemCommentClasses[attr.id].join(' ') : '';
                                                    return (
                                                        <td key={attrKey} className={'point_group ' + attrClass} data-group={attr.group} data-attr={attr.id}>
                                                            {this.renderAttrCell(item, attr)}
                                                        </td>
                                                    )
                                                })}
                                                </React.Fragment>
                                            ) : null}
                                            <td className="_point_avg auto_fill">
                                                <strong className="_value">{typeof evalPoints[item.id] == 'undefined' ? 0 : evalPoints[item.id].sumary}</strong>
                                            </td>
                                            <td className="auto_fill">
                                                {item.team_id ? (
                                                <span data-toggle="tooltip" title={this.renderArrProjNames(item.arr_proj_name)} data-html="true">{item.effort}</span>
                                                ) : (
                                                <span>{item.effort}</span>
                                                )}
                                            </td>
                                            <td className="_contribute_val _break_word auto_fill">
                                                {MeService.renderContriLabel(item.id, evalPoints)}
                                            </td>
                                            <td className={'note_group ' + (typeof itemCommentClasses[-1] != 'undefined' ? itemCommentClasses[-1].join(' ') : '')}></td>
                                            <td className={'_break_word _status_text auto_fill' + MeService.getStatusClass(item.status)}>
                                                {MeService.renderStatusLabel(item.status)}
                                            </td>
                                        </tr>
                                    )
                                })}
                            </tbody>

                            <MeTFoot
                                createTeam={createForTeam}
                                attributes={attributes}
                                avgAttrPoints={avgAttrPoints}
                            />
                        </table>
                    </div>
                    <div className="box-body text-center">{MeService.renderEditStatistics(items, evalPoints)}</div>
                </div>
                ) : (
                <React.Fragment>
                    {currProj && currMonth ? (
                    <h4 className="text-center">{Helper.trans('No result')}</h4>
                    ) : (
                    <h4 className="text-center">
                        {!createForTeam ? Helper.trans('Please select project and month') : Helper.trans('Please select team and month')}
                    </h4>
                    )}
                </React.Fragment>
                )}
                <div className="box-body"></div>
                </React.Fragment>
            )
        }
    }

}


