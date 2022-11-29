<?php
use Rikkei\Me\View\View as MeView;
?>

@extends('layouts.default')

@section('title', trans('project::me.Config data'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<style>
    .help {
        font-size: 15px;
        position: relative;
        top: -8px;
    }
    .help .help-note {
        display: none;
        position: absolute;
        width: 300px;
        background: #000;
        color: #fff;
        padding: 5px;
        border-radius: 5px;
        top: -10px;
        z-index: 999;
        line-height: 20px;
    }
    .help:hover .help-note{
        display: inline-block;
    }
</style>
@endsection

@section('content')

<div class=" setting-system-data-page">

    <div class="box box-info">
        <div class="box-header">
            <h2 class="box-body-title">{{ trans('me::view.Reward') }}</h2>
        </div>

        <div class="box-body">
            <?php
            $configRewards = $configRewards ? unserialize($configRewards) : [];
            $configNewRewards = $configNewRewards ? unserialize($configNewRewards) : [];
            $configNew2Rewards = $configNew2Rewards ? unserialize($configNew2Rewards) : [];
            $configRewardsOnsite = $configRewardsOnsite ? unserialize($configRewardsOnsite) : [];
            if (count($configNewRewards) < 1) {
                $configNewRewards = [
                    MeView::TYPE_S => 2000000,
                    MeView::TYPE_A => 1000000,
                    MeView::TYPE_B => 300000,
                    MeView::TYPE_C => 0
                ];
            }
            if (count($configNew2Rewards) < 1) {
                $configNew2Rewards = [
                    MeView::TYPE_S => 4000000,
                    MeView::TYPE_A => 2000000,
                    MeView::TYPE_B => 600000,
                    MeView::TYPE_C => 0
                ];
            }
            if (count($configRewardsOnsite) < 1) {
                $configRewardsOnsite = [
                    MeView::TYPE_S => 1000000,
                    MeView::TYPE_A => 500000,
                    MeView::TYPE_B => 0,
                    MeView::TYPE_C => 0
                ];
            }
            ?>
            <form id="form-system-tour_event_birthday" method="post" action="{{ route('project::me.config_data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}

                @include('project::me.template.config-reward-data', [
                    'contributes' => $contributes,
                    'configRewards' => $configRewards,
                    'keyDb' => 'me.config.reward'
                ])
            </form>
        </div>

        <div class="box-header">
            <h2 class="box-body-title">Old Reward <span class="fa fa-question-circle help"><span class="help-note">{{ trans('me::view.Applies to February 2021 and earlier') }}</span></span></h2>
        </div>
        <div class="box-body">
            <form id="form-system-tour_event_birthday" method="post" action="{{ route('project::me.config_data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}

                @include('project::me.template.config-reward-data', [
                    'contributes' => $newContributes,
                    'configRewards' => $configNewRewards,
                    'keyDb' => 'me.new.config.reward'
                ])
            </form>
        </div>

        <div class="box-header">
            <h2 class="box-body-title">New Reward <span class="fa fa-question-circle help"><span class="help-note">{{ trans('me::view.Valid for March 2021 and beyond until new regulations') }}</span></span></h2>
        </div>
        <div class="box-body">
            <form id="form-system-tour_event_birthday" method="post" action="{{ route('project::me.config_data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
        
                @include('project::me.template.config-reward-data', [
                    'contributes' => $newContributes,
                    'configRewards' => $configNew2Rewards,
                    'keyDb' => 'me.new2.config.reward'
                ])
            </form>
        </div>
        
        <div class="box-header">
            <h2 class="box-body-title">Reward Onsite</h2>
        </div>
        <div class="box-body">
            <form id="form-system-tour_event_birthday" method="post" action="{{ route('project::me.config_data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}

                @include('project::me.template.config-reward-data', [
                    'contributes' => $newContributes,
                    'configRewards' => $configRewardsOnsite,
                    'keyDb' => 'me.config.reward_onsite'
                ])
            </form>
        </div>
    </div>

</div>
@endsection

@section('script')
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
{{-- <script src="{{ URL::asset('common/js/setting-data.js') }}"></script> --}}
@endsection

