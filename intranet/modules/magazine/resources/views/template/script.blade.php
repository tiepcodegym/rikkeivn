<div class="imgPreviewWrap hidden" data-id="" id="preview_item">
    <img src="" alt="No image">
    <div class="actions">
        <button type="button" class="action-delete" title="{{ trans('magazine::view.Delete image') }}"><span>x</span></button>
    </div>
    <input type="hidden" class="image_val" value="">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script>
    var _token = "{{ csrf_token() }}";
    var uploadImageUrl = "{{ route('magazine::upload_image') }}";
    var listMagazineUrl = "{{ route('magazine::manage') }}";
    var MAX_SIZE = "{{ substr(ini_get('post_max_size'), 0, -1) }}";
    
    var magazine_id = null;
    <?php if (isset($item)) { ?>
        magazine_id = '{{ $item->id }}';
    <?php } ?>
    
    var msg = new Array();
    msg["deleteImage"] = "{{ trans('magazine::view.Delete image') }}";
    msg["errorUploadImage"] = "{{ trans('magazine::view.There was an error upload Image') }}";
    msg["errorDelete"] = "{{ trans('magazine::view.Error on delete. Please try again.') }}";
    msg["nameRequired"] = "{{ trans('magazine::message.The name field is required') }}";
    msg["imageRequired"] = "{{ trans('magazine::message.The Image upload is required') }}";
    msg["fileMaxSize"] = "{{ trans('magazine::message.Server allow file max size', ['max' => ini_get('post_max_size')]) }}";
    msg["validFileSize"] = "{{ trans('magazine::message.The file may not be greater than max kilobytes', ['max' => 5120]) }}";
    msg["errorOccurred"] = "{{ trans('magazine::message.Error occurred') }}";
</script>
<script src="{{ asset('magazine/js/create.js') }}"></script>
