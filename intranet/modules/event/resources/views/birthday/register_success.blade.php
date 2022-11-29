@extends('layouts.guest_bg')

@section('title')
{{ trans('event::view.Register success', [], '', $languageView) }}
@endsection

@section('content')
<div class="page-main">
    <div class="tc-page">
        <h1 class="tc-logo">
            <img src="{{ URL::asset('common/images/logo_new.png') }}" 
                alt="Rikkeisoft Intranet" class="img-responsive" />
        </h1>

        @if ($languageView == "ja")
            <h2 class="tc-title">{{ trans('event::view.Sincerely thank you', [], '', $languageView) }}</h2>
            <div class="tc-content">
                {{ trans('event::view.Your registration information has been sent to the event organizers.', [], '', $languageView) }}<br/>
                {{ trans('event::view.We are looking forward to meeting you at this event.', [], '', $languageView) }}
            </div>
        @else
            <div class="tc-content">
                Thank you very much for your answer.<br/>
                We have received your attendance confirmation.
            </div>
        @endif
        
    </div>
</div>
@endsection

@section('wrapper_after')
<div class="bottom-fix">
    <div class="bt-left">
        <span>Copyright &copy; <?php echo date('Y') ?> <span class="color-red">Rikkeisoft</span>. All rights reserved.</span>
    </div>

    <div class="bt-right">
        21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi.
        <br/>Tel: +84 4 3623 1685    |    Fax: +84 4 3623 1686    |  <a class="color-red" href="mailto:contact@rikkeisoft.com">Email us</a>
    </div>
</div>
@endsection