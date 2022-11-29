<?php

namespace Rikkei\Resource\Http\Requests;


class RecommendPost extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fullname' => 'required|string',
            'email' => 'required|string',
            'mobile' => 'required|string',
            'birthday' => 'date|string',
            'gender' => 'required|integer',
            'skype' => 'string',
            'other_contact' => 'string',
            'recruiter' => 'string',
            'experience' => 'integer',
            'university' => 'string',
            'certificate' => 'string',
            'old_company' => 'string',
            'comment' => 'required|string',
            'languages' => 'string',
        ];
    }
}