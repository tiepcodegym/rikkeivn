@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;

$roles = getOptions::getInstance()->getRoles();
$devTypes = getOptions::getInstance()->getDevTypeOptions();
$generalStatuses = getOptions::getInstance()->cddGeneralStatuses();
?>
@section('title', trans('recruitment::view.Email marketing'))

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">
@endsection

@section('content')
<div id="email_marketing_container"></div>

<div class="hidden">
    <input type="hidden" id="email_subject_val" value="{{ $emailSubject }}" />
    <textarea id="email_content_val">{!! $emailContent !!}</textarea>
</div>

<div class="modal fade" id="modal_perview_mail" tabindex="0" role="dialog">
    <div class="modal-dialog modal-lg modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('recruitment::view.Preview') }}</h4>
            </div>
            <div class="modal-body" style="height: 80vh">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('core::view.Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    var textTrans = {
        'Email marketing': "{!! trans('recruitment::view.Email marketing') !!}",
        'Request': "{!! trans('resource::view.Request') !!}",
        'Detail': "{!! trans('resource::view.Detail') !!}",
        'Position': "{!! trans('resource::view.Position') !!}",
        'Type': "{!! trans('resource::view.Type') !!}",
        'Offer fail': "{!! trans('resource::view.Offer fail') !!}",
        'Interview fail': "{!! trans('resource::view.Interview fail') !!}",
        'Test fail': "{!! trans('resource::view.Test fail') !!}",
        'Contact fail': "{!! trans('resource::view.Contact fail') !!}",
        'Fail': "{!! trans('resource::view.Fail') !!}",
        'Contacting': "{!! trans('resource::view.Contacting') !!}",
        'Programing language': "{!! trans('resource::view.Programing language') !!}",
        'Search': "{!! trans('core::view.Search') !!}",
        'List not send email marketing yet': "{!! trans('recruitment::view.List not send email marketing yet') !!}",
        'List sent email marketing': "{!! trans('recruitment::view.List sent email marketing') !!}",
        'Not found item': "{!! trans('core::view.Not found item') !!}",
        'Please click search button': "{!! trans('recruitment::view.Please click search button') !!}",
        'Total': "{!! trans('core::view.Total') !!}",
        'page': "{!! trans('core::view.page') !!}",
        'Show': "{!! trans('core::view.Show') !!}",
        'entity': "{!! trans('core::view.entity') !!}",
        'Reset filter': "{!! trans('core::view.Reset filter') !!}",
        'Send': "{!! trans('recruitment::view.Send') !!}",
        'Send email': "{!! trans('recruitment::view.Send email') !!}",
        'Send to :number emails': "{!! trans('recruitment::view.Send to :number email') !!}",
        'Send only checked items': "{!! trans('recruitment::view.Send only checked items') !!}",
        'Send all items in current filter': "{!! trans('recruitment::view.Send all items in current filter') !!}",
        'Email subject': "{!! trans('recruitment::view.Email subject') !!}",
        'Email content': "{!! trans('recruitment::view.Email content') !!}",
        'item(s)': "{!! trans('recruitment::view.item(s)') !!}",
        'Add file': "{!! trans('recruitment::view.Add file') !!}",
        'None item checked': "{!! trans('recruitment::message.None item checked') !!}",
        'Are you sure want to sending email?': "{!! trans('recruitment::message.Are you sure want to sending email?') !!}",
        'Preview': "{!! trans('recruitment::view.Preview') !!}",
        'This field is required': "{!! trans('core::message.This field is required') !!}",
        'Please choose resource request': "{!! trans('recruitment::message.Please choose resource request') !!}",
        'error_file_max_size': "{!! trans('recruitment::message.error_file_max_size') !!}",
        'Email template': "{!! trans('recruitment::view.Email template') !!}",
        'Reset checked': "{!! trans('recruitment::view.Reset checked') !!}",
        'Note the selected items before change filter': "{!! trans('recruitment::view.Note the selected items before change filter') !!}",
        'Only tab not send email yet': "{!! trans('recruitment::view.Only tab not send email yet') !!}",
        'View detail': "{!! trans('recruitment::view.View detail') !!}",
        'click to view': "{!! trans('recruitment::view.click to view') !!}",
    };

    <?php
    $searchRqParams = [
        'status' => getOptions::STATUS_INPROGRESS,
        'approve' => getOptions::APPROVE_ON,
        'published' => Rikkei\Resource\Model\ResourceRequest::PUBLISHED,
        'not_enough_amount' => 1,
    ];
    ?>
    var pageParams = {
        _token: '{{ csrf_token() }}',
        urlGetCandidates: "{{ route('recruitment::email.candidate.list') }}",
        positions: JSON.parse('{!! json_encode($roles, JSON_HEX_TAG) !!}'),
        devTypes: JSON.parse('{!! json_encode($devTypes, JSON_HEX_TAG) !!}'),
        programingLanguages: JSON.parse('{!! json_encode($programingLanguages, JSON_HEX_TAG) !!}'),
        cddResultFail: '{{ getOptions::RESULT_FAIL }}',
        statusFail: '{{ getOptions::FAIL }}',
        generalStatuses: JSON.parse('{!! json_encode($generalStatuses) !!}'),
        stepFailStatuses: JSON.parse('{!! json_encode(getOptions::getInstance()->cddFailStepStatuses()) !!}'),
        allStatuses: JSON.parse('{!! json_encode(getOptions::getInstance()->getCandidateStatusOptionsAll()) !!}'),
        pagerOptionsLimit: JSON.parse('{!! json_encode(Config::toOptionLimit()) !!}'),
        urlSearchRequest: "{!! route('resource::request.list.search.ajax', $searchRqParams) !!}",
        urlSendMail: "{{ route('recruitment::email.send') }}",
        urlSaveConfigMail: "{{ route('recruitment::email.save_mail') }}",
        urlPreviewMail: "{{ route('recruitment::email.preview') }}",
        MAX_FILE_SIZE: parseInt('{{ \Rikkei\Core\View\CoreFile::getInstance()->getMaxFileSize() }}'),
        templates: JSON.parse('{!! json_encode($templates) !!}'),
        urlRequestDetail: "{{ route('resource::request.detail', ['id' => null]) }}",
        urlCandidateDetail: "{{ route('resource::candidate.detail', ['id' => null]) }}",
        interestedOptions: JSON.parse('{!! json_encode(getOptions::listInterestedOptions()) !!}'),
        urlLoadTemplateContent: "{{ route('recruitment::email.template.get_content') }}",
        urlGetRequestFilter: "{{ route('recruitment::email.get_request_filter') }}",
        urlViewCddWillSend: "{{ route('recruitment::email.view_cdd_will_send') }}",
    };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ CoreUrl::asset('recruitment/js/email-marketing.js') }}"></script>
<script>
    if (typeof Storage != 'undefined') {
        if (typeof Storage != 'undefined') {
            var key = 'email_marketing_filterData';
            var pageFilterData = sessionStorage.getItem(key);
            if (pageFilterData) {
                pageFilterData = JSON.parse(pageFilterData);
                var tab = 'not_send';
                if (typeof pageFilterData[tab].filter.except == 'undefined') {
                    pageFilterData[tab].filter.except = {};
                }
                if (typeof pageFilterData[tab].filter.except.request_id != 'undefined') {
                    setTimeout(function () {
                        $('#init_search_data').click();
                    }, 300);
                }
            }
        }
    }
</script>
@endsection
