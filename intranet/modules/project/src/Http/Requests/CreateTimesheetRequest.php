<?php

namespace Rikkei\Project\Http\Requests;


class CreateTimesheetRequest extends Request
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
    public static function rules()
    {
        return [
            'line_item.*.details.*.working_hour' => 'numeric|max:24',
            'line_item.*.details.*.ot_hour' => 'numeric|max:24',
            'line_item.*.details.*.overnight_hour' => 'numeric|max:24',
            'line_item.*.details.*.note' => 'string|max:255',
            'status' => 'required|integer|min:1|max:2'
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [];
    }
}
