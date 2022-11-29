(function(Dexie, $){
RKTagLDB = {
    databaseName: 'RKTagProjTag',
    tableProjTag: 'ProjTag',
    tableConfig: 'Config',
    url: {},
    db: null,
    _callback: null,
    data: null,
    jslineqData: null,
    jslineq: null,
    // init var
    init: function (callback) {
        var __this = this;
        if (typeof callback === 'undefined') {
            callback = null;
        }
        __this._callback = callback;
        if (__this.data) {
            __this.then();
            return __this;
        }
        var baseUrl = siteConfigGlobal.base_url;
        __this.url = {
            checkVersionProjTag: baseUrl + 'tag/ldb/proj/tag/version',
            getDataProjTag: baseUrl + 'tag/ldb/proj/tag/get/data'
        };
        if (typeof Dexie === 'undefined') {
            return true;
        }
        /**
         * call connect to db
         */
        Dexie.exists(__this.databaseName).then(function (exists) {
            if (!exists) {
                __this.db = new Dexie(__this.databaseName);
                __this.db.version(1).stores({
                    ProjTag: '++id,tag_id, field_id, tag_name, project_id',
                    Config: 'key, value'
                });
            } else {
                __this.db = new Dexie(__this.databaseName);
            }
            __this.db.open().then(function(){
                __this.db_projTag = __this.db.table(__this.tableProjTag);
                __this.checkRemoteData();
            }).catch(function(){});
        });
        return __this;
    },
    /**
     * check remote record data
     */
    checkRemoteData: function() {
        var __this = this,
        versionItem = __this.db.table(__this.tableConfig).get('version');
        versionItem.then(function(item) {
            if (!item) {
                item = {value: -1};
            }
            $.ajax({
                url: __this.url.checkVersionProjTag,
                type: 'GET',
                success: function(data) {
                    if (!data) {
                        data = 1;
                    }
                    data = parseInt(data);
                    if (item.value !== data) {
                        __this.db.table(__this.tableConfig).put({
                            key: 'version',
                            value: data
                        });
                        __this.replaceDb();
                    } else {
                        __this.setData();
                    }
                }
            });
        });
        return __this;
    },

    /**
     * remote get data
     */
    replaceDb: function() {
        var __this = this;
        $.ajax({
            url: __this.url.getDataProjTag,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var dataInt = [], i;
                for (i in data) {
                    dataInt.push({
                        field_id: parseInt(data[i].field_id),
                        tag_id: parseInt(data[i].tag_id),
                        project_id: parseInt(data[i].project_id),
                        tag_name: data[i].tag_name.trim()
                    });
                }
                __this.db.transaction('rw', __this.db_projTag, function() {
                    __this.db_projTag.clear();
                    __this.db_projTag.bulkAdd(dataInt);
                    __this.setData(dataInt);
                }).catch(function(){});
            }
        });
        return __this;
    },
    /**
     * set data from db
     * 
     * @returns {unresolved}
     */
    setData: function(data) {
        var __this = this;
        if (data) {
            __this.data = data;
            __this.then();
            return __this;
        }
        return Dexie.spawn(function () {
            __this.db_projTag.toArray().then(function(result) {
                __this.data = result;
                __this.then();
            });
        }).catch(function(){});
    },
    /**
     * call back after set data
     */
    then: function() {
        var __this = this;
        __this.jslineq = JSLINQ;
        if (!__this.data || !__this.data.length) {
            __this.jslineqData = __this.jslineq([]);
            __this._callback.then(true);
            __this._callback = null;
            return [];
        }
        if (__this._callback) {
            __this.jslineqData = __this.jslineq(__this.data);
            __this._callback.then();
            __this._callback = null;
        }
    },
    /**
     * search field 
     * 
     * @param {object} field {fieldId1: [tagid1,tagid2]}
     * @param {array} tag [tagname1, tagname2, tagname3]
     * @returns {RKTagLDB.searchField.projectIds}
     */
    searchField: function(field, tag) {
        var __this = this,
        projectIds = null, i;
        if (field && Object.keys(field).length) {
            // where: or if tag same field, and if tag diffrent field
            for(i in field) {
                projectIds = __this.jslineqData.Where(function(item) {
                    if (item.field_id == i && 
                        field[i].map(Number).indexOf(item.tag_id) > -1
                    ) {
                        if (projectIds) {
                            if (projectIds.indexOf(item.project_id) > -1) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                }).Distinct(function(item){
                    return item.project_id;
                }).ToArray();
                if (!projectIds.length) {
                    return [];
                }
            }
        } else {
            projectIds = __this.jslineqData
                .Distinct(function(item){
                    return item.project_id;
                }).ToArray();
        }
        
        // where and tag name
        if (tag && Object.keys(tag).length) {
            for(i in tag) {
                projectIds = __this.jslineqData.Where(function(item) {
                    if (projectIds.indexOf(item.project_id) > -1 &&
                        item.tag_name.trim().toLocaleLowerCase() === 
                        tag[i].trim().toLocaleLowerCase()
                    ) {
                        return true;
                    }
                    return false;
                }).Distinct(function(item){
                    return item.project_id;
                }).ToArray();
                if (!projectIds.length) {
                    return [];
                }
            }
        }
        return projectIds;
    }
    /**
     * search follow field: 
     * {
     *     fieldId1: [tagid1, tagid2],
     *     fieldId1: [tagid1, tagid2]
     * }
     * @param {array} fieldsId
     * @return {array} project ids
     */
    /*search: function(field, callback) {
        var __this = this, j;
        var collection, collectionField;
        projectIds = null;
        return Dexie.spawn(function* () {
            collection = yield __this.db_projTag;
            var i,v;
            if (Object.keys(field).length) {
                for(i in field) {
                    v = field[i];
                    collectionField = collection.where('tag_id').anyOf(v.map(Number));
                    if (projectIds !== null) {
                        collectionField = collectionField.filter(function(item) {
                            if (projectIds.indexOf(item.project_id) > -1) {
                                return true;
                            }
                        });
                    }
                    projectIds = __this.getProjectIds(collectionField, callback)
                            ._value;
                }
            } else {
                projectIds = __this.getProjectIds(collection, callback)._value;
            }
            return projectIds;
        }).catch(function(e){
            if (typeof callback === 'function') {
                setTimeout(function(){
                    callback([]);
                }, 50);
            }
            return [];
        });
    },
    /**
     * get project ids and call callback $http
     */
    /*getProjectIds: function(promise, callback) {
        var projectIds = [];
        return promise.toArray().then(function(result) {
            var length = result.length, i, item;
            if (length > 0) {
                for (i in result) {
                    i = parseInt(i);
                    item = result[i];
                    if (projectIds.indexOf(item.project_id) === -1) {
                        projectIds.push(item.project_id);
                    }
                    if (i === length - 1 && typeof callback === 'function') {
                        callback(projectIds);
                    }
                }
                return projectIds;
            } else {
                callback([]);
                return [];
            }
        });
    }*/
};
})(Dexie, jQuery);
