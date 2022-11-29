
<html>

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            background: white;
            margin: 50px auto auto;
        }

        td,
        th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }

        button {
            color: #fff;
            background-color: #c9302c;
            border-color: #ac2925;
            border-radius: 5px;
            text-decoration: auto;
        }

        button:hover, button:focus, button:active {
            border-color: #c9302c;
            outline: #c9302c;
            color: #fff;
        }

        a[href] {
            color: black;
        }

        .text-center {
            color: #fff;
            font-size: 20px;
            padding-top: 2rem;
            margin: auto;
            padding-bottom: 50px;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="body">
    <h2>Thông tin nhân viên đăng ký làm: {{ $data['arrRoles'][$data['role_id']] }}</h2>
    @if($data['role_id'] == $data['roleMentor'])
        <table style="width:100%">
            <tr>
                <th>Họ và Tên</th>
                <th>email</th>
                <th>Vai trò</th>
                <th>Chuyên môn</th>
            </tr>
            <tr>
                <td>{{ $data['employeeName'] }}</td>
                <td>{{ $data['employeeEmail'] }}</td>
                <td>{{ $data['arrRoles'][$data['role_id']] }}</td>
                <td>
                    @if(isset($data['topics']) && $data['topics'])
                        @foreach($data['topics'] as $item)
                            {{ $item['name'] }} <br>
                        @endforeach
                    @endif
                </td>
            </tr>
        </table>
    @else
        <h2>Thân gửi: {{ $data['mentorName'] }}</h2>
        <h4>Phòng L&D xin thông báo đã có 1 CBNV đăng ký trở thành Mentee của Anh/chị với thông tin như sau:</h4>
        <table style="width:95%">
            <tr>
                <th>Họ và Tên</th>
                <th>email</th>
                <th>Chủ đề đăng ký</th>
                <th>Vấn đề gặp phải</th>
            </tr>
            <tr>
                <td>{{ $data['employeeName'] }}</td>
                <td>{{ $data['employeeEmail'] }}</td>
                <td>
                    @if(isset($data['topics']) && $data['topics'])
                        @foreach($data['topics'] as $item)
                            {{ $item['name_full'] }} <br>
                        @endforeach
                    @endif
                </td>
                <td>{{ $data['problem'] }}</td>
            </tr>
        </table>
        <div class="text-center">
            <button>
                <a type="button" class="btn btn-success" target="_blank" style="color: #FFFFFF"
                   href="{{ $data['url_reject'] }}">Từ chối</a>
            </button>
            <button>
                <a type="button" class="btn btn-danger" target="_blank" style="color: #FFFFFF"
                   href="{{ $data['url_confirmed'] }}">Xác nhận</a>
            </button>
        </div>
    @endif
</div>
</body>
</html>
