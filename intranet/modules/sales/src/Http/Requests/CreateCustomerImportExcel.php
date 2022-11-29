<?php

namespace Rikkei\Sales\Http\Requests;
use Rikkei\Sales\Model\Customer;

/**
 * Description of CreateCustomerImportExcel
 */
class CreateCustomerImportExcel
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

    public function rules($params)
    {
        $rules = [
            'id' => 'nullable',

        ];

        return $rules;
    }

    public function messages()
    {
        return [
            
        ];
    }

}

