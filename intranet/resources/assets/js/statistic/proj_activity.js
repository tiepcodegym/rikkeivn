/**
 * write chart project new dashboard activity
 *
 * @param {type} $ jQuery
 * @param {type} RKExternal core external.js
 * @param {type} chartColors require from lib/chartjs/utils.js
 * @returns {undefined}
 */
(function ($, document, window, RKExternal, ColorArray) {
    var varPass = typeof globVarPass === 'object' ? globVarPass : {};
    var RKProjActivity = {
        chart: {},
        init: function () {
            var that = this;
            that.initHtmlChart();
            that.initDate();
            that.loadPlugin();
            that.projName = null;
            that.action();
            // init company name
            varPass.team.company = {
                data: {
                    name: 'Company',
                    isCompany: 1
                },
            };
            setTimeout(function () {
                that.submitFormFilter();
            }, 500);
            that.getTeamQa();
        },
        /**
         * get info statistic to chart
         *  function general to call multi request
         *
         * @param {string} pathSuffix name function Controller and process chart
         */
        getInfo: function (pathSuffix) {
            var that = this;
            if (that.formFilter.data('process') || (
                typeof that.formFilter.valid === 'function' && !that.formFilter.valid()
            )) {
                return true;
            }
            that.formFilter.data('process', true);
            $('[d-dom-btn="submit-filter"]').prop('disabled', true);
            $('[d-dom-i="ajax-load"]').removeClass('hidden');
            that.beforeSubmitFilter();
            $.ajax({
                url: varPass.urlGetInfo + pathSuffix,
                type: 'get',
                datatype: 'json',
                data: that.getDataForm(),
                success: function (response) {
                    that.afterSubmitDone();
                    if (!response.status) {
                        return that.errorAjax(response);
                    }
                    if (response.projName) {
                        that.initProjName(response.projName);
                    }
                    var methodInfo = 'execData' + pathSuffix;
                    if (typeof that[methodInfo] === 'function') {
                        that[methodInfo](response);
                    }
                    response = null;
                },
                error: function (response) {
                    that.errorAjax(response);
                    that.afterSubmitDone();
                },
            });
        },
        afterSubmitDone: function () {
            var that = this;
            that.formFilter.data('process', false);
            $('[d-dom-btn="submit-filter"]').prop('disabled', false);
            $('[d-dom-i="ajax-load"]').addClass('hidden');
        },
        /**
         * chart line of code change
         *
         * @param {object} response
         */
        execDataemplLoc: function (response) {
            var that = this,
                option = {
                    tab: 'loc',
                    dataFlag: 'emplLoc',
                    titleFilter: 'Line of code changed of ',
                    titleCompany: 'Line of code changed in the company',
                    titleXChart: 'Line',
                };
            that.locDate = {}; // period x in chart
            that.callDataChartGeneral(response, option);
            that.execDataemplBug(response);
            that.execDataemplBuglea(response);
            that.execDataemplBugdefix(response);
            that.execDataemplBuglefix(response);
            that.execDataprojDeli(response);
            that.initSlider();
        },
        /**
         * new bug defect
         *
         * @param {object} response
         */
        execDataemplBug: function (response) {
            var that = this,
                option = {
                    tab: 'bug',
                    dataFlag: 'emplBug',
                    titleFilter: 'Number new bug defect of ',
                    titleCompany: 'Number new bug defect in the company',
                    titleXChart: 'Number',
                };
            that.callDataChartGeneral(response, option);
        },
        /**
         * new bug leakage
         *
         * @param {object} response
         */
        execDataemplBuglea: function (response) {
            var that = this,
                option = {
                    tab: 'buglea',
                    dataFlag: 'emplBuglea',
                    titleFilter: 'Number new bug leakage of ',
                    titleCompany: 'Number new bug leakage in the company',
                    titleXChart: 'Number',
                };
            that.callDataChartGeneral(response, option);
        },
        /**
         * new bug defect fixed
         *
         * @param {object} response
         */
        execDataemplBugdefix: function (response) {
            var that = this,
                option = {
                    tab: 'bugdefix',
                    dataFlag: 'emplBugdefix',
                    titleFilter: 'Number bug defect fixed of ',
                    titleCompany: 'Number bug defect fixed in the company',
                    titleXChart: 'Number',
                };
            that.callDataChartGeneral(response, option);
        },
        /**
         * new bug leakage fixed
         *
         * @param {object} response
         */
        execDataemplBuglefix: function (response) {
            var that = this,
                option = {
                    tab: 'buglefix',
                    dataFlag: 'emplBuglefix',
                    titleFilter: 'Number bug leakage fixed of ',
                    titleCompany: 'Number bug leakage fixed in the company',
                    titleXChart: 'Number',
                };
            that.callDataChartGeneral(response, option);
        },
        /**
         * new bug leakage fixed
         *
         * @param {object} response
         */
        execDataprojDeli: function (response) {
            var that = this;
            that.deli.wrap.html('');
            if (!response.projDeli || !response.projDeli.length) {
                that.deli.noresult.removeClass('hidden');
                that.deli.wrap.addClass('hidden');
                return true;
            }
            that.deli.noresult.addClass('hidden');
            that.deli.wrap.removeClass('hidden');
            that.deliDataProj = that.processDataText(response.projDeli);
            var incre = 0;
            that.renderHtmlprojDeli(that.deliDataProj.company, 'company', 0);
            $.each(that.deliDataProj.team, function (teamId, data) {
                that.renderHtmlprojDeli(data, teamId, incre++);
            });
        },
        /**
         * 
         * @param {type} data
         * @param {type} isCompany addClass db-company
         */
        renderHtmlprojDeli: function (data, teamId, incre) {
            var that = this,
            htmlItem = that.deli.htmlItem;
            if (typeof incre === 'undefined') {
                incre = 1;
            }
            htmlItem = htmlItem.replace(/\{team\}/gi, varPass.team[teamId].data.name)
                .replace(/\{number_deli\}/gi, data.deli.count)
                .replace(/\{number_deli_proj\}/gi, data.deli.projs.length)
                .replace(/\{number_out\}/gi, data.out.count)
                .replace(/\{number_out_proj\}/gi, data.out.projs.length)
                .replace(/\{team_id\}/gi, teamId);
            htmlItem = $(htmlItem);
            // show proj name detail deliver
            if (data.deli.projs.length) {
                that.renderHtmlDeliShort(htmlItem, data.deli.projs, 'listFg');
            }
            if (data.out.projs.length) {
                that.renderHtmlDeliShort(htmlItem, data.out.projs, 'listFg2');
            }
            if (varPass.team[teamId].data.isCompany) {
                htmlItem.addClass('db-company');
            }
            if (incre % 2 === 0) {
                htmlItem.addClass('even-clear');
            }
            if (varPass.isSlide) {
                that.slideInner.append(htmlItem);
            } else {
                that.deli.wrap.append(htmlItem);
            }
        },
        /**
         * render html list project deli and out of date
         *
         * @param {type} domHtmlItem
         * @param {type} projs
         * @param {type} listFg
         * @returns {unresolved}
         */
        renderHtmlDeliShort: function (domHtmlItem, projs, listFg) {
            var that = this,
            maxShow = 5,
            index = 0;
            if (varPass.isSlide) {
                maxShow = 15;
            }
            projs.some(function (projId) {
                index++;
                if (index > maxShow) {
                    domHtmlItem.find(that.deli[listFg]).append(that.deli.htmlProjMore);
                    return true;
                }
                domHtmlItem.find(that.deli[listFg]).append(
                    that.deli.htmlProjItem
                        .replace(/\{proj_name\}/gi, that.projName[projId])
                        .replace(/xxx000/gi, projId)
                );
            });
            return domHtmlItem;
        },
        /**
         * general call chart exec
         *
         * @param {object} response
         * @param {object} option
         * @returns {Boolean}
         */
        callDataChartGeneral: function (response, option) {
            var that = this;
            $('#project-' + option.tab).unbind();
            if (!$('[data-chart-type="com-'+option.tab+'"]').length) {
                return true;
            }
            $('[data-chart-type="com-'+option.tab+'"]').addClass('hidden');
            that.domChartTeam[option.tab].html('');
            that.domChartCompany[option.tab].html(that.htmlChartCompny[option.tab]);
            if (typeof option.callBack === 'function') {
                option.callBack();
            }
            if (!response[option.dataFlag] || !response[option.dataFlag].length) {
                that.noResult[option.tab].removeClass('hidden');
                if (varPass.isSlide) {
                    that.noResult[option.tab].closest('[d-dom-tab]').remove();
                }
                return true;
            }
            that.noResult[option.tab].addClass('hidden');
            var resultData = that.processDatasetFormat(response[option.dataFlag], option),
            datasets = [],
            index = 0;
            response[option.dataFlag] = null;
            // get data chart for company team
            if (that.isFilterEmployee) {
                datasets.push({
                    label: that.isFilterEmployee,
                    fill: false,
                    backgroundColoprojsExistsr: ColorArray.color(index),
                    borderColor: ColorArray.color(index),
                    data: resultData.companyTeam.company,
                });
                resultData = null;
                that.chartWrite({
                    datasets: datasets,
                    title: option.titleFilter + that.isFilterEmployee,
                    elementId: 'project-'+option.tab,
                    chartKey : 'com-'+option.tab,
                }, option);
                $('[data-chart-type="com-'+option.tab+'"]').removeClass('hidden');
            } else {
                $.each(resultData.companyTeam, function (teamId, data) {
                    // show chart team, only a team => hide company
                    if (varPass.team[teamId].data.isCompany &&
                        varPass.teamFilter &&
                        varPass.teamFilter.indexOf('-') === -1
                    ) {
                        return true;
                    }
                    datasets.push({
                        label: varPass.team[teamId].data.name,
                        fill: false,
                        backgroundColor: ColorArray.color(index),
                        borderColor: ColorArray.color(index),
                        data: data,
                    });
                    index++;
                });
                that.chartWrite({
                    datasets: datasets,
                    title: option.titleCompany,
                    elementId: 'project-'+option.tab,
                    chartKey : 'com-'+option.tab,
                }, option);
                $('[data-chart-type="com-'+option.tab+'"]').removeClass('hidden');
                // get data chart for team project
                $.each(resultData.teamProj, function (teamId, dataProjs) {
                    that.insertHtmlTeamChart(option, teamId);
                    var index = 0;
                    datasets = [];
                    $.each(dataProjs, function (projId, data) {
                        datasets.push({
                            label: that.projName[projId],
                            fill: false,
                            backgroundColor: ColorArray.color(index),
                            borderColor: ColorArray.color(index),
                            data: data,
                        });
                        index++;
                    });
                    that.chartWrite({
                        datasets: datasets,
                        title: option.titleFilter + varPass.team[teamId].data.name,
                        chartKey : option.tab + '-' + teamId,
                        elementId: 'project-'+option.tab+'-' + teamId,
                    }, option);
                });
                resultData = null;
                that.domChartTeam[option.tab].removeClass('hidden');
            }
        },
        insertHtmlTeamChart: function (option, teamId) {
            var that = this;
            if (varPass.isSlide) {
                $('[d-dom-tab="'+option.tab+'"]:last')
                    .after(
                        that.htmlChartTeam[option.tab].replace(/\{teamId\}/gi, teamId)
                    );
                that.domChartTeam[option.tab].remove();
            } else {
                that.domChartTeam[option.tab]
                    .append(
                        that.htmlChartTeam[option.tab].replace(/\{teamId\}/gi, teamId)
                    );
            }
        },
        /**
         * chart write image canvas
         *
         * @param {object} options
         */
        chartWrite: function (options, configGeneral) {
            var that = this,
            ctx = document.getElementById(options.elementId),
            fontTitle, fontText;
            if (!ctx) {
                return true;
            }
            if (varPass.isSlide) {
                fontTitle = 28;
                fontText = 24;
            } else {
                fontTitle = fontText = 14;
            }
            ctx = ctx.getContext('2d');
            that.chart[options.chartKey] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: that.locDate[configGeneral.tab],// y column
                    datasets: options.datasets,
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: options.title,
                        fontSize: fontTitle,
                    },
                    tooltips: {
                        mode: 'point',
                        intersect: false,
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    elements: {
                        line: {
                            tension: 0.000001,
                        },
                    },
                    legend: {
                        labels: {
                            fontSize: fontText,
                        },
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Date',
                                fontSize: fontText,
                            },
                            ticks: {
                                fontSize: fontText,
                            },
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: configGeneral.titleXChart,
                                fontSize: fontText,
                            },
                            ticks: {
                                fontSize: fontText,
                            },
                        }]
                    },
                },
            });
        },
        /**
         * exec loc data
         *
         * @param {object} data
         */
        processDatasetFormat: function (emplLocData, options) {
            var that = this,
            dataExec = {
                company: {},
            },
            dataProjTeamExec = {};
            that.locDate[options.tab] = [];
            emplLocData.forEach(function (item) {
                // 1 commit have > 2000 line code => not calculator
                if (options.tab === 'loc' && item.value > 2000) {
                    return true;
                }
                if (typeof that.projName[item.proj_id] === 'undefined') {
                    return true;
                }
                var teams = (item.team_id + '').split('-');
                teams = that.teamFilterExec(teams);
                var itemCreatedAt = that.getTimePeriod(item.created_at);
                // each item => plus for company
                if (typeof dataExec.company[itemCreatedAt] === 'undefined') {
                    dataExec.company[itemCreatedAt] = 0;
                    that.locDate[options.tab].push(itemCreatedAt);
                }
                dataExec.company[itemCreatedAt] += item.value;
                // analyze each team
                if (!teams || !teams.length) {
                    return true;
                }
                teams.forEach(function (teamId) {
                    if (typeof varPass.team[teamId] === 'undefined' ||
                        that.teamQA.indexOf(teamId) > -1
                    ) {
                        return true;
                    }
                    // data loc company
                    if (typeof dataExec[teamId] === 'undefined') {
                        dataExec[teamId] = {};
                    }
                    if (typeof dataExec[teamId][itemCreatedAt] === 'undefined') {
                        dataExec[teamId][itemCreatedAt] = 0;
                    }
                    dataExec[teamId][itemCreatedAt] += item.value;
                    // data loc project
                    if (typeof dataProjTeamExec[teamId] === 'undefined') {
                        dataProjTeamExec[teamId] = {};
                    }
                    if (typeof dataProjTeamExec[teamId][item.proj_id] === 'undefined') {
                        dataProjTeamExec[teamId][item.proj_id] = {};
                    }
                    if (typeof dataProjTeamExec[teamId][item.proj_id][itemCreatedAt] === 'undefined') {
                        dataProjTeamExec[teamId][item.proj_id][itemCreatedAt] = 0;
                    }
                    dataProjTeamExec[teamId][item.proj_id][itemCreatedAt] += item.value;
                });
            });
            that.locDate[options.tab].sort();
            var resultCompanyTeam = {
                company: [],
            }, resultTeamProj = {};
            that.locDate[options.tab].forEach(function (item) {
                // sort date of data company-team
                $.each(dataExec, function (teamId, dataLoc) {
                    if (typeof resultCompanyTeam[teamId] === 'undefined') {
                        resultCompanyTeam[teamId] = [];
                    }
                    resultCompanyTeam[teamId].push(
                        typeof dataLoc[item] !== 'undefined' ? dataLoc[item] : 0
                    );
                });
                // sort date of data team-proj
                $.each(dataProjTeamExec, function (teamId, dataProj) {
                    if (typeof resultTeamProj[teamId] === 'undefined') {
                        resultTeamProj[teamId] = {};
                    }
                    $.each(dataProj, function (projId, dataLoc) {
                        if (typeof resultTeamProj[teamId][projId] === 'undefined') {
                        resultTeamProj[teamId][projId] = [];
                    }
                        resultTeamProj[teamId][projId].push(
                            typeof dataLoc[item] !== 'undefined' ? dataLoc[item] : 0
                        );
                    });
                });
            });
            return {
                companyTeam: resultCompanyTeam,
                teamProj: resultTeamProj,
            };
        },
        /**
         * exec data format text
         *
         * @param {object} dataResponse data response deliver
         * @returns {object}
         */
        processDataText: function (dataResponse) {
            var that = this,
            result = {
                company: {
                    deli: {
                        count: 0,
                        projs: [],
                    },
                    out: {
                        count: 0,
                        projs: [],
                    },
                },
                team: {},
            };
            $.each (dataResponse, function (i, item) {
                if (typeof that.projName[item.proj_id] === 'undefined') {
                    return true;
                }
                result.company.deli.count++;
                if (result.company.deli.projs.indexOf(item.proj_id) === -1) {
                    result.company.deli.projs.push(item.proj_id);
                }
                if (item.atd && item.cmd < item.atd) {
                    result.company.out.count++;
                    if (result.company.out.projs.indexOf(item.proj_id) === -1) {
                        result.company.out.projs.push(item.proj_id);
                    }
                }
                if (!item.team_id) {
                    return true;
                }
                var teams = (item.team_id + '').split('-');
                teams = that.teamFilterExec(teams);
                if (!teams || !teams.length) {
                    return true;
                }
                teams.forEach(function (teamId) {
                    if (typeof varPass.team[teamId] === 'undefined' ||
                        that.teamQA.indexOf(teamId) > -1
                    ) {
                        return true;
                    }
                    if (typeof result.team[teamId] === 'undefined') {
                        result.team[teamId] = {
                            deli: {
                                count: 0,
                                projs: [],
                            },
                            out: {
                                count: 0,
                                projs: [],
                            },
                        };
                    }
                    result.team[teamId].deli.count++;
                    if (result.team[teamId].deli.projs.indexOf(item.proj_id) === -1) {
                        result.team[teamId].deli.projs.push(item.proj_id);
                    }
                    if (item.atd && item.cmd < item.atd) {
                        result.team[teamId].out.count++;
                        if (result.team[teamId].out.projs.indexOf(item.proj_id) === -1) {
                            result.team[teamId].out.projs.push(item.proj_id);
                        }
                    }
                });
            });
            return result;
        },
        /**
         * get time flag period from a date
         *  eg: created_at: 2018-06-17, unit = m => period = 2018-06
         *
         * @param {type} itemCreatedAt
         * @returns {undefined}
         */
        getTimePeriod: function (itemCreatedAt) {
            var that = this,
            unit = that.getFilterUnit();
            if (unit === 'd') {
                return itemCreatedAt;
            }
            var itemCreatedAt = moment(itemCreatedAt);
            if (unit === 'm') {
                return itemCreatedAt.format('Y-MM');
            }
            return itemCreatedAt.format('Y');
        },
        /**
         * get project name exec data id: name
         *
         * @param {Object} data
         */
        initProjName: function (projNameData) {
            var that = this;
            that.projName = {};
            if (!projNameData || !projNameData.length) {
                return {};
            }
            var that = this;
            projNameData.forEach(function (item) {
                that.projName[item.id] = item.name;
            });
            return that.projName;
        },
        /**
         * show error ajax
         *
         * @param {object} response
         */
        errorAjax: function (response) {
            if (typeof response !== 'object') {
                RKExternal.notify('System error', false);
                return true;
            }
            if (typeof response.message === 'string') {
                RKExternal.notify(response.message, false);
            } else if (typeof response.responseJSON === 'object' &&
                    response.responseJSON.message
                    ) {
                RKExternal.notify(response.responseJSON.message, false);
            } else {
                RKExternal.notify('System error', false);
            }
        },
        /**
         * resize chart follow screen
         */
        screenResize: function () {
            var width = $(document).width(),
                height = $(document).height();
           $('.chart-full-box').width(width * 0.9).height(height * 0.9);
        },
        /**
         * resize chart follow canvas
         */
        canvasResize: function () {
            $('.chart-full-box').each(function () {
                var dom = $(this),
                domCanvas = dom.find('canvas');
                dom.width(domCanvas.width()).height(domCanvas.height());
            });
        },
        /**
         * action chart
         */
        action: function () {
            var that = this;
            // show, hide element chart
            $(document).on('click', '[data-btn-chart]', function (event) {
                event.preventDefault();
                var typeBtn = $(this).data('btn-chart'),
                typeChart = $(this).closest('[data-chart-type]').data('chart-type');
                if (!typeChart || !typeBtn || !that.chart[typeChart]) {
                    return true;
                }
                switch (typeBtn) {
                    case 'hide':
                        that.chart[typeChart].data.datasets.forEach(function (dataset) {
                            $.each(dataset._meta, function (key, value) {
                                value.hidden = true;
                            });
                            //dataset._meta[0].hidden = true;
                        });
                        that.chart[typeChart].update();
                        break;
                    case 'show':
                        that.chart[typeChart].data.datasets.forEach(function (dataset) {
                            $.each(dataset._meta, function (key, value) {
                                value.hidden = null;
                            });
                            //dataset._meta[0].hidden = null;
                        });
                        that.chart[typeChart].update();
                        break;
                    default:
                        // nothing
                }
            });
            // submit form filter
            that.formFilter.submit(function (event) {
                event.preventDefault();
                that.submitFormFilter();
            });
            // change unit view
            $('[d-dom-flag="unit"]').change(function () {
                that.loadDatepicker(false);
            });
            // remove selected employee
            $(document).on('click', '.select2-selection__clear', function (event) {
                event.preventDefault();
                $(this).closest('.select2-container').siblings('select').val(null).change();
                return false;
            });
            /*$('#project-open').click(function(e) {
                var activePoints = myChart.getElementAtEvent(e);
                if (!activePoints.length || typeof activePoints[0] !== 'object') {
                    return true;
                }
            });*/
            $(document).on('click', '[d-list-detail="deli"]', function (event) {
                event.preventDefault();
                that.deliViewProjMore($(this).closest('[data-list-type]'));
            });
        },
        /**
         * send request when submit filter
         *
         * @returns {undefined}
         */
        submitFormFilter: function () {
            var that = this;
            that.getInfo('emplLoc');
            //that.getInfo('emplBug');
        },
        /**
         * init html chart for request each data
         */
        initHtmlChart: function () {
            var that = this;
            that.noResult = {};
            that.domChartCompany = {};
            that.domChartCompany = {};
            that.htmlChartCompny = {};
            that.domChartTeam = {};
            that.htmlChartTeam = {};
            ['loc', 'bug', 'buglea', 'bugdefix', 'buglefix'].forEach(function (item) {
                that.noResult[item] = $('[data-no-result="'+item+'"]');
                that.domChartCompany[item] = $('[d-dom-chart="'+item+'-company-team"]');
                that.htmlChartCompny[item] = that.domChartCompany[item].html();
                that.domChartTeam[item] = $('[d-dom-chart="'+item+'-team"]');
                that.htmlChartTeam[item] = that.domChartTeam[item].html();
                that.domChartTeam[item].html('');
            });
            that.formFilter = $('[d-dom-form="proj-activity-filter"]');
            // init deliver html
            that.deli = {
                wrap: $('[d-db-wrap="deli"]'),
                noresult: $('[data-no-result="deli"]'),
                listFg: '[d-db-list-1="deli"]',
                listFg2: '[d-db-list-2="deli"]',
                htmlProjItem: $('[d-db-list-item="deli"]')[0].outerHTML,
                htmlProjMore: $('[d-db-list-item-more="deli"]')[0].outerHTML,
            };
            that.slideInner = $('[d-slider-inner]');
            $(that.deli.listFg).html('');
            that.deli.htmlItem = $('[d-db-wrap="deli"]').html();
            that.deli.wrap.html('');
            if (varPass.isSlide) {
                that.deli.wrap.remove();
            }
        },
        /**
         * init date when reload page
         * default before 30 day
         */
        initDate: function () {
            var now = moment();
            $('#to').val(now.format('Y-MM-DD'));
            now = now.subtract(30, 'days');
            $('#from').val(now.format('Y-MM-DD'));
        },
        /**
         * load plugin js
         */
        loadPlugin: function () {
            if (varPass.isSlide) {
                return true;
            }
            var that = this;
            RKExternal.select2.init({
                allowClear: true,
            });
            that.loadJValidate();
            that.loadDatepicker(true);
        },
        /**
         * load jquery validate
         */
        loadJValidate: function () {
            var that = this;
            that.formFilter.validate({
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
         * load datepicker
         */
        loadDatepicker: function (isInit) {
            if (varPass.isSlide) {
                return true;
            }
            var that = this;
            if ($('[data-date-picker]').data('datepicker')) {
                $('[data-date-picker]').datepicker('destroy');
            }
            var unit = that.getFilterUnit(),
                options = {
                    useCurrent: false,
                    todayHighlight: true,
                    weekStart: 1,
                    autoclose: true,
                    format: 'yyyy-mm-dd',
                };
            if (!isInit) { // change unit => change format date
                var from = $('[name="from"]').val(),
                    to = $('[name="to"]').val();
                switch (unit) {
                    case 'd':
                        $.extend(options, {
                            format: 'yyyy-mm-dd',
                        });
                        if (!/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/.test(from)) {
                            from = moment(from).date(1).format('Y-MM-DD');
                            to = moment(to).add(1, 'months').date(0).format('Y-MM-DD');
                        }
                        break;
                    case 'm':
                        $.extend(options, {
                            format: 'yyyy-mm',
                            viewMode:"months",
                            minViewMode:"months",
                        });
                        from = moment(from).format('Y-MM');
                        to = moment(to).format('Y-MM');
                        break;
                    default: // 'y'
                        $.extend(options, {
                            format: 'yyyy',
                            viewMode:"years",
                            minViewMode:"years",
                        });
                        from = moment(from).format('Y');
                        to = moment(to).format('Y');
                        break;
                }
                $('[name="from"]').val(from),
                $('[name="to"]').val(to);
            }
            $('[data-date-picker]').datepicker(options).on('changeDate', function(e) {
                // change date => reload project name
                that.projName = null;
            });
        },
        /**
         * get data form filter
         *
         * @returns {object}
         */
        getDataForm: function () {
            var that = this,
            dataForm = {};
            that.isFilterEmployee = false;
            that.formFilter.find('[d-filter-input]').each(function (i, v) {
                var domInput = $(v),
                name = domInput.attr('name'),
                value = domInput.val(),
                unit = that.getFilterUnit();
                switch (name) {
                    case 'from':
                        value = moment(value).format('Y-MM-DD');
                        break;
                    case 'to':
                        value = moment(value);
                        if (unit === 'm') {
                            value = value.add(1, 'months').date(0); // last date of month
                        } else if (unit === 'y') {
                            value = value.month(12).date(0); // last date of year
                        }
                        value = value.format('Y-MM-DD');
                        break;
                    default:
                        if (name === 'employee' && value) {
                            that.isFilterEmployee = RKfuncion.general.parseHtml(
                                $('[name="employee"] option:selected')
                                    .html()
                            );
                        }
                        //nothing
                }
                dataForm[name] = value;
            });
            // more another form data
            if (that.projName) {
                dataForm.projsExists = 1;
            }
            if (varPass.teamFilter) {
                dataForm.team = varPass.teamFilter;
                varPass.teamFilterArray = varPass.teamFilter.split('-');
            }
            return dataForm;
        },
        /**
         * filter team in db with filter team request => get filter request
         *
         * @param {type} teams
         * @returns {undefined}
         */
        teamFilterExec: function (teams) {
            if (!varPass.teamFilterArray) {
                return teams;
            }
            return varPass.teamFilterArray.filter(function (item) {
                return this.has(item);
            }, new Set(teams));
        },
        beforeSubmitFilter: function () {
            if (!$('[name="employee"]').val()) {
                $('[d-dom-fg="hide-empl"]').removeClass('hidden');
            } else {
                // hide tab bug, show loc
                $('[d-dom-fg="hide-empl"]').addClass('hidden');
                $('[d-dom-fg="show-empl"] a').trigger('click');
            }
        },
        /**
         * get value of filter unit
         *
         * @returns {String}
         */
        getFilterUnit: function () {
            var unit = $('[d-dom-flag="unit"]').val();
            if (['y', 'm', 'd'].indexOf(unit) === -1) {
                return 'd';
            }
            return unit;
        },
        /**
         * get team QA, PQA
         *
         * @returns {Array}
         */
        getTeamQa: function () {
            var that = this;
            that.teamQA = [];
            $.each(varPass.team, function (id, item) {
                if (typeof item.data !== 'object' || !item.data.name) {
                    return true;
                }
                if (/qa/i.test(item.data.name)) {
                    that.teamQA.push(id);
                }
            });
            return that.teamQA;
        },
        /**
         * init slider
         *
         * @returns {Boolean}
         */
        initSlider: function () {
            if (!varPass.isSlide) {
                return true;
            }
            var that = this;
            setTimeout(function () {
                that.carousel($(".swiper-wrapper .swiper-slide"), 0);
            }, 200);
        },
        /**
         * action slide
         *
         * @param {type} slide
         * @param {type} slideIndex
         * @returns {undefined}
         */
        carousel: function (slide, slideIndex) {
            var that = this;
            slide.hide();
            slide.removeClass('active');
            slideIndex++;
            if (slideIndex > slide.length) {
                slideIndex = 1;
            } 
            slide[slideIndex-1].style.display = "block"; 
            slide[slideIndex-1].classList.add('active');
            that.centerHeighChart($(slide[slideIndex-1]));
            setTimeout(function () {
                that.centerHeighChart($(slide[slideIndex-1]));
            }, 10);
            setTimeout(function () {
                that.carousel(slide, slideIndex);
            }, 20000);
        },
        /**
         * center slide
         *
         * @param {type} slideActive
         * @returns {Boolean}
         */
        centerHeighChart: function (slideActive) {
            if (!slideActive.data('dom-center')) {
                return true;
            }
            var height = $(window).outerHeight(),
                heightSlider = slideActive.outerHeight() + 20;
            if (height <= heightSlider) {
                return true;
            }
            slideActive.css('margin-top', '' + ((height-heightSlider)/2) + 'px');
        },
        deliViewProjMore: function (domList) {
            var that = this,
            type = domList.data('list-type'),
            teamId = domList.data('team-id'),
            data;
            if (!type || !teamId) {
                return true;
            }
            if (isNaN(teamId)) {
                data = that.deliDataProj.company;
            } else {
                data = that.deliDataProj.team[teamId];
            }
            if (!data || !data[type] || !data[type].projs) {
                return true;
            }
            var modal = $('#modal-pd-deli-more');
            if (!modal.length) {
                modal = $('<div class="modal fade" tabindex="-1" role="dialog"> <div class="modal-dialog" role="document"> <div class="modal-content"> <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> <h4 class="modal-title" d-modal-title="deli"></h4> </div><div class="modal-body" d-modal-content=deli></div><div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> </div></div></div></div>');
            }
            modal.find('[d-modal-title="deli"]').text(varPass.team[teamId].data.name);
            var html = '';
            data[type].projs.some(function (projId) {
                html += that.deli.htmlProjItem
                    .replace(/\{proj_name\}/gi, that.projName[projId])
                    .replace(/xxx000/gi, projId);
            });
            modal.find('[d-modal-content="deli"]').html(html);
            modal.modal('show');
        },
    };
    RKProjActivity.init();
})(jQuery, document, window, RKExternal, ColorArray);
