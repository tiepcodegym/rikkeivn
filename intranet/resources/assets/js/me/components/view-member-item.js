import React, {Component} from 'react';
import Helper from './../../helper.js';
import MeThead from './me-thead';
import MeService from './../me-service';

export default class ViewMemberItem extends Component {

    constructor(props) {
        super(props);
    }

    render() {
        let {
            attributes,
            items,
            listRangeMonths,
            commentClasses,
            evalPoints,
            loadingData,
            setFilterData,
            getFilterData,
        } = this.props;

        return (
            <div className="pdh-10">
                <div className="table-responsive _me_table_responsive fixed-table-container">
                    <table id="_me_table" require-comment="1" className="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">

                        <MeThead
                            checkbox={false}
                            staffView={false}
                            hasMonth={true}
                            createTeam={false}
                            actionCol={false}
                            sortContri={true}
                            isLeaderReview={false}
                            isReviewTeam={true}
                            attributes={attributes}
                            setFilterData={setFilterData}
                            getFilterData={getFilterData}
                        />

                        <tbody>
                            {loadingData ? (
                                <tr>
                                    <td colSpan={attributes.length + 12}>
                                        <h5 className="text-center"><i className="fa fa-spin fa-refresh"></i></h5>
                                    </td>
                                </tr>
                            ) : (
                            <React.Fragment>
                            {items.data.length > 0 ? (
                                <React.Fragment>
                                {items.data.map((item, keyItem) => {
                                    let itemCommentClasses = typeof commentClasses[item.id] != 'undefined' ? commentClasses[item.id] : {};

                                    return (
                                    <tr key={keyItem} data-eval={item.id} data-project={item.project_id} data-email={item.email} data-time={item.eval_time}>
                                        <td className="_break_word fixed-col _nowwrap">{item.employee_code}</td>
                                        <td className={'_nowwrap fixed-col date-tooltip' + MeService.renderOldMonthClass(item.eval_month)}>
                                            {Helper.getItemMonth(item.eval_time)}
                                            {typeof listRangeMonths[item.eval_month] != 'undefined' ? (
                                                <span>&nbsp;<i data-toggle="tooltip" data-placement="right" className="fa fa-question-circle"
                                                    title={listRangeMonths[item.eval_month]['start'] + ' : ' + listRangeMonths[item.eval_month]['end']}></i></span>
                                            ) : null}
                                        </td>
                                        <td className="_break_word fixed-col employee">{Helper.getNickName(item.email)}</td>
                                        <td>
                                            {item.project_id ? (
                                                <a href={pageParams.urlProjPoint + '/' + item.project_id} target="_blank" className="project_code_auto">{item.project_name}</a>
                                            ) : (
                                                <span>{item.team_name}</span>
                                            )}
                                        </td>
                                        <td>{MeService.renderProjTypeLabel(item.project_type)}</td>
                                        {attributes.length > 0 ? (
                                            <React.Fragment>
                                            {attributes.map((attr, attrKey) => (
                                                <React.Fragment key={attrKey}>
                                                    {MeService.renderAttrCell(item, attr, itemCommentClasses, this)}
                                                </React.Fragment>
                                            ))}
                                            </React.Fragment>
                                        ) : null}
                                        <td className="auto_fill num-val"><strong>{item.avg_point}</strong></td>
                                        <td className="auto_fill num-val">{item.effort}</td>
                                        <td className={'_contribute_val _break_word auto_fill' + MeService.renderOldMonthClass(item.eval_month)}>
                                            {MeService.renderContriLabel(item.id, evalPoints, item.avg_point, item.eval_month)}
                                        </td>
                                        <td className={'note_group ' + (typeof itemCommentClasses[-1] != 'undefined' ? itemCommentClasses[-1].join(' ') : '')}></td>
                                        <td className={'_break_word auto_fill status_label' + MeService.getStatusClass(item.status)}>
                                            {MeService.renderStatusLabel(item.status, item.is_leader_updated, item.status_label)}
                                        </td>
                                    </tr>
                                )
                                })}
                                </React.Fragment>
                            ) : (
                                <tr>
                                    <td colSpan="3"></td>
                                    <td colSpan={attributes.length + 7}><h4>{Helper.trans('No result')}</h4></td>
                                </tr>
                            )}
                            </React.Fragment>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        )
    }
}
