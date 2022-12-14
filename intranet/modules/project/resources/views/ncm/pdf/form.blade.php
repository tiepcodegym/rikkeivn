<?php
use Carbon\Carbon;


?>
<!DOCTYPE html>
<html>
<head>
    <title>form pdf</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css" media="all">
        .container {
            font-family: Arial!important;
        }
        .page-head {
            height: 50px;
        }
        .logo-img {
            width: 110px;
            padding: 15px;
        }
        
        .title-header {
            font-size: 15px;
            text-align: center;
            font-weight: bold;
            width: 300px;
        }

        div.code-header {
            padding-left: 0px;
        }
        .number-card, p {
            margin-left: 130px;
        }
        .table-body {
            border: 0.005pt solid #000000;
            font-size: 20px;
        }
        .teams {
            width: 470px;
            border-right: 0.005pt solid #000000;
            border-bottom: 0.005pt solid #000000;
            height: 40px;
        }
        .ncm_request_date {
            width: 230px;
            font-weight: bold;
            border-bottom: 0.005pt solid #000000;

        }
        .ncm-document {
            width: 470px;
            border-right: 0.005pt solid #000000;
            border-bottom: 0.005pt solid #000000;
            height: 40px;
            font-weight: bold;
        }
        .ncm-request_standard {
            width: 230px;
            font-weight: bold;
            border-bottom: 0.005pt solid #000000;

        }
        .task-content {
            width: 700px;
            text-decoration:underline;
            height: 40px;
        }
        .field-null, .field-null-2, .field-null-3, .field-null-4 {
            height: 50px;
            border-bottom: 0.005pt solid #000000;
        }
        .field-null-2 {
            width: 250px;
            border-right: 0.005pt solid #000000;
        }
        .field-null-3 {
            width: 250px;
            border-right: 0.005pt solid #000000;
        }
        .field-null-4 {
            width: 200px;
        }
        .ncm_requester {
            width: 470px;
            border-right: 0.005pt solid #000000;
            border-bottom: 0.005pt solid #000000;
            height: 40px;
            font-weight: bold;
        }
        .asign {
            width: 230px;
            font-weight: bold;
            border-bottom: 0.005pt solid #000000;

        }
        .task-fix_content {
            width: 700px;
            height: 50px;
        }
        .task_duedate, .ncm-evaluate_date {
            width: 250px;
            height: 40px;
            border-right: 0.005pt solid #000000;
            border-bottom: 0.005pt solid #000000;
            font-weight: bold;
        }
        .task_assign-depart_represent, .task_assign-evaluater {
            width: 250px;
            border-right: 0.005pt solid #000000;
            border-bottom: 0.005pt solid #000000;
            font-weight: bold;
        }
        .asign_task_duedate {
            width: 200px;
            border-bottom: 0.005pt solid #000000;
            font-weight: bold;
        }
        .ncm_result {
            height: 40px;
        }
        .Satisfactory {
            width: 350px;
            border-bottom: 0.005pt solid #000000;
        }
        .Unsatisfactory {
            width: 350px;
            border-bottom: 0.005pt solid #000000;
        }
        .checkbox {
            border: 4px 0.005pt solid #0000;
            width: 40.005pt;
            height: 38px;
        }
        .checkbox-image {
            width: 50px;
            height: 50px;
        }
        .ncm-next_measure, .ncm-evaluate_effect {
            height: 40px;
            font-weight: bold;
        }
        .font-none {
            font-weight: normal;
        }
        .content-part {
            font-weight: bold;
        }
        .text-underline {
            text-decoration:underline;
        }
    </style>
