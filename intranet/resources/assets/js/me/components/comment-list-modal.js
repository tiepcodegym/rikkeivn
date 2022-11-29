import React, { Component } from 'react';
import Helper from './../../helper';

class CommentListModal extends Component {
    constructor(props) {
        super(props);

        this.state = {
            attr_10: "",
            attr_11: "",
            attr_11: "",
            attr_12: "",
            attr_13: "",
            attr_14: "",
        };
        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }
    
    handleChange(e) {
        this.setState({
            [e.target.name]: e.target.value
        });
    }

    handleSubmit(event) {
        event.preventDefault();
        let checkSubmit = true;
        let checkedItems = this.props.checkedItems;

        if (checkedItems.length <= 0) {
            checkSubmit = false;
        }

        if (checkedItems) {
            var el_this = $(this);
            $.ajax({
                url: pageParams.urlAddListComment,
                type: 'POST',
                data: {
                    _token: pageParams._token,
                    eval_ids: this.props.checkedItems,
                    data: this.state,
                },
                cache: false,
                success: function(result) {
                    if (result.length <= 0) {
                        return;
                    }
                    var _me_table = $('#_me_table');
                    for (var i in result) {
                        var data = result[i];
                        var tr = _me_table.find('tbody tr[data-eval="' + data.id + '"]');
                        let commentItem = data.comment_item;
                        let attr_id = commentItem.attr_id;
                        if (attr_id) {
                            tr.find('td[data-attr="'+ attr_id +'"]').addClass('has_comment ' + data.td_type);
                            tr.find('td[data-attr="'+ attr_id +'"]').data('current-commented', 1);
                        } else {
                            tr.find('td.note_group').addClass('has_comment ' + data.td_type);
                        }
                        
                        var can_change = data.change_status;
                        if (can_change.approved || can_change.closed) {
                            tr.find('._btn_accept').prop('disabled', false);
                        } else {
                            tr.find('._btn_accept').prop('disabled', true);
                        }
                        if (can_change.feedback) {
                            tr.find('._btn_feedback').removeClass('is-disabled');
                        } else {
                            tr.find('._btn_feedback').addClass('is-disabled');
                        }
                    }

                    $('.show-modal-list-cm').click();
                },
                error: function(err) {
                    $('.show-modal-list-cm').click();
                    _showStatus(err.responseJSON, true);
                    el_this.prop('disabled', false);
                },
            });
        }
    }
    render() {
        let {checkedItems, attributes} = this.props;
        return (
            <div>
                <div id="modalListComment" className="modal fade modalListComment" role="dialog">
                    <form onSubmit={this.handleSubmit}>
                        <div className="modal-dialog">
                            <div className="modal-content">
                                <div className="modal-header">
                                    <button type="button" className="close" data-dismiss="modal">×</button>
                                    <h3 className="modal-title text-center">Comment hàng loạt</h3>
                                </div>
                                <div className="modal-body">
                                    {attributes.map((attr, attrKey) => {
                                        return (
                                            <div  key={attrKey}>
                                                <label><b>{attr.label}</b></label>
                                                <textarea className="form-control"
                                                    rows={3}
                                                    name={"attr_" + attr.id}
                                                    onChange={this.handleChange}
                                                />
                                            </div>
                                        )
                                    })}
                                    <label><b>Nhận xét chung</b></label>
                                    <textarea className="form-control"
                                        rows={3}
                                        name={"attr_"}
                                        onChange={this.handleChange}
                                    />
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-default" data-dismiss="modal" style={{float: 'left'}}>Close</button>
                                    <button type="submit" className="btn btn-primary" style={{marginBottom: '5px'}}>Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        );
    }
}

export default CommentListModal;