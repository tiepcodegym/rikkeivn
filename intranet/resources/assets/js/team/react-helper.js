const Helper = {
    trans: function (text) {
        if (typeof textTrans[text] != 'undefined') {
            return textTrans[text];
        }
        return text;
    },
    findIndexById: function (id, list) {
        for (let i = 0; i < list.length; i++) {
            if (list[i].id == id) {
                return i;
            }
        }
        return -1;
    },
    findItemById: function (id, list) {
        for (let i = 0; i < list.length; i++) {
            if (list[i].id == id) {
                return list[i];
            }
        }
        return null;
    },
    htmlEntities: function (str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
};

export default Helper;


