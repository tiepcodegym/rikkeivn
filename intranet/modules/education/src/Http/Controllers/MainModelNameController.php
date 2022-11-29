<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Rikkei\Education\Http\Service\MainModelNameService;

class MainModelNameController extends Controller
{
    protected $mainModelNameService;

    public function __construct(MainModelNameService $mainModelNameService)
    {
        $this->mainModelNameService = $mainModelNameService;
    }

    /**
     * Send Message To Bot
     *
     * @param Request $request
     *
     * @return object
     */
    public function doHere(Request $request)
    {
        $response = $this->mainModelNameService->doHere($request);

        return $response;
    }
}
