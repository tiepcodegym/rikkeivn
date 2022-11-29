var tmpEle=null;!function($){function getSelectionCoords(e){e=e||window;var t,n,o,i=e.document,r=i.selection,l=0,a=0;if(r)"Control"!=r.type&&(t=r.createRange(),t.collapse(!0),l=t.boundingLeft,a=t.boundingTop);else if(e.getSelection&&(r=e.getSelection(),r.rangeCount&&(t=r.getRangeAt(0).cloneRange(),t.getClientRects&&(t.collapse(!0),n=t.getClientRects(),n.length>0&&(o=n[0]),l=o.left,a=o.top),0==l&&0==a))){var s=i.createElement("span");if(s.getClientRects){s.appendChild(i.createTextNode("​")),t.insertNode(s),o=s.getClientRects()[0],l=o.left,a=o.top;var p=s.parentNode;p.removeChild(s),p.normalize()}}return{x:l,y:a}}function getSelectionEndPositionInCurrentLine(){var e=0;if(window.getSelection){var t=window.getSelection();e=t.focusOffset}return e}var KEY={AT:64,BACKSPACE:8,DELETE:46,TAB:9,ESC:27,RETURN:13,LEFT:37,UP:38,RIGHT:39,DOWN:40,SPACE:32,HOME:36,END:35,COMMA:188,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190};jQuery.fn.mentiony=function(e,t){var n={debug:0,applyInitialSize:!0,globalTimeout:null,timeOut:400,triggerChar:"@",onDataRequest:function(e,t,n){},onKeyPress:function(e,t,n){t.trigger(e)},onKeyUp:function(e,t,n){t.trigger(e)},onBlur:function(e,t,n){t.trigger(e)},onPaste:function(e,t,n){t.trigger(e)},onInput:function(e,t){},popoverOffset:{x:-30,y:0},templates:{container:'<div id="mentiony-container-[ID]" class="mentiony-container"></div>',content:'<div id="mentiony-content-[ID]" class="mentiony-content" contenteditable="true"></div>',popover:'<div id="mentiony-popover-[ID]" class="mentiony-popover"></div>',list:'<ul id="mentiony-popover-[ID]" class="mentiony-list"></ul>',listItem:'<li class="mentiony-item" data-item-id=""><div class="row"><div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"><img src="https://avatars2.githubusercontent.com/u/1859127?v=3&s=140"></div><div class="pl0 col-xs-9 col-sm-9 col-md-9 col-lg-9"><p class="title">Company name</p><p class="help-block">Addition information</p></div></div></li>',normalText:'<span class="normal-text">&nbsp;</span>',highlight:'<span class="highlight"></span>',highlightContent:'<a href="[HREF]" data-item-id="[ITEM_ID]"  class="mentiony-link">[TEXT]</a>'}};"object"!=typeof e&&e||(t=e);var o=$.extend({},n,t);return this.each(function(){var t=$.data(this,"mentiony")||$.data(this,"mentiony",new MentionsInput(o));return"function"==typeof t[e]?t[e].apply(this,Array.prototype.slice.call(outerArguments,1)):"object"!=typeof e&&e?void $.error("Method "+e+" does not exist"):t.init.call(this,this)})};var MentionsInput=function(settings){function initTextArea(e){if(elmInputBox=$(e),"true"!=elmInputBox.attr("data-mentions-input")){elmInputBox.attr("data-mentions-input","true"),0==elmInputBox.attr("id").length?(elmInputBoxId="mentiony-input-"+inputId,elmInputBox.attr("id",elmInputBoxId)):elmInputBoxId=elmInputBox.attr("id"),elmInputBoxInitialWidth=elmInputBox.prop("scrollWidth"),elmInputBoxInitialHeight=elmInputBox.prop("scrollHeight"),elmInputBoxContainer=$(settings.templates.container.replace("[ID]",inputId)),elmInputBoxContent=$(settings.templates.content.replace("[ID]",inputId));var t=elmInputBox.attr("placeholder");"undefined"==typeof t&&(t=elmInputBox.text()),elmInputBoxContent.attr("data-placeholder",t),elmInputBoxContainer.append(elmInputBox.clone().addClass("mention-input-hidden")),elmInputBoxContainer.append(elmInputBoxContent),elmInputBox.replaceWith(elmInputBoxContainer),popoverEle=$(settings.templates.popover.replace("[ID]",inputId)),list=$(settings.templates.list.replace("[ID]",inputId)),elmInputBoxContainer.append(popoverEle),popoverEle.append(list),elmInputBox=$("#"+elmInputBoxId);var n=parseInt(elmInputBoxContainer.css("padding"));settings.applyInitialSize?(elmInputBoxContainer.addClass("initial-size"),elmInputBoxContainer.css({width:elmInputBoxInitialWidth+"px"}),elmInputBoxContent.width(elmInputBoxInitialWidth-2*n+"px")):elmInputBoxContainer.addClass("auto-size"),elmInputBoxContent.css({minHeight:elmInputBoxInitialHeight+"px"}),elmInputBoxContentAbsPosition=elmInputBoxContent.offset(),editableContentLineHeightPx=parseInt($(elmInputBoxContent.css("line-height")).selector),elmInputBoxContent.bind("keydown",onInputBoxKeyDown),elmInputBoxContent.bind("keypress",onInputBoxKeyPress),elmInputBoxContent.bind("input",onInputBoxInput),elmInputBoxContent.bind("keyup",onInputBoxKeyUp),elmInputBoxContent.bind("click",onInputBoxClick),elmInputBoxContent.bind("blur",onInputBoxBlur),elmInputBoxContent.bind("paste",onInputBoxPaste)}}function onInputBoxKeyDown(e){if(events={keyDown:!0,keyPress:!1,input:!1,keyup:!1},dropDownShowing)return handleUserChooseOption(e)}function onInputBoxKeyPress(e){events.keyPress=!0,needMention||(needMention=e.keyCode===KEY.AT||e.which===KEY.AT),settings.onKeyPress.call(this,e,elmInputBox,elmInputBoxContent)}function onInputBoxInput(e){if(events.input=!0,null!==e.originalEvent.data)var t=e.originalEvent.data.charCodeAt(0);needMention||(needMention=e.keyCode===KEY.AT||e.which===KEY.AT||t===KEY.AT),settings.onInput.call(this,elmInputBox,elmInputBoxContent)}function onInputBoxKeyUp(e){events.keyup=!0,events.input&&updateDataInputData(e),needMention&&(e.keyCode===KEY.RETURN&&e.which!==KEY.RETURN||!events.input&&e.keyCode!==KEY.LEFT&&e.which!==KEY.LEFT&&e.keyCode!==KEY.RIGHT&&e.which!==KEY.RIGHT||(updateMentionKeyword(e),doSearchAndShow())),settings.onKeyUp.call(this,e,elmInputBox,elmInputBoxContent)}function onInputBoxClick(e){needMention&&(updateMentionKeyword(e),doSearchAndShow())}function onInputBoxBlur(e){settings.onBlur.call(this,e,elmInputBox,elmInputBoxContent)}function onInputBoxPaste(e){settings.onPaste.call(this,e,elmInputBox,elmInputBoxContent)}function onListItemClick(e){setSelectedMention($(this)),choseMentionOptions(!0)}function updateDataInputData(e){var t=elmInputBoxContent.html();elmInputBox.val(convertSpace(t)),log(elmInputBox.val(),"elmInputBoxText : "),tmpEle=elmInputBox}function trimSpace(e){return e.replace(/^(&nbsp;|&nbsp|\s)+|(&nbsp;|&nbsp|\s)+$/g,"")}function convertSpace(e){return e.replace(/(&nbsp;)+/g," ")}function doSearchAndShow(){settings.timeOut>0?(null!==settings.globalTimeout&&clearTimeout(settings.globalTimeout),settings.globalTimeout=setTimeout(function(){settings.globalTimeout=null,settings.onDataRequest.call(this,"search",currentMention.keyword,onDataRequestCompleteCallback)},settings.timeOut)):settings.onDataRequest.call(this,"search",currentMention.keyword,onDataRequestCompleteCallback)}function populateDropdown(e,t){list.empty(),currentMention.jqueryDomNode=null,currentMention.mentionItemDataSet=t,t.length?(currentMention.charAtFound===!0&&showDropDown(),t.forEach(function(e,t){var n=$(settings.templates.listItem);n.attr("data-item-id",e.id),n.find("img:first").attr("src",e.avatar_url),n.find("p.title:first").html(e.name),n.find("p.help-block:first").html(e.info),n.bind("click",onListItemClick),list.append(n)})):hideDropDown()}function showDropDown(){var e=getSelectionCoords();dropDownShowing=!0,popoverEle.css({display:"block",top:e.y-(elmInputBoxContentAbsPosition.top-$(document).scrollTop())+(editableContentLineHeightPx+10),left:e.x-elmInputBoxContentAbsPosition.left})}function hideDropDown(){dropDownShowing=!1,popoverEle.css({display:"none"})}function handleUserChooseOption(e){return!dropDownShowing||(e.keyCode===KEY.UP||e.which===KEY.UP||e.keyCode===KEY.DOWN||e.which===KEY.DOWN?(choosingMentionOptions(e),!1):e.keyCode!==KEY.HOME&&e.which!==KEY.HOME&&e.keyCode!==KEY.RETURN&&e.which!==KEY.RETURN&&e.keyCode!==KEY.TAB&&e.which!==KEY.TAB||(choseMentionOptions(),!1))}function updateMentionKeyword(e){if(document.selection)var t=document.selection.createRange();else var t=window.getSelection().anchorNode;var n=t.data;"undefined"==typeof n&&(n="");var o=getSelectionEndPositionInCurrentLine();currentMention.lastActiveNode=t,currentMention.keyword="";for(var i=o-1,r=!0;r;){var l=n.charAt(i);""!==l&&l!==settings.triggerChar||(r=!1),i--}currentMention.keyword=n.substring(i+1,o),currentMention.keyword.indexOf(settings.triggerChar)===-1?(currentMention.keyword="",currentMention.charAtFound=!1,hideDropDown()):(currentMention.keyword=currentMention.keyword.substring(1,o),currentMention.charAtFound=!0),log(currentMention.keyword,"currentMention.keyword")}function getMentionKeyword(){return currentMention.keyword}function setSelectedMention(e){currentMention.jqueryDomNode=e,updateSelectedMentionUI(e),log(e,"setSelectedMention item: ")}function updateSelectedMentionUI(e){$.each(list.children(),function(e,t){$(t).removeClass("active")}),e.addClass("active")}function choosingMentionOptions(e){log("choosingMentionOptions"),null===currentMention.jqueryDomNode&&setSelectedMention(list.children().first());var t=[];e.keyCode===KEY.DOWN||e.which===KEY.DOWN?t=currentMention.jqueryDomNode.next():e.keyCode!==KEY.UP&&e.which!==KEY.UP||(t=currentMention.jqueryDomNode.prev()),0===t.length&&(t=currentMention.jqueryDomNode),setSelectedMention(t)}function choseMentionOptions(e){"undefined"===e&&(e=!1),log("choosedMentionOptions by "+(e?"Mouse":"Keyboard"));var t={};null===currentMention.jqueryDomNode&&setSelectedMention(list.children().first());for(var n=currentMention.jqueryDomNode.attr("data-item-id"),o=0,i=currentMention.mentionItemDataSet.length;o<i;o++)if(n==currentMention.mentionItemDataSet[o].id){t=currentMention.mentionItemDataSet[o];break}var r=$(settings.templates.highlight),l=$(settings.templates.highlightContent.replace("[HREF]",t.href).replace("[TEXT]",t.name).replace("[ITEM_ID]",t.id));r.append(l),replaceTextInRange("@"+currentMention.keyword,r.prop("outerHTML"),e),log("Finish mention","","warn"),needMention=!1,currentMention.keyword="",hideDropDown(),updateDataInputData()}function log(msg,prefix,level){"undefined"==typeof level&&(level="log"),1===settings.debug&&eval("console."+level+"(inputId, prefix ? prefix + ':' : '', msg);")}function replaceTextInRange(e,t,n){var o,i={startBefore:0,startAfter:0,stopBefore:0,stopAfter:0},r=window.getSelection();if("undefined"!==n&&n===!0){var l=currentMention.lastActiveNode;try{var a=l.data.length}catch(s){log(s,"lastActiveNode Error:");var a=0}o=document.createRange(),o.setStart(l,a),o.collapse(!0),r.removeAllRanges(),r.addRange(o)}var p=!1,u=r.focusOffset,c=u-e.length;window.getSelection?(p=!1,r=window.getSelection(),r.rangeCount>0&&(o=r.getRangeAt(0).cloneRange(),o.collapse(!0))):(r=document.selection)&&"Control"!=r.type&&(o=r.createRange(),p=!0),c!==u&&(o.setStart(r.anchorNode,c),o.setEnd(r.anchorNode,u),o.deleteContents());var d=document.createElement("span");d.setAttribute("class","mention-area"),d.innerHTML=t,o.insertNode(d),o.setEnd(r.focusNode,o.endContainer.length),i.startBefore=c,i.stopBefore=u,i.startAfter=c,i.stopAfter=c+d.innerText.length;var m=!1;for(d=$(r.anchorNode);!m;)0===d.next().text().length?m=!0:d=d.next();var I=$(settings.templates.normalText).insertAfter(d);return o=document.createRange(),o.setStartAfter(I.get(0)),o.setEndAfter(I.get(0)),r.removeAllRanges(),r.addRange(o),i}var elmInputBoxContainer,elmInputBoxContent,elmInputBox,elmInputBoxInitialWidth,elmInputBoxInitialHeight,editableContentLineHeightPx,popoverEle,list,elmInputBoxId,elmInputBoxContentAbsPosition={top:0,left:0},dropDownShowing=!1,events={keyDown:!1,keyPress:!1,input:!1,keyup:!1},currentMention={keyword:"",jqueryDomNode:null,mentionItemDataSet:[],lastActiveNode:0,charAtFound:!1},needMention=!1,inputId=Math.random().toString(36).substr(2,6),onDataRequestCompleteCallback=function(e){populateDropdown(currentMention.keyword,e)};return{init:function(e){initTextArea(e)}}}}(jQuery);