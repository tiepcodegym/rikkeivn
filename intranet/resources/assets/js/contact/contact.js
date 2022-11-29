(function ($, RKExternal) {
var isSendRequest = false,
teams, roles, noAvatarUrl;
RKExternal.paginate.beforeRender = {
    search: function (dataItem) {
        dataItem.name = htmlEntities(dataItem.name || '');
        dataItem.skype = htmlEntities(dataItem.skype || '');
        dataItem.bank_account = htmlEntities(dataItem.bank_account || '');
        dataItem.bank_name = htmlEntities(dataItem.bank_name || '');
        dataItem.mobile_phone = htmlEntities(dataItem.mobile_phone || '');
        if (!dataItem.avatar_url) {
            dataItem.avatar_url = noAvatarUrl;
        } else {
            var strIndexParams = dataItem.avatar_url.lastIndexOf('?');
            if (strIndexParams > -1) {
                dataItem.avatar_url = dataItem.avatar_url.substr(0, strIndexParams);
            }
        }
        //bank
        if (dataItem.bank_account || dataItem.bank_name) {
            dataItem.bank = (dataItem.bank_account ? dataItem.bank_account : '')
                + ' ('+(dataItem.bank_name ? dataItem.bank_name : '')+')';
        } else {
            dataItem.bank = '';
        }
        dataItem.teamName = '';
        // team, role
        if (dataItem.team) {
            var itemTeam = dataItem.team.split(';');
            itemTeam.forEach(function(teamRole) {
                teamRole = teamRole.split('-');
                if (teamRole.length !== 2 ||
                    typeof teams[teamRole[0]] === 'undefined' ||
                    typeof roles[teamRole[1]] === 'undefiend'
                ) {
                    return true;
                }
                dataItem.teamName += teams[teamRole[0]].name + ' - ' + roles[teamRole[1]].role
                    + '; ';
            });
            dataItem.teamName = dataItem.teamName.slice(0, -2);
        }
        return dataItem;
    },
};
RKExternal.paginate.beforeSendRequest = {
    search: function (params) {
        if (isSendRequest) {
            params.isExistsTeam = 1;
            return params;
        }
        return params;
    },
};
RKExternal.paginate.beforeExecSuccess = {
    search: function (response) {
        if (response.team) {
            isSendRequest = true;
            teams = response.team;
        }
        if (response.role) {
            isSendRequest = true;
            roles = response.role;
        }
        if (response.no_avatar) {
            noAvatarUrl = response.no_avatar;
        }
    },
};
RKExternal.paginate.afterDone = {
    search: function () {
        setTimeout(function () {
            RKExternal.simple.cutTextLine(null, 1);
        }, 200);
    }
};

if (!window.location.search.replace(/^\?|\?$/, '')) {
    $('[data-page-list="search"]').data('load-init', 0);
}

RKExternal.paginate.init();
})(jQuery, RKExternal);

