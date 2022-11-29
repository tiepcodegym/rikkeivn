<?php

namespace Rikkei\Notes\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Rikkei\Notes\Model\ReleaseNotes;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class ReleaseNotesController extends Controller {

    /**
     * list release notes
     */
    public function index() {
        Breadcrumb::add('Release Notes', URL::route('notes::notes.index'));
        $listData = ReleaseNotes::getData();
        return view('notes::user.index', [
            'titleHeadPage' => Lang::get('notes::view.List notes'),
            'dataReleaseNotes' => $listData
        ]);
    }

    /**
     * detail release notes
     */
    public function getDetail($id) {
        Breadcrumb::add('Release Notes', URL::route('notes::notes.index'));
        Breadcrumb::add('Detail');
        $data = ReleaseNotes::find($id);
        if ($data == null) {
            $response['message'] = Lang::get('notes::message.Not found item');
            Session::flash(
                    'messages', [
                'errors' => [
                    $response['message']
                ]
                    ]
            );
            return back();
        }
        return view('notes::user.detail', [
            'titleHeadPage' => Lang::get('notes::view.Detail notes'),
            'data' => $data
        ]);
    }
}
