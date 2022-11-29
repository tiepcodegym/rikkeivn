import React, {Component} from 'react';
import Helper from './../../helper';

export default class MeTHead extends Component {

    constructor(props) {
        super(props);
    }

    componentDidMount() {
        $('.i-me-tooltip').each(function () {
            let className = 'me-popup-tooltip';
            let addClass = $(this).attr('data-class');
            if (typeof addClass != 'undefined') {
                className += ' ' + addClass;
            }
            $(this).tooltip({
                customClass: className,
                container: 'body',
                html: true,
                placement: 'left',
            }).trigger('mouseenter');
        });

        $('.i-me-tooltip').trigger('mouseleave');
        
        if ($('.check-all-items').length > 0) {
            setTimeout(function () {
                $('.check-all-items').trigger('click');
            }, 300);
        }
    }

    renderHeadRow(length, config) {
        let row = [];
        for (let i = 1; i <= length; i++) {
            if (i <= config) {
                row.push(<th key={i} className="text-center fixed-col">({i})</th>)
                if (config == 2 && i == 2) {
                    i += 2;
                }
                if (config == 3 && i == 3) {
                    i += 1;
                }
            } else {
                row.push(<th key={i} className="text-center">({i})</th>)
            }
        }
        return row;
    }

    render() {
        let {
            checkbox,
            staffView,
            hasMonth,
            createTeam,
            isLeaderReview,
            isReviewTeam,
            attributes,
            sortContri,
            actionCol,
            trFilter,
            setFilterData,
            getFilterData,
        } = this.props;

        let normalWeight = 0;
        let length = attributes.length + 10; //21
        let config = 2; //fixed col
        if (isLeaderReview) {
            config = 3;
            length += 2;//23
        }
        if (isReviewTeam) {
            config = 3;
            length += 1;//22
        }
        if (createTeam) {
            config = 2;
            length -= 1; //19
        }
        if (staffView) {
            config = 1;
            length -= 1; //20
        }

        return (
            <thead>
                <tr>
                    {checkbox ? (
                    <th className="fixed-col" width="30"><input type="checkbox" className="check-all-items" /></th>
                    ) : null }
                    {!staffView ? (
                    <th width="25" style={{width: '85px'}} className="fixed-col">ID</th>
                    ) : null}
                    {hasMonth ? (
                    <th width="65" className="fixed-col">{Helper.trans('Month')}</th>
                    ) : null}
                    {!staffView ? (
                    <th className="fixed-col">{Helper.trans('Account')}</th>
                    ) : null}
                    {!createTeam ? (
                    <th>{Helper.trans('Project name')}</th>
                    ) : null}
                    {(isLeaderReview || isReviewTeam || staffView) ? (
                    <th>{Helper.trans('Project type')}</th>
                    ) : null}
                    {attributes.map((attr, attrKey) => {
                        normalWeight += attr.weight;
                        return (
                            <th key={attrKey} className="tooltip_group attr_normal num-val" data-related-col="summary">
                                <span>{attr.label}</span>
                                <i className="fa fa-question-circle i-me-tooltip" data-class="text-left"
                                    title={'<p>'+ attr.name +'</p><div>' + attr.description + '</div>'}></i>
                            </th>
                        )
                    })}
                    <th data-name="summary" width="50" className="tooltip_group cal-value attr_perform nul-val">
                        <span>{Helper.trans('Summary')}</span>
                        <i className="fa fa-question-circle i-me-tooltip" data-class="text-center"
                            title={Helper.trans('Summary')}></i>
                    </th>
                    <th width="50" className="tooltip_group attr_perform num-val" style={{minWidth: '55px'}}>
                        <span>{Helper.trans('Effort in Project')}</span>
                        <i className="fa fa-question-circle i-me-tooltip" data-class="text-center"
                            title={Helper.trans('Work day in this project')}></i>
                        <p>{Helper.trans('days')}</p>
                    </th>
                    <th width="65" className="tooltip_group _word_break attr_perform"
                        style={!sortContri ? {minWidth: '110px'} : null} >
                        <span>{Helper.trans('Contribution level')}</span>
                        <i className="fa fa-question-circle i-me-tooltip" data-class="text-center"
                            title={Helper.trans('Contribution level')}></i>
                        {sortContri ? (
                        <div className={'sorting sort_static col-name sorting_' + getFilterData(['orderby', 'avg_point'])}
                            onClick={(e) => setFilterData(['orderby', 'avg_point'], null, true, e)} data-order="avg_point" data-dir="asc"></div>
                        ) : null}
                    </th>
                    <th width="65" className="tooltip_group _bd_right attr_normal">
                        <span>{Helper.trans('Note')}</span>
                        <i className="fa fa-question-circle i-me-tooltip" data-class="text-center"
                            title={Helper.trans('Note')}></i>
                    </th>
                    <th className="attr_normal tooltip_group" style={!sortContri ? {minWidth: '90px'} : null}>
                        {Helper.trans('Status')}
                        {sortContri ? (
                        <div className={'sorting sort_static col-name sorting_' + getFilterData(['orderby', 'status'])}
                            onClick={(e) => setFilterData(['orderby', 'status'], null, true, e)} data-order="status" data-dir="asc"></div>
                        ) : null}
                    </th>

                    {actionCol ? (
                    <th style={!staffView ? {minWidth: '130px'} : null}></th>
                    ) : null}
                </tr>
                {typeof trFilter != 'undefined' ? (
                    <React.Fragment>
                        {trFilter()}
                    </React.Fragment>
                ) : null}
            </thead>
        )
    }
}


