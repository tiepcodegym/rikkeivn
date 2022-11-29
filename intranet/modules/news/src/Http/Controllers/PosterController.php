<?php

namespace Rikkei\News\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\News\Model\Poster;
use Rikkei\News\View\ViewPoster;
use Rikkei\Core\Http\Controllers\Controller;


class PosterController extends Controller
{
    private $viewPoster;

    public function __construct(ViewPoster $viewPoster)
    {
        parent::__construct();
        Menu::setActive('admin', 'news');
        $this->viewPoster = $viewPoster;
    }

    public function index()
    {
        $collection = $this->viewPoster->index();
        $listStatus = Poster::getStatus();
        $listStatusLabel = Poster::getStatusLabel();

        return view('news::poster.index', [
            'collectionModel' => $collection,
            'listStatus' => $listStatus,
            'listStatusLabel' => $listStatusLabel
        ]);
    }

    public function create()
    {
        Breadcrumb::add(trans('news::view.List poster'), URL::route('news::posters.index'));
        Breadcrumb::add(trans('news::view.Create Poster'));
        $poster = new Poster;

        return view('news::poster.create', [
            'poster' => $poster,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'slug' => 'required',
            'order' => 'required',
            'image' => 'required',
            'start_at' => 'required|date|date_format:Y-m-d',
            'end_at' => 'required|date|date_format:Y-m-d',
            'status' => 'required|in:1,2',
        ]);

        $poster = $this->viewPoster->store($request);

        return redirect()->route('news::posters.edit', ['id' => $poster->id])->with('flash_success', Lang::get('core::message.Save success'));
    }

    public function edit($id)
    {
        Breadcrumb::add(trans('news::view.List poster'), URL::route('news::posters.index'));
        Breadcrumb::add(trans('news::view.Detail poster'));

        $poster = $this->viewPoster->edit($id);

        return view('news::poster.edit', [
            'poster' => $poster,
        ]);
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'slug' => 'required',
            'order' => 'required',
            'image' => 'required',
            'start_at' => 'required|date|date_format:Y-m-d',
            'end_at' => 'required|date|date_format:Y-m-d',
            'status' => 'required|in:1,2',
        ]);
        try {
            $poster = $this->viewPoster->update($id, $request);

            return redirect()->route('news::posters.edit', ['id' => $poster->id])->with('flash_success', Lang::get('core::message.Save success'));
        } catch (Exception $e) {
            \Log::error($e);
            return redirect()->back()->withErrors($e->getMessage());
        }

    }

    public function delete($id)
    {
        $this->viewPoster->delete($id);

        return redirect()->route('news::posters.index');
    }
}
