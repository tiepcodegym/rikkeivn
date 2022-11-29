<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class CandidateMailInterviewer extends CoreModel
{
    protected $table = 'candidate_mail_interviewer';

    protected $fillable = ['candidate_id', 'interviewer_id', 'start_at', 'end_at', 'room_id', 'title', 'description'];

    public $timestamps = true;

    /**
     * Get interviewers has sent mail
     *
     * @param int $candidateId
     * @return CandidateMailInterviewer collection
     */
    public static function getInterviewerSent($candidateId)
    {
        return self::where('candidate_id', $candidateId)->get();
    }

    /**
     * Get list id of interviewers has sent mail
     *
     * @param int $candidateId
     * @return CandidateMailInterviewer collection
     */
    public static function getInterviewerIdSent($interviewersSent)
    {
        $ids = [];
        foreach ($interviewersSent as $person) {
            $ids[] = $person->interviewer_id;
        }
        return $ids;
    }

    /**
     * Get interviewer have sent notice mail but mail content changed from last time
     *
     * @param CandidateMailInterviewer $interviewersSent
     * @param array $dataCurrent
     * @return array
     */
    public static function getInterviewersResendMail($interviewersSent, $dataCurrent)
    {
        $ids = [];
        foreach ($interviewersSent as $person) {
            if ($person->title != $dataCurrent['title']
                || strtotime($person->start_date) != strtotime($dataCurrent['startDate'])
                || strtotime($person->end_date) != strtotime($dataCurrent['endDate'])
                || $person->room_id != $dataCurrent['roomId']
            ) {
                $ids[] = $person->interviewer_id;
            }
        }

        return $ids;
    }

    /**
     * Insert data
     *
     * @param int $candidateId
     * @param array $interviewersId
     * @param array $dataInsert
     * @throws \Rikkei\Resource\Model\Exception
     * @return void
     */
    public static function insertData($candidateId, $interviewersId, $dataInsert)
    {
        $data = [];
        try {
            foreach ($interviewersId as $interviewerId) {
                $data[] = [
                    'interviewer_id' => $interviewerId,
                    'candidate_id' => $candidateId,
                    'title' => $dataInsert['title'],
                    'start_date' => $dataInsert['startDate'],
                    'end_date' => $dataInsert['endDate'],
                    'description' => $dataInsert['description'],
                    'room_id' => $dataInsert['roomId'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            if (count($data)) {
                self::insert($data);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }

    /**
     * Delete from interviewersId
     *
     * @param int $candidateId
     * @param array|null $interviewersId
     * @return void
     */
    public static function deleteData($candidateId, $interviewersId = null)
    {
        $delete = self::where('candidate_id', $candidateId);
        if ($interviewersId) {
            $delete->whereIn('interviewer_id', $interviewersId);
        }

        $delete->delete();
    }
}
