<?php

namespace Rikkei\Welfare\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Welfare\Model\Event;

class WelfareFee extends CoreModel
{
    use SoftDeletes;

    protected $table = 'wel_fee';

    protected $fillable = [
        'wel_id',
        'empl_trial_fee',
        'empl_trial_company_fee',
        'empl_trial_number',
        'empl_offical_after_date',
        'empl_offical_fee',
        'empl_offical_company_fee',
        'empl_offical_number',
        'intership_fee',
        'intership_company_fee',
        'intership_number',
        'attachments_first_fee',
        'attachments_first_company_fee',
        'attachments_first_number',
        'attachments_second_fee',
        'attachments_second_company_fee',
        'attachments_second_number',
        'fee_total',
        'empl_trial_fee_actual',
        'empl_trial_company_fee_actual',
        'empl_trial_number_actual',
        'empl_offical_fee_actual',
        'empl_offical_company_fee_actual',
        'empl_offical_number_actual',
        'intership_fee_actual',
        'intership_company_fee_actual',
        'intership_number_actual',
        'attachments_first_fee_actual',
        'attachments_first_company_fee_actual',
        'attachments_first_number_actual',
        'attachments_second_fee_actual',
        'attachments_second_company_fee_actual',
        'attachments_second_number_actual',
        'fee_total_actual',
        'fee_estimates'
    ];


    public static function saveWelFee($welId, $welFeeArray, $isAttached = false)
    {
        $welfare = Event::find($welId);

        if (!$welfare) {
            return redirect()->back()->withErrors(trans('welfare::view.Not Found Welfare'));
        }

        $welfareFee = self::where('wel_id', $welId)->first();

        if (!$welfareFee) {
            $welFee = new self();
            $welFee->wel_id = $welId;
        } else {
            $welFee = self::find($welfareFee->id);
            if (!$isAttached) {
                $welFeeArray = array_except($welFeeArray, [
                    'attachments_first_fee_actual',
                    'attachments_first_company_fee_actual',
                    'attachments_first_number_actual',
                    'attachments_second_fee_actual',
                    'attachments_second_company_fee_actual',
                    'attachments_second_number_actual',
                ]);
            }
        }
        if (!$isAttached) {
            $welFeeArray = array_except($welFeeArray, [
                'attachments_first_fee',
                'attachments_first_company_fee',
                'attachments_first_number',
                'attachments_second_fee',
                'attachments_second_company_fee',
                'attachments_second_number',
            ]);
        }

        $welFee->fill($welFeeArray);
        $welFee->empl_offical_after_date = $welFeeArray['empl_offical_after_date'] == '' ? Null : $welFeeArray['empl_offical_after_date'];

        try {
            $welFee->save();
        } catch (Exception $ex) {
            return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
        }
    }

    /**
     * Get fee followed welfare
     *
     * @param int $welId
     * @return WelfareFee $welfarefee
     */
    public static function getFeeByWelfare($welId)
    {
        return self::where('wel_id', $welId)->first();
    }

    /**
     * Get date official employee
     *
     * @param int $welId
     * @return $empl_offical_after_date
     */
    public static function getDateOfficialEmployee($welId)
    {
        return self::select('empl_offical_after_date')
            ->where('wel_id', $welId)
            ->first();
    }

    /**
     *
     * @param int $welId
     * @param boole $actual
     * @return array
     */
    public static function exportFee($welId, $actual = true)
    {
        $welfare = Event::find($welId);
        $query = self::where('wel_id', $welId);

        // Take out the expected cost
        if (!$actual) {
            $fee = $query->select('empl_offical_number', 'empl_offical_fee', 'empl_offical_company_fee',
                            'empl_trial_number', 'empl_trial_fee', 'empl_trial_company_fee', 'intership_number',
                            'intership_fee', 'intership_company_fee', 'attachments_first_number',
                            'attachments_first_fee', 'attachments_first_company_fee')
                        ->first()->toArray();

            // No attachments
            if ($welfare->is_allow_attachments == Event::NOT_ATTACHED) {
                $posit = array_search('attachments_first_number', array_keys($fee));
                if ($posit !== false) {
                    array_splice($fee, ($posit));
                }
            }
        }

        // Take out the actual cost
        if ($actual) {
            $fee = $query->select('empl_offical_number_actual', 'empl_offical_fee_actual', 'empl_offical_company_fee_actual',
                            'empl_trial_number_actual', 'empl_trial_fee_actual', 'empl_trial_company_fee_actual',
                            'intership_number_actual', 'intership_fee_actual', 'intership_company_fee_actual',
                            'attachments_first_number_actual', 'attachments_first_fee_actual', 'attachments_first_company_fee_actual')
                        ->first()->toArray();

            // No attachments
            if ($welfare->is_allow_attachments == Event::NOT_ATTACHED) {
                $posit = array_search('attachments_first_number_actual', array_keys($fee));
                if ($posit !== false) {
                    array_splice($fee, ($posit));
                }
            }
        }

        foreach ($fee as $key => $value) {
            $fee[$key] = (int) $value;
        }
        $feeExte = array_chunk($fee, 3);

        return $feeExte;
    }
    /**
     *
     *
     * @param array $array
     * @param int $a
     * @param int $b
     */
    public static function moveElement(&$array, $a, $b)
    {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }

    /**
     *
     * @param int $welId
     * @return String
     */
    public static function getEmplOfficalAfterDate($welId)
    {
        $welfee =  self::select('empl_offical_after_date')
            ->where('wel_id', $welId)
            ->first();
        return $welfee->empl_offical_after_date;
    }

    /**
     * Get fee estimates of welfare
     *
     * @param int $welId
     * @return int
     */
    public static function getFeeEstimate($welId)
    {
        $fee = self::select('fee_estimates')
            ->where('wel_id', $welId)
            ->first();
        return (int) $fee->fee_estimates;
    }

}
