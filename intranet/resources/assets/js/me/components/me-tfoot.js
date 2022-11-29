import React, {Component} from 'react';
import Helper from './../../helper';

export default class MeTFoot extends Component {

    render() {
        let {
            createTeam,
            attributes,
            avgAttrPoints,
        } = this.props;

        return (
            <tfoot>
                <tr>
                    {createTeam ? (
                        <td colSpan="3" className="fixed-col text-right">
                            <strong>{Helper.trans('Average')}</strong>
                        </td>
                    ) : (
                        <React.Fragment>
                            <td colSpan="3" className="fixed-col"></td>
                            <td className="text-right"><strong>{Helper.trans('Average')}</strong></td>
                        </React.Fragment>
                    )}

                    {attributes.length > 0 ? (
                        <React.Fragment>
                        {attributes.map((attr, attrKey) => (
                            <td key={attrKey} className="_nm_avg col-avg text-center" data-attr={attr.id}>
                                {typeof avgAttrPoints[attr.id] != 'undefined' ? avgAttrPoints[attr.id] : 0}
                            </td>
                        ))}
                        </React.Fragment>
                    ) : null}

                    <td colSpan="5"></td>
                </tr>
            </tfoot>
        )
    }

}


