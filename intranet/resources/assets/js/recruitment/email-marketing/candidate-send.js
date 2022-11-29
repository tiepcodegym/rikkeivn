import React, {Component} from 'react';
import Helper from './../../helper';
import Pager from './../../pager';

export default class CandidateSend extends Component {

    constructor(props) {
        super(props);
    }

    /*
     * get candidate position label
     */
    getPositionLabel(pos) {
        if (!pos) {
            return null;
        }
        let positions = pageParams.positions;
        let aryPos = (pos + '').split(', ');
        let result = [];
        for (var i = 0; i < aryPos.length; i++) {
            if (typeof positions[aryPos[i]] != 'undefined') {
                result.push(positions[aryPos[i]]);
            }
        }
        return result.join(', ');
    }

    /*
     * get candidate status label
     */
    getStatusLabel(status, item) {
        if (status == pageParams.statusFail) {
            if (item.offer_result == pageParams.cddResultFail) {
                return Helper.trans('Offer fail');
            }
            if (item.interview_result == pageParams.cddResultFail) {
                return Helper.trans('Interview fail');
            }
            if (item.test_result == pageParams.cddResultFail) {
                return Helper.trans('Test fail');
            }
            if (item.contact_result = pageParams.cddResultFail) {
                return Helper.trans('Contact fail');
            }
            return Helper.trans('Fail');
        }
        let generalStatuses = pageParams.generalStatuses;
        if (typeof generalStatuses[status] == 'undefined') {
            return Helper.trans('Contacting');
        }
        return generalStatuses[status];
    }

    /*
     * get developer type level label
     */
    getDevTypeLabel(type) {
        let devTypes = pageParams.devTypes;
        if (typeof devTypes[type] == 'undefined') {
            return null;
        }
        return devTypes[type];
    }

    /*
     * get resource request title
     */
    getRequestTitle(strTitle) {
        if (!strTitle) {
            return null;
        }
        let outPut = [];
        let arrTitle = strTitle.split(', ');
        for (var i = 0; i < arrTitle.length; i++) {
            let titleItem = arrTitle[i];
            let arrItem = titleItem.split('||');
            if (arrItem.length < 2) {
                continue;
            }
            outPut.push(arrItem[1]);
        }
        return outPut;
    }

    /*
     * get programming language name
     */
    getProgNames(progIds) {
        if (!progIds) {
            return null;
        }
        let programs = pageParams.programingLanguages;
        let arrProgIds = progIds.split(', ');
        let outPut = [];
        for (var i = 0; i < arrProgIds.length; i++) {
            let progId = arrProgIds[i];
            if (typeof programs[progId] != 'undefined') {
                outPut.push(programs[progId]);
            }
        }
        return outPut.join(', ');
    }

    /*
     * interested label
     */
    getInterestedLabel(interested) {
        if (!interested) {
            return '';
        }
        let interestedOptions = pageParams.interestedOptions;
        if (typeof interestedOptions[interested] == 'undefined') {
            return '';
        }
        return '<i class="fa fa-star-o '+ interestedOptions[interested].class +'" title="'+ interestedOptions[interested].label +'"></i>';
    }

    onChangeDraft() {

    }

