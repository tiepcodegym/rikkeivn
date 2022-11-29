import React, {Component} from 'react';
import Helper from './../../helper';
import MeService from './../me-service';

export default class CommentModal extends Component {

    constructor(props) {
        super(props);
        let {setMainState} = this.props;
        this.state = {
            isShow: false,
            modalStyle: {
                top: 0,
                left: 0,
            }
        };

        //disable context menu for point and note cell
        $('body').on('contextmenu', '.point_group, .note_group', function (e) {
            e.preventDefault();
            return false;
        });

        let that = this;
        let xhrLoadComment = null;
        /*
        * right mouse click load comment ME attributes
        */
        $('body').on('mousedown', '.point_group, .note_group', function (e) {
            if (e.which != 3) {
                return;
            }
            let elThis = $(this);
            setTimeout(function () {
                that.setState({isShow: true});

                let elPopup = $('#me_comment_modal');
                let elParent = $('.content-container .box:first');
                let elOffset = Helper.getOffsetParent(elThis, elParent);
                var px = elOffset.left;
                var py = elOffset.top;
                if (px + elPopup.width() + elThis.width() + 10 >= elParent.width()) {
                    px = px - elPopup.width();
                } else {
                    px = px + elThis.width() + 10;
                }
                let meResTbl = $('._me_table_responsive');
                elPopup.attr('data-left', px)
                        .attr('data-scroll', meResTbl.scrollLeft())
                        .attr('data-scroll-top', meResTbl.scrollTop())
                        .attr('data-top', py + meResTbl.scrollTop());
                that.setState({
                    modalStyle: {
                        top: py + 'px',
                        left: px + 'px',
                    }
                });
            }, 200);

            if (xhrLoadComment) {
                xhrLoadComment.abort();
            }

            let evalId = elThis.closest('tr').attr('data-eval');
            let attrId = elThis.attr('data-attr') || null;
            let commentType = pageParams.CM_TYPE_COMMENT;
            if (elThis.hasClass('note_group')) {
                commentType = pageParams.CM_TYPE_NOTE;
            }
            let currCommentAttr = {
                evalId: evalId,
                attrId: commentType == pageParams.CM_TYPE_NOTE ? null : attrId,
                commentType: commentType,
                commentText: '',
            };

            setMainState({
                currCommentAttr: currCommentAttr,
                attrComments: {
                    data: [],
                    next_page_url: null,
                },
            });

            xhrLoadComment = that.getComments(null, currCommentAttr);
        });

        this.handleChangeCommentText = this.handleChangeCommentText.bind(this);
        this.handleGetComments = this.handleGetComments.bind(this);
        this.removeComment = this.removeComment.bind(this);
        this.closeModal = this.closeModal.bind(this);
    }

    closeModal(e) {
        e.preventDefault();
        this.setState({isShow: false});
        let {
            currCommentAttr,
            pointMustComment,
            evalPoints,
            setMainState,
            originPoints,
            attributes,
        } = this.props;
        let {evalId, attrId} = currCommentAttr;
        let dataState = {
            currCommentAttr: {
                evalId: null,
                attrId: null,
                commentType: null,
                commentText: '',
            },
            pointMustComment: {},
        }
        if (attrId && typeof pointMustComment != 'undefined'
            && typeof pointMustComment[evalId] != 'undefined'
            && typeof pointMustComment[evalId][attrId] != 'undefined'
            && typeof evalPoints[evalId] != 'undefined'
            && typeof evalPoints[evalId].attr_points[attrId] != 'undefined') {
            evalPoints[evalId].attr_points[attrId] = MeService.getPoint(originPoints, evalId, attrId);
            evalPoints[evalId].sumary = MeService.calSumaryPoint(evalPoints[evalId].attr_points, attributes);
            dataState.evalPoints = evalPoints;
            dataState.avgAttrPoints = MeService.calAvgAttrPoints(evalPoints, attributes);
        }
        setMainState(dataState);
        this.setState({
            modalStyle: {}
        });
    }

    componentDidMount() {
        let that = this;
    }

    /*
     * handle click get more comments
     */
    handleGetComments (e, url, append = true) {
        e.preventDefault();
        let {currCommentAttr} = this.props;
        return this.getComments(url, currCommentAttr, append);
    }

