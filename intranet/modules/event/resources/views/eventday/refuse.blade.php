@extends('layouts.guest_bg')

@section('title')
{{ trans('event::view.Confirm absence', [], '', $languageView) }}
@endsection

@section('content')
<style>
    @media only screen and (max-width: 600px)  {
        .tc-title
        {
            font-size: 28px !important;
            margin-top: 50px;
        }
        .bt-right
        {
            float: left !important;
            text-align: left !important;
        }
        .br-text
        {
            display: block;
        }
        .tc-content
        {
            font-size: 13px !important;
        }
        .tc-page
        {
            padding-top: 0px !important;
        }
    }

</style>
<div class="page-main">
    <div class="tc-page">
        <h1 class="">
            <img src="https://rikkei.vn/common/images/email/event-banner-header.png" 
                 alt="Rikkeisoft Intranet" class="img-responsive" />
        </h1>

        <h2 class="tc-title">{{ trans('event::view.Sincerely thank you', [], '', $languageView) }}</h2>
        <div class="tc-content">
            {!! trans('event::view.We hope to welcome you in the future event of Rikkeisoft.', [], '', $languageView) !!}
        </div>
    </div>
</div>
@endsection

@section('wrapper_after')
<div class="bottom-fix">
    <div class="bt-left">
        <span>Copyright &copy; <?php echo date('Y');?> <span class="color-red">Rikkeisoft</span>. All rights reserved.</span>
    </div>

    <div class="bt-right">
        21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi.
        <br/>Tel: +84 4 3623 1685    |    Fax: +84 4 3623 1686    |  <a class="color-red" href="mailto:contact@rikkeisoft.com">Email us</a>
    </div>
</div>
@endsection