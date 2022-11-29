<?php
use Rikkei\Team\View\Permission;
?>

<div class="col-md-2" id="contentL">    
    <div class="box box-info">
        <div class="box-body">
            @if(Permission::getInstance()->isAllow('help::manage.help.edit'))
            <button id="addHelp" type="button" class="btn btn-primary btn-md" @if($pageType == 'view') onclick="window.location='{{ URL::route('help::manage.help.create') }}'" @else onclick="createHelp()" @endif>
                <i class="fa fa-plus fa-2" aria-hidden="true"></i> &nbsp;&nbsp;{{ trans('help::view.Add') }}
            </button>
            @endif
            <br/>
            <br/>
            <div class="input-group display-search">
                <input type="text" id="input-search" class="form-control" placeholder="{{ trans('help::view.Search') }}..." value="">
                <span class="input-group-btn">
                    <button class="btn btn-primary btn-search" type="button">
                        <i class="fa fa-search fa-2" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
            <br/>
            <br/>
            <div id="container">
            </div> 
        </div>
    </div>
</div>    