    /*
     * loading comments
     */
    getComments (url = null, currCommentAttr = {}, append = false) {
        if (!url) {
            url = pageParams.urlLoadComment;
        }
        let {attrComments, setMainState} = this.props;

        setMainState({loadingComment: true});
        return $.ajax({
            type: 'GET',
            url: url,
            data: {
                eval_id: currCommentAttr.evalId,
                attr_id: currCommentAttr.attrId,
                comment_type: currCommentAttr.commentType,
            },
            success: function (response) {
                let dataItems = attrComments.data.concat(response.data);
                attrComments = response;
                if (append) {
                    attrComments.data = dataItems;
                }
                setMainState({
                    attrComments: attrComments,
                });

                setTimeout(function () {
                    Helper.scrollBottom($('.me_comments_list'));
                }, 300);
            },
            error: function (error) {
                Helper.alertResError(error);
            },
            complete: function () {
                setMainState({loadingComment: false});
            },
        });
    }

    getAccountByEvalId(evalId, currUser = null) {
        if (currUser !== null) {
            return Helper.getNickName(currUser.email);
        }
        let {items} = this.props;
        let evalIds = $.isArray(evalId) ? evalId : [evalId];
        let results = [];
        for (let i = 0; i < evalIds.length; i++) {
            let item = Helper.findItemById(evalIds[i], items);
            if (item) {
                results.push(Helper.getNickName(item.email ? item.email : item.emp_email))
            }
        }
        return results.join(', ');
    }

    getAttrLabelById(attrId = null) {
        if (attrId == null) {
            return Helper.trans('Note');
        }
        let {attributes} = this.props;
        let attr = Helper.findItemById(attrId, attributes);
        if (!attr) {
            return '';
        }
        return attr.label;
    }

    /*
     * event add new comment
     */
    addComment(e) {
        e.preventDefault();
        let {
            currCommentAttr,
            savingComment,
            //checkedItems,
            attrComments,
            commentClasses,
            pointMustComment,
            attrsCommented,
            evalPoints,
            savePoint,
            calAvgAttrPoints,
            setMainState,
            calSumaryPoint,
        } = this.props;
        if (savingComment) {
            return;
        }
        if (!currCommentAttr.commentText.trim()) {
            Helper.alertError(Helper.trans('Comment is required'));
            return;
        }

        let currEvalId = currCommentAttr.evalId;
        let evalIds = [currEvalId];
        /*if (currCommentAttr.isReviewPage && currCommentAttr.commentType == pageParams.CM_TYPE_NOTE && checkedItems.length > 0) {
            evalIds = checkedItems;
        }*/
        let attrId = currCommentAttr.attrId;
        let dataRq = {
            _token: pageParams._token,
            eval_ids: evalIds,
            return_item: 1,
            attr_id: attrId,
            comment_text: currCommentAttr.commentText,
        };
        if (attrId == null) {
            dataRq.comment_type = pageParams.CM_TYPE_NOTE;
        }

        setMainState({
            savingComment: true,
        });
        $.ajax({
            type: 'POST',
            url: pageParams.urlAddComment,
            data: dataRq,
            success: function (response) {
                let idxIncr = 0;
                for (let evalId in response) {
                    let resItem = response[evalId];
                    let commentItem = resItem.comment_item;
                    commentItem.td_type = resItem.td_type;
                    commentItem.change_status = resItem.change_status;
                    //multiple evaluation but push only one comment
                    if (idxIncr == 0) {
                        attrComments.data.push(commentItem);
                    }

                    if (typeof commentClasses[evalId] == 'undefined') {
                        commentClasses[evalId] = {};
                    }
                    let attrId = currCommentAttr.attrId || -1;
                    if (typeof commentClasses[evalId][attrId] == 'undefined') {
                        commentClasses[evalId][attrId] = [];
                    }
                    if (commentClasses[evalId][attrId].indexOf(resItem.td_type) < 0) {
                        if (commentClasses[evalId][attrId].indexOf('has_comment') < 0) {
                            commentClasses[evalId][attrId].push('has_comment');
                        }
                        commentClasses[evalId][attrId].push(resItem.td_type);
                    }
                    idxIncr++;
                }
                //set point must require comment
                if (attrId && typeof pointMustComment != 'undefined'
                        && typeof pointMustComment[currEvalId] != 'undefined'
                        && typeof pointMustComment[currEvalId][attrId] != 'undefined') {
                    if (typeof attrsCommented[currEvalId] == 'undefined') {
                        attrsCommented[currEvalId] = {};
                    }
                    if (typeof attrsCommented[currEvalId][attrId] == 'undefined') {
                        attrsCommented[currEvalId][attrId] = [];
                    }
                    let commentItem = response[currEvalId].comment_item;
                    if (commentItem) {
                        attrsCommented[currEvalId][attrId].push(commentItem.id);
                    }
                    //make change point
                    let attrPoint = pointMustComment[currEvalId][attrId];
                    if (typeof evalPoints[currEvalId] != 'undefined') {
                        let currAttrPoints = evalPoints[currEvalId].attr_points;
                        currAttrPoints[attrId] = attrPoint;
                        //set sumary
                        if (typeof calSumaryPoint != 'undefined') {
                            evalPoints[currEvalId].sumary = calSumaryPoint(currEvalId, currAttrPoints);
                        }
                        //cal avg point
                        if (typeof calAvgAttrPoints != 'undefined') {
                            setMainState({avgAttrPoints: calAvgAttrPoints(evalPoints)});
                        }
                        //set point and save
                        if (typeof savePoint != 'undefined') {
                            savePoint();
                        }
                    }
                    delete pointMustComment[currEvalId][attrId];
                }

                currCommentAttr.commentText = '';
                setMainState({
                    attrComments: attrComments,
                    currCommentAttr: currCommentAttr,
                    commentClasses: commentClasses,
                    attrsCommented: attrsCommented,
                    pointMustComment: pointMustComment,
                    evalPoints: evalPoints,
                });
                setTimeout(function () {
                    Helper.scrollBottom($('.me_comments_list'));
                }, 100);
            },
            error: function (error) {
                Helper.alertResError(error);
            },
            complete: function () {
                setMainState({
                    savingComment: false,
                });
            },
        });
    }