    render() {
        let {
            tab,
            inited,
            candidates,
            searching,
            setFilterData,
            getFilterData,
            keyPressFilter,
        } = this.props;
        if (typeof candidates.data == 'undefined') {
            candidates.data = [];
        }
        if (!inited) {
            if (searching) {
                return (<p className="text-center"><i className="fa fa-spin fa-refresh"></i></p>)
            } else {
                return (<h4 className="text-center text-green">{Helper.trans('Please click search button')}</h4>)
            }
        } else {
            return (
                <React.Fragment>
                <div className="table-responsive">
                    <table className="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead className="align-middle">
                            <tr>
                                <th>
                                    <input type="checkbox" value="1" className="check-all-items" />
                                </th>
                                <th>No.</th>
                                <th className="col-sorting">
                                    {Helper.trans('Interested')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'interested'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'interested'], null, true, e)}></div>
                                </th>
                                <th className="col-sorting">
                                    {Helper.trans('Fullname')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'fullname'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'fullname'], null, true, e)}></div>
                                </th>
                                <th className="col-sorting">
                                    {Helper.trans('Email')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'email'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'email'], null, true, e)}></div>
                                </th>
                                <th>{Helper.trans('Request')}</th>
                                <th className="col-sorting">
                                    {Helper.trans('Request Department')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'team_req_names'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'team_req_names'], null, true, e)}></div>
                                </th>
                                <th>{Helper.trans('Position')}</th>
                                <th>{Helper.trans('Programing language')}</th>
                                <th className="col-sorting">
                                    {Helper.trans('Status')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'status'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'status'], null, true, e)}></div>
                                </th>
                                <th className="col-sorting">
                                    {Helper.trans('Recruiter')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'recruiter'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'recruiter'], null, true, e)}></div>
                                </th>
                                <th className="col-sorting">
                                    {Helper.trans('Type')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'type'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'type'], null, true, e)}></div>
                                </th>
                                <th className="col-sorting">
                                    {Helper.trans('Status update date')}
                                    <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'updated_date'])}
                                        onClick={(e) => setFilterData(tab, ['orderby', 'updated_date'], null, true, e)}></div>
                                </th>
                                {tab == 'sent' ? (
                                    <th className="col-sorting">
                                        {Helper.trans('Sent at')}
                                        <div className={'sorting sort_static col-name sorting_' + getFilterData(tab, ['orderby', 'sent_date'])}
                                            onClick={(e) => setFilterData(tab, ['orderby', 'sent_date'], null, true, e)}></div>
                                    </th>
                                ) : null}
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <select className="control select-search filter-field select-with-icon"
                                        data-key="cdd.interested" data-tab={tab} data-refresh="1"
                                        style={{width: '140px'}}>
                                        <option value="">&nbsp;</option>
                                        {pageParams.interestedOptions.map((iItem, iKey) => (
                                            <option value={iKey} key={iKey} data-icon-class={'fa fa-star-o ' + iItem.class}>{iItem.label}</option>
                                        ))}
                                    </select>
                                </td>
                                <td>
                                    <input type="text" className="form-control" value={getFilterData(tab, ['filter', 'cdd.fullname'])}
                                        onChange={(e) => setFilterData(tab, ['filter', 'cdd.fullname'], e.target.value)}
                                        onKeyPress={(e) => keyPressFilter(e)}
                                        placeholder={Helper.trans('Search')} />
                                </td>
                                <td>
                                    <input type="text" className="form-control" value={getFilterData(tab, ['filter', 'cdd.email'])}
                                        onChange={(e) => setFilterData(tab, ['filter', 'cdd.email'], e.target.value)}
                                        onKeyPress={(e) => keyPressFilter(e)}
                                        placeholder={Helper.trans('Search')} />
                                </td>
                                <td></td>
                                <td>
                                    <input type="text" className="form-control" value={getFilterData(tab, ['filter', 'except', 'req_team'])}
                                        onChange={(e) => setFilterData(tab, ['filter', 'except', 'req_team'], e.target.value)}
                                        onKeyPress={(e) => keyPressFilter(e)}
                                        placeholder={Helper.trans('Search')} />
                                </td>
                                <td></td>
                                <td></td>
                                <td>
                                    <select className="form-control select-search filter-field"
                                        data-key="except" data-key2="status" data-tab={tab} data-refresh="1"
                                        style={{minWidth: '110px'}}>
                                        <option value="">&nbsp;</option>
                                        {(tab == 'not_send') ? (
                                            <React.Fragment>
                                            {Object.keys(pageParams.stepFailStatuses).map((sKey, index) => (
                                                <option value={sKey} key={index}>{pageParams.stepFailStatuses[sKey]}</option>
                                            ))}
                                            </React.Fragment>
                                        ) : (
                                            <React.Fragment>
                                            {pageParams.allStatuses.map((sItem, index) => (
                                                <option value={sItem.id} key={index}>{sItem.name}</option>
                                            ))}
                                            </React.Fragment>
                                        )}
                                    </select>
                                </td>
                                <td>
                                    <input type="text" className="form-control" value={getFilterData(tab, ['filter', 'cdd.recruiter'])}
                                        onChange={(e) => setFilterData(tab, ['filter', 'cdd.recruiter'], e.target.value)}
                                        onKeyPress={(e) => keyPressFilter(e)}
                                        placeholder={Helper.trans('Search')} />
                                </td>
                                <td></td>
                                <td></td>
                                {tab == 'sent' ? (
                                    <td></td>
                                ) : null}
                            </tr>
                            {candidates.data.length > 0 ? (
                                <React.Fragment>
                                {candidates.data.map((cddItem, index) => {
                                    let perPage = candidates.per_page;
                                    let currPage = candidates.current_page;
                                    let arrReqTitle = this.getRequestTitle(cddItem.request_titles);
                                    return (
                                        <tr key={index}>
                                            <td>
                                                <input type="checkbox" value={cddItem.id} className="check-item" />
                                            </td>
                                            <td>{(currPage - 1) * perPage + index + 1}</td>
                                            <td className="text-center" dangerouslySetInnerHTML={{__html: this.getInterestedLabel(cddItem.interested)}}></td>
                                            <td><a href={pageParams.urlCandidateDetail + '/' + cddItem.id} target="_blank">{cddItem.fullname}</a></td>
                                            <td>{cddItem.email}</td>
                                            <td>
                                                {arrReqTitle ? (
                                                    <ul className="padding-left-20" style={{marginBottom: '0px'}}>
                                                        {arrReqTitle.map((title, keyTitle) => (
                                                            <li key={keyTitle}>{title}</li>
                                                        ))}
                                                    </ul>
                                                ) : null}
                                            </td>
                                            <td>{cddItem.team_req_names}</td>
                                            <td>{this.getPositionLabel(cddItem.position_applies)}</td>
                                            <td>{this.getProgNames(cddItem.prog_ids)}</td>
                                            <td>{this.getStatusLabel(cddItem.status, cddItem)}</td>
                                            <td>{cddItem.recruiter}</td>
                                            <td>{this.getDevTypeLabel(cddItem.type)}</td>
                                            <td className="white-space-nowrap">{cddItem.updated_date}</td>
                                            {tab == 'sent' ? (
                                                <td className="white-space-nowrap">{cddItem.sent_date}</td>
                                            ) : null}
                                        </tr>
                                    )
                                })}
                                </React.Fragment>
                            ) : (
                            <tr>
                                <td colSpan={tab == 'sent' ? 14 : 13} className="text-center">
                                    <h4>{searching ? (
                                            <i className="fa fa-spin fa-refresh"></i>
                                        ) : (Helper.trans('Not found item'))}</h4>
                                </td>
                            </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className="box-body">
                    <Pager
                        collection={candidates}
                        setFilterData={setFilterData}
                        tab={tab}
                    />
                </div>
                </React.Fragment>
            )
        }
    }

}

