<?php

namespace Rikkei\News\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostSchedule extends CoreModel
{
    use SoftDeletes;
    protected $table = 'blog_post_enabled';
    protected $fillable = ['post_id', 'publish_at'];
}
