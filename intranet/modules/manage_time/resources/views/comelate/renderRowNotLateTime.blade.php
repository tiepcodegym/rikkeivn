<tr data-row-id="new">
    <td class="text-center stt"></td>
    <td>
        <div class="text txt-emp_email hidden"></div>
        <div class="input email "></div>
    </td>
    <td>
        <div class="text txt-emp_name hidden"></div>
        <div class="input name">
            <select name="empid" class="form-control select-search-employee f-input" required="required"
                data-remote-url="{{ URL::route('manage_time::profile.comelate.ajax-search-employee') }}">
            </select>
            <div class="error hidden empid-error"></div>
        </div>
    </td>
    <td>
        <div class="text txt-start_date hidden"></div>
        <div class="input start">
            <div class="input-group date datepicker" data-provide="datepicker">
                <input type="text" name="startDate" class="form-control f-input" required="required" autocomplete="off">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
            <div class="error hidden startDate-error"></div>
        </div>
    </td>
    <td>
        <div class="text txt-end_date hidden"></div>
        <div class="input end">
            <div class="input-group date datepicker" data-provide="datepicker">
                <input type="text" name="endDate" class="form-control f-input " required="required" autocomplete="off">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </div>
            </div>
            <div class="error hidden endDate-error"></div>
        </div>
    </td>
    <td>
        <div class="text txt-minute hidden"></div>
        <div class="input minute">
            <input type="number" min="1" name="minute" max="119" class="form-control f-input" required="required" value="10">
            <div class="error hidden minute-error"></div>
        </div>
    </td>
    <td>
        <div class="text hidden">
            <button class="btn btn-primary btn-ss-action" data-btn-action="edit" type="button"><i class="fa fa-pencil"></i></button>
            <button class="btn btn-danger btn-ss-action" data-btn-action="delete" type="button"
                data-url="{{ route('manage_time::admin.staff-late.delete-not-late-time') }}"
            ><i class="fa fa-trash"></i></button>
        </div>
        <div class="input">
            <button class="btn btn-success btn-ss-action"
                data-btn-action="create" type="button"
                data-url="{{ route('manage_time::admin.staff-late.create-not-late-time') }}"
            ><i class="fa fa-floppy-o"><i class="fa fa-refresh fa-spin margin-left-10 hidden"hiddenaria-hidden="true"></i></i></button>
        </div>
    </td>
</tr>