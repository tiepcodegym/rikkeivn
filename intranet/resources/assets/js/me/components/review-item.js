import React, {Component} from 'react';
import Helper from './../../helper.js';
import MeThead from './me-thead';
import MeService from './../me-service';

export default class ReviewItem extends Component {

    constructor(props) {
        super(props);
        this.renderTrFilter = this.renderTrFilter.bind(this);
        this.updateStatus = this.updateStatus.bind(this);
        this.deleteItem = this.deleteItem.bind(this);
        this.multiUpdateStatus = this.multiUpdateStatus.bind(this);
    }

    showCheckbox(status) {
        let aryStatus = [pageParams.STT_SUBMITED, pageParams.STT_CLOSED];
        return aryStatus.indexOf(status) > -1;
    }

    isShowMultiFeedback() {
        let {checkedItems, items} = this.props;
        if (checkedItems.length < 1) {
            return false;
        }
        let isShow = false;
        for (let i = 0; i < checkedItems.length; i++) {
            let id = checkedItems[i];
            let item = Helper.findItemById(id, items.data);
            if (item && MeService.isShowFeedback(item.status)) {
                isShow = true;
                break;
            }
        }
        return isShow;
    }

    isShowMultiApprove() {
        let {checkedItems, items} = this.props;
        if (checkedItems.length < 1) {
            return false;
        }
        let isShow = false;
        for (let i = 0; i < checkedItems.length; i++) {
            let id = checkedItems[i];
            let item = Helper.findItemById(id, items.data);
            if (item && MeService.isShowApprove(item.status)) {
                isShow = true;
                break;
            }
        }
        return isShow;
    }

    /*
     * render filter row
     */
    renderTrFilter() {
        let {
            attributes,
            currEmployee,
            currProject,
        } = this.props;
        let notShowFilterStatus = false;
        let meTbl = pageParams.meTbl;

        return (
            <tr className="tr-filter">
                <td className="fixed-col"></td>
                <td className="fixed-col"></td>
                <td className="td_filter_months fixed-col">
                    <input type="text" name="filter[excerpt][month]"
                        className="form-control filter-date month-picker" placeholder="Y-m"
                        data-key="excerpt" data-key2="month" data-refresh="1"
                        style={{minWidth: '75px'}} autoComplete="off"
                        data-format="yyyy-mm"
                        data-options='{"viewMode":"months","minViewMode":"months","autoclose":true,"clearBtn":true,"useCurrent":false}'/>
                </td>
                <td className="td_filter_employees fixed-col">
                    <select className="form-control select-search filter-field"
                        data-key="number" data-key2={meTbl + '.employee_id'} data-refresh="1"
                        data-remote-url={pageParams.urlSearchEmployee}
                        data-placeholder={Helper.trans('Select employee')}
                        data-options={'{"allowClear":true,"placeholder":"'+ Helper.trans('Select employee') +'"}'}
                        style={{minWidth: '130px'}}>
                        <option value="">{Helper.trans('Select employee')}</option>
                        {currEmployee ? (
                            <option value={currEmployee.id}>{Helper.getNickName(currEmployee.email)}</option>
                        ) : null}
                    </select>
                </td>
                <td>
                    <select id="filter_projects" className="form-control select-search filter-field"
                        data-key="excerpt" data-key2="project_id" data-refresh="1"
                        data-remote-url={pageParams.urlSearchProject}
                        data-placeholder={Helper.trans('Select project')}
                        data-options={'{"allowClear":true,"placeholder":"'+ Helper.trans('Select project') +'"}'}
                        style={{minWidth: '160px'}}>
                        <option value="">{Helper.trans('Select project')}</option>
                        {currProject ? (
                            <option value={currProject.id}>{currProject.name}</option>        
                        ) : null}
                    </select>
                </td>
                <td>
                    <select id="filter_project_types" className="form-control select-search filter-field"
                        data-key="excerpt" data-key2="proj_type" data-refresh="1"
                        style={{minWidth: '110px'}}>
                        <option value="">--{Helper.trans('Project type')}--</option>
                        <option value="_team_">{Helper.trans('Team')}</option>
                        {Object.keys(pageParams.listProjTypes).map((projType, keyIdx) => (
                            <option key={keyIdx} value={projType}>{pageParams.listProjTypes[projType]}</option>
                        ))}
                    </select>
                </td>
                {attributes.map((attr, attrKey) => (
                    <td key={attrKey}></td>
                ))}
                <td></td>
                <td></td>
                <td>
                    <select className="form-control select-search filter-field"
                        data-key="excerpt" data-key2="avg_point" data-refresh="1"
                        style={{minWidth: '100px'}}>
                        <option value="">--{Helper.trans('Level')}--</option>
                        {Object.keys(pageParams.listContriLabels).map((cVal, keyIdx) => (
                            <option key={keyIdx} value={cVal}>{pageParams.listContriLabels[cVal]}</option>
                        ))}
                    </select>
                </td>
                <td></td>
                <td>
                    {!notShowFilterStatus ? (
                        <select className="form-control select-search filter-field"
                            data-key={meTbl + '.status'} data-refresh="1"
                            style={{minWidth: '110px'}}>
                            <option value="">--{Helper.trans('Status')}--</option>
                            {Object.keys(pageParams.listFilterStatuses).map((sttVal, sttIdx) => (
                                <option key={sttIdx} value={sttVal}>{pageParams.listStatuses[sttVal]}</option>
                            ))}
                        </select>
                    ) : null}
                </td>
                <td></td>
            </tr>
        )
    }

