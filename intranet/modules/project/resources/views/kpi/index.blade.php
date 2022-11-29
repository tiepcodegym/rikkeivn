<?php
use Rikkei\Project\Model\Project;
use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')
@section('title', 'KPI')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{!!CoreUrl::asset('project/css/edit.css')!!}" />
@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <form class="form-inline" autocomplete="off"
                    action="{!!route('project::kpi.index')!!}"
                    method="get" id="form-kpi-export"
                    data-form-submit="ajax"
                    data-flag-valid="1"
                    data-cb-success="kpiDataSuccess">
                        <div class="form-group">
                            <label for="from" class="required">From <em>*</em></label>
                            <input name="from" class="form-control input-field" type="text" id="from" value="" data-date-picker>
                        </div>
                        <div class="form-group">
                            <label for="to" class="required">To <em>*</em></label>
                            <input name="to" class="form-control input-field" type="text" id="to" value="" data-date-picker>
                        </div>
                        <button type="submit" class="btn btn-primary margin-right-20">
                            {!!trans('project::view.View')!!}
                            <i class="fa fa-spin fa-refresh hidden loading-submit"></i>
                        </button>
                        <button type="button" class="btn btn-primary hidden" d-dom-fg="btn-export">
                            <i class="fa fa-download"></i>
                            {!!trans('project::view.Export')!!}
                        </button>
                    </form>
                    <p>&nbsp;</p>
                    <div data-fg-dom="kpi-result" class="hidden">
                        <div class="nav-tabs-custom">
                            <!-- tab header -->
                            <ul class="nav nav-tabs" d-kpi-dom="tab-title">
                                <li class="active">
                                    <a href="#hrindex" data-toggle="tab">
                                        <strong>{{ trans('project::view.HR data') }}</strong>
                                    </a>
                                </li>
                                <li>
                                    <a href="#proj" data-toggle="tab">
                                        <strong>{{ trans('project::view.Project') }}</strong>
                                    </a>
                                </li>
                                <li>
                                    <a href="#average" data-toggle="tab">
                                        <strong>{{ trans('project::view.Average') }}</strong>
                                    </a>
                                </li>
                                <li d-aver-detail="tab-title">
                                    <a href="#average_detail_{id}" data-toggle="tab">
                                        <strong>{division}</strong>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content" d-kpi-dom="tab-content">
                                <div class="tab-pane active" id="hrindex">
                                    @include('project::kpi.hr_index')
                                </div>
                                <div class="tab-pane" id="proj">
                                    @include('project::kpi.project')
                                </div>
                                <div class="tab-pane" id="average">
                                    @include('project::kpi.average')
                                </div>
                                <div class="tab-pane" id="average_detail_{id}" d-aver-detail="tab-content">
                                    @include('project::kpi.average_detail')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{!!asset('common/js/methods.validate.js')!!}"></script>
<script src="{!! CoreUrl::asset('common/js/methods.validate.js') !!}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/blob-polyfill/2.0.20171115/Blob.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/javascript-canvas-to-blob/3.14.0/js/canvas-to-blob.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
<script>
    var globVarPass = {
        teamParent: {!!isset($teamParent) && $teamParent ? json_encode($teamParent) : 0!!},
        projEffortTypeMD: {!!Project::MD_TYPE!!},
        projTypes: {!!json_encode(Project::labelTypeProject())!!},
        divisions: {!!json_encode($divisions)!!},
        labelCompany: '{!!trans('project::view.Company')!!}',
        labelOther: '{!!trans('project::view.Other')!!}',
        evaluationLabel: {!!json_encode($evaluation)!!},
    };
</script>
<script src="{!!CoreUrl::asset('project/js/kpi.js')!!}"></script>
<script type="text/xml" id="styleXml">
    <Style ss:ID="hr_title">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center" />
        <Font ss:FontName="Arial" x:Family="Swiss" ss:Size="11" ss:Bold="1" />
        <Interior ss:Color="#D8D8D8" ss:Pattern="Solid" />
    </Style>
    <Style ss:ID="hr_cw">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="hr_block_1">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#D7E4BC" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_block_2">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#B6DDE8" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_block_3">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#CCC0DA" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_block_4">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#E6B9B8" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_body_odd">
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="hr_body_even">
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#e2e1e1" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_detail_title">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#D7E4BC" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="hr_detail_team">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="hr_detail_body_odd">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="hr_detail_body_even">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#e2e1e1" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="proj_body_odd">
        <Alignment ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="proj_body_even">
        <Alignment ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#e2e1e1" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="proj_ave_ave">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/>
        <Interior ss:Color="#000000" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="text_center">
        <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11" />
    </Style>
    <Style ss:ID="text_left_bold">
        <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11" ss:Bold="1"/>
    </Style>
    <Style ss:ID="proj_detail_label_odd">
        <Alignment ss:Vertical="Center" ss:Horizontal="Right" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
    </Style>
    <Style ss:ID="proj_detail_label_even">
        <Alignment ss:Vertical="Center" ss:Horizontal="Right" ss:WrapText="1"/>
        <Borders>
            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
        </Borders>
        <Font ss:FontName="Arial" ss:Size="11"/>
        <Interior ss:Color="#e2e1e1" ss:Pattern="Solid"/>
    </Style>
</script>
@endsection
