<script>
    var rcParams = {
        chartTitle: "<?php echo trans('resource::view.Human resource chart') ?>",
        actualTitle: "<?php echo trans('resource::view.Actual') ?>",
        planTitle: "<?php echo trans('resource::view.Plan') ?>",
        monthTitle: "<?php echo trans('resource::view.Month') ?>",
        amountTitle: "<?php echo trans('resource::view.Amount') ?>",
        errorMessNumberGreaterMonth: "<?php echo trans('resource::message.Number this month mus greater than previous month') ?>",
        errorMessNumberLesserMonth: "<?php echo trans('resource::message.Number this month mus lesser than next month') ?>",
    };
    @if (isset($charActualsMonth))
        var actualsMonth = '{{ json_encode($charActualsMonth) }}';
        rcParams.actualsMonth = $.map(JSON.parse(actualsMonth.replace(/&quot;/g, '"')), function (el) {
            return el;
        });
    @endif
    @if (isset($plansMonth))
        var plansMonth = '{{ json_encode($plansMonth) }}';
        rcParams.plansMonth = $.map(JSON.parse(plansMonth.replace(/&quot;/g, '"')), function (el) {
            return el;
        });
    @endif
</script>
