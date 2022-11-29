import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './../helper';
import ConfirmItem from './components/confirm-item';
import MeService from './me-service';
import Pager from './../pager';
import CommentModal from './components/comment-modal';

export default class MeConfirmList extends Component {

    constructor(props) {
        super(props);

        let filterData = Helper.session.getRawItem('filterData');
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
            let currMonth = moment(pageParams.currMonth).format('YYYY-MM');
            filterData.filter.excerpt.month = currMonth;
            MeService.checkOldVersion(currMonth, pageParams.currProjId);
        }

        this.state = {
            searching: false,
            attempt: 0,
            currProject: pageParams.currProject,
            currMonth: pageParams.currMonth,
            listProjsOfEmp: [],
            attributes: [],
            filterData: filterData,
            evalPoints: {},
            items: {
                data: [],
            },
            listRangeMonths: {},
            commentClasses: {},
            attrsCommented: {},
            attrComments: { //attribute comment list
                data: [],
            },
            currCommentAttr: { // params of attribute comments
                evalId: null,
                attrId: null,
                commentType: null,
                commentText: '',
            },
            loadingData: false,
            inited: false,
            loadingComment: false,
            savingComment: false,
            deletingComment: false,
            updatingStatus: false,
            currUpdateItem: null,
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
        this.resetFilter = this.resetFilter.bind(this);
        this.initConfirm = this.initConfirm.bind(this);
        this.initFilterData = this.initFilterData.bind(this);
        this.getCollection = this.getCollection.bind(this);
        this.filterFieldChange = this.filterFieldChange.bind(this);
        this.setMainState = this.setMainState.bind(this);
        this.getMainState = this.getMainState.bind(this);
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

        Helper.session.removeItem('filterData');
        let location = window.location;
        window.history.pushState({}, document.title, location.origin + location.pathname);

        this.getCollection(data);
    }

    initConfirm() {
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

        let {filterData, inited} = this.state;
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
        Helper.setFilterData(params, value, refresh, this);
    }

    /*
     * get filter collection data
     */
    getFilterData(keys = ['filter']) {
        return Helper.getFilterData(keys, this);
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
        let getFields = ['attributes', 'listProjsOfEmp'];
        for (let i in getFields) {
            let field = getFields[i];
            if (!that.state[field] || that.state[field].length < 1) {
                reqData.fields[field] = 1;
            } else {
                delete reqData.fields[field];
            }
        }

        if (xhrLoadingData) {
            xhrLoadingData.abort();
        }
        xhrLoadingData = $.ajax({
            type: 'GET',
            url: pageParams.urlGetConfirmData,
            data: reqData,
            success: function (res) {
                let {
                    attributes,
                    filterProject,
                    listProjsOfEmp,
                } = res;
                let dataState = {
                    items: res.items,
                    evalPoints: MeService.setEvalPoints(res.attrPoints),
                    commentClasses: res.commentClasses,
                    attrsCommented: res.attrsCommented,
                    listRangeMonths: res.listRangeMonths,
                };
                if (typeof attributes != 'undefined') {
                    dataState.attributes = attributes;
                }
                if (typeof filterProject != 'undefined') {
                    dataState.currProject = filterProject;
                }
                if (typeof listProjsOfEmp != 'undefined') {
                    dataState.listProjsOfEmp = listProjsOfEmp;
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
                    xhrLoadingData: null,
                });
            }
        });

        that.setState({
            loadingData: true,
            xhrLoadingData: xhrLoadingData,
        });
    }

    render() {
        let {
            searching,
            listProjsOfEmp,
            attributes,
            items,
            listRangeMonths,
            commentClasses,
            evalPoints,
            currProject,
            currCommentAttr,
            attrComments,
            attrsCommented,
            loadingData,
            inited,
            loadingComment,
            savingComment,
            deletingComment,
            currUpdateItem,
            updatingStatus,
        } = this.state;

        let meTbl = pageParams.meTbl;

        return (
            <React.Fragment>
                <div className="box-body">
                    <div className="row">
                        <div className="col-md-8">
                            <div className="form-inline box-action select-media mgr-20">
                                <select className="form-control select-search has-search filter-field"
                                    data-key="excerpt" data-key2="project_id" data-refresh="1">
                                    <option value="">{Helper.trans('Select project')}</option>
                                    <option value="_TEAM_">&nbsp;</option>
                                    {listProjsOfEmp.map((proj, projKey) => (
                                        <option value={proj.id} key={projKey}>{proj.name}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="form-inline box-action select-media mgr-20">
                                <input type="text" name="filter[excerpt][month]"
                                    className="form-control filter-date month-picker" placeholder="Y-m"
                                    data-key="excerpt" data-key2="month" data-refresh="1"
                                    style={{width: '220px'}} autoComplete="off"
                                    data-format="yyyy-mm"
                                    data-options='{"viewMode":"months","minViewMode":"months","autoclose":true,"clearBtn":true,"useCurrent":false}'/>
                            </div>
                            <div className="form-inline box-action select-media">
                                <select className="form-control select-search filter-field"
                                    data-key={meTbl + '.status'} data-refresh="1">
                                    <option value="">--{Helper.trans('Select status')}--</option>
                                    {Object.keys(pageParams.listFilterStatuses).map((sttVal, sttIdx) => (
                                        <option key={sttIdx} value={sttVal}>{pageParams.listStatuses[sttVal]}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <i>{Helper.trans('View old item before') + ' ' + moment(pageParams.SEP_MONTH).format('MM-YYYY') + ': '}
                                    <a href={pageParams.urlOldMe + '?time=' + pageParams.SEP_MONTH + '-01 00:00:00'}> {Helper.trans('click here')}</a></i>
                            </div>
                        </div>
                        <div className="col-md-4 text-right">
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
                    <div className="clearfix"></div>
                </div>

                {inited ? (
                    <ConfirmItem
                        attributes={attributes}
                        items={items}
                        commentClasses={commentClasses}
                        currCommentAttr={currCommentAttr}
                        evalPoints={evalPoints}
                        listRangeMonths={listRangeMonths}
                        currProject={currProject}
                        setFilterData={this.setFilterData}
                        getFilterData={this.getFilterData}
                        initFilterData={this.initFilterData}
                        loadingData={loadingData}
                        setMainState={this.setMainState}
                        currUpdateItem={currUpdateItem}
                        updatingStatus={updatingStatus}
                    />
                ) : (
                    <h4 className="text-center">
                        <i className="fa fa-spin fa-refresh"></i>
                    </h4>
                )}

                <div className="box-body">
                    <Pager
                        collection={items}
                        setFilterData={this.setFilterData}
                    />
                </div>

                <CommentModal
                    currUser={pageParams.currUser}
                    setMainState={this.setMainState}
                    getMainState={this.getMainState}
                    attrComments={attrComments}
                    currCommentAttr={currCommentAttr}
                    items={items.data}
                    attributes={attributes}
                    checkedItems={[]}
                    commentClasses={commentClasses}
                    attrsCommented={attrsCommented}
                    evalPoints={evalPoints}
                    loadingComment={loadingComment}
                    savingComment={savingComment}
                    deletingComment={deletingComment}
                />

                <div className="hidden">
                    <button type="button" onClick={this.initConfirm} id="btn_init_confirm"></button>
                </div>
            </React.Fragment>
        )
    }
}

if (document.getElementById('me_confirm_list_container')) {
    ReactDOM.render(<MeConfirmList />, document.getElementById('me_confirm_list_container'));
}

