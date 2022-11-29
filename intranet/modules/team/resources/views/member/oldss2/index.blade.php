<?php
use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\CoreUrl;

if ($isAccess) {
    $flagEditable = '<span class="flg-editable"></span>';
} else {
    $flagEditable = '';
}
?>
@extends('layouts.default')

@section('body_class', 'page-cv')

@section('title')
{{ trans('team::view.Skill sheet of :employeeName', ['employeeName' => $employeeModelItem->name]) }}
@endsection

@section('css')
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2-bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="{{ URL::asset('team/css/style.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker3.min.css" />
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection

@section('content')
<form action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType])!!}"
    autocomplete="off" method="post" id="form-employee-cv"
    data-form-submit="ajax">
    {!! csrf_field() !!}
<div class="row member-cv">
    <!-- Right column-->
    <!-- Edit form -->
    <div class="col-md-12 tab-content">
        <div class="box box-info tab-pane active">
            <div class="row sks-header">
                <div class="col-md-8">
                    <ol class="steps-ui">
                        <p>
<button type="button" id="submit-btn">Submit</button>
</p>
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SAVE ? ' class="current"' : '' !!}><strong>Saved</strong></li>
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_SUBMIT ? ' class="current"' : '' !!}><strong>Submitted</strong></li>
                        <li{!! $employeeCvEav->getVal('status') == EmplCvAttrValue::STATUS_APPROVE ? ' class="current"' : '' !!}><strong>Approved</strong></li>
                    </ol>
                    <div class="multi-lang">
                        <label class="checkbox-inline">
                            <input type="radio" name="cv_view_lang" value="en"> EN
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="cv_view_lang" value="ja"> JP
                        </label>
                    </div>
                </div>
                <div class="col-md-4 margin-top-10">
                    <div class="pull-right margin-right-10">
                        @if ($isAccess)
                            <button type="submit" class="btn btn-primary" data-btn-submit="1">Save</button>
                            <button type="submit" class="btn btn-primary" data-btn-submit="2">Submit</button>
                        @endif
                        @if ($isAccessTeamEdit)
                            <button type="submit" class="btn btn-success" data-btn-submit="3">Approve</button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box-header with-border text-center">
                        <h2 class="box-title" data-lang-r="cv title"></h2>
                    </div>
                </div>
            </div>
            <div class="box-body row">
                <div class="table-responsiveaa">
                    <table class="table dataTable tbl-container">
                        <tbody>
                            <tr>
                                <td>
                                    <table class="table dataTable tbl-cv tbl-proj-exper">
                                        <colgroup>
                                            <col style="width: 10%; max-width: 110px;">
                                            <col style="width: 13%; min-width: 300px;">
                                            <col style="width: 10%; min-width: 120px;">
                                            <col style="width: 12%;">
                                            <col style="width: 12%;">
                                            <col style="width: 12%;">
                                            <col style="width: 10%; max-width: 120px;">
                                            <col style="width: 10%; min-width: 100px;">
                                            <col style="width: 5%; min-width: 100px;">
                                            <col style="width: 5%; min-width: 100px;">
                                        </colgroup>
                                        <tbody>
                                            <tr>
                                                <td data-lang-r="full name"></td>
                                                <td>{{ $employeeModelItem->name }}</td>
                                                <td>
<!--<span href="#" class="abc editore" data-name="username[1][]" data-type="text"  data-title="Enter username" style="width: 20px;
    height: 20px;    background: red;
    display: inline-block;">username</span>

<span href="#" class="abc editore" data-name="email[2][]" data-type="textarea"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;">email</span>
<span href="#" class="abc editore" data-name="date" data-type="date"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;">date</span>
<span href="#" class="editore" id="options" data-name="list" data-type="checklist"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" id="radio" data-name="radio" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
    
