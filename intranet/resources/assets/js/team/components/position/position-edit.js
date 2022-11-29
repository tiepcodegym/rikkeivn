import React, {Component} from 'react';
import Helper from './../../react-helper';

export default class PositionEdit extends Component {

    render() {
        let {
            posEdit,
            handleChangePosField,
            savePosition,
            savingPosition,
            errorSavePos
        } = this.props;

        return (
            <div className="modal fade" id="position-edit-form">
                <div className="modal-dialog modal-dialog">
                    <div className="modal-content">
                        <form className="form" method="post" action=""
                            onSubmit={savePosition}>
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 className="modal-title">
                                    {posEdit.id ? Helper.trans('Edit team position') : Helper.trans('Create team position')}
                                </h4>
                            </div>
                            <div className="modal-body">
                                {errorSavePos ? (
                                    <div className="margin-bottom-15">
                                    {typeof errorSavePos == 'object' ? (
                                        <div>
                                            {Object.keys(errorSavePos).map((field, index) => (
                                                <ul key={index}>
                                                    {errorSavePos[field].map((mess, key) => (
                                                        <li key={key} className="error">{mess}</li>
                                                    ))}
                                                </ul>
                                            ))}
                                        </div>
                                    ) : (
                                        <ul>
                                            <li className="error">{errorSavePos}</li>
                                        </ul>
                                    )}
                                    </div>
                                ) : null}

                                <div className="form-group">
                                    <label className="form-label required">{Helper.trans('Position name')} <em className="text-red">*</em></label>
                                    <div className="form-data">
                                        <input type="text" className="form-control" id="position-name" name="position[role]" 
                                            value={posEdit.role} required 
                                            onChange={(e) => handleChangePosField(e, 'role')} />
                                    </div>
                                </div>
                                <div className="clearfix"></div>
                            </div>
                            <div className="modal-footer">
                                <button type="submit" className="btn-add btn-large">
                                    {Helper.trans('Save')} {savingPosition ? (<span className="fa fa-spin fa-refresh"></span>) : null}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        )
    }

}
