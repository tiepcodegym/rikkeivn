<?php
use Rikkei\Core\View\Form;

if (Form::getData('position.id')) {
    $suffixId = '-edit';
} else {
    $suffixId = '';
}
?>
<div class="modal-body">
    <div class="form-group">
        <label for="position-name{{ $suffixId }}" class="form-label required">{{ trans('team::view.Position name') }} <em>*</em></label>
        <div class="form-data">
        <input type="text" class="form-control" id="position-name{{ $suffixId }}" name="position[role]" 
            value="{{ Form::getData('position.role') }}" required />
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="modal-footer">
    <button type="submit" class="btn-add btn-large">{{ trans('team::view.Save') }}</button>
</div>

<?php
//if is page edit team, remove data of add team
if(Form::getData('position.id')) {
    Form::forget('position');
}

