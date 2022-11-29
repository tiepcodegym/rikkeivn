<?php
    extract($data);
    $i = 0;
?>
<!DOCTYPE html>
<html>

<head>
    <style>
    table {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    table td,
    table th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    table tr:hover {
        background-color: #ddd;
    }

    table th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #4CAF50;
        color: white;
    }
    </style>
</head>

<body>
</body>

</html>
    Chào <b>hungnt2</b>, <br>
    Có một số company được đồng bộ từ CRM nhưng account (manage, sale, created_by) chưa được tạo ở rikkei.vn:
    <table style="width:100%">
        <tr>
            <th>STT</th>
            <th>Id company</th>
            <th>Name company</th>
            <th>Email Manage</th>
            <th>Email Sale</th>
            <th>Email Created_by</th>
        </tr>
        @foreach ($data as $key => $item)
        <tr>
            <td>{{ ++$i }}</td>
            <td>{{ $key }}</td>
            <td>{{isset($item['name_companay']) ? $item['name_companay'] : '' }}</td>
            <td>{{isset($item['emailManages']) ? $item['emailManages'] : '' }}</td>
            <td>{{isset($item['emailSale']) ? $item['emailSale'] : '' }}</td>
            <td>{{isset($item['emailEmpCreated']) ? $item['emailEmpCreated'] : '' }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>