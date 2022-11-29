(function(document, $, vis, RKExternal, RKVarPassGlobal) {
var trans = typeof globalTrans === 'undefined' ? {} : globalTrans,
varGlobalProj = typeof globalPassModule === 'undefined' ? {} : globalPassModule;
var rkWoAllocation = {
    dom: {},
    lang: {},
    memberType: {},
    members: {},
    memberKeyId: {},
    dataMember: {},
    groupsVis: {},
    itemsVis: {},
    project: false,
    teamMember: {},
    labelResource: '',
    timelineVis: {},
    optionVis: {},
    dataGeneral: {},
    htmlTeamAllocate: '',
    groupOrderData: {},
    init: function(data, dom) {
        if (typeof vis !== 'object') {
            return true;
        }
        var __this = this;
        __this.project = varGlobalProj.project;
        __this.getLabelResource();
        __this.dom = dom;
        __this.lang = data.lang;
        __this.memberType = data.type;
        __this.execTeamMember(data);
        __this.groupsVis = new vis.DataSet();
        __this.itemsVis = new vis.DataSet();
        __this.members = __this.memberGroupParent(data.member);
        __this.addItemsVis(__this.members);
        __this.opitonVis = {};
        __this.htmlTeamAllocate = $('[data-flag-dom="wo-team-allocate"]').html();
        $('[data-flag-dom="wo-team-allocate"]').remove();
        dom.html(__this.htmlTeamAllocate);
        __this.timelineVis = new vis.Timeline(document.getElementById('ta-vis'));
        __this.setOptionVis();
        __this.timelineVis.setOptions(__this.opitonVis);
        __this.timelineVis.setGroups(__this.groupsVis);
        __this.timelineVis.setItems(__this.itemsVis);
        __this.editAvai();
        __this.event();
        __this.action();
        __this.validateForm();
        __this.calTotalActualEffort();
        __this.getCurrentSkillNewEmp();
        $('[data-flag-dom="datetime-picker"]').datepicker({
            format: 'yyyy-mm-dd',
            weekStart: 1,
            todayHighlight: true,
            autoclose: true
        });
    },
    /**
     * group member follow: parent - child
     *
     * @param {object} member
     * @return {object}: format
     * {
     *      memberParentId: {
     *         parent: {dataMember},
     *         child: {dataMember}
     *      }
     * }
     */
    memberGroupParent: function(member) {
        var result = {},
        __this = this;
        $.each(member, function (i, v) {
            v.start_at = v.start_at.replace(/\s.*/, '');
            v.end_at = v.end_at.replace(/\s.*/, '');
            __this.memberKeyId[v.id] = v;
            if (v.parent_id && parseInt(v.status) !== 1) {
                if (typeof result[v.parent_id] === 'undefined') {
                    result[v.parent_id] = {};
                }
                result[v.parent_id].child = v;
            } else {
                if (typeof result[v.id] === 'undefined') {
                    result[v.id] = {};
                }
                result[v.id].parent = v;
            }
        });
        $.each(result, function (i, v) {
            var item;
            if (v.child) {
                item = v.child;
            } else {
                item = v.parent;
            }
            if (!__this.groupsVis.get(item.employee_id)) {
                __this.groupsVis.add({
                    id: item.employee_id,
                    content: item.name + '<br/>' +
                        __this.replaceEmailToAccount(item.email)
                        + __this.getTeamOfEmployee(item.employee_id)
                });
            }
        });
        return result;
    },
    /**
     * add item to vis timeliness lib
     *
     * @param {object} members
     * @return {object}
     */
    addItemsVis: function(members) {
        var __this = this;
        $.each(members, function(memberParentId, memberData) {
            __this.itemsVis.add(__this.addItemVis(memberData));
        });
    },
    /**
     * exec item to item vis
     */
    addItemVis: function (memberData) {
        var __this = this,
        item, 
        content = '', 
        title = '',
        className = '';
        if (memberData.child) {
            item = memberData.child;
        } else {
            item = memberData.parent;
        }
        if (varGlobalProj.status.sDelete.indexOf(parseInt(item.status)) > -1) {
            className = 's-delete';
        } else if (parseInt(item.status) !== 1) {
            className = 'not-approve';
        }
        content = item.flat_resource + ' ' + __this.labelResource 
            + ' &nbsp;|&nbsp; <span class="effort-number">'+ item.effort +'%</span>'
            + ' &nbsp;|&nbsp; <span class="position">'+ __this.getLabelMemberType(item.type) +'</span>';

        if (typeof IS_OPPORTUNITY == 'undefined' || !IS_OPPORTUNITY) {
            title += '<p><b>' + trans['Status'] + ':</b> ';
            item.status = parseInt(item.status);
            if ( item.status === 1) {
                title += trans['Approved'];
            } else if (varGlobalProj.status.sDelete.indexOf(item.status) > -1) {
                title += trans['Delete draft'];
            } else if (varGlobalProj.status.sEdit.indexOf(item.status) > -1) {
                title += trans['Edit draft'];
            } else {
                title += trans['Add draft'];
            }
            title += '</p>';
        }

        if (__this.isDiffValue(memberData, 'email')) {
            title += '<p><b>' + trans['Account'] + ':</b> ';
            title += '<span class="unapprove-value">'
                + __this.replaceEmailToAccount(item.email)
                + '</span> | <span class="approve-value">approved: '
                + __this.replaceEmailToAccount(memberData.parent.email)
                + '</span>';
        }
        title += '<p><b>' + trans['Position'] + ':</b> ';
        if (__this.isDiffValue(memberData, 'type')) {
            title += '<span class="unapprove-value">'
                + __this.getLabelMemberType(item.type)
                + '</span> | <span class="approve-value">approved: '
                + __this.getLabelMemberType(memberData.parent.type)
                + '</span>';
        } else {
            title += __this.getLabelMemberType(item.type);
        }
        title += '</p><p><b>' + 'Start date' + ':</b> ';
        if (__this.isDiffValue(memberData, 'start_at')) {
            title += '<span class="unapprove-value">'
                + item.start_at
                + '</span> | <span class="approve-value">approved: ' 
                + memberData.parent.start_at
                + '</span>';
        } else {
            title += item.start_at;
        }
        title += '</p><p><b>' + 'End date' + ':</b> ';
        if (__this.isDiffValue(memberData, 'end_at')) {
            title += '<span class="unapprove-value">'
                + item.end_at
                + '</span> | <span class="approve-value">approved: ' 
                + memberData.parent.end_at
                + '</span>';
        } else {
            title += item.end_at;
        }
        title += '</p><p><b>' + 'Effort' + '(%)</b>: ';
        if (__this.isDiffValue(memberData, 'effort')) {
            title += '<span class="unapprove-value">'
                + item.effort
                + '</span> | <span class="approve-value">approved: ' 
                + memberData.parent.effort + '</span>';
        } else {
            title += item.effort;
        }
        title += '</p><p><b>' + trans['Actual Effort'] + '(' + __this.labelResource + '):</b> ';
        if (__this.isDiffValue(memberData, 'flat_resource')) {
            title += '<span class="unapprove-value">'
                + item.flat_resource
                + '</span> | <span class="approve-value">approved: ' 
                + memberData.parent.flat_resource + '</span>';
        } else {
            title += item.flat_resource;
        }
        title += '</p>';
        if (item.prog_lang_ids) {
            title += '<p><b>PL:</b> ';
            if (__this.isDiffValue(memberData, 'prog_lang_ids')) {
                title += '<span class="unapprove-value">'
                    + __this.getLabelProLang(item.prog_lang_ids)
                    + '</span> | <span class="approve-value">approved: ' 
                    + __this.getLabelProLang(memberData.parent.prog_lang_ids)
                    + '</span>';
            } else {
                title += __this.getLabelProLang(item.prog_lang_ids);
            }
            title += '</p>';
        }
        var startAtMoment = moment(item.start_at);
        if (!__this.groupOrderData[item.employee_id]) {
            __this.groupOrderData[item.employee_id] = startAtMoment;
        } else {
            if (startAtMoment.isAfter(__this.groupOrderData[item.employee_id])) {
                __this.groupOrderData[item.employee_id] = startAtMoment;
            }
        }
        return {
            id: item.id,
            group: item.employee_id,
            start: item.start_at,
            end: item.end_at,
            content: content,
            title: title,
            className: className
        };
    },
    event: function() {
        var __this = this,
            flagShowPopup = false;
        __this.timelineVis.on('select', function(data) {
            if (Array.isArray(data.items) && data.items.length) {
                __this.memberId = data.items[0];
            } else if (isNaN(data.items)) {
                __this.memberId = null;
            } else {
                __this.memberId = data.items;
            }
            if (!__this.memberId) {
                return null;
            }
            __this.formEditMember();
            if (!flagShowPopup) {
                flagShowPopup = true;
                $('#modal-proj-member-edit').on('hide.bs.modal', function () {
                    __this.memberId = null;
                    __this.timelineVis.setSelection([]);
                    $('#ta-vis .vis-range.vis-selected').removeClass('vis-selected');
                });
            }
        });
        $(document).on('click', '[data-btn-action="woAddProjMember"]', function(event) {
            event.preventDefault();
            __this.memberId = null;
            __this.formEditMember();
        });
    },
    /**
     * get for mat date: YYYY-MM-DD
     *
     * @param {object} date
     * @return {string}
     */
    getFormatDate: function(date) {
        return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
    },
    /**
     * get label resource type
     *
     * @return {String}
     */
    getLabelResource: function() {
        var __this = this;
        if (__this.project.resource_type === MD_TYPE) {
            __this.labelResource = 'MD';
        } else {
            __this.labelResource = 'MM';
        }
        return __this.labelResource;
    },
    /**
     * get label member type
     *
     * @param {int} type
     * @return {String}
     */
    getLabelMemberType: function(type) {
        if (typeof this.memberType[type] !== 'undefined') {
            return this.memberType[type];
        }
        return 'Dev';
    },
    /**
     * get lang lable of items
     *
     * @param {string} langIds
     * @return {String}
     */
    getLabelProLang: function(langIds) {
        var result = '', __this = this;
        langIds = __this.getLangArray(langIds);
        $.each(langIds, function(i,v) {
            if (typeof __this.lang[v] !== 'undefined') {
                result += __this.lang[v] + ', ';
            }
        });
        return result.slice(0, -2);
    },
    /**
     * get lang array from string
     *
     * @param {string} langIdsString
     * @return {Array}
     */
    getLangArray: function(langIdsString) {
        if (!langIdsString) {
            return [];
        }
        return langIdsString.split('-');
    },
    /**
     * set option for vis timeliness
     */
    setOptionVis: function() {
        var __this = this;
        __this.opitonVis = {
            //groupOrder: 'content',
            groupOrder: function (a, b) {
                //     > 0 when a > b
                //     < 0 when a < b
                //       0 when a == b
                if (!__this.groupOrderData[a.id] || 
                    !__this.groupOrderData[b.id]
                ) {
                    return 0;
                }
                if (__this.groupOrderData[a.id].isAfter(__this.groupOrderData[b.id])) {
                    return 1;
                }
                return -1;
              },
            tooltip: {
                followMouse: false,
                overflowMethod: 'cap'
            },
            align: 'center',
            zoomMin: 864000000,
            editable: false
        };
    },
    /**
     * check diff value of parent and child
     *
     * @param {object} memberData
     * @param {string} type
     * @return {Boolean}
     */
    isDiffValue: function(memberData, type) {
        if (!memberData.child) {
            return false;
        }
        if (memberData.parent[type] == memberData.child[type]) {
            return false;
        }
        return true;
    },
    formEditMember: function() {
        var __this = this;
        var modalProjMember = $('#modal-proj-member-edit');
        if (!modalProjMember.length) {
            modalProjMember = $('#flag-modal-proj-member-edit');
            if (!modalProjMember.length) {
                return true;
            }
            modalProjMember.attr('id', 'modal-proj-member-edit');
            modalProjMember = __this.formInitData(modalProjMember);
        }
        __this.formRenderDataMember();
        __this.getCurrentSkill(modalProjMember);
        if ($('#currentSkills').val != null) {
            $('#currentSkills').val(null).trigger('change');
        }
        modalProjMember.modal('show');
    },

    getCurrentSkill: function(modalProjMember) {
        var _this = this;
        var empId = modalProjMember.find('#field-account').val();
        if (empId != null) {
            $.ajax({
                url: urlGetCurrentSkill,
                data: {
                    'empId': empId
                },
                type: 'POST',
                datatype: 'JSON',
                success: function (response) {
                    if (response) {
                        $("#currentSkills").empty();
                        _this.renderListSkill(response);
                    }
                },
                error: function (response) {
                    console.log("error");
                }
            });
        }
    },

    renderListSkill: function(currentSkill) {
        var crSkill = currentSkill.currentSkill;
        var tagData = currentSkill.tagData;
        $.each(typesSkill, function (index, value) {
            var html = '<optgroup label="'+ value.label +'">';
            $.each(crSkill[index], function (skill, skillData) {
                var skillData = skillData.tag_id;
                $.each(tagData[index], function (tag, dataTag) {
                    html +='<option value="'+ tag +'"';
                    if (tag === skillData) {
                        html += ' selected'
                    }
                    html +='>'+ dataTag +'</option>';
                })
            }) 
            html += '</optgroup>';
        $('#currentSkills').append(html);
        })
        $(".js-example-matcher-start").select2({
            width: '100%',
            matcher: function(params, data) {
                if ($.trim(params.term) === '') { return data; }
                if (typeof data.text === 'undefined') { return null; }
                var q = params.term.toLowerCase();
                if (data.text.toLowerCase().indexOf(q) > -1 || data.id.toLowerCase().indexOf(q) > -1) {
                    return $.extend({}, data, true);
                }
                return null;
            }
        }).prop('disabled', true);
    },

    getCurrentSkillNewEmp: function() {
        var empIdField = $('#field-account');
        var _this = this;
        var flagGetCurrentSkill = false;
        empIdField.on('change', function() {
            flagGetCurrentSkill = true;
            if ($('#currentSkills').val != null) {
                $('#currentSkills').val(null).trigger('change');
            }
            if ($('#modal-proj-member-edit').hasClass('in') === true && flagGetCurrentSkill === true) {
                var empId = empIdField.val();
                if (empId != null) {
                    $.ajax({
                        url: urlGetCurrentSkill,
                        data: {
                            'empId': empId
                        },
                        type: 'POST',
                        datatype: 'JSON',
                        success: function (response) {
                            if (response) {
                                $("#currentSkills").empty();
                                _this.renderListSkill(response);
                            }
                        },
                        error: function (response) {
                            console.log("error");
                        }
                    });
                }
            }
        })
    },
    /**
     * init data default
     *
     * @param {object} modalProjMember
     */
    formInitData: function(modalProjMember) {
        var __this = this;
        var html = '';
        // select position
        $.each(__this.memberType, function(i,v) {
            html += '<option value="'+i+'">'+v+'</option>';
        });
        modalProjMember.find('#pm-position').html(html);
        // multiselect program language
        html = '';
        var langAvai = [];
        if ($('.frm-create-project select#prog_langs').length) {
            langAvai = $('.frm-create-project select#prog_langs').val();
            if (!langAvai) {
                langAvai = [];
            }
        } else {
            langAvai = Object.keys(RKVarPassGlobal.projLangs);
        }
        if (langAvai.length) {
            $.each(langAvai, function(i,v) {
                if (typeof __this.lang[v] !== 'undefined') {
                    html += '<option value="'+v+'">'+__this.lang[v]+'</option>';
                }
            });
            modalProjMember.find('#field-pl').html(html);
        }
        //label type resource
        modalProjMember.find('[data-proj-label="label-type"]').html(__this.labelResource);
        return modalProjMember;
    },
    /**
     * render data member for form
     */
    formRenderDataMember: function() {
        var __this = this,
            item = __this.memberKeyId[__this.memberId];
        //reset data null
        try { // set today active show
            $('[data-flag-dom="datetime-picker"]').datepicker('setDate', 'now');
        } catch (e) {}
        $('form#form-project-member [data-input-form]').val('').change();
        if (typeof __this.formValidate === 'object' &&
            typeof __this.formValidate.resetForm === 'function'
        ) {
            __this.formValidate.resetForm();
        }
        if (!item) { // add item
            return __this.formFillData({
                status: 1,
                flagAddEvent: true
            });
        }
        $('[data-flag-dom="modal-pm-title"]')
            .html($('[data-flag-dom="modal-pm-title"]').data('title-edit'));
        if ([
                RKVarPassGlobal.memberTypePm,
                RKVarPassGlobal.memberTypeSubPm,
                RKVarPassGlobal.memberTypeLeader,
                RKVarPassGlobal.memberTypeDev
            ].indexOf(parseInt(item.type)) === -1
        ) {
            item.program = [];
        } else if (!item.program) {
            item.program = __this.getLangArray(item.prog_lang_ids);
        } else {
            // nothing
        }
        __this.formFillData(item);
    },
    /**
     * form fill data
     */
    formFillData: function(item) {
        var __this = this,
            disabledForm;
        // exec button submit form
        if (varGlobalProj.status.sDelete.indexOf(parseInt(item.status)) > -1) {
            __this.dataGeneral.cancelItem = true;
            // cancel delete item
            $('[data-flag-dom="btnSaveProjMember"]').addClass('hidden');
            $('[data-flag-dom="btnDeleteProjMember"]').addClass('hidden');
            $('[data-flag-del="revert"]').removeClass('hidden');
            disabledForm = true;
        } else { // normal
            __this.dataGeneral.cancelItem = false;
            $('[data-flag-dom="btnSaveProjMember"]').removeClass('hidden');
            $('[data-flag-dom="btnDeleteProjMember"]').removeClass('hidden');
            $('[data-flag-del="revert"]').addClass('hidden');
            disabledForm = false;
        }
        if (item.flagAddEvent) {
            $('[data-flag-dom="btnDeleteProjMember"]').addClass('hidden');
        }
        if (!varGlobalProj.editWOAvai) {
            disabledForm = true;
        }
        $('form#form-project-member [data-input-form]').each(function (i, v) {
            var name = $(v).data('input-form');
            $(v).prop('disabled', disabledForm);
            if (typeof item[name] === 'undefined' || !item[name]) {
                return true;
            }
            if (name === 'start_at' ||
                name === 'end_at'
            ) {
                $(v).datepicker('setDate', item[name].replace(/\s.*/, ''));
                $(v).datepicker('update');
                return true;
            }
            if (name !== 'employee_id') {
                $(v).val(item[name]).change();
                return true;
            }
            if (item.employee_id) {
                $(v).data('select2-more-name', item.name)
                    .data('select2-more-email', item.email);
                $(v).html('<option value="' + item.employee_id
                    + '">' + __this.replaceEmailToAccount(item.email) + ' (' + item.name +')' +'</option>')
                    .val(item.employee_id).change();
            }
        });
        __this.actionInputPL();
        $('form#form-project-member [data-input-form="id"]').prop('disabled', false);
    },
    // action more submit form
    action: function() {
        var __this = this;
        __this.dataMember = {};
        // call back before submit
        RKExternal.projMemberBeforeSubmit = function() {
            __this.dataMember = {};
            $('form#form-project-member [data-input-form]').each(function (i, v) {
                __this.dataMember[$(v).data('input-form')] = $(v).val();
            });
            if (__this.dataMember.program && __this.dataMember.program.length) {
                __this.dataMember.prog_lang_ids = __this.dataMember.program.join('-');
            } else {
                __this.dataMember.prog_lang_ids = "";
            }
            __this.dataMember.email = $('form#form-project-member [data-input-form="employee_id"]')
                .data('select2-more-email');
            __this.dataMember.name = $('form#form-project-member [data-input-form="employee_id"]')
                .data('select2-more-name');
        };
        // call back success after save member
        RKExternal.projMemberSaveSuccess = function(response) {
            if (response.isCheckShowSubmit) {
                $('.submit-workorder').removeClass('display-none');
            }
            if (typeof response.member !== 'object') {
                return true;
            }
            var groupIdCheck = null;
            __this.formFillData(response.member);
            var newMember = null,
                memberGroup;
            if (response.delete) {
                var itemRemove = __this.itemsVis.get(__this.dataMember.id);
                if (itemRemove) {
                    groupIdCheck = itemRemove.group;
                    __this.itemsVis.remove(__this.dataMember.id);
                }
            }
            if (response.delete && !response.approve) {
                // delete draft or delete unapprove => remove
                delete __this.memberKeyId[response.member.id];
                delete __this.members[response.member.id];
                if (response.member.parent_id) {
                    newMember = __this.memberKeyId[response.member.parent_id];
                    delete __this.members[newMember.id].child;
                }
            } else { // delete approve, add or update member
                newMember = $.extend(__this.dataMember, response.member);
                __this.memberKeyId[newMember.id] = newMember;
            }
            if (!newMember) {
                if (groupIdCheck) {
                    __this.checkGroupItems(groupIdCheck);
                }
                __this.calTotalActualEffort();
                __this.timelineVis.fit();
                return true;
            }
            if (newMember.parent_id) {
                if (typeof __this.members[newMember.parent_id] === 'undefined') {
                    __this.members[newMember.parent_id] = {};
                }
                __this.members[newMember.parent_id].child = newMember;
                memberGroup = __this.members[newMember.parent_id];
            } else {
                if (typeof __this.members[newMember.id] === 'undefined') {
                    __this.members[newMember.id] = {};
                }
                if (parseInt(newMember.status) === 1) {
                    if (__this.members[newMember.id].child &&
                        typeof __this.members[newMember.id].child.id !== 'undefined' &&
                        __this.itemsVis.get(__this.members[newMember.id].child.id)
                    ) {
                        __this.itemsVis.remove(__this.members[newMember.id].child.id);
                    }
                    delete __this.members[newMember.id].child;
                }
                __this.members[newMember.id].parent = newMember;
                memberGroup = __this.members[newMember.id];
            }
            __this.execTeamMember(response);
            // update or add group vis
            if (!__this.groupsVis.get(newMember.employee_id)) {
                __this.groupsVis.add({
                    id: newMember.employee_id,
                    content: newMember.name + '<br/>'
                        + __this.replaceEmailToAccount(newMember.email)
                        + __this.getTeamOfEmployee(newMember.employee_id)
                });
            }
            if (response.oldEmpId !== 'undefined' && response.oldEmpId) {
                __this.groupsVis.remove(response.oldEmpId);
            }
            // update or add item vis
            var itemVis = __this.addItemVis(memberGroup);
            if (__this.itemsVis.get(response.member.id)) {
                __this.itemsVis.update(itemVis);
            } else {
                if (newMember.parent_id) {
                    __this.itemsVis.remove(newMember.parent_id);
                }
                __this.itemsVis.add(itemVis);
            }
            __this.calTotalActualEffort();
            __this.timelineVis.fit();
        };
        // rewiere confirm no function
        RKExternal.confirm.no = function() {
            $('form#form-project-member').data('submit-noti', '');
            $('form#form-project-member input[name="isDelete"]').val(0);
            RKExternal.confirm.hide();
        };
        // action delete submit
        $(document).on('click', '[data-flag-dom="btnDeleteProjMember"]', function() {
            $('#form-project-member').validate().cancelSubmit = true;
            if ($(this).data('flag-del') === 'revert') {
                var message = $('form#form-project-member').data('cancel-delete-noti');
            } else {
                var message = $('form#form-project-member').data('delete-noti');
            }
            $('form#form-project-member').data('submit-noti', message);
            $('form#form-project-member input[name="isDelete"]').val(1);
        });
        // done ajax submit
        RKExternal.projMemberComplete = function() {
            $('form#form-project-member').data('submit-noti', '');
            $('form#form-project-member input[name="isDelete"]').val(0);
            $('#modal-proj-member-edit').modal('hide');
        };
    },
    /**
     * calculator actual effort
     */
    calTotalActualEffort: function() {
        var __this = this,
            taeApproved = 0,
            taeUn = 0,
            flagUn = false;
        $('[data-dom-flag="type-resource"]').text(__this.getLabelResource());
        $.each(__this.members, function (memberParentId, data) {
            // parent is approve
            if (data.parent && parseInt(data.parent.status) === 1) {
                taeApproved += parseFloat(data.parent.flat_resource);
            }
            // child is not delete
            if (data.child && 
                varGlobalProj.status.sDelete.indexOf(parseInt(data.child.status)) === -1
            ) {
                taeUn += parseFloat(data.child.flat_resource);
                flagUn = true;
            } else if (!data.child &&
                varGlobalProj.status.sDelete.indexOf(parseInt(data.parent.status)) === -1
            ) {
                taeUn += parseFloat(data.parent.flat_resource);
                if (parseInt(data.parent.status) !== 1) {
                    flagUn = true;
                }
            } else if (data.child){
                flagUn = true;
            } else {
                //nothing
            }
        });
        $('[data-dom-effort="approved"]').text(taeApproved.toFixed(2));
        if (flagUn) {
            $('[data-dom-effort="un"]').text(taeUn.toFixed(2));
            $('[data-dom-flag="tae-unapprove"]').removeClass('hidden');
        } else {
            $('[data-dom-flag="tae-unapprove"]').addClass('hidden');
        }
    },
    // edit available member
    editAvai: function() {
        // diable add button
        if (varGlobalProj.editWOAvai) {
            $('[data-btn-action="woAddProjMember"]').removeClass('hidden');
        } else {
            $('[data-btn-action="woAddProjMember"]').remove();
            $('[data-flag-dom="btnSaveProjMember"]').remove();
            $('[data-flag-dom="btnDeleteProjMember"]').remove();
            $('form#form-project-member [data-input-form]').prop('disabled', true);
        }
    },
    /**
     * validate form member edit
     */
    validateForm: function() {
        var __this = this;
        $(document).on('change', '[data-input-form="type"]', function() {
            __this.actionInputPL();
        });
        // method validate customer - programming language
        $.validator.addMethod("woMemberPL", function(value, element) {
            var valueType = $('[data-input-form="type"]').val();
            if (valueType && parseInt(valueType) === RKVarPassGlobal.memberTypeDev
                && (!value || !value.length)) {
                return false;
            }
            return true;
        }, 'This field is required.');
        $.validator.addMethod("woMemberEndat", function(value, element) {
            var valueStartMember = $('[data-input-form="start_at"]').val(),
            valueEndProj = $('#summary #end_at').val();
            if (value && valueStartMember && valueEndProj) {
                var valueEndMember = new Date(value);
                valueStartMember = new Date(valueStartMember);
                valueEndProj = new Date(valueEndProj);
                if (valueEndMember < valueStartMember || valueEndMember > valueEndProj) {
                    return false;
                }
            }
            return true;
        }, 'End at may not be least than start at of member and greater than end at of project');
        $.validator.addMethod("woMemberStartat", function(value, element) {
            var valueStartProj = $('#summary #start_at').val();
            if (value && valueStartProj) {
                var valueStartMember = new Date(value);
                valueStartProj = new Date(valueStartProj);
                if (valueStartMember < valueStartProj) {
                    return false;
                }
            }
            return true;
        }, 'Start at may not be least than start at of project');
        __this.formValidate = $('form#form-project-member').validate({
            rules: {
                'item[type]': {
                    required: true,
                },
                'item[employee_id]': {
                    required: true,
                },
                'item[start_at]': {
                    required: true,
                    date: true,
                    woMemberStartat: true,
                },
                'item[end_at]': {
                    required: true,
                    date: true,
                    woMemberEndat: true,
                },
                'item[effort]': {
                    required: true,
                    range: [0, maxEffort]
                },
                'item[prog_langs][]': {
                    woMemberPL: true,
                },
            },
        });
    },
    /**
     * active pl input
     */
    actionInputPL: function() {
        var __this = this;
        var value = $('[data-input-form="type"]').val();
        if (!value || [
                RKVarPassGlobal.memberTypeDev,
                RKVarPassGlobal.memberTypeLeader,
                RKVarPassGlobal.memberTypePm,
                RKVarPassGlobal.memberTypeSubPm
            ].indexOf(parseInt(value)) > -1
        ) {
            if (varGlobalProj.editWOAvai && !__this.dataGeneral.cancelItem) {
                $('[data-input-form="program"]').prop('disabled', false);
            }
        } else {
            $('[data-input-form="program"]').val('').change();
            $('[data-input-form="program"]').prop('disabled', true);
        }
    },
    /**
     * exec team member
     *
     * @param {object} data
     */
    execTeamMember: function(data) {
        if (!data.team) {
            return true;
        }
        var __this = this;
        $.each(data.team, function (i, v) {
            if (typeof __this.teamMember[v.employee_id] === 'undefined') {
                __this.teamMember[v.employee_id] = [];
            }
            if (__this.teamMember[v.employee_id].indexOf(v.team_id) === -1) {
                __this.teamMember[v.employee_id].push(v.team_id);
            }
        });
    },
    /**
     * get teams name of employee
     *
     * @param {type} employeeId
     * @return {String}
     */
    getTeamOfEmployee: function (employeeId) {
        var __this = this, teamText = '';
        if (!__this.teamMember[employeeId] ||
            !__this.teamMember[employeeId].length
        ) {
            return '';
        }
        $.each (__this.teamMember[employeeId], function (i, teamId) {
            if (!RKVarPassGlobal.teamPath[teamId] ||
                !RKVarPassGlobal.teamPath[teamId].data
            ) {
                return true;
            }
            teamText += RKVarPassGlobal.teamPath[teamId].data.name + ', ';
        });
        if (teamText) {
            return ' - ' + teamText.slice(0, -2);
        }
        return '';
    },
    /**
     * replace email to account
     */
    replaceEmailToAccount: function (email) {
        if (!email) {
            return '';
        }
        return email.replace(/@.*/, '');
    },
    /**
     * check group has item? not item=> remove
     *
     * @param {int} groupId
     * @return {Boolean}
     */
    checkGroupItems: function(groupId) {
        var __this = this;
        groupId = parseInt(groupId);
        if (!groupId || !__this.groupsVis.get(groupId)) {
            return true;
        }
        var isHas = false;
        $.each (__this.itemsVis._data, function (itemId, itemData) {
            if (parseInt(itemData.group) === groupId) {
                isHas = true;
                return false;
            }
        });
        if (isHas) {
            return true;
        }
        return __this.groupsVis.remove(groupId);
    }
};

window.rkWoAllocation = rkWoAllocation;
})(document, jQuery, vis, RKExternal, RKVarPassGlobal);
