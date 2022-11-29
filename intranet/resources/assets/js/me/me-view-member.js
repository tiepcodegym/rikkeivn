import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './../helper';
import ViewMemberItem from './components/view-member-item';
import MeService from './me-service';
import Pager from './../pager';
import CommentModal from './components/comment-modal';

export default class MeViewMember extends Component {

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
            MeService.checkOldVersion(moment(pageParams.currMonth).format('YYYY-MM'), pageParams.currProjId);
            let currMonth = moment(pageParams.currMonth).format('YYYY-MM');
            filterData.filter.excerpt.month = currMonth;
        }

        this.state = {
            searching: false,
            isScopeCompany: false,
            teamName: '',
            attempt: 0,
            attributes: [],
            filterData: filterData,
            evalPoints: {},
            items: {
                data: [],
            },
            listRangeMonths: {},
            commentClasses: {},
            attrsCommented: {},
            filterTeams: [], //list team
            currProject: null, //filter project or team
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
            xhrLoadingData: null,
            inited: false,
            loadingComment: false,
            savingComment: false,
            deletingComment: false,
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

        this.getCollection = this.getCollection.bind(this);
        this.setFilterData = this.setFilterData.bind(this);
        this.getFilterData = this.getFilterData.bind(this);
        this.resetFilter = this.resetFilter.bind(this);
        this.initViewMember = this.initViewMember.bind(this);
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

    initViewMember() {
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
        let getFields = ['attributes', 'filterTeams'];
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
            url: pageParams.urlGetViewMemberData,
            data: reqData,
            success: function (res) {
                let {
                    attributes,
                    filterTeams,
                    filterProject,
                } = res;
                let dataState = {
                    isScopeCompany: res.isScopeCompany,
                    teamName: res.teamName,
                    items: res.items,
                    evalPoints: MeService.setEvalPoints(res.attrPoints),
                    commentClasses: res.commentClasses,
                    attrsCommented: res.attrsCommented,
                    listRangeMonths: res.listRangeMonths,
                };
                if (typeof attributes != 'undefined') {
                    dataState.attributes = attributes;
                }
                if (typeof filterTeams != 'undefined') {
                    dataState.filterTeams = filterTeams;
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
            isScopeCompany,
            teamName,
            searching,
            attributes,
            items,
            listRangeMonths,
            commentClasses,
            evalPoints,
            filterTeams,
            currProject,
            currCommentAttr,
            attrComments,
            attrsCommented,
            loadingData,
            inited,
            loadingComment,
            savingComment,
            deletingComment,
        } = this.state;

        return (
            <React.Fragment>
                <div className="box-body">
                    <div className="row">
                        <div className="col-md-8 col-lg-9">
                            {isScopeCompany ? (
                            <div className="form-inline box-action select-media mgr-35">
                                <select id="filter_teams" className="form-control select-search filter-field has-search"
                                    data-key="excerpt" data-key2="team_id" data-refresh="1">
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
                            ) : null}
                            <div className="form-inline select-media box-action mgr-35">
                                <select id="filter_projects" className="form-control select-search filter-field"
                                    data-key="excerpt" data-key2="project_id" data-refresh="1"
                                    data-remote-url={pageParams.urlSearchProject}
                                    data-options={'{"allowClear":true,"placeholder":"'+ Helper.trans('Project') +'"}'}>
                                </select>
                            </div>
                            <div className="form-inline box-action select-media mgr-35">
                                <input type="text" name="filter[excerpt][month]"
                                    className="form-control filter-date month-picker" placeholder="Y-m"
                                    data-key="excerpt" data-key2="month" data-refresh="1"
                                    style={{width: '220px'}} autoComplete="off"
                                    data-format="yyyy-mm"
                                    data-options='{"viewMode":"months","minViewMode":"months","autoclose":true,"clearBtn":true,"useCurrent":false}'/>
                            </div>
                            {teamName ? (
                            <div className="form-inline box-action select-media">
                                <span className="team-of-project">{teamName}</span>
                            </div>
                            ) : null}
                    
                            <div>
                                <i>{Helper.trans('View old item before') + ' ' + moment(pageParams.SEP_MONTH).format('MM-YYYY') + ': '}
                                    <a href={pageParams.urlOldMe + '?time=' + pageParams.SEP_MONTH}> {Helper.trans('click here')}</a></i>
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
                </div>

                {inited ? (
                    <ViewMemberItem
                        attributes={attributes}
                        items={items}
                        checkedItems={[]}
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
                    <button type="button" onClick={this.initViewMember} id="btn_init_view_member"></button>
                </div>
            </React.Fragment>
        )
    }
}

if (document.getElementById('me_view_member_container')) {
    ReactDOM.render(<MeViewMember />, document.getElementById('me_view_member_container'));
}

