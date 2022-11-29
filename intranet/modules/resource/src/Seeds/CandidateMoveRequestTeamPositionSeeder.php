<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Resource\Model\CandidatePosition;
use DB;
use Rikkei\Resource\View\getOptions;

class CandidateMoveRequestTeamPositionSeeder extends CoreSeeder
{
    /**
     * In table candidate
     * Move request_id to table candidate_request
     * Move team_id to table candidate_team
     * Move position_apply to table candidate_pos
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        DB::beginTransaction();
        try {
            $allCandi = Candidate::all(['id', 'request_id', 'team_id', 'position_apply', 'status']);
            if (count($allCandi)) {
                foreach ($allCandi as $candidate) {
                    if ($candidate->request_id) {
                        $candiRequest = new CandidateRequest();
                        $candiRequest->candidate_id = $candidate->id;
                        $candiRequest->request_id = $candidate->request_id;
                        $candiRequest->save();
                    }
                    if ($candidate->team_id) {
                        $candiTeam = new CandidateTeam();
                        $candiTeam->candidate_id = $candidate->id;
                        $candiTeam->team_id = $candidate->team_id;
                        $candiTeam->save();
                    }
                    if ($candidate->position_apply) {
                        $candiPos = new CandidatePosition();
                        $candiPos->candidate_id = $candidate->id;
                        $candiPos->position_apply = $candidate->position_apply;
                        $candiPos->save();
                    }
                    if ($candidate->status != getOptions::END && $candidate->status != getOptions::WORKING) {
                        $candidate->request_id = null;
                        $candidate->team_id = null;
                        $candidate->position_apply = null;
                        $candidate->save();
                    }
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
        
    }
}
