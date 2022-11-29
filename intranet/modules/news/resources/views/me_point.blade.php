<?php
    use Rikkei\Core\View\CoreUrl;
?>
@extends('layouts.default')

@section('title')
Thiết lập quỹ thưởng ME
@endsection

@section('css')

@endsection



@section('content')
<div class="row container-fluid">
    <div class="col-md-5" >
        <div class="box box-primary " style="min-height: 843px;
        background: #fff; border-top: 1px solid #ccc;">
            <div class="box-header with-border" style="text-align: right;">
                <button type="button" class="btn btn-default" style="border-radius: 23px;
                        padding: 10px 34px;"><i class="fa fa-refresh"></i>&nbsp;Refresh</button>
                <button type="button" class="btn bg-maroon btn-flat margin" style="border-radius: 23px;
        padding: 10px 34px;"><i class="fa fa-check"></i>&nbsp;Submit</button>
            </div>
            <div class="box-body form-horizontal" style="font-size:15px; padding: 15px 25px;">
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-5 control-label" style="font-weight: 600;margin-top: 11px;">Hệ số thưởng ME</label>
                    <label for="inputEmail3" class="col-sm-7 control-label" style="font-size: 18px;color: #d81b60;"><span class="badge bg-yellow" style="font-size: 18px; padding: 3px 15px;">3,000,000 VND</span></label>
                </div>
                <hr>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-5 control-label" style="font-weight: 600;margin-top: 11px;">Quỹ thưởng ME</label>
                    <label for="inputEmail3" class="col-sm-7 control-label" style="font-size: 30px;color: #d81b60;">50,000,000 VND</label>
                </div>
                <hr>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-5 control-label" style="font-weight: 600;">Quỹ phát triển division</label>
                    <div class="col-sm-3">
                        <div class="input-group">
                            <input type="number" class="form-control pull-right" value="50">
                            <div class="input-group-addon">
                                <i class="fa">%</i>
                            </div>

                        </div>
                    </div>
                    <label for="inputEmail3" class="col-sm-4 control-label" style="font-size: 18px;">50,000,000 VND</label>
                </div>
                <hr>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-5 control-label" style="font-weight: 600;">Lợi nhuận</label>
                    <label for="inputEmail3" class="col-sm-7 control-label" style="color: #07c162;font-size: 18px;">100,000,000 VND</label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-7" style="padding-left: 0;">
        <div class="row">
            <div class="col-md-12">
                <div class="box " style="border-top: 1px solid #ccc;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Bảng doanh thu</h3>
                    </div>

                    <div class="box-body">
                        <table class="table table-bordered">
                            <tbody><tr>
                                    <th style="width: 10px">#</th>
                                    <th>Dự án</th>
                                    <th style="width: 40px">Effort</th>
                                    <th >Doanh thu (VND)</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong>25</strong></td>
                                    <td><strong><span class="badge bg-green" style="font-size: 15px;">200,000,000</span></strong></td>
                                </tr>
                                <tr>
                                    <td>1.</td>
                                    <td>Update software</td>
                                    <td>
                                        5
                                    </td>
                                    <td>120,000,000</td>
                                </tr>
                                <tr>
                                    <td>2.</td>
                                    <td>Clean database</td>
                                    <td>
                                        3
                                    </td>
                                    <td>20,000,000</td>
                                </tr>
                                <tr>
                                    <td>3.</td>
                                    <td>Cron job running</td>
                                    <td>
                                        12
                                    </td>
                                    <td>40,000,000</td>
                                </tr>
                                <tr>
                                    <td>4.</td>
                                    <td>Fix and squish bugs</td>
                                    <td>
                                        5
                                    </td>
                                    <td>120,000,000</td>
                                </tr>
                            </tbody></table>
                    </div>

                    <div class="box-footer clearfix">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <li><a href="#">«</a></li>
                            <li><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">»</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box " style="border-top: 1px solid #ccc;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Bảng Chi phí</h3>
                    </div>

                    <div class="box-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Tên chi phí</th>
                                    <th >Chi phí (VND)</th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td><strong><span class="badge bg-green" style="font-size: 15px;">100,000,000</span></strong></td>
                                </tr>
                                <tr>
                                    <td>1</td>
                                    <td>Lương tháng</td>
                                    <td>30,000,000</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Chi phí OT</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Phụ cấp</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Thưởng</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Chi phí văn phòng</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Chi phí khấu hao</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>Bảo hiểm</td>
                                    <td>10,000,000</td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Chi phí khác</td>
                                    <td>10,000,000</td>
                                </tr>
                            </tbody></table>
                    </div>

                    <div class="box-footer clearfix">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <li><a href="#">«</a></li>
                            <li><a href="#">1</a></li>
                            <li><a href="#">2</a></li>
                            <li><a href="#">3</a></li>
                            <li><a href="#">»</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

@section('script')
 <script src="{{ CoreUrl::asset('asset_news/js/news.js') }}"></script>
<script>
jQuery(document).ready(function(){
    RKfuncion.boxMatchHeight.init({
        parent: '.bl-row',
        children: ['.bci-header', '.bci-image', '.post-desc', '.bc-item'],
        center: ['.bci-image']
    });
});
</script>
@endsection