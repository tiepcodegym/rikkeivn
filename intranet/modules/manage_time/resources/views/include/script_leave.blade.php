<script>
    $(document).ready(function () {
        $('#number_days_leave').click(function() {
            $('#modal_leave_days').modal('show');
        });
        $(".group-email").select2({
            tags: true,
            placeholder: "<?php echo trans('manage_time::view.Select related email group') ?>",
        });

        if($('#reason option:selected').data('calculate-full-day')){
            $('#datetimepicker-start-date').data('DateTimePicker').disabledDates(null);
            $('#datetimepicker-end-date').data('DateTimePicker').disabledDates(null);
        }

        $('#reason').on('select2:select', function (evt) {
            var calculate_full_day = $('#reason').find(":selected").data("calculate-full-day");
            if (calculate_full_day != '0') {
                CalculateFullDay = true;
                $('#datetimepicker-start-date').data('DateTimePicker').disabledDates(null);
                $('#datetimepicker-end-date').data('DateTimePicker').disabledDates(null);
                $('#calculate-full-day').val(1);
            } else {
                CalculateFullDay = false;
                $('#datetimepicker-start-date').data('DateTimePicker').disabledDates(disabledDates);
                $('#datetimepicker-end-date').data('DateTimePicker').disabledDates(disabledDates);
                $('#calculate-full-day').val(0);
            }
        })
    })
</script>