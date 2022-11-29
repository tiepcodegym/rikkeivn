<div class="add-items-item row form-group form-group-nmargin">
    <div class="col-md-7 form-group form-group-nmargin">
        <textarea name="quotation[old][{{ $slideQuotationsItem->id }}][content]" rows="2" 
            class="form-control text-resize-y quotation-input" 
            placeholder="{{ trans('slide_show::view.Content') }}">{{ $slideQuotationsItem->content }}</textarea>
    </div>
    <div class="col-md-4 form-group form-group-nmargin">
        <input name="quotation[old][{{ $slideQuotationsItem->id }}][author]" class="form-control quotation-input" 
            placeholder="{{ trans('slide_show::view.Author') }}" 
            value="{{ $slideQuotationsItem->author }}" />
    </div>
    <div class="col-md-1 form-group form-group-nmargin">
        <button type="button" class="btn-delete add-items-btn-delete{{ $countSlideQuotations <= 1 ? ' hidden' : '' }}">
            <i class="fa fa-minus"></i>
        </button>
    </div>
</div>
