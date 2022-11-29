<?php
use Rikkei\Core\View\Form;

if (Form::getData('id')) {
    $suffixId = '-edit';
} else {
    $suffixId = '';
}
?>
<div class="modal-body">
    <div class="form-group">
        <label for="team-name{{ $suffixId }}" class="form-label required">{{ trans('team::view.Team name') }} <em>*</em></label>
        <p class="form-data">
        <input type="text" class="form-control" id="team-name{{ $suffixId }}" name="item[name]" 
            value="{{ Form::getData('name') }}" required />
        </p>
    </div>

    <div class="form-group">
        <label class="form-label required">{{ trans('team::view.Mail group') }}</label>
        <p class="form-data">
            <input type="text" class="form-control" name="item[mail_group]"
                   value="{{ Form::getData('mail_group') }}">
        </p>
    </div>
    <div class="form-group">
        <label class="form-label required">{{ trans('team::view.Branch code') }}</label>
        <p class="form-data">
            <input type="text" class="form-control" name="item[branch_code]"
                   value="{{ Form::getData('branch_code') }}" />
        </p>
    </div>

    <div class="form-group">
        <label class="form-label required">{{ trans('team::view.Team code') }} <em>*</em></label>
        <p class="form-data">
        <input type="text" class="form-control" name="item[code]" 
            value="{{ Form::getData('code') }}" required />
        </p>
    </div>
    <div class="form-group">
        <label for="is_branch{{ $suffixId }}" class="form-label">{{ trans('team::view.Is branch') }}</label>
        <input type="checkbox" name="item[is_branch]" id="is_branch{{ $suffixId }}"
               value="1"<?php if (Form::getData('is_branch') == 1): ?> checked<?php endif; ?> />
    </div>
    <div class="form-group">
        <label class="form-label">{{ trans('team::view.Functional unit') }}</label>
        <div class="clearfix"></div>
        <div class="form-group-sub">
            <div class="form-label">
                <input type="checkbox" name="item[is_function]" id="is-function{{ $suffixId }}" class="input-is-function" data-id="group-{{ Form::getData('id') }}"
                    value="1"<?php if (Form::getData('is_function') == 1): ?> checked<?php endif; ?> />
                <label for="is-function{{ $suffixId }}">{{ trans('team::view.Is function unit') }}</label>
            </div>
            <div class="form-data team-group-function" data-id="group-{{ Form::getData('id') }}">
                <p>
                    <input type="radio" name="permission_same" id="permission-type-new{{ $suffixId }}" value="0"<?php if (!isset($permissionAs) || ! $permissionAs): ?> checked<?php endif; ?> />
                    <label for="permission-type-new{{ $suffixId }}">{{ trans('team::view.New') }}</label>
                </p>
                <div class="row">
                    <p class="col-md-6">
                        <input type="radio" name="permission_same" id="permission-type-same{{ $suffixId }}" value="1"<?php if (isset($permissionAs) && $permissionAs): ?> checked<?php endif; ?> />
                        <label for="permission-type-same{{ $suffixId }}">{{ trans('team::view.Permission following function unit') }}</label>&nbsp;&nbsp;&nbsp;
                    </p>
                    <p class="col-md-6">
                        <select class="input-select select-search" name="item[follow_team_id]">
                            @foreach(Rikkei\Team\View\TeamList::toOption(Form::getData('id'), true) as $option)
                            <option value="{{ $option['value'] }}"
                                <?php if (Form::getData('follow_team_id') == $option['value']): ?> selected<?php endif; ?>
                                    {{ $option['option'] }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </p>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="team-parent{{ $suffixId }}" class="form-label">{{ trans('team::view.Team parent') }}</label>
        <select class="input-select form-data select-search" name="item[parent_id]" id="team-parent{{ $suffixId }}">
            @foreach(Rikkei\Team\View\TeamList::toOption(Form::getData('id')) as $option)
            <option value="{{ $option['value'] }}"<?php if (Form::getData('parent_id') == $option['value']): ?> selected<?php endif; ?>>{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="is_soft_dev{{ $suffixId }}" class="form-label">{{ trans('team::view.Is software development') }}</label>
        <input type="checkbox" name="item[is_soft_dev]" id="is_soft_dev{{ $suffixId }}" 
                    value="1"<?php if (Form::getData('is_soft_dev') == 1): ?> checked<?php endif; ?> />
    </div>
    <div class="clearfix"></div>
</div>
<div class="modal-footer">
    <button type="submit" class="btn-add btn-large">{{ trans('team::view.Save') }}</button>
</div>

<?php
//if is page edit team, remove data of add team
if(Form::getData('id')) {
    Form::forget();
}
