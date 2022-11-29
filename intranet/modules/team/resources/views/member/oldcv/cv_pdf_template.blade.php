<?php 
use Rikkei\Resource\View\View;
use Rikkei\Core\View\CoreUrl;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" media="all"/>
        <style>
            /* my CV*/
            *{ font-family: DejaVu Sans;}
            @page{margin: 0.5in 0.5in 0.5in 0.5in;}
            <?php echo $css;?>
        </style>   
    </head>
    <body>
        {!! $content !!}
    </body>
</html>

