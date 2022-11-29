import React, {Component} from 'react';
import Helper from './../../helper';

export default class PreviewSend extends Component {

    constructor(props) {
        super(props);

        this.state = {
            loadingMailContent: false,
            loadingViewCdd: false,
        };

        this.previewEmail = this.previewEmail.bind(this);
        this.previewCddWillSend = this.previewCddWillSend.bind(this);

        setTimeout(function () {
            RKfuncion.CKEditor.init(['email_content'])
        }, 100);

        let that = this;
        $('body').on('change', '#email_template', function () {
            let {
                handleChangeMailData,
                currRequestId,
            } = that.props;
            let template = $(this).val();
            handleChangeMailData('template', template);
            that.setState({
                loadingMailContent: true,
            });
            if (typeof CKEDITOR.instances['email_content'] != 'undefined') {
                CKEDITOR.instances['email_content'].setReadOnly(true);
            }
            $.ajax({
                type: 'GET',
                url: pageParams.urlLoadTemplateContent,
                data: {
                    request_id: currRequestId,
                    template: template,
                },
                success: function (response) {
                    CKEDITOR.instances['email_content'].setData(response.content);
                },
                error: function (error) {
                    bootbox.alert({
                        className: 'modal-danger',
                        message: error.responseJSON,
                    });
                },
                complete: function () {
                    that.setState({
                        loadingMailContent: false,
                    });
                    if (typeof CKEDITOR.instances['email_content'] != 'undefined') {
                        CKEDITOR.instances['email_content'].setReadOnly(false);
                    }
                },
            });
        });
    }

    previewEmail(e) {
        e.preventDefault();
        $('#modal_preview_mail .modal-body').html('');
        $('#modal_perview_mail').modal('show');
        let {
            mailData,
            currRequestId,
        } = this.props;

        $('#modal_perview_mail .modal-body').html('<p class="text-center"><i class="fa fa-spin fa-refresh"></i></p>');
        $.ajax({
            type: 'POST',
            url: pageParams.urlSaveConfigMail,
            data: {
                request_id: currRequestId,
                subject: mailData.subject,
                content: CKEDITOR.instances['email_content'].getData(),
                _token: pageParams._token
            }, 
            success: function (response) {
                $('#modal_perview_mail .modal-body')
                        .html('<iframe style="width: 100%; height: 75vh;" frameborder="0" src="'
                            + pageParams.urlPreviewMail + '?template='+ (mailData.template || 'default') +'&request_id='+ currRequestId +'"></iframe>');
            },
            error: function (error) {
                bootbox.alert({
                    className: 'modal-danger',
                    message: error.responseJSON
                });
            }
        });
    }

    /**
     * preview candidate checked
     */
    previewCddWillSend(e) {
        e.preventDefault();
        let {mailData} = this.props;
        let candidateIds = mailData.candidate_ids;
        if (candidateIds.length < 1) {
            return;
        }
        this.setState({
            loadingViewCdd: true,
        });
        let that = this;
        $.ajax({
            type: 'GET',
            url: pageParams.urlViewCddWillSend,
            data: {
                'candidate_ids[]': candidateIds,
            },
            success: function (res) {
                let resMess = [];
                for (let i = 0; i < res.length; i++) {
                    resMess.push('<li>' + $('<div>' + res[i].name_email + '</div>').text() + '</li>');
                }
                bootbox.alert({
                    className: 'modal-default',
                    message: '<ul>' + resMess.join(" ") + '</ul>',
                });
            },
            error: function (error) {
                Helper.alertResError(error);
            },
            complete: function () {
                that.setState({
                    loadingViewCdd: false,
                });
            },
        });
    }

