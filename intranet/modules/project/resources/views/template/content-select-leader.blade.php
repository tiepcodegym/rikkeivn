@if(isset($reset) || !count($leaders))
<input type="text" class="form-control input_leader_id" readonly="readonly" name="except[leader_id]" />
<input type="hidden" name="leader_id" class="input_leader_id_hidden" value="">
<?php return; ?>
@endif
@if(!isset($permissionEdit) || !$permissionEdit || (isset($checkEditWorkOrder) && !$checkEditWorkOrder))
    <p class="form-control-static{{( $leaderOld && $idLeader != $leaderOld->id) ? ' changed' : ''}}" data-id="{{$idLeader}}" data-toggle="tooltip" data-container="body"
       <?php if ($leaderOld):  ?>data-original-title="{{trans('project::view.Approved Value')}}: {{ $leaderOld->name }}<?php endif; ?>">{{ isset($leaders[$idLeader]['name']) ? $leaders[$idLeader]['name'] : '' }}</p>
    <?php return; ?>
@endif

@if(count($leaders) > 1 || $idLeader === null)
<span class="span_leader_id">
    <select id="leader_id" class="form-control select_leader_id input-basic-info{{ ( $leaderOld && $idLeader != $leaderOld->id && !in_array($leaderOld->id, $leaders)) ? ' changed' : '' }}"
        name="leader_id" 
        <?php if ($leaderOld):  ?>data-original-title="{{ trans('project::view.Approved Value') }}: {{ $leaderOld->name }}<?php endif; ?>">
        @if ($idLeader === null)
        <option value="">&nbsp;</option>
        @endif
        @foreach($leaders as $leader)
            <option value="{{ $leader['id'] }}"{{ $leader['id'] == $idLeader ? ' selected' : ''}} >{{ $leader['name'] }}</option>
        @endforeach
    </select>
</span>
@else (count($leaders))
    <p class="form-control-static" data-toggle="tooltip" data-container="body"
       <?php if ($leaderOld):  ?>data-original-title="{{trans('project::view.Approved Value')}}: {{ $leaderOld->name }}<?php endif; ?>">{{ $leaders[$idLeader]['name'] }}</p>
    <input type="hidden" name="leader_id" class="input_leader_id_hidden" value="{{ $idLeader }}">
@endif