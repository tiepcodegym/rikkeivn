@if(isset($resourceList) && count($resourceList))
    @foreach($resourceList as $resource)
        <div class="item">
            <p>
                <span>{{ ($resource->file_name === '') ? $resource->file_url : $resource->file_name }}  ( created at: {{ $resource->created_at }})</span>
                <a class="margin-left-10" title="{{ trans('project::view.download') }}"
                   href="{{ route('project::plan.download', ['filename' => $resource->file_url]) }}?projectId={!! $projectId !!}">
                    <span style="font-size: 20px"><i class="fa fa-download"></i></span>
                </a>
                @if ($isPmOfProject)
                <span class="btn-delete-file" data-remote-url="{!! route('project::plan.delete-file', ['filename' => $resource->file_url]) !!}">
                    <i class="fa fa-trash"></i>
                </span>
                @endif
            </p>
        </div>
    @endforeach
@else
    <p style="font-size: 18px">{{ trans('project::view.No file uploaded') }}</p>
@endif