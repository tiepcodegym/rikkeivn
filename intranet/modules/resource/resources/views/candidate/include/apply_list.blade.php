@if (count($applyHistory))
<div class="dropdown pull-right margin-right-30 apply-history">
    <a id="dropdown-toggle" class="dropdown-toggle cursor-pointer"
       role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-history"></i>
        {{ trans('resource::view.Apply history') }}
    </a>
    <ol class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        @php $no = count($applyHistory); @endphp
        @foreach($applyHistory as $apply)
            <li>
                @if ($candidate->id == $apply->id)
                <a class="viewing">{{ 'Lần ' . $no . ': ' .$apply->received_cv_date }}({{ trans('resource::view.Viewing') }})</a>
                @else
                    <a href="{{ $page == 'detail' ? route('resource::candidate.detail', $apply->id) : route('resource::candidate.edit', $apply->id) }}">
                        {{ 'Lần ' . $no . ': ' .$apply->received_cv_date }}
                    </a>
                @endif
            </li>
            @php $no--; @endphp
        @endforeach
    </ol>
</div>
@endif
