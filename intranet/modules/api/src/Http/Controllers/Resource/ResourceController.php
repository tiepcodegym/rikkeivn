<?php

namespace Rikkei\Api\Http\Controllers\Resource;

use Rikkei\Api\Helper\Resource;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Resource\Model\ResourceRequest;

class ResourceController extends Controller
{
    /**
     * Get assets list
     * @params asset codes
     */
    public function getRecruitmentData()
    {
        try {
            $resource = Resource::getInstance()->getData();
            return [
                'success' => 1,
                'data' => $resource,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * API get data request approved from intranet
     */
    public function getDataRequestApproved()
    {
        try {
            $resourceRequest = ResourceRequest::getInstance();
            $data = $resourceRequest->getDataRequestApproved();

            return [
                'success' => 1,
                'data' => $resourceRequest->formatDataRequestApproved($data),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => $ex->getMessage()
            ];
        }
    }
}
