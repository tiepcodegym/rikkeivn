<?php
    use Rikkei\Files\Model\ManageFileText;

    $countTypeGo = ManageFileText::countTypeGo();
    $countTypeTo = ManageFileText::countTypeTo();
    $countApprovalTo = ManageFileText::countApprovalTo();
    $countUnApprovalTo = ManageFileText::countUnApprovalTo();
    $countApprovalGo = ManageFileText::countApprovalGo();
    $countUnApprovalGo = ManageFileText::countUnApprovalGo();
    $typeCvdi = ManageFileText::CVDI;
    $typeCvden = ManageFileText::CVDEN;
    $appProval = ManageFileText::APPROVAL;
    $unAppProval = ManageFileText::UNAPPROVAL;
?>
<!-- Box self -->
<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('files::view.Văn bản đi') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('file::file.add', $typeCvdi) }}">
                    <i class="fa fa-share-square-o"></i> {{ trans('files::view.Thêm mới văn bản') }}
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvdi) }}">
                    <i class="fa fa-inbox"></i> {{ trans('files::view.All') }}
                    <span class="label bg-aqua pull-right">{{ $countTypeGo}}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvdi) }}?status={{ $appProval}}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('files::view.Văn bản đã vào sổ') }}
                    <span class="label bg-gray-active pull-right">{{ $countApprovalGo }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvdi) }}?status={{ $unAppProval}}">
                    <i class="fa fa-check"></i> {{ trans('files::view.Văn bản chờ xử lý') }}
                    <span class="label bg-green pull-right">{{ $countUnApprovalGo }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /. box -->
<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('files::view.Văn bản đến') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            <li>
                <a href="{{ route('file::file.add', $typeCvden) }}">
                    <i class="fa fa-share-square-o"></i> {{ trans('files::view.Thêm mới văn bản') }}
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvden) }}">
                    <i class="fa fa-inbox"></i> {{ trans('files::view.All') }}
                    <span class="label bg-aqua pull-right">{{ $countTypeTo }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvden) }}?status={{ $appProval}}">
                    <i class="fa fa-hourglass-half"></i> {{ trans('files::view.Văn bản đã vào sổ') }}
                    <span class="label bg-gray-active pull-right">{{ $countApprovalTo }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('file::file.list', $typeCvden) }}?status={{ $unAppProval}}">
                    <i class="fa fa-check"></i> {{ trans('files::view.Văn bản chờ xử lý') }}
                    <span class="label bg-green pull-right">{{ $countUnApprovalTo }}</span>
                </a>
            </li>
        </ul>
    </div>
</div>