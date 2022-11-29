<!doctype html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
    <script>(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)</script>
    <meta charset="utf-8">
    <style>
        .js #features {
            margin-left: -12000px; width: 100%;
        }
    </style>
    <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
       Remove this if you use the .htaccess -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>YUME - {{ $magazine->name }} - Rikkeisoft</title>
    <meta name="description" content="">
    <meta name="author" content="Rikkeisoft">

    <!--  Mobile viewport optimized: j.mp/bplateviewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" >

    <!-- CSS : implied media="all" -->
    <link rel="stylesheet" type="text/css" href="/magazine/wowbook/css/style.css" >
    <link rel="stylesheet" type="text/css" href="/magazine/wowbook/css/wow_book.css" >
    <link rel="stylesheet" type="text/css" href="/magazine/wowbook/css/preview.css?v=1"><!-- Uncomment if you are specifically targeting less enabled mobile browsers
	<link rel="stylesheet" media="handheld" href="css/handheld.css?v=2">  -->

    <!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
    <script src="/magazine/wowbook/js/modernizr-1.6.min.js"></script>

</head>
<body>
<div id="container">
<nav>
    <ul>
        <li><a id='first'     href="#" title='goto first page'   >First page</a></li>
        <li><a id='back'      href="#" title='go back one page'  >Back</a></li>
        <li><a id='next'      href="#" title='go foward one page'>Next</a></li>
        <li><a id='last'      href="#" title='goto last page'    >last page</a></li>
        <li><a id='zoomin'    href="#" title='zoom in'           >Zoom In</a></li>
        <li><a id='zoomout'   href="#" title='zoom out'          >Zoom Out</a></li>
        <li><a id='slideshow' href="#" title='start slideshow'   >Slide Show</a></li>
        <li><a id='flipsound' href="#" title='flip sound on/off' >Flip sound</a></li>
        <li><a id='fullscreen' href="#" title='fullscreen on/off' >Fullscreen</a></li>
        <li><a id='thumbs'    href="#" title='thumbnails on/off' >Thumbs</a></li>
    </ul>
</nav>
<div id="main">
    <img id='click_to_open' src="/magazine/wowbook/images/click_to_open.png" alt='click to open' />
    <div id='features'>
        
        <?php
        $bgImage = '/magazine/wowbook/images/wood.jpg';
        $firstImage = $images->first();
        ?>
        @foreach($images as $num => $image)
            <?php
            $slide_src = $image->getSrc('slide');
            $full_src = $image->getSrc('full');
            if (!$full_src) {
                $full_src = $slide_src;
            }
            if ($image->pivot->is_background) {
                $bgImage = $full_src;
            }
            ?>
            
            @if ($image->id == $firstImage->id)
            <div id="cover" style="background: #fff url({{ $slide_src }}) no-repeat"></div>
            @else
            <div class="feature responsive">
                <img src="{{ $slide_src }}" width="100%" height="100%">
            </div>
            @endif
        @endforeach
        <div class='last_cover'>
            <img src="/magazine/wowbook/images/cover_last.png" width="100%" height="100%" />
        </div>
        
        <style>
            body{
                background: url(<?php echo $bgImage ?>) center center fixed;
                background-size: cover;
            }
        </style>

    </div> <!-- features -->

</div>
<div id='thumbs_holder'>
</div>
<footer>

</footer>
</div> <!--! end of #container -->
</div>

<!-- Javascript at the bottom for fast page loading -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script type="text/javascript" src="/magazine/wowbook/wow_book.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#features').wowBook({
            height : 706
            ,width  : 1000
            ,centeredWhenClosed : true
            ,hardcovers : true
            ,turnPageDuration : 1000
            ,pageNumbers: false
            ,numberedPages : [1, -2]
            ,controls : {
                zoomIn    : '#zoomin',
                zoomOut   : '#zoomout',
                next      : '#next',
                back      : '#back',
                first     : '#first',
                last      : '#last',
                slideShow : '#slideshow',
                flipSound : '#flipsound',
                thumbnails : '#thumbs',
                fullscreen : '#fullscreen'
            }
            ,scaleToFit: "#container"
            ,thumbnailsPosition : 'bottom'
            ,onFullscreenError : function(){
                var msg="Fullscreen failed.";
                if (self!=top) msg="The frame is blocking full screen mode. Click on 'remove frame' button above and try to go full screen again."
                alert(msg);
            }
        }).css({'display':'none', 'margin':'auto'}).fadeIn(1000);
        
        $("#cover").click(function(){
            $.wowBook("#features").advance();
        });

        var book = $.wowBook("#features");
        book.zoom(1.15);

        function rebuildThumbnails(){
            book.destroyThumbnails()
            book.showThumbnails()
            $("#thumbs_holder").css("marginTop", -$("#thumbs_holder").height()/2)
        }
        $("#thumbs_position button").on("click", function(){
            var position = $(this).text().toLowerCase()
            if ($(this).data("customized")) {
                position = "top"
                book.opts.thumbnailsParent = "#thumbs_holder";
            } else {
                book.opts.thumbnailsParent = "body";
            }
            book.opts.thumbnailsPosition = position
            rebuildThumbnails();
        })
        $("#thumb_automatic").click(function(){
            book.opts.thumbnailsSprite = null
            book.opts.thumbnailWidth = null
            rebuildThumbnails();
        })
        $("#thumb_sprite").click(function(){
            book.opts.thumbnailsSprite = "images/thumbs.jpg"
            book.opts.thumbnailWidth = 136
            rebuildThumbnails();
        })
        $("#thumbs_size button").click(function(){
            var factor = 0.02*( $(this).index() ? -1 : 1 );
            book.opts.thumbnailScale = book.opts.thumbnailScale + factor;
            rebuildThumbnails();
        })
        
    });
</script>

<!-- scripts concatenated and minified via ant build script-->
<script src="/magazine/wowbook/js/plugins.js"></script>
<script src="/magazine/wowbook/js/script.js"></script>
<!-- end concatenated and minified scripts-->

<!--[if lt IE 7 ]>
<script src="js/dd_belatedpng.js"></script>
<script> DD_belatedPNG.fix('img, .png_bg'); //fix any <img> or .png_bg background-images </script>
<![endif]-->

</body>
</html>
