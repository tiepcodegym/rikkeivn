/**
 * listen to menu item click action 
 */
$.fn.menuItemClickListener = function () {
    $(this).on("select_node.jstree", function (e, data) {
        //reveal delete button
        if ($('#delete').css('visibility') == 'hidden') {
            $('#delete').css({'visibility': 'visible'});
            $('#delete').removeClass('hidden');
        }  
        //add new nodes 
        try {          
            $('#form-post-edit').validate().resetForm();
            setHelpbyID(data)
        } catch (e) {
            if (e instanceof TypeError) {
            }
        }       
    });
} 

/**
 * listen to menu item click action in view
 */
$.fn.menuItemViewClickListener = function () {
    $(this).on("select_node.jstree", function (e, data) {
        //add new nodes    
        try {            
            $('.button-manage > #edit').css('visibility','visible');
            setHelpContentbyID(data.selected.toString());            
        } catch (e) {
            if (e instanceof TypeError) {
            }
        }
    });
}

/**
 * init jstree for manage page
 */
$.fn.initMenu = function () {   
    var helpId = typeof helpModId !== 'undefined' ? helpModId : window.location.pathname.match("\\d+$"); 
    //init jstree
    if (pageType == typeArr[2]) {
        $(this).jstree({
            "core": {
                "check_callback": true,
                "dblclick_toggle": false,
                "themes": {
                    "name" : "proton",
                    "responsive": true
                },
                "data" : menu
            },
            types: {
                "parent": {
                  "icon" : "fa fa-folder-o"
                },
                "leaf": {
                  "icon" : "fa fa-file-o"
                },
                "default" : {
                }
            },
            plugins: ["types"],
        });
    } else {
        $(this).jstree({
            "core": {
                "check_callback": true,
                "dblclick_toggle": false,
                "themes": {
                    "name" : "proton",     
                    "responsive": true
                },
                "data" : menu
            },
            types: {
                "parent": {
                  "icon" : "fa fa-folder-o"
                },
                "leaf": {
                  "icon" : "fa fa-file-o"
                },
                "default" : {
                }
            },
            plugins: ["contextmenu", "types"],         
            contextmenu: {items: customMenu}
        });        
    }
    $(this).on('ready.jstree', function() {        
        try {
            if (helpId){
                $('#container').jstree().select_node(helpId);
            }             
        } catch (e) {
            if (e instanceof TypeError) {
            }
        }
    });    
}

/**
 * init save and delete buttons 
 */
function initManageButton() {
    if (pageType == typeArr[0]) {
        $('#delete').css({'visibility': 'hidden'});
        $('#delete').addClass('hidden');
    } else {
        $('#delete').css({'visibility': 'visible'});
        $('#delete').removeClass('hidden');
    }
}

/**
 * set Help's properties
 * @param string id
 * @param string title
 * @param string active
 * @param string parents
 * @param string order
 * @param string content
 */
function setHelp(id, title, active, parent, order, content, slug){
    $('#help-id').val(id);
    $('#help-title').val(title);
    $('#help-active').val(active);
    $('#help-parent').val(parent).trigger('change.select2');
    $('#help-slug').val(slug);
    setParentHelp(id);    
    $('#help-order').val(order);    
    setCKEditorContent('help-content', content);
    window.history.pushState("", "", "/help/edit/" + id);
}

/**
 * set parent options for help
 * @param string helpId
 */
function setParentHelp(helpId){
    $('#help-parent option').prop('disabled',false);    
    var option = $('#help-parent option[value="'+helpId+'"]');
    var next = option.next('option');    
    option.prop('disabled',true);
    
    try { 
        var ind = option.attr('class').split('-').pop();  
        
        while (ind < next.attr('class').split('-').pop()){
            $('#help-parent option[value="'+next.val()+'"]').prop('disabled',true);
            next = next.next('option');
        }        
    } catch (e) {
        if (e instanceof TypeError) {            
        }
    }   
    $("#help-parent").reloadSelect2();
}

/**
 * customer jstree context menu
 */
function customMenu(node) {
    // The default set of all items    
    var items = {
        createItem: {// The "create child" menu item
            label: "Create Child",
            action: function () {
                var option = $('#help-id').val();           
                $('#addHelp').trigger("click");             
                $('#help-parent').val(option).trigger('change.select2');
            }
        },
        deleteItem: {// The "delete" menu item
            label: "Delete",
            action: function () {
                if(!$("#delete").is(':disabled')){              
                    $("#delete").trigger("click");
                }
            }
        }
    };
    return items;
}

/**
 * set help info for edit
 * @param int itemID id of menu item
 */
