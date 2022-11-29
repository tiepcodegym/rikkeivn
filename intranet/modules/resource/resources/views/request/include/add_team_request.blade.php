
<div class="modal fade" id="modal-teams" tabindex="-1" role="dialog"  data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" >
            <div class="modal-body bg-wrapper">
                <h4 class="modal-title">{{ trans('resource::view.Teams of request') }}</h4>
                <div class="teams-container">
                    @if ($checkEdit)
                        @foreach($teams as $team)
                            <?php 
                                $positions = explode(';', $team->col); 
                                $posSelected = explode(',', $team->pos_selected); 
                                $teamSelected = explode(',', $team->team_selected);
                            ?>
                            <section class="box box-info team-container" data-has="1">
                                <div class="box-body">
                                    <div class="row margin-bottom-20">
                                        <div class="col-md-12">
                                            <span>                                  
                                                <select class="form-control select-search width-100-per team" onchange="changeTeam(this);">
                                                    <option value="0">{{ trans('resource::view.Request.Create.Select team') }}</option>
                                                    @foreach($teamsOptionAll as $option)
                                                    <option value="{{ $option['value'] }}" 
                                                        <?php if (in_array($option['value'], $teamSelected) && $option['value'] != $team->team_id) echo 'disabled'; ?>
                                                        <?php if ($option['value'] == $team->team_id) echo 'selected'; ?>
                                                    >{{ $option['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </span>
                                        </div>
                                    </div>
                                    @foreach ($positions as $position)
                                        <?php 
                                            $temp = explode(',', $position) ;
                                            $positionId = $temp[0];
                                            $number = $temp[1];
                                        ?>
                                        <div class="row margin-bottom-20 box-position" data-has="1">
                                            <div class="col-md-6">
                                                <span>                                  
                                                    <select class="form-control select-search has-search width-93 position-apply" onchange="changePosition(this);" >
                                                        <option value="0">{{ trans('resource::view.Select position') }}</option>
                                                        @foreach($roles as $key => $option)
                                                            <option value="{{ $key }}" 
                                                                 <?php if ($key == $positionId) echo 'selected'; ?>   
                                                                 <?php if (in_array($key, $posSelected) && $key != $positionId) echo 'disabled'; ?>    
                                                            >{{ $option }}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                            </div>    
                                            <div class="col-md-5">
                                                <span>                                  
                                                    <input type="number" class="form-control num number-resource" min="1" value="{{$number}}" onkeyup="numberInput(this);" placeholder="{{trans('resource::view.Number resource')}}" />
                                                </span>
                                            </div>
                                            <div class="col-md-1">
                                                <span href="#" class="btn-delete btn-delete-row float-right" row="2" onclick="removePosition(this);"><i class="fa fa-trash"></i></span>
                                            </div>
                                        </div>
                                    @endforeach
                                    <!-- BUTTON ADD POSITION -->
                                    <span href="#" class="btn btn-info btn-add-position hidden" onclick="addBoxPosition(this);"><i class="fa fa-plus"></i>&nbsp;{{trans('resource::view.Add position')}}</span>
                                    <span href="#" class="btn btn-danger pull-right" onclick="removeTeam(this);"><i class="fa fa-trash"></i>&nbsp;{{trans('resource::view.Remove team')}}</span>
                                </div>
                            </section>
                        @endforeach
                    @endif
                    <!-- BUTTON ADD TEAM -->
                    <span href="#" class="btn btn-success btn-add-team hidden"><i class="fa fa-plus"></i>&nbsp;<b>{{trans('resource::view.Add team')}}</b></span>
                </div>
                
                <!-- BOX TEAM --> 
                <div class="add-box-team hidden">
                    <section class="box box-info team-container">
                        <div class="box-body">
                            <div class="row margin-bottom-20">
                                <div class="col-md-12">
                                    <span>                              
                                        <select class="form-control select-search width-100-per team" onchange="changeTeam(this);">
                                            <option value="0">{{ trans('resource::view.Request.Create.Select team') }}</option>
                                            @foreach($teamsOptionAll as $option)
                                            
                                            <option value="{{ $option['value'] }}" >{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <div class="row margin-bottom-20 box-position">
                                <div class="col-md-6">
                                    <span>                                  
                                        <select class="form-control width-93 position-apply" onchange="changePosition(this);" >
                                            <option value="0">{{ trans('resource::view.Select position') }}</option>
                                            @foreach($roles as $key => $option)
                                                <option value="{{ $key }}" >{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </div>    
                                <div class="col-md-5">
                                    <span>                                  
                                        <input type="number" class="form-control num number-resource" min="1" value="" onkeyup="numberInput(this);" placeholder="{{trans('resource::view.Number resource')}}" />
                                    </span>
                                </div>
                                <div class="col-md-1">
                                    <span href="#" class="btn-delete btn-delete-row float-right" row="2" onclick="removePosition(this);"><i class="fa fa-trash"></i></span>
                                </div>
                            </div>
                            <!-- BUTTON ADD POSITION -->
                            <span href="#" class="btn btn-info btn-add-position hidden" onclick="addBoxPosition(this);"><i class="fa fa-plus"></i>&nbsp;{{trans('resource::view.Add position')}}</span>
                            <span href="#" class="btn btn-danger pull-right" onclick="removeTeam(this);"><i class="fa fa-trash"></i>&nbsp;{{trans('resource::view.Remove team')}}</span>
                        </div>
                    </section>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary pull-left" data-dismiss="modal">{{ Lang::get('resource::view.Cancel & close') }}</button>
                <button type="submit" class="btn btn-primary save-team">{{ Lang::get('resource::view.Request.Create.Save') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
