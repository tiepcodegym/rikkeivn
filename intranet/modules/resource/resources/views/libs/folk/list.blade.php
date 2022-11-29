@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CoreUrl;

$folkTbl = Rikkei\Team\Model\LibsFolk::getTableName();
?>
@section('title')
{{ trans('resource::view.Libs.Folk.List Folk name') }}
@endsection
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link href="{{ asset('sales/css/customer_index.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
@endsection
@section('content')
<div class="row" id="lib-folk-list">
    <div class="col-sm-12">
        <button class="btn btn-primary" style="margin-bottom:10px;" id="add-folk">{{ trans('resource::view.Libs.Folk.Create Folk') }}</button>
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter')
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-15-per">{{ trans('resource::view.Numerical order') }}</th>
                            <th class="sorting {{ Config::getDirClass('name') }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name') }}">{{ trans('resource::view.Name') }}</th>
                            <th class="col-action width-10-per">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $folkTbl }}.name]" value="{{ Form::getFilterData("{$folkTbl}.name") }}" placeholder="{{ trans('resource::view.Search') }}..." class="filter-grid form-control" />
                                    </div>
                                </div>
                            </td>
                            <td></td>  
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr id='folk_{{ $item->id }}'>
                                    <td>{{ $i }}</td>
                                    <td data-col= "name">{{ $item->name }}</td>
                                    <td class="text-center">
                                        <a href="javascript::void(0);" class="edit-folk btn-edit" data-id='{{ $item->id }}'>
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('resource::view.Languages.List.No results found')}}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
    </div>
</div>
@include('resource::libs.folk.create')
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    
    <script>
        $('#add-folk,.edit-folk').on('click', function(e) {
            e.preventDefault();
            $('#lib-folk-create').show(800);
            $('#lib-folk-create').removeClass('hidden');
            $('#lib-folk-list').hide(800);
            var id = parseInt($(this).attr('data-id'));
            setData(id);
        });
        
        $('#close').on('click', function(e) {
            e.preventDefault();
            hideForm();
        });
        
        var rules = {
            'name': {
                required: true,
                rangelength: [0, 255],
            },
        };
        var messages = {
            'name': {
                required: '<?php echo trans('core::view.This field is required'); ?>',
                rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 255]) ; ?>'
            },  
        };
        
        $('#form-create-folk').validate({
            'rules': rules,
            'messages': messages,
        });
        
        $('#form-create-folk').on('submit', function(e){
           e.preventDefault();
           if ($('#form-create-folk').valid()) {
               $.ajaxSetup({
                headers: {
                  'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
               $.ajax({
                   url: $(this).attr('action'),
                   type: 'POST',
                   data: $(this).serialize(),
                   dataType: 'JSON',
                   success : function(rs) {
                       if(rs.success) {
                           setTimeout(function() {
                               window.location.href = '{{ route('resource::libfolk.list') }}';
                           }, 1000);
                       } else {
                           setErrorMessage(rs.messages);
                       }
                   },
                   error : function(rs) {
                       alert(rs.statusText);
                   }
               });
           }
        });
        
        function setErrorMessage($errors) {
           var attrs = Object.keys($errors);
           $.each(attrs, function($i, $key){
               $('input[name="'+$key+'"]').addClass('error');
               var label = $('#'+$key+ '-error');
               if(!label.length) {
                   $('input[name="'+$key+'"]').after('<label id="'+$key+'-error" class="error" for="'+$key+'"></label>');
               }
               label = $('#'+$key+ '-error');
               label.text(''+$errors[$key][0]);
           });
        }
        
        /**
         * setData by id
         * @param {type} $id
         * @returns {undefined}         */
        function setData($id) {
            var trId = $('#folk_' + $id);
            if (($id != 0) && trId.length) {
                // change text btn submit
                $('#form-create-folk').find('button[type="submit"]').text('{{ trans('resource::view.Libs.Folk.Edit Folk update') }}');
                var name = trId.find('td[data-col="name"]').text();
                var form = $('#form-create-folk');
                $('#form-create-folk input[name="name"]').val(name);
                $('#form-create-folk input[name="id"]').val($id);
            }
        }
        
        function resetForm() {
            $('#form-create-folk').find('button[type="submit"]').text('{{ trans('resource::view.Libs.Folk.Create Folk') }}');
            $('#form-create-folk input[name="name"]').val('');
            $('#form-create-folk input[name="id"]').val('');
        }
        function hideForm() {
            resetForm();
            $('#lib-folk-create').hide(800);
            $('#lib-folk-create').addClass('hidden');
            $('#lib-folk-list').show(800);
        }
    </script>
@endsection