import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './../helper';
import MeItem from './components/me-item';
import CommentModal from './components/comment-modal';
import MeService from './me-service';

export default class MeEdit extends Component {

    constructor(props) {
        super(props);
        let currMonth = pageParams.currMonth;
        if (currMonth) {
            currMonth = moment(currMonth).format('YYYY-MM');
        }
        this.state = {
            currTeamId: pageParams.currTeamId, // current project
            currMonth: currMonth, // current month
            evalTeamList: pageParams.evalTeamList,
            listMonths: pageParams.listEvalTeamMonths,
            rangeTime: {}, // rang baseline time
            attributes: [], // list attributes
            items: [], // list me items
            leaderId: null, // current leader ID of project
            evalPoints: {}, // evaluation attributes point
            originPoints: {},
            avgAttrPoints: {}, // average attribute point
            checkedItems: [], // ticked evaluation items
            attrComments: { // list attribute comments
                data: [],
                next_page_url: null,
            },
            currCommentAttr: { // params of attribute comments
                evalId: null,
                attrId: null,
                commentType: null,
                commentText: '',
            },
            commentClasses: {}, // list comments class of evaluation
            attrsCommented: {}, // list attribute current user commented
            pointMustComment: {},
            loadingMonth: false,
            loadingMember: false,
            timeoutSavePoint: null,
            xhrSavingPoint: null,
            savingPoint: false,
            savedFirst: false,
            showSubmit: false,
            submiting: false,
            loadingComment: false,
            savingComment: false,
            deletingComment: false,
            isNetworkOnline: true,
        }

        let that = this;
        let xhrLoadMembers = null;
        //change project
        $('body').on('change', '#_me_team', function () {
            let teamId = $(this).val();
            that.setState({
                currTeamId: teamId,
            });
            Helper.pushStateUrl({team_id: teamId});
            let {currMonth} = that.state;
            if (currMonth) {
                $('#_me_month').val(currMonth).trigger('change');
            }
        });

        //change month of project
        $('body').on('change', '#_me_month', function () {
            let time = $(this).val();
            let {currTeamId, attributes} = that.state;
            let showSubmit = false;
            that.setState({
                currMonth: time,
            });
            if (!time) {
                return;
            }
            //check month lester than sep date then return old version
            MeService.checkOldVersion(time, currTeamId, 'team');

            if (xhrLoadMembers) {
                xhrLoadMembers.abort();
            }

            let getFields = {};
            if (!attributes || attributes.length < 1) {
                getFields['attributes'] = 1;
            }

            Helper.pushStateUrl({team_id: currTeamId, month: time});
            that.setState({
                loadingMember: true,
                showSubmit: false,
            });
            $.ajax({
                type: 'GET',
                url: pageParams.urlLoadMembersOfTeam,
                data: {
                    team_id: currTeamId,
                    month: time,
                    fields: getFields,
                },
                success: function (response) {
                    let {attributes, items, attrPoints, commentClasses, attrsCommented} = response;

                    //check show submit button
                    for (let i = 0; i < items.length; i++) {
                        let item = items[i];
                        if (!showSubmit && pageParams.sttsShowSubmit.indexOf(parseInt(item.status)) > -1) {
                            showSubmit = true;
                            break;
                        }
                    }
                    //set attr points
                    let evalPoints = MeService.setEvalPoints(attrPoints, that.calSumaryPoint, attributes);
                    let originPoints = MeService.cloneEvalPoints(evalPoints);

                    let dataState = {
                        rangeTime: response.range_time,
                        items: response.items,
                        evalPoints: evalPoints,
                        originPoints: originPoints,
                        avgAttrPoints: that.calAvgAttrPoints(evalPoints, attributes),
                        commentClasses: commentClasses,
                        attrsCommented: attrsCommented,
                        leaderId: response.leaderId,
                        showSubmit: showSubmit,
                    }
                    if (typeof attributes != 'undefined') {
                        dataState.attributes = attributes;
                    }

                    that.setState(dataState);

                    setTimeout(function () {
                        let fixedCols = $('.fixed-table thead tr:first .fixed-col').length;
                        $(".fixed-table").tableHeadFixer({"left" : fixedCols});
                    }, 100);
                },
                error: function (error) {
                    that.setState(that.resetItems());
                    if (error.status != 404) {
                        that.alertError(error);
                    }
                },
                complete: function () {
                    that.setState({
                        loadingMember: false,
                    });
                },
            });
        });

        //checked item
        $('body').on('click', 'table .check-all-items', function () {
            $(this).closest('table').find('.check-item').prop('checked', $(this).is(':checked'));
            let {checkedItems} = that.state;
            $(this).closest('table').find('.check-item').each(function () {
                let id = $(this).val();
                if ($(this).is(':checked')) {
                    checkedItems = Helper.pushUniqueItem(id, checkedItems);
                } else {
                    checkedItems = Helper.removeListItem(id, checkedItems);
                }
            });
            that.setState({checkedItems: checkedItems});
        });

        $('body').on('click', 'table .check-item', function () {
            let table = $(this).closest('table');
            table.find('.check-all-items')
                    .prop('checked', table.find('.check-item:checked').length == table.find('.check-item').length);
            let {checkedItems} = that.state;
            let id = $(this).val();
            if ($(this).is(':checked')) {
                checkedItems = Helper.pushUniqueItem(id, checkedItems);
            } else {
                checkedItems = Helper.removeListItem(id, checkedItems);
            }
            that.setState({checkedItems: checkedItems});
        });

        setTimeout(function () {
            RKfuncion.select2.init();

            if (pageParams.currTeamId) {
                $('#_me_team').val(pageParams.currTeamId).trigger('change');
            }
        }, 100);

        this.handleChangePoint = this.handleChangePoint.bind(this);
        this.handleSubmitMe = this.handleSubmitMe.bind(this);
        this.setMainState = this.setMainState.bind(this);
        this.getMainState = this.getMainState.bind(this);
        this.savePoint = this.savePoint.bind(this);
        this.calSumaryPoint = this.calSumaryPoint.bind(this);
        this.calAvgAttrPoints = this.calAvgAttrPoints.bind(this);
    }