    getMessageConfirm(status) {
        if (status == pageParams.STT_FEEDBACK) {
            return Helper.trans('confirm_feedback');
        }
        if (status == pageParams.STT_APPROVED) {
            return Helper.trans('confirm_approve');
        }
        return Helper.trans('Confirm submit');
    }

    /*
     * update item status
     */
    updateStatus(id, status, e = null) {
        if (e) {
            e.preventDefault();
        }

        MeService.updateStatus(id, status, 'leader', this);
    }

    /*
     * update multiple items
     */
    multiUpdateStatus(status, e = null) {
        if (e) {
            e.preventDefault();
        }
        let {
            updatingMulti,
            checkedItems,
            items,
            setMainState,
        } = this.props;
        if (updatingMulti || checkedItems.length < 1) {
            return;
        }

        let that = this;
        bootbox.confirm({
            className: 'modal-default',
            message: that.getMessageConfirm(status),
            callback: function (result) {
                if (result) {
                    setMainState({
                        updatingMulti: true,
                    });
                    $.ajax({
                        type: 'POST',
                        url: pageParams.urlMultiUpdatestatus,
                        data: {
                            _token: pageParams._token,
                            eval_ids: checkedItems,
                            action: status,
                        },
                        success: function (res) {
                            Helper.alertSuccess(res);
                            for (let i = 0; i < checkedItems.length; i++) {
                                let id = checkedItems[i];
                                let index = Helper.findIndexById(id, items.data);
                                if (index > -1) {
                                    let item = items.data[index];
                                    item.status = status;
                                    item.is_leader_updated = pageParams.LEADER_UPDATED;
                                    items.data[index] = item;
                                }
                            }
                            setMainState({
                                items: items,
                                checkedItems: checkedItems,
                            });
                        },
                        error: function (error) {
                            Helper.alertResError(error);
                        },
                        complete: function () {
                            setMainState({
                                updatingMulti: false,
                            });
                        },
                    });
                }
            },
        });
    }

