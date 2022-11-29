<?php

namespace Rikkei\Magazine\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Magazine\Model\Magazine;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Http\Request;
use Rikkei\Magazine\Lib\FileEloquent;
use Rikkei\Magazine\Model\ImageModel;
use Rikkei\News\Model\Category;
use Validator;
use DB;
use Illuminate\Support\Facades\Cache;
use Rikkei\News\Model\Post;

class MagazineController extends Controller {
    
    protected $file;

    /**
     * construct more
     */
    public function __construct(FileEloquent $file) {
        parent::__construct();
        $this->file = $file;
        Menu::setActive('pr');
        Breadcrumb::add(trans('magazine::view.Magazine'));
    }

    /**
     * Display magazine manager page
     *
     * @return \Illuminate\Http\Response
     */
    public function manage() {
        $collectionModel = Magazine::getGridData();

        return view('magazine::manage', compact('collectionModel'));
    }

    /**
     * Read magazine in new and full window
     *
     * @return \Illuminate\Http\Response
     */
    public function read($id) {
        $magazine = Magazine::find($id);
        if (!$magazine) {
            abort(404);
        }
        if ($magazine->type != Magazine::MAGAZINE) {
            abort(404);
        }
        $images = $magazine->images()->orderBy('order', 'ASC')->get();

        return view('magazine::view', compact('magazine', 'images'));
    }
    
    /**
     * show all magazine
     * @param Request $request
     * @return type
     */
    public function listAll(Request $request) {
        $collectionModel = Magazine::getLists($request->all());
        $searchParams = $request->get('search');
        $activeCategories = Category::getAllActiveCategory();
        $isYume = true;
        return view('magazine::list', compact('collectionModel', 'searchParams', 'activeCategories', 'isYume'));
    }

    /*
     * create Magazine 
     * @return view
     */
    public function create() {
        Breadcrumb::add(trans('magazine::view.Create'));
        return view('magazine::create');
    }

    /**
     * ajax upload file
     * @param Request $request
     * @return array
     */
    public function uploadImage(Request $request) {
        if (!$request->hasFile('images')) {
            return response()->json(trans('magazine::message.No file choosed'), 422);
        }
        $input_files = $request->file('images');  
        
        $result = [];
        DB::beginTransaction();
        try {
            foreach ($input_files as $ip_file) {
                $file = $this->file->insert($ip_file);
                if ($file) {
                    $file->html = view('magazine::template.image-item', ['image' => $file])->render();
                    array_push($result, $file);
                }
            }
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            if ($result) {
                foreach ($result as $file) {
                    $file->deleteImage();
                }
            }
            DB::rollback();
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * create magazine
     * @param Request $request
     * @return type
     */
    public function store(Request $request)
    {
        ini_set('max_execution_time', 300);
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:magazine,name',
            'images' => 'required'
        ]);
        
        if ($valid->fails()) {
            $response['err'] = view('messages.errors', ['errors' => $valid->errors()])->render();
            return response()->json($response);
        }
        
        $select_index = $request->get('select_index');
        $name = $request->get('name');
        $images = $request->file('images');
        if ($request->get('document') != null) {
            $type = Magazine::DOCUMENT;
        } else {
            $type = Magazine::MAGAZINE;
        }

        DB::beginTransaction();
        
        try {
            $magazine = Magazine::create([
                'name' => $name,
                'slug' => str_slug($name),
                'type' => $type
            ]);
            
            if ($images) {
                foreach ($images as $key => $file) {
                    $image = $this->file->insert($file, true);
                    $magazine->images()->attach($image->id, ['order' => $key, 'is_background' => ($key == $select_index)]);
                }
            }

            if ($type == Magazine::DOCUMENT) {
                $response['id'] = $magazine->id;
                $response['name'] = $magazine->name;
                $response['message'] = trans('magazine::message.Create successful');
            } else {
                $response = trans('magazine::message.Create successful');
            }
            DB::commit();
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_MAGAZINE);
            return response()->json($response);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(trans('magazine::message.Error occurred'), 500);
        }
        
    }
    
    /**
     * get images
     * @param Request $request
     * @return array
     */
    public function getImages(Request $request) {
        $valid = Validator::make($request->all(), [
            'image_ids' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(null, 500);
        }
        $image_ids = $request->get('image_ids');
        $result = [];
        foreach ($image_ids as $id) {
            $image = ImageModel::find($id);
            if ($image) {
                $image->html = view('magazine::template.image-item', ['image' => $image])->render();
                array_push($result, $image);
            }
        }
        return $result;
    }

    /*
     * view page edit Magazine
     * @param int
     * @return view
     */
    public function edit($id) {
        Breadcrumb::add(trans('magazine::view.Edit'));

        $item = Magazine::find($id);
        if (!$item) {
            abort(404);
        }
        return view('magazine::edit', compact('item'));
    }

    /*
     * update Magazine
     */
    public function update($id, Request $request) {
        
        $valid = Validator::make($request->all(), [
            'name' => 'required|max:255|unique:magazine,name,' . $id,
            'images' => 'required_without:image_ids'
        ]);
        
        if ($valid->fails()) {
            $response['err'] = view('messages.errors', ['errors' => $valid->errors()])->render();
            return response()->json($response);
        }
        
        $select_index = $request->get('select_index');
        $name = $request->get('name');
        $images = $request->file('images');
        $image_ids = $request->get('image_ids');
        if ($request->get('document') != null) {
            $type = Magazine::DOCUMENT;
        } else {
            $type = Magazine::MAGAZINE;
        }
        $magazine = Magazine::find($id);
        if (!$magazine) {
            abort(404);
        }
        
        DB::beginTransaction();
        
        try {
            $magazine->name = $name;
            $magazine->slug = str_slug($name);
            $magazine->type = $type;
            $magazine->save();
            //detach all
            $magazine->images()->detach();
            //update attach
            if ($image_ids) {
                foreach ($image_ids as $key => $image_id) {
                    $magazine->images()->attach($image_id, ['order' => $key, 'is_background' => ($key == $select_index)]);
                }
            }
            //upload new images
            if ($images) {
                foreach ($images as $key => $file) {
                    $image = $this->file->insert($file, true);
                    $magazine->images()->attach($image->id, ['order' => $key, 'is_background' => ($key == $select_index)]);
                }
            }
            //delete not attach image
            ImageModel::deleteNotAttach();

            if ($type == Magazine::DOCUMENT) {
                $response['id'] = $magazine->id;
                $response['name'] = $magazine->name;
                $response['message'] = trans('magazine::message.Create successful');
            } else {
                $response = trans('magazine::message.Create successful');
            }
            DB::commit();
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_MAGAZINE);
            return response()->json($response);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json($ex->getMessage(), 500);
        }
        
    }

    /*
     * delete Magazine
     */
    public function delete($id) {
        $magazine = Magazine::find($id);
        if (!$magazine) {
            abort(404);
        }

        DB::beginTransaction();
        try {
            $images = $magazine->images;
            if (!$images->isEmpty()) {
                foreach ($images as $image) {
                    $image->deleteImage();
                }
            }
            $magazine->delete();
            
            DB::commit();
            Cache::forget(Post::CACHE_KEY . '_' . Post::CACHAE_SUFFIX_MAGAZINE);
            return redirect()->back()->with('messages', ['success' => [trans('magazine::message.Delete successful')]]);
        } catch (Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('messages', ['errors' => [trans('magazine::message.Delete item error')]]);
        }
    }

}
