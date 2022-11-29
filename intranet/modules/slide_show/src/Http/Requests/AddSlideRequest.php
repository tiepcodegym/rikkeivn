<?php

namespace Rikkei\SlideShow\Http\Requests;

use Rikkei\SlideShow\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;
use Rikkei\SlideShow\View\ValidatorExtend;
use Rikkei\SlideShow\Model\Slide;

class AddSlideRequest extends Request
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
    public static function rules($data)
    {
        $rules = [
            'title' => 'required',
        ];
        if (isset($data['type'])) {
            $rules['type'] = 'required|type_slide';
        }
//        if (isset($data['name_customer'])) {
//            $rules['name_customer'] = 'required';
//        }
        if (isset($data['language'])) {
            $rules['language'] = 'required|type_lang';
        }
        if (isset($data['option'])) {
            $rules['option'] = 'option_slide';
        }
        if (isset($data['option']) && 
            isset($data['quotation']) && 
            $data['option'] == Slide::OPTION_QUOTATIONS
        ) {
            $rules['quotation'] = 'slide_quotation';
        }
        if (isset($data['option']) && 
            isset($data['birthday']) && 
            $data['option'] == Slide::OPTION_BIRTHDAY
        ) {
            $rules['birthday'] = 'slide_birthday';
        }
        if (isset($data['id'])) {
            $rules['hour_start'] = 'required|date_format:H:i|before:hour_end|unique_hour_start:'.$data['date'].','. $data['id'].','. $data['hour_end'];
            $rules['hour_end'] = 'required|date_format:H:i';
            $rules['repeat'] = 'repeat_slide|unique_repeat:'.$data['date'].','. $data['id'].','. $data['hour_start'].','. $data['hour_end'];
        } else {
            $id = null;
            $rules['hour_start'] = 'required|date_format:H:i|before:hour_end|unique_hour_start:'.$data['date'].','. $id.','. $data['hour_end'];
            $rules['hour_end'] = 'required|date_format:H:i';
            $rules['repeat'] = 'repeat_slide|unique_repeat:'.$data['date'].','. $id.','. $data['hour_start'].','. $data['hour_end'];
        }
        return $rules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'title.required' => Lang::get('slide_show::message.Title is field required'),
            'hour_start.required' => Lang::get('slide_show::message.Hour start is field required'),
            'hour_end.required' => Lang::get('slide_show::message.Hour end is field required'),
            'repeat.repeat_slide' => Lang::get('slide_show::message.Please choose correct type repeat'),
            'repeat.unique_repeat' => Lang::get('slide_show::message.Do not allow repeat hourly'),
            'type.type_slide' => Lang::get('slide_show::message.Please choose correct type slide'),
            'hour_start.unique_hour_start' => Lang::get('slide_show::message.Please choose other periods'),
            'option.option_slide' => Lang::get('slide_show::message.Please choose correct option slide'),
            'language.required' => Lang::get('slide_show::message.Language is field required'),
            'language.type_lang' =>  Lang::get('slide_show::message.Please choose correct type language'),
            'quotation.slide_quotation' =>  Lang::get('slide_show::message.Please fill in at least one quotation having content'),
            'birthday.slide_birthday' =>  Lang::get('slide_show::message.Birthday content is required'),
        ];
    }

    /**
     * validate data 
     * @param array
     * @return validator
     */
    public static function validateData($data = array())
    {
        ValidatorExtend::extendValidator();
        $rules = self::rules($data);
        return Validator::make($data, $rules, self::messagesValidates());
    }
}
