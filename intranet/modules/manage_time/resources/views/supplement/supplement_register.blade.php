@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Supplement register') }} 
@endsection

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\Model\CoreConfigData;
    use Rikkei\Team\Model\Team;
    use Rikkei\ManageTime\Model\SupplementReasons;

    $annualHolidays = CoreConfigData::getAnnualHolidays(2);
    $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePreOfEmp);
    $urlSearchRelatedPerson = route('manage_time::profile.supplement.find-employee');
?>

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_managetime/css/jquery.fileuploader.css') }}" />
@endsection

@section('sidebar-common')
    @include('manage_time::include.sidebar_supplement')
@endsection

@section('content-common')
    <div class="se-pre-con"></div>
    <!-- Box register -->
    <div class="box box-primary" id="mission_register">
        <div class="box-header with-border">
            <h3 class="box-title managetime-box-title">{{ trans('manage_time::view.Supplement register') }}</h3>
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            @include('manage_time::supplement.include.form_supplement_register')
        </div>
        <!-- /.box-body -->
    </div>
    <!-- /. box -->
    <div class="box box-primary">
        <div class="box-body font-size-14">
            {!! trans('manage_time::view.Guide register supplement') !!}
        </div>
    </div>
@endsection

@section('script-common')
<script>
    var fileUploaderMessage = '{{ trans('manage_time::message.Upload proofs message') }}';
    var teamCode = '{{ $teamCodePreOfEmp }}';
    var codeJp = '{{ Team::CODE_PREFIX_JP }}';
    var isEmpJp = teamCode === codeJp;
    var compensationDays = <?php echo json_encode($compensationDays); ?>;
    var empProjects = <?php echo json_encode($empProjects); ?>;
    /**
     * Store working time setting of employee
     */
    var timeSetting = <?php echo json_encode($timeSetting); ?>;
    var currentEmpId = {{ $registrantInformation->id }};
    var token = '{{ csrf_token() }}';
    var urlGetTimeSetting = '{{ route("manage_time::profile.leave.get-time-setting") }}';
    var keyDateInit = '{{ $keyDateInit }}';
    var typeOther = {{ SupplementReasons::TYPE_OTHER }};
    var typeImageRequired = {{ SupplementReasons::IS_IMAGE_REQUIRED }};
    var textUpload = '{{ trans('manage_time::view.Upload proofs (image file of computer logs, images of meeting with clients...)') }}';

    // Variable check is working in Japan
    var isWorkingJP = false;
    @if ($reasons)
        isWorkingJP = true;
    @endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/additional-methods.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/common.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/register.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/jquery.fileuploader.js') }}"></script>

<script type="text/javascript">
    var urlSearchRelatedPerson = '{{ $urlSearchRelatedPerson }}';
    var startDateDefault = new Date();
    startDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['morningInSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['morningInSetting']['minute']);
    var endDateDefault = new Date();
    endDateDefault.setHours(timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['hour'], timeSetting[currentEmpId][keyDateInit]['afternoonOutSetting']['minute']);
    var annualHolidays = '{{ implode(', ', $annualHolidays) }}';
    var arrAnnualHolidays = annualHolidays.split(', ');
    var specialHolidays = '{{ implode(', ', $specialHolidays) }}';
    var arrSpecialHolidays = specialHolidays.split(', ');
    var pageType = "{{ $pageType }}";
    
</script>
@endsection
