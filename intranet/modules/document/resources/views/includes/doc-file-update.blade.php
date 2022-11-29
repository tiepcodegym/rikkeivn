<?php 
$magazineImages = $item->images()->orderBy('order', 'asc')->get();
?>
<div id="error_box_up" class="hidden" style="margin-bottom: 20px; margin-top: -20px"></div>
{!! Form::open(['method' => 'post', 'route' => ['magazine::update', $item->id], 'files' => true, 'id' => 'create_magazine', 'class' => 'imageloaderForm']) !!}
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group row">
                <label class="col-sm-3">{{ trans('doc::view.File name') }} <em class="text-red">*</em></label>
                <div class="col-sm-9">
                    {!! Form::text('name', $item->name, ['id' => 'name_magazine', 'name' => 'name_magazine', 'class' => 'form-control', 'placeholder' => trans('magazine::view.Magazine name')]) !!}
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <label class="fileUpload btn btn-primary">
                {{ trans('doc::view.Add image') }} <i class="hidden uploading fa fa-spin fa-refresh"></i>
                <input name="image[]" id="fileUpload" class="upload" type="file" accept="image/*" multiple/>
            </label>
            <span><i>{{ trans('magazine::view.Drag image to range order, check image to choose background') }}</i></span>
            <br />
            {{ trans('magazine::view.Recommend size') }}
        </div>
    </div>
    <div id="uploadPreview">
        @if(isset($magazineImages))
            @if (!$magazineImages->isEmpty())
                @foreach($magazineImages as $image)
                    @include('magazine::template.image-item', ['image' => $image])
                @endforeach
            @endif
        @endif
    </div>
    <input type="hidden" name="selected" id="selected" value="">
    <div class="form-group text-center">
        <br />
        <p class="submit-alert hidden"><i class="fa fa-spin fa-refresh"></i> {{ trans('magazine::message.Processing image, please wait') }}</p>
        <label><em class="required">*</em> {{ trans('doc::view.Edit document and click save') }}</label>
        </br>
        <button id="submit_magazine" class="btn-edit" type="submit"><i class="fa fa-save"></i> {{ trans('doc::view.Update') }}</button>
    </div>
{!! Form::close() !!}
@include('magazine::template.script')
<script type="text/javascript">
    $('#error_box_up').removeClass('hidden').html('<div class="flash-message"><div class="alert alert-success"><ul><li>'+ '{{ trans('magazine::message.Create successful') }}' +'</li></ul></div></div>');
    setTimeout(function() {$('#error_box_up').addClass('hidden');}, 4000);
    $("#create_magazine").submit(function () {
            var el_this = $(this);
            $('#error_box').addClass('hidden').html('');
            var btn = el_this.find('button[type="submit"]');

            var formData = new FormData();
            formData.append('_token', el_this.find('input[name="_token"]').val());
            formData.append('name', $('#name_magazine').val());
            formData.append('select_index', select_index);
            formData.append('document', 'document');

            var size = 0;
            $('#uploadPreview .imgPreviewWrap').each(function (e_index) {
                if (typeof $(this).attr('data-index') != "undefined") {
                    var item_index = $(this).attr('data-index');
                    var file = tempFiles[item_index];
                    if (file) {
                        if (typeof file == 'object') {
                            // check each file size < 10MB
                            if (file.size > MAX_FILE_SIZE * 1024 * 1024) {
                                showModalError(msg['validFileSize'] + ' ('+ file.name +' - ' + Math.floor(file.size / 1024) + 'KB)');
                                btn.prop('disabled', false);
                                return false;
                            }
                            size += file.size;
                            formData.append('images['+ e_index +']', file);
                        } else {
                            formData.append('image_ids['+ e_index +']', file);
                        }
                    }
                }
            });
            //check total file size < server allow post file size
            if (size > MAX_SIZE * 1024 * 1024) {
                showModalError(msg['fileMaxSize']);
                btn.prop('disabled', false);
                return false;
            }
            // begin upload file
            $('#fileUpload').prop('disabled', true);
            // show loading
            $('.submit-alert').removeClass('hidden');

            $.ajax({
                type: 'POST',
                url: el_this.attr('action'),
                dataType: 'json',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.err) {
                        $('#error_box_up').removeClass('hidden').html(data.err);
                    } else {
                        $('#error_box_up').removeClass('hidden').html('<div class="flash-message"><div class="alert alert-success"><ul><li>'+ data.message +'</li></ul></div></div>');
                        setTimeout(function() {$('#error_box_up').addClass('hidden');}, 4000);
                    }
                },
                error: function (err) {
                    if (err.status == 422) {
                        $('#error_box_up').removeClass('hidden').html(err.responseJSON);
                    } else {
                        showModalError(err.responseJSON);
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                    $('.submit-alert').addClass('hidden');
                    $('#fileUpload').prop('disabled', false);
                }
            });
            
            return false;
        });
    </script>
</script>
