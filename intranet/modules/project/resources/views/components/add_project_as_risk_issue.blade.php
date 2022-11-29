
<div class="modal fade" id="modal_teams" tabindex="-1" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <form class="modal-content" >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('project::view.Select team and project') }}</h4>
                <div class="teams-container">
                    <section class="box box-info team-container" data-has="1">
                        <div class="box-body">
                            <div class="row margin-bottom-20">
                                <div class="col-md-12">
                                    <span>
                                        <select class="form-control" id="select_team_leader">
                                            <option value="0">{{ trans('resource::view.Request.Create.Select team') }}</option>
                                            @foreach($teamsOptionAll as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="box-body" id="select_team_and_project"></div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancel_add_risk" class="btn btn-primary pull-left" data-dismiss="modal">{{ Lang::get('resource::view.Cancel & close') }}</button>
                <tr class="tr-add-risk">
                    <td colspan="9" class="slove-risk">
                        <button href="#" class="btn-add add-risk"><i class="fa fa-plus"></i></button>
                    </td>
                </tr>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
