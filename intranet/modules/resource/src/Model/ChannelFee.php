<?php

namespace Rikkei\Resource\Model;

use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

class ChannelFee extends CoreModel
{
    protected $table = 'channel_fees';

    protected $fillable = ['channel_id', 'cost', 'start_date', 'end_date'];

    public static function saveFee($channel, $data, $flagUpdate)
    {
        $id = $channel->id;
        ChannelFee::where('channel_id', $id)->delete();
        $data = array_filter(array_values($data));
        foreach ($data as $key => $val) {
            if ($val['start_date'] > $val['end_date']) {
                throw new Exception(trans('resource::message.End date must be greater than Start date'));
            }
            if ($key > 0) {
                if ($data[$key]['start_date'] <= $data[$key - 1]['end_date']) {
                    throw new Exception(trans('resource::message.Channel.check start date'));
                }
            }
            $data[$key]['cost'] = str_replace(Channels::PRICE, '', $data[$key]['cost']);
            $data[$key]['channel_id'] = $id;
        }
        if ($flagUpdate) {
            ChannelFee::checkUpdate($channel, $data);
        }
        ChannelFee::insert($data);
    }

    /**
     * @param $channel
     * @param $data
     * @return bool
     * @throws Exception
     */
    public static function checkUpdate($channel, $data)
    {
        $data = array_values(array_filter($data));
        $candidateTbl = Candidate::getTableName();
        foreach ($channel->channelFees as $key => $value) {
            if (isset($data[$key])) {
                $candidateWorkingTimes = Candidate::select(
                    DB::raw("MAX($candidateTbl .start_working_date) as max_working_date"),
                    DB::raw("MIN($candidateTbl .start_working_date) as min_working_date")
                )
                    ->whereBetween($candidateTbl . '.start_working_date', [$value->start_date, $value->end_date])
                    ->where('channel_id', $channel->id)
                    ->first();

                if ($candidateWorkingTimes->max_working_date) {
                    if ($data[$key]['end_date'] < date('Y-m-d', strtotime($candidateWorkingTimes->max_working_date))) {
                        throw new Exception(trans('resource::message.Channel.rule.time.can not update channel'));
                    }
                }
                if ($candidateWorkingTimes->min_working_date) {
                    if ($data[$key]['start_date'] > date('Y-m-d', strtotime($candidateWorkingTimes->min_working_date))) {
                        throw new Exception(trans('resource::message.Channel.rule.time.can not update channel'));
                    }
                }
            } else {
                $checkCandidates = Candidate::whereBetween($candidateTbl . '.start_working_date', [$value->start_date, $value->end_date])
                    ->where('channel_id', $channel->id)
                    ->count();

                if ($checkCandidates > 0) {
                    throw new Exception(trans('resource::message.Channel.rule.time.delete'));
                }
            }
        }

        return true;
    }
}