function setHelpbyID(itemID) {
    waiting();
    $.ajax({
        type: "get",
        url: getHelp,
        data: {
            id: itemID.selected.toString(),
            _token: _token
        },
        success: function (data) {            
            if(!data['parent']){
                data['parent'] = '#';
            }
            setHelp(data['id'], data['title'], data['active'], data['parent'], data['order'], data['content'], data['slug']);          
            finish();
        },
        error: function(xhr, ajaxOptions, thrownError){
            finish();
        }
    });
}

function viewFile(path) {
   $('#modal-view-file iframe').html('');
   $('#modal-view-file iframe').attr('src', path);
   $('#modal-view-file').modal('show');
}

/**
 * add element view file office with google
 */
function appendViewFileElement() {
    $('#help-content a').each(function() {
        var thisN = $(this);
        var url   = $(this).attr('href');
        if (url && url.length > 0) {
            var other = url.replace(/^(http|https):\/\/(.){0,99}(\.com)/, '');
            if (url === other) {
                var filename = other.substring(other.lastIndexOf('/')+1);
                if (/(pdf|doc|docx|jpg|xlsx|pptx|xls|csv|png)$/ig.test(filename)) {
                    $.ajax({
                        url:url,
                        error: function() {},
                        success: function() {
                            var pathFile = 'http://'+window.location.hostname+url;
                            var path = JSON.stringify("https://docs.google.com/gview?url=" +pathFile+"&embedded=true");
                            thisN.parent().append("&nbsp;&nbsp;<a style='color:red;' data-toggle='modal' href='#' onclick='viewFile("+path+")'>View File</a>");
                        },
                    });
                }
            }
        }
    })
}

/**
 * add help content to view
 * @param int itemID id of menu item
 */
function setHelpContentbyID(itemID) {
    waiting();
    $.ajax({
        type: "get",
        url: getHelpContent,
        data: {
            id: itemID,
            _token: _token
        },
        success: function (data) {
            $('#help-title').html(htmlEntities(data['title']));
            $('#help-content').html(data['content']);
            window.history.pushState("", "", "/help/view/" + data['id']);
            appendViewFileElement();
            finish();
        },
        error: function(xhr, ajaxOptions, thrownError){
            finish();
        }
    });
}

/**
 * create new Help using ajax
 */
function createHelp() {
    //hide delete button
    if ($('#delete').css('visibility') == 'visible') {
        $('#delete').css({'visibility': 'hidden'});
        $('#delete').addClass('hidden');
    }
    //create Help
    waiting();
    $('#container').jstree().deselect_all();
    
    $('#help-id').val('');
    $('#help-title').val('');
    $('#help-active').val(0);
    $('#help-parent option').prop('disabled',false); 
    $('#help-slug').val(''); 
    $('#help-parent').reloadSelect2();
    $('#help-parent').val('#').trigger('change.select2');
    $('#help-order').val(''); 
     
    removeCKEditorContent('help-content', function (){});
    window.history.pushState("", "", "/help/create");
    finish();
}

/**
 * save creat/edit Help by Ajax
 */