    render() {
        let {
            mailData,
            handleChangeMailData,
            changeAttachmentFiles,
            submitSendEmail,
        } = this.props;

        let {
            loadingMailContent,
            loadingViewCdd,
        } = this.state;

        return (
            <div className="modal fade" id="perview_send_mail" tabIndex="-1" role="dialog">
                <div className="modal-dialog modal-lg" role="document">
                    <form onSubmit={(e) => submitSendEmail(e)}>
                        <div className="modal-content">
                            <div className="modal-header">
                                <button type="button" className="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 className="modal-title">{Helper.trans('Email marketing')}</h4>
                            </div>
                            <div className="modal-body">
                                <div className="form-group row">
                                    <div className="col-sm-6">
                                        <label style={{marginBottom: 0}}>
                                            <input type="radio" name="is_send_all" checked={!parseInt(mailData.is_send_all)}
                                                onChange={(e) => handleChangeMailData('is_send_all', 0)} />
                                            <span> {Helper.trans('Send only checked items')} 
                                            ( <a href="#" onClick={this.previewCddWillSend}
                                                title={Helper.trans('View detail')}>{mailData.candidate_ids.length + ' ' + Helper.trans('item(s)') + 
                                                        (mailData.candidate_ids.length > 0 ? ' - ' + Helper.trans('click to view') : '')} </a>)
                                                {loadingViewCdd ? (
                                                    <i className="fa fa-spin fa-refresh"></i>
                                                ) : null}
                                            </span>
                                        </label>
                                        <div><i className="text-blue">({Helper.trans('Note the selected items before change filter')})</i></div>
                                    </div>
                                    <div className="col-sm-6">
                                        <label>
                                            <input type="radio" name="is_send_all" checked={parseInt(mailData.is_send_all)}
                                                onChange={(e) => handleChangeMailData('is_send_all', 1)} />
                                            <span> {Helper.trans('Send all items in current filter')}</span>
                                            <div><i className="text-blue">({Helper.trans('Only tab not send email yet')})</i></div>
                                        </label>
                                    </div>
                                </div>
                                <div className="form-group">
                                    <label><strong>{Helper.trans('Email subject')}</strong> <em className="error">*</em></label>
                                    <input type="text" className="form-control" value={mailData.subject}
                                        onChange={(e) => handleChangeMailData('subject', e.target.value)} />
                                    {mailData.is_submit && mailData.subject.trim() == '' ? (
                                        <div className="error">{Helper.trans('This field is required')}</div>
                                    ) : null}
                                </div>
                                <div className="form-group">
                                    <label><strong>{Helper.trans('Email template')}</strong></label>
                                    <select id="email_template" className="form-control select-search" style={{width: '100%'}}>
                                        {Object.keys(pageParams.templates).map((tplName, key) => (
                                            <option value={tplName} key={key}>{tplName}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label>
                                        <strong>{Helper.trans('Email content')} </strong>
                                        {loadingMailContent ? (
                                            <i className="fa fa-spin fa-refresh"></i>
                                        ) : null}
                                    </label>
                                    <textarea className="form-control content-editor" id="email_content" rows="8"
                                        value={mailData.content} disabled={loadingMailContent}
                                        onChange={(e) => handleChangeMailData('content', e.target.value)}></textarea>
                                    <div className="hint-note" style={{marginTop: '5px'}}>&#123;&#123; name &#125;&#125;: {Helper.trans('Fullname')}</div>
                                </div>

                                <div className="attachment-files-box form-group">
                                    <label><strong>{Helper.trans('Attachment files')}</strong></label>
                                    {mailData.attachment_files.length > 0 ? (
                                    <ul>
                                        {mailData.attachment_files.map((file, index) => (
                                            <li className="attach-file-item text-green" key={index}>
                                                {file.name} ({Helper.toKbSize(file.size)} KB)
                                            </li>
                                        ))}
                                    </ul>
                                    ) : null}
                                    <div className="form-group">
                                        <input type="file" name="attachment_files[]" multiple onChange={(e) => changeAttachmentFiles(e)} />
                                    </div>
                                </div>
                            </div>
                            <div className="modal-footer">
                                <button type="button" className="btn btn-default" data-dismiss="modal">{Helper.trans('Close')}</button>
                                <button type="button" className="btn btn-info"
                                    onClick={this.previewEmail}><i className="fa fa-eye"></i> {Helper.trans('Preview')}</button>
                                <button type="submit" className="btn btn-success"><i className="fa fa-send"></i> {Helper.trans('Send')}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        )
    }

}

