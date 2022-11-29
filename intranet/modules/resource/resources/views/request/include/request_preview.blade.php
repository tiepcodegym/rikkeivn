<div class="modal fade modal-default" id="modal-preview" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{trans('resource::view.Request.Preview')}}</h4>
            </div>
            <div class="modal-body"  style="height:500px; overflow-y: auto;">
                <div class="job-detail">
                    <div class="header">
                        <div class="job_info">
                            <h3 class="job_title" id="job_title"></h3>
                        </div>
                    </div>
                    <div class="job_reason_to_join_us">
                        <table border="0">
                            <tr>
                                <td width="150px"><strong>Position:</strong></td>
                                <td id="position"></td>
                            </tr>
                            <tr>
                                <td width="150px"><strong>Location:</strong></td>
                                <td id="location"></td>
                            </tr>
                            <tr>
                                <td width="150px"><strong>Salary:</strong></td>
                                <td id="salary_modal"></td>
                            </tr>
                            <tr>
                                <td width="150px"><strong>Job expired:</strong></td>
                                <td id="job_expired"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="job_description">
                        <div>
                            <p><strong>Description</strong></p>
                            <ul class="job_des">
                            </ul>
                        </div>
                    </div>
                    <div class="skills_experience">
                        <div>
                            <p><strong>Job qualifications</strong></p>
                            <ul class="job_qualification"></ul>
                        </div>
                    </div>
                    <div class="job_benefits">
                        <div>
                            <p><strong>Benefits</strong></p>
                            <ul class="job_ben"></ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel btn-close pull-left" data-dismiss="modal">{{ Lang::get('resource::view.Close') }}</button>
                <button type="submit" class="btn btn-primary btn-success-preview">{{ Lang::get('resource::view.OK') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>