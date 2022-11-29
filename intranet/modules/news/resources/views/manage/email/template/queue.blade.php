<?php
use Rikkei\News\View\ViewNews;
use Exception as ExceptionRK;

$template = ViewNews::existsTemplate(true);
if (!$template) {
    throw new ExceptionRK('Not found template of email post');
}
echo $template;