</head>
<body> 
    <div class="container">
        <div class="page-head">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td><img class="logo-img" src=" {{ URL::asset('project/images/logo.png') }}" alt="logo"></td>
                        <td class="title-header">
                            <p>PHI???U Y??U C???U H??NH ?????NG <br>KH???C PH???C/ PH??NG NG???A<br></p></td>
                        <td>
                            <table>
                                <tr><td><p>M?? s???:</p></td></tr>
                                <tr><td><p>L???n ban h??nh:</p></td></tr>
                                <tr><td><p>Ng??y ban h??nh:</p></td></tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="number-card">
            <p>S???:........../...........</p>
        </div>
        <div class="page-body">
            <table class="table-body">
                <tbody>
                    <tr>
                        <td class="teams">
                            <table>
                                <tr>
                                    <td><p class="content-part">B??? ph???n li??n quan:</p></td>
                                    <td><span>{{$teamsSelected}}</span></td>
                                </tr>
                            </table>
                        </td>
                        <td class="ncm_request_date" colspan="2">
                            <table>
                                <tr>
                                    <td><p class="content-part">Ng??y:</p></td>
                                    <td><span class="font-none">{{$ncmRequest->request_date}}</span></td>
                                </tr>
                            </table>
                            </td>
                    </tr>
                    <tr>
                        <td class="ncm-document">
                        <table>
                            <tr>
                                <td><p class="content-part" >T??i li???u li??n quan:</p></td>
                                <td><span class="font-none">{{$ncmRequest->document}}</span></td>
                            </tr>    
                        </table>
                        </td>
                        <td class="ncm-request_standard" colspan="2">
                            <table>
                                <tr>
                                    <td><p class="content-part">Y??u c???u ti??u chu???n:</p></td>
                                    <td><span class="font-none">{{$ncmRequest->request_standard}}</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="task-content" colspan="3"><i>M?? t??? s??? kh??ng ph?? h???p:</i></td>
                    </tr>
                    <tr>
                        <td class="field-null" colspan="3"><span style="font-weight: normal">{{$task->content}}</span></td>
                    </tr>
                    <tr>
                        <td class="ncm_requester">
                            <table>
                                <tr>
                                    <td><p class="content-part">Ng?????i ghi y??u c???u:</p></td>
                                    <td><span class="font-none">{{$ncmRequest->requester_name}}</span></td>
                                </tr>
                            </table>                        </td>
                        <td class="asign" colspan="2">K?? t??n:</td>
                    </tr>
                    <tr>
                        <td class="task-fix_content" colspan="3"><p class="content-part">H??nh ?????ng kh???c ph???c/ ph??ng ng???a</p></td>
                    </tr>
                    <tr>
                        <td class="task-fix_reason text-underline" colspan="3"><p><i>Nguy??n nh??n:</i></p></td>
                    </tr>
                    <tr>
                        <td class="field-null" colspan="3" style="border-bottom: none"><span>{{$ncmRequest->fix_reason}}</span></td>
                    </tr>
                    <tr>
                        <td class="fix_content text-underline" colspan="3"><p><i>H??nh ?????ng kh???c ph???c/ ph??ng ng???a:</i></p></td>
                    </tr>
                    <tr>
                        <td class="field-null" colspan="3"><span>{{$ncmRequest->fix_content}}</span></td>
                    </tr>
                    <tr>
                        <td class="task_duedate"><p style="padding-top: 0px">Ng??y d??? ki???n ho??n th??nh</td>
                        <td class="task_assign-depart_represent"><p>?????i di???n b??? ph???n:</p></td>
                        <td class="asign_task_duedate"><p>K?? t??n:</p></td>
                    </tr>
                    <tr>
                        <td class="field-null-2"></p><span>{{$task->duedate->toDateString()}}</span></td>
                        <td class="field-null-3"><span>{{$taskAssign->depart_represent_name}}</span></td>
                        <td class="field-null-4"></td>
                    </tr>
                    <tr>
                        <td class="ncm_result" colspan="3"><p class="content-part">Ki???m tra vi???c th???c hi???n:</p></td>
                    </tr>
                    <tr>
                        <td class="Satisfactory">
                            <table>
                                <tr>
                                    <td><label>?????t y??u c???u</label></td>
                                    <td style="width: 15px"></td>
                                    @if ($ncmRequest->test_result == 1)
                                    <td><img src="{{ URL::asset('project/images/checkbox.png') }}" class="checkbox-image"></td>
                                    @else
                                    <td class="checkbox"></td>
                                    @endif            
                                </tr>
                            </table>
                        </td>
                        <td class="Unsatisfactory" colspan="2">
                            <table>
                                <tr>
                                    <td>
                                        <label>Kh??ng ?????t y??u c???u</label>
                                    </td>
                                    <td style="width: 15px"></td>
                                    @if ($ncmRequest->test_result == 2)
                                    <td><img src="{{ URL::asset('project/images/checkbox.png') }}" class="checkbox-image"></td>
                                    @else
                                    <td class="checkbox"></td>
                                    @endif
                                </tr>
                            </table>  
                        </td>
                    </tr>
                    <tr>
                        <td class="ncm-next_measure" colspan="3"><p class="content-part">Bi???n ph??p ti???p theo:</p></td>
                    </tr>
                    <tr>
                        <td class="field-null" colspan="3"><span>{{$ncmRequest->next_measure}}</span></td>
                    </tr>
                    <tr>
                        <td class="task_duedate"><p>Ng??y ki???m tra:</p></td>
                        <td class="task_assign-depart_represent"><p>Ng?????i ki???m tra:</p></td>
                        <td class="asign_task_duedate"><p>K?? t??n:</p></td>
                    </tr>
                    <tr>
                        <td class="field-null-2"><span class="font-none">{{$task->actual_date}}</span></td>
                        <td class="field-null-3"><span>{{$taskAssign->tester_name}}</span></td>
                        <td class="field-null-4"></td>
                    </tr>
                    <tr>
                        <td class="ncm-evaluate_effect" colspan="3"><p><i>????nh gi?? hi???u qu???( Sau 03 th??ng):</i></p></td>
                    </tr>
                    <tr>
                        <td class="field-null" colspan="3"><span>{{$ncmRequest->evaluate_effect}}</span></td>
                    </tr>
                    <tr>
                        <td class="ncm-evaluate_date"><p>Ng??y ????nh gi??:</p></td>
                        <td class="task_assign-evaluater"><p>Ng?????i ????nh gi??:</p></td>
                        <td class="asign_task_duedate"><p>K?? t??n:</p></td>
                    </tr>
                    <tr>
                        <td class="field-null-2" style="border-bottom: none"><span>{{$ncmRequest->evaluate_date}}</span></td>
                        <td class="field-null-3" style="border-bottom: none"><span>{{$taskAssign->evaluater_name}}</span></td>
                        <td class="field-null-4" style="border-bottom: none"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>