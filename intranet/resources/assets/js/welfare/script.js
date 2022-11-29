jQuery(document).ready(function ($) {
    selectSearchReload();
    var tokenValue = $('input[name=_token]').val();
    var conf = $('#table-partner .delete-modal-group').data('confirm');
    var editPartnerUrl = $('#table-partner .edit-modal-partner').data('url');
    var deletePartnerUrl = $('#table-partner .delete-modal-partner').data('url');
    $('input[type="search"]').css('pointer-events', 'auto');

    $('div.alert').delay(10000).slideUp();

    $('#addPartnerGroup').on('submit', function (event) {
        event.preventDefault();
        var url = $("#addPartnerGroup").attr('action');
        var name = $("#namePartnerGroup").val();
        if (!$("#addPartnerGroup").valid()) {
            return false;
        } else {
            $('#addPartnerGroup').find('#uploading').removeClass('hidden');
            $('#addPartnerGroup').find('#partner-group-add').prop('disabled', 'disabled');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    '_token': tokenValue,
                    'name': name
                },
                success: function (data) {
                    if ((data.errors)) {
                        $('#list-partner-group').find('p#error-name-unique').removeClass('hidden');
                        $('#list-partner-group').find('p#error-name-unique').html(data.errors.name);
                    } else {
                        $('.table-partner-group').DataTable().ajax.reload(null, false);
                        $('#namePartnerGroup').val('');
                        $('#myModal #fid').val(data.id);
                        $('#myModal #n').val(data.name);
                        $('#modal-add-partner #partner_type_id').append($('<option>', {
                            value: data.id,
                            text: data.name
                        }));
                    }
                    $('#addPartnerGroup').find('#uploading').addClass('hidden');
                    $('#partner-group-add').removeAttr('disabled');
                }
            });
        }
    });
    $(document).on('keyup', '#namePartnerGroup', function() {        
        if ($(this).valid()) {
            $('#addPartnerGroup').find('#partner-group-add').removeAttr('disabled');
        } else {
            $('#addPartnerGroup').find('#partner-group-add').prop('disabled', 'disabled');
        }
        $('#list-partner-group').find('p#error-name-unique').addClass('hidden');
    });

    $(document).on('click', '.edit-modal-group', function () {
        $('#modal-edit-partner-group #fid').val($(this).data('id'));
        $('#modal-edit-partner-group #n').val($(this).data('name'));
        $('#modal-edit-partner-group').modal('show');
    });

    $(document).on('click', '.delete-modal-group', function () {
        var url = $('.modal-footer .actionBtn').data('url');
        $('.form-confirm-delete').attr('action', url);

        $('#modal-delete-partner-group .did').text($(this).data('id'));
        $('#modal-delete-partner-group').modal('show');
    });

    $('#formModalPertnerGroup').on('submit', function (event) {
        event.preventDefault();
        var url = $('#formModalPertnerGroup').attr('action');
        var name = $('#formModalPertnerGroup').find("#n").val();
        if (name.trim().length === 0) {
            $('#formModalPertnerGroup').find('#error-name').removeClass('hidden');
            return false;
        }
        if (name.trim().length > 50) {
            $('#formModalPertnerGroup').find('#error-name-length').removeClass('hidden');
            return false;
        }
        $(this).find('._uploading').removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': tokenValue,
                'id': $("#fid").val(),
                'name': $('#n').val(),
            },
            success: function (data) {
                if ((data.errors)) {
                        $('#modal-edit-partner-group').find('p#error-name-unique').removeClass('hidden');
                        $('#modal-edit-partner-group').find('p#error-name-unique').html(data.errors.name);
                } else {
                    $('.table-partner-group').DataTable().ajax.reload(null, false);
                    $("#modal-add-partner #partner_type_id option[value='" + data.id + "']").text(data.name);                    
                    $('#modal-edit-partner-group').modal('hide');
                }
                $('#formModalPertnerGroup').find('._uploading').addClass('hidden');
                $('#formModalPertnerGroup').find('.edit').removeAttr('disabled');
            }
        });
    });

    $(document).on('keyup', '#formModalPertnerGroup #n', function () {
        var name = $('#formModalPertnerGroup').find("#n").val();
        if (name.trim().length !== 0) {
            $('#formModalPertnerGroup').find('#error-name').addClass('hidden');
        } else {
            $('#formModalPertnerGroup').find('#error-name').removeClass('hidden');
        }
        if (name.trim().length <= 50) {
            $('#formModalPertnerGroup').find('#error-name-length').addClass('hidden');
        } else {
            $('#formModalPertnerGroup').find('#error-name-length').removeClass('hidden');
        }
        $('#modal-edit-partner-group').find('p#error-name-unique').addClass('hidden');
    });

    $('#modal-edit-partner-group').on('hidden.bs.modal', function () {
        $(this).find('label.error').addClass('hidden');
        $(this).find('p.error').addClass('hidden');
    });
    $('.modal-footer').on('click', '.btn-ok', function () {
        var url = $('.form-confirm-delete').attr('action');
        var id = $('.did').text();
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': tokenValue,
                'id': id,
            },
            success: function (data) {

                if (data == 'PartnerGroup') {
                    $('.table-partner-group').DataTable().ajax.reload(null, false);
                    $("#modal-add-partner #partner_type_id option[value='" + id + "']").remove();
                }
                if (data == 'Partner') {
                    $('#table-partner').DataTable().ajax.reload(null, false);
                    if ($('.information-partners .partner_id option:selected').val() == id) {
                        $('.information-partners input[type="text"]').val('');
                    }
                    $("#partner .information-partners .partner_id option[value='" + id + "']").remove();
                }
            }
        });
    });

    $(document).on('click', '.table-partner-group .check-group-partner', function () {
        $('.check-group-partner').css('background-color', '#fff');
        $(this).css('background-color', '#ccc');
        $(this).find("input[name=choose]").prop('checked', true);

    });

    $(document).on('click', '.btn-choose-group-partner', function () {
        $('#modal-add-partner #partner_type_id option:selected').removeAttr("selected");
        if ($('.check-group-partner').find("input[name=choose]").is(':checked')) {
            var id = $('.check-group-partner').find("input[name=choose]:checked").val();                    
                $('#modal-add-partner').find('#partner_type_id').val(id).trigger('change');
            $(".table-partner-group .item" + id).css('background-color', '#fff');
        }
        else {
            return;
        }
    });

    // CRUD Partner
    $(document).on('click', '#list-partner', function () {
        $('#modal-list-partner').modal('show');
    });

    $(document).on('click', '#modal-list-partner button#add-partner', function () {
        var url = $(this).data('url');
        $.ajax({
            type: 'GET',
            url: url,
            data: {
                '_token': tokenValue
            },
            success: function (data) {
                $('#form-add-partner').find('input[name=code]').val(data);
                $('#modal-add-partner').modal('show');
            }
        });

    });

    $('#modal-add-partner').on('hidden.bs.modal', function () {
        $('.addPartner')[0].reset();
        $(this).find('label.error').addClass('hidden');
        $(this).find('input.error, select.error').removeClass('error');
        $(this).find("#partner_type_id").val('').trigger('change');
        $(this).find('p.error').addClass('hidden');
    });

    $('#modal-list-partner').on('hidden.bs.modal', function () {
        $('#table-partner tbody tr').css('background-color', '#fff');
    });

    $('.addPartner').on('submit', function (event) {
        event.preventDefault();

        var url = $('.addPartner').attr('action');
        var $inputs = $('.addPartner :input');
        var attribute = {};
        $inputs.each(function () {
            attribute[this.name] = $(this).val();
        });
        if (!$(".addPartner").valid()) {
            return false;
        } else {
            $(this).find('#uploading').removeClass('hidden');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    '_token': tokenValue,
                    'attribute': attribute,
                },
                success: function (data) {
                    if (data.errors != undefined && data.errors) {
                        $('#modal-add-partner').find('#error-general').removeClass('hidden');
                        $('#modal-add-partner').find('#error-general').html(data.errors.name);                        
                    } else {
                        if (data.isPartner) {
                            $('#table-partner').DataTable().ajax.reload(null, false);
                            $("#partner .partner_id option[value='" + data.id + "']").text(data.name);
                            if ($('#partner .information-partners .partner_id option:selected').val() == data.id) {
                                getPartners(data.id);
                            }
                        } else {
                            $('#table-partner').DataTable().ajax.reload(null, false);
                            $("#partner .partner_id").append($('<option>', {
                                value: data.id,
                                text: data.name
                            }));
                        }
                        $('#modal-add-partner').modal('hide');
                    }
                    $('.btn-add-partner').removeAttr('disabled');
                    $('.btn-add-partner').find('#uploading').addClass('hidden');
                }
            });
        }
    });
    $('#modal-add-partner #name').keyup(function () {
        $('#error-name-partner').addClass('hidden');
        $('#modal-add-partner').find('p.error').addClass('hidden');
    });
    $('#modal-add-partner #partner_type_id').change(function () {
        $('#error-partner-type-id').addClass('hidden');
    });
    $(document).on('click', '.edit-modal-partner', function () {
        var id = $(this).data('id');
        $.ajax({
            type: 'GET',
            url: editPartnerUrl + '/' + id,
            data: {},
            success: function (data) {
                for (var key in data) {                    
                    $("#modal-add-partner #" + key).val(data[key]); 
                }
                $('#modal-add-partner').find('#partner_type_id').val(data['partner_type_id']).trigger('change');
                $('#modal-add-partner #isPartner').val(data.id);
                $('#modal-add-partner #title-add').addClass('hidden');
                $('#modal-add-partner #title-update').removeClass('hidden');
                $('#modal-add-partner').modal('show');
            }
        });
    });

    $(document).on('click', '.delete-modal-partner', function () {
        $('.form-confirm-delete').attr('action', deletePartnerUrl);
        $('#modal-delete-partner-group .did').text($(this).data('id'));
        $('#modal-delete-partner-group').modal('show');
    });

    $('.partners-implementation select').change(function () {
        var id;
        $('.partners-implementation select option:selected').each(function () {
            id = $(this).val();
            if (id == "") {
                $(".information-partners input[type=text]").val('');
                $(".information-partners input[type=textare]").val('');
            } else {
                getPartners(id);
            }
        });
    });

    $(document).on('click', '#table-partner tbody tr', function () {
        $('#table-partner tbody tr').css('background-color', '#fff');
        $(this).css('background-color', '#ccc');
        $(this).find("input[name=choose]").prop('checked', true);
    });

    $(document).on('click', '#btn-choose-partner', function () {
        $('.information-partners .partners-implementation select option:selected').removeAttr("selected");
        if ($('#table-partner tr').find('input[name=choose]').is(':checked')) {
            var id = $('#table-partner tr').find('input[name=choose]:checked').val();
            $('.information-partners .partners-implementation .partner_id').find('option[value=' + id + ']').prop('selected', true).trigger('change');
            getPartners(id);
            $("#modal-list-partner #table-partner .item" + id).css('background-color', '#fff');
            $('#modal-list-partner').modal('hide');
        }
        else {
            return;
        }
    });

    function getPartners(id) {
        var url = $('#modal-list-partner').find('#btn-choose-partner').data('url');
        $.ajax({
            type: 'GET',
            url: url + '/' + id,
            data: {},
            success: function (data) {
                for (var key in data) {
                    $(".information-partners input[name='wel_partner[" + key + "]']").val(data[key]);
                }
            }
        });
    }

    // JQuery Welfare 
    var htmlFileUpload = $('.welfare-file-upload .file').html();
    $(document).on('click', '.welfare-file-upload .add-file-input', function () {
        $('.welfare-file-upload .file').append(htmlFileUpload);
        $('.upload-file-input').prop('disabled', false);
        $('#error-file-extension').html('');
        $('#error-file-extension').addClass('hidden');
        $('#error-file-empty').addClass('hidden');
    });

    $(document).on('click touchstart', '.welfare-file-upload .delete-file-input', function () {
        $(this).parents('.form-group').remove();
    });

    if ($('#is_same_fee').is(":checked")) {
        $('.trial-employee').addClass('hidden');
    } else {
        $('.trial-employee').removeClass('hidden');
    }
    $('#is_same_fee').change(function () {
        if ($(this).is(":checked")) {
            $('.trial-employee').addClass('hidden');
        } else {
            $('.trial-employee').removeClass('hidden');
        }
    });
    var totalPerson = 0;
    $('.personal_fee').on('change', function () {
        $('.personal_fee').each(function () {
            var feePerson = parseFloat($(this).val().replace(/,/g, ""));
            var quanlity = $(this).closest('tr').find('input.number-expected').val();
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            isNaN(feePerson) ? feePerson = 0 : feePerson;
            totalPerson += feePerson*quanlity;
        });
        $('#total_personal_fee').val(totalPerson.toLocaleString("en-US"));
        totalPerson = 0;
    });
    var totalCompany = 0;
    $('.company_fee').on('change', function () {
        $('.company_fee').each(function () {
            var feeCompany = parseFloat($(this).val().replace(/,/g, ""));
            var quanlity = $(this).closest('tr').find('input.number-expected').val();
            isNaN(feeCompany) ? feeCompany = 0 : feeCompany;
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            totalCompany += feeCompany*quanlity;
        });
        $('#total_company_fee').val(totalCompany.toLocaleString("en-US"));
        totalCompany = 0;
    });
    var totalPersonAct = 0;
    $('.personal-fee-actual').on('change', function () {
        $('.personal-fee-actual').each(function () {
            var feePersonAct = parseFloat($(this).val().replace(/,/g, ""));
            var quanlity = $(this).closest('tr').find('input.number-actual').val();
            isNaN(feePersonAct) ? feePersonAct = 0 : feePersonAct;
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            totalPersonAct += feePersonAct*quanlity;
        });
        $('#total_personal_fee_actual').val(totalPersonAct.toLocaleString("en-US"));
        totalPersonAct = 0;
    });
    var totalCompanyAct = 0;
    $('.company-fee-actual').on('change', function () {
        $('.company-fee-actual').each(function () {
            var feeCompanyAct = parseFloat($(this).val().replace(/,/g, ""));
            var quanlity = $(this).closest('tr').find('.number-actual').val();
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            isNaN(feeCompanyAct) ? feeCompanyAct = 0 : feeCompanyAct;
            totalCompanyAct += feeCompanyAct*quanlity;
        });
        $('#total_company_fee_actual').val(totalCompanyAct.toLocaleString("en-US"));
        totalCompanyAct = 0;
    });
    $('.number-actual').on('change', function () {
        $('.number-actual').each(function () {
            var quanlity = parseFloat($(this).val().replace(/,/g, ""));
            var feePersonAct = $(this).closest('tr').find('input.personal-fee-actual').val();
            var feeCompanyAct = $(this).closest('tr').find('input.company-fee-actual').val();
            isNaN(feeCompanyAct) ? feeCompanyAct = 0 : feeCompanyAct;
            isNaN(feePersonAct) ? feePersonAct = 0 : feePersonAct;
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            totalCompanyAct += feeCompanyAct*quanlity;
            totalPersonAct += feePersonAct*quanlity;
        });
        $('#total_personal_fee_actual').val(totalPersonAct.toLocaleString("en-US"));
        $('#total_company_fee_actual').val(totalCompanyAct.toLocaleString("en-US"));
        totalCompanyAct = 0;
        totalPersonAct = 0;
    });

    $('.number-expected').on('change', function() {
        $('.number-expected').each(function () {
            var quanlity = parseFloat($(this).val().replace(/,/g, ""));
            var feePerson = $(this).closest('tr').find('input.personal_fee').val();
            var feeCompany = $(this).closest('tr').find('input.company_fee').val();
            isNaN(feeCompany) ? feeCompany = 0 : feeCompany;
            isNaN(feePerson) ? feePerson = 0 : feePerson;
            isNaN(quanlity) ? quanlity = 0 : quanlity;
            totalCompany += feeCompany*quanlity;
            totalPerson += feePerson*quanlity;
        });
        $('#total_company_fee').val(totalCompany.toLocaleString("en-US"));
        $('#total_personal_fee').val(totalPerson.toLocaleString("en-US"));
        totalPerson = 0;
        totalCompany = 0;
    });
    
    var totalExpected = formatValue($('.total-all-expected').val());
    var total = 0;
    var number = 0;
    var feePerson = 0;
    var feeCompany = 0;
    var totalActual = 0;
    var numberActual = 0;
    var feePersonActual = 0;
    var feeCompanyActual = 0;
    attachedFirst = getValueInput('attached-first-fee-plan');
    attachedSecond = getValueInput('attached-second-fee-plan');
    first = $('#form-event-info').find('.attached-first-fee-plan input');
    second = $('#form-event-info').find('.attached-second-fee-plan input');

    attachedFirstActual = getValueInput('attached-first-fee-actual');
    attachedSecondActual = getValueInput('attached-second-fee-actual');
    firstActual = $('#form-event-info').find('.attached-first-fee-actual input');
    secondActual = $('#form-event-info').find('.attached-second-fee-actual input');

    $('#is_allow_attachments').change(function () {
        var numberAll = parseInt($('.number-expected-total').val());
        var totalAll = parseFloat($('.total-all-expected').val().replace(/,/g, ""));
        var feePersonPlan = parseFloat($('#total_personal_fee').val().replace(/,/g, ""));
        var feeCompanyPlan = parseFloat($('#total_company_fee').val().replace(/,/g, ""));

        var numberAllActual = parseInt($('.number-actual-total').val());
        var totalAllActual = formatValue($('.total-all-actual').val());
        var feePersonAllActual = formatValue($('.total_personal_fee_actual').val());
        var feeCompanyAllActual = formatValue($('.total_company_fee_actual').val());

        if ($(this).is(":checked")) {
            $('#fee-free-attach-infor').removeClass('hidden');
            $('.attached').removeClass('hidden');
            if ((totalAll === 0 || isNaN(totalAll)) && (numberAll === 0 || isNaN(numberAll))) {
                return;
            }
            if (isNaN(totalAllActual) && isNaN(numberAllActual)) {
                return;
            }
            // fee table expected 
            for (var key in attachedFirst) {
                $('.attached-first-fee-plan #' + first[key].id).val(attachedFirst[key].toLocaleString("en-US"));
            }

            total = totalAll + attachedFirst[3];
            number = numberAll + attachedFirst[0];
            feePerson = feePersonPlan +  attachedFirst[1];
            feeCompany = feeCompanyPlan +  attachedFirst[2];
            $('.total-all-expected').val(total.toLocaleString("en-US"));
            $('#total_personal_fee').val(feePerson.toLocaleString("en-US"));
            $('#total_company_fee').val(feeCompany.toLocaleString("en-US"));
            $('.number-expected-total').val(number);
            $("input[name='event[join_number_plan]']").val(number);

            // fee table acutal
            for (var key in attachedFirstActual) {
                $('.attached-first-fee-actual #' + firstActual[key].id).val(attachedFirstActual[key].toLocaleString("en-US"));
            }
            
            totalActual = totalAllActual + attachedFirstActual[3];
            numberActual = numberAllActual + attachedFirstActual[0];
            feePersonActual = feePersonAllActual + attachedFirstActual[1];
            feeCompanyActual = feeCompanyAllActual + attachedFirstActual[2];
            $('.number-actual-total').val(numberActual);
            $('.total-all-actual').val(totalActual.toLocaleString("en-US"));
            $('.total_personal_fee_actual').val(feePersonActual.toLocaleString("en-US"));
            $('.total_company_fee_actual').val(feeCompanyActual.toLocaleString("en-US"));
            
        } else {
            $('#fee-free-attach-infor').addClass('hidden');
            $('.attached').addClass('hidden');
            if ((totalAll === 0 || isNaN(totalAll)) && (numberAll === 0 || isNaN(numberAll))) {
                return;
            }
            if (isNaN(totalAllActual)&& isNaN(numberAllActual)) {
                return;
            }
            
            //fee table expected 
            attachedFirst = getValueInput('attached-first-fee-plan');
            attachedSecond = getValueInput('attached-second-fee-plan');
            total = totalAll - attachedFirst[3];
            number = numberAll - attachedFirst[0];
            feePerson = feePersonPlan - attachedFirst[1];
            feeCompany = feeCompanyPlan - attachedFirst[2];
            $('.total-all-expected').val(total.toLocaleString("en-US"));
            $('#total_personal_fee').val(feePerson.toLocaleString("en-US"));
            $('#total_company_fee').val(feeCompany.toLocaleString("en-US"));
            $('.number-expected-total').val(number);
            $("input[name='event[join_number_plan]']").val(number);

            // fee table actual
            attachedFirstActual = getValueInput('attached-first-fee-actual');
            totalActual = totalAllActual - attachedFirstActual[3];
            numberActual = numberAllActual - attachedFirstActual[0];
            feePersonActual = feePersonAllActual - attachedFirstActual[1];
            feeCompanyActual = feeCompanyAllActual - attachedFirstActual[2] ;
            $('.number-actual-total').val(numberActual);
            $('.total-all-actual').val(totalActual.toLocaleString("en-US"));
            $('.total_personal_fee_actual').val(feePersonActual.toLocaleString("en-US"));
            $('.total_company_fee_actual').val(feeCompanyActual.toLocaleString("en-US"));

            // add  value
            $('.attached-first input').val(0);
            $('.attached-second input').val(0);
        }
        totalExpected = total;
        total = 0;
        number = 0;
        feePerson = 0;
        feeCompany = 0;
        totalActual = 0;
        numberActual = 0;
        feePersonActual = 0;
        feeCompanyActual = 0;
    });

    function getValueInput(className)
    {
        var objectClass = $('#form-event-info').find('.' + className + ' input');
        var para = {};
        var value;
        for (var i = 0; i < objectClass.length; i++) {
            value = parseFloat($('#' + objectClass[i].id).val().replace(/,/g, ""));
            isNaN(value) ? value = 0 : value;
            para[i] = value;
        }
        ;
        return para;
    }

    $(document).on('click', '#partner-group', function () {
        $('#list-partner-group').find('label.error').addClass('hidden');
        $('#list-partner-group').find('p.error').addClass('hidden');
        $('#list-partner-group').find('#namePartnerGroup').val('');
        $('#list-partner-group').modal('show');
    });

    function addOption(url, name, i) {
        var data = $('input[name="name' + i + '"]').val();
        $.ajax({
            method: "POST",
            url: url,
            data: {
                _token: '{{ csrf_token()}}', data: data
            },
            success: function (data) {
                var html = '<option value="' + data.id + '">' + data.name + '</option>';
                $('select[name="' + name + '"]').append(html);
            }
        });
    }
    ;

    $(document).on('change', '.welfare-select-team-member', function () {
        $("li").each(function () {
            var $this = $(this);
            $this.html($this.html().replace(/&nbsp;/g, ' '));
        });
    });
    $(document).on('click', 'a[dataurl]', function () {
        $('#' + this.getAttribute('class')).DataTable({
            processing: false,
            serverSide: true,
            retrieve: true,
            ajax: this.getAttribute('dataurl'),
        });
    });
    $(document).on('click', '.edit-checkbox', function (e) {
        e.preventDefault();
        URLajaxComfirmJonin = this.closest('table').getAttribute('link');
        var id = this.closest('table').getAttribute('id');
        dataComfirmJonin = {
            employee_id: this.parentElement.parentElement.getAttribute('employee_id'),
            wel_id: this.parentElement.parentElement.getAttribute('wel_id'),
            name: this.name,
            value: this.checked ? 1 : 0,
        };
        $('.modal-noti-dange').attr('id','notifi-comfirm-jonin');
         if(this.checked) {
            $('#notifi-comfirm-jonin').find('#content-noti').text(textConfirm.confirm);
        } else {
            $('#notifi-comfirm-jonin').find('#content-noti').text(textConfirm.cancelConfirm);
        }
        $('.modal-noti-dange').modal('show');

    });
    $(document).on('click','#notifi-comfirm-jonin .btn-cancel-employee', function(){
        $.ajax({
            method: "GET",
            url: URLajaxComfirmJonin,
            data: dataComfirmJonin,
            success: function (data) {
                if (data) {
                    $('#users-table-emp').DataTable().ajax.reload(null, false);
                    $('#notifi-comfirm-jonin').modal('hide');
                } else {
                    alert('Error');
                }
            }
        });
    });

    $('.btn-group li').on('click', function () {
        $('.btn-success')[0].innerText = this.textContent;
        $('[name="event[status]"]').val($(this).index() + 1);
    });
    $('.btn-success')[0].innerText = $('[name="event[status]"]').children()[$('[name="event[status]"]').val() - 1].innerText;
    $(document).on('click', '#btn-submit-fake', function () {
        $('.number-expected').each(function () {
            var val = parseInt($(this).val());
            if (isNaN(val) || val === 0) {
                $("#form-event-info").find("input[group='" + $(this).attr('group') + "']").val(0);
            }
        });
        $('#btn-submit').click();
        var form = $("#form-event-info");
        if (form.valid()) {
            $('#btn-submit-fake ._uploading').removeClass('hidden');
        }
    });
    /**
     * format number input number
     */
    var $form = $("#form-event-info");
    var $input = $form.find("input[placeholder='0.00']");
    var $group = $form.find("input[group]");
    $group.on('change', function () {
        var para = $form.find("input[group='" + $(this).attr('group') + "']");
        setSumvalue(para[0].id, para[1].id, para[2].id, para[3].id);
        var total = 0;
        var numberTotal = 0;
        var totalAcutal = 0;
        var numberAcutal = 0;
        $(".total-expected").each(function () {
            var val = parseFloat($(this).val().replace(/,/g, ""));
            if (isNaN(val)) {
                val = 0;
            }
            total += val;
        });
        var feeEstimates = parseFloat($('#fee_estimates').val().replace(/,/g, ""));
        isNaN(feeEstimates) ? feeEstimates = 0 : feeEstimates;
        totalExpected = total;
        $('.total-all-expected').val((total + feeEstimates).toLocaleString("en-US"));
        $('.number-expected').each(function () {
            var val = parseInt($(this).val());
            if (isNaN(val)) {
                val = 0;
            }
            numberTotal += val;
        });
        $('.number-expected-total').val(numberTotal);
        $("input[name='event[join_number_plan]']").val(numberTotal);
        $('.number-actual').each(function () {
            var number = parseInt($(this).val());
            isNaN(number) ? number = 0 : number;
            numberAcutal += number;
        });
        $('.number-actual-total').val(numberAcutal);
        $(".total-actual").each(function () {
            var valtotal = parseFloat($(this).val().replace(/,/g, ""));
            if (isNaN(valtotal)) {
                valtotal = 0;
            }
            totalAcutal += valtotal;
        });
        var feeExtra = $('#fee_extra').val();
        if (typeof feeExtra == 'undefined') {
            feeExtra = 0;
        } else {
            feeExtra = parseFloat(feeExtra.replace(/,/g, ""));
        }
        isNaN(feeExtra) ? feeExtra = 0 : feeExtra;
        $('.total-all-actual').val((totalAcutal + feeExtra).toLocaleString("en-US"));

    });
    function setSumvalue(id1, id2, id3, idSum) {
        var val1 = parseFloat($('#' + id1).val().replace(/,/g, ""));
        var val2 = parseFloat($('#' + id2).val().replace(/,/g, ""));
        var val3 = parseFloat($('#' + id3).val().replace(/,/g, ""));
        isNaN(val1) ? val1 = 0 : val2;
        isNaN(val2) ? val2 = 0 : val2;
        isNaN(val3) ? val3 = 0 : val3;
        $('#' + idSum).val((val1 * (val2 + val3)).toLocaleString("en-US"));
    }

    $(document).on("keyup", "#form-event-info input[placeholder='0.00']", function (event) {
        // When user select text in the document, also abort.
        var selection = window.getSelection().toString();
        if (selection !== '') {
            return;
        }

        //  When the arrow keys are pressed, abort.
        if ($.inArray(event.keyCode, [38, 40, 37, 39]) !== -1) {
            return;
        }
        var $this = $(this);
        var input = $this.val();
        var input = input.replace(/[\D\s\._\-]+/g, "");
        input = input ? parseFloat(input, 10) : 0;
        $this.val(function () {
            return (input === 0) ? 0 : input.toLocaleString("en-US");
        });
    });
    $(document).on('keyup', '.number-expected, .number-actual', function () {
        if (/\D/g.test($(this).val())) {
            $(this).val($(this).val().replace(/\D/g, ''));
        }
    });
    $(document).on('keypress', '.number-expected, .number-actual', function () {
        var input = $(this).val();
        $(this).val(function () {
            return (input == 0) ? '' : input;
        });
    });
    $(document).on("keypress", "#form-event-info input[placeholder='0.00']", function () {
        var input = parseInt($(this).val().replace(/,/g, ""));
        isNaN(input) ? input = 0 : input;
        $(this).val(function () {
            return (input / 10 == 0) ? '' : input.toLocaleString("en-US");
        });
    });
    
    $(document).on('change', '#fee_estimates', function () {
        var total = 0;
        $(".total-expected").each(function () {
            var val = parseFloat($(this).val().replace(/,/g, ""));
            if (isNaN(val)) {
                val = 0;
            }
            total += val;
        });
        var feeEstimate = parseFloat($(this).val().replace(/,/g, ""));
        isNaN(feeEstimate) ? feeEstimate = 0 : feeEstimate;
        var feeChange = total + feeEstimate;

        $('#fee_total').val(feeChange.toLocaleString("en-US"));

    });
    /*
     ** // bind data to field when popup organizer close .()
     */
    $(document).on('click', '#choiceBtn', function () {
        var rowSelected = $(".selected");
        if (rowSelected.length > 0) {
            $('[name="wel_organizer[name]"]').val(rowSelected.children()[0].textContent);
            $('[name="wel_organizer[phone]"]').val(rowSelected.children()[1].textContent);
            $('[name="wel_organizer[position]"]').val(rowSelected.children()[2].textContent);
            $('[name="wel_organizer[phone_company]"]').val(rowSelected.children()[3].textContent);
            $('[name="wel_organizer[company]"]').val(rowSelected.children()[4].textContent);
            $('[name="wel_organizer[email_company]"]').val(rowSelected.children()[5].textContent);
            $('#modal-list-organizer').modal('hide');
        } else {
            $('#select-null-data').modal('show');
        }

    });

    $("input[name=groupEvent], input[name=purposeName]").keyup(function (event) {
        if (event.keyCode == 13) {
            $(this).parents().eq(1).find('button').click();
        }
    });

    $(document).on('keyup', 'input[name=name1]', function (event) {
        if (event.keyCode == 13) {
            $(this).parent().find('button').click();
        }
    });

    /*
     ** create new item on '+' click
     */
    $(document).on('click', '.btn-save-event-popup', function () {
        $('.massage_exist').attr('hidden', true);
        $('.massage_null').attr('hidden', true);
        var url = $(this).attr('route');
        var name = $(this).attr('selector');
        var classSelect = $('select[name="' + name + '"]').parents().eq(2).attr('class').split(' ')[1];
        var input = $(this).attr('inputname');
        var modal = $(this).attr('modal');
        var data = {};
        data['name'] = $('input[name="' + input + '"]').val().trim();
        data['_token'] = $('input[name=_token]').val();
        var $tableName = $(this).attr('tableid');
        if (data['name'] == '') {
            $('.massage_null').removeAttr('hidden');
        } else {
            $(this).find('._uploading').removeClass('hidden');
            $.ajax({
                method: "POST",
                url: url,
                data: data,
                success: function (data) {
                    if (typeof data.id !== "undefined") {
                        if (data.name.length > 43) {
                            var nameAttr = data.name.substring(0, 40) + '...';
                        } else {
                            nameAttr = data.name;
                        }
                        var html = '<option selected="true" value="' + data.id + '">' + nameAttr + '</option>';
                        $('select[name="' + name + '"]').append(html);
                        $("." + classSelect).load(location.href + " ." + classSelect + ">*", "");
                        $('#' + $tableName).DataTable().ajax.reload(function () {
                            $('.modal-backdrop').remove();
                        });
                        $('input[name="' + input + '"]').val('');
                    } else {
                        if (typeof data.messages !== "object") {
                            $('.massage_exist').text(data.name);
                            $('.massage_exist').removeAttr('hidden');
                        }
                    }
                    $('.btn-save-event-popup ._uploading').addClass('hidden');
                }
            });
        }
        $(this).css('color', '#fff');
    });
    /*
     ** get item selected fill to input on form
     */
    $(document).on('click', '.choiceBtnGroupWel', fillValuefromP0pup);
    function fillValuefromP0pup() {
        var rowSelected = $("#" + this.getAttribute('table') + " .selected");
        if (rowSelected.length > 0) {
            var value = rowSelected.attr('numrow');
            var name = $("#" + this.getAttribute('table') + " .selected").attr('fillto');
            $('select[name="' + name + '"]').val(value).trigger('change');
            $('#' + this.getAttribute('modal')).modal('hide');
        } else {
            $('#select-null-data').modal('show');
        }
    }

    /*
     ** edit item on datatable
     */
    $(document).on('click', '.edit-modal-groupEvent', function () {
        var className = $(this).parents().eq(1).attr('class').split(/\s+/);
        var tagSelect = $(this).data('select');
        $(this).prop('disabled', true);
        var inputHtml = '<div class="col-md-12 row"><input class="col-md-10" style="height: 34px" type="text" name="name1" value="' + $('#edit' + this.getAttribute('data-id')).text() + '">' +
                '<button type="button" data-select="' + tagSelect + '" route="' + this.getAttribute('route') + '" class="btn btn-info save-modal-groupEvent" id="save-modal-groupEvent" data-id="' + this.getAttribute('data-id') + '">' +
                '<i class="fa fa-check-square-o" aria-hidden="true"></i><span class="_uploading hidden">&nbsp;<i class="fa fa-spin fa-refresh"></i></span></button></div>' +
                '<div class="col-md-12 row"><p class="massage_exist_edit" style="color: red" hidden>' + $(".massage_exist").first().text() + '</p>' +
                '<p class="massage_null_edit" style="color: red" hidden>' + $(".massage_null").first().text() + '</p></div>';
        $('.' + className[0]).find('#edit' + $(this).data('id')).text('');
        $('.' + className[0]).find('#edit' + $(this).data('id')).append(inputHtml);
    });

    /*
     ** save item on datatable
     */
    $(document).on('click', '.save-modal-groupEvent', saveItempopup);
    function saveItempopup() {
        $('.massage_null_edit').attr('hidden', true);
        $('.massage_exist_edit').attr('hidden', true);
        var $tableName = $(this).closest('table').attr('id');
        var url = this.getAttribute('route');
        var select = $(this).data('select');
        var data = {};
        data['name'] = $(this).prev().val().trim();
        data['_token'] = $('input[name=_token]').val();
        data['id'] = this.getAttribute('data-id');
        if (data['name'] == '') {
            $('.massage_null_edit').removeAttr('hidden');
        } else {
            $(this).find('._uploading').removeClass('hidden');
            $.ajax({
                method: "POST",
                url: url,
                data: data,
                success: function (data) {
                    if (typeof data.id !== "undefined") {
                        $('#' + $tableName).DataTable().ajax.reload();
                    }
                    else {
                        if (typeof data.messages !== "object") {
                            $('.massage_exist_edit').text(data.name);
                            $('.massage_exist_edit').removeAttr('hidden');
                        }
                    }
                    $("." + select).load(location.href + " ." + select + ">*", "");
                    $('.save-modal-groupEvent ._uploading').addClass('hidden');
                }
            });
        }
    }

    function addWelFeeMore()
    {
        var feeName = $('input[fieldname="Extra_payments_name"]').val();
        var fee = $('input[fieldname="Extra_payments_budget"]').val();
        var feeSrc = $('input[fieldname="Extra_payments_src"]').val();
        var countError = 0;

        if (feeName.trim().length == 0) {
            $('#error-extra-name').removeClass('hidden');
            countError++;
        }

        if (fee.trim().length == 0) {
            $('#error-extra-budget').removeClass('hidden');
            countError++;
        } else if (fee.trim().length >= 19) {
            $('#error-extra-budget-number').removeClass('hidden');
            countError++;
        }

        if (countError > 0) {
            return false;
        }
        return true;
    }

    $(document).on('keyup', 'input[fieldname="Extra_payments_name"]', function () {
        $(this).closest('td').find('label#error-extra-name-unique').addClass('hidden');
        var lengthInput = $(this).val();
        if (lengthInput.trim().length == 0) {
            $(this).closest('td').find('label#error-extra-name').removeClass('hidden');
        } else {
            $(this).closest('td').find('label#error-extra-name').addClass('hidden');
        }
    });

    $(document).on('keyup', 'input[fieldname="Extra_payments_budget"]', function () {
        var lengthInput = $(this).val();
        if (lengthInput.trim().length == 0) {
            $(this).closest('td').find('label#error-extra-budget').removeClass('hidden');
        } else {
            $(this).closest('td').find('label#error-extra-budget').addClass('hidden');
        }
        if (lengthInput.trim().length >= 19) {
            $(this).closest('td').find('label#error-extra-budget-number').removeClass('hidden');
        } else {
            $(this).closest('td').find('label#error-extra-budget-number').addClass('hidden');
        }
    });

    /*
     ** save welfare fee more
     */
    $(document).on('click', '#save-welfee-more', function () {
        if (!addWelFeeMore()) {
            return false;
        }
        var $row = $(this).closest('tr');
        var $columns = $row.find('td');
        var data = {};
        jQuery.each($columns, function (i, item) {
            data[$(item).find('input').attr('fieldname')] = $(item).find('input').val();

        });
        var url = $(this).attr('route');
        $.ajax({
            method: "GET",
            url: url,
            data: data,
            success: function (data) {
                if (data.success == 0) {
                    $.each(data.messages, function (key, value) {
                        $('input[fieldname="' + key + '"]').closest('td').find('label#error-extra-name-unique').removeClass('hidden').html(value);
                    });
                } else {
                    $('#table_wel_fee_more').DataTable().ajax.reload(null, false);
                    $('#wel_fee_more').find('#addRow').show();
                    $('#table_wel_fee_more thead').find('tr').removeClass('disableMouse');
                    updateFeeActualWithFeeMore(data.cost, true);
                }
            }
        });
    });

    /*
     ** delete item on modal
     */
    $(document).on('click', '.delete-modal-item', deleteItemModal);
    function deleteItemModal() {
        var modal = this.getAttribute('modal');
        var route = this.getAttribute('route');
        var tableName = $(this).closest('table').attr('id');
        var select = $(this).data('select');
        $('#' + modal).on('shown.bs.modal', function () {
            $('#' + modal + ' .btn-delete-fee-more').attr('route', route);
            $('#' + modal + ' .btn-delete-fee-more').attr('modal', modal);
            $('#' + modal + ' .btn-delete-fee-more').attr('tablename', tableName);
            $('#' + modal + ' .btn-delete-fee-more').attr('select', select);
        }).modal('show');
    };
    $(document).on('click', '.modal-delete-confirm .btn-delete-fee-more', function () {
        $('#' + this.getAttribute('modal')).modal('hide');
        var url = this.getAttribute('route');
        var table = this.getAttribute('tableName');
        var select = this.getAttribute('select');
        $.ajax({
            method: "GET",
            url: url,
            success: function (data) {
                $('#' + table).DataTable().ajax.reload();
                $("." + select).load(location.href + " ." + select + ">*", "");
                if (data.cost) {
                    updateFeeActualWithFeeMore(data.cost, false);
                }
            }
        });
    });
    
    //$('#start_at_register, #end_at_register').datetimepicker('destroy');
    
    $('#start_at_exec').datetimepicker({
        useCurrent: false,
        sideBySide: true,
        format: 'YYYY-MM-DD'
    });
    $('#end_at_exec').datetimepicker({
        useCurrent: false,
        sideBySide: true,
        format: 'YYYY-MM-DD'
    });
    $('#start_at_register').datetimepicker({
        useCurrent: false,
        sideBySide: true,
        format: 'YYYY-MM-DD'
    });
    $('#end_at_register').datetimepicker({
        useCurrent: false,
        sideBySide: true,
        format: 'YYYY-MM-DD'
    });

    var optionDatePicker = {
        autoclose: true,
        format: 'yyyy-mm-dd',
        weekStart: 1,
        todayHighlight: true
    };

    $('#rep_card_id_date').datepicker(optionDatePicker);
    $('#empl_offical_after_date').datepicker(optionDatePicker);
    $('#birthday').datepicker(optionDatePicker).on('changeDate', function (ev) {
        $(this).valid();
    });

    $(".myModal_group_event").on("hidden.bs.modal", function () {
        var $table = $(this).find('table[id]').attr('id');
        $('#' + $table).DataTable().ajax.reload();
        $('.massage_exist').attr('hidden', true);
        $('.massage_null').attr('hidden', true);
        var url = $(this).attr('route').slice(0, $(this).attr('route').lastIndexOf('/'));
        var name = $(this).find('tr[fillto]').attr('fillto');
        var old_value = $("[name='" + name + "']").val();
    });
    $(document).on('change', 'input[name^="wel_file"]', function () {
        $('#error-file-extension').html('');
        $('#error-file-extension').addClass('hidden');
        $('.upload-file-input').removeAttr('disabled');
        $('input[name^="wel_file"]').each(function () {
            if ($(this).val() != 0) {
                $(this).closest('.form-group').find('#error-file-required').addClass('hidden');
            }
        });
    });

    $(document).on('click', '.file-upload .upload-file-input', function (e) {
        e.preventDefault();
        var count = 0;
        var fileLength = $('.file').children('.form-group').length;
        if (fileLength == 0) {
            $('#error-file-empty').removeClass('hidden');
            return false;
        }
        $('input[name^="wel_file"]').each(function () {
            if ($(this).val() == 0) {
                $(this).closest('.form-group').find('#error-file-required').removeClass('hidden');
                count++;
            }
        });
        if (count > 0) {
            return false;
        }
        $(this).attr('disabled', 'disabled');
        $('.file-upload ._uploading').removeClass('hidden');
        var url = $(this).data('url');
        var wel_id = $('.file-upload input[name=wel_id]').val();
        var formData = new FormData($('#form-event-info')[0]);
        formData.append('wel_id', wel_id);
        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                $('._uploading').addClass('hidden');
                if (data.status == false) {
                    $('#error-file-extension').removeClass('hidden');
                    $('#error-file-extension').html(data.msg);
                    $('#error-file-extension').show();
                }
                if (data.status == true) {
                    $("#wel-file").load(location.href + " #wel-file>*", "");
                }
                if (data.status == null) {
                    $('#modal-success-notification').find('.text-default').text('Tổng dung lượng file nhỏ hơn 25MB');
                    $('#modal-success-notification').find('.text-default').css('padding', '5px');
                    $('#modal-success-notification').find('.modal-title').text(titleModal);
                    $('#modal-success-notification').modal('show');
                }
                setTimeout(function () {
                    $('.upload-file-input').removeAttr('disabled');
                }, 1000);

            }
        });
    });

    $(document).on('click', '.delete-welfare-file', function () {
        var url = $(this).data('url');
        $('#modal-delete-welfare-file .btn-ok').data('url', url);
        $('#modal-delete-welfare-file .did').text($(this).data('id'));
        $('#modal-delete-welfare-file').modal('show');
    });

    $(document).on('click', '#modal-delete-welfare-file .btn-ok', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var id = $('#modal-delete-welfare-file .did').text();
        $('.delete-welfare-file[data-id=' + id + ']').find('._uploading').removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': tokenValue,
                'id': id
            },
            success: function (data) {
                if (data.status == true) {
                    $("#wel-file .welfare-file-upload .list-file").load(location.href + " #wel-file .welfare-file-upload .list-file>*", "");
                }
            }
        });
    });

    $("#welfare-subject").keyup(function () {
        if ($(this).val().trim().length === 0) {
            $('#error-subject').removeClass('hidden');
        } else {
            $('#error-subject').addClass('hidden');
        }
    });

    $(document).on('click', '#modal-success-send-mail .btn-confirm', function (e) {
        $('#btn-send-mail').attr('disabled', 'disabled');
        var url = $('.send-mail-event #btn-send-mail').data('url');
        var data = {};
        data['wel_id'] = $('input[name="email[wel_id]"]').val();
        data['subject'] = $('input[name="email[subject]"]').val();
        data['content'] = CKEDITOR.instances.content.getData();
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': siteConfigGlobal.token,
                'email': data
            },
            success: function (data) {
                if (data.success == 0) {
                    $('#modal-warning-send-mail').find('p.text-default').addClass('hidden');
                    $('#modal-warning-send-mail').find('p.text-change').html(data.message);
                    $('#modal-warning-send-mail').find('p.text-change').removeClass('hidden');
                    $('#modal-warning-send-mail').modal('show');
                    return false;
                }
                if (data.ok == 'ok') {
                    $('#modal-warning-send-mail').removeClass('modal-warning');
                    $('#modal-warning-send-mail').addClass('modal-success');
                    $('#modal-warning-send-mail').find('p.text-default').addClass('hidden');
                    $('#modal-warning-send-mail').find('p.text-change').html(data.message);
                    $('#modal-warning-send-mail').find('p.text-change').removeClass('hidden');
                    $('#modal-warning-send-mail').modal('show');
                }
            }
        });
    });
    $('#modal-warning-send-mail').on('hidden.bs.modal', function () {
        $(this).find('p.text-default').removeClass('hidden');
        $(this).find('p.text-change').addClass('hidden');
        $(this).addClass('modal-warning');
        $(this).removeClass('modal-success');
        $('#btn-send-mail').removeAttr('disabled');
    });

    $(document).on('contextmenu', '#users-table-emp-att tbody tr', function (e) {
        var wel_id = $('input[name="event[id]"]').val();
        $('#modal-add-relatives #id, #modal-delete-relative-attached #id-relative-attach').val($(this).attr('id'));
        $('#modal-add-relatives #emplid').val($(this).attr('employee_id'));
        $('#modal-add-relatives #welid').val(wel_id);
        $('#modal-add-relatives #nameEmpl').val($(this).attr('emplname'));
        $('.confirm-relative-participation').data('welId', wel_id);
        $('.confirm-relative-participation').data('emplId', $(this).attr('employee_id'));        
        if ($(this).find('.edit-checkbox').is(":checked")) {
            $('.confirm-relative-participation').find('.text-default').addClass('hidden');
            $('.confirm-relative-participation').find('.text-change').removeClass('hidden');
            $('.confirm-relative-participation').data('value', 0);
        } else {
            $('.confirm-relative-participation').find('.text-default').removeClass('hidden');
            $('.confirm-relative-participation').find('.text-change').addClass('hidden');
            $('.confirm-relative-participation').data('value', 1);
        }
        $('#users-table-emp-att tbody tr').css('background-color', '#fff');
        $(this).css('background-color', '#ccc');
        $('.radmenu').show();
        $('.radmenu').css({
            left: e.pageX,
            top: e.pageY,
            overflow: 'visible'
        });
        return false;
    });
    $(document).on('click', '.add-relative', function () {
        $('#modal-add-relatives').find('input[name=id]').val('');
        $('.btn-add-wel-empl-relatives').removeAttr('disabled');
        $('#modal-add-relatives').modal('show');
    });
    $(document).on('click', '.edit-relative', function () {
        var url = $(this).data('url');
        var id = $('#modal-add-relatives #id').val();
        $.ajax({
            type: 'GET',
            url: url,
            data: {'id': id},
            success: function (data) {
                for (var key in data)
                {
                    $('#modal-add-relatives #' + key).val(data[key]);
                }
                if (data['employee_id']) {
                    $('.relative_employee_id_attached').append('<option value="'+ data['employee_id'] +'" selected="selected">'+ data['employee_name'] +'</option>').trigger('change');
                }
                if (data['support_cost']) {
                    $('#fee_favorable_attached').val(data['support_cost']);
                }
                setSelectRelation(data['welfare_id'], data['support_cost']);
                $('#relation_name_id').append('<option value="'+ data['relation_name_id'] +'" selected="selected">'+ data['relation_name'] +'</option>').trigger('change');
                if (data['card_id'] == "") {
                    $('#modal-add-relatives').find('#not_card_id').prop('checked', true);
                    $('#modal-add-relatives').find('.label-card-id').html(label.replace('<em>*</em>', ''));
                    $('.input-relative_card_id').addClass('hidden');;
                } else {
                    $('#modal-add-relatives').find('#is_card_id').prop('checked', true);
                }
                $('.relative_employee_id_attached').parent().addClass('disabledbutton');
                $('.btn-add-wel-empl-relatives').prop('disabled', false);
                $('#modal-add-relatives').modal('show');
            }
        });
        $('#modal-add-relatives').modal('show');
    });
    $(document).on('submit', '#form-add-wel-empl-relatives', function (event) {
        event.preventDefault();
        var url = $(this).attr('action');
        var $inputs = $('#modal-add-relatives :input');
        var relatives = {};
        $inputs.each(function () {
            relatives[this.name] = $(this).val();
        });        
        if (!$(this).valid()) {
            return false;
        } else {
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    'relatives': relatives,
                    '_token': siteConfigGlobal.token
                },
                success: function (data) {
                    if (data.status == 0) {
                      $('#favorable-require-max').show();  
                    } else if (data.status === 'ok') {
                        $('#users-table-emp-att').DataTable().ajax.reload();
                        if (data.count == true) {
                            $('.edit-relative').removeClass('hidden');
                            $('.remove-relative').removeClass('hidden');
                        } else {
                            $('.edit-relative').addClass('hidden');
                            $('.remove-relative').addClass('hidden');
                        }
                        $('.confirm-relative-participation').removeClass('hidden');
                        $('#modal-add-relatives').modal('hide');
                    }
                    $('.btn-add-wel-empl-relatives').removeAttr('disabled');
                }
            });
        }

    });
    var label = $('.label-card-id').html();
    $('#modal-add-relatives').on('hidden.bs.modal', function () {
        $(this).find('input[type=text]').val('');
        $(this).find('#gender').prop('selectedIndex', 0);
        $(this).find('#relation_name_id').prop('selectedIndex', 0);
        $(this).find('#fee_favorable_attached').prop('selectedIndex', 0);
        $(this).find('#is_card_id').prop('checked', true);
        $(this).find('.label-card-id').html(label);
        //$(this).find('#relation_name_id').select2("destroy");
        $(this).find('p.error').hide();
        $(this).find('.input-relative_card_id').removeClass('hidden');
        $('.input-relative_card_id').find('#card_id').prop('readonly', false);
        $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
        $('.btn-add-wel-empl-relatives').removeAttr('disabled');
        $('.relative_employee_id_attached').val('').trigger("change");
        $('.relative_employee_id_attached').parent().removeClass('disabledbutton');
        $('#modal-add-relatives').find('label[class=error]').hide();
    });

    $('#modal-add-relatives').on('shown.bs.modal', function () {
        var url = $(this).data('list-select2');
        $('.relative_employee_id_attached').select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            }
        }).on('change', function() {
            $(this).valid();
        });
        $('#relation_name_id').select2();
    });
    
    $(document).on('change', '#fee_favorable_attached', function () {
        var wel_id = $('input[name="welid"]').val();
        var favorable = $('#fee_favorable_attached').val();
        $('p.error').hide();
        setSelectRelation(wel_id, favorable);
    });
    
    function setSelectRelation(wel_id, favorable)
    {
        var url = $('#fee_favorable_attached').data('url');
        $('#relation_name_id').select2({
            ajax: {
                url: url + '/' + wel_id + '/' + favorable,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
                cache: false
            }
        });
    }
    
    $(document).on('click', '.remove-relative', function () {
        $('#modal-delete-relative-attached').modal('show');
    });

    $(document).on('click', '#modal-delete-relative-attached .btn-ok', function () {
        var url = $('#modal-delete-relative-attached').find('form').attr('action');
        var id = $('#modal-delete-relative-attached #id-relative-attach').val();
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                '_token': siteConfigGlobal.token,
                'welid': id,
            },
            success: function (data) {
                $('#users-table-emp-att').DataTable().ajax.reload();
                if (data.count == true) {
                    $('.edit-relative').removeClass('hidden');
                    $('.remove-relative').removeClass('hidden');
                } else {
                    $('.edit-relative').addClass('hidden');
                    $('.remove-relative').addClass('hidden');
                    $('.confirm-relative-participation').addClass('hidden');
                }
            }
        });
    });

    $(document).on('click', '.confirm-relative-participation', function () {
        var url = $('#users-table-emp-att').attr('link');
        var data = {
            employee_id: $(this).data('emplId'),
            wel_id: $(this).data('welId'),
            name: 'is_joined',
            value: $(this).data('value'),
        };
        $.ajax({
            method: "GET",
            url: url,
            data: data,
            success: function (data) {
                if (data) {
                    $('#users-table-emp-att').DataTable().ajax.reload(null, false);

                } else {
                    alert('Error');
                }
            }
        });
    });

    $(document).click(function (e) {
        $('#users-table-emp-att tbody tr').css('background-color', '#fff');
        $('.radmenu').hide();
        $('.radmenu').css({
            overflow: 'hidden'
        });
    });

    $('#reset-list-purposes-welfare, #reset-list-group-welfare').on('click', function () {
        var table = $(this).parents().eq(1).find('.dataTables_filter');
        var idTable = $(this).parents().eq(1).find('table').DataTable();
        idTable.order([1, 'desc']).draw();
        table.find('input').val('').keyup();
    });

    $('.relative_employee_id').select2();

    // Datatable group event and purposes
    $(".myModal_group_event").on("shown.bs.modal", function () {
        var $table = $(this).find('table').attr('id');
        var url = $(this).attr('route');
        var table = $('#' + $table).DataTable({
            "destroy": true,
            pagingType: "full_numbers",
            "processing": false,
            "serverSide": true,
            "ajax": url,
            "scrollY": "200px",
            "scrollCollapse": true,
            "paging": true,
            "fixedColumns": true,
            "bLengthChange": false,
            "oLanguage": dataLang,
            order: [[1, 'desc']],
            aoColumns: [
                {sWidth: '85%'},
                {sWidth: '15%'},
            ],
            "pagingType": "input",
        });
        $(window).trigger('resize');
        $(this).find('input').val('');
        $('#' + $table + ' tbody').on('click', 'tr td:not(:last-child)', setSelected);
        $('.modal-search .dataTables_filter').find('input').remove();
        $('.modal-search .dataTables_filter').append('<input type="search" placeholder="" data-index="0">');
        $(table.table().container()).on('keyup', '.dataTables_filter input[data-index]', function () {
            table
                    .column(0)
                    .search(this.value)
                    .draw();
        });
    });

    // Datatable partner group
    $('input[type="search"]').css('pointer-events', 'auto');
    $('#list-partner-group').on("shown.bs.modal", function () {
        var url = $('.table-partner-group').data('url');
        $('.table-partner-group').dataTable({
            destroy: true,
            pagingType: "full_numbers",
            processing: false,
            serverSide: true,
            autoWidth: false,
            select: true,
            "bLengthChange": false,
            "oLanguage": dataLang,
            ajax: url,
            order: [[0, 'desc']],
            aoColumns: [
                {sWidth: '75%'},
                {
                    sWidth: '25%',
                    "bSortable": false
                },
            ],
            "pagingType": "input"
        });
    });

    // dataTable partner
    $('#modal-list-partner').on("shown.bs.modal", function () {
        var url = $('#table-partner').data('url');
        $('#table-partner').dataTable({
            destroy: true,
            pagingType: "full_numbers",
            processing: false,
            serverSide: true,
            autoWidth: false,
            select: true,
            "bLengthChange": false,
            "oLanguage": dataLang,
            ajax: url,
            order: [[0, 'desc']],
            columnDefs: [{
                    "targets": 'no-sort',
                    "orderable": false,
            }],
            aoColumns: [
                {sWidth: '20%'},
                {sWidth: '20%'},
                {sWidth: '15%'},
                {sWidth: '17%'},
                {sWidth: '15%'},
                {sWidth: '13%'},
            ],            
            "pagingType": "input"
        });
    });

    // Welfare fee more
    $(document).on('click', '.delete-row-btn', function () {
        $('#table_wel_fee_more .dataTables_empty').removeClass('hidden');
        $('#table_wel_fee_more thead, tbody').find('tr').removeClass('disableMouse');
        $('#wel_fee_more').find('#addRow').show();
        $('#row' + $(this).attr('rowid')).remove();
        $('#addRow').show();
    });

    changeTabs();
    // check tabs active 
    $(window).bind('hashchange', function () {
        changeTabs();
    });

    $('#event-team-id').select2({
        minimumResultsForSearch: Infinity
    });
    $('#partner_type_id').select2({
        minimumResultsForSearch: Infinity
    });
    $('#welfare_group_id, #wel_purpose_id').select2();
    
    $(".select2-selection__rendered").each(function () {
        var $this = $(this);
        $this.html($this.html().replace(/&nbsp;/g, ' '));
    });
    
    $(document).on('change', '#end_at_exec', function () {
        $('#start_at_exec').valid();
    });
    $(document).on('change', '#end_at_register', function () {
        $('#start_at_register').valid();
    });
    $(document).on('change', '#start_at_exec', function () {
        if ($('#end_at_register').val() != '') {
            $('#end_at_register').valid();
        }
    });
    $(document).on('change', '#start_at_register', function () {
        if ($('#end_at_register').val() != '') {
            $('#end_at_register').valid();
        }
    });
    $(document).on('click', '.reset-datatable', function() {
        
    });
    
    $(document).on('keyup', '#tab-employee-CMTND', function() {
        var dataCMTND = $(this).val(); 
        if (dataCMTND.length <= 13) {
            $('.error-CMTND-employee').addClass('hidden');
        } else {
            $('.error-CMTND-employee').removeClass('hidden');
        }        
    });
    $(document).on('keyup', '#tab-employee-phone', function() {
        var dataPhone = $(this).val(); 
        if (dataPhone.length <= 13) {
            $('.error-phone-employee').addClass('hidden');
        } else {
            $('.error-phone-employee').removeClass('hidden');
        }      
    });
    
   $(document).on("change", "input[name=is_card_id]",function(){
        var text = $('.label-card-id').html();  
        if ($('#is_card_id').is(":checked")) {
            var str = text + '<em>*</em>';
            $('.label-card-id').html(str);
            $('.input-relative_card_id').removeClass('hidden');
        }
        if ($('#not_card_id').is(":checked")) {
            var str = text.replace('<em>*</em>', '');
            $('.label-card-id').html(str);
            $('.input-relative_card_id').find('#card_id').val('');
            $('.input-relative_card_id').addClass('hidden');
            $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
        }
    });
    
    $(document).on('keyup', '.input-relative_card_id #card_id', function () {
        var cardId = $(this).val();
        if (cardId.trim().length == 0) {
            $('.input-relative_card_id').find('#custom-error-card-id').removeClass('hidden');
            $('.input-relative_card_id').find('#custom-error-card-id').show();
        } else {
            $('.input-relative_card_id').find('#custom-error-card-id').addClass('hidden');
        }
    });
});

