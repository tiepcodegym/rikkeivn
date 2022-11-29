<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Event\View\MailEmployee;
use Rikkei\Team\Model\Employee;

if (!isset($data['employee'])) {
    throw new Exception('Not found emploee to send email');
}
if (!is_object($data['employee'])) {
    $employee = Employee::find($data['employee']);
} else {
    $employee = $data['employee'];
}
if (!$employee) {
    throw new Exception('Not found employee to send email');
}
$layout = EmailQueue::getLayoutConfig(5);
$dataContent = MailEmployee::patternsNotiBirtday($employee, [
    'content' => CoreConfigData::getValueDb('event.mail.bitrhday.employee.content')
], $result);
$old = isset($result['old']) ? $result['old'] : 0;
$oldString = str_split($old);
?>
@extends($layout)

@section('css')
<style>
    p {
        /*margin: 8px auto;*/
    }
</style>
@endsection

@section('before_content')
<table align="center">
    <tr>
        <td width="600" height="55">&nbsp;</td>
    </tr>
    <tr>
        <td>
            <div style="text-align: center;">
                <div style="background: url({{ URL::asset('assets/event/gift-2.png') }}) no-repeat center; height: 129px; background-size: cover;">
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td width="600" height="0">&nbsp;</td>
    </tr>
    @if ($employee->gender != Employee::GENDER_FEMALE || $old <= MailEmployee::OLD_THRESHOLD)
    <tr>
        <td>
            <div style="text-align: center;">
                @foreach ($oldString as $number)
                    <img src="{{ URL::asset('assets/event/' . (int) $number . '.png') }}" 
                        style="width: auto; max-height: 100px"/>
                @endforeach
            </div>
        </td>
    </tr>
    @endif
</table>

@endsection

@section('content')
{!! $dataContent['content'] !!}
@endsection

