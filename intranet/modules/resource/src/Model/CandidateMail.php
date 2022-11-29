<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use DB;

class CandidateMail extends CoreModel
{
    protected $table = 'candidate_mail';

    protected $fillable = ['candidate_id', 'candidate_email', 'created_by', 'created_at', 'updated_at', 'type'];

    /**
     * Save data
     * @param array $data
     * @throws \Rikkei\Resource\Model\Exception
     */
    public static function saveData($data)
    {
        DB::beginTransaction();
        try {
            $time = Carbon::now()->toDateTimeString();
            $fillable = (new static)->fillable;
            $data['created_at'] = $time;
            $data['updated_at'] = $time;

            $firstData = array_only($data, $fillable);
            $dataInsert = [$firstData];

            $relateds = isset($data['relateds']) ? $data['relateds'] : null;
            if ($relateds && !$relateds->isEmpty()) {
                $dataEmp = $firstData;
                foreach ($relateds as $emp) {
                    $dataEmp['candidate_email'] = $emp->email;
                    $dataInsert[] = $dataEmp;
                }
            }

            self::insert($dataInsert);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }

    /**
     * Check sent mail to candidate
     * @param string $email
     * @param int $type
     * @return int
     */
    public static function checkExist($email, $type)
    {
        return self::where('candidate_email', $email)
                    ->where('type', $type)
                    ->count();
    }

    /**
     * Get last send by candidate and type
     * @param string $email
     * @param int $type
     * @return CandidateMail
     */
    public static function getLastSend($email, $type, $candidateId = null)
    {
        $lastSend = self::orderBy('id', 'desc');

        if (is_array($type)) {
            $lastSend->whereIn('type', $type);
        } else {
            $lastSend->where('type', $type);
        }
        if ($type != Candidate::MAIL_RECRUITER) {
            $lastSend->where('candidate_email', $email);
        }

        if ($candidateId) {
            $lastSend->where('candidate_id', $candidateId);
        }
        return $lastSend->first();
    }

    public static function getEmailSent($type, $candidateId = null)
    {
        $emails = self::where('type', $type);
        if ($candidateId) {
            $emails->where('candidate_id', $candidateId);
        }
        return $emails->get();
    }
}
