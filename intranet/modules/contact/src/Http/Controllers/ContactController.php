<?php

namespace Rikkei\Contact\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Contact\Helpers\ContactHelper;
use Auth;

class ContactController extends Controller
{
    /**
     * list contact
     */
    public function index()
    {
        return view('contact::contact.list', ['authId' => Auth::id()]);
    }

    /**
     * get list contact
     */
    public function getList()
    {
        $contact = new ContactHelper();
        return response()->json($contact->getContact(Input::get()), 200);
    }
}