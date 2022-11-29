<?php
use Rikkei\Sales\View\OpporView;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Country;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\ProjectMember;

$strCountries = json_encode(Country::getCountryList());
$strCountries = preg_replace("/'/", "\'", $strCountries);
$aryPrograms = Programs::getListOption();
$programs = [];
foreach ($aryPrograms as $key => $value) {
    $programs[] = ['id' => $key, 'name' => $value];
}
$typeMembers = ProjectMember::getTypeMember();
$roles = [];
foreach ($typeMembers as $key => $value) {
    $roles[] = ['id' => $key, 'name' => $value];
}
?>

<script>
    var OpporParams = {
        token: siteConfigGlobal.token,
        STT_OPEN: {{ OpporView::STT_OPEN }},
        STT_PROCESSING: {{ OpporView::STT_PROCESSING }},
        STT_SUBMIT: {{ OpporView::STT_SUBMIT }},
        STT_CANCEL: {{ OpporView::STT_CANCEL }},
        STT_PASS: {{ OpporView::STT_PASS }},
        STT_FAIL: {{ OpporView::STT_FAIL }},
        STT_CLOSED: {{ OpporView::STT_CLOSED }},
        PRIORITY_NORMAL: {{ OpporView::PRIORITY_NORMAL }},
        COUNTRY_VN: 242,
        currentUserId: {{ auth()->id() }},
        itemCode: '{{ $itemCode }}',
        statusLabels: JSON.parse('{!! json_encode($statusLabels) !!}'),
        priorityLabels: JSON.parse('{!! json_encode(OpporView::priorityLabels()) !!}'),
        programs: JSON.parse('{!! json_encode($programs) !!}'),
        languages: JSON.parse('{!! json_encode(OpporView::listLanguages()) !!}'),
        currentSale: JSON.parse('{!! json_encode($item && $item->sale ? ["id" => $item->sale->id, "name" => $item->sale->getNickName()] : []) !!}'),
        locations: JSON.parse('{!! json_encode(OpporView::listLocations()) !!}'),
        saleSearchUrl: '{{ route("team::employee.list.search.ajax", ["type" => null, "team_type" => Team::TEAM_TYPE_SALE]) }}',
        searchEmployeeUrl: '{{ route("team::employee.list.search.ajax") }}',
        previousUrl: '{{ route("sales::req.list.oppor.index") }}',
        saveUrl: '{{ route("sales::req.oppor.save") }}',
        exportUrl: '{{ route("sales::req.oppor.export", $item ? $item->id : null) }}',
        checkingCodeUrl: '{{ route("sales::req.oppor.check_exists") }}',
        getOpporUrl: '{{ route("sales::req.apply.oppor.getOppor", ["id" => null]) }}',
        saveCvMemberUrl: '{{ route("sales::req.apply.oppor.cv_member.save") }}',
        listCvNotesUrl: '{{ route("sales::req.apply.oppor.cv_member.list", null) }}',
        deleteCvMemberUrl: '{{ route("sales::req.apply.oppor.cv_member.delete", null) }}',
        members: JSON.parse('{!! $item ? $item->membersWithProgs() : json_encode([]) !!}'),
        roles: JSON.parse('{!! json_encode($roles) !!}'),
        typeOptions: JSON.parse('{!! json_encode($typeOptions) !!}'),
        langLevels: JSON.parse('{!! json_encode($langLevels) !!}'),
        countries: JSON.parse('{!! $strCountries !!}'),
        permissEdit: parseInt('{{ $permissEdit ? 1 : 0 }}'),
        permissApply: parseInt('{{ $permissApply ? 1 : 0 }}'),
        titleView: '{{ trans('sales::view.View opportunity') }}',
        titleEdit: '{{ trans('sales::view.Edit opportunity') }}',
        titleCreate: '{{ trans('sales::view.Create opportunity') }}'
    };

    var OpporTrans = {
        'Request name': '{!! trans("sales::view.Request name") !!}',
        'Export': '{!! trans("sales::view.Export") !!}',
        'Code': '{!! trans("sales::view.Code") !!}',
        'Priority': '{!! trans('sales::view.Priority') !!}',
        'Status': '{!! trans('sales::view.Status') !!}',
        'Detail': '{!! trans('sales::view.Detail') !!}',
        'Potential': '{!! trans('sales::view.Potential') !!}',
        'Program language': '{!! trans('sales::view.Program language') !!}',
        'Language': '{!! trans('sales::view.Language') !!}',
        'From date': '{!! trans('sales::view.From date')  !!}',
        'To date': '{!! trans('sales::view.To date') !!}',
        'Salesperson': '{!! trans('sales::view.Salesperson') !!}',
        'Location': '{!! trans('sales::view.Location') !!}',
        'Customer': '{!! trans('sales::view.Customer') !!}',
        'Note': '{!! trans('sales::view.Note') !!}',
        'Employees': '{!! trans('sales::view.Employees') !!}',
        'Click Add button bellow to add employees': '{!! (trans('sales::view.Click Add button bellow to add employees')) !!}',
        'Number': '{!! trans('sales::view.Number') !!}',
        'Role': '{!! trans('sales::view.Role') !!}',
        'Expertise level': '{!! trans('sales::view.Expertise level') !!}',
        'English level': '{!! trans('sales::view.English level') !!}',
        'Japanese level': '{!! trans('sales::view.Japanese level') !!}',
        'This field is required': '{!! trans('sales::message.This field is required') !!}',
        'This code has already taken': '{!! trans('sales::message.This code has already taken') !!}',
        'To date must be greater than From date': '{!! trans('sales::message.To date must be greater than From date') !!}',
        'Error system, please try again later!': '{!! trans('sales::message.Error system, please try again later!') !!}',
        'Number of employees': '{!! trans('sales::view.Number of employees') !!}',
        'Number received': '{!! trans('sales::view.Number received') !!}',
        'Duedate': '{!! trans('sales::view.Duedate') !!}',
        'Deadline': '{!! trans('sales::view.Deadline') !!}',
        'Duration': '{!! trans('sales::view.Duration') !!}',
        'Country': '{!! trans('sales::view.Country') !!}',
        'Province/City': '{!! trans('sales::view.Province/City') !!}',
        'Send mail': '{!! trans('sales::view.Send mail') !!}',
        'Send mail after save change?': '{!! trans('sales::view.Send mail after save change?') !!}',
        'Back': '{!! trans('sales::view.Back') !!}',
        'Save': '{!! trans('sales::view.Save') !!}',
        'Comments': '{!! trans('sales::view.Comments') !!}',
        'Submit': '{!! trans('sales::view.Submit') !!}',
        'Note Cv': '{!! trans('sales::view.Note Cv') !!}',
        'Edit': '{!! trans('sales::view.Edit') !!}',
        'Cancel': '{!! trans('sales::view.Cancel') !!}',
        'Are you sure want to delete?': '{!! trans('sales::message.Are you sure want to delete?') !!}',
        'This field is less than Number of employees': '{!! trans("sales::message.This field is less than Number of employees") !!}',
        'None comment': '{!! trans("sales::message.None comment") !!}',
        'Person in charge': '{!! trans("sales::view.Person in charge") !!}',
        'Person in charge email': '{!! trans("sales::view.Person in charge email") !!}',
        'Company': '{!! trans("sales::view.Company") !!}',
        'Opportunity information' : '{!! trans("sales::view.Opportunity information") !!}',
        'Client': '{!! trans("sales::view.Client") !!}',
    };

    var OpporEvents = {
        init: function () {
            setTimeout(function () {
                RKfuncion.select2.init();
                RKfuncion.bootstapMultiSelect.init();

                $('.date-picker').each(function () {
                    var format = $(this).data('format');
                    $(this).datetimepicker({
                        format: format,
                    });
                });
            }, 100);
        },

        afterAddMember: function () {
            setTimeout(function () {
                $('.emp-item').removeClass('hidden');
                RKfuncion.select2.init();
                RKfuncion.bootstapMultiSelect.init();
                //$('select.new-select2').select2();
                //$('select.new-multiselect').multiselect();
            }, 100);
        },

        alertSuccess: function (message) {
            bootbox.alert({
                message: message,
                className: 'modal-success'
            });
        },

        exportData: function (response) {
            var wb = XLSX.utils.table_to_book($(response.table)[0], {sheet: "Sheet1",});
            var numCols = 12;
            var cols = [{wch: 5,}];
            for (var i = 1; i <= numCols; i++) {
                cols.push({wch: 22,});
            }
            wb.Sheets.Sheet1['!cols'] = cols;
            var wbout = XLSX.write(wb, {bookType: 'xlsx', bookSST: false, type: 'binary',});
            var fname = response.fileName + '.xlsx';
            try {
                saveAs(new Blob([s2ab(wbout)], {type: "",}), fname);
            } catch(e) {
                //error
                return;
            }
        },

        errorHandle: function (error) {
            if (typeof error == 'undefined' || !error) {
                error = OpporTrans['Error system, please try again later!'];
            }
            bootbox.alert({
               message: error,
               className: 'modal-danger',
            });
        }
    };

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }
</script>
