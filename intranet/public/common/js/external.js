!function(e,t,a){var r={},n="object"==typeof RKfuncion?RKfuncion:{};r.notify=function(t,a,r){"undefined"==typeof a||a===!0||null===a?a="success":a===!1&&(a="warning"),r=e.extend({from:"top",align:"right"},r);var n="";return"undefined"!=typeof t&&t?Array.isArray(t)?t.length>1?(n+="<ul>",e.each(t,function(e,t){n+="<li>"+t+"</li>"}),n+="</ul>"):1===t.length?n+=t[0]:n=a:n=t:n=a,e.notifyClose(),e.notify({message:n},{type:a,z_index:2e3,placement:{from:r.from,align:r.align},delay:"undefined"!=typeof r.delay?r.delay:5e3})},r.progressBar={bar:null,timeout:null,step:100,process:!1,start:function(){var t=this;return!!t.process||(t.process=!0,void(this.bar?(t.bar.progressbar("value",0),t.bar.show(),t.progress()):(e(".progressbar").length||e("body").append('<div class="progressbar ui-progressbar-top"></div>'),t.bar=e(".progressbar"),t.bar.progressbar({value:0,max:100,complete:function(){t.end()},create:function(){t.progress()}}))))},end:function(){var e=this;return e.process=!1,!e.bar||(100!==e.bar.progressbar("value")&&e.bar.progressbar("value",100),e.timeout&&clearTimeout(e.timeout),void setTimeout(function(){e.bar.hide()},700))},progress:function(){var e=this.bar.progressbar("value")||0,t=e+Math.floor(10*Math.random()),a=this;t<70?(a.bar.progressbar("value",t),a.timeout=setTimeout(function(){a.progress()},a.step)):t<95?(a.bar.progressbar("value",e+.5),a.timeout=setTimeout(function(){a.progress()},a.step)):clearTimeout(a.timeout)},setStep:function(e){return this.step=e,this}},r.formAjax={flagDom:'[data-form-submit="ajax"]',flagButton:'[data-btn-submit="ajax"]',flagFormFile:'[data-form-file="1"]',init:function(){var a=this;e(a.flagDom+' [type="submit"]').prop("disabled",!1),e(t).on("submit",a.flagDom,function(t){t.preventDefault(),a.elementSubmit(e(this),1)}),e(t).on("click",a.flagButton,function(t){t.preventDefault(),a.elementSubmit(e(this),2)})},elementSubmit:function(e,t){var a=this;return""+e.data("flag-valid")!="1"||e.valid()?!!e.data("running")||void(e.data("submit-noti")?r.confirm(e.data("submit-noti"),function(r){r.result&&a.execSubmit(e,t)}):a.execSubmit(e,t)):(e.find("[type=submit]").removeAttr("disabled"),!0)},execSubmit:function(t,n){var i,o,s=this,d=t.find(".loading-submit"),l=t.find(".loading-hidden-submit"),c=t.data("cb-before-submit"),f=t.data("cb-get-form-data");if(2===n)i=t,o={_token:siteConfigGlobal.token};else if(i=t.find("[type=submit]:not(.no-disabled)"),f&&"function"==typeof r[f]){if(o=r[f](t),o===!1)return i.prop("disabled",!1),!0}else o=s.getDataForm(t);c&&"function"==typeof r[c]&&r[c](o,t),t.data("running",!0),i.prop("disabled",!0),d.removeClass("hidden"),l.addClass("hidden");var p=t.attr("method");p||(p="post");var u={url:t.attr("action"),type:p,dataType:"json",data:o,success:function(e){if("undefined"!=typeof e.reload&&""+e.reload=="1")return a.location.reload(),!0;if("undefined"==typeof e.status||!e.status){r.notify(e.message,!1);var n=t.data("cb-error");return n&&"function"==typeof r[n]&&r[n](e,t),!0}if("undefined"!=typeof e.redirect&&e.redirect)return a.location.href=e.redirect,!0;"undefined"!=typeof e.popup&&""+e.popup!="1"||"undefined"==typeof e.message||!e.message||r.notify(e.message,!0,{delay:"undefined"!=typeof t.data("delay-noti")?t.data("delay-noti"):1e4}),e.urlReplace&&r.urlReplace(e.urlReplace);var i=t.data("cb-success");i&&"function"==typeof r[i]&&r[i](e,t)},error:function(e){"object"==typeof e&&e.message?r.notify(e.message,!1):"object"==typeof e&&"object"==typeof e.responseJSON&&e.responseJSON.message?r.notify(e.responseJSON.message,!1):r.notify("System error",!1);var a=t.data("cb-error");a&&"function"==typeof r[a]&&r[a](e,t)},complete:function(e){if("undefined"!=typeof e.reload&&""+e.reload=="1"||"undefined"!=typeof e.redirect&&e.redirect||"object"==typeof e.responseJSON&&e.responseJSON.reload)return!0;t.data("running",!1),i.prop("disabled",!1),d.addClass("hidden"),l.removeClass("hidden");var a=t.data("cb-complete");a&&"function"==typeof r[a]&&r[a](e,t)}};t.data("form-file")&&(u.contentType=!1,u.processData=!1),e.ajax(u)},getDataForm:function(t){if(!t.data("form-file"))return t.serialize();var a=new FormData;return t.find("input:not([disabled]), select:not([disabled]), textarea:not([disabled])").each(function(t,r){var n=e(r).attr("type"),i=e(r).attr("name"),o=e(r).val();switch(n){case"file":1===r.files.length?a.append(i,r.files[0]):r.files.length>1&&a.append(i,r.files);break;case"checkbox":case"radio":e(r).is(":checked")&&a.append(i,o);break;default:a.append(i,o)}}),a}},r.params=function(){var e={};return decodeURIComponent(a.location.search).replace(/[?&]+([^=&]+)=([^&]*)/gi,function(t,a,r){e[a]=r}),e},r.urlReplace=function(t,n,i){if("string"==typeof t)return a.history.pushState(null,null,t),!0;"undefined"!=typeof i&&i&&(n=e.extend(r.params(),n));var t,o=a.location.href,s=o.indexOf("?");t=s===-1?o+"?"+e.param(n):o.substr(0,s)+"?"+e.param(n),a.history.pushState(null,null,t)},r.urlReplaceEncode=function(t,n){"undefined"!=typeof n&&n&&(t=e.extend(r.params(),t));var i,o=a.location.href,s=o.indexOf("?"),d="";e.each(t,function(e,t){d+=e+"="+t+"&"}),d=encodeURIComponent(d.slice(0,-1)),i=s===-1?o+"?"+d:o.substr(0,s)+"?"+d,a.history.pushState(null,null,i)},r.confirm=function(t,a,i){i="object"==typeof i?i:{},i=e.extend({autoHide:!0},i);var o=r.confirm,s=e("#modal-confirm-submit");s.length||(s=e('<div class="modal" id="modal-confirm-submit" data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">'+textConfirm+'</h4></div><div class="modal-body"><p data-mconfirm="body"></p></div><div class="modal-footer"><button type="button" class="btn btn-default btn-confirm-no pull-left" onclick="RKExternal.confirm.no()">'+confirmNo+'</button><button type="button" class="btn btn-primary btn-confirm-yes" onclick="RKExternal.confirm.yes()">'+confirmYes+"</button></div></div></div></div>"),e("body").append(s),o.hide=function(){e("#modal-confirm-submit").modal("hide")}),e("#modal-confirm-submit").find("button.btn-confirm-yes").removeClass("btn-primary").removeClass("btn-danger"),i.btnOkColor?e("#modal-confirm-submit").find("button.btn-confirm-yes").addClass(i.btnOkColor):e("#modal-confirm-submit").find("button.btn-confirm-yes").addClass("btn-primary"),o.yes=function(){return i.autoHide&&o.hide(),"function"!=typeof a||a({result:!0,hide:o.hide})},"function"!=typeof o.no&&(o.no=function(){return i.autoHide&&o.hide(),"function"==typeof a&&a({result:!1,hide:o.hide})}),"undefined"!=typeof n.general&&"function"==typeof n.general.modalBodyPadding&&n.general.modalBodyPadding(),s.find('[data-mconfirm="body"]').html(t),s.modal("show")},r.stringToUrlReplace=function(e,t,a){t=""+t,a=""+a;var r=e.lastIndexOf(t),n=t.length;return r<0?e:e.substr(0,r)+a+e.substr(r+n)},r.uploadFile={fgWrapper:'[data-flag-attach="file-wrapper"]',fgInput:'[data-flag-attach="input"]',fgSize:'[data-flag-attach="file-size"]',fgName:'[data-flag-attach="file-name"]',fgRemove:'[data-flag-attach="file-remove"]',fgNameShow:'[data-flag-attach="name-show"]',fgInputRemove:'[data-flag-attach="input-remove"]',fgInputAs:'[data-flag-attach="input-as"]',init:function(t){var a=this,r={isCheckType:!1,messageSize:"File size is large",messageType:"File type dont allow",size:5};a.option=e.extend(r,t),e(a.fgWrapper+" "+a.fgInput).change(function(){a.readFileUrl(e(this).closest(a.fgWrapper),e(this))}),e(a.fgWrapper+" "+a.fgRemove).click(function(t){t.preventDefault(),a.removeFile(e(this).closest(a.fgWrapper))})},readFileUrl:function(t,a){var r=this;if(!a[0].files||!a[0].files[0])return t.find(r.fgName).text().trim()&&t.find(r.fgNameShow).removeClass("hidden"),t.find(r.fgInputAs).val(""),!0;var n=a[0].files[0],i=t.find(r.fgSize);return r.option.isCheckType&&e.inArray(n.type,r.option.isCheckType)<0?(a.val(""),t.find(r.fgInputAs).val(""),alert(r.option.messageType),i.val(""),!0):n.size/1e3/1e3>r.option.size?(a.val(""),alert(r.option.messageSize),i.val(""),t.find(r.fgInputAs).val(""),!0):(t.find(r.fgInputAs).val("1"),t.find(r.fgNameShow).addClass("hidden"),void i.val(Intl.NumberFormat().format((n.size/1e3).toFixed(2))))},removeFile:function(e){var t=this;e.find(t.fgNameShow).hide(),e.find(t.fgInputRemove).val(1),e.find(t.fgInputAs).val("")}},r.autoComplete={fgDom:'[data-autocomplete-dom="true"]',dataUrlRemote:"ac-url",init:function(t){var a=this;if(!e(a.fgDom).length)return!0;var r={minLength:1,beforeRemote:null};t=e.extend(r,t),e(a.fgDom).autocomplete({minLength:t.minLength,source:function(r,n){var i=this.element,o={},s={term:r.term};if("function"==typeof t.beforeRemote){if(o=t.beforeRemote(r,n,i),!o)return n([]);"object"==typeof o.params&&(s=e.extend(s,o.params))}e.ajax({url:this.element.data(a.dataUrlRemote),type:"GET",dataType:"json",data:s,success:function(e){return n(e.data)}})},select:function(a,r){var n=e(this);n.data("item-id",r.item.id),"function"==typeof t.afterSelected&&t.afterSelected(r.item,n)}})}},r.select2={fgSelect:'[data-select2-dom="1"]',dataRemote:"select2-url",dataHasSearch:"select2-search",option:{},init:function(t){var a=this;if(t=e.extend({},t),a.option=t,t.enforceFocus)try{e.fn.modal.Constructor.prototype.enforceFocus=function(){}}catch(r){}e(a.fgSelect).each(function(){var r,n=e(this);return!!n.data("select2")||(r=n.attr("placeholder")?e.extend({placeholder:n.attr("placeholder")},t):e.extend({},t),void(e(this).data(a.dataRemote)?a.execRemote(n,r):a.exec(n,r)))})},exec:function(t,a){var r=this;t.data(r.dataHasSearch)||(a=e.extend({minimumResultsForSearch:1/0},a)),"1"==t.data("select2-multi-trim")&&(a.templateSelection=r.formatSelectedBasic,a.escapeMarkup=function(e){return e}),t.select2(a),"1"==t.data("select2-multi-trim")&&(t.on("select2:select",function(){r.trimText(t)}),t.on("select2:unselect",function(){r.trimText(t)}))},trimText:function(t){var a,r;t.siblings(".select2.select2-container").find("ul.select2-selection__rendered > li.select2-selection__choice").each(function(t,n){r=e(n).find('[data-select2-result="selected"]'),a=r.text().trim(),r.html(a).attr("title",a)})},execRemote:function(t,a){var r=this;a=e.extend({delay:500,minimumInputLength:2,allowClear:!1},a),t.select2({id:function(e){return e.id},placeholder:a.placeholder,minimumInputLength:a.minimumInputLength,allowClear:a.allowClear,ajax:{url:t.data("select2-url"),dataType:"json",delay:a.delay,data:function(e){return{q:e.term,page:e.page}},processResults:function(e,t){return t.page=t.page||1,{results:e.data,pagination:{more:10*t.page<e.total}}},cache:!0},escapeMarkup:function(e){return e},templateResult:r.formatReponse,templateSelection:r.formatReponesSelection})},formatReponse:function(e){return e.loading?e.text:"<div class='select2-result-repository clearfix'><div class='select2-result-repository__title'>"+htmlEntities(e.text)+"</div></div>"},formatReponesSelection:function(t,a){return"function"==typeof r.select2.option.afterSelected&&r.select2.option.afterSelected(t,e(a).closest(".select2.select2-container").prev('select[data-flag-dom="select2"]')),htmlEntities(t.text)},formatSelectedBasic:function(e){return'<span data-select2-result="selected">'+e.text+"</span>"}},r.simple={textShort:function(t,a){var r,n,i,o=50;return"undefined"==typeof a&&(a=!0),r=t&&t.length?t.find("[data-text-short]"):e("[data-text-short]"),!r.length||!a||void r.each(function(t,a){return n=e(a).data("text-short"),n&&!isNaN(n)||(n=o),!!e(a).attr("title")||(i=e(a).text().trim(),i.length<=n?(e(a).removeAttr("title"),!0):(e(a).text(i.substr(0,n-3)+"..."),void e(a).attr("title",i)))})},textHeight:function(t){var a,r,n,i,o,s=100,d=[];return t&&t.length||(t=e("body")),a=t.find("[data-text-height]"),!a.length||void a.each(function(){var a=e(this);if(n=a.data("text-flag-org")||a.text().trim(),a.text(""),r=a.data("text-height"),o=a.data("flag-height"),"undefined"!=typeof o&&d.indexOf(o)===-1&&(t.find('[data-text-height][data-flag-height="'+o+'"]').each(function(){var t=e(this);t.data("text-flag-org",t.text()),t.text("")}),d.push(o)),i=a.data("fix-width")?a.data("fix-width"):a.outerWidth(),r&&!isNaN(r)||(r=s),a.text(n),a.data("text-flag-org",null),a.outerHeight()<=r&&a.outerWidth()<=i)return a.removeAttr("style"),a.removeAttr("title"),!0;if(a.width(i),a.outerHeight()<=r)return a.removeAttr("title"),!0;var l,c="",f=n.length;for(l=0;l<f&&(c+=n.substr(l,5),a.text(c),!(a.outerHeight()>r));l+=5);c+="...",a.text(c),a.attr("title",n)})},cutTextLine:function(t,a){return t&&t.length||(t=e("body [data-text-line]")),a||(a=1),!t.length||void t.each(function(){var t=e(this);if(t.attr("title"))return!0;var r=t.data("text-flag-org")||t.text().trim(),n=t.data("text-line")||a,i=parseFloat(t.css("line-height"))*n;if(t.outerHeight()<=i)return!0;var o,s="",d=r.length;for(o=0;o<d;o+=5)if(s+=r.substr(o,5),t.text(s),t.outerHeight()>i){s=s.slice(0,-8);break}s+="...",t.text(s),t.attr("title",r)})},diffTimeYM:function(e,t){if("function"!=typeof moment)return alert("Miss lib moment.js"),!0;if("object"!=typeof e&&(e=moment(e)),t?"object"!=typeof t&&(t=moment(t)):t=moment(),t.isBefore(e))return{Y:0,M:0};t.add(1,"d");var a={};a.Y=t.diff(e,"Y"),a.M=t.diff(e,"M"),a.M=a.M-12*a.Y;var r=t.diff(e,"d")-365*a.Y-30*a.M;return r>15&&a.M++,a},formatDate:function(e,t){var a=this;return"object"!=typeof t&&(t=new Date),"string"!=typeof e&&(e="Y-m-d H:i:s"),e.replace(/Y/gi,t.getFullYear()).replace(/m/gi,a.lengtwo(t.getMonth()+1)).replace(/d/gi,a.lengtwo(t.getDate())).replace(/H/gi,a.lengtwo(t.getHours())).replace(/i/gi,a.lengtwo(t.getMinutes())).replace(/s/gi,a.lengtwo(t.getSeconds()))},lengtwo:function(e){return e<10?"0"+e:e},getFileSplitFromPath:function(e){var t,a,r,n=e.lastIndexOf("/");return n===-1?(t="",r=e):(t=e.substr(0,n+1),r=e.substr(n+1)),n=r.lastIndexOf("."),n===-1?a="":(a=r.substr(n+1),r=r.substr(0,n)),[t,r,a]},rand:function(e,t,a){var r=this,n=r.seed;return e="undefined"==typeof e?0:e,t="undefined"==typeof t?1:t,r.seed=(9301*n+49297)%233280,n=e+r.seed/233280*(t-e),a?Math.round(n):n}},r.simple.seed=Date.now(),r.tblWidth={fDom:'[data-tbl-fix="width"]',init:function(t){var a=this;"undefined"==typeof t&&(t=!0),t&&a.tdHide(),e(a.fDom).each(function(){var t=e(this);return!!t.data("fixedWidth")||(t.data("fixedWidth",!0),void a.fixItem(t))}),t&&a.tdShow()},tdHide:function(){var t=this;e(t.fDom+" > tbody > tr > td").children().addClass("tblWidth-hide")},tdShow:function(){var t=this;e(t.fDom+" > tbody > tr > td").children().removeClass("tblWidth-hide")},fixItem:function(t){var a,r,n=this,i=t.outerWidth(),o=t.children("colgroup").children("col"),s=[];return!o.length||(o.each(function(t,a){e(a).data("priority")&&!isNaN(e(a).data("priority"))&&s.push(parseInt(e(a).data("priority")))}),s=s.sort(),e.each(s,function(e,n){return a=t.children("colgroup").children('col[data-priority="'+n+'"]'),r=a.data("percent"),!(r&&!isNaN(r))||void a.width(i*r/100)}),n.colRemainItem(t,o,i),void n.colFixItem(t))},colFixItem:function(t){var a=t.children("colgroup").children("col[data-col-fix]");return!a.length||void a.each(function(){var a,r,n=e(this),i=n.outerWidth(),o=n.data("col-fix"),s=n.index();return o?a=t.find('[data-col-fixed="'+o+'"]'):(a=t.find("> tbody > tr:first > td:eq("+s+")"),a.length||(a=t.find("> thead > tr > th:eq("+s+")"))),!a.length||(r=a.outerWidth(),a.width(i),void a.data("fix-width",i))})},colRemainItem:function(t,a,r){var n=t.children("colgroup").children("col[data-col-remain]");if(1!==n.length)return!0;var i=0;a.each(function(){var t=e(this);return!!t.is("[data-col-remain]")||void(i+=t.outerWidth())}),n.width(r-i)}},r.previewImage={init:function(t){var a=this;t="undefined"==typeof t?{}:t,a.option=e.extend({type:["image/jpeg","image/png","image/gif"],size:5120,message_size:"File size is large",message_type:"File type dont allow"},t),e("[data-img-pre-input]").change(function(){a.readUrl(this,e(this).data("img-pre-input"))})},readUrl:function(t,a){var r=this,n=e(t);if(!t.files||!t.files[0])return!0;var i=t.files[0];if(e.inArray(i.type,r.option.type)<0)return n.val(""),alert(r.option.message_type),!0;if(i.size/1e3>r.option.size)return n.val(""),alert(r.option.message_size),!0;var o=new FileReader;o.onload=function(t){e('[data-img-pre-img="'+a+'"]').attr("src",t.target.result)},o.readAsDataURL(i)}},r.excel={init:function(){var e=this;return e.xmlHead='<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:html="http://www.w3.org/TR/REC-html40">',e.xmlFoot="</Workbook>",e.xml=e.xmlHead+'<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Author>Giang Soda</Author><Created>1527065780058</Created></DocumentProperties><Styles><Style ss:ID="Default"><Font ss:Size="11" /></Style><Style ss:ID="Currency"><NumberFormat ss:Format="Currency"></NumberFormat></Style><Style ss:ID="Date"><NumberFormat ss:Format="Medium Date"></NumberFormat></Style><Style ss:ID="Thead"><Alignment ss:Vertical="Center" ss:Horizontal="Center" ss:WrapText="1"/><Font ss:Size="11" ss:Bold="1" /><Interior ss:Color="#ececec" ss:Pattern="Solid" /><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style></Styles><Worksheet ss:Name="wsName"><Table><Column ss:AutoFitWidth="0" /><Row ss:AutoFitHeight="0"><Cell ss:StyleID="Default"><Data ss:Type="String"></Data></Cell></Row></Table></Worksheet>'+e.xmlFoot,this},getXmlFile:function(e,t){var a=new XMLHttpRequest;a.onreadystatechange=function(){4==this.readyState&&200==this.status&&"function"==typeof t&&t(a.responseXML)},a.open("get",e,!0),a.send()},getSymbol:function(){return{enter:"&#13;&#10;"}},replaceEnterToBr:function(e){return e.replace(/\r\n|\n|\r/gi,"{{--br--}}")},replaceBrToBreak:function(e){return e.replace(/\{\{\-\-br\-\-\}\}/gi,"&#13;&#10;")},exportExcel:function(t){if("undefined"==typeof Blob||"undefined"==typeof saveAs)return alert("Miss Blob and FileSaver lib"),!0;var a=this;t=e.extend({tblFlg:e("table"),sheetsName:[],fileName:"exportExcel",stylesFlg:"#styleXml",replaceXml:null},t);var r=e.parseXML(a.xml.trim()),n=e(r),i=e(t.stylesFlg).html(),o={};if(i=i&&i.trim()?e.parseXML(a.xmlHead+i.trim()+a.xmlFoot):null,o.cel=n.find("Cell"),n.find("Cell").remove(),o.row=n.find("Row"),n.find("Row").remove(),o.col=n.find("Column"),n.find("Column").remove(),o.tbl=n.find("Table"),n.find("Table").remove(),o.ws=n.find("Worksheet"),n.find("Worksheet").remove(),i&&n.find("Styles").append(e(i).find("Style")),"function"==typeof t.replaceXml)t.replaceXml(r,n,o,t);else{if(!t.tblFlg.length)return!0;a.tblToXml(n,o,t)}a.saveFile(a.xmlDomToString(r),t.fileName)},createWsTbl:function(e,t,a){var r=t.ws.clone();r.attr("ss:Name",a),e.find("Workbook").append(r);var n=t.tbl.clone();return r.append(n),n},tblToXml:function(t,a,r){var n=this;r.tblFlg.each(function(i,o){"undefined"==typeof r.sheetsName[i]&&(r.sheetsName[i]=e(o).data("xml-ws-name"),r.sheetsName[i]||(r.sheetsName[i]="sheet"+i));var s=n.createWsTbl(t,a,r.sheetsName[i]);e(o).find("> colgroup > col").each(function(t,r){if("ignore"===e(r).data("excel-fg"))return!0;var n=a.col.clone(),i=e(r).data("width");i?(n.attr("ss:AutoFitWidth",0),n.attr("ss:Width",i)):n.attr("ss:AutoFitWidth",1),s.append(n)});var d=e(o).find("> thead > tr");if(d.length&&"ignore"!==d.data("excel-fg")){var l=a.row.clone(),c=d.data("height");c?(l.attr("ss:AutoFitHeight",0),l.attr("ss:Height",c)):l.attr("ss:AutoFitHeight",1),s.append(l),d.children("th").each(function(t,r){if("ignore"===e(r).data("excel-fg"))return!0;var i=a.cel.clone(),o=e(r);n.setAttrMerge(i,o),i.attr("ss:StyleID","Thead"),e.each(o.data(),function(e,t){return!e.startsWith("xml")||void i.attr("ss:"+e.substr(3),t)});var s=o.text();s&&(s=s.trim()),o.hasClass("number")&&i.children("Data").attr("ss:Type","Number"),i.children("Data").text(s),l.append(i)})}e(o).find("> tbody > tr").each(function(t,r){if("ignore"===e(r).data("excel-fg"))return!0;var i=a.row.clone(),o=e(r).data("height");o?(i.attr("ss:AutoFitHeight",0),i.attr("ss:Height",o)):i.attr("ss:AutoFitHeight",1),s.append(i),e(r).find("> td").each(function(t,r){if("ignore"===e(r).data("excel-fg"))return!0;var o=a.cel.clone(),s=e(r);n.setAttrMerge(o,s),e.each(s.data(),function(e,t){return!e.startsWith("xml")||void o.attr("ss:"+e.substr(3),t)});var d=s.text();d&&(d=d.trim()),s.hasClass("number")&&o.children("Data").attr("ss:Type","Number"),o.children("Data").text(d),i.append(o)})})})},setAttrMerge:function(t,a){var r={colspan:"ss:MergeAcross",rowspan:"ss:MergeDown"};e.each(r,function(e,r){var n=a.attr(e);n&&!isNaN(n)&&t.attr(r,parseInt(n)-1)})},base64:function(e){return a.btoa(unescape(encodeURIComponent(e)))},format:function(e,t){return e.replace(/{(\w+)}/g,function(e,a){return t[a]})},xmlDomToString:function(e){return this.replaceBrToBreak((new XMLSerializer).serializeToString(e))},stringToXml:function(e){var t=new DOMParser;return t.parseFromString(e,"text/xml")},saveFile:function(e,t,a){var r=this;"object"==typeof e&&(e=r.xmlDomToString(e),a&&(e='<?xml version="1.0"?>'+e));var n=new Blob([e],{type:"application/vnd.ms-excel;charset=utf-8"});saveAs(n,(t?t:"exportExcel")+".xls")}},r.normal={scrollTo:function(t){return!t.length||void e("html, body").stop().animate({scrollTop:t.offset().top},500)}},r.paginate={data:{},dom:[],html:{},url:{},moreType:{},moreLoad:{},hasData:{},init:function(t){var a=this;return t||(t=e("[data-page-list]")),a.option={numberPage:5},t.each(function(t,n){var i=e(n),o=i.data("page-list");if(!o)return!0;switch(a.dom.push(o),a.data[o]={},a.html[o]={itemWrapper:e('[data-page-item-wrapper="'+o+'"]')},a.html[o].itemHtml=a.html[o].itemWrapper.html(),a.url[o]=i.data("page-url"),a.moreType[o]=i.data("page-more"),a.moreLoad[o]=i.data("page-load"),a.moreType[o]&&["html","append","prepend"].indexOf(a.moreType[o])!==-1||(a.moreType[o]="html"),a.moreLoad[o]&&["scroll","paginate"].indexOf(a.moreLoad[o])!==-1||(a.moreLoad[o]="scroll"),a.html[o].itemWrapper.html(""),i.data("page-load")){case"scroll":a.loadMoreScroll(o);break;case"btn":a.loadMoreBtn(o);break;default:a.setHtmlPaginate(),a.loadMorePaginate(o)}var s=i.data("page-param");if(!s||"no"!==s){s||(s="page");var d=r.params();i.data("page",parseInt(d[s])-1)}("undefined"==typeof i.data("load-init")||i.data("load-init"))&&a.loadMoreItemAjax(o)}),a.paginateSearchInit(),a.paginateSearch(),a},setHtmlPaginate:function(){var t=this;return t.paginate?t:(t.paginate={number:e('[data-pager-item="number"]')[0].outerHTML,more:e('[data-pager-item="more"]')[0].outerHTML,fgFirst:'[data-pager-item="first"]',fgLast:'[data-pager-item="last"]',fgPrev:'[data-pager-item="prev"]',fgNext:'[data-pager-item="next"]',fgNumber:'[data-pager-item="number"]',fgRenderNumber:"[data-pager-page]"},e('[data-pager-item="more"]').remove(),t.paginate.wrapper=e('[data-pager-item="page"]')[0].outerHTML,e('[data-pager-item="page"]').remove(),void t.paginateClick())},setData:function(e,t){var a=this;return a.data[e]=t,a.exec(e),a},execAllDom:function(){var e=this;return e.dom.forEach(function(t){e.exec(t)}),e},exec:function(t){var a,n=this,i="";return"object"==typeof n.data[t]&&n.data[t].data&&n.data[t].data.length?(e.each(n.data[t].data,function(o,s){a=n.html[t].itemHtml,"object"==typeof r.paginate.beforeRender&&"function"==typeof r.paginate.beforeRender[t]&&(s=r.paginate.beforeRender[t](s)),e.each(s,function(e,t){null===t&&(t="");var r=new RegExp("{"+e+"}","gm");a=a.replace(r,t)}),"prepend"===n.moreType[t]?i=a+i:i+=a}),1===e('[data-page-list="'+t+'"]').data("page")?n.html[t].itemWrapper.html(i):n.html[t].itemWrapper[n.moreType[t]](i),"paginate"===n.moreLoad[t]?n.renderPaginate(t):n.renderBtnLoadmore(t),"html"===n.moreType[t]&&r.normal.scrollTo(n.html[t].itemWrapper),"object"==typeof r.paginate.afterDone&&"function"==typeof r.paginate.afterDone[t]&&r.paginate.afterDone[t](),e('[data-page-result="'+t+'"]').removeClass("hidden"),e('[data-page-noresult="'+t+'"]').addClass("hidden"),n.hasData[t]=!0,n):n.hasData[t]&&["append","prepend"].indexOf(n.moreType[t])>-1?(1==e('[data-page-list="'+t+'"]').data("page")&&n.showNoResult(t),!0):(n.showNoResult(t),n)},showNoResult:function(t){var a=this;e('[data-page-result="'+t+'"]').addClass("hidden"),e('[data-page-noresult="'+t+'"]').removeClass("hidden"),a.html[t].itemWrapper[a.moreType[t]]("")},loadMoreScroll:function(t){var r=this;e(a).scroll(function(){if(!r.data[t].is_next_page)return!0;if("prepend"===r.moreType[t]){var n=e(a).scrollTop(),i=e('[data-page-item-wrapper="'+t+'"]').offset().top;return n<i+10&&r.loadMoreItemAjax(t),!0}var n=e(a).scrollTop()+e(a).height(),i=e('[data-page-item-wrapper="'+t+'"]').offset().top+e('[data-page-item-wrapper="'+t+'"]').height();n>i-10&&r.loadMoreItemAjax(t)})},loadMoreBtn:function(a){var r=this;e(t).on("click",'[data-page-more-btn="'+a+'"]',function(){return!r.data[a].is_next_page||void r.loadMoreItemAjax(a)})},loadMorePaginate:function(e){var t=this;return!t.data[e].next_page_url||void t.loadMoreItemAjax(e)},renderBtnLoadmore:function(t){var a=this;a.data[t].is_next_page?e('[data-page-more-btn="'+t+'"]').removeClass("hidden"):e('[data-page-more-btn="'+t+'"]').addClass("hidden")},renderPaginate:function(t){var a=this,r=e('[data-page-paginate="'+t+'"]');if(!r.length)return!0;var n=a.data[t];if("object"!=typeof n||!n.last_page||1==n.last_page)return r.html(""),!0;var i=parseInt((a.option.numberPage-1)/2),o=n.current_page-i,s=n.current_page+i,d=n.current_page>1,l=n.current_page<n.last_page;o<1&&(s+=1-o,o=1),s>n.last_page&&(o-=s-n.last_page,o<1&&(o=1),s=n.last_page);var c=e(a.paginate.wrapper);d?(c.find(a.paginate.fgPrev).find(a.paginate.fgRenderNumber).attr("data-pager-page",n.current_page-1),c.find(a.paginate.fgFirst).find(a.paginate.fgRenderNumber).attr("data-pager-page",1)):(c.find(a.paginate.fgPrev).addClass("disabled"),c.find(a.paginate.fgFirst).addClass("disabled"));var f=c.find(a.paginate.fgNext);l?(f.find(a.paginate.fgRenderNumber).attr("data-pager-page",n.current_page+1),c.find(a.paginate.fgLast).find(a.paginate.fgRenderNumber).attr("data-pager-page",n.last_page)):(f.addClass("disabled"),c.find(a.paginate.fgLast).addClass("disabled")),c.find(a.paginate.fgNumber).remove();for(var p=o;p<=s;p++){var u=e(a.paginate.number);u.find(a.paginate.fgRenderNumber).attr("data-pager-page",p).text(p),p===parseInt(n.current_page)&&u.addClass("active"),f.before(u)}return s<n.last_page&&f.before(a.paginate.more),r.html(c[0].outerHTML),a},paginateClick:function(){var a=this;e(t).on("click","[data-page-paginate] [data-pager-page]",function(t){t.preventDefault();var r=e(this),n=r.closest("[data-page-paginate]").data("page-paginate"),i=r.data("pager-page");return!(i&&!isNaN(i))||(i=parseInt(i),i<1||(e('[data-page-list="'+n+'"]').data("page",i-1),void a.loadMoreItemAjax(n)))})},loadMoreItemAjax:function(t,a){var n=this,i=e('[data-page-list="'+t+'"]');if(!n.url[t]||i.data("process"))return!0;a="object"==typeof a?a:{},a.reset?e('[data-page-reset-loading="'+t+'"]').removeClass("hidden"):e('[data-page-loading="'+t+'"]').removeClass("hidden"),e('[data-page-loading-org="'+t+'"]').addClass("hidden"),i.data("process",1);var o=r.params(),s=i.data("page");return(isNaN(s)||!s||s<1)&&0!=s&&(s=0),o.page=s+1,"object"==typeof r.paginate.beforeSendRequest&&"function"==typeof r.paginate.beforeSendRequest[t]&&(o=r.paginate.beforeSendRequest[t](o)),e.ajax({url:n.url[t],type:"GET",dataType:"json",data:o,success:function(a){"object"==typeof r.paginate.beforeExecSuccess&&"function"==typeof r.paginate.beforeExecSuccess[t]&&r.paginate.beforeExecSuccess[t](a),i.data("page",o.page),n.setData(t,a),"string"==typeof contactGetListUrl&&n.url[t]===contactGetListUrl&&(e('[data-can-show-phone="'+NOT_SHOW_PHONE+'"]').map(function(t,a){e(a).next().remove(),e(a).remove()}),e('[data-can-show-birthday="'+NOT_SHOW_BIRTHDAY+'"]').remove(),e("[data-can-show-birthday]").map(function(t,a){"BR"===e(a).prev().prop("tagName")&&"BR"===e(a).prev().prev().prop("tagName")&&(e(a).prev().remove(),e(a).parent().append("<br/>"))}))},complete:function(){i.data("process",0),e('[data-page-loading="'+t+'"]').addClass("hidden"),e('[data-page-reset-loading="'+t+'"]').addClass("hidden"),e('[data-page-loading-org="'+t+'"]').removeClass("hidden")}}),n},paginateSearch:function(){var t=this;e("[data-page-search]").keypress(function(a){if(13===a.keyCode){a.preventDefault();var r=e(this),n=r.data("page-search");if(!n)return!0;t.searchSubmit(n)}}),e("[data-page-search-btn]").click(function(a){a.preventDefault();var r=e(this),n=r.data("page-search-btn");return!n||void t.searchSubmit(n)}),e("[data-page-search-btn]").prop("disabled",!1)},searchSubmit:function(t){var a=this,n=r.params(),i=[],o=[];e('[data-page-search="'+t+'"]').each(function(a,r){var s=e(r).attr("name");if("checkbox"===e(r).attr("type")){if(i.indexOf(s)>-1)return!0;i.push(s);var d="";return e('[name="'+s+'"][data-page-search="'+t+'"]:checked').each(function(){d+=e(this).val()+"-"}),d=d.slice(0,-1),d?n[s]=d:delete n[s],!0}if("radio"===e(r).attr("type")){var s=e(r).attr("name");if(o.indexOf(s)>-1)return!0;o.push(s);var l=e('[name="'+s+'"][data-page-search="'+t+'"]:checked');return l.length?n[s]=l.val():delete n[s],!0}var c=e(r).val().trim();""+c==""?delete n[s]:n[s]=c}),e('[data-page-list="'+t+'"]').data("page",0),r.urlReplaceEncode(n),a.loadMoreItemAjax(t)},paginateSearchInit:function(){if(!e("[data-page-search]").length)return!0;var t=r.params();e.each(t,function(t,a){var r=e('[name="'+t+'"][data-page-search]');if(!r.length)return!0;if(a=a.trim(),"radio"===r.attr("type"))return e('[name="'+t+'"][data-page-search][value="'+a+'"]').prop("checked",!0),!0;if("checkbox"===r.attr("type")){var n=a.split("-");return e.each(n,function(a,r){e('[name="'+t+'"][data-page-search][value="'+r+'"]').prop("checked",!0)}),!0}r.val(a)})},reset:function(t){var a=this;e('[data-page-list="'+t+'"]').data("page",0),r.urlReplace(null,{},!1),a.loadMoreItemAjax(t,{reset:!0}),e('[data-page-search="'+t+'"]').val(""),a.html[t].itemWrapper.html("")}},r.moreHeight={o:{},init:function(a,r){"undefined"!=typeof a&&a&&a.length||(a=e("[data-more-height]"));var n=this;n.o=e.extend({textMore:"view more",textLess:"view less",height:150,css:{height:"200px",overflow:"hidden",display:"block",position:"relative","margin-bottom":"0px"},cssMore:{height:"auto",overflow:"unset"},cssMargin:40,cssBsd:{"box-shadow":"rgba(176, 176, 176, 0.76) 0px 20px 40px 30px"}},r),e.each(a,function(){n.viewMore(e(this))}),e(t).on("click","[data-more-btn]",function(t){t.preventDefault(),n.actionBtn(e(this))})},viewMore:function(t,a){var r=this,n=t.data("more-height");n||(n=r.o.height),t.removeAttr("style"),t.css("display","block");var i=t.outerHeight();return i<=n&&!a||(t.css(e.extend({},r.o.css,{height:""+n+"px"})),!!t.find("[data-more-btn]").length||(t.append('<div class="block-more" style="position: absolute;bottom: 0;left: 0;width: 100%;" d-bm-wrap><div class="bm-shadow" d-bm-sd></div><button type="button" data-more-btn="more" class="btn btn-primary">'+r.o.textMore+"</button></div>"),void t.find("[d-bm-sd]").css(r.o.cssBsd)))},actionBtn:function(e){var t=this,a=e.data("more-btn"),r=e.closest("[d-bm-wrap]"),n=r.closest("[data-more-height]");"more"===a?(e.text(t.o.textLess),n.css("margin-bottom",""+t.o.cssMargin+"px").css(t.o.cssMore),r.css("bottom","-"+t.o.cssMargin+"px"),r.find("[d-bm-sd]").removeAttr("style"),e.data("more-btn","less")):(e.text(t.o.textMore),t.viewMore(n,!0),r.css("bottom","0px"),r.find("[d-bm-sd]").css(t.o.cssBsd),e.data("more-btn","more"))}},r.onload={init:function(){var e=this;e.momentSD()},momentSD:function(){"function"==typeof moment&&"function"==typeof moment.locale&&moment.locale("en",{week:{dow:1}})}},e(a).load(function(){r.formAjax.init(),r.simple.textShort(null,!0),r.onload.init()}),a.RKExternal=r}(jQuery,document,window);