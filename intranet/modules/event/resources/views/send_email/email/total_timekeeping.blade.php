<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Core\Model\CoreConfigData;

$layout = EmailQueue::getLayoutConfig();
$titleIndex = ViewEvent::getHeadingIndexTotalTimekeeping();
$content = CoreConfigData::getValueDb('event.total_timekeeping.email_content');
if(isset($data['reg_replace']) && $data['reg_replace'] &&
    isset($data['reg_replace']['patterns']) && $data['reg_replace']['patterns'] && 
    isset($data['reg_replace']['replaces']) && $data['reg_replace']['replaces']
) {
    $content = preg_replace(
        $data['reg_replace']['patterns'], 
        $data['reg_replace']['replaces'], 
        $content
    );
}
?>
@extends($layout)

@section('content')
<?php
extract($data);
?>

<div style="line-height: 17px;">
    {!! $content !!}
</div>
@if (isset($columnsHeading) && count($columnsHeading))
<table class="sabatical_tbl" style="border-collapse: collapse; border: 2px solid #767676; width: 100%;">
    <tbody>
        @foreach($columnsHeading as $index => $colName)
        <?php
            $value = isset($data[$index]) ? $data[$index] : null;
        ?>
            <tr>
                <td style="padding: 8px 10px; border: 1px solid #767676;">{{ $colName }}</td>
                <td style="padding: 8px 10px; border: 1px solid #767676; text-align: right;">{{ $value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection
