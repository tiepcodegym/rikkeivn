<?php
$count_normal = $normalAttrs->count();
?>
<tfoot>
    <tr>
        <td colspan="3" class="fixed-col"></td>
        @if (!$normalAttrs->isEmpty())
            @if (isset($create_team))
            <td colspan="{{ $count_normal }}" class="text-right"><strong>{{trans('project::me.Average')}}</strong></td>
            @else
            <td colspan="{{ $count_normal + 1 }}" class="text-right"><strong>{{trans('project::me.Average')}}</strong></td>
            @endif
        @endif
        
        @if (!$performAttrs->isEmpty())
            @foreach ($performAttrs as $attr)
                <td class="_pf_avg text-center" data-attr="{{$attr->id}}"></td>
            @endforeach
        @endif
        <td class="_pf_person_avg_col _none"></td>
        <td colspan="10"></td>
    </tr>
</tfoot>