<span href="#" id="select2" class="select2 editore" data-name="select2" data-type="select2"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;">...........</span>-->
    
    
                                                    <span data-lang-r="kana name" data-lang-show="ja"></span>
                                                </td>
                                                <td colspan="3">
                                                    <div class="" data-dom-edit="dbclick" data-lang-show="ja"
                                                        data-edit-name="employee[japanese_name]">
                                                        {!!$flagEditable!!}
                                                        <span data-edit-label="input" data-valid-type='{"maxlength":255}'>{{ $employeeModelItem->japanese_name }}</span>
                                                    </div>
                                                </td>
                                                <td data-lang-r="sex"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="employee[gender]"
                                                    data-edit-value="{!!'gender_' . $employeeModelItem->gender!!}" data-edit-values="gender_1|gender_0">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="select-lang" data-lang-r="{!!'gender_' . $employeeModelItem->gender!!}"></span>
                                                </td>
                                                <td colspan="2" rowspan="5"></td>
                                            </tr>
                                            <tr>
                                                <td data-lang-r="birthday"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="employee[birthday]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="date"  data-valid-type='{"date":true}'>{{ $employeeModelItem->birthday }}</span>
                                                </td>
                                                <td data-lang-r="old"></td>
                                                <td colspan="3">
                                                    <?php if ($employeeModelItem->birthday):
                                                        $old = Carbon::now()->year - Carbon::parse($employeeModelItem->birthday)->year; ?>
                                                        <span>{!! $old > 0 ? $old : '' !!}</span>
                                                        <span data-lang-r="year old"></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-lang-r="address home"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="eav[address_home]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="address_home"
                                                        data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td data-lang-r="address"></td>
                                                <td colspan="7" data-dom-edit="dbclick"
                                                    data-edit-name="eav[address]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="address"
                                                        data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td data-lang-r="school graduation"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="eav[school_graduation]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="school_graduation"
                                                         data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                                <td data-lang-r="field develop"></td>
                                                <td colspan="3" data-dom-edit="dbclick"
                                                    data-edit-name="eav[field_dev]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="textarea" data-db-lang="field_dev" class="white-space-pre"
                                                         data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                                <td data-lang-r="level japanese"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="eav[lang_ja]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="lang_ja"
                                                        data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td data-lang-r="experience year"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="eav[exper_year]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="exper_year"
                                                        data-valid-type='{"number": true,"min":0, "max":100}'></span>
                                                </td>
                                                <td data-lang-r="scope develop"></td>
                                                <td colspan="3" data-dom-edit="dbclick"
                                                    data-edit-name="eav[scope_dev]">
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="textarea" data-db-lang="scope_dev" class="white-space-pre"
                                                        data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                                <td data-lang-r="level english"></td>
                                                <td data-dom-edit="dbclick"
                                                    data-edit-name="eav[lang_en]">
<span href="#" class="abc editore" data-name="username[]" data-type="text"  data-title="Enter username" style="width: 20px;
    height: 20px;    background: red;
    display: inline-block;">user name</span>

<span href="#" class="abc editore" data-name="email[]" data-type="textarea"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;">email@gmali.coms</span>
<span href="#" class="abc editore" data-name="date" data-type="date"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;">2017-12-12</span>
<span href="#" class="editore options" id="" data-name="list" data-type="checklist"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="1" data-name="radio" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="2" data-value="2" data-name="radio" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="3" data-name="radio" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="4" data-name="radio" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
    
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="1" data-name="radio1" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="2" data-value="2" data-name="radio1" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="3" data-name="radio1" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" class="editore" data-editable-dom="radio" data-flag-val="4" data-name="radio1" data-type="radiolist" data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
    
<span href="#" id="select2" class="select2 editore" data-value="4,7" data-name="select2[1]" data-type="select2"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
<span href="#" id="select2" class="select2 editore" data-value="1,9" data-name="select2[2]" data-type="select2"   data-title="Enter username" style="width: 20px;
    height: 20px;     background: red;
    display: inline-block;"></span>
                                                    {!!$flagEditable!!}
                                                    <span data-edit-label="text" data-db-lang="lang_en"
                                                        data-valid-type='{"maxlength":255}'></span>
                                                </td>
                                            </tr>

                                            <!-- experience project -->
                                            @include ('team::member.synthesis.project')
                                            <!-- end experience project -->
                                        </tbody>
                                    </table>
                                </td>
                                <td>
                                    <!-- experience skill -->
                @include ('team::member.synthesis.skill')
                <!-- end experience skill -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer">
            <div class="row">
                <div class="col-md-12">
                    <p><strong>{!!trans('team::cv.Note')!!}</strong></p>
                    {!!trans('team::profile.skill sheet guide')!!}
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
</form>
@endsection

@section('script')
<script>
    var globalPassModule = {
        tabType: '{!!$tabType!!}',
        isSelfProfile: {!!$isSelfProfile ? 1 : 0!!},
        urlSaveCv: '{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => 0])!!}',
        urlRemoteos: '{!!route('tag::search.tag.select2', ['fieldCode' => 'os'])!!}',
        urlRemotelang: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language-database'])!!}',
        urlRemotelanguage: '{!!route('tag::search.tag.select2', ['fieldCode' => 'language'])!!}',
        urlRemotedatabase: '{!!route('tag::search.tag.select2', ['fieldCode' => 'database'])!!}',
        isAccess: {!!(int) $isAccess!!}
    },
    globalTrans = {
        en: {!!json_encode(trans('team::cv', [], '', 'en'))!!},
        ja: {!!json_encode(trans('team::cv', [], '', 'ja'))!!},
        vi: {!!json_encode(trans('team::cv', [], '', 'vi'))!!},
    },
    globalDbTrans = {!!json_encode($employeeCvEav->eav)!!};
</script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/3.5.4/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.js"></script>-->
<script src="{{ asset('lib/x-editable-1.15.1/bootstrap-editable.js') }}"></script>
<script src="{{ CoreUrl::asset('common/js/methods.validate.js') }}"></script>
<script src="{{ CoreUrl::asset('team/js/synthesis.js') }}"></script>
<style>
    .some_class {
        min-width: 200px;
    }
