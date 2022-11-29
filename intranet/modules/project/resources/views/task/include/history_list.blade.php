<?php
use Rikkei\Project\Model\TaskComment;
use Rikkei\Core\View\View;
use Rikkei\Project\View\GeneralProject;
?>

<div>
    @if (isset($creatorTask) && $creatorTask)
        <div class="item">
            <p>
                {{ trans('project::view.Created by') }}:  <strong>{{ $creatorTask->email . ' - ' . $creatorTask->name }}</strong>
            </p>
        </div>
    @endif
    @if(isset($collectionModel) && count($collectionModel))
        @foreach($collectionModel as $item)
            <?php
            $createdBy = $item->name . ' (' . GeneralProject::getNickNameNormal($item->email) . ')';
            ?>
            <div class="item">
                <p class="history-record">{{ $item->created_at }} <strong>{{ $createdBy }}</strong><br/>{!! View::nl2br($item->content) !!}</p>
            </div>
        @endforeach
    @endif
    <div class="box-body pager-full">
        @include('team::include.pager', ['domainTrans' => 'project'])
    </div>
</div>