    /*
     * set state
     */
    setMainState(state) {
        this.setState(state);
    }

    /*
     * get state
     */
    getMainState() {
        return this.state;
    }

    resetItems() {
        return {
            evalPoints: {},
            rangeTime: {},
            attributes: [],
            items: [],
            leaderId: null,
            showSubmit: false,
        };
    }

    /*
     * alert error
     */
    alertError(error) {
        Helper.alertResError(error);
    }

    /*
     * change attribute point
     */
    handleChangePoint(e, evalId, attrId) {
        MeService.handleChangePoint(evalId, attrId, e.target.value, this);
    }

    /*
     * check commented attributes
     */
    isCommented(evalId, attrId) {
        let {attrsCommented} = this.state;
        return MeService.isCommented(evalId, attrId, attrsCommented);
    }

    /*
     * caculate sumary point by evaluation ID
     */
    calSumaryPoint(evalId, attrPoints = null, attributes = null) {
        let {
            evalPoints,
        } = this.state;
        if (attributes === null) {
            attributes = this.state.attributes;
        }
        if (!attrPoints) {
            attrPoints = typeof evalPoints[evalId] != 'undefined' ? evalPoints[evalId] : null;
        }
        return MeService.calSumaryPoint(attrPoints, attributes);
    }

    /*
     * caculate average attribute point
     */
    calAvgAttrPoints(evalPoints, attributes = null) {
        if (attributes === null) {
            attributes = this.state.attributes;
        }
        return MeService.calAvgAttrPoints(evalPoints, attributes);
    }

    /*
     * save attribute point
     */
    savePoint() {
        MeService.savePoint(this);
    }