function changeTabs()
{
    var hasTagUrl = window.location.hash;
    if (hasTagUrl == '#employeeAtt' || hasTagUrl == '#participants'
            || hasTagUrl == '#employee'
            ) {
        var url = $('#users-table-emp-att').data('list');
        $('#users-table-emp-att').dataTable({
            destroy: true,
            pagingType: "full_numbers",
            processing: false,
            serverSide: true,
            "bLengthChange": false,
            "oLanguage": dataLang,
            ajax: url,
            "pagingType": "input",
        });
    } else if (hasTagUrl == '#wel_fee_more') {
        var url = $('#table_wel_fee_more').data('list');
        var table = $('#table_wel_fee_more').DataTable({
            destroy: true,
            pagingType: "full_numbers",
            processing: false,
            serverSide: true,
            "bLengthChange": false,
            "oLanguage": dataLang,
            ajax: url,
            order: [[3, 'desc']],
            "pagingType": "input",            
        });
        $('#table_wel_fee_more_wrapper .dataTables_filter').find('input').remove();
        $('#table_wel_fee_more_wrapper .dataTables_filter').append('<input type="text" placeholder="" data-index="0">');
        $(table.table().container()).on('keyup', '.dataTables_filter input[data-index]', function () {
            table.search(htmlEntities(this.value)).draw();
        });
    }
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function enterKeyPress(e) {
    if (e.keyCode == 13)
    {
        $(this).parents().find('button').click();
        return false;
    }
    return true;
}

function setSelected() {
    var tableID = '#' + this.closest('table').getAttribute('id');
    if ($(this).parent().hasClass('selected')) {
        $(this).parent().removeClass('selected');
    }
    else {
        $(tableID + ' tr.selected').removeClass('selected');
        $(this).parent().addClass('selected');
        $(tableID + ' input').prop("checked", false);
    }
};
/*
 ** add item in popup
 */
function addOption(url, name, i) {
    var data = $('input[name="name' + i + '"]').val();
    $.ajax({
        method: "GET",
        url: url,
        data: {
            data: data
        },
        success: function (data) {
            var html = '<option selected="true" value="' + data.id + '">' + data.name + '</option>';
            $('select[name="' + name + '"]').append(html);
        }
    });
};
/*
 ** input number only on wel_fee_more[cost]
 */
function onKeyUp($this) {
    var selection = this.getSelection().toString();
    if (selection !== '') {
        return;
    }

    //  When the arrow keys are pressed, abort.
    if ($.inArray(this.event.keyCode, [38, 40, 37, 39]) !== -1) {
        return;
    }
    var input = $($this).val();
    var input = input.replace(/[\D\s\._\-]+/g, "");
    input = input ? parseFloat(input, 10) : 0;
    $($this).val(function () {
        return (input === 0) ? "" : input.toLocaleString("en-US");
    });
}

//Remove space star end string
function trimAll(sString) {
    while (sString.substring(0, 1) == ' ') {
        sString = sString.substring(1, sString.length);
    }
    while (sString.substring(sString.length - 1, sString.length) == ' ') {
        sString = sString.substring(0, sString.length - 1);
    }
    return sString;
}

function submitForm() {
    var subject = $('#welfare-subject').val();
    var content = trimAll(CKEDITOR.instances.content.getData().replace(/<[^>]*>/gi, ''));
    var countError = 0;

    if (subject.trim().length == 0) {
        $('#error-subject').removeClass('hidden');
        countError++;
    }

    if (content.trim().length == 0) {
        $('#error-content').removeClass('hidden');
        countError++;
    }

    if (countError > 0) {
        return false;
    } else {
        if (!$('#btn-send-mail').data('allow')) {
            $('#modal-warning-send-mail').modal('show');
            return false;
        }
        document.getElementById('content').value = CKEDITOR.instances.content.getData();
        $('.success-message-send-mail').find('.alert').hide();
        $('#modal-success-send-mail').modal('show');
    }
}

function confirmPreview()
{
    $('#modal-preview-confirm').modal('show');
}

// function checkCardId()
// {
//     var count = 0;
//     if ($('#is_card_id').is(":checked")) {
//         var cardId = $('.input-relative_card_id').find('#card_id').val();
//         if (cardId.trim().length == 0) {
//             $('.input-relative_card_id').find('#custom-error-card-id').removeClass('hidden');
//             $('.input-relative_card_id').find('#custom-error-card-id').show();
//             count++;
//         }
//     }
//     if (!$('#form-add-wel-empl-relatives').valid()) {
//         count++;
//     }
//     return count == 0;
// }

function updateFeeActualWithFeeMore(cost, update)
{
    var feeExtra = parseFloat($('#fee_extra').val().replace(/,/g, ""));
    var totalActual = parseFloat($('#fee_total_actual').val().replace(/,/g, ""));
    isNaN(feeExtra) ? feeExtra = 0 : feeExtra;
    isNaN(totalActual) ? totalActual = 0 : totalActual;
    var costUpdate = parseFloat(cost);
    
    var updatefeeExtra = 0;
    var updatetotalActual = 0;

    if (update) {
        updatefeeExtra = feeExtra + costUpdate;
        updatetotalActual = totalActual + costUpdate;
    } else {
        updatefeeExtra = feeExtra != 0 ? (feeExtra - costUpdate) : 0;
        updatetotalActual = totalActual != 0 ? (totalActual - costUpdate) : 0;
    }

    $('#fee_extra').val(updatefeeExtra.toLocaleString("en-US"));
    $('#fee_total_actual').val(updatetotalActual.toLocaleString("en-US"));
}
function formatValue(value) {
    if (typeof value == 'undefined') {
        value = 0;
    } else {
        value = parseFloat(value.replace(/,/g, ""));
        isNaN(value) ? value = 0 : value;
    }
    return value;
}

var rulesrelatives = {
    name: {
        required: true,
        rangelength: [1, 50]
    },
    relation_name_id: {
        required: true
    },
    gender: {
        required: true
    },
    phone: {
        rangelength: [1, 15],
        customphone: true
    },
    relative_employee_id: {
        required: true
    },
    relative_card_id: {
        required: true,
        number: true,
        rangelength: [1, 12]
    },
    'birthday': {
        required: true
    },
    'support_cost': {
        required: true
    }
};
var messagesrelatives = {
    name: {
        required: textRequired,
        rangelength: errLengthInput.length50,
    },
    'relation_name_id': {
        required: textRequired
    },
    'gender': {
        required: textRequired
    },
    'phone': {
        rangelength: errLengthInput.length15,
    },
    'relative_employee_id': {
        required: textRequired
    },
    'relative_card_id': {
        required: textRequired,
        'number': errValidNumber,
        rangelength: errLengthInput.length12,
    },
    'birthday': {
        required: textRequired
    },
    'support_cost': {
        required: textRequired
    }
};

var validator = $('#form-add-wel-empl-relatives').validate({
    rules: rulesrelatives,
    messages: messagesrelatives,
    errorPlacement: function(error, element) {
        if(element.hasClass('val-custom')) {
            error.insertAfter(element.parent().find('.val-message'));
        }
        else if(element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        }
        else {
            error.insertAfter(element);
        }
    },
});

var messages = {
    'event[name]': {
        required: textRequired,
        rangelength: errLengthInput.length255,
    },
    'event[start_at_plan]': {
        required: textRequired
    },
    'event[end_at_plan]': {
        required: textRequired
    },
    'event[wel_form_imp_id]': {
        required: textRequired
    },
    'event[start_at_exec]': {
        required: textRequired
    },
    'event[end_at_exec]': {
        required: textRequired
    },
    'name': {
        required: textRequired,
        rangelength: errLengthInput.length50,
    },
    'code': {
        required: textRequired,
        rangelength: errLengthInput.length255,
    },
    'partner_type_id': {
        required: textRequired,
        rangelength: errLengthInput.length255,
    },
    'email': {
        rangelength: errLengthInput.length255,
        email: errValidEmail
    },
    'rep_email': {
        rangelength: errLengthInput.length255,
        email: errValidEmail
    },
    'rep_card_id': {
        'number': errValidNumber,
    },
    'tax_code': {
        'number': errValidNumber,
    },
    'wel_partner[email]': {
        email: errValidEmail
    },
    'wel_partner[partner_id]': {
        required: textRequired,
    },
    'wel_partner[fee_return]': {
        'number': errValidNumber,
        rangelength: errLengthInput.length19,
    },
    'part_email' : {
        email: errValidEmail
    },
    'wel_partner[rep_email]': {
        email: errValidEmail
    },
    'fax': {
        'number': errValidNumber,
    },
    'rep_card_id_date': {
        date: errValidDate,
    },
    'wel_partner[rep_phone_company]': {
        rangelength: errLengthInput.length15,
    },
    'wel_partner[rep_phone]': {
        rangelength: errLengthInput.length15,
    },
    'phone': {
        rangelength: errLengthInput.length15,
    },
    'rep_phone_home': {
        rangelength: errLengthInput.length15,
    },
    'rep_phone': {
        rangelength: errLengthInput.length15,
    },
    'rep_phone_company': {
        rangelength: errLengthInput.length15,
    },
    'wel_fee[empl_offical_number]': {
        rangelength: errLengthInput.length5,
    },
    'event_team_id': {
        required: textRequired,
    },
    'event[start_at_exec]': {
        lessThan: errAfterEndAtExec,
    },
    'event[start_at_register]': {
        lessThan: errBeforeEndAtExec,
    },
    'event[end_at_register]': {
        lessThan: errBeforeEndAtExec,
        greaterThan: errAfterEndAtExec,
    },
};
var rules = {
    'event[name]': {
        required: true,
        rangelength: [1, 255]
    },
    'event[start_at_exec]': {
        required: true
    },
    'event[end_at_exec]': {
        required: true
    },
    'event[wel_form_imp_id]': {
        required: true
    },
    'name': {
        required: true,
        rangelength: [1, 50]
    },
    'code': {
        required: true,
        rangelength: [1, 255]
    },
    'partner_type_id': {
        required: true,
        rangelength: [1, 100]
    },
    'email': {
        customemail: true,
        rangelength: [1, 100]
    },
    'rep_email': {
        customemail: true,
        rangelength: [1, 100]
    },
    'rep_card_id': {
        number: true,
    },
    'tax_code': {
        number: true,
    },
    'wel_partner[email]': {
        customemail: true,
    },
    'part_email' : {
        customemail: true,
    },
    'wel_partner[fee_return]': {
        number: true,
        rangelength: [1, 19]
    },
    'wel_partner[rep_email]' : {
        customemail: true,
    },
    'wel_partner[rep_phone_company]' : {
        customphone: true,
        rangelength: [1, 15]
    },
    'wel_partner[rep_phone]': {
        customphone: true,
        rangelength: [1, 15]
    },
    'phone': {
        customphone: true,
        rangelength: [1, 15]
    },
    'fax': {
        number: true
    },
    'rep_phone_home': {
        customphone: true,
        rangelength: [1, 15]
    },
    'rep_phone': {
        customphone: true,
        rangelength: [1, 15]
    },
    'rep_phone_company': {
        customphone: true,
        rangelength: [1, 15]
    },
    'rep_card_id_date': {
        date: true
    },
    'wel_fee[empl_offical_number]': {
        rangelength: [1, 5]
    },
    'event_team_id': {
        required: true
    },
    'event[start_at_exec]': {
        lessThan: '#end_at_exec'
    },
    'event[start_at_register]': {
        lessThan: '#start_at_exec',
    },
    'event[end_at_register]': {
        lessThan: '#start_at_exec',
        greaterThan: '#start_at_register',
    },
};

$.validator.addMethod('customphone', function (value, element) {
    return this.optional(element) || /^(0|\+)[\d]{9,13}$/.test(value);
}, errValidPhone);
$.validator.addMethod('customemail', function (value, element) {
    return this.optional(element) || /^(?!.*\.{2})([a-zA-Z0-9])([a-zA-Z0-9_.+-])+([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(value);
}, errValidEmail);
jQuery.validator.addMethod("greaterThan", function(value, element, params) {
    if ($(params).val() == '' || value == '') {
        return true;
    }
    if (!/Invalid|NaN/.test(new Date(value))) {
        return new Date(value) > new Date($(params).val());
    }
    return isNaN(value) && isNaN($(params).val())
        || (Number(value) > Number($(params).val()));
}, 'Must be greater than {0}.');
jQuery.validator.addMethod("lessThan", function(value, element, params) {
    if ($(params).val() == '' || value == '') {
        return true;
    }
    if (!/Invalid|NaN/.test(new Date(value))) {
        return new Date(value) < new Date($(params).val());
    }
    return isNaN(value) && isNaN($(params).val())
        || (Number(value) < Number($(params).val()));
}, 'Must be less than {0}.');


$(document).ready(function () {
    $(".js-example-basic-multiple").select2();
    var formSkillValidate = {};

    formSkillValidate['form-event-info'] = $('#form-event-info').validate({
        rules: rules,
        messages: messages,
        lang: 'vi',
        errorPlacement: function(error, element) {
            if(element.hasClass('val-custom')) {
                error.insertAfter(element.parent().find('.val-message'));
            }
            else if (element.hasClass('val-custom-input')) {
                error.insertAfter(element.parent().parent().find('.val-message-input'));
            }
            else if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            }
            else {
                error.insertAfter(element);
            }
        },
    });

    $('.addPartner').validate({
        rules: rules,
        messages: messages,
        lang: 'vi',
        errorPlacement: function(error, element) {
            if(element.hasClass('val-custom')) {
                error.insertAfter(element.parent().find('.val-message'));
            }
            else if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            }
            else {
                error.insertAfter(element);
            }
        },
    });

    $('#form-send-mail, #form-upload-file').validate({
        rules: rules,
        messages: messages,
        lang: 'vi'
    });

    $('#addPartnerGroup').validate({
        rules: rules,
        messages: messages,
        lang: 'vi'
    });
    

    $(document).on('click', '.sendMailNotify', function () {
        var url = $(this).attr('data-url');
        bootbox.confirm({
            message: msgConfirmSendMail,
            className: 'modal-warning',
            buttons: {
                confirm: {
                    label: confirmYes,
                },
                cancel: {
                    label: confirmNo,
                },
            },
            callback: function (result) {
                if (result) {
                    $.ajax({
                        method: 'POST',
                        url: url,
                        data: {
                            id : $(this).attr('data-welId'),
                            _token: _token,
                        },
                        success: function (data) {
                            if (data.status === false) {
                                bootbox.alert({
                                    message: data.message,
                                    className: 'modal-warning',
                                    buttons: {
                                        ok: {
                                            label: confirmYes,
                                        },
                                    },
                                });
                            }
                            if (data.status === true) {
                                bootbox.alert({
                                    message: data.message,
                                    className: 'modal-success',
                                    buttons: {
                                        ok: {
                                            label: confirmYes,
                                        },
                                    },
                                });
                            }
                        },
                    });
                }
            },
        });
    });
});

