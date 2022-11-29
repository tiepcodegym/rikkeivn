<?php

use Rikkei\Music\View\ViewMusic;
?>
<?php
    $current_url        = URL::current();
    $curent_office_id   = substr($current_url, strrpos($current_url, '/') + 1);
    $curent_office = ViewMusic::getOffice($curent_office_id);

    $offices            = ViewMusic::getMenuMusic();
?>
<div class="row music-header">
    <div class="col-md-4 music-logo align-center"">
        <div class=" col-md-2 redundancy"></div>
        <div class="col-md-12 col-xs-6  col-md-10  align-center" id="logo-sm">
            <a class="navbar-brand align-center" href="#" id="a-logo">
                <img src="{{ asset('/asset_music/images/logo-rikkei.png') }}"/>
            </a>
        </div>
        <div class="col-xs-6 align-right" id="menu-sm">
            <span type="button" data-toggle="collapse" data-target="#myNavbar" id="hamburger">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </span>
        </div>
    </div>
    
    <div class="col-md-8" id="menu-list">
        <div class="row menu-list">
            <div class="col-md-1"></div>
            <div class="col-md-10 ">
                <div class="scrollmenu">
                    @if($curent_office)
                        <a href="{{ URL::route('music::order.office', ['id' => $curent_office_id ]) }}" value="{{$curent_office_id}}">{{$curent_office->name}}</a>
                    @endif
                    @foreach($offices as $office )
                        @if($curent_office_id != $office->id)
                                <a href="{{ URL::route('music::order.office', ['id' => $office->id ]) }}" value="{{$office->id}}">{{$office->name}}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<div class="collapse panel-collapse" id="myNavbar">
    <ul class="list-group">
        @foreach($offices as $office )
        @if($curent_office_id != $office->id)
            <li class="list-group-item">
                <a href="{{ URL::route('music::order.office', ['id' => $office->id ]) }}" value="{{$office->id}}">{{$office->name}}</a>
            </li>
        @else
            <li class="list-group-item">
                <a class="choose" href="{{ URL::route('music::order.office', ['id' => $office->id ]) }}" value="{{$office->id}}">{{$office->name}}</a>
            </li>
        @endif
        @endforeach
    </ul>
</div>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-8">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <span class="arrow-down"></span>
        </div>
    </div>
</div>
