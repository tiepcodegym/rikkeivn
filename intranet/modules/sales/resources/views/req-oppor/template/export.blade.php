<?php
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Resource\Model\Candidate;
use Carbon\Carbon;

$listRoles = ProjectMember::getTypeMember();
$listTypes = Candidate::listTypes();
$creator = $item->creator;
?>

<table border="1">
    <tbody>
        <tr>
            <td>
                <img src="{{ asset('common/images/logo-rikkei.png') }}" >
            </td>
            <td></td>
            <td rowspan="2" colspan="5" s='{{ json_encode(["font" => ["sz" => 25, "color" => ["rgb" => "ff0000"]]]) }}'>
                <h1>{{ strtoupper(trans('sales::view.Project information')) }}</h1>
            </td>
        </tr>
        <tr></tr>
        <tr>
            <td></td>
            <td></td>
            <td rowspan="2" colspan="5" s='{{ json_encode(["font" => ["sz" => 20, "color" => ["rgb" => "ff0000"]]]) }}'>
                <h2>{{ $item->name }}</h2>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr s='{{ json_encode(["font" => ["sz" => 13]]) }}'>
            <td></td>
            <td>{{ trans('sales::view.Period') }}</td>
            <td t="s" z="%s">{{ Carbon::parse($item->to_date)->format('M - Y') }}</td>
        </tr>
        <tr s='{{ json_encode(["font" => ["sz" => 13]]) }}'>
            <td></td>
            <td rowspan="3">{{ trans('sales::view.Contact information') }}</td>
            <td>{{ trans('sales::view.Name') }}</td>
            <td>{{ $creator ? $creator->name : null }}</td>
        </tr>
        <tr s='{{ json_encode(["font" => ["sz" => 13]]) }}'>
            <td></td>
            <td>{{ trans('sales::view.Email address') }}</td>
            <td>{{ $creator ? $creator->email : null }}</td>
        </tr>
        <tr s='{{ json_encode(["font" => ["sz" => 13]]) }}'>
            <?php
            $empContact = $creator ? $creator->getItemRelate('contact') : null;
            ?>
            <td></td>
            <td>{{ trans('sales::view.Mobile') }}</td>
            <td t="s">{{ $empContact ? $empContact->mobile_phone : null }}</td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        
        <tr s='{{ json_encode([
            "fill" => [
                "bgColor" => ["rgb" => "009933"],
                "patternType" => "solid"
            ],
            "font" => [
                "color" => ["rgb" => "ffffff"]
            ]
        ]) }}'>
            <th>No.</th>
            <th>ID</th>
            <th>{{ trans('sales::view.Detail') }}</th>
            <th>{{ trans('sales::view.Number') }}</th>
            <th>{{ trans('sales::view.Program language') }}</th>
            <th>{{ trans('sales::view.Role') }}</th>
            <th>{{ trans('sales::view.Expertise level') }}</th>
            <th>{{ trans('sales::view.English level') }}</th>
            <th>{{ trans('sales::view.Japanese level') }}</th>
        </tr>

        @if (!$memberExports->isEmpty())
            <?php $rowCount = $memberExports->count(); ?>
            @foreach ($memberExports as $order => $member)
            <tr>
                <td>{{ $order + 1 }}</td>
                @if ($order == 0)
                <td rowspan="{{ $rowCount }}">{{ $item->code }}</td>
                <td rowspan="{{ $rowCount }}">{{ $item->detail }}</td>
                @endif
                <td>{{ $member->number }}</td>
                <td>{{ $member->prog_names }}</td>
                <td>{{ isset($listRoles[$member->role]) ? $listRoles[$member->role] : null }}</td>
                <td>{{ isset($listTypes[$member->member_exp]) ? $listTypes[$member->member_exp] : null }}</td>
                <td>{{ $member->english_level }}</td>
                <td>{{ $member->japanese_level }}</td>
            </tr>
            @endforeach
        @else
        <tr>
            <td colspan="9">{{ trans('sales::message.Not found item') }}</td>
        </tr>
        @endif
    </tbody>
</table>

