import React, {Component} from 'react';
import Helper from './../../helper';
import CandidateSend from './candidate-send';

export default class CandidateList extends Component {

    render() {
        let {
            inited,
            candidates,
            searching,
            setFilterData,
            getFilterData,
            keyPressFilter,
            checkedItems,
        } = this.props;
        return (
            <div className="nav-tabs-custom nav-tabs-rikkei">
                <ul className="nav nav-tabs" role="tablist">
                    <li role="presentation" className="active">
                        <a href="#tab_not_send" data-toggle="tab">{Helper.trans('List not send email marketing yet')}</a>
                    </li>
                    <li role="presentation">
                        <a href="#tab_send" data-toggle="tab">{Helper.trans('List sent email marketing')}</a>
                    </li>
                </ul>
                <div className="tab-content">
                    <div className="tab-pane active" id="tab_not_send">
                        <CandidateSend
                            tab={'not_send'}
                            inited={inited}
                            candidates={candidates.not_send}
                            searching={searching}
                            setFilterData={setFilterData}
                            getFilterData={getFilterData}
                            keyPressFilter={keyPressFilter}
                            checkedItems={checkedItems}
                        />
                    </div>
                    <div className="tab-pane" id="tab_send">
                        <CandidateSend
                            tab={'sent'}
                            inited={inited}
                            candidates={candidates.sent}
                            searching={searching}
                            setFilterData={setFilterData}
                            getFilterData={getFilterData}
                            keyPressFilter={keyPressFilter}
                            checkedItems={checkedItems}
                        />
                    </div>
                </div>
            </div>
        )
    }

}

