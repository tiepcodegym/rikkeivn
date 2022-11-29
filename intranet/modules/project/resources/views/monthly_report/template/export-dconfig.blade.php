<?php
use Rikkei\Project\View\MRExcel;

if ($dConfig) {
    $dConfig = json_decode($dConfig->value, true);
} else {
    $dConfig = [];
    if (!$teams->isEmpty()) {
        foreach ($teams as $team) {
            foreach ($listRoles as $key => $label) {
                $dConfig[$team->id][$key] = 1;
            }
        }
    }
}
?>

<table style="border-collapse: collapse;">
    <thead>
        <tr>
            <th>Team_Role</th>
            <th>Config</th>
        </tr>
    </thead>
    <tbody>
        @if (!$teams->isEmpty())
            @foreach ($teams as $team)
                @foreach ($listRoles as $key => $label)
                <tr>
                    <td>{{ MRExcel::shortName($team->name) . '_' . $label }}</td>
                    <td>{{ isset($dConfig[$team->id][$key]) ? $dConfig[$team->id][$key] : null }}</td>
                </tr>
                @endforeach
            @endforeach
        @endif
    </tbody>
</table>
