<?php
namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\View\DocConst;
use Illuminate\Support\Facades\Storage;
use DB;

class EducationClassDocument extends CoreModel
{
    protected $table = 'education_class_documents';
    protected $fillable = [
        'id', 'class_id', 'name', 'url', 'content', 'type', 'minetype'
    ];
}
