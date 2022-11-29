<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
?>
<div class="modal fade" id="modal-number-resource-info" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content"  >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Number resource information') }}</h4>
                <section class="box box-info" data-has="1">
                    <div class="box-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{trans('resource::view.Team')}}</th>
                                    <th>{{trans('resource::view.Position apply')}}</th>
                                    <th>{{trans('resource::view.Count plan')}}</th>
                                    <th>{{trans('resource::view.Count actual')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teams as $team)
                                <?php $positions = explode(';', $team->col); ?>
                                <tr>
                                    <td rowspan="{{count($positions)}}">{{$team->team_name}}</td>
                                    @foreach ($positions as $position)
                                    <?php $positionInfo = explode(',', $position); ?>
                                    <td>{{getOptions::getInstance()->getRole($positionInfo[0])}}</td>
                                    <td>{{$positionInfo[1]}}</td>
                                    <?php 
                                        $countPass =  Candidate::countCandidatePass($request->id, $positionInfo[0], $team->team_id);
                                        if ((int)$countPass > (int)$positionInfo[1]) {
                                            $bg = 'bg-navy';
                                        } else if ((int)$countPass == (int)$positionInfo[1]) {
                                            $bg = 'bg-green';
                                        } else {
                                            $bg = '';
                                        }
                                    ?>
                                    <td class="{{ $bg }}">{{$countPass}}</td>
                                </tr>
                                <tr>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ Lang::get('resource::view.Close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
