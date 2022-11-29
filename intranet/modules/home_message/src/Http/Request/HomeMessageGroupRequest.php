<?php


namespace Rikkei\HomeMessage\Http\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Rikkei\HomeMessage\View\TypeSchedulerConst;

class HomeMessageGroupRequest extends FormRequest
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
        $rules = [
            'txt_group_name_vi' => 'string',
            'txt_group_name_en' => 'string',
            'txt_group_name_jp' => 'string',
            'txt_priority' => 'required|integer|min:0'
        ];
        $this->addRuleMessage($rules);
        return $rules;
    }

    public function messages()
    {
        return [];
    }

    /**
     * @param array $rules
     */
    private function addRuleMessage(array &$rules = [])
    {
        $name_vi = $this->input('txt_group_name_vi');
        $name_en = $this->input('txt_group_name_en');
        $name_jp = $this->input('txt_group_name_jp');
        if ((!is_string($name_vi) || trim($name_vi) == '')
            && (!is_string($name_en) || trim($name_en) == '')
            && (!is_string($name_jp) || trim($name_jp) == '')) {
            $rules['txt_group_name_vi'] = ['required'];
            $rules['txt_group_name_en'] = ['required'];
            $rules['txt_group_name_jp'] = ['required'];
        }
    }
}