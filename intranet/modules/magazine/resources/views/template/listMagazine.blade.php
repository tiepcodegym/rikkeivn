<?php //Eager loading for magazine
        use Rikkei\Magazine\Model\Magazine;

        $countMagazine = Magazine::count();
        $Magazine = Magazine::with(
            ['magazineImages' => function ($query) {
                $query->where('order_number', '=', 0);
            }]
        )->get()->sortByDesc("id");
?>
@if($countMagazine > 0)
<div class="box box-primary">
<div class="magazine-manager-body">
    <div class="row">
        <div class="magazine-manager-wrapper col-xs-10 col-centered">
            @foreach($Magazine as $magazine)
                <?php
                     $magazineImages = $magazine->magazineImages->first();
                     $magazineId = $magazine->id;
                ?>
                @if($magazineImages)
                <div class="magazine-wrapper">
                    <h4>{{ $magazine['name'] }}</h4>
                    <div class="magazine-cover thumbnail">
                        <a href="{{ route('magazine::read', $magazineId) }}" target="_blank">
                            <img class="img-responsive" src='{{ asset("magazine/yume/{$magazine->id}/{$magazineImages["image_name"]}") }}'>
                        </a>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
</div>

<input id="token" type="hidden" value="{{ Session::token() }}" />
<!-- Check value if press back button then reload page -->
<input type="hidden" id="refreshed" value="no">

<!-- Styles -->
@section('css')
@parent
<link href="{{ asset('magazine/magazineCss/style.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" type="text/css" href="{{ asset('magazine/plugins/slick/slick-theme.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('magazine/plugins/slick/slick.css') }}"/>
@endsection

<!-- Script -->
@section('script')
@parent
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="{{ asset('magazine/plugins/slick/slick.min.js') }}"></script>
<script>
$(document).ready(function(){
  $('.magazine-manager-wrapper').slick({
       infinite: true,
       slidesToShow: 3
  });
  
});
</script>
<script type="text/javascript">
    onload=function(){
        var e=document.getElementById("refreshed");
        if (e.value=="no") e.value="yes";
        else {
            e.value="no";
            $('.btn-create').prop('disabled',true);
            location.reload();}
    }
    
</script>
@endsection
@endif
