<?php
use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();

?>
@extends($layout)

@section('css')
<style>
    .vote-mail-content * {
        line-height: 22px;
    }
</style>
@endsection

@section('content')
<?php
extract($data);
?>

<div class="vote-mail-content" style="line-height: 22px;">
    {!! $content !!}
</div>

@endsection