    /*
     * type comment
     */
    handleChangeCommentText(e) {
        let {setMainState, currCommentAttr} = this.props;
        currCommentAttr.commentText = e.target.value;
        setMainState({
            currCommentAttr: currCommentAttr,
        });
    }

    /*
     * remove comment
     */
    removeComment(e, commentId) {
        e.preventDefault();
        let {
            attrComments,
            deletingComment,
            setMainState,
            currCommentAttr,
            commentClasses,
            attrsCommented,
        } = this.props;
        if (deletingComment) {
            return;
        }

        bootbox.confirm({
            className: 'modal-warning',
            message: Helper.trans('Are you sure want to remove comment?'),
            callback: function (result) {
                if (result) {
                    setMainState({deletingComment: true});
                    $('#me_comment_' + commentId).addClass('processing');
                    $.ajax({
                        type: 'DELETE',
                        url: pageParams.urlDelComment + '/' + commentId,
                        data: {
                            _token: pageParams._token,
                            eval_id: currCommentAttr.evalId,
                            attr_id: currCommentAttr.attrId,
                        },
                        success: function (response) {
                            let commentIndex = Helper.findIndexById(commentId, attrComments.data);
                            if (commentIndex > -1) {
                                attrComments.data.splice(commentIndex, 1);
                            }
                            $('#me_comment_' + commentId).removeClass('processing');

                            let attrId = currCommentAttr.attrId || -1;
                            if (typeof commentClasses[currCommentAttr.evalId] != 'undefined'
                                    && typeof commentClasses[currCommentAttr.evalId][attrId] != 'undefined') {
                                let attrClass = response.td_class ? response.td_class : '';
                                attrClass = attrClass.split(' ');
                                commentClasses[currCommentAttr.evalId][attrId] = attrClass;
                            }
                            //remove current user commented
                            if (typeof attrsCommented[currCommentAttr.evalId] != 'undefined'
                                    && typeof attrsCommented[currCommentAttr.evalId][attrId] != 'undefined') {
                                let aryCommentedIds = attrsCommented[currCommentAttr.evalId][attrId];
                                let idxCommented = aryCommentedIds.indexOf(commentId);
                                if (idxCommented > -1) {
                                    aryCommentedIds.splice(idxCommented, 1);
                                }
                            }

                            setMainState({
                                attrComments: attrComments,
                                commentClasses: commentClasses,
                                attrsCommented: attrsCommented,
                            });
                        },
                        error: function (error) {
                            Helper.alertResError(error);
                        },
                        complete: function () {
                            setMainState({deletingComment: false});
                        },
                    });
                }
            }
        });
    }

