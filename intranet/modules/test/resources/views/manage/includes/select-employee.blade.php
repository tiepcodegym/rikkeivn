<?php
use Rikkei\Core\View\View;
?>

@if($view == 'index')
<div class="row table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th><input type="checkbox" class="check-all" id="tbl_check_all" data-list=".table-check-list"></th>
                <th style="width: 20px" class="col-id">{{ trans('core::view.NO.') }}</th>
                <th>{{ trans('team::view.Avatar_en') }}</th>
                <th class="sorting col-id" data-order="employee_code" data-dir="">{{ trans('team::view.Code') }}</th>
                <th class="sorting col-name" data-order="name" data-dir="">{{ trans('team::view.Name') }}</th>
                <th class="sorting col-name" data-order="email" data-dir="">{{ trans('team::view.Email') }}</th>
            </tr>
        </thead>
        <tbody class="checkbox-list table-check-list" data-all="#tbl_check_all" data-export="">
            @if(count($collectionEmployee) != 0)
                <?php $i = 1; if (isset($page)) $i = ($page - 1) * 10 + $i; ?>
            @foreach($collectionEmployee as $test => $item)
                <tr>
                <td><input type="checkbox" class="check-item" <?php if(isset($checkArr)) if (in_array($item->id, $checkArr)): ?> checked <?php endif ?> value="{{ $item->id }}"></td>
                <td>{{ $i }}</td>
                <td><img width="50" class="img-responsive img-circle" src="{{ $item->getAvatarUrl() }}"></td>
                <td>{{ $item->employee_code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                </tr>
            <?php $i++ ?>
            @endforeach
            @else
            <tr>
                <td colspan="12" class="text-center">
                    <h2 class="no-result-grid">{{trans('core::view.No results found')}}</h2>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="box-body">
    {!! $collectionEmployee->links() !!}
</div>
@endif

@if(isset($page) || isset($checkArr))
<script type="text/javascript">
    checkAll();
    $(document).ready( function() {
        $(".check-all").click( function() {
            var checkArr;
            if($(this).is(':checked')) {
                $('.check-item').each( function() {
                    $(this).prop('checked', true);
                    checkArr = checkEmployeeArr($(this));
                });
            } else {
                $('.check-item').each( function() {
                    $(this).prop('checked', false);
                    $(this).removeAttr('checked');
                    checkArr = checkEmployeeArr($(this));
                });
            }
            $.ajax({
                url: urlEmployee,
                type: 'post',
                data: {
                    _token: token,
                    type: 'check',
                    checkArr: checkArr
                },
                dataType: 'json',
                success: function(check) {
                    $('.select-employee').html(check.content);
                }
            });
        });
    });
</script>
@endif

@if($view == 'check')
@if(count($collectionEmployee) != 0)
<div class="row table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th style="width: 20px" class="col-id">{{ trans('core::view.NO.') }}</th>
                <th>{{ trans('team::view.Avatar_en') }}</th>
                <th class="sorting col-id" data-order="employee_code" data-dir="">{{ trans('team::view.Code') }}</th>
                <th class="sorting col-name" data-order="name" data-dir="">{{ trans('team::view.Name') }}</th>
                <th class="sorting col-name" data-order="email" data-dir="">{{ trans('team::view.Email') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="checkbox-list table-check-list" data-all="#tbl_check_all" data-export="">
            <?php $i = 1 ?>
            @foreach($collectionEmployee as $item)
                <tr><input type="hidden" class="check-employee" value="{{ $item->id }}">
                <td><span class="id-number">{{ $i }}</span></td>
                <td><img width="50" class="img-responsive img-circle" src="{{ $item->getAvatarUrl() }}"></td>
                <td>{{ $item->employee_code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td><button type="button" class="btn btn-danger remove-check"><i class="fa fa-minus"></i></button></td>
                </tr>
            <?php $i++ ?>
            @endforeach
        </tbody>
    </table>
</div>

<script type="text/javascript">
    var $th = $('.select-employee .table-responsive').find('thead th')
    $('.select-employee .table-responsive').on('scroll', function() {
        var trans = this.scrollTop - 1;
        $th.css('transform', 'translateY('+ trans +'px)');
    });
    $(".remove-check").click( function() {
        var removeCheck = $(this).parent().parent().find('input');
        var i = checkEmployee.indexOf(removeCheck.val());
        if (i != -1) {
            checkEmployee.splice(i,1);
        }
        $(this).parent().parent().remove();
        $('.check-item').each( function() {
            if ($(this).val() == removeCheck.val()) {
                $(this).prop('checked', false);
            }
        });
    });
    checkAll();
    $number = 1;
    $('.id-number').each( function() {
        $(this).html('');
        $(this).html($number++);
    });
</script>
@endif
@endif
