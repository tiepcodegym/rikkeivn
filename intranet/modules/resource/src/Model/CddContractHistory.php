<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

class CddContractHistory extends CoreModel
{
    protected $table = 'candidate_contract_history';
    protected $fillable = ['candidate_id', 'contract_type', 'start_at', 'end_at'];
    public $timestamps = false;

    public static function insertData($candidate, $data = [])
    {
        if (!isset($data['working_type'])) {
            return;
        }
        //insert new item
        $dataUpdate = [
            'candidate_id' => $candidate->id,
            'contract_type' => $data['working_type'],
            'start_at' => $data['start_working_date']
        ];

        // check if trainee, then delete other and update trainee
        if ($data['working_type'] == getOptions::WORKING_INTERNSHIP) {
            self::where('candidate_id', $candidate->id)
                    ->where('contract_type', '!=', getOptions::WORKING_INTERNSHIP)
                    ->delete();
            $item = self::where('candidate_id', $candidate->id)
                    ->where('contract_type', getOptions::WORKING_INTERNSHIP)
                    ->first();
            if ($item) {
                $item->update($dataUpdate);
            } else {
                $item = self::create($dataUpdate);
            }
            return $item;
        }
        //update trainee item
        self::where('candidate_id', $candidate->id)
            ->where('contract_type', getOptions::WORKING_INTERNSHIP)
            ->update([
                'end_at' => Carbon::parse($data['start_working_date'])->subDay()->toDateString()
            ]);
        //if not trainee then update or create item
        $item = self::where('candidate_id', $candidate->id)
                ->where('contract_type', $data['working_type'])
                ->first();
        if ($item) {
            $item->update($dataUpdate);
        } else {
            $item = self::create($dataUpdate);
        }
        return $item;
    }
}
