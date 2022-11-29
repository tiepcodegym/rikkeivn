<?php

namespace Rikkei\Core\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Model\EmailQueue;

/**
 * Description of EmailQueuesController
 *
 * @author 
 */
class EmailQueuesController extends Controller
{
    public function _construct()
    {
        Breadcrumb::add('Email Queues List');
    }

    public function index()
    {
        return view('core::email-queues', [
            'collectionModel' => EmailQueue::getAllEmailQueues()
        ]);
    }
}
