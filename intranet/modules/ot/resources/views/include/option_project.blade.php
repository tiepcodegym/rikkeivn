<option value="">&nbsp;</option>
@if (!empty($empProjects))
    @foreach ($empProjects as $projs)
        <option value="{{ $projs->project_id }}">
            {{ $projs->projName }}</option>
    @endforeach
@endif