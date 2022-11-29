<?php
use Rikkei\Resource\View\getOptions;

$length = count($groupItems);
?>

@foreach($groupItems as $index => $sItem)
<?php
//result
$labelResult = 'N/A';
$classResult = '';
if ($sItem->test_result == getOptions::RESULT_PASS) {
    $labelResult = strtoupper(trans('resource::view.Candidate.Detail.Pass'));
    $classResult = 'text-green';
} elseif ($sItem->test_result == getOptions::RESULT_FAIL) {
    $labelResult = strtoupper(trans('resource::view.Candidate.Detail.Fail'));
    $classResult = 'text-red';
}
?>
<tr data-id="{{ $sItem->id }}">
    @if ($index == 0)
    <td rowspan="{{ $length }}" class="text-center">
        <strong>{{ $sItem->test_time ? $sItem->test_time->format('d/m') : '' }}</strong>
    </td>
    @endif
    <td class="text-center"><strong>{{ $sItem->test_time ? $sItem->test_time->format('H\hi') : '' }}</strong></td>
    <td class="text-center">
        @if ($sItem->test_result != getOptions::RESULT_DEFAULT)
        <i class="fa fa-check text-green"></i>
        @endif
    </td>
    <td class="text-center">
        @if ($sItem->interview_result != getOptions::RESULT_DEFAULT)
        <i class="fa fa-check text-green"></i>
        @endif
    </td>
    <td>
        @if ($hasPermissDetail)
        <a target="_blank" href="{{ route('resource::candidate.detail', ['id' => $sItem->id]) }}">{{ $sItem->fullname }}</a>
        @else
        {{ $sItem->fullname }}
        @endif
    </td>
    <td>{{ $sItem->mobile }}</td>
    <td>{{ $sItem->email }}</td>
    <td>{{ getOptions::getInstance()->getRole($sItem->position_apply) }}</td>
    <td class="white-space-pre">{{ $sItem->test_note }}</td>
    <td data-result="{{ $sItem->test_result }}" class="{{ $classResult }}">
        {{ $labelResult }}
    </td>
</tr>
@endforeach

