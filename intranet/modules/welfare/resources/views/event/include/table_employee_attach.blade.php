<?php 
    use Rikkei\Welfare\Model\WelEmployeeAttachs;
?>
@foreach($data as $value)
    <tr>
        <td>{{$value->name}}</td>
        <td>{{$value->nameRelation}}</td>
        <td>{{$value->birthday}}</td>
        <td>@if($value->gender == WelEmployeeAttachs::GENDER_MALE) Nam @else Nữ @endif
        </td>
        <td>{{$value->phone}}</td>
        <td>
            <button class="btn btn-edit btn-edit-employee-attach" data-id="{{$value->id}}"><i class="fa fa-edit"></i></button>
            <button class="btn btn-delete btn-delete-employee-attach" data-id="{{$value->id}}"><i class="fa fa-trash"></i></button>

        </td>
    </tr>
@endforeach