</style>
<script>
$.fn.editable.defaults.mode = 'popup';
var Radiolist = function(options) {
    this.init('radiolist', options, Radiolist.defaults);
};
$.fn.editableutils.inherit(Radiolist, $.fn.editabletypes.checklist);
$.extend(Radiolist.prototype, {
        renderList : function() {
            var $label;
            this.$tpl.empty();
            if (!$.isArray(this.sourceData)) {
                return;
            }

            for (var i = 0; i < this.sourceData.length; i++) {
                $label = $('<div>').append($('<label>', {'class':this.options.inputclass})).append($('<input>', {
                    type : 'radio',
                    name : this.options.name,
                    value : this.sourceData[i].value
                })).append($('<span>').text(this.sourceData[i].text));

                // Add radio buttons to template
                this.$tpl.append($label);
            }

            this.$input = this.$tpl.find('input[type="radio"]');
        },
        input2value : function() {
            return this.$input.filter(':checked').val();
        },
        str2value: function(str) {
           return str || null;
        },
        
        value2input: function(value) {
           this.$input.val([value]);
        },
        value2str: function(value) {
           return value || '';
        },
    });

    Radiolist.defaults = $.extend({}, $.fn.editabletypes.list.defaults, {
        /**
         @property tpl
         @default <div></div>
         **/
        tpl : '<div class="editable-radiolist"></div>',

        /**
         @property inputclass, attached to the <label> wrapper instead of the input element
         @type string
         @default null
         **/
        inputclass : '',

        name : 'defaultname'
    });

    $.fn.editabletypes.radiolist = Radiolist;
$(document).ready(function() {
    $('.abc').editable({
        inputclass: 'some_class',
        emptytext: '',
        url: '/delete',
        send: 'never',
        type: 'textarea',
        datepicker: {
            weekStart: 1
        },
        success: function(response, newValue) {
            console.log('succees');
        },
        validate: function(value,dom1, dom2) {
            if($.trim(value) == '') {
                return 'This field is required';
            }
        },
    });
    $('.options').editable({
        emptytext: '',
        value: [2, 3],    
        source: [
              {value: 1, text: 'option1'},
              {value: 2, text: 'option2'},
              {value: 3, text: 'option3'}
           ]
    });
    $('[data-editable-dom="radio"]').editable({
        emptytext: '',
        source: [
              {value: 1, text: '1'},
              {value: 2, text: '2'},
              {value: 3, text: '3'},
              {value: 4, text: '4'},
              {value: 5, text: '5'}
        ],
        success: function(response, newValue) {
            var dom = $(this),
            name = dom.data('name'),
            domSiblings = $('[data-name="'+name+'"]'),
            datakey = 'editable';
            domSiblings.each(function(i, v){
                $(this).editable('setValue',newValue);
            });
        },
    });
var select2Vals = {
    1: {
        id: 1,
        text: 'g1',
    },
    4: {
        id: 4,
        text: 'g4',
    },
    7: {
        id: 7,
        text: 'g7',
    },
    9: {
        id: 9,
        text: 'g9',
    },
};
    $('.select2').editable({
        emptytext: '',
        inputclass: 'some_class',
        success: function(response, newValue) {
            console.log(response, newValue);
        },
        display: function(value, sourceData, response) {
            var html = [];
            $.each (value, function (i, v) {
                if (typeof select2Vals[v] !== 'undefined') {
                    html.push($.fn.editableutils.escape(select2Vals[v].text));
                }
            });
            if(html.length) {
                $(this).html(html.join(', '));
            } else {
                $(this).empty(); 
            }
         },
        select2: {
            placeholder: 'typing text',
            allowClear: true,
            width: 200,
            multiple: true,
            id: function(response) {
                return response.id;
            },
            minimumInputLength: 1,
            ajax: {
                url: globalPassModule.urlRemoteos,
                dataType: 'json',
                delay: 200,
                data: function (term, page) {
                    return {
                        q: term, // search term
                        page: page
                    };
                },
                processResults: function (data, page) {
                    page = page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (page * 10) < data.total
                        }
                    };
                },
                results: function (data, page) {
                    return { results: data };
                },
                cache: true
            },
            escapeMarkup: function (markup) { 
                return markup; 
            }, // let our custom formatter work
            initSelection: function (element, callback) {
                var val = $(element).val();
                if (!val) {
                    return true;
                }
                val = val.split(',');
                var result = [];
                $.each (val, function (i, v) {
                    if (typeof select2Vals[v] !== 'undefined') {
                        result.push(select2Vals[v]);
                    }
                });
                return callback(result);
            },
            formatResult: function (item) {
                return item.text;
            },
            formatSelection: function (item) {
                select2Vals[item.id] = item;
                return item.text;
            },
        }
    });
    $('#submit-btn').click(function () {
        $('.editore').editable('submit', {
            data: {
                _token: siteConfigGlobal.token
            },
            url: '{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => $tabType, 'typeId' => 0])!!}', 
            ajaxOptions: {
                dataType: 'json',
                
            },
            savenochange: false,
            success: function (data, config) {
                console.log(data, config);
            },
            error: function (error) {
                console.log(error);
            },
            soda: {
                filterValue: function (data) {
                    if (data.options.name === 'select2[]') {
                        return true;
                    }
                }
            },
        });
    });
});
</script>
@endsection
