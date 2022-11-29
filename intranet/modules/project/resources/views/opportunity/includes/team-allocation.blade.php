<div data-flag-dom="wo-team-allocate">
    <div class="table-content-team-allocation">
        <div id="ta-vis" class="vis-member"></div>
        <div class="margin-top-10 row">
            <div class="col-md-3">
                <button type="button" class="btn-add" data-btn-action="woAddProjMember">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
            <div class="col-md-9">
                <div class="pull-right">
                    <table class="table table-no-border">
                        <tr>
                            <td style="padding-right: 25px;">
                                {!!trans('project::view.Total actual effort')!!}
                                (<span data-dom-flag="type-resource"></span>)
                            </td>
                            <td><span data-dom-effort="approved"></span></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="col-md-12">
                <p><strong>{{ trans('project::view.Note:') }}</strong></p>
                <p>{!! trans('project::view.note_team_allocation') !!}</p>
            </div>
        </div>
    </div>
</div>