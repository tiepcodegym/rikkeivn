import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './../helper';
import ReviewItem from './components/review-item';
import NotEvalModal from './components/not-eval-modal';
import MeService from './me-service';
import Pager from './../pager';
import CommentModal from './components/comment-modal';
import ModalCommentBulk from './components/comment-list-modal';

export default class MeReview extends Component {

    constructor(props) {
        super(props);

        let filterData = Helper.session.getRawItem('filterData', 'me_review');
        if (!filterData) {
            filterData = {
                page: 1,
                per_page: 50,
                orderby: {
                    eval_time: 'desc',
                    project_id: 'desc',
                },
                filter: {},
            }
        }
        if (typeof filterData.filter.excerpt == 'undefined') {
            filterData.filter.excerpt = {};
        }
        filterData.filter.excerpt.from_month = pageParams.SEP_MONTH;
        if (pageParams.currProjId) {
            filterData.filter.excerpt.project_id = pageParams.currProjId;
        }
        if (pageParams.currMonth) {
            let parseMonth = moment(pageParams.currMonth);
            let currMonth = pageParams.currMonth;
            if (parseMonth.isValid()) {
                MeService.checkOldVersion(parseMonth.format('YYYY-MM'), pageParams.currProjId);
                currMonth = parseMonth.format('YYYY-MM');
            } else {
                MeService.checkOldVersion(currMonth, pageParams.currProjId);
            }
            filterData.filter.excerpt.month = currMonth;
        }

        this.state = {
            searching: false,
            attempt: 0,
            attributes: [],
            filterData: filterData,
            evalPoints: {},
            items: {
                data: [],
            },
            checkedItems: [],
            statistics: {},
            listRangeMonths: {},
            commentClasses: {},
            attrsCommented: {},
            filterTeams: [], //list team
            hasPermissDelete: false,
            currEmployee: null, //filter employee
            currProject: null, //filter project or team
            attrComments: { //attribute comment list
                data: [],
            },
            currCommentAttr: { // params of attribute comments
                evalId: null,
                attrId: null,
                commentType: null,
                commentText: '',
                isReviewPage: true,
            },
            totalMember: '',
            projNotEval: [],
            xhrLoadProjNotEval: null,
            loadingProjNotEval: false,
            loadingData: false,
            xhrLoadingData: null,
            inited: false,
            loadingComment: false,
            savingComment: false,
            deletingComment: false,
            updatingStatus: false,
            currUpdateItem: null,
            updatingMulti: false,
            firstDateChange: false,
        };

        let that = this;

        $('body').on('change', '.filter-field', function () {
            that.filterFieldChange($(this));
        });

        $('body').on('change dp.change', '.filter-date', function (e) {
            let {firstDateChange} = that.state;
            if (!firstDateChange) {
                that.setState({firstDateChange: true});
                return;
            }
            that.filterFieldChange($(this), true);
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

        this.getCollection = this.getCollection.bind(this);
        this.setFilterData = this.setFilterData.bind(this);
        this.getFilterData = this.getFilterData.bind(this);
        this.keyPressFilter = this.keyPressFilter.bind(this);
        this.resetFilter = this.resetFilter.bind(this);
        this.initReview = this.initReview.bind(this);
        this.initFilterData = this.initFilterData.bind(this);
        this.getCollection = this.getCollection.bind(this);
        this.filterFieldChange = this.filterFieldChange.bind(this);
        this.setMainState = this.setMainState.bind(this);
        this.getMainState = this.getMainState.bind(this);
        this.showProjNotEval = this.showProjNotEval.bind(this);
    }

    componentDidMount() {
        this.initFilterData();
        this.initFilterData($('.filter-date'));
        RKfuncion.select2.init();
    }

    setMainState(state) {
        this.setState(state);
    }

    getMainState() {
        return this.state;
    }

    resetFilter(e) {
        e.preventDefault();
        let data = {
            page: 1,
            per_page: 50,
            filter: {},
        };
        this.setState({
            filterData: data
        });

        Helper.session.removeItem('filterData', 'me_review');
        let location = window.location;
        window.history.pushState({}, document.title, location.origin + location.pathname);

        this.getCollection(data);
    }

    initReview() {
        this.getCollection(null, true);
    }

    /*
     * triggle change filter field
     */
    filterFieldChange(elThis, checkChange = false) {
        let key = elThis.attr('data-key');
        let key2 = elThis.attr('data-key2') || null;
        let refresh = elThis.attr('data-refresh') || 0;
        let value = elThis.val();
        let params = ['filter', key];
        if (key2) {
            params.push(key2);
        }
        if (key2 == 'month' && value) {
            MeService.checkOldVersion(value, this.getFilterData(['filter', 'excerpt', 'project_id']));
        }

        let {inited} = this.state;
        //check change data
        if (checkChange) {
            let oldVal = this.getFilterData(params);
            if (oldVal == value) {
                return false;
            }
        }
        this.setFilterData(params, value);

        if (refresh == 1 || (refresh == 2 && inited)) {
            this.getCollection();
        }
    }

    /*
     * init select filter data
     */
    initFilterData(elSelect = null) {
        elSelect = elSelect ? elSelect : $('.filter-field');
        let that = this;
        Helper.initFilterData(elSelect, that);
    }

    /*
     * set filter collection data
     */
    setFilterData(params = ['filter'], value, refresh = false) {
        //key, value, refresh, that, prefix
        Helper.setFilterData(params, value, refresh, this, 'me_review');
    }

    /*
     * get filter collection data
     */
    getFilterData(keys = ['filter']) {
        return Helper.getFilterData(keys, this);
    }

    /*
     * define press enter input filter
     */
    keyPressFilter(e) {
        let code = e.keyCode || e.charCode;
        //enter key
        if (code == 13) {
            this.getCollection();
        }
    }

    /*
     * get collection
     */
    getCollection(data = null, checkAttempt = false) {
        let that = this;
        let {
            filterData,
            xhrLoadingData,
            inited,
            attempt,
        } = that.state;

        let reqData = data ? data : filterData;
        reqData.fields = {};
        let getFields = ['attributes', 'filterTeams'];
        for (let i in getFields) {
            let field = getFields[i];
            if (!that.state[field] || that.state[field].length < 1) {
                reqData.fields[field] = 1;
            } else {
                delete reqData.fields[field];
            }
        }
        reqData.fields['hasPermissDelete'] = 1;

        if (xhrLoadingData) {
            xhrLoadingData.abort();
        }
        xhrLoadingData = $.ajax({
            type: 'GET',
            url: pageParams.urlGetReviewData,
            data: reqData,
            success: function (res) {
                let {
                    attributes,
                    filterTeams,
                    hasPermissDelete,
                    filterEmployee,
                    filterProject,
                } = res;
                let dataState = {
                    items: res.items,
                    statistics: res.statistics,
                    evalPoints: MeService.setEvalPoints(res.attrPoints),
                    commentClasses: res.commentClasses,
                    attrsCommented: res.attrsCommented,
                    listRangeMonths: res.listRangeMonths,
                    checkedItems: [],
                    totalMember: res.totalMember,
                };
                if (typeof attributes != 'undefined') {
                    dataState.attributes = attributes;
                }
                if (typeof filterTeams != 'undefined') {
                    dataState.filterTeams = filterTeams;
                }
                if (typeof hasPermissDelete != 'undefined') {
                    dataState.hasPermissDelete = hasPermissDelete;
                }
                if (typeof filterEmployee != 'undefined') {
                    dataState.currEmployee = filterEmployee;
                }
                if (typeof filterProject != 'undefined') {
                    dataState.currProject = filterProject;
                }
                if (!inited) {
                    dataState.inited = true;
                }

                that.setState(dataState);

                setTimeout(function () {
                    RKfuncion.general.initDatePicker($('.month-picker'));
                    that.initFilterData();
                    that.initFilterData($('.filter-date'));
                    RKfuncion.select2.init();

                    let fixedCols = $('.fixed-table thead tr:first .fixed-col').length;
                    $(".fixed-table").tableHeadFixer({"left" : fixedCols});
                    MeService.scrollCommentPopup();
                }, 100);
            },
            error: function (error) {
                if (checkAttempt && attempt < 1 && (error.status == 403 || error.status == 401)) {
                    that.setState({attempt: attempt + 1});
                    that.getCollection();
                    return;
                }
                Helper.alertResError(error);
            },
            complete: function () {
                that.setState({
                    loadingData: false,
                    searching: false,
                    xhrLoadingData: null,
                });
            }
        });

        that.setState({
            loadingData: true,
            searching: true,
            xhrLoadingData: xhrLoadingData,
        });
    }

    /*
     * render statistic html
     */
    renderStatistics(stat) {
        let aryStats = [
            {type: 's', label: Helper.trans('S')},
            {type: 'a', label: Helper.trans('A')},
            {type: 'b', label: Helper.trans('B')},
            {type: 'c', label: Helper.trans('C')},
        ]
        return (
            <React.Fragment>
                <span className="per_gr">
                    <strong>{Helper.trans('Total')}: </strong>
                    <span className="val-total">{stat.total}</span>
                </span>
                {aryStats.map((statItem, statKey) => {
                    let statNum = stat['count_' + statItem.type] || 0;
                    return (
                        <span key={statKey} className="per_gr">
                            <strong>{statItem.label}: </strong>
                            <span className={'val-' + statItem.type}>
                                {statNum + ' ('+ (stat.total == 0 ? 0 : (statNum / stat.total * 100).toFixed(2)) +'%)'}
                            </span>
                        </span>
                    )
                })}
            </React.Fragment>
        )
    }

    /*
     * show modal project not evaluate
     */
    showProjNotEval(e) {
        e.preventDefault();
        let {
            xhrLoadProjNotEval,
            filterData,
        } = this.state;
        if (xhrLoadProjNotEval) {
            xhrLoadProjNotEval.abort();
        }

        let that = this;
        var dataTable = $('#table-not-eval');
        if (dataTable.closest('.dataTables_wrapper').length > 0) {
            dataTable.DataTable().destroy();
            dataTable.empty();
        }

        xhrLoadProjNotEval = $.ajax({
            type: 'GET',
            url: pageParams.urlGetProjNotEval,
            data: filterData,
            success: function (res) {
                that.setState({
                    projNotEval: res.projNotEval,
                });
                setTimeout(function () {
                    $('#table-not-eval').DataTable({
                        pageLength: 10,
                    });
                }, 100);
            },
            error: function (error) {
                Helper.alertResError(error);
            },
            complete: function () {
                that.setState({
                   loadingProjNotEval: false,
                   xhrLoadProjNotEval: null,
                });
            },
        });

        this.setState({
            loadingProjNotEval: true,
            xhrLoadProjNotEval: xhrLoadProjNotEval,
        });
    }

    render() {
        let {
            searching,
            attributes,
            items,
            statistics,
            listRangeMonths,
            commentClasses,
            evalPoints,
            filterTeams,
            currEmployee,
            currProject,
            hasPermissDelete,
            currCommentAttr,
            attrComments,
            attrsCommented,
            checkedItems,
            totalMember,
            projNotEval,
            loadingProjNotEval,
            loadingData,
            inited,
            loadingComment,
            savingComment,
            deletingComment,
            currUpdateItem,
            updatingStatus,
            updatingMulti,
        } = this.state;

        let filterMonth = this.getFilterData(['filter', 'excerpt', 'month']);

        return (
            <React.Fragment>
                <div className="box-body">
                    <div className="row">
                        <div className="col-md-8 col-lg-9">
                            <div className="form-inline select-media box-action mgr-35">
                                <select id="filter_teams" className="form-control select-search has-search filter-field"
                                    data-key="team_filter" data-key2="team_id" data-refresh="1"
                                    name="filter[team_filter][team_id]">
                                    <option value="">{Helper.trans('Select project team')}</option>
                                    {filterTeams.length > 0 ? (
                                        <React.Fragment>
                                        {filterTeams.map((team, teamIdx) => (
                                            <option key={teamIdx} value={team.value}
                                                dangerouslySetInnerHTML={{__html: team.label}}></option>
                                        ))}
                                        </React.Fragment>
                                    ) : null}
                                </select>
                            </div>
                            <div className="form-inline select-media box-action mgr-35">
                                <select id="filter_teams" className="form-control select-search has-search filter-field"
                                    data-key="team_filter" data-key2="team_member" data-refresh="1"
                                    name="filter[team_filter][team_member]">
                                    <option value="">{Helper.trans('Select team member')}</option>
                                    {filterTeams.length > 0 ? (
                                        <React.Fragment>
                                        {filterTeams.map((team, teamIdx) => (
                                            <option key={teamIdx} value={team.value}
                                                dangerouslySetInnerHTML={{__html: team.label}}></option>
                                        ))}
                                        </React.Fragment>
                                    ) : null}
                                </select>
                            </div>
                            {filterMonth ? (
                            <div className="form-inline" id="proj_not_eval_box">
                                <button type="button" className="btn btn-primary" data-toggle="modal" data-target="#notEvaluate"
                                    onClick={(e) => this.showProjNotEval(e)}>
                                    {Helper.trans('Not Evaluate', {month: filterMonth})}
                                </button>
                                <strong className="margin-left-20"><span id="total_member">{totalMember}</span> {Helper.trans('persons evaluated')}</strong>
                            </div>
                            ) : null}

                            <div>
                                <i>{Helper.trans('View old item before') + ' ' + moment(pageParams.SEP_MONTH).format('MM-YYYY') + ': '}
                                    <a href={pageParams.urlOldMe + '?time=' + moment(pageParams.SEP_MONTH).format('MM-YYYY')}> {Helper.trans('click here')}</a></i>
                            </div>  
                        </div>
                        <div className="col-md-4 col-lg-3 text-right">
                            <div className="text-right group-buttons">
                                <button className="btn btn-primary mrl-5" id="reset_filter_button"
                                    onClick={this.resetFilter}>{Helper.trans('Reset filter')}</button>
                                <button className="btn btn-primary mrl-5" id="search_button"
                                    onClick={(e) => this.getCollection()}>
                                    {Helper.trans('Search')}
                                    {searching ? (
                                        <span>&nbsp; <i className="fa fa-spin fa-refresh"></i></span>
                                    ) : null}
                                </button>
                                <a target="_blank" href={pageParams.urlHelpPage} className="btn btn-primary mrl-5">{Helper.trans('Help')}</a>
                            </div>
                            <div className="text-right"><i>{Helper.trans('Right click to comment')}</i></div>
                        </div>
                    </div>                  

                    {inited ? (
                    <div className="text-center hide-filter" id="header_statistic">
                        {this.renderStatistics(statistics)}
                    </div>
                    ) : null}
                </div>

                {inited ? (
                    <ReviewItem
                        attributes={attributes}
                        items={items}
                        checkedItems={checkedItems}
                        commentClasses={commentClasses}
                        currCommentAttr={currCommentAttr}
                        evalPoints={evalPoints}
                        listRangeMonths={listRangeMonths}
                        hasPermissDelete={hasPermissDelete}
                        currEmployee={currEmployee}
                        currProject={currProject}
                        setFilterData={this.setFilterData}
                        getFilterData={this.getFilterData}
                        keyPressFilter={this.keyPressFilter}
                        initFilterData={this.initFilterData}
                        loadingData={loadingData}
                        setMainState={this.setMainState}
                        currUpdateItem={currUpdateItem}
                        updatingStatus={updatingStatus}
                        updatingMulti={updatingMulti}
                    />
                ) : (
                    <h4 className="text-center">
                        <i className="fa fa-spin fa-refresh"></i>
                    </h4>
                )}

                {inited ? (
                <div className="text-center hide-filter" id="footer_statistic">
                    {this.renderStatistics(statistics)}
                </div>
                ) : null}

                <div className="box-body">
                    <Pager
                        collection={items}
                        setFilterData={this.setFilterData}
                    />
                </div>

                <NotEvalModal
                    projNotEval={projNotEval}
                    filterMonth={filterMonth}
                    loadingProjNotEval={loadingProjNotEval}
                />

                <CommentModal
                    setMainState={this.setMainState}
                    getMainState={this.getMainState}
                    attrComments={attrComments}
                    currCommentAttr={currCommentAttr}
                    items={items.data}
                    attributes={attributes}
                    checkedItems={checkedItems}   
                    commentClasses={commentClasses}
                    attrsCommented={attrsCommented}
                    evalPoints={evalPoints}
                    loadingComment={loadingComment}
                    savingComment={savingComment}
                    deletingComment={deletingComment}
                />
                
                <ModalCommentBulk
                    setMainState={this.setMainState}
                    getMainState={this.getMainState}
                    attrComments={attrComments}
                    currCommentAttr={currCommentAttr}
                    items={items.data}
                    attributes={attributes}
                    checkedItems={checkedItems}
                    commentClasses={commentClasses}
                    attrsCommented={attrsCommented}
                    evalPoints={evalPoints}
                    loadingComment={loadingComment}
                    savingComment={savingComment}
                    deletingComment={deletingComment}
                >
                </ModalCommentBulk>

                <div className="hidden">
                    <button type="button" onClick={this.initReview} id="btn_init_review"></button>
                </div>
            </React.Fragment>
        )
    }
}

if (document.getElementById('me_review_container')) {
    ReactDOM.render(<MeReview />, document.getElementById('me_review_container'));
}

