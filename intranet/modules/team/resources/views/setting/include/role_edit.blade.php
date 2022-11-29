<?php
use Rikkei\Core\View\Form;

if (Form::getData('role.id')) {
    $suffixId = '-edit';
} else {
    $suffixId = '';
}
?>
<div class="modal-body">
    <div class="form-group">
        <label for="position-name{{ $suffixId }}" class="form-label required">{{ trans('team::view.Name') }} <em>*</em></label>
        <div class="form-data">
        <input type="text" class="form-control" id="position-name{{ $suffixId }}" name="role[role]" 
            value="{{ Form::getData('role.role') }}" required />
        </div>
    </div>
    <div class="margin-bottom-15 clearfix"></div>
    <div class="form-group">
        <label class="form-label required">{{ trans('team::view.Description') }}</label>
        <div class="form-data">
            <textarea class="form-control" name="role[description]" rows="3">{{ Form::getData('role.description') }}</textarea>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<div class="modal-footer">
    <button type="submit" class="btn-add btn-large">{{ trans('team::view.Save') }}</button>
</div>

<?php
//if is page edit team, remove data of add team
if(Form::getData('role.id')) {
    Form::forget('role');
}


