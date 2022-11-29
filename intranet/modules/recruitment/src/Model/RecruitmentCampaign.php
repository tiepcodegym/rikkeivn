<?php
namespace Rikkei\Recruitment\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentCampaign extends CoreModel
{
    
    use SoftDeletes;
    
    protected $table = 'recruitment_campaigns';
    
}