function saveHelp() {
    var itemHelp = {};
    itemHelp['id'] = $('#help-id').val();
    itemHelp['title'] = $('#help-title').val();
    itemHelp['active'] = $('#help-active').find(":selected").val();
    itemHelp['parent'] = $('#help-parent').find(":selected").val();
    itemHelp['order'] = $('#help-order').val();
    itemHelp['content'] = CKEDITOR.instances['help-content'].getData();    

    waiting();
    $.ajax({
        type: "post",
        url: saveHelpRoute,
        data: {
            item: JSON.stringify(itemHelp),
            _token: _token
        },
        success: function (data) {
            if (data['success']) {
                $('#modal-success-notification > .modal-dialog > .modal-content > .modal-header > .modal-title').text(data['notification']);
                $('#modal-success-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(data['message']);
                $('#modal-success-notification').modal('show');
                if (data['data']) {
                    setTimeout(function(){ 
                        window.location.href = data['routerView'];
                    }, 1500);
                }
                if ($('#help-id').val()) { //edit help                            
                    //rename menu
                    var selected = $('#container').find('.jstree-clicked').parent().attr('id');                     
                    $('#container').jstree().rename_node(selected, htmlEntities(data['data']['title'])); 
                    
                    if(!data['data']['parent']){
                        data['data']['parent'] = '#';
                    }                    
                    //if parent changed
                    if (data['data']['parent'] != $('#container').jstree().get_parent(selected)){       
                        $('option[value="'+data['data']['id']+'"]').detach().insertAfter('option[value="'+data['data']['parent']+'"]');                      
                        
                        if ($('#container').jstree().is_loaded(data['data']['parent'])){
                            $('#container').jstree().move_node(selected, data['data']['parent'], 'last');
                        }
                        else{
                            $('#container').jstree().delete_node(selected);
                        }
                    }
                    
                    //rename option     
                    var parentLvl = -1;
                    if(data['data']['parent'] != '#'){//parent not root
                        var parentLvl = parseInt($('#help-parent option[value="'+data['data']['parent']+'"]').attr('class').split('-').pop());                        
                    }
                    $('#help-parent option[value="'+data['data']['id']+'"]').text(appendLevel(parentLvl+1)+data['data']['title']);                
                    $('#help-parent').reloadSelect2();
                    //select help
                    $('#'+selected+' > a').addClass('jstree-clicked');
                } else { //create help                 
                    $('#container').jstree("deselect_all");
                    $('#delete').css({'visibility': 'visible'});
                    $('#delete').removeClass('hidden');
                    window.history.pushState("", "", "/help/edit/" + data['data']['id']); 
                    $('#help-id').val(data['data']['id']);
                    
                    //create new help menu item
                    if(!data['data']['parent']){
                        data['data']['parent'] = '#';
                    }                    
                    if ($('#container').jstree().is_loaded(data['data']['parent'])) {
                        $('#container').jstree().create_node(data['data']['parent'], {
                            'id': data['data']['id'],
                            'text': htmlEntities(data['data']['title']),
                            'type': 'leaf'
                        }, 'last', function () {});                        
                        //select new help item
                        $('#'+data['data']['id']+' > a').addClass('jstree-clicked');                                                            
                    } 
                    var parentLvl = -1;
                    if(data['data']['parent'] != '#'){//parent not root
                        var parentLvl = parseInt($('#help-parent option[value="'+data['data']['parent']+'"]').attr('class').split('-').pop());                   
                        var option = '<option class="level-'+(parentLvl+1)+'" value="'+data['data']['id']+'" disabled>'
                                        +appendLevel(parentLvl+1)+htmlEntities(data['data']['title'])+'</option>';
                    }
                    else{
                        var option = '<option class="level-0" value="'+data['data']['id']+'" disabled>'+appendLevel(parentLvl+1)+htmlEntities(data['data']['title'])+'</option>';    
                    }     
                   
                    $(option).insertAfter('option[value="'+data['data']['parent']+'"]');    
                    $('#help-parent').reloadSelect2();
                }              
            }
            if (data['error']) {
                $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-header > .modal-title').text(data['notification']);
                $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(data['message']);
                $('#modal-warning-notification').modal('show');
            }
            $('#save').prop('disabled',false);
            finish();
        },
        error: function(xhr, ajaxOptions, thrownError){
            finish();
        }
    });
}

/**
 * return level of node
 * @param {type} lvl
 * @returns {String} level of node
 */
function appendLevel(lvl){
    var ind = "";
    for (var i=0; i<lvl; i++){
        ind += "- ";
    }
    return ind;
}

/**
 * delete Help using Ajax
 */
function deleteHelp() {
    var deletedID = $('#help-id').val();   
    if ($('#container').jstree().is_leaf(deletedID)){
        $('#help-confirm-modal').find('.modal-body .text-default').text('Bạn có chắc chắn xóa đối tượng không?');
    }
    else{
        $('#help-confirm-modal').find('.modal-body .text-default').text('Bạn có chắc chắn xóa đối tượng không? Ấn đồng ý sẽ xóa cả những đối tượng con!');
    }
    
    $('#help-confirm-modal').modal('show');
    $('#help-confirm-modal').on('show.bs.modal', function (e) {
        $(this).find('.modal-footer .btn-ok').show();        
        $(this).find('.modal-body .text-default').show(); 
    });        

    $('#help-confirm-modal > .modal-dialog > .modal-content > .modal-footer > .btn-ok').one('click', function(){  
        $('#help-confirm-modal').modal('hide');        
        $.ajax({
            type: "post",
            url: deleteHelpRoute,
            data: {
                id: deletedID,
                _token: _token
            },
            success: function (data) {
                if (data['success']) {
                    $('option:disabled').remove();
                    $('#container').jstree().delete_node(deletedID);
                    $('#addHelp').click();
                    $('#modal-success-notification > .modal-dialog > .modal-content > .modal-header > .modal-title').text(data['notification']);
                    $('#modal-success-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(data['message']);
                    $('#modal-success-notification').modal('show');
                    setTimeout(function() { 
                        window.location.href = data['routerView'];
                    }, 1500);
                }
                if (data['error']) {
                    $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-header > .modal-title').text(data['notification']);
                    $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(data['message']);
                    $('#modal-warning-notification').modal('show');
                }                   
            }
        });
    });    
} 

/**
 * remove content in CKEditor
 * @param int $elementID ckeditor element's id * 
 */
function removeCKEditorContent($elementID) {
    try {
        CKEDITOR.instances[$elementID].setData('');
    } catch (e) {
        if (e instanceof TypeError) {
        }
    }
}

/**
 * add content in CKEditor
 * @param int $elementID ckeditor element's id
 */
function setCKEditorContent($elementID, $content) {
    try {
        CKEDITOR.instances[$elementID].setData($content);
    } catch (e) {
        if (e instanceof TypeError) {
        }
    }
}

/**
 * validate form
 */
function validateForm() {
    $('#form-post-edit').validate({
        rules: {
            'help[title]': {
                required: true,
                maxlength: 255
            },
            'help[active]': {
                required: true
            },
            'help[order]': {
                number: true,
                greaterThan: -1
            }
        },
        messages: {
            'help[title]': {
                required: "&nbsp&nbsp"+validateErr[0],
                maxlength: "&nbsp&nbsp"+validateErr[1]
            },
            'help[active]': {
                required: "&nbsp&nbsp"+validateErr[0]
            },
            'help[order]': {
                greaterThan: "&nbsp&nbsp"+validateErr[2]
            }
        },
        submitHandler: function () {
             saveHelp();
             return false; // required to block normal submit
        }
    }).resetForm();

    $.validator.addMethod('greaterThan', function (value, element, param) {
        return value > param;
    });
}

/**
 * search for help
 * @returns array help menu items that contain key
 */
function searchHelps(key) {
    waiting()
    $('#container').jstree("deselect_all");
    key = htmlEntities(key);
    if(!key){
        if($("#search-empty").length == 0){
            $('<p id="search-empty" style="color:red; font-size: 16px">&nbsp&nbsp'+searchErr[1]+'</p>').insertAfter($('.display-search'));
        }  
        finish();finish();
        setTimeout(function () {
           $('#search-empty').remove();
        }, 1500);         
        return false;
    }
    $.ajax({            
        type: "get",
        url: searchHelp,       
        data: {
            id: key,
            type : pageType,
            _token: _token
        },
        success: function (results) {           
            if(results.length>0){
                for (var i=0; i< results.length; i++){
                    $('#container').jstree()._open_to(results[i]);
                    $('#'+results[i].id+' > a').addClass('jstree-clicked');
                } 
            }
            else{
                if($("#search-error").length == 0){
                    $('<p id="search-error" style="color:red; font-size: 16px">&nbsp&nbsp'+searchErr[0]+'</p>').insertAfter($('.display-search'));
                }                
                setTimeout(function () {
                   $('#search-error').remove();
                }, 3500);
            }  
            finish()
        },
        error: function(xhr, ajaxOptions, thrownError){
            finish();
        }
    });
}

/**
 * allow search by click search icon
 */
$.fn.clickToSearch = function () {
    $(this).on('click', function (e) {        
        var keyword = $('#input-search').val();
        searchHelps(keyword);
    });
};

/**
 * allow search by press enter
 */
$.fn.pressEnterToSearch = function () {
    $(this).keypress(function (e) {        
        var keyword = $('#input-search').val();
        if (e.which == 13) {
            searchHelps(keyword);
        }
    });
}; 

/**
 * edit currently view
 */
function edit(){       
    var id = window.location.pathname.match("\\d+$");
    window.location.href = "http://"+$(location).attr('hostname')+"/help/edit/"+id;
}

/**
 * disable page
 */
function waiting(){
    $("#overlay").show();
    $("html, body").css("cursor", "progress");
}

/**
 * enable page
 */
function finish(){    
    $("#overlay").hide();
    $("html, body").css("cursor", "default");
}

/**
 * replace html special character with safe character
 * @param {String} input
 * @returns {String}
 */
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

/**
 * show edit help button if there is help selected
 */
$.fn.showEditButton = function (){
    var id = window.location.pathname.match("\\d+$");
    if(id){
        $(this).css('visibility', 'visible');
    }
}
/**
 * reload select2 
 */
$.fn.reloadSelect2 = function(){
    $(this).select2({
        dropdownCssClass : 'bigdrop',
    });  
}

/**
 * enable menu to scroll along with page
 */
function enableFixedMenu (){
    $(document).scroll(function () {
        //stick menu to top of page
        var y = $(this).scrollTop();
        var menuL = $('.view-wraper').offset().top;
        if($(document).width() >425){
            if (y > menuL) {
                $('#contentL').addClass('sticky');
            } else {
                $('#contentL').removeClass('sticky');
            }
        }        
    });
}