    /*
     * delete item
     */
    deleteItem(id, e = null) {
        if (e) {
            e.preventDefault();
        }
        let {
            setMainState,
            items,
            updatingStatus
        } = this.props;

        if (updatingStatus) {
            return;
        }

        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Are you sure want to remove item?'),
            callback: function (result) {
                if (result) {
                    setMainState({
                        updatingStatus: true,
                        currUpdateItem: id,
                    });
                    $.ajax({
                        type: 'DELETE',
                        url: pageParams.urlDeleteItem,
                        data: {
                            _token: pageParams._token,
                            id: id,
                        },
                        success: function (res) {
                            if (res.success == 0) {
                                Helper.alertError(res.message);
                                return;
                            }

                            let itemIdx = Helper.findIndexById(id, items.data);
                            if (itemIdx > -1) {
                                items.data.splice(itemIdx, 1);
                            }
                            Helper.alertSuccess(res.message);
                            setMainState({
                                currUpdateItem: null,
                                items: items,
                            });
                        },
                        error: function (error) {
                            Helper.alertResError(error);
                        },
                        complete: function () {
                            setMainState({
                                updatingStatus: false,
                            });
                        },
                    });
                }
            }
        });
    }

    componentDidMount() {

    }

    render() {
        let {
            attributes,
            items,
            listRangeMonths,
            commentClasses,
            evalPoints,
            hasPermissDelete,
            loadingData,
            currUpdateItem,
            updatingStatus,
            updatingMulti,
            setFilterData,
            getFilterData,
        } = this.props;

        return (
            <React.Fragment>
            <div className="pdh-10">
                <div className="table-responsive _me_table_responsive fixed-table-container">
                    <table id="_me_table" require-comment="1" className="fixed-table table dataTable table-striped table-bordered table-hover table-grid-data table-th-middle">

                        <MeThead
                            checkbox={true}
                            staffView={false}
                            hasMonth={true}
                            createTeam={false}
                            actionCol={true}
                            sortContri={true}
                            setFilterData={setFilterData}
                            getFilterData={getFilterData}
                            isLeaderReview={true}
                            isReviewTeam={false}
                            attributes={attributes}
                            trFilter={this.renderTrFilter}
                        />

                        <tbody>
                            {loadingData ? (
                                <tr>
                                    <td colSpan={attributes.length + 14}>
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
                                    <tr key={keyItem} data-eval={item.id} data-project={item.project_id} data-email={item.email} data-time={item.eval_time}
                                        className={currUpdateItem == item.id && updatingStatus ? 'processing' : ''}>
                                        <td className="fixed-col text-center">
                                            {this.showCheckbox(item.status) ? (
                                                <input type="checkbox" className="check-item" value={item.id} />
                                            ) : null}
                                        </td>
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
                                        <td className="dropdown _nowwrap">
                                            {MeService.isShowFeedback(item.status) ? (
                                                <button type="button" className="btn-delete _btn_feedback"
                                                    disabled={currUpdateItem == item.id && updatingStatus}
                                                    onClick={(e) => this.updateStatus(item.id, pageParams.STT_FEEDBACK, e)}>{Helper.trans('Feedback')}</button>
                                            ) : null}
                                            {MeService.isShowApprove(item.status) ? (
                                                <button type="button" className="btn-add _btn_accept mrl-5"
                                                    disabled={currUpdateItem == item.id && updatingStatus}
                                                    onClick={(e) => this.updateStatus(item.id, pageParams.STT_APPROVED, e)}>{Helper.trans('Approve')}</button>
                                            ) : null}

                                            {hasPermissDelete ? (
                                                <button data-url="delete_item" type="button"
                                                    disabled={currUpdateItem == item.id && updatingStatus}
                                                    onClick={(e) => this.deleteItem(item.id, e)}
                                                    className="btn btn-danger mrl-5"><i className="fa fa-trash"></i></button>
                                            ) : null}
                                        </td>
                                    </tr>
                                )
                                })}
                                </React.Fragment>
                            ) : (
                                <tr>
                                    <td colSpan="4"></td>
                                    <td colSpan={attributes.length + 8}><h4>{Helper.trans('No result')}</h4></td>
                                </tr>
                            )}
                            </React.Fragment>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="box-body text-right hide-filter">
                <button type="button" className="btn btn-info show-modal-list-cm"
                    data-toggle="modal"
                    data-target="#modalListComment"
                    disabled={!this.isShowMultiFeedback() || updatingMulti}
                >
                    {Helper.trans('Bulk Comment')}
                </button>
                <button type="button" className="btn btn-danger btn_form_feedback mrl-5"
                    disabled={!this.isShowMultiFeedback() || updatingMulti}
                    onClick={(e) => this.multiUpdateStatus(pageParams.STT_FEEDBACK, e)}>
                    {Helper.trans('Feedback')}
                </button>
                <button type="button" className="btn btn-primary btn_form_accept mrl-5"
                    disabled={!this.isShowMultiApprove() || updatingMulti}
                    onClick={(e) => this.multiUpdateStatus(pageParams.STT_APPROVED, e)}>
                    {Helper.trans('Approve')}
                </button>
            </div>
            </React.Fragment>
        )
    }
}


