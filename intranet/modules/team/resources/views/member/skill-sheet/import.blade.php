<div class="row">
    <div class="col-md-12 form-inline">
        <p>{!!trans('team::view.cv import help')!!}</p>
        <div class="form-group">
            <input type="file" name="cv" value="" class="form-control" d-ss-input="file-cv" />
            <button type="button" class="btn btn-success" d-ss-dom="import"
                action="{!!route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'import'])!!}">
                <i class="fa fa-upload loading-hidden-submit" aria-hidden="true"></i>
                <i class="fa fa-spin fa-refresh hidden loading-submit"></i>
                {!!trans('team::view.Upload')!!}
            </button>
        </div>
        <p class="error hidden" data-ss-error="file-cv"></p>
    </div>
</div>
