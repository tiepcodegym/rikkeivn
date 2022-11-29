@if (!isset($notify))
<li>
    <a href="#" class="media notify-item" data-id="">
        <span class="media-left pull-left notify-icon">
            <img src="" alt="icon">
        </span>
        <span class="media-body notify-body">
            <span class="notify-content hidden"></span>
            <span class="notify-time"></span>
        </span>
        <span class="mark-read" data-toggle="tooltip" title="{{ trans('notify::view.Mark read') }}" data-placement="left">
            <i class="fa fa-circle-o"></i>
        </span>
    </a>
</li>
@else
<li>
    <?php
    $link = \Rikkei\Notify\View\NotifyView::fixLink($notify->id, $notify->link);
    $content = strip_tags($notify->content);
    ?>
    <a href="{{ $link }}" @if ($notify->link === 'https://mail.google.com') target="_blank" @endif
       class="media notify-item{{ !$notify->read_at ? ' not-read' : '' }}" data-id="{{ $notify->id }}"
       title="{{ $content }}">
        <span class="media-left pull-left notify-icon">
            <img src="{{ $notify->getImage() }}" alt="icon">
        </span>
        <span class="media-body notify-body">
            <span class="notify-content hidden">{{ $content }}</span>
            <span class="notify-time" data-time="{{ $notify->updated_at->timestamp }}">{{ $notify->getDiffTime() }}</span>
        </span>
        <span class="mark-read" data-toggle="tooltip" title="{{ trans('notify::view.Mark read') }}" data-placement="left">
            <i class="fa fa-circle-o"></i>
        </span>
    </a>
</li>
@endif
