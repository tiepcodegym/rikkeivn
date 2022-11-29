import React from 'react';
import Helper from './../helper';

const MeService = {
    //set evalPoints: {evalId: {attr_points: {}, sumary: 0}}
    setEvalPoints: function (attrPoints, calSumaryPoint = null, attributes = null) {
        let evalPoints = {};
        for (let evalId in attrPoints) {
            let dataEval = attrPoints[evalId];
            let evalAttrPoints = {};
            for (let i = 0; i < dataEval.length; i++) {
                evalAttrPoints[dataEval[i].attr_id] = dataEval[i].point;
            }
            evalPoints[evalId] = {
                attr_points: evalAttrPoints,
            };
            if (calSumaryPoint) {
                evalPoints[evalId].sumary = calSumaryPoint(evalId, evalAttrPoints, attributes);
            }
        }
        return evalPoints;
    },

    //clone attr_points of evalPoints
    cloneEvalPoints: function (evalPoints) {
        let originPoints = {};
        for (let evalId in evalPoints) {
            if (typeof originPoints[evalId] == 'undefined') {
                originPoints[evalId] = {};
            }
            originPoints[evalId].attr_points = {...evalPoints[evalId].attr_points};
        }
        return originPoints;
    },

    /*
     * render contribute label
     */
    renderContriLabel: function (evalId, evalPoints = null, sumary = null, month = null) {
        let listContriLabels = {};
        if (month <= pageParams.SEP_MONTH) {
            listContriLabels = pageParams.listOldContriLabels;
        } else {
            listContriLabels = pageParams.listContriLabels;
        }
        if (sumary === null) {
            sumary = typeof evalPoints[evalId] == 'undefined' ? 0 : evalPoints[evalId].sumary;
        }
        let label = '';
        for (let key in listContriLabels) {
            let aryPoints = key.split('-');
            if (parseInt(aryPoints[0]) <= sumary && sumary < parseInt(aryPoints[1])) {
                label = listContriLabels[key];
                break;
            }
        }
        return label;
    },

    renderOldMonthClass: function (month) {
        return month <= pageParams.SEP_MONTH ? ' text-yellow' : '';
    },

    /*
     * render status label
     */
    renderStatusLabel: function (status, updateType = null, label = null) {
        if (label !== null && typeof label != 'undefined') {
            return label;
        }
        let prefix = '';
        if (status == pageParams.STT_FEEDBACK && updateType !== null) {
            if (updateType == pageParams.LEADER_UPDATED) {
                prefix = 'Leader ';
            } else if (updateType == pageParams.COO_UPDATED) {
                prefix = 'COO ';
            } else {
                prefix = 'Staff ';
            }
        }

        let {listStatuses} = pageParams;
        if (typeof listStatuses[status] == 'undefined') {
            return '';
        }
        return prefix + listStatuses[status];
    },

    /*
     * render project type label
     */
    renderProjTypeLabel: function (projType = null) {
        if (!projType) {
            return 'Team';
        }

        if (typeof pageParams.listProjTypes[projType] == 'undefined') {
            return '';
        }
        return pageParams.listProjTypes[projType];
    },

    renderAttrCell: function (evalItem, attr, itemCommentClasses, that) {
        let {evalPoints, currCommentAttr} = that.props;
        let attrPoint = this.getPoint(evalPoints, evalItem.id, attr.id);
        let attrClass = typeof itemCommentClasses[attr.id] != 'undefined' ? itemCommentClasses[attr.id].join(' ') : '';
        if (currCommentAttr.evalId == evalItem.id && currCommentAttr.attrId == attr.id) {
            attrClass += ' highlight';
        }
        return (
            <td className={'point_group ' + attrClass} data-group={attr.group} data-attr={attr.id}
                title={Helper.trans('Right click to comment')}>
                <span className="_me_attr_point" data-attr={attr.id}>{attrPoint}</span>
            </td>
        )
    },

    //review show feedback
    isShowFeedback: function (status) {
        return (status == pageParams.STT_SUBMITED || status == pageParams.STT_CLOSED);
    },

    //review show approve
    isShowApprove: function (status) {
        return status == pageParams.STT_SUBMITED;
    },

    getStatusClass: function (status) {
        if (status == pageParams.STT_FEEDBACK) {
            return ' text-red';
        }
        if (status == pageParams.STT_CLOSED) {
            return ' text-green';
        }
        if (status == pageParams.STT_APPROVED) {
            return ' text-blue';
        }
        if (status == pageParams.STT_SUBMITED) {
            return ' text-yellow';
        }
        return '';
    },

    /*
     * caculate sumary point foreach evaluation
     */
    calSumaryPoint: function (attrPoints, attributes) {
        if (attrPoints === null) {
            return 0;
        }
        let sumary = 0;
        for (let i = 0; i < attributes.length; i++) {
            let attr = attributes[i];
            let key = attr.id;
            let attrPoint = typeof attrPoints[key] == 'undefined' ? 0 : attrPoints[key];
            attrPoint = attrPoint ? parseInt(attrPoint) : 0;
            sumary += attrPoint * parseInt(attr.weight) / 100;
        }
        return sumary.toFixed(2);
    },

    /*
     * cal average attribute point foreach attributes (col)
     */
    calAvgAttrPoints: function (evalPoints, attributes) {
        let avgAttrPoints = {};
        for (let i = 0; i < attributes.length; i++) {
            let attr = attributes[i];
            if (typeof avgAttrPoints[attr.id] == 'undefined') {
                avgAttrPoints[attr.id] = 0;
            }
            let totalItem = 0;
            for (let evalId in evalPoints) {
                let attrPoints = evalPoints[evalId].attr_points;
                if (typeof attrPoints != 'undefined') {
                    for (let attrId in attrPoints) {
                        if (attrId == attr.id) {
                            avgAttrPoints[attr.id] += parseInt(attrPoints[attrId]);
                        }
                    }
                }
                totalItem++;
            }
            avgAttrPoints[attr.id] = avgAttrPoints[attr.id] == 0 ? 0 : (avgAttrPoints[attr.id] / totalItem).toFixed(2);
        }
        return avgAttrPoints;
    },

    isCommented: function (evalId, attrId, attrsCommented) {
        if (typeof attrsCommented[evalId] == 'undefined') {
            return false;
        }
        if (typeof attrsCommented[evalId][attrId] == 'undefined') {
            return false;
        }
        return attrsCommented[evalId][attrId].length > 0;
    },

    savePoint: function (that) {
        let {
            timeoutSavePoint,
            xhrSavingPoint,
            evalPoints,
        } = that.state;

        if (timeoutSavePoint) {
            clearTimeout(timeoutSavePoint);
            if (xhrSavingPoint) {
                xhrSavingPoint.abort();
            }
        }

        let service = this;
        timeoutSavePoint = setTimeout(function () {
            that.setState({
                savingPoint: true,
            });
            xhrSavingPoint = $.ajax({
                type: 'POST',
                url: pageParams.urlSavePoint,
                data: {
                    _token: pageParams._token,
                    eval_points: evalPoints,
                },
                success: function () {
                    that.setState({
                        savedFirst: true,
                        originPoints: service.cloneEvalPoints(evalPoints),
                    });
                },
                error: function (error) {
                    that.alertError(error);
                },
                complete: function () {
                    that.setState({
                        savingPoint: false,
                        timeoutSavePoint: null,
                        xhrSavingPoint: null,
                        isNetworkOnline: window.navigator.onLine,
                    });
                },
            });
        }, 2000);

        that.setState({
            timeoutSavePoint: timeoutSavePoint,
        });
    },

    clearSavePoint(that) {
        let {
            timeoutSavePoint,
            xhrSavingPoint,
        } = that.state;
        if (timeoutSavePoint) {
            clearTimeout(timeoutSavePoint);
        }
        if (xhrSavingPoint) {
            xhrSavingPoint.abort();
        }
    },

    /*
     * event change attr point
     */
    handleChangePoint: function (evalId, attrId, point, that) {
        //check network online (connected)
        if (!window.navigator.onLine) {
            that.setState({
                isNetworkOnline: false
            });
            return;
        }
        let {
            evalPoints,
            attributes,
            pointMustComment,
            isNetworkOnline,
        } = that.state;
        //set network online
        if (!isNetworkOnline && window.navigator.onLine) {
            that.setState({
                isNetworkOnline: true
            });
        }
        if (typeof evalPoints[evalId] == 'undefined') {
            evalPoints[evalId] = {
                attr_points: {},
                sumary: 0,
            };
        }
        let attr = Helper.findItemById(attrId, attributes);
        let maxPoint = attr ? parseInt(attr.range_max) : pageParams.MAX_ATTR_POINT;
        let minPoint = attr ? parseInt(attr.range_min) : pageParams.MIN_ATTR_POINT;
        let isComment = that.isCommented(evalId, attrId);

        point = point ? parseInt(point) : 0;
        let elPoint = $('[data-eval="'+ evalId +'"] input[data-attr="'+ attrId +'"]');
        elPoint.val(point);
        if (point < minPoint) {
            point = minPoint;
        }
        if (point > maxPoint) {
            point = maxPoint;
        }
        let attrPoints = evalPoints[evalId].attr_points;
        attrPoints[attrId] = point;
        evalPoints[evalId].sumary = that.calSumaryPoint(evalId, attrPoints);

        that.setState({
            evalPoints: evalPoints,
            avgAttrPoints: that.calAvgAttrPoints(evalPoints),
        });

        //max point must be comment
        if (attr.type == pageParams.ATTR_TYPE_WORK_PERFORM && !isComment) {
            if (point == maxPoint || point == maxPoint - 1 || point == minPoint) {
                if (typeof pointMustComment[evalId] == 'undefined') {
                    pointMustComment[evalId] = {};
                }
                pointMustComment[evalId][attrId] = point;
                that.setState({pointMustComment: pointMustComment});

                let elGroup = $('[data-eval="'+ evalId +'"] [data-attr="'+ attrId +'"]');
                elGroup.trigger({
                    type: 'mousedown',
                    which: 3,
                });
                this.clearSavePoint(that);
                return;
            } else {
                if (typeof pointMustComment[evalId] != 'undefined' && typeof pointMustComment[evalId][attrId] != 'undefined') {
                    that.setState({pointMustComment: {}});
                }
            }
        }

        this.savePoint(that);
    },

    getPoint: function (evalPoints, evalId, attrId) {
        let attrPoints = typeof evalPoints[evalId] != 'undefined' ? evalPoints[evalId].attr_points : {};
        return typeof attrPoints[attrId] == 'undefined' ? 0 : attrPoints[attrId];
    },

    /*
     * calucate percentage foreach contribute levels
     */
    renderEditStatistics: function(items, evalPoints) {
        let {listContriLabels} = pageParams;
        let results = {};
        let totalItem = items.length;
        for (let key in listContriLabels) {
            if (typeof results[key] == 'undefined') {
                results[key] = 0;
            }
            if (totalItem == 0) {
                continue;
            }
            let aryPoints = key.split('-');
            let fromPoint = parseInt(aryPoints[0]);
            let toPoint = parseInt(aryPoints[1]);

            for (let i = 0; i < items.length; i++) {
                let itemId = items[i].id;
                let sumary = typeof evalPoints[itemId] != 'undefined' ? evalPoints[itemId].sumary : 0;
                if (fromPoint <= sumary && sumary < toPoint) {
                    results[key]++;
                }
            }
        }

        listContriLabels.total = Helper.trans('Total');
        results = {total: totalItem, ...results};
        return Object.keys(results).map((key, index) => {
            let percent = totalItem == 0 ? 0 : (results[key] / totalItem * 100).toFixed(0);
            return (
                <span key={index} className="per_gr">
                    <strong>{listContriLabels[key]}: </strong> 
                    {key == 'total' ? (
                        <span>{items.length}</span>
                    ) : (
                        <span>{results[key]} ({percent}%)</span>
                    )}
                </span>
            );
        });
    },

    //update status item
    updateStatus: function (id, status, type, that) {
        let {
            setMainState,
            items,
            updatingStatus
        } = that.props;
        if (updatingStatus) {
            return;
        }

        bootbox.confirm({
            className: 'modal-default',
            message: that.getMessageConfirm(status),
            callback: function (result) {
                if (result) {
                    setMainState({
                        currUpdateItem: id,
                        updatingStatus: true,
                    });
                    $.ajax({
                        type: 'POST',
                        url: pageParams.urlUpdatestatus,
                        data: {
                            _token: pageParams._token,
                            id: id,
                            status: status,
                            type: type,
                        },
                        success: function (res) {
                            if (res.success == 0) {
                                Helper.alertError(res.message);
                                return;
                            }

                            Helper.alertSuccess(res.message);
                            let itemIdx = Helper.findIndexById(id, items.data);
                            if (itemIdx > -1) {
                                let item = items.data[itemIdx];
                                item.status = status;
                                item.status_label = res.status_label;
                                items.data[itemIdx] = item;

                                setMainState({
                                    items: items,
                                    currUpdateItem: null,
                                });
                            }
                        },
                        error: function (error) {
                            Helper.alertResError(error);
                        },
                        complete: function () {
                            setMainState({
                                updatingStatus: false,
                            });
                        },
                    });
                }
            },
        });
    },

    scrollCommentPopup: function() {
        let elCommentPopup = $('#me_comment_modal');
        $('._me_table_responsive').on('scroll', function () {
            let scrollLeft = $(this).scrollLeft();
            let scrollTop = $(this).scrollTop();
            let cssLeft = parseInt(elCommentPopup.attr('data-left'));
            let cssTop = parseInt(elCommentPopup.attr('data-top'));
            let cmLeft = parseInt(elCommentPopup.attr('data-scroll'));
            elCommentPopup.css('left', (cssLeft - scrollLeft + cmLeft))
                    .css('top', cssTop - scrollTop);
        });
    },

    //check and return old version
    checkOldVersion: function (time, currProj = null, type = 'project') {
        let dateTime = new Date(time);
        let sepDateTime = new Date(pageParams.SEP_MONTH);
        if (dateTime.getTime() <= sepDateTime.getTime()) {
            let params = '', sepParam = '?';
            if (currProj) {
                if (type == 'project') {
                    params += '?project_id=' + currProj;
                } else {
                    params += '?team_id=' + currProj;
                }
                sepParam = '&';
            }
            params += sepParam + 'month=' + time + '&time=' + time;
            window.location.href = pageParams.urlOldMe + params;
            return;
        }
    },
};

export default MeService;


