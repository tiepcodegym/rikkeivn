(function ($, RKExternal) {
var globVar = typeof globVarPass === 'object' ? globVarPass : {};
var kpiExport = {
    init: function () {
        var that = this;
        that.initData();
        that.plugin();
        that.projIndex = [];
        that.domAver = {
            shortList: $('[data-dom-list="short-ave-team"]'),
            shortItemHtml: $('[data-dom-item="short-ave-team"]')[0].outerHTML,
            itemHtml: '',
            title: $('[data-dom-title="ave-team"]'),
        };
        that.renderLayoutAverFromProj();
        that.getLayoutHrIndex();
        that.domProj = {
            itemHtml: $('[data-dom-item="projs"]')[0].outerHTML,
            list: $('[data-dom-list="projs"]'),
        };
        that.projIndexCss = ['css_value', 'css_billable_mm'];
        that.renderDivisions();
        that.renderTeamParentReplace();
        // average detail
        that.averDetail = {
            title: $('[d-aver-detail="tab-title"]')[0].outerHTML,
            content: $('[d-aver-detail="tab-content"]')[0].outerHTML,
        };

        $('[data-dom-item="projs"]').remove();
        $('[data-dom-item="short-ave-team"]').remove();
        $('[data-dom-item="ave-team"]').remove();
        
        $('[d-dom-fg="btn-export"]').click(function (e) {
            e.preventDefault();
            that.exportKpi($(this));
        });
    },
    /**
     * init data 
     * - date time from to - default 6 month
     */
    initData: function () {
        var now = moment();
        $('input[name="to"]').val(now.format('YYYY-MM'));
        $('input[name="from"]').val(now.subtract('5', 'M').format('YYYY-MM'));
    },
    /**
     * plugin init
     */
    plugin: function () {
        // date picker
        $('[data-date-picker]').datepicker({
            format: 'yyyy-mm',
            useCurrent: false,
            viewMode:"months",
            minViewMode:"months",
        });
        // validate datetime
        $('#form-kpi-export').validate({
            rules: {
                from: {
                    required: true,
                },
                to: {
                    required: true,
                    greaterEqualThan: '[name="from"]'
                },
            },
            messages: {
                to: {
                    greaterEqualThan: 'Must be greater or equal than From'
                }
            }
        });
    },
    /**
     * render data - reset html
     */
    renderData: function (response) {
        var that = this;
        // reset data
        that.domProj.list.html('');
        that.domAver.title.nextAll().remove();
        // average summary
        $('[data-dom-item="short-ave-team"]').remove();
        // average detail
        $('[d-aver-detail="tab-title"]').remove();
        $('[d-aver-detail="tab-content"]').remove();
        // hr index
        that.hrOutTitle.domTr.html('');
        that.hrOutTitle.domTrTry.html('');
        $('[d-hr-out="body"]').remove();
        $('[d-hr-out-try="body"]').remove();
        $('[d-hr-out-all="body"]').remove();
        $('[d-hr-employee="out"]').remove();
        $('[d-hr-employee="join"]').remove();

        var projAverageData = that.renderProject(response.projs);
        that.renderAverage(projAverageData);
        that.renderAverageShort(projAverageData);
        that.renderAverageDetail(projAverageData);
        that.renderHrIndex(response);
        $('[data-fg-dom="kpi-result"]').removeClass('hidden');
        $('[d-dom-fg="btn-export"]').removeClass('hidden');
    },
    /**
     * render project
     */
    renderProject: function (projsData) {
        var that = this,
        projAverageData = $.extend(true, {}, that.projAverageData),
        incre = 0;
        $.each(projsData, function (projIndex, projData) {
            var html = that.domProj.itemHtml,
            newReg;
            // call cost index
            if (projData.type_mm == globVar.projEffortTypeMD) {
                projData.cost_bill = (projData.cost_bill / 20).toFixed(2);
                projData.cost_plan_total = (projData.cost_plan_total / 20).toFixed(2);
                projData.cost_plan_current = (projData.cost_plan_current / 20).toFixed(2);
                projData.cost_resource_total = (projData.cost_resource_total / 20).toFixed(2);
                projData.cost_resource_current = (projData.cost_resource_current / 20).toFixed(2);
                projData.cost_actual = (projData.cost_actual / 20).toFixed(2);
            }
            // cal quality index
            if (!projData.qua_number_defect) {
                projData.qua_number_defect = 0;
            }
            if (!projData.qua_number_leakage) {
                projData.qua_number_leakage = 0;
            }
            if (projData.qua_defect_value) {
                projData.effort_dev = parseFloat(projData.qua_number_defect / projData.qua_defect_value / 20).toFixed(2);
            } else {
                projData.effort_dev = '';
            }
            if (projData.effort_dev) {
                projData.qua_leakage_mm = parseFloat(projData.qua_number_leakage / projData.effort_dev).toFixed(2);
                projData.qua_defect_mm = parseFloat(projData.qua_number_defect / projData.effort_dev).toFixed(2);
            } else {
                projData.qua_leakage_mm = 0;
                projData.qua_defect_mm = 0;
            }
            // cal pm data
            projData.pm = projData.pm_name + ' - ' + (projData.pm_email ? projData.pm_email.replace(/@.*$/, '') : '');
            // cal type label
            projData.type_label = typeof globVar.projTypes[projData.type] !== 'undefined'
                ? globVar.projTypes[projData.type] : '';
            // cal date project
            projData.startMo = moment(projData.start_date);
            projData.endMo = moment(projData.end_date);
            projData.duaration_day = projData.endMo.diff(projData.startMo, 'days');
            // cal billable effect
            if (projData.css_value) {
                projData.css_billable_mm = parseFloat(projData.cost_bill * projData.css_value).toFixed(2);
            } else {
                projData.css_billable_mm = '';
            }
            projData.leakage_billable_mm = parseFloat(projData.cost_bill * projData.qua_number_leakage).toFixed(2);
            // cal avarage team
            var teams = [],teamsSplit = [];
            if (projData.team_ids) {
                teamsSplit = projData.team_ids.split('-');
                projData.division = that.getDivisionsName(teamsSplit);
                // add flag for teams
                $.each (teamsSplit, function (i, team) {
                    var teamNameTmp = that.getDivisionName(team);
                    // check project of team QA, PQA => not view
                    if (!teamNameTmp || /^p?qa$/gi.test(teamNameTmp)) {
                        return true;
                    }
                    teams.push(that.fgTeam(team));
                });
                if (!teams.length) {
                    return true;
                }
            } else {
                projData.division = '';
            }
            $.each (teams, function (i, team) {
                if (typeof projAverageData.team[team] === 'undefined') {
                    projAverageData.team[team] = {
                        data: {},
                        count: {},
                        aver: {},
                    };
                }
                // spec ptpm dn - remove after
                if (that.teamParent && that.teamParent[team] &&
                    typeof projAverageData.team[that.teamParent[team]] === 'undefined'
                ) {
                    projAverageData.team[that.teamParent[team]] = {
                        data: {},
                        count: {},
                        aver: {},
                    };
                } // end spec ptpm dn - remove after
            });
            incre++;
            projData.no = incre;
            $.each(projData, function (key, value) {
                newReg = new RegExp('\{' + key + '\}', 'gi');
                if (that.projIndex.indexOf(key) > -1) {
                    // sum of avarage team
                    var valueIndex;
                    if (!isNaN(value) && value) {
                        valueIndex = parseFloat(value);
                    } else {
                        valueIndex = 0;
                    }
                    if (that.projIndexCss.indexOf(key) > -1 && !valueIndex) {
                        valueIndex = '';
                    } else {
                        $.each (teams, function (i, team) {
                            if (typeof projAverageData.team[team].data[key] === 'undefined') {
                                projAverageData.team[team].data[key] = 0;
                            }
                            if (typeof projAverageData.team[team].count[key] === 'undefined') {
                                projAverageData.team[team].count[key] = 0;
                            }
                            projAverageData.team[team].data[key] += valueIndex;
                            projAverageData.team[team].count[key] += 1;
                            // spec ptpm dn - remove after
                            if (that.teamParent && that.teamParent[team]) {
                                var teamParent = that.teamParent[team];
                                if (typeof projAverageData.team[teamParent].data[key] === 'undefined') {
                                    projAverageData.team[teamParent].data[key] = 0;
                                }
                                if (typeof projAverageData.team[teamParent].count[key] === 'undefined') {
                                    projAverageData.team[teamParent].count[key] = 0;
                                }
                                projAverageData.team[teamParent].data[key] += valueIndex;
                                projAverageData.team[teamParent].count[key] += 1;
                            } // end spec ptpm dn - remove after
                        });
                        if (typeof projAverageData.company.data[key] === 'undefined') {
                            projAverageData.company.data[key] = 0;
                        }
                        if (typeof projAverageData.company.count[key] === 'undefined') {
                            projAverageData.company.count[key] = 0;
                        }
                        projAverageData.company.data[key] += valueIndex;
                        projAverageData.company.count[key] += 1;
                        projData.key = valueIndex;
                    }
                    value = valueIndex;
                }
                html = html.replace(newReg, value);
            });
            html = html.replace(/\{projbodystyle\}/gi, (incre%2===0) ? 'proj_body_even' : 'proj_body_odd');
            that.domProj.list.append(html);
            //exec data css_mm
            that.getDataCssMM(projAverageData, projData, teams);
        });
        return projAverageData;
    },
    getDataCssMM: function (projAverageData, projData, teams) {
        var that = this,
            keysCal = ['sum_css_mm', 'sum_leakage_mm', 'sum_bill_css', 'sum_bill_leakage'];
        $.each(teams, function (i, team) {
            that.getDataCssMMItem(projAverageData.team[team], projData, keysCal);
        });
        that.getDataCssMMItem(projAverageData.company, projData, keysCal);
    },
    getDataCssMMItem: function (v, projData, keysCal) {
        $.each(keysCal, function (m, key) {
            if (typeof v.data[key] === 'undefined') {
                v.data[key] = 0;
            }
        });
        // cal css mm when css_value > 0
        if (!projData.cost_bill) {
            projData.cost_bill = 0
        }
        if (projData.css_value) {
            v.data['sum_css_mm'] += parseFloat(projData.css_value) * parseFloat(projData.cost_bill);
            v.data['sum_bill_css'] += parseFloat(projData.cost_bill);
        }
        v.data['sum_leakage_mm'] += parseFloat(projData.qua_number_leakage) * parseFloat(projData.cost_bill);
        v.data['sum_bill_leakage'] += parseFloat(projData.cost_bill);
    },
    /**
     * render average index point
     */
    renderAverage: function (projAverageData) {
        var that = this,
        htmlAllItem = '',
        incre = 0;
        $.each(projAverageData.team, function (team, v) {
            incre++;
            htmlAllItem += that.renderAverageItem(v, team, incre);
        });
        incre++;
        htmlAllItem += that.renderAverageItem(projAverageData.company, globVar.labelCompany, incre);
        that.domAver.title.after(htmlAllItem);
    },
    /**
     * render average index point of team
     */
    renderAverageItem: function (v, team, incre) {
        var that = this,
        html = that.domAver.itemHtml,
        count = 0;
        if ($.isEmptyObject(v.data)) {
            $.each (that.projIndex, function (i, key) {
                var newReg = new RegExp('\{' + key + '\}', 'gi');
                html = html.replace(newReg, '');
                v.aver[key] = '';
            });
        } else {
            if (v.data.sum_bill_css) {
                v.data.css_mm = (v.data.sum_css_mm / v.data.sum_bill_css).toFixed(2);
            } else {
                v.data.css_mm = '';
            }
            if (v.data.sum_bill_leakage) {
                v.data.leakage_mm = (v.data.sum_leakage_mm / v.data.sum_bill_leakage).toFixed(2);
            } else {
                v.data.leakage_mm = '';
            }
            if (!v.data.css_value) {
                v.data.css_value = '';
                v.count.css_value = 0;
            }
            $.each(v.data, function (key, value) {
                var newReg = new RegExp('\{' + key + '\}', 'gi');
                var valueIndex;
                if (v.count[key]) {
                    valueIndex = (value / v.count[key]).toFixed(2);
                } else {
                    valueIndex = value;
                }
                html = html.replace(newReg, valueIndex);
                if (!count) {
                    count = v.count[key];
                }
                v.aver[key] = valueIndex;
            });
        }
        return html.replace(/\{proj_count\}/gi, count)
            .replace(/\{division\}/gi, that.getNameFg(team))
            .replace(/\{projbodystyle\}/gi, (incre%2===0) ? 'proj_body_even' : 'proj_body_odd');
    },
    /**
     * render average index point
     */
    renderAverageShort: function (projAverageData) {
        var that = this,
        htmlAllItem = '',
        incre = 0;
        $.each(projAverageData.team, function (team, v) {
            incre++;
            htmlAllItem += that.renderAverageShortItem(v, team, incre);
        });
        incre++;
        htmlAllItem += that.renderAverageShortItem(projAverageData.company, globVar.labelCompany, incre);
        that.domAver.shortList.prepend(htmlAllItem);
    },
    renderAverageShortItem: function (v, team, incre) {
        var that = this;
        var html = that.domAver.shortItemHtml;
        var dataShort = {
            division: that.getNameFg(team),
            cost_bill: v.data.cost_bill ? v.data.cost_bill : '',
            aver_cost_effi: v.aver.cost_effi,
            aver_cost_effectiveness: v.aver.cost_effectiveness,
            cost_resource_total: v.data.cost_resource_total ? v.data.cost_resource_total : '',
            aver_qua_defect_mm: v.aver.qua_defect_mm,
            aver_qua_leakage_mm: v.aver.qua_leakage_mm,
            aver_css_value: v.aver.css_value ? v.aver.css_value : '',
        };
        $.each(dataShort, function (key, value) {
            var newReg = new RegExp('\{' + key + '\}', 'gi');
            if (value && !isNaN(value)) {
                value = parseFloat(value).toFixed(2);
            }
            html = html.replace(newReg, value);
        });
        html = html.replace(/\{projbodystyle\}/gi, (incre%2===0) ? 'proj_body_even' : 'proj_body_odd');
        return html;
    },
    renderAverageDetail: function (projAverageData) {
        var that = this,
        htmlAllItem = {
            title: '',
            content: '',
        };
        that.renderAverageDetailItem(projAverageData.company, globVar.labelCompany, htmlAllItem);
        $.each(projAverageData.team, function (team, v) {
            that.renderAverageDetailItem(v, team, htmlAllItem);
        });
        $('[d-kpi-dom="tab-title"]').append(htmlAllItem.title);
        $('[d-kpi-dom="tab-content"]').append(htmlAllItem.content);
    },
    renderAverageDetailItem: function (v, team, htmlAllItem) {
        var that = this,
        htmlTitle = that.averDetail.title,
        htmlContent = that.averDetail.content;
        if ($.isEmptyObject(v.count)) {
            v.aver.evaluation = '';
        } else {
            v.aver.evaluation = that.getEvaluation(v.aver.sumary_point);
        }
        $.each(v.aver, function (key, value) {
            var newReg = new RegExp('\{' + key + '\}', 'gi');
            htmlContent = htmlContent.replace(newReg, value);
        });
        var idRand = '' + (new Date()).getTime() + (''+Math.random() * 1e10).substr(0, 6);
        // replace id, teamname
        htmlTitle = htmlTitle.replace(/\{id\}/gi, idRand)
            .replace(/\{division\}/gi, that.getNameFg(team));
        htmlContent = htmlContent.replace(/\{id\}/gi, idRand)
            .replace(/\{division\}/gi, that.getNameFg(team));
        htmlAllItem.title += htmlTitle;
        htmlAllItem.content += htmlContent;
    },
    /**
     * render html-data for hr index
     */
    renderHrIndex: function (response) {
        var that = this,
            periodsData = that.getPeriodMonth(),
            periods = periodsData.period,
            periodsMoment = periodsData.periodMoment,
            hrIndexData = {};
        that.lengPeriods = periodsData.length;
        $('[d-hr-colspan="count-month"]').attr('colspan', that.lengPeriods);
        $('[d-hr-dom="td-all"]').attr('colspan', that.lengPeriods * 4 + 2);
        that.renderHrOutTitleHtml(periods);
        // render count employee in, out
        that.renderHrOutBodyHtml(periods, {
            countEmplOutNotTried: that.execHrTeamMonth(response.hrOutNotTried),
            countEmplJoin: that.execHrTeamMonth(response.hrJoin),
            countEmplTeams: that.execHrTeamMonthStartEnd(response.hrTeams, periodsMoment),
            countEmplOutAll: that.execHrTeamMonth(response.hrOutAll),
            countEmplOutTry: that.execHrTeamMonth(response.hrOutTry),
        });
        // render employee detail in,out in period
        that.renderHrEmployeeOut(response.hrEmplInfo, that.hrEmployee.htmlBody, that.hrEmployee.titleOut);
        that.renderHrEmployeeOut(response.hrEmplJoinInfo, that.hrEmployee.htmlBodyJoin, that.hrEmployee.titleJoin);
    },
    /**
     * exec data team follow month: out, all
     */
    execHrTeamMonth: function (data) {
        var that = this,
            result = {
                company: {
                    sum: 0,
                },
            };
        $.each (data, function (i, v) {
            var fgTeam = v.team_id,
            count = parseInt(v.count);
            if (that.teamDev.indexOf(fgTeam) === -1) {
                fgTeam = 'other';
            }
            if (typeof result[fgTeam] === 'undefined') {
                result[fgTeam] = {
                    sum: 0,
                };
            }
            if (typeof result[fgTeam][v.month] === 'undefined') {
                result[fgTeam][v.month] = 0;
            }
            result[fgTeam][v.month] += count;
            result[fgTeam]['sum'] += count;
            // spec ptpm dn - remove after
            if (that.teamParent && that.teamParent[fgTeam]) {
                var teamParent = that.teamParent[fgTeam];
                if (typeof result[teamParent] === 'undefined') {
                    result[teamParent] = {
                        sum: 0,
                    };
                }
                if (typeof result[teamParent][v.month] === 'undefined') {
                    result[teamParent][v.month] = 0;
                }
                result[teamParent][v.month] += count;
                result[teamParent]['sum'] += count;
            } // end spec ptpm dn - remove after
            if (typeof result['company'][v.month] === 'undefined') {
                result['company'][v.month] = 0;
            }
            result['company'][v.month] += count;
            result['company']['sum'] += count;
        });
        return result;
    },
    /**
     * exec data count employee of team in each month
     */
    execHrTeamMonthStartEnd: function (data, periodsMoment) {
        var that = this,
            countEmplTeams = {
                company: {
                    sum: 0,
                },
            };
        $.each (data, function (i, v) {
            var fgTeam = v.team_id,
            count = parseInt(v.count),
            vStart, vEnd;
            if (that.teamDev.indexOf(fgTeam) === -1) {
                fgTeam = 'other';
            }
            if (typeof countEmplTeams[fgTeam] === 'undefined') {
                countEmplTeams[fgTeam] = {
                    sum: 0,
                };
            }
            // spec ptpm dn - remove after
            /*if (that.teamParent && that.teamParent[fgTeam]) {
                var teamParent = that.teamParent[fgTeam];
                if (typeof countEmplTeams[teamParent] === 'undefined') {
                    countEmplTeams[teamParent] = {
                        sum: 0,
                    };
                }
            } */// end spec ptpm dn - remove after
            if (v.start_at) {
                vStart = moment(v.start_at);
            }
            if (v.end_at) {
                vEnd = moment(v.end_at);
            }
            $.each(periodsMoment, function (j, periodM) {
                var monthFormat = periodM.format('YYYY-MM'),
                    isInPeriod = false;
                if (typeof countEmplTeams[fgTeam][monthFormat] === 'undefined') {
                    countEmplTeams[fgTeam][monthFormat] = 0;
                }
                if (typeof countEmplTeams['company'][monthFormat] === 'undefined') {
                    countEmplTeams['company'][monthFormat] = 0;
                }
                // start null, end null => employee of team forever
                if (!vStart && !vEnd) {
                    isInPeriod = true;
                } else if (!vStart && vEnd) { // start null
                    if (periodM.isSameOrBefore(vEnd)) {
                        isInPeriod = true;
                    }
                } else if (vStart && !vEnd) {// end null
                    if (periodM.isSameOrAfter(vStart)) {
                        isInPeriod = true;
                    }
                } else { // start and end not null
                    if (periodM.isSameOrAfter(vStart) && periodM.isSameOrBefore(vEnd)) {
                        isInPeriod = true;
                    }
                }
                if (isInPeriod) {
                    countEmplTeams[fgTeam][monthFormat] += count;
                    countEmplTeams['company'][monthFormat] += count;
                    // spec ptpm dn - remove after
                    /*if (that.teamParent && that.teamParent[fgTeam]) {
                        if (typeof countEmplTeams[teamParent][monthFormat] === 'undefined') {
                            countEmplTeams[teamParent][monthFormat] = 0;
                        }
                        countEmplTeams[teamParent][monthFormat] += count;
                    }*/ // end spec ptpm dn - remove after
                }
            });
        });
        // count sum = last month
        var lastMonth = periodsMoment.slice(-1).pop().format('YYYY-MM');
        $.each(countEmplTeams, function (teamId, dataM) {
            if (typeof countEmplTeams[teamId][lastMonth] === 'undefined') {
                countEmplTeams[teamId]['sum'] = 0;
            } else {
                countEmplTeams[teamId]['sum'] = countEmplTeams[teamId][lastMonth];
            }
        });
        return countEmplTeams;
    },
    /**
     * get period month from filter
     */
    getPeriodMonth: function () {
        var that = this,
            start = moment($('input[name="from"]').data('datepicker').dates[0]).startOf('month'),
            end = moment($('input[name="to"]').data('datepicker').dates[0]).startOf('month'),
            period = {},
            format = 'MM',
            length = 0,
            periodMoment = [];
        if (start.get('Y') !== end.get('Y')) {
            format = 'YYYY-MM';
        }
        that.startEndString = start.format('YYYY.MM') + '-' + end.format('YYYY.MM');
        do {
            period[start.format('YYYY-MM')] = start.format(format);
            periodMoment.push(moment(start));
            length++;
            if (start.isSameOrAfter(end)) {
                break;
            }
            start.add(1, 'months');
        } while(1);
        period['sum'] = 'Sum';
        length++;
        return {
            period: period,
            length: length,
            periodMoment: periodMoment,
        };
    },
    /**
     * render hr index title follow month period
     */
    renderHrOutTitleHtml: function (periods) {
        var that = this,
        htmlOut = '',
        htmlJoin = '',
        htmlSum = '',
        htmlDiff = '',
        classMore,
        index = 1,
        attrCss;
        $.each (periods, function (i, v) {
            attrCss = '';
            if (index === 1) {
                classMore = ' block-left';
                attrCss = 'data-xml-index="3"';
            } else if (index === that.lengPeriods) {
                classMore = ' block-right';
            } else {
                classMore = '';
            }
            classMore += ' block-bottom';
            htmlOut += that.hrOutTitle.htmlTried.replace(/\{month\}/gi, v)
                .replace(/\{class\}/gi, classMore)
                .replace(/\{attrcss\}/gi, attrCss);
            htmlJoin += that.hrOutTitle.htmlJoin.replace(/\{month\}/gi, v)
                .replace(/\{class\}/gi, classMore);
            htmlSum += that.hrOutTitle.htmlSum.replace(/\{month\}/gi, v)
                .replace(/\{class\}/gi, classMore);
            htmlDiff += that.hrOutTitle.htmlDiff.replace(/\{month\}/gi, v)
                .replace(/\{class\}/gi, classMore);
            index++;
        });
        that.hrOutTitle.domTr.html(htmlOut + htmlJoin + htmlSum);
        that.hrOutTitle.domTrTry.html(htmlOut + htmlJoin + htmlSum);
        that.hrOutTitle.domTrAll.html(htmlOut + htmlJoin + htmlSum + htmlDiff);
    },
    /**
     * render html hr index body follow month period
     */
    renderHrOutBodyHtml: function (periods, dataHrBody) {
        var that = this,
            htmlBody = '',
            htmlBodyTry = '',
            htmlBodyAll = '',
            lengthTeam = Object.keys(that.teamDevOther).length,
            incre = 0;
        $.each(that.teamDevOther, function (i, teamId) {
            incre++;
            var htmlOut = that.renderHrOutBodyPeriod(dataHrBody.countEmplOutNotTried, periods, teamId, that.hrOutBody.htmlTried),
            htmlOutTry = that.renderHrOutBodyPeriod(dataHrBody.countEmplOutTry, periods, teamId, that.hrOutBody.htmlTry),
            htmlOutAll = that.renderHrOutBodyPeriod(dataHrBody.countEmplOutAll, periods, teamId, that.hrOutBody.htmlAll),
            htmlJoin = that.renderHrOutBodyPeriod(dataHrBody.countEmplJoin, periods, teamId, that.hrOutBody.htmlJoin),
            htmlSum = that.renderHrOutBodyPeriod(dataHrBody.countEmplTeams, periods, teamId, that.hrOutBody.htmlSum),
            htmlDiff = that.renderHrOutDiffJoin(dataHrBody.countEmplOutAll, dataHrBody.countEmplJoin, periods, teamId),
            bodyClone = that.hrOutBody.body.replace(/\{team\}/gi, that.divisions[teamId]),
            bodyTryClone = that.hrOutBody.bodyTry.replace(/\{team\}/gi, that.divisions[teamId]),
            bodyAllClone = that.hrOutBody.bodyAll.replace(/\{team\}/gi, that.divisions[teamId]);
            bodyClone = $(bodyClone);
            bodyTryClone = $(bodyTryClone);
            bodyAllClone = $(bodyAllClone);
            // replace body out not tried
            bodyClone.find('[d-hr-count="out-tried"]').replaceWith(htmlOut);
            bodyClone.find('[d-hr-count="join"]').replaceWith(htmlJoin);
            bodyClone.find('[d-hr-count="sum"]').replaceWith(htmlSum);
            // replace body out try
            bodyTryClone.find('[d-hr-count="out-try"]').replaceWith(htmlOutTry);
            bodyTryClone.find('[d-hr-count="join"]').replaceWith(htmlJoin);
            bodyTryClone.find('[d-hr-count="sum"]').replaceWith(htmlSum);
            // replace body all
            bodyAllClone.find('[d-hr-count="out-all"]').replaceWith(htmlOutAll);
            bodyAllClone.find('[d-hr-count="join"]').replaceWith(htmlJoin);
            bodyAllClone.find('[d-hr-count="sum"]').replaceWith(htmlSum);
            bodyAllClone.find('[d-hr-count="diff"]').replaceWith(htmlDiff);

            // tail block
            if (incre === lengthTeam) {
                bodyClone.children('td').addClass('block-bottom');
                bodyAllClone.children('td').addClass('block-bottom');
            }
            htmlBody += bodyClone[0].outerHTML.replace(
                /\{turnoverate\}/gi,
                that.calTurnoverate(dataHrBody.countEmplOutNotTried, dataHrBody.countEmplTeams, teamId, periods)
            ).replace(/\{hrbodystyle\}/gi, (incre %2 === 0) ? 'hr_body_even' : 'hr_body_odd');
            htmlBodyTry += bodyTryClone[0].outerHTML.replace(
                /\{turnoverate\}/gi,
                that.calTurnoverate(dataHrBody.countEmplOutTry, dataHrBody.countEmplTeams, teamId, periods)
            ).replace(/\{hrbodystyle\}/gi, (incre %2 === 0) ? 'hr_body_even' : 'hr_body_odd');
            htmlBodyAll += bodyAllClone[0].outerHTML.replace(
                /\{turnoverate\}/gi,
                that.calTurnoverate(dataHrBody.countEmplOutAll, dataHrBody.countEmplTeams, teamId, periods)
            ).replace(/\{hrbodystyle\}/gi, (incre %2 === 0) ? 'hr_body_even' : 'hr_body_odd');
        });
        $('[d-hr-out="title"]').after(htmlBody);
        $('[d-hr-out-try="title"]').after(htmlBodyTry);
        $('[d-hr-out-all="title"]').after(htmlBodyAll);
    },
    /**
     * render html hr index body follow month period
     */
    renderHrOutBodyPeriod: function (countEmplOut, periods, teamId, htmlTried) {
        var that = this,
            htmlOut = '',
            index = 1;
        $.each(periods, function (periodKey, periodLabel) {
            var count = 0, classMore;
            if (typeof countEmplOut[teamId] !== 'undefined' &&
                typeof countEmplOut[teamId][periodKey] !== 'undefined'
            ) {
                count = countEmplOut[teamId][periodKey];
            }
            if (index === 1) {
                classMore = ' block-left';
                
            } else if (index === that.lengPeriods) {
                classMore = ' block-right';
            } else {
                classMore = '';
            }
            htmlOut += htmlTried.replace(/\{count\}/gi, count)
                .replace(/\{class\}/gi, classMore);
            index++;
        });
        return htmlOut;
    },
    /**
     * render html hr index diff out and join: join - out
     */
    renderHrOutDiffJoin: function (countEmplOutAll, countEmplJoin, periods, teamId) {
        var that = this,
            htmlOut = '',
            index = 1;
        $.each(periods, function (periodKey, periodLabel) {
            var count = 0, classMore;
            if (typeof countEmplJoin[teamId] !== 'undefined' &&
                typeof countEmplJoin[teamId][periodKey] !== 'undefined'
            ) {
                count = countEmplJoin[teamId][periodKey];
            }
            if (typeof countEmplOutAll[teamId] !== 'undefined' &&
                typeof countEmplOutAll[teamId][periodKey] !== 'undefined'
            ) {
                count = count - countEmplOutAll[teamId][periodKey];
            }
            if (index === 1) {
                classMore = ' block-left';
            } else if (index === that.lengPeriods) {
                classMore = ' block-right';
            } else {
                classMore = '';
            }
            htmlOut += that.hrOutBody.htmlDiff.replace(/\{count\}/gi, count)
                .replace(/\{class\}/gi, classMore);
            index++;
        });
        return htmlOut;
    },
    /**
     * cal of team
     */
    calTurnoverate: function (countEmplOut, countEmplTeams, teamId, periods) {
        var that = this,
            turnoverate = 0;;
        $.each(periods, function (periodKey, periodLabel) {
            if (periodKey != 'sum' &&
                typeof countEmplOut[teamId] !== 'undefined' &&
                typeof countEmplOut[teamId][periodKey] !== 'undefined' &&
                typeof countEmplTeams[teamId] !== 'undefined' &&
                typeof countEmplTeams[teamId][periodKey] !== 'undefined' &&
                countEmplTeams[teamId][periodKey]
            ) {
                turnoverate += countEmplOut[teamId][periodKey] / countEmplTeams[teamId][periodKey]
            }
        });
        return (turnoverate * 100).toFixed(2);
    },
    /**
     * render hr employee out
     */
    renderHrEmployeeOut: function (employeeData, htmlBody, domTitle) {
        var that = this,
        employeesOutTeam = that.execHrEmployeeData(employeeData),
        htmlHrOutTeam = '',
        colspan = that.lengPeriods * 4 + 2 - 10;
        if (colspan < 5) {
            colspan = 5;
        }
        $('[d-hr-empl="title-reason"]').attr('colspan', colspan);
        var incre = 0;
        $.each(that.teamDevOther, function (i, teamId) {
            if (typeof employeesOutTeam[teamId] === 'undefined') {
                return true;
            }
            var lengthEmpl = employeesOutTeam[teamId].length,
                htmlTitleTeam = that.hrEmployee.htmlTeam
                    .replace(/\{team\}/gi, that.divisions[teamId])
                    .replace(/\{rowspan\}/gi, lengthEmpl);
            $.each(employeesOutTeam[teamId], function (j, emplInfo) {
                var htmlEachEmpl = htmlBody,
                classMore = '';
                incre++;
                $.each(emplInfo, function (key, value) {
                    var reg = new RegExp('\{' + key + '\}', 'gi');
                    htmlEachEmpl = htmlEachEmpl.replace(reg, value);
                });
                if (j === 0) { // first tr => insert team
                    htmlEachEmpl = htmlEachEmpl.replace(/\{attrcss\}/gi, '');
                    htmlEachEmpl = ($(htmlEachEmpl).prepend(htmlTitleTeam))[0].outerHTML;
                } else {
                    htmlEachEmpl = htmlEachEmpl.replace(/\{attrcss\}/gi, 'data-xml-index="2"');
                }
                if (j === lengthEmpl - 1) {
                    classMore = ' block-bottom';
                }
                htmlEachEmpl = htmlEachEmpl.replace(/\{class\}/gi, classMore)
                    .replace(/\{colspan\}/gi, colspan)
                    .replace(/\{hrbodystyle\}/gi, (incre % 2 === 0) ? 'hr_detail_body_even' : 'hr_detail_body_odd');
                htmlHrOutTeam += htmlEachEmpl;
            })
        });
        domTitle.after(htmlHrOutTeam);
    },
    /**
     * exec data hr employee out group by team id
     */
    execHrEmployeeData: function (employeeData) {
        var that = this,
            employeesOutTeam = {};
        $.each(employeeData, function (i, data) {
            var team = parseInt(data.team_id);
            if (that.teamDev.indexOf(team) === -1) {
                team = 'other';
            }
            delete data['team_id'];
            if (typeof employeesOutTeam[team] === 'undefined') {
                employeesOutTeam[team] = [];
            }
            employeesOutTeam[team].push(data);
        });
        return employeesOutTeam;
    },
    /**
     * render layout of table project average
     */
    renderLayoutAverFromProj: function () {
        var that = this,
            domTitle = $('[data-dom-title="ave-team-1"]'),
            htmlDomTitle = domTitle.html(),
            htmlDomItem = $('[data-dom-item="ave-team"]').html();
        $('[d-proj-index]').each (function (i, v) {
            var domTh = $(v),
            index = domTh.index();
            var domIndexTd = $('[data-dom-item="projs"] td').eq(index);
            that.projIndex.push(domIndexTd.html().replace(/\{|\}/gi, ''));
            if (domTh.attr('d-proj-index') !== 'not-aver') {
                htmlDomTitle += '<td rowspan="2" class="td-thead" data-xml-style-i-d="Thead">'+domTh.html()+'</td>';
                htmlDomItem += domIndexTd[0].outerHTML;
            }
            if (domTh.attr('d-proj-index') === 'remove') {
                domIndexTd.remove();
                domTh.remove();
            }
        });
        domTitle.html(htmlDomTitle);
        that.domAver.itemHtml = '<tr>' + htmlDomItem + '</tr>';
    },
    /**
     * get layout hr index
     */
    getLayoutHrIndex: function () {
        var that = this,
            htmlTriedTmp = $('[d-hr-month="out-tried"]')[0].outerHTML
                .replace(/\{attrcss\}\s*\=\s*\"\"/gi, '{attrcss}');
        that.hrOutTitle = {
            domTr: $('[d-hr-out="title"]'),
            domTrTry: $('[d-hr-out-try="title"]'),
            domTrAll: $('[d-hr-out-all="title"]'),
            htmlTried: htmlTriedTmp,
            htmlJoin: $('[d-hr-month="join"]')[0].outerHTML,
            htmlSum: $('[d-hr-month="sum"]')[0].outerHTML,
            htmlDiff: $('[d-hr-month="diff"]')[0].outerHTML,
        };
        that.hrOutTitle.domTr.html('');
        that.hrOutTitle.domTrTry.html('');
        that.hrOutTitle.domTrAll.html('');
        // body hr index out
        that.hrOutBody = {
            body : $('[d-hr-out="body"]')[0].outerHTML,
            bodyTry : $('[d-hr-out-try="body"]')[0].outerHTML,
            bodyAll : $('[d-hr-out-all="body"]')[0].outerHTML,
            htmlTried: $('[d-hr-count="out-tried"]')[0].outerHTML,
            htmlTry: $('[d-hr-count="out-try"]')[0].outerHTML,
            htmlAll: $('[d-hr-count="out-all"]')[0].outerHTML,
            htmlJoin: $('[d-hr-count="join"]')[0].outerHTML,
            htmlSum: $('[d-hr-count="sum"]')[0].outerHTML,
            htmlDiff: $('[d-hr-count="diff"]')[0].outerHTML,
        };
        // employee detail
        that.hrEmployee = {
            titleOut: $('[d-hr-title="empl-out"]'),
            titleJoin: $('[d-hr-title="empl-join"]'),
            htmlTeam: $('[d-hr-empl="team"]')[0].outerHTML,
        };
        $('[d-hr-empl="team"]').remove();
        that.hrEmployee.htmlBody = $('[d-hr-employee="out"]')[0].outerHTML
            .replace(/\{attrcss\}\s*\=\s*\"\"/gi, '{attrcss}');
        that.hrEmployee.htmlBodyJoin = $('[d-hr-employee="join"]')[0].outerHTML
            .replace(/\{attrcss\}\s*\=\s*\"\"/gi, '{attrcss}');
        $('[d-hr-out="body"]').remove();
        $('[d-hr-out-try="body"]').remove();
        $('[d-hr-out-all="body"]').remove();
    },
    /**
     * get division dev
     */
    renderDivisions: function () {
        var that = this;
        that.divisions = globVar.divisions.leaf;
        that.divisions['other'] = globVar.labelOther;
        that.divisions['company'] = globVar.labelCompany;
        that.projAverageData = {
            team: {},
            company: {
                data: {},
                count: {},
                aver: {},
            },
        };
        $.each(globVar.divisions.dev, function (i, id) {
            // flag team to sort by name
            that.projAverageData.team[that.fgTeam(id)] = {
                data: {},
                count: {},
                aver: {},
            };
        });
        if (globVar.divisions.qa && Array.isArray(globVar.divisions.qa)) {
            that.teamDev = globVar.divisions.dev.concat(globVar.divisions.qa);
        } else {
            that.teamDev = globVar.divisions.dev;
        }
        that.teamDevOther = $.extend({}, that.teamDev);
        that.teamDevOther[globVar.labelOther] = 'other';
        that.teamDevOther[globVar.labelCompany] = 'company';
        return that.projAverageData;
    },
    /**
     * add team parent to show total
     *
     * @returns {undefined}
     */
    renderTeamParentReplace: function () {
        var that = this;
        if (!globVar.teamParent) {
            return true;
        }
        that.teamParent = {};
        $.each(globVar.teamParent, function (p, cs) {
            p = parseInt(p);
            $.each(cs, function (k, i) {
                that.teamParent[i] = p;
            });
        });
        return that.teamParent;
    },
    /**
     * get evaluation point label
     */
    getEvaluation: function (point) {
        var eva;
        if (point > 20) {
            eva = 4;
        } else if (point > 15) {
            eva = 3;
        } else if (point > 10) {
            eva = 2;
        } else if (point > 0) {
            eva = 1;
        } else {
            eva = 0;
        }
        return globVar.evaluationLabel[eva];
    },
    /**
     * get multi division name follow ids
     */
    getDivisionsName: function (teamIds) {
        var that = this,
        result = '';
        $.each(teamIds, function (i, id) {
            if (typeof that.divisions[id] !== 'undefined') {
                result += that.divisions[id] + ', ';
            }
        });
        return result.slice(0, -2)
    },
    /**
     * get a division name follow id
     */
    getDivisionName: function (id) {
        var that = this;
        if (typeof that.divisions[id] !== 'undefined') {
            return that.divisions[id];
        }
        return '';
    },
    /**
     * set flag team, add prefix t
     */
    fgTeam: function (teamId) {
        return 't' + teamId;
    },
    /**
     * get name team from fg 't+ID'
     */
    getNameFg: function (fgTeam) {
        var that = this;
        if (!/^t/.test(fgTeam)) {
            return fgTeam;
        }
        return that.getDivisionName(fgTeam.substr(1));
    },
    exportKpi: function (btnDom) {
        var that = this;
        if (btnDom.data('process')) {
            return true;
        }
        btnDom.data('process', true);
        RKExternal.excel.init().exportExcel({
            tblFlg: $('.tbl-kpi'),
            sheetsName: [],
            fileName: 'Rikkeisoft KPI ' + that.startEndString,
            //stylesFlg: '#styleXml', // default #styleXml - xml style for xml sheet
        });
        btnDom.data('process', false);
    },
};
RKExternal.kpiDataSuccess = function (response) {
    kpiExport.renderData(response);
};
kpiExport.init();
})(jQuery, RKExternal);