    render() {
        let {
            currUser,
            attrComments,
            currCommentAttr,
            //checkedItems,
            pointMustComment,
            loadingComment,
            savingComment,
            deletingComment,
        } = this.props;

        let evalIds = [currCommentAttr.evalId];
        /*if (currCommentAttr.commentType == pageParams.CM_TYPE_NOTE && checkedItems.length > 0) {
            evalIds = checkedItems;
        }*/
        let attrId = currCommentAttr.attrId;
        let commentAccount = this.getAccountByEvalId(evalIds, typeof currUser != 'undefined' ? currUser : null);
        let commentAttr = this.getAttrLabelById(attrId);
        let showRequireComment = attrId && typeof pointMustComment != 'undefined'
                                    && typeof pointMustComment[currCommentAttr.evalId] != 'undefined'
                                    && typeof pointMustComment[currCommentAttr.evalId][attrId] != 'undefined';

        return (
            <div className={'panel panel-primary' + (this.state.isShow ? '' : ' hidden')} id="me_comment_modal"
                style={this.state.modalStyle}>

                <div className="panel-heading">
                    <button type="button" className="close" onClick={(e) => {this.closeModal(e)}}>
                        <span aria-hidden="true">×</span></button>
                    <h4 className="panel-title">{Helper.trans('Comment') + ': ' + commentAccount + ' - ' + commentAttr}</h4>
                </div>
                <div className="panel-body" style={{paddingBottom: '5px'}}>
                    {showRequireComment ? (
                        <p style={{color: '#00c0ef'}}>
                            <i>{Helper.trans('You must comment for this value')}</i>
                            <b className="text-red"> ({pointMustComment[currCommentAttr.evalId][attrId]})</b>
                        </p>
                    ) : null}
                    <div className="me_comment_form">
                        {attrComments.data.length > 0 ? (
                            <div className="me_comments_list me_new" style={{marginBottom: '20px'}}>
                            {attrComments.data.map((comment, commentKey) => {
                                if (comment.comment_type == pageParams.CM_TYPE_LATE_TIME) {
                                    let avatar = '/common/images/login-r.png';
                                    return (
                                        <div key={commentKey} id={'me_comment_' + comment.id}
                                            className={'media comment_item ' + comment.type_class}>
                                            <div className="media-left pull-left">
                                                <img className="_comment_avatar" src={avatar} alt="" />
                                            </div>
                                            <div className="media-body">
                                                <h4 className="media-heading">
                                                    <b>{Helper.trans('System')} </b> <span className="date"> {Helper.trans('at') + ' ' + comment.created_at}</span>
                                                </h4>
                                                <div className="comment_content text-blue">{comment.content}</div>
                                            </div>
                                        </div>
                                    )
                                } else {
                                    let avatar = comment.avatar_url ? comment.avatar_url : '/common/images/noavatar.png';
                                    return (
                                        <div key={commentKey} id={'me_comment_' + comment.id}
                                            className={'media comment_item ' + comment.type_class}>
                                            {pageParams.currUser.id == comment.employee_id ? (
                                                <button type="button" disabled={deletingComment} className="btn_del_comment" onClick={(e) => this.removeComment(e, comment.id)}>×</button>
                                            ) : null}
                                            <div className="media-left pull-left">
                                                <img className="_comment_avatar" src={avatar} alt="" />
                                            </div>
                                            <div className="media-body">
                                                <h4 className="media-heading">
                                                    <b>{comment.name} </b> <span className="date"> {Helper.trans('at') + ' ' + comment.created_at}</span>
                                                </h4>
                                                <div className="comment_content text-blue">{comment.content}</div>
                                            </div>
                                        </div>
                                    )
                                }
                            })}
                            </div>
                        ) : (
                            <React.Fragment>
                                {!loadingComment ? (
                                <p className="text-center">{Helper.trans('No comments')}</p>
                                ) : null}
                            </React.Fragment>
                        )}
                        {loadingComment ? (
                            <div className="_loading text-center" style={{marginBottom: '15px'}}><i className="fa fa-spin fa-refresh"></i></div>
                        ) : null}
                        {attrComments.next_page_url ? (
                            <div className="text-center margin-bottom-5">
                                <a href="#" className="_comment_loadmore"
                                    onClick={(e) => this.handleGetComments(e, attrComments.next_page_url, true)}>{Helper.trans('Load more')}</a>
                            </div>
                        ) : null}
                        <div className="comment-text-group">
                            <textarea type="text" className="form-control resize-none" rows="3"
                                value={currCommentAttr.commentText}
                                onChange={(e) => this.handleChangeCommentText(e)}
                                placeholder={Helper.trans('Comment')}></textarea>
                            <div className="text-right margin-top-5">
                                <button type="button" className="cancel-btn btn btn-default"
                                    onClick={(e) => this.closeModal(e)}
                                    style={{marginRight: '5px'}}>{Helper.trans('Cancel')}</button>
                                <button type="button" className="me_comment_submit btn btn-primary"
                                    disabled={savingComment}
                                    onClick={(e) => this.addComment(e)}>
                                    {Helper.trans('Comment')}
                                    {savingComment ? (
                                        <span>&nbsp; <i className="fa fa-spin fa-refresh"></i></span>
                                    ) : null}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}


