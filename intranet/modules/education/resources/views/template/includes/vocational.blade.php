<form method="post" action="{{route('education::education.settings.update-template')}}"
      enctype="multipart/form-data" autocomplete="off" id="form-create-setting-education" class="form-horizontal form-sales-module">
    {!! csrf_field() !!}
    <div>
        <textarea name="description" class="ckedittor-text" id="description">
            @if (isset($collection) && count($collection))
                {{ old('description', $collection->description) }}
            @endif
        </textarea>
        <input name="name" value="vocational"  type="hidden" />
        <input name="template" value="education::template-mail.education-vocational"  type="hidden" />
    </div>
    @if($errors->has('description'))
        <label id="description-error" class="error" for="description">{{$errors->first('description')}}</label>
    @endif
    <div class="style-button">
        <button class="btn-add" type="submit">
            Submit
        </button>
    </div>
</form>
