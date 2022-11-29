<?php


namespace Rikkei\HomeMessage\Http\Request;


use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Exception;
use Illuminate\Support\Facades\Lang;
use Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\HomeMessage\View\TypeSchedulerConst;

class InsertHomeMessageRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('after_or_equal', function ($attribute, $value, $parameters, $validator) {
            return strtotime($validator->getData()[$parameters[0]]) <= strtotime($value);
        });
        $rules = [
            'message_vi' => 'string|max:255',
            'message_en' => 'string|max:255',
            'message_jp' => 'string|max:255',
            'group_id' => 'required|exists:m_home_message_groups,id',
            'team_id' => 'required|array',
            'team_id.*' => 'integer|exists:teams,id',
            'icon_url' => 'file|dimensions:width=100,height=100',
        ];

        $this->addRuleMessage($rules);
        $this->addRuleInsertOrEdit($rules);
        return $rules;
    }

    public function messages()
    {
        return [
            'message.required' => Lang::get('HomeMessage::message.The content is required'),
            'message_vi.required' => Lang::get('HomeMessage::message.The content_vi is required'),
            'message_en.required' => Lang::get('HomeMessage::message.The content_en is required'),
            'message_jp.required' => Lang::get('HomeMessage::message.The content_jp is required'),
            'message_vi.max' => Lang::get('HomeMessage::message.The content_vi length max is 255'),
            'message_en.max' => Lang::get('HomeMessage::message.The content_en length max is 255'),
            'message_jp.max' => Lang::get('HomeMessage::message.The content_jp length max is 255'),
            'group_id.required' => Lang::get('HomeMessage::message.The group_id is required'),
            'team_id.required' => Lang::get('HomeMessage::message.The team_id is required'),
        ];
    }


    /**
     * @param array $rules
     */
    private function addRuleMessage(array &$rules = [])
    {
        $message_vi = $this->input('message_vi');
        $message_en = $this->input('message_en');
        $message_jp = $this->input('message_jp');
        if ((!is_string($message_vi) || trim($message_vi) == '')
            && (!is_string($message_en) || trim($message_en) == '')
            && (!is_string($message_jp) || trim($message_jp) == '')) {
            $rules['message'] = ['required'];
        }
    }

    private function addRuleInsertOrEdit(array &$rules = [])
    {
        $iconOld = $this->input('icon_url_old');
        if (trim($iconOld) == '') {
            $rules['icon_url'] = $rules['icon_url'] . '|required';
            unset($rules['icon_url']);
        }
    }


    private function addRuleTypeScheduler(array &$rules = [])
    {
        $typeScheduler = $this->input('type_scheduler');
        if ($typeScheduler == TypeSchedulerConst::ONLY) {
            //Todo add rules only
            $ruleTmp = [
                'priority' => 'required|integer|min:0',
                'start_at' => 'required|date_format:H:i A',
                'txt_date_apply' => 'required|string',
                'end_at' => 'required|date_format:H:i A|after_or_equal:start_at',
            ];
            $txt_date_apply = $this->input('txt_date_apply');
            $txt_date_apply = preg_split('/\r\n|\r|\n/', $txt_date_apply);
            foreach ($txt_date_apply as $date) {
//                if (count(trim($date)) == 5) {
//                    //Todo check format H:i A
//                } else {
//                    //Todo check format d-m-Y
//                }
            }
            $rules = array_merge($rules, $ruleTmp);
        } elseif ($typeScheduler == TypeSchedulerConst::REPEAT) {
            //Todo add rules repeat
            $ruleTmp = [
                'priority' => 'required|integer|min:0',
                'start_at' => 'required|date_format:H:i A',
                'end_at' => 'required|date_format:H:i A|after_or_equal:start_at',
            ];
            $rules = array_merge($rules, $ruleTmp);
        } else {
            //Todo not check
        }
    }


}