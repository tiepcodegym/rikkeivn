<!DOCTYPE html>
<html>
<head>
</head>
<body>
<table border="1">
	<thead class="managetime-thead">
      <tr>
         <th>STT</th>
         <th>Mã nhân viên</th>
         <th>Nhân viên</th>
         <th>Chứng chỉ</th>
         <th>Cấp độ</th>
         <th>Từ ngày</th>
         <th>Đến ngày</th>
		 <th>Ngày duyệt/Không duyệt</th>
		 <th>Status</th>
	  </tr>
	</thead>
   <tbody>
   	@if(isset($data) && count($data) > 0)
   	@foreach($data as $index => $item)
   		<tr>
   			<td colspan="7" class="text-left"><b>{{$item[0]['teams_name']}}</b></td>
   		</tr>
   		@foreach($item as $index => $item1)
   		<tr>
		   <td class="">{{$index + 1}}</td>
		   <td class="">{{$item1['employee_code']}}</td>
		   <td class=" managetime-show-popup">{{$item1['employees_name']}}</td>
		   <td class="">{{$item1['name']}}</td>
		   <td>{{$item1['level']}}</td>
		   <td class="">{{$item1['start_at']}}</td>
		   <td class="">{{$item1['end_at']}}</td>
		</tr>
   		@endforeach
   	@endforeach
   	@endif
   </tbody>
</table>
</body>
</html>