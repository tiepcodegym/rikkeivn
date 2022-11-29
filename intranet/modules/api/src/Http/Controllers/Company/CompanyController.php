<?php

namespace Rikkei\Api\Http\Controllers\Company;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\Api\Helper\Company as CompanyHelper;
use Rikkei\Api\Helper\Contact as ContactHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Sales\Model\Company;

class CompanyController extends Controller
{
    public function getCompany()
    {
        try {
            $company = Company::getCustCompany();
            return [
                'success' => 1,
                'data' => $company
            ];

        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * getCompanyCrm
     *
     * @param  Request $request
     * @return json
     */
    public function getCompanyCrm(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'crm_ids' => 'required|array|min:1',
            'crm_ids.*' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'success' => 0,
                'messages' => $errors
            ]);
        }

        try {
            return [
                'success' => 1,
                'data' => CompanyHelper::getInstance()->getCompanyByCrmId($data['crm_ids']),
            ];

        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'messages' => [$ex->getMessage()]
            ];
        }
    }

    
    public function getCompanyById(Request $request)
    {
        $params = $request->all();
        $rules = [
            'companies_id' => 'array',
        ];    
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $data = CompanyHelper::getInstance()->getCompany($params);
            return [
                'success' => 1,
                'data' => $data,
            ];

        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'messages' => [$ex->getMessage()]
            ];
        }
    }

    public function getContact(Request $request)
    {
        $params = $request->all();
        $rules = [
            'company_id' => 'int',
        ];   
        $validator = Validator::make($params, $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $data = ContactHelper::getInstance()->getContact($params);
            return [
                'success' => 1,
                'data' => $data,
            ];

        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'messages' => [$ex->getMessage()]
            ];
        }
    }
}
