<?php
use Rikkei\Project\View\ProjectGitlab;

$gitlab = ProjectGitlab::getInstance();
?>
@extends('layouts.default')
@section('title')
    Danh sách dự án trên gitlab
@endsection
@section('content')
<div class="box box-primary">
    <div class="box-body">
        <div class="css-create-page request-create-page request-detail-page word-break">
            <div class="table-responsive">
                <table id="candidateTbl" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="example2_info">
                    <thead>
                        <tr role="row">
                            <th  >Tên dự án</th>
                            <th >Link dự án</th>
                            <th >Thành viên trong dự án</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($collectionModel) > 0)
                        @foreach($collectionModel as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td><a href="{{ 'https://git.rikkei.org/' . $item['path_with_namespace'] }}">{{ 'https://git.rikkei.org/' . $item['path_with_namespace'] }}</a></td>
                            <td>{{ $gitlab->showMembers($item['id']) }}</td>
                        </tr>
                        @endforeach
                        @else
                        <tr><td colspan="3" class="text-align-center"><h2>Không có bản ghi nào</h2></td></tr>
                        @endif
                    </tbody>
                </table>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
