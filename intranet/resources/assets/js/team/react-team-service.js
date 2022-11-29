const TeamService = {

    setRoleListData: function(data) {
        if (typeof Storage == 'undefined') {
            return;
        }
        sessionStorage.setItem('roleListData', JSON.stringify(data));
    },

    getRoleListData: function () {
        if (typeof Storage == 'undefined') {
            return [];
        }
        let list = sessionStorage.getItem('roleListData');
        if (!list) {
            return [];
        }
        return JSON.parse(list);
    },

    setIniting: function(value) {
        if (typeof Storage == 'undefined') {
            return;
        }
        sessionStorage.setItem('team-setting-initing', value);
    },

    getIniting: function() {
        if (typeof Storage == 'undefined') {
            return 0;
        }
        let initing = sessionStorage.getItem('team-setting-initing');
        if (initing === null) {
            return 0;
        }
        return parseInt(initing);
    },

    storeItem: function(key, value) {
        if (typeof Storage == 'undefined') {
            return;
        }
        if (Array.isArray(value) || typeof value == 'object') {
            value = JSON.stringify(value);
        }
        sessionStorage.setItem(key, value);
    },

    getStoredItem: function(key, parseJson = false) {
        if (typeof Storage == 'undefined') {
            return parseJson ? [] : '';
        }
        let item = sessionStorage.getItem(key);
        if (!item) {
            return parseJson ? [] : '';
        }
        if (parseJson) {
            return JSON.parse(item);
        }
        return item;
    }

}

export default TeamService;
