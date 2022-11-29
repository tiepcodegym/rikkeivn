<li class="history-item" data-id="{{ $history->id }}">
    <div>
        <span class="time">{{ $history->created_at }}</span>
        <strong>{{ $history->name }} ({{ ucfirst(strtolower(preg_replace('/@.*/', '', $history->email))) }})</strong>
    </div>
    <p class="ws-pre-line el-short-content">{{ $history->content }}</p>
</li>
