import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Helper from './../helper';
import CandidateList from './email-marketing/candidate-list';
import PreviewSend from './email-marketing/preview-send';

export default class EmailMarketing extends Component {
    constructor(props) {
        super(props);
        let filterData = Helper.session.getRawItem('filterData', 'email_marketing');
        if (!filterData) {
            filterData = {
                sent: {
                    is_sent: 1,
                    page: 1,
                    per_page: 50,
                    filter: {},
                },
                not_send: {
                    is_sent: 0,
                    page: 1,
                    per_page: 50,
                    filter: {},
                },
            }
        }

        this.state = {
            candidates: {
                sent: {},
                not_send: {}
            },
            currRequestId: null,
            currRequest: null,
            searching: false,
            inited: false,
            filterData: filterData,
            checkedItems: [],
            mailData: {
                is_send_all: 0,
                candidate_ids: [],
                subject: $('#email_subject_val').val(),
                content: '', //$('#email_content_val').val(),
                attachment_files: [],
                is_submit: false,
            },
            sendingMail: false,
            errorSendingMail: null,
            loadingRequest: false,
        }

        this.getCollection = this.getCollection.bind(this);
        this.setFilterData = this.setFilterData.bind(this);
        this.getFilterData = this.getFilterData.bind(this);
        this.keyPressFilter = this.keyPressFilter.bind(this);
        this.resetFilter = this.resetFilter.bind(this);
        this.handleChangeMailData = this.handleChangeMailData.bind(this);
        this.previewSendEmail = this.previewSendEmail.bind(this);
        this.changeAttachmentFiles = this.changeAttachmentFiles.bind(this);
        this.submitSendEmail = this.submitSendEmail.bind(this);
        this.resetCheckedItems = this.resetCheckedItems.bind(this);
        this.redirectRequestDetail = this.redirectRequestDetail.bind(this);
        this.initSearchData = this.initSearchData.bind(this);

        let that = this;
        setTimeout(function () {
            //get init filter data for select field
            that.initFilterData();
            RKfuncion.select2.init();
        }, 100);

        $('body').on('change', '.filter-field', function () {
            let elThis = $(this);
            let key = elThis.attr('data-key');
            let key2 = elThis.attr('data-key2') || null;
            let tabName = elThis.attr('data-tab') || '_all_';
            let refresh = elThis.attr('data-refresh') || 0;
            let value = elThis.val();
            let params = ['filter', key];
            if (key2) {
                params.push(key2);
            }

            let {filterData, inited} = that.state;
            if (tabName == '_all_') {
                $.each(filterData, function (tab, filter) {
                    that.setFilterData(tab, params, value);
                });
            } else {
                that.setFilterData(tabName, params, value);
            }
            that.setState({
                filterData: filterData
            });

            if (refresh == 1 || (refresh == 2 && inited)) {
                that.getCollection();
            }
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

        $('body').on('change', '#filter_request_id', function () {
            let requestId = $(this).val();
            let {filterData} = that.state;
            let changeState = {currRequestId: requestId};
            if (!requestId) {
                that.setState(changeState);
                setTimeout(function () {
                    that.getCollection();
                }, 100);
                return;
            }
            //reset mail content
            $('#email_template').val('blank').trigger('change');

            changeState.loadingRequest = true;
            that.setState(changeState);

            $.ajax({
                url: pageParams.urlGetRequestFilter,
                type: 'GET',
                data: {
                    id: requestId,
                },
                success: function (res) {
                    $.each(filterData, function (tab, filter) {
                        that.setFilterData(tab, ['filter', 'except', 'dev_types'], res.type);
                        that.setFilterData(tab, ['filter', 'except', 'positions'], res.position);
                        that.setFilterData(tab, ['filter', 'except', 'prog_ids'], res.progIds);
                    });
                    setTimeout(function () {
                        that.getCollection();
                    }, 100);
                },
                error: function (error) {
                    Helper.alterError(error);
                },
                complete: function () {
                    that.setState({loadingRequest: false});
                },
            });
        });
    }

    /*
     * init select filter data
     */
    initFilterData(elSelect = null) {
        elSelect = elSelect ? elSelect : $('.filter-field');
        let {filterData} = this.state;
        let that = this;
        elSelect.each(function () {
            let elThis = $(this);
            let tabName = elThis.attr('data-tab') || '_all_';
            let key = elThis.attr('data-key');
            let key2 = elThis.attr('data-key2') || null;
            let keys = ['filter', key];
            if (key2) {
                keys.push(key2);
            }
            let value = '';
            if (tabName == '_all_') {
                $.each(filterData, function (tab, filter) {
                    value = that.getFilterData(tab, keys);
                });
            } else {
                value = that.getFilterData(tabName, keys);
            }
            $(this).val(value);
        });
    }

    /*
     * set filter collection data
     */
    setFilterData(tab, params = ['filter'], value, refresh = false) {
        if (params.length < 1) {
            return;
        }
        let {filterData} = this.state;
        if (typeof filterData[tab] == 'undefined') {
            return;
        }
        if (params[0] == 'orderby') {
            let oldVal = this.getFilterData(tab, params);
            if (!oldVal) {
                value = 'asc';
            } else {
                value = oldVal == 'asc' ? 'desc' : 'asc';
            }
            filterData[tab].orderby = {};
        }
        let evalStr = 'filterData[tab]';
        for (let i = 0; i < params.length; i++) {
            evalStr += '[\''+ params[i] +'\']';
            eval('if (typeof '+ evalStr +' == "undefined") { '+ evalStr +' = {}; }');
            eval(evalStr);
        }
        evalStr += '=value';
        eval(evalStr);

        this.setState({
            filterData: filterData
        });
        Helper.session.setRawItem('filterData', filterData, 'email_marketing');
        if (refresh) {
            this.getCollection();
        }
    }

    /*
     * set params for nested keys
     */
    setParams(keys, value, index = 0) {
        let data = [];
        let key = keys[index];
        if (index == (keys.length - 1)) {
            data[key] = value;
            return data;
        }
        data[key] = this.setParams(keys, value, index + 1);
        return data;
    }

    /*
     * get filter collection data
     */
    getFilterData(tab, keys = ['filter']) {
        let {filterData} = this.state;
        if (typeof filterData[tab] == 'undefined' || keys.length < 1) {
            return '';
        }
        let evalStr = 'filterData[tab]';
        let result = '';
        for (let i = 0; i < keys.length; i++) {
            evalStr += '[\''+ keys[i] +'\']';
            eval(
                'if (typeof '+ evalStr +' == "undefined") {'
                    + evalStr + ' = {};'
                    + 'result = "";'
                + '} else {'
                    + 'if (!' + evalStr + ') {'
                        + 'result = "";'
                    + '} else {'
                        + 'result = Object.keys(' + evalStr + ').length == 0 ? "" : ' + evalStr + ';'
                    + '}'
                + '}'
            );
        }
        return result;
    }

    /*
     * define press enter input filter
     */
    keyPressFilter(e) {
        let code = e.keyCode || e.charCode;
        //enter key
        if (code == 13) {
            this.getCollection();
        }
    }

    resetFilter(e) {
        e.preventDefault();
        let data = {
            sent: {
                is_sent: 1,
                page: 1,
                per_page: 50,
                filter: {},
            },
            not_send: {
                is_sent: 0,
                page: 1,
                per_page: 50,
                filter: {},
            },
        };
        this.setState({
            filterData: data
        });

        Helper.session.removeItem('filterData', 'email_marketing');
        this.getCollection(data);
    }

    /*
     * ajax get collection
     */
    getCollection(data = null) {
        let {searching, filterData, checkedItems} = this.state;
        if (searching) {
            return;
        }

        this.setState({
            searching: true,
            candidates: {
                sent: {},
                not_send: {}
            },
        });

        let that = this;
        $.ajax({
            type: 'GET',
            url: pageParams.urlGetCandidates,
            data: data ? data : filterData,
            success: function (response) {
                that.setState({
                    candidates: {
                        sent: response.sent,
                        not_send: response.not_send
                    },
                    currRequest: response.request,
                    currRequestId: response.request ? response.request.id : null,
                });

                setTimeout(function () {
                    that.initFilterData();
                    RKfuncion.select2.init();
                    $('.table').each(function () {
                        $(this).find('.check-item').each(function () {
                            if (checkedItems.indexOf($(this).val()) > -1) {
                                $(this).prop('checked', true);
                            }
                        });
                        $(this).find('.check-all-items')
                                .prop('checked', $(this).find('.check-item:checked').length > 0
                                        && $(this).find('.check-item').length == $(this).find('.check-item:checked').length);
                    });
                }, 100);
            },
            error: function (error) {
                Helper.alertError(error);
            },
            complete: function () {
                that.setState({
                    searching: false,
                    inited: true,
                });
            }
        });
    }

    /*
     * reset checked item
     */
    resetCheckedItems(e) {
        e.preventDefault();
        let {mailData} = this.state;
        mailData.candidate_ids = [];
        this.setState({
            mailData: mailData,
            checkedItems: [],
        });
        $('.table').each(function () {
            $(this).find('.check-all-items').prop('checked', false);
            $(this).find('.check-item').prop('checked', false);
        });
    }

    /*
     * onclick preview to send email
     */
    previewSendEmail() {
        let {checkedItems, mailData, filterData} = this.state;
        let requestId = filterData.not_send.filter.except.request_id;
        if (typeof requestId == 'undefined' || !requestId || (typeof requestId == 'object' && Object.keys(requestId).length < 1)) {
            bootbox.alert({
                className: 'modal-danger',
                message: Helper.trans('Please choose resource request'),
            })
            return false;
        }

        $('#perview_send_mail').modal('show');
        mailData.candidate_ids = checkedItems;
        this.setState({
            mailData: mailData
        });
    }

    /*
     * change file in mailData
     */
    handleChangeMailData(field, value) {
        let {mailData} = this.state;
        mailData[field] = value;
        this.setState({
            mailData: mailData
        });
    }

    /*
     * add attachment files
     */
    changeAttachmentFiles(e) {
        let {mailData} = this.state;
        let files = e.target.files;
        let totalSize = 0;
        let aryFiles = [];
        for (let i = 0; i < files.length; i++) {
            aryFiles.push(files[i]);
            totalSize += files[i].size / 1024;
        }
        if (totalSize > pageParams.MAX_FILE_SIZE) {
            bootbox.alert({
                className: 'modal-danger',
                message: Helper.trans('error_file_max_size', {
                    attribute: Helper.trans('Attachment files'),
                    max: pageParams.MAX_FILE_SIZE
                }),
            });
            return false;
        }
        mailData.attachment_files = aryFiles;
        this.setState({
            mailData: mailData
        });
    }

    /*
     * send email
     */
    submitSendEmail(e) {
        e.preventDefault();
        let {
            mailData,
            sendingMail,
            filterData,
            errorSendingMail,
        } = this.state;
        if (sendingMail) {
            return false;
        }

        mailData.content = CKEDITOR.instances['email_content'].getData();
        mailData.is_submit = true;
        this.setState({
            mailData: mailData
        });
        if (mailData.subject.trim() == '') {
            return false;
        }

        let that = this;
        if (!mailData.is_send_all && mailData.candidate_ids.length < 1) {
            bootbox.alert({
                className: 'modal-danger',
                message: Helper.trans('None item checked'),
            });
            return false;
        }
        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Are you sure want to sending email?'),
            callback: function (result) {
                if (result) {
                    let postData = new FormData();
                    let exceptKeys = ['attachment_files', 'candidate_ids', 'is_submit'];
                    //append key value
                    $.each(mailData, function (key, value) {
                        if (exceptKeys.indexOf(key) < 0) {
                            postData.append(key, value);
                        }
                    });
                    //append file
                    if (mailData.attachment_files.length > 0) {
                        for (let i = 0; i < mailData.attachment_files.length; i++) {
                            postData.append('files[]', mailData.attachment_files[i]);
                        }
                    }
                    //append candidate ids
                    if (mailData.candidate_ids.length > 0) {
                        for (let i = 0; i < mailData.candidate_ids.length; i++) {
                            postData.append('candidate_ids[]', mailData.candidate_ids[i]);
                        }
                    }
                    let requestId = filterData.not_send.filter.except.request_id;
                    postData.append('request_id', typeof requestId != 'undefined' ? requestId : null);
                    if (mailData.is_send_all) {
                        filterData = Helper.filterEmptyData(filterData);
                        postData.append('filterData', JSON.stringify(filterData));
                    }
                    postData.append('_token', pageParams._token);

                    $.ajax({
                        type: 'POST',
                        url: pageParams.urlSendMail,
                        processData: false,
                        contentType: false,
                        data: postData,
                        success: function (response) {
                            bootbox.alert({
                               className: 'modal-success',
                               message: response.message,
                            });
                            mailData.candidate_ids = [];
                            that.setState({
                                mailData: mailData,
                                checkedItems: [],
                            });
                            that.getCollection();
                        },
                        error: function (error) {
                            bootbox.alert({
                                className: 'modal-danger',
                                message: error.responseJSON,
                            });
                        },
                        complete: function () {
                            that.setState({
                                sendingMail: false
                            });
                        }
                    });
                }
            }
        });
    }

