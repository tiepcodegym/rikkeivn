<?php
use Rikkei\Resource\Model\Candidate;

$compareList = Candidate::getCompareList();
$arrayGreaterSmaller = [ 
    Candidate::COMPARE_GREATER, 
    Candidate::COMPARE_SMALLER, 
    Candidate::COMPARE_GREATER_EQUAL, 
    Candidate::COMPARE_SMALLER_EQUAL 
];
?>

<select class="form-control compare-list">
    @foreach ($compareList as $compare)
    @if (!(isset($equal) && $compare == Candidate::COMPARE_EQUAL) && !(isset($greaterSmaller) && in_array($compare, $arrayGreaterSmaller)) && !(isset($like) && $compare == Candidate::COMPARE_LIKE))
    <option value="{{ $compare }}">{{ $compare }}</option>
    @endif
    @endforeach
</select>
