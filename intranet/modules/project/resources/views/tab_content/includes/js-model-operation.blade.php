<?php

use Rikkei\Core\View\Form;
use Rikkei\Project\View\GeneralProject;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\View\getOptions;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectApprovedProductionCost;

$urlSubmitFilter = GeneralProject::getUrlFilterDb();
$teamPath = Team::getTeamPathTree();
$teamFilter = Form::getFilterData('exception', 'team_id', $urlSubmitFilter);
$currentUser = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
$isScopeCompany = \Rikkei\Team\View\Permission::getInstance()->isScopeCompany();
$projectKindInternal = \Rikkei\Project\Model\ProjectKind::KIND_INTERNAL;
$getOptions = new getOptions();
$levels = $getOptions->getDevTypeOptions();
$typeMembers = ProjectMember::getTypeMember();
$costDefault = ProjectApprovedProductionCost::UNIT_PRICE_DEFAULT;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>
    const PROJECT_TYPE_INTERNAL = {{$projectKindInternal}};

    var globalDefaultApproveCostPrice = '0';
    var globalUnitPrices = JSON.parse('{!! json_encode($unitPrices) !!}');

    var globalTeamPathModule = {
        teamPath: JSON.parse('{!! json_encode($teamPath) !!}'),
        teamSelected: JSON.parse('{!! json_encode($teamFilter) !!}')
    };
    var globalLevel = {
        levels: JSON.parse('{!! json_encode($levels) !!}'),
    };
    var globalTypeMember = {
        typeMembers: JSON.parse('{!! json_encode($typeMembers) !!}'),
    };
    var currentEmployeeId = '{{$currentUser->id}}';
    var isScopeCompany = {{$isScopeCompany ? 1 : 0}};

    var dontAllowUpdateApproveCost = typeof globalIsAllowUpdateApproveCost != "undefined" ? !globalIsAllowUpdateApproveCost : false;

    var listOwnerTeamIds = typeof globalIsAllowUpdateApproveCost != "undefined" ? globalOwnerTeamIds : [];
    // if (typeof globalCheckEditWorkOrderReview !== 'undefined' && globalCheckEditWorkOrderReview && dontAllowUpdateApproveCost) {
    //     dontAllowUpdateApproveCost = !globalCheckEditWorkOrderReview;
    // }

    // Create array, get from month to month in year
    function getMonthInYear(from_month, to_month) {
        var from_month = moment(from_month);
        var to_month   = moment(to_month);
        var arr_month  = [];

        while(from_month <= to_month){
            arr_month.push(from_month.format('YYYY-MM'));
            from_month.add(1, 'months');
        }
        return arr_month;
    }

    //Function handle update default approve cost price
    var updateDefaultCostPrice = function () {
        if (+$('#kind_id').val() === PROJECT_TYPE_INTERNAL) {
            globalDefaultApproveCostPrice = '{{ $costDefault }}';
        } else {
            globalDefaultApproveCostPrice = '{{ $costDefault }}';
        }
    };

    updateDefaultCostPrice();
    //Onchange Project Kind
    $(document).on('change', '#kind_id', function () {
        updateDefaultCostPrice();
    });

    $(document).on('blur', '.tblDetailInput input, .tblDetailInput select', function(){
        if ($(this).val()) {
            $(this).removeClass('error-input');
        }

        if ($('#tblOperationBody').find('.error-input').length == 0 ) {
            $('.error-input-mess').hide();
        } else {
            $('.error-input-mess').show();
        }
    });

    $(document).on('keydown', '#tblOperationBody input[id*=price]', function(event){
        if (event.keyCode == 109 || event.keyCode == 107 || event.keyCode == 189 || event.keyCode == 187) {
            return false;
        }
    });

    $(document).on('keyup mouseup', '#tblOperationBody input[id*=cost_approved_production]', function(){
        var totalAppProdCost = 0;
        $('#tblOperationBody input[id*=cost_approved_production]').each(function () {
            if ($(this).val() === '') {
                totalAppProdCost = totalAppProdCost + 0;
            } else {
                totalAppProdCost = totalAppProdCost + Number($(this).val());
            }
        });
        $('.total-app-pro-cost').text(totalAppProdCost.toFixed(2));
    });

    function isAllowViewPriceApprove() {
        var saleIds = $('select#sale_id').val();
        var dLeadIds = [+$('input[name=leader_id]').val(), +$('select[name=leader_id]').val(), +$('.div-leader-id>.form-control-static').data('id')];

        return (dLeadIds && dLeadIds.length > 0 && dLeadIds.indexOf(+currentEmployeeId) >= 0) || isScopeCompany || (saleIds && saleIds.indexOf(currentEmployeeId) >= 0);
    }

    var isAllowViewButtonDetail = hasPermissionViewCostPriceDetail;

    function checkApproveTeamIsInOwnerTeams(teamId) {
        return listOwnerTeamIds.indexOf(teamId) >= 0;
    }

    function setApprovePrice(price) {
        price = price.trim();
        return price ? price : globalDefaultApproveCostPrice;
    }
    function setApproveUnitPrice(price) {
        price = price ? price.trim() : 1;
        return price ? price : 1;
    }

    $('body').on('click', '#js-is-approve-all', function (e) {
        var checkBoxes = $("input[name=is_approve]");
        checkBoxes.prop("checked", this.checked);
    });

    // ------------------------------------ Add row table --------------------------------//
    function addRowDetail(p_objData, numRowNo, data, checkData = true) {
        var initIndex = numRowNo + 1;
        var strApprovedCost = '';
        var strApprovedPrice = null;
        var strUnApprovedPrice = null;
        var team_id = false;
        var role_id = false;
        var level_id = false;
        var unit_price = 1;
        var id = 0;
        var note = '';
        // var isAllowViewPrice = isAllowViewPriceApprove();
        var isAllowViewPrice = hasPermissionViewCostPriceDetail;
        if (!isAllowViewPrice) {
            $('.col-price').hide();
        } else {
            $('.col-price').show();
        }
        if (p_objData) {
            strApprovedCost = p_objData['approved_production_cost'];
            // strApprovedPrice = setApprovePrice(p_objData['price']);
            strApprovedPrice = p_objData['price'];
            strUnApprovedPrice = p_objData['unapproved_price'];
            if (strUnApprovedPrice == undefined) {
                strUnApprovedPrice = p_objData['price'];
                strApprovedPrice = p_objData['price_main'];
            }
            team_id = p_objData['team_id'];
            role_id = p_objData['role'] ? p_objData['role'] : p_objData['role_id'];
            level_id = p_objData['level'] ? p_objData['level'] : p_objData['level_id'];
            unit_price = setApproveUnitPrice(p_objData['unit_price']);
            id = p_objData['id'];
            note = p_objData['approve_cost_note'];
        }
        var nonRequired = ' non-required ';
        if (data !== '') {
            arrData = data.split('-');
            if (arrData[0] >= '2020') {
                nonRequired = '';
            }
        }
        var strHtml = '';
        // strHtml += '<tbody >';
        strHtml += '    <tr data-record-id="' + id + '" data-row="' + 1 + '" tabindex="' + initIndex + '" class="tblDetailInput">';
        strHtml += '       <td>';
        strHtml += '          <span class="apc-month">' + data + '</span>';
        strHtml += '          <input type="hidden" readonly id="activity_month_from' + initIndex + '" name="month" class="js-arr-month form-control form-inline month-picker maxw-100" value="' + data + '" autocomplete="off">';
        strHtml += '       </td>';

        if (!showByTeam || showByTeam == team_id) {
            strHtml += '       <td class="approved-cost-wrapper">';
            strHtml += '            <input type="number" step="any" min="0" class="non-required js-cost-approved-production approve_cost_item form-control" id="cost_approved_production' + initIndex + '" name="cost_approved_production' + initIndex + '" placeholder="{{trans('project::view.Approved production cost')}}" value="' + strApprovedCost + '" />';
            strHtml += '            <input type="hidden" id="id' + initIndex + '" value="' + id + '"  name="id" />';
            if (showByTeam) {
                strHtml += '            <input type="hidden" id="id_temp' + initIndex + '" value="1"  name="id_temp" />';
            }
            strHtml += '       </td>';
        } else {
            strHtml += '       <td>';
            strHtml += '            <input type="hidden" id="id' + initIndex + '" value="' + id + '"  name="id" />';
            strHtml += '       </td>';
        }

        if (!showByTeam || showByTeam == team_id) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown team-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select  id="team-group-' + initIndex + '" name="filter[exception][team_id]" class="js-team-id form-control filter-grid select-search has-search team-dev-tree' + initIndex + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }

        if (!showByTeam || showByTeam == team_id) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown role-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select  id="role-group-' + initIndex + '" name="filter[exception][role_id]" class="js-role-id non-required form-control filter-grid select-search has-search role-dev-tree' + initIndex + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown level-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select  id="level-group-' + initIndex + '" name="filter[exception][level_id]" class="js-level-id non-required form-control filter-grid select-search has-search level-dev-tree' + initIndex + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id) {
            strHtml += '       <td>';
            strHtml += '          <div class="operation-note-input">';
            strHtml += '            <textarea rows="1" class="non-required note_item form-control js-note"  id="approve_cost_note' + initIndex + '" name="approve_cost_note' + initIndex + '" placeholder="{{trans('project::view.Note')}}">' + note + "</textarea>";
            strHtml += '          </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id) {
            let checkTooltip = false;
            if (isAllowViewPrice) {
                let data_tooltip = 'Approved Value: ' + (strApprovedPrice != '' ? convertPrice(strApprovedPrice) : 'null');
                if (strUnApprovedPrice != '' && (strUnApprovedPrice != strApprovedPrice)) {
                    checkTooltip = true;
                }
                strHtml += '<td class="js-td-price">';
                strHtml += '    <input data-toggle="tooltip" title="" data-original-title="'+(checkTooltip ? data_tooltip : '')+'" aria-describedby="" type="text" step="any" min="0" class="non-required form-control price js-input-price '+ (checkTooltip ? 'unapproved-price' : '') +'" id="price' + initIndex + '" name="price' + initIndex + '" placeholder="{{trans('project::view.Approved production price')}}" value="' + (checkTooltip ? convertPrice(strUnApprovedPrice) : convertPrice(strApprovedPrice)) + '" />';
                strHtml += '    <span style="display: none;" id="price_main' + initIndex + '" class="js-price-main" data-pricemain="'+convertPrice(strApprovedPrice)+'">'+convertPrice(strApprovedPrice)+'</span>';
                strHtml += '</td>';

                strHtml += '<td>';
                    strHtml += '<select class="form-control js-unit-price" id="unit_price' + initIndex + '" name="unit_price' + initIndex + '">';
                    for (var key in globalUnitPrices) {
                        // check if the property/key is defined in the object itself, not in parent
                        if (globalUnitPrices.hasOwnProperty(key)) {
                            strHtml += '<option value="' + key + '" ';
                            if (+key === +unit_price) {
                                strHtml += 'selected';
                            }
                            strHtml += '>';
                            strHtml += globalUnitPrices[key];
                            strHtml += '</option>';
                        }
                    }
                    strHtml += '</select>';
                strHtml += '</td>';
                if (checkTooltip || ((strUnApprovedPrice == '' || strUnApprovedPrice == null) && (strApprovedPrice == '' || strApprovedPrice == null))) {
                    strHtml += '   <td class="td-is-approve"><input type="checkbox" checked name="is_approve" id="is-approve-'+initIndex+'" value="1"></td>';
                } else {
                    let valInputApprove = 0;
                    if (!checkData) {
                        valInputApprove = 'data_null';
                    }
                    strHtml += '   <td class="td-is-approve"><input type="hidden" name="is_approve" id="is-approve-'+initIndex+'" value="'+valInputApprove+'"></td>';
                }
            } else {
                strHtml += '   <input type="hidden" id="is-approve-'+initIndex+'" value="0">';
            }
        } else {
            strHtml += '       <td></td>';
            strHtml += '       <td></td>';
            strHtml += '       <td></td>';
        }
        
        strHtml += '       <td> <span href="#" style="color: seagreen" class="btn-add-row"><i class="fa fa-plus"></i></span> </td>';
        strHtml += '       <td> <span href="#" style="color: #d33724" class="btn-remove-row hidden"><i class="fa fa-minus"></i></span>  </td>';
        strHtml += '    </tr>';
        // strHtml += '</tbody>';

        $('#tblOperationBody tbody').append(strHtml);
        if (p_objData) {
            renderDataSelectTeam(team_id, initIndex, showByTeam);
            renderDataSelectTypeMember(role_id, initIndex);
            renderDataSelectLevel(level_id, initIndex);
        } else {
            renderDataSelectTeam(null, initIndex, showByTeam);
            renderDataSelectTypeMember(null, initIndex);
            renderDataSelectLevel(null, initIndex);
        }
    }

    //---------------------------- Function render rowspan when click button (add or remove) ----//
    function renderRowSpan(p_objectData,tabindex, index, arr_month = '', typeAdd = false) {
        var strApprovedCost  = '';
        var strApprovedPrice  = null;
        var strUnApprovedPrice = null;
        var id               = '&nbsp;';
        var team_id = false;
        var role_id = false;
        var level_id = false;
        var unit_price = 1;
        var note = '';
        if (p_objectData) {
            strApprovedCost = p_objectData['approved_production_cost'];
            // strApprovedPrice = setApprovePrice(p_objectData['price']);
            strApprovedPrice = p_objectData['price'];
            strUnApprovedPrice = p_objectData['unapproved_price'];
            if (strUnApprovedPrice == undefined) {
                strUnApprovedPrice = p_objectData['price'];
                strApprovedPrice = p_objectData['price_main'];
            }
            unit_price = setApproveUnitPrice(p_objectData['unit_price']);
            id              = p_objectData['id'];
            team_id = p_objectData['team_id'];
            role_id = p_objectData['role'] ? p_objectData['role'] : p_objectData['role_id'];
            level_id = p_objectData['level'] ? p_objectData['level'] : p_objectData['level_id'];
            note = p_objectData['approve_cost_note'];
        }
        var isAllowViewPrice = hasPermissionViewCostPriceDetail;
        if (!isAllowViewPrice) {
            $('.col-price').hide();
        } else {
            $('.col-price').show();
        }
        var strHtml          = '';
        strHtml += '    <tr data-record-id="' + id + '" data-row="' + index + '" tabindex="' + tabindex + '">';

        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td class="approved-cost-wrapper">';
            strHtml += '          <input type="number" step="any" min="0" class="js-cost-approved-production non-required approve_cost_item form-control" id="cost_approved_production' + tabindex + '_' + index + '" name="cost_approved_production" placeholder="{{trans('project::view.Approved production cost')}}" value="' + strApprovedCost + '" />';
            strHtml += '          <input type="hidden" id="id' + tabindex + '_' + index + '" value="' + id + '" name="id" />';
            strHtml += '          <input type="hidden" class="js-arr-month" value="' + (arr_month ? arr_month : 0) + '" name="arr_month" />';
            if (showByTeam) {
                strHtml += '          <input type="hidden" id="id_temp' + tabindex + '_' + index + '" value="1"  name="id_temp" />';
            }
            strHtml += '       </td>';
        } else {
            strHtml += '       <td>';
            strHtml += '          <input type="hidden" id="id' + tabindex + '_' + index + '" value="' + id + '" name="id" />';
            strHtml += '          <input type="hidden" class="js-arr-month" value="' + (arr_month ? arr_month : 0) + '" name="arr_month" />';
            strHtml += '       </td>';
        }
        
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown team-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select id="team-group'  + tabindex + '_' + index +'" name="filter['+ tabindex + '][' + index + '][exception][team_id]" class="js-team-id form-control filter-grid select-search has-search team-dev-tree' + tabindex + '_' + index + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown role-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select id="role-group'  + tabindex + '_' + index +'" name="filter['+ tabindex + '][' + index + '][exception][role_id]" class="js-role-id non-required form-control filter-grid select-search has-search role-dev-tree' + tabindex + '_' + index + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td>';
            strHtml += '         <div class="dropdown level-dropdown">';
            strHtml += '           <span>';
            strHtml += '               <select id="level-group'  + tabindex + '_' + index +'" name="filter['+ tabindex + '][' + index + '][exception][team_id]" class="js-level-id non-required form-control filter-grid select-search has-search level-dev-tree' + tabindex + '_' + index + '">';
            strHtml += '               </select>';
            strHtml += '           </span>';
            strHtml += '         </div>';
            strHtml += '       </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td>';
            strHtml += '        <div class="operation-note-input">';
            strHtml += '<textarea rows="1" class="non-required note_item form-control js-note"  id="approve_cost_note' + tabindex + '_' + index + '" name="approve_cost_note' + tabindex + '_' + index + '"  placeholder="{{trans('project::view.Note')}}" >' + note + "</textarea>";
            strHtml += '        </div>';
            strHtml += '    </td>';
        } else {
            strHtml += '       <td></td>';
        }
        
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            let checkTooltip = false;
            if (isAllowViewPrice) {
                let data_tooltip = 'Approved Value: ' + (strApprovedPrice != '' ? convertPrice(strApprovedPrice) : 'null');
                if (strUnApprovedPrice != '' && (strUnApprovedPrice != strApprovedPrice)) {
                    checkTooltip = true;
                }
                strHtml += '<td class="js-td-price">';
                strHtml += '    <input data-toggle="tooltip" title="" data-original-title="'+(checkTooltip ? data_tooltip : '')+'" aria-describedby="" type="text" step="any" min="0" class="non-required  form-control price js-input-price '+ (checkTooltip ? 'unapproved-price' : '') +'" id="price' + tabindex + '_' + index + '" name="price" placeholder="{{trans('project::view.Approved production price')}}" value="' + (checkTooltip ? convertPrice(strUnApprovedPrice) : convertPrice(strApprovedPrice)) + '" />';
                strHtml += '    <span style="display: none;" id="price_main' + tabindex + '_' + index + '" class="js-price-main" data-pricemain="'+convertPrice(strApprovedPrice)+'">'+convertPrice(strApprovedPrice)+'</span>';
                strHtml += '</td>';
                strHtml += '<td>';
                strHtml += '<select class="form-control js-unit-price" id="unit_price' + tabindex + '_' + index + '" name="unit_price">';
                for (var key in globalUnitPrices) {
                    // check if the property/key is defined in the object itself, not in parent
                    if (globalUnitPrices.hasOwnProperty(key)) {
                        strHtml += '<option value="' + key + '" ';
                        if (+key === +unit_price) {
                            strHtml += 'selected';
                        }
                        strHtml += '>';
                        strHtml += globalUnitPrices[key];
                        strHtml += '</option>';
                    }
                }
                strHtml += '</select></td>';
                if (checkTooltip || ((strUnApprovedPrice == '' || strUnApprovedPrice == null) && (strApprovedPrice == '' || strApprovedPrice == null))) {
                    strHtml += '   <td class="td-is-approve"><input type="checkbox" checked name="is_approve" id="is-approve'+ tabindex + '_' + index +'" value="1"></td>';
                } else {
                    strHtml += '   <td class="td-is-approve"><input type="hidden" name="is_approve" id="is-approve'+ tabindex + '_' + index +'" value="0"></td>';
                }
            } else {
                strHtml += '   <input type="hidden" id="is-approve'+ tabindex + '_' + index +'" value="0">';
            }
        } else {
            strHtml += '       <td></td>';
            strHtml += '       <td></td>';
            strHtml += '       <td></td>';
        }
        
        strHtml += '       <td> <span href="#" style="color: seagreen" class="btn-add-row"><i class="fa fa-plus"></i></span> </td>';
        if (!showByTeam || showByTeam == team_id || typeAdd) {
            strHtml += '       <td> <span href="#" style="color: #d33724" class="btn-remove-row "><i class="fa fa-minus"></i></span>  </td>';
        } else {
            strHtml += '       <td></td>';
        }
        strHtml += '    </tr>';

        return strHtml;
    }

    function convertPrice(price) {
        if (price == null || price == '') {
            return '';
        }
        if (price.indexOf(",") == -1) {
            // return new Intl.NumberFormat().format(price);
            return price.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
        }
        return price;
    }
    
    $('body').on('change', '.js-input-price', function () {
        var price = $(this).val();
        if (price) {
            var reg = /^[0-9,]+$/;
            if (reg.test(price)) {
                price = price.replace(new RegExp(',', 'g'),"");
                price = convertPrice(price);
                $(this).val(price);
            } else {
                bootbox.alert({
                    message: 'Đơn giá nhập vào không hợp lệ!',
                    className: 'modal-danger',
                });
            }
        }
    });

    //----------------  function render select ----------------------//
    function renderDataSelectTeam(id = null, initIndex, showByTeam = null) {
        if (typeof globalTeamPathModule !== 'undefined' && $('select.team-dev-tree'+initIndex).length) {
            var teamDevOption = RKfuncion.teamTree.init(globalTeamPathModule.teamPath, globalTeamPathModule.teamSelected);
            var idTeam = $('#team_id option:selected');
            var htmlTeamDevOption, selectedTeamDevOption;
            $.each(teamDevOption, function(i,v) {
                idTeam.each(function() {
                    var idChoose = $(this).val();
                    if (showByTeam) {
                        if (idChoose == v.id && idChoose == showByTeam) {
                            selectedTeamDevOption = id == idChoose ? ' selected' : '';
                            htmlTeamDevOption += '<option class="item-team " data-old="'+ v.label +'" value="'+v.id+'"'
                                +selectedTeamDevOption+'>' + v.label.trim()+'</option>';
                        }
                    } else {
                        if (idChoose == v.id) {
                            selectedTeamDevOption = id == idChoose ? ' selected' : '';
                            htmlTeamDevOption += '<option class="item-team " data-old="'+ v.label +'" value="'+v.id+'"'
                                +selectedTeamDevOption+'>' + v.label.trim()+'</option>';
                        }
                    }
                });
            });

            // $('select.team-dev-tree'+initIndex).append('<option value="" class="item-team action-item" data-old="">&nbsp;</option>');
            $('select.team-dev-tree'+initIndex).append(htmlTeamDevOption);
        }
    }

    //----------------  function render select ----------------------------//
    function renderDataChildSelectTeam(id = null, initIndex, indexRow, showByTeam = null) {
        if (typeof globalTeamPathModule !== 'undefined' && $('select.team-dev-tree'+ initIndex + '_' + indexRow).length) {
            var teamDevOption = RKfuncion.teamTree.init(globalTeamPathModule.teamPath, globalTeamPathModule.teamSelected);
            var idTeam = $('#team_id option:selected');
            var htmlTeamDevOption, selectedTeamDevOption;
            $.each(teamDevOption, function(i,v) {
                idTeam.each(function() {
                    var idChoose = $(this).val();
                    if (showByTeam) {
                        if (idChoose == v.id && idChoose == showByTeam) {
                            selectedTeamDevOption = id == idChoose ? ' selected' : '';
                            htmlTeamDevOption += '<option class="item-team " data-old="'+ v.label +'" value="'+v.id+'"'
                                +selectedTeamDevOption+'>' + v.label.trim()+'</option>';
                        }
                    } else {
                        if (idChoose == v.id) {
                            selectedTeamDevOption = id == idChoose ? ' selected' : '';
                            htmlTeamDevOption += '<option class="item-team " data-old="'+ v.label +'" value="'+v.id+'"'
                                +selectedTeamDevOption+'>' + v.label.trim()+'</option>';
                        }
                    }
                    
                });
            });

            // $('select.team-dev-tree'+ initIndex + '_' + indexRow).append('<option value="" class="item-team action-item" data-old="">&nbsp;</option>');
            $('select.team-dev-tree'+ initIndex + '_' + indexRow).append(htmlTeamDevOption);
        }
    }

    //----------------  function render Level select ----------------------//
    function renderDataSelectLevel(id = null, initIndex) {
        if (typeof globalLevel !== 'undefined' && $('select.level-dev-tree'+initIndex).length) {
            let html = '<option value=""'+'></option>';
            $.each(globalLevel.levels, function(i, v) {
                html += '<option value="'+i+'"';
                        if (id && id == i) {
                            html += 'selected';
                        }
                html += '>'+ v + '</option>';
            });
            $('select.level-dev-tree'+initIndex).append(html);
        }
    }
    function renderDataChildSelectLevel(id = null, initIndex, indexRow) {
        if (typeof globalLevel !== 'undefined' && $('select.level-dev-tree'+ initIndex + '_' + indexRow).length) {
            let html = '<option value=""'+'></option>';
            $.each(globalLevel.levels, function(i, v) {
                html += '<option value="'+i+'"';
                        if (id && id == i) {
                            html += 'selected';
                        }
                html += '>'+ v + '</option>';
            });
            $('select.level-dev-tree'+ initIndex + '_' + indexRow).append(html);
        }
    }

    //----------------  function render Role select ----------------------------//
    function renderDataSelectTypeMember(id = null, initIndex) {
        if (typeof globalTypeMember !== 'undefined' && $('select.role-dev-tree'+initIndex).length) {
            let html = '<option value=""'+'></option>';
            $.each(globalTypeMember.typeMembers, function(i, v) {
                html += '<option value="'+i+'"';
                        if (id && id == i) {
                            html += 'selected';
                        }
                html += '>'+ v + '</option>';
            });
            $('select.role-dev-tree'+initIndex).append(html);
        }
    }
    function renderDataChildSelectTypeMember(id = null, initIndex, indexRow) {
        if (typeof globalTypeMember !== 'undefined' && $('select.role-dev-tree'+ initIndex + '_' + indexRow).length) {
            let html = '<option value=""'+'></option>';
            $.each(globalTypeMember.typeMembers, function(i, v) {
                html += '<option value="'+i+'"';
                        if (id && id == i) {
                            html += 'selected';
                        }
                html += '>'+ v + '</option>';
            });
            $('select.role-dev-tree'+ initIndex + '_' + indexRow).append(html);
        }
    }

    //----------------  Set Data Json ----------------------------//
    function setErrorInput() {
        var $tbodyCtr  = $('#tblOperationBody tbody');

        $tbodyCtr.each(function() {
            $(this).find('input:not(".non-required"), select:not(".non-required")').each(function () {
                if ($(this).val() == '') {
                    $('.error-input-mess').text('{{ trans('project::message.Input all') }}');
                    $('.group-error').removeClass('hidden');
                    $(this).addClass('error-input');
                } else {
                    $(this).removeClass('error-input');
                }
            });

            if ($('#tblOperationBody').find('.error-input').length == 0 ) {
                $('.error-input-mess').hide();
            } else {
                $('.error-input-mess').show();
                bootbox.alert({
                    message: 'All Input field is required!',
                    className: 'modal-danger',
                });
            }
        });
    }

    //---------------- Check 2 array find different item ----------------- /
    function Unique(array1,array2) {
        var unique = [];
        for(var i = 0; i < array1.length; i++){
            var found = false;

            for(var j = 0; j < array2.length; j++){ // j < is missed;
                if(array1[i] == array2[j]){
                    found = true;
                    break;
                }
            }
            if(found == false){
                unique.push(array1[i]);
            }
        }
        return unique;
    }
</script>
