import React, {Component} from 'react';
import Helper from './../../react-helper';

export default class RoleEdit extends Component {

    render() {
        let {
            roleEdit,
            handleChangeRoleField,
            saveRole,
            savingRole,
            errorSaveRole
        } = this.props;

        return (
            <div className="modal fade" id="role-edit-form">
                <div className="modal-dialog modal-dialog" role="document">
                    <div className="modal-content">
                        <form className="form" method="post" action="" id="form-role-edit"
                            onSubmit={saveRole}>
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 className="modal-title">
                                    {roleEdit.id ? Helper.trans('Edit role') : Helper.trans('Create role')}
                                </h4>
                            </div>
                            <div className="modal-body">
                                {errorSaveRole ? (
                                    <div className="margin-bottom-15">
                                    {typeof errorSaveRole == 'object' ? (
                                        <div>
                                            {Object.keys(errorSaveRole).map((field, index) => (
                                                <ul key={index}>
                                                    {errorSaveRole[field].map((mess, key) => (
                                                        <li key={key} className="error">{mess}</li>
                                                    ))}
                                                </ul>
                                            ))}
                                        </div>
                                    ) : (
                                        <ul>
                                            <li className="error">{errorSaveRole}</li>
                                        </ul>
                                    )}
                                    </div>
                                ) : null}
                            
                                <div className="form-group">
                                    <label className="form-label">{Helper.trans('Name')} <em className="text-red">*</em></label>
                                    <div className="form-data">
                                    <input type="text" className="form-control" id="position-name" name="role[role]" 
                                        value={roleEdit.role} required 
                                        onChange={(e) => handleChangeRoleField(e, 'role')}/>
                                    </div>
                                </div>
                                <div className="margin-bottom-15 clearfix"></div>
                                <div className="form-group">
                                    <label className="form-label required">{Helper.trans('Description')} <em className="text-red">*</em></label>
                                    <div className="form-data">
                                        <textarea className="form-control" name="role[description]" rows="3"
                                            onChange={(e) => handleChangeRoleField(e, 'description')} required
                                            value={roleEdit.description}></textarea>
                                    </div>
                                </div>
                                <div className="clearfix"></div>
                            </div>
                            <div className="modal-footer">
                                <button type="submit" className="btn-add btn-large">
                                    {Helper.trans('Save')} {savingRole ? (<i className="fa fa-spin fa-refresh"></i>) : null}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        )
    }

}
