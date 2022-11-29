<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Candidate;

class RicodeTest extends CoreModel {
    protected $table = 'ricode_test';
    
    protected $fillable = [
        'id',
        'level_easy', 
        'level_medium', 
        'level_hard', 
        'duration',
        'url',
        'url_view_source',
        'exam_id',
        'candidate_id',
        'title',
        'start_time',
        'total_correct_answers',
        'penalty_point',
        'time_remaining'
    ];

    public function candidate() {
        return $this->hasOne(Candidate::class, 'id', 'candidate_id');
    }
}