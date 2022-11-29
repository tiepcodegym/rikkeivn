import React, {Component} from 'react';
import Helper from './helper';

export default class Pager extends Component {

    /*
     * go to page
     */
    goPage(e, page = null) {
        e.preventDefault();
        let {collection} = this.props;
        let intPage = parseInt(page);
        if (intPage < 1 || intPage > parseInt(collection.last_page) || intPage == parseInt(collection.current_page)) {
            return;
        }
        if (page == null) {
            page = (typeof e.target.value != 'undefined') ? e.target.value : 1;
        }
        this.preSetFilterData('page', page, true);
    }

    /*
     * input page
     */
    pressPage(event) {
        let code = event.keyCode || event.charCode;
        if (code == 13) {
            event.preventDefault();
            let page = event.target.value;
            this.preSetFilterData('page', page, true);
        }
    }

    /*
     * handle set filter data on event
     */
    handleSetFilterData(e, field, refresh = false) {
        this.preSetFilterData(field, e.target.value, refresh);
    }

    /*
     * handle set filter data after event fire
     */
    preSetFilterData(field, value, refresh) {
        let {tab, setFilterData} = this.props;
        if (typeof tab != 'undefined') {
            if (field == 'per_page') {
                setFilterData(tab, ['page'], 1);
            }
            setFilterData(tab, [field], value, refresh);
        } else {
            if (field == 'per_page') {
                setFilterData(['page'], 1);
            }
            setFilterData([field], value, refresh);
        }
    }

    render() {
        let {collection, tab} = this.props;
        let optionLimit = [
            {value: 10, label: 10},
            {value: 20, label: 20},
            {value: 50, label: 50},
            {value: 100, label: 100},
            {value: 150, label: 150},
            {value: 200, label: 200}
        ];
        let defaultLimit = collection.per_page;
        if (typeof pageParams.pagerOptionsLimit != 'undefined') {
            optionLimit = pageParams.pagerOptionsLimit;
        }

        setTimeout(function () {
            let elPageNum = typeof tab == 'undefined' ? $('.form-pager [name="page"]')
                            : $('[data-tab="'+ tab +'"] .form-pager [name="page"]');
            elPageNum.val(collection.current_page);
        }, 100);

        if (typeof collection.total == 'undefined') {
            return null;
        }
        return (
            <div className="grid-pager-redirect" data-tab={tab != 'undefined' ? tab : null}>
                <div className="data-pager-info grid-pager-box" role="status" aria-live="polite">
                    <span>{Helper.trans('Total') + ' ' + collection.total + ' ' + Helper.trans('entity')} / {collection.last_page + ' ' + Helper.trans('page')}</span>
                </div>

                <div className="grid-pager-box-right">
                    <div className="dataTables_length grid-pager-box">
                        <label>{Helper.trans('Show')}
                            <select className="form-control input-sm" autoComplete="off" value={defaultLimit}
                                onChange={(e) => this.handleSetFilterData(e, 'per_page', true)}>
                                {optionLimit.map((item, index) => (
                                    <option value={item.value} key={index}>{item.label}</option>
                                ))}
                            </select>
                        </label>
                    </div>

                    <div className="dataTables_paginate paging_simple_numbers grid-pager-box pagination-wrapper">
                        <ul className="pagination">
                            <li className={'paginate_button first-page' + (collection.current_page == 1 ? ' disabled' : '')}>
                                <a href="#" onClick={(e) => this.goPage(e, 1)}>
                                    <i className="fa fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li className={'paginate_button previous' + (collection.current_page == 1 ? ' disabled' : '')}>
                                <a href="#" onClick={(e) => this.goPage(e, collection.current_page - 1)}>
                                    <i className="fa fa-arrow-left"></i>
                                </a>
                            </li>
                            <li className="paginate_button">
                                <div action="" method="get" className="form-pager">
                                    <input className="input-text form-control" name="page"
                                        onKeyPress={(e) => this.pressPage(e)}/>
                                </div>
                            </li>
                            <li className={'paginate_button next' + (!collection.next_page_url ? ' disabled' : '')}>
                                <a href="#" onClick={(e) => this.goPage(e, collection.current_page + 1)}>
                                    <i className="fa fa-arrow-right"></i>
                                </a>
                            </li>
                            <li className={'paginate_button lastpage-page' + (collection.last_page == collection.current_page ? ' disabled' : '')}>
                                <a href="#" onClick={(e) => this.goPage(e, collection.last_page)}>
                                    <i className="fa fa-angle-double-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div className="clearfix"></div>
            </div>
        )
    }

}


