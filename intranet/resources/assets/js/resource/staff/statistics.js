(function ($) {

    $(document).ready(function () {

        //collect nested team
        var teamList = [];
        $('.team-data').each(function () {
            var teamData = $(this).text();
            $(this).text('');
            teamData = teamData ? JSON.parse(teamData) : [];
            var trRow = $(this).closest('tr');
            var teamId = parseInt(trRow.data('id'));
            teamList[teamId] = {
                id: teamId,
                parentId: trRow.data('parent') ? parseInt(trRow.data('parent')) : null,
                data: teamData
            };
        });

        var nestedTeamList = [];
        function toNestedTeamData(teamList, parentId) {
            var results = [];
            if (typeof parentId == 'undefined') {
                parentId = null;
            }
            for (var teamId in teamList) {
                if (teamList[teamId].parentId == parentId) {
                    nestedTeamList[teamId] = teamList[teamId];
                    results = results.concat(teamList[teamId].data);

                    var childData = toNestedTeamData(teamList, teamId);
                    if (childData) {
                        if (teamId != TEAM_BOD_ID) {
                            nestedTeamList[teamId].data = nestedTeamList[teamId].data.concat(childData);
                            results = results.concat(childData);
                        }
                    }
                }
            }
            return results;
        }

        toNestedTeamData(teamList, null);

        var timeEnd = new Date(dateEnd);

        $('.team-data').each(function () {
            var trRow = $(this).closest('tr');
            var teamId = parseInt(trRow.data('id'));
            var teamData = typeof nestedTeamList[teamId] != 'undefined' ? nestedTeamList[teamId].data : [];
            if (!teamData) {
                return;
            }

            var dataTotal = [], dataRoles = [], dataWorkTimes = [], dataContracts = [];
            for (var i in teamData) {
                var empId = teamData[i].id;
                //total
                pushUniqueArray(dataTotal, empId);
                //collect roles
                var itemRoles = teamData[i].roles;
                if (!itemRoles) {
                    if (typeof dataRoles[-1] == 'undefined') {
                        dataRoles[-1] = [];
                    }
                    pushUniqueArray(dataRoles[-1], empId);
                } else {
                    if ($.isNumeric(itemRoles)) {
                        itemRoles = [itemRoles];
                    } else {
                        itemRoles = JSON.parse(itemRoles);
                    }
                    for (var r = 0; r < itemRoles.length; r++) {
                        var roleId = parseInt(itemRoles[r]);
                        if (typeof dataRoles[roleId] == 'undefined') {
                            dataRoles[roleId] = [];
                        }
                        pushUniqueArray(dataRoles[roleId], empId);
                    }
                }
                //collect worktimes
                var dateJoin = new Date(teamData[i].date_join);
                var workedMonths = diffMonths(dateJoin, timeEnd);
                for (var month in aryWorkTimes) {
                    var condWorkTime = aryWorkTimes[month];
                    if (typeof dataWorkTimes[month] == 'undefined') {
                        dataWorkTimes[month] = [];
                    }
                    if (workedMonths >= condWorkTime.from && workedMonths < condWorkTime.to) {
                        pushUniqueArray(dataWorkTimes[month], empId);
                    }
                }
                //collect contracts
                var contractType = teamData[i].contract_type;
                if (!contractType) {
                    contractType = -1;
                }
                if (typeof dataContracts[contractType] == 'undefined') {
                    dataContracts[contractType] = [];
                }
                pushUniqueArray(dataContracts[contractType], empId);
            }

            trRow.find('.col-total').text(dataTotal.length);

            trRow.find('.col-role').each(function () {
                var roleId = parseInt($(this).data('role'));
                if (typeof dataRoles[roleId] != 'undefined') {
                    $(this).text(dataRoles[roleId].length);
                } else {
                    $(this).text(0);
                }
            });

            trRow.find('.col-time').each(function () {
                var monthId = parseInt($(this).data('month'));
                if (typeof dataWorkTimes[monthId] != 'undefined') {
                    $(this).text(dataWorkTimes[monthId].length);
                } else {
                    $(this).text(0);
                }
            });

            trRow.find('.col-contract').each(function () {
                var contractType = parseInt($(this).data('type'));
                if (typeof dataContracts[contractType] != 'undefined') {
                    $(this).text(dataContracts[contractType].length);
                } else {
                    $(this).text(0);
                }
            });
        });

        $('.statistics-table').tableHeadFixer({'left' : fixedCols}); 
    });

    function pushUniqueArray(array, input) {
        if (array.indexOf(input) < 0) {
            array.push(input);
        }
        return array;
    }

    function diffMonths(dateStart, dateEnd) {
        var months = (dateEnd.getFullYear() - dateStart.getFullYear()) * 12;
        months += (dateEnd.getMonth() - dateStart.getMonth() + 1);
        return months < 0 ? 0 : months;
    }

})(jQuery);
