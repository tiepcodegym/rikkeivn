<?php


namespace Rikkei\Notify\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class NotifyStoreRequest extends FormRequest
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
        return [
            'team_list' => 'required',
            'title' => 'required',
            'content' => 'required',
            'available_at' => 'after:now'
        ];
    }

    public function messages()
    {
        return [
            'team_list.required' => trans('notify::view.team'),
            'title.required' => trans('notify::view.title'),
            'content.required' => trans('notify::view.content'),
            'available_at.required' => trans('notify::view.available_at'),
            'available_at.after' => trans('notify::view.available_at_after')
        ];
    }
}