    handleSubmitMe(e) {
        let that = this;
        let {submiting, checkedItems, items, evalPoints} = that.state;
        if (submiting) {
            return;
        }
        if (checkedItems.length < 1) {
            Helper.alertError(Helper.trans('None item checked'));
            return;
        }

        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Confirm submit'),
            callback: function (result) {
                if (result) {
                    that.setState({
                        submiting: true,
                    });

                    $.ajax({
                        type: 'POST',
                        url: pageParams.urlSumitMeTeam,
                        data: {
                            _token: pageParams._token,
                            eval_ids: checkedItems,
                            eval_points: evalPoints,
                        },
                        success: function (response) {
                            if (typeof response.eval_require_comment != 'undefined' && response.eval_require_comment) {
                                let accountMustComments = [];
                                for (let i = 0; i < response.eval_require_comment.length; i++) {
                                    let evalRqId = response.eval_require_comment[i];
                                    accountMustComments.push($('tr[data-eval="'+ evalRqId +'"] ._account_col').text());
                                }
                                Helper.alertError(Helper.trans('You must comment before submiting') + ': <br />' + accountMustComments.join(', '));
                                return;
                            }

                            Helper.alertSuccess(response.message);
                            let resultItems = response.results;
                            let showSubmit = false;
                            for (let i = 0; i < items.length; i++) {
                                let item = items[i];
                                if (typeof resultItems[item.id] != 'undefined') {
                                    item.status = resultItems[item.id].status;
                                    item.can_change_point = resultItems[item.id].can_change;
                                    items[i] = item;
                                }
                                if (!showSubmit && pageParams.sttsShowSubmit.indexOf(parseInt(item.status)) > -1) {
                                    showSubmit = true;
                                }
                            }
                            that.setState({
                               items: items,
                               showSubmit: showSubmit,
                               checkedItems: [],
                            });

                            $('.check-all-items').prop('checked', false);
                        },
                        error: function (error) {
                            that.alertError(error);
                        },
                        complete: function () {
                            that.setState({
                                submiting: false,
                            });
                        },
                    });
                }
            }
        });
    }

    render() {
        let {
            currTeamId,
            currMonth,
            evalTeamList,
            listMonths,
            rangeTime,
            items,
            checkedItems,
            attributes,
            evalPoints,
            originPoints,
            avgAttrPoints,
            attrComments,
            currCommentAttr,
            commentClasses,
            attrsCommented,
            pointMustComment,
            savingPoint,
            savedFirst,
            showSubmit,
            submiting,
            loadingMember,
            loadingComment,
            savingComment,
            deletingComment,
            isNetworkOnline,
        } = this.state;

        return (
            <div>
                <div className="box-body">
                    <div className="row">
                        <div className="col-md-10">
                            <div className="form-inline box-action select-media mgr-35">
                                <select className="form-control select-search has-search" id="_me_team">
                                    <option value="">{Helper.trans('Select team')}</option>
                                    {evalTeamList.length > 0 ? (
                                        <React.Fragment>
                                        {evalTeamList.map((team, teamKey) => (
                                            <option key={teamKey} value={team.value}
                                                dangerouslySetInnerHTML={{__html: team.label}}></option>
                                        ))}
                                        </React.Fragment>
                                    ) : null}
                                </select>
                            </div>
                            <div className="form-inline box-action select-media mgr-35">
                                <select className="form-control select-search" id="_me_month"
                                    disabled={!currTeamId}>
                                    <option value="">{Helper.trans('Select month')}</option>
                                    {listMonths.map((month, keyMonth) => (
                                        <option value={month.timestamp} key={keyMonth}>{month.string}</option>
                                    ))}
                                </select>
                            </div>
                            {Object.keys(rangeTime).length > 0 ? (
                                <div className="form-inline box-action select-media mgr-35">
                                    <span className="month-range-time">
                                        {Helper.trans('Date from') + ': ' + rangeTime.start + ' '+ Helper.trans('to') +': ' + rangeTime.end}
                                    </span>
                                </div>
                            ) : null}

                            {!isNetworkOnline ? (
                                <div className="form-inline box-action mgr-35">
                                    <span className="error" style={{fontWeight: 600}}>{Helper.trans('Please checking network connection!')}</span>
                                </div>
                            ) : (
                            <React.Fragment>
                                {savingPoint ? (
                                    <div className="form-inline box-action select-media">
                                        <i className="page-saving fa fa-spin fa-refresh text-blue"></i>
                                        <i className="text-blue">&nbsp; {Helper.trans('Saving data')}</i>
                                    </div>
                                ) : (
                                    <React.Fragment>
                                    {savedFirst ? (
                                        <span className="text-green">{Helper.trans('Saved data')}</span>
                                    ) : null}
                                    </React.Fragment>
                                )}
                            </React.Fragment>
                            )}
                        </div>
                        <div className="col-md-2 text-right">
                            <a target="_blank" href={pageParams.urlHelpPage} className="btn btn-primary">{Helper.trans('Help')}</a>
                        </div>
                    </div>
                    <div className="text-right"><i>{Helper.trans('Right click to comment')}</i></div>
                </div>

                <MeItem
                    items={items}
                    attributes={attributes}
                    handleChangePoint={this.handleChangePoint}
                    evalPoints={evalPoints}
                    avgAttrPoints={avgAttrPoints}
                    commentClasses={commentClasses}
                    currCommentAttr={currCommentAttr}
                    pointMustComment={pointMustComment}
                    submiting={submiting}
                    loadingMember={loadingMember}
                    currProj={currTeamId}
                    currMonth={currMonth}
                    createForTeam={true}
                />

                {showSubmit ? (
                    <div className="text-center margin-top-20" style={{paddingBottom: '30px'}}>
                        <button type="button" className="btn btn-lg btn-success" disabled={submiting || savingPoint}
                            onClick={(e) => this.handleSubmitMe(e)}>
                            {Helper.trans('Submit')}
                            {submiting ? (
                                <span>&nbsp; <i className="fa fa-spin fa-refresh"></i></span>
                            ) : null}
                       </button>
                    </div>
                ) : null}

                <CommentModal
                    setMainState={this.setMainState}
                    getMainState={this.getMainState}
                    savePoint={this.savePoint}
                    calAvgAttrPoints={this.calAvgAttrPoints}
                    calSumaryPoint={this.calSumaryPoint}
                    attrComments={attrComments}
                    currCommentAttr={currCommentAttr}
                    loadingComment={loadingComment}
                    items={items}
                    attributes={attributes}
                    checkedItems={checkedItems}
                    commentClasses={commentClasses}
                    pointMustComment={pointMustComment}
                    attrsCommented={attrsCommented}
                    evalPoints={evalPoints}
                    originPoints={originPoints}
                    savingComment={savingComment}
                    deletingComment={deletingComment}
                />
            </div>
        )
    }
}

if (document.getElementById('me_team_edit_container')) {
    ReactDOM.render(<MeEdit />, document.getElementById('me_team_edit_container'));
}


