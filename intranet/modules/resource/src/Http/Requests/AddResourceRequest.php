<?php

namespace Rikkei\Resource\Http\Requests;

use Rikkei\Resource\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\View\ValidatorExtend;

class AddResourceRequest extends Request
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
    
    public static function rules($input)
    {
        $arrayRules = [];
        if (isset($input['old_channel_id'])) {
            $arrayRules['channel_id'] = 'required|unique:request_channel,channel_id,'.(int)$input['rc_id'].',id,request_id,' . (int)$input['request_id'];
        } else {
            $arrayRules['channel_id'] = 'required|unique:request_channel,channel_id,NULL,id,request_id,' . (int)$input['request_id'];
        }
        
        $arrayRules['url'] =  'required|check_url:'.$input['url'];
        $arrayRules['cost'] =  'required';
        return $arrayRules;
    }  

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {   
        $arrayMessages = [];
        $arrayMessages['channel_id.required'] = trans('resource::message.Channel is required');
        $arrayMessages['channel_id.unique'] =  trans('resource::message.This channel is exist');
        $arrayMessages['url.required'] =  trans('resource::message.The channel url of request is required');
        $arrayMessages['url.check_url'] = trans('resource::message.Url is invalid');
        $arrayMessages['cost.required'] =  trans('resource::message.The channel cost is required');
        return $arrayMessages;
    }

    /**
     * validate data 
     * @param array
     * @return validator
     */
    public static function validateData($data = array())
    {
        ValidatorExtend::addUrl();
        $rules = self::rules($data);
        return Validator::make($data, $rules, self::messagesValidates());
    }
}
