<tr>
    <td>
        {{ $file->pivot->version }}
        @if ($file->pivot->is_current)
        <span class="label label-success">{{ trans('doc::view.Current version') }}</span>
        @endif
    </td>
    <td>
        <a @if($file->type == 'link') target="_blank" @endif href="{{ $file->downloadLink($item->id) }}">{{ $file->name }}</a>
    </td>
    <td>{{ ucfirst(strtolower(preg_replace('/@.*/', '', $file->email))) }}</td>
    <td>{{ $file->created_at }}</td>
    <td class="white-space-nowrap text-right">
        @if ($permisEdit && !$file->pivot->is_current)
            {!! Form::open([
                'method' => 'post',
                'route' => ['doc::admin.file.set_current', $item->id, $file->id],
                'class' => 'form-inline mark-as-current-form no-validate'
            ]) !!}
            <button type="submit" class="btn btn-primary" title="{{ trans('doc::view.Set as current') }}"><i class="fa fa-check-square"></i></button>
            {!! Form::close() !!}

            {!! Form::open([
                'method' => 'delete',
                'route' => ['doc::admin.file.delete', $item->id, $file->id],
                'class' => 'form-inline no-validate'
            ]) !!}
            <button type="submit" class="btn-delete delete-confirm" title="{{ trans('doc::view.Delete') }}"><i class="fa fa-trash"></i></button>
            {!! Form::close() !!}
        @endif
    </td>
</tr>