    /*
     * click and redirect to request detail page
     */
    redirectRequestDetail(e) {
        e.preventDefault();
        let {currRequestId} = this.state;
        let url = pageParams.urlRequestDetail + '/' + currRequestId;
        window.open(url, '_blank');
    }

    initSearchData(e) {
        e.preventDefault();
        this.getCollection();
    }

    render() {
        let {
            candidates,
            currRequestId,
            currRequest,
            searching,
            inited,
            mailData,
            checkedItems,
            loadingRequest,
        } = this.state;
        let {
            positions,
            devTypes,
            programingLanguages
        } = pageParams;

        return (
            <React.Fragment>
            <div className="box box-rikkei">
                <div className="box-header with-border">
                    <h3 className="box-title">{Helper.trans('Search')}</h3>
                </div>
                <div className="box-body">
                    <div className="filter-box">
                        <div className="row request-select">
                            <div className="col-sm-4 form-group">
                                <label>{Helper.trans('Request')}</label>
                                <div className="row">
                                    <div className="col-xs-12">
                                        <select className="form-control select-search filter-field" id="filter_request_id"
                                            data-key="except" data-key2="request_id" data-tab='_all_' data-refresh="3"
                                            data-remote-url={pageParams.urlSearchRequest}
                                            data-options='{"minimumInputLength":0,"allowClear":true,"placeholder":"Request"}'>
                                            {currRequest ? (
                                                <option value={currRequest.id}>{currRequest.title}</option>
                                            ) : null}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div className="col-sm-4 form-group">
                                <div><label>&nbsp;</label></div>
                                <button className="btn btn-success" disabled={!currRequestId}
                                    onClick={(e) => this.redirectRequestDetail(e)}>
                                    {loadingRequest ? (
                                        <span><i className="fa fa-spin fa-refresh"></i> &nbsp;</span>
                                    ) : null}
                                    {Helper.trans('Detail')}
                                </button>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-sm-4 form-group">
                                <label>{Helper.trans('Position')}</label>
                                <div className="row">
                                    <div className="col-xs-12">
                                        <select className="form-control select-search has-search filter-field" multiple
                                            data-key="except" data-key2="positions" data-tab="_all_" data-refresh="2"
                                            disabled={loadingRequest}>
                                            {Object.keys(positions).map((value, index) => (
                                                <option value={value} key={index}>{positions[value]}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div className="col-sm-4 form-group">
                                <label>{Helper.trans('Type')}</label>
                                <select className="form-control select-search filter-field" multiple
                                    data-key="except" data-key2="dev_types" data-tab="_all_" data-refresh="2"
                                    disabled={loadingRequest}>
                                    {Object.keys(devTypes).map((value, index) => (
                                        <option value={value} key={index}>{devTypes[value]}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="col-sm-4 form-group">
                                <label>{Helper.trans('Programing language')}</label>
                                <select className="form-control select-search has-search filter-field" multiple
                                    data-key="except" data-key2="prog_ids" data-tab="_all_" data-refresh="2"
                                    disabled={loadingRequest}>
                                    {Object.keys(programingLanguages).map((value, index) => (
                                        <option value={value} key={index}>{programingLanguages[value]}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                        <div className="text-right group-buttons">
                            <button className="btn btn-info" disabled={!inited}
                                onClick={this.previewSendEmail}><i className="fa fa-send"></i> {Helper.trans('Send email')}</button>
                            <button className="btn btn-primary margin-left-10" onClick={this.resetCheckedItems}>{Helper.trans('Reset checked')}</button>
                            <button className="btn btn-primary margin-left-10" id="reset_filter_button"
                                onClick={this.resetFilter}>{Helper.trans('Reset filter')}</button>
                            <button className="btn btn-primary margin-left-10" id="search_button"
                                onClick={(e) => this.getCollection()}>
                                {Helper.trans('Search')}
                                {searching ? (
                                    <span>&nbsp; <i className="fa fa-spin fa-refresh"></i></span>
                                ) : null}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <CandidateList
                inited={inited}
                candidates={candidates}
                searching={searching}
                setFilterData={this.setFilterData}
                getFilterData={this.getFilterData}
                getCollection={this.getCollection}
                keyPressFilter={this.keyPressFilter}
                checkedItems={checkedItems}
            />

            <PreviewSend
                mailData={mailData}
                handleChangeMailData={this.handleChangeMailData}
                changeAttachmentFiles={this.changeAttachmentFiles}
                submitSendEmail={this.submitSendEmail}
                currRequestId={currRequestId}
            />

            <button id="init_search_data" className="hidden" onClick={(e) => this.initSearchData(e)}></button>
            </React.Fragment>
        )
    }
}

if (document.getElementById('email_marketing_container')) {
    ReactDOM.render(<EmailMarketing />, document.getElementById('email_marketing_container'));
}



