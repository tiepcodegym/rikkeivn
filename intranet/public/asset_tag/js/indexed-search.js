!function(a,t){RKTagLDB={databaseName:"RKTagProjTag",tableProjTag:"ProjTag",tableConfig:"Config",url:{},db:null,_callback:null,data:null,jslineqData:null,jslineq:null,init:function(t){var e=this;if("undefined"==typeof t&&(t=null),e._callback=t,e.data)return e.then(),e;var n=siteConfigGlobal.base_url;return e.url={checkVersionProjTag:n+"tag/ldb/proj/tag/version",getDataProjTag:n+"tag/ldb/proj/tag/get/data"},"undefined"==typeof a||(a.exists(e.databaseName).then(function(t){t?e.db=new a(e.databaseName):(e.db=new a(e.databaseName),e.db.version(1).stores({ProjTag:"++id,tag_id, field_id, tag_name, project_id",Config:"key, value"})),e.db.open().then(function(){e.db_projTag=e.db.table(e.tableProjTag),e.checkRemoteData()})["catch"](function(){})}),e)},checkRemoteData:function(){var a=this,e=a.db.table(a.tableConfig).get("version");return e.then(function(e){e||(e={value:-1}),t.ajax({url:a.url.checkVersionProjTag,type:"GET",success:function(t){t||(t=1),t=parseInt(t),e.value!==t?(a.db.table(a.tableConfig).put({key:"version",value:t}),a.replaceDb()):a.setData()}})}),a},replaceDb:function(){var a=this;return t.ajax({url:a.url.getDataProjTag,type:"GET",dataType:"json",success:function(t){var e,n=[];for(e in t)n.push({field_id:parseInt(t[e].field_id),tag_id:parseInt(t[e].tag_id),project_id:parseInt(t[e].project_id),tag_name:t[e].tag_name.trim()});a.db.transaction("rw",a.db_projTag,function(){a.db_projTag.clear(),a.db_projTag.bulkAdd(n),a.setData(n)})["catch"](function(){})}}),a},setData:function(t){var e=this;return t?(e.data=t,e.then(),e):a.spawn(function(){e.db_projTag.toArray().then(function(a){e.data=a,e.then()})})["catch"](function(){})},then:function(){var a=this;return a.jslineq=JSLINQ,a.data&&a.data.length?void(a._callback&&(a.jslineqData=a.jslineq(a.data),a._callback.then(),a._callback=null)):(a.jslineqData=a.jslineq([]),a._callback.then(!0),a._callback=null,[])},searchField:function(a,t){var e,n=this,r=null;if(a&&Object.keys(a).length){for(e in a)if(r=n.jslineqData.Where(function(t){return t.field_id==e&&a[e].map(Number).indexOf(t.tag_id)>-1&&(!r||r.indexOf(t.project_id)>-1)}).Distinct(function(a){return a.project_id}).ToArray(),!r.length)return[]}else r=n.jslineqData.Distinct(function(a){return a.project_id}).ToArray();if(t&&Object.keys(t).length)for(e in t)if(r=n.jslineqData.Where(function(a){return r.indexOf(a.project_id)>-1&&a.tag_name.trim().toLocaleLowerCase()===t[e].trim().toLocaleLowerCase()}).Distinct(function(a){return a.project_id}).ToArray(),!r.length)return[];return r}}}(Dexie,jQuery);