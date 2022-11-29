<?php

namespace Rikkei\SlideShow\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\Lang;
use Rikkei\SlideShow\Model\Slide;
use Rikkei\SlideShow\Model\Repeat;
use Illuminate\Http\Request;
use Rikkei\SlideShow\Http\Requests\AddSlideRequest;
use Rikkei\SlideShow\Http\Requests\SliderPasswordRequest;
use Illuminate\Support\Facades\Blade;
use Rikkei\SlideShow\Model\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Rikkei\SlideShow\Http\Requests\SliderListRequest;
use Rikkei\SlideShow\Http\Requests\BirthdayRequest;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\SlideShow\View\View;
use Rikkei\Core\View\View as ViewCore;
use Rikkei\Team\View\Permission;
use Rikkei\SlideShow\Model\VideoDefault;
use Illuminate\Support\Facades\Input;
use Rikkei\SlideShow\View\RunBgSlide;
use Illuminate\Support\Facades\Log;
use Rikkei\SlideShow\Model\SlideQuotation;
use Rikkei\SlideShow\Model\SlideBirthday;
use Rikkei\SlideShow\View\SlideBirthday as ViewSlideBirthday;
use Illuminate\Support\Facades\Auth;
use Exception;
use Validator;

class SlideShowController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('Admin', 'slide-show/setting');
    }

    /**
     * play slide show
     * @return view
     */
    public function index()
    {
        if (Session::has('password-slider')) {
            $slide = Slide::getSlideShowNow();
            $fileOfSlide = File::getFileOfSlide($slide);
            $urlVideo = url('/') . '/' . Config::get('general.upload_folder') . '/' .  File::PATH_VIDEO_DEFAULT.'/';
            // $videoDefault = CoreConfigData::getVideoDefault();
            $videoDefault = VideoDefault::getValueDefault();
            $secondPlaySlide = View::getSecondPlayVideo($slide);
            $secondToBirthday = View::getSecondToBirthday();
            $isPreview = false;
            $urlImage = Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
            return view('slide_show::index', compact('slide', 'fileOfSlide', 'urlImage', 'urlVideo', 'videoDefault', 'secondPlaySlide', 'isPreview', 'secondToBirthday'));
        } else {
            return view('slide_show::login');
        }
    }

    /**
     * list slide show
     * @return view
     */
    public function listSlideShow()
    {
        if (Permission::getInstance()->isAllow('slide_show::list-slider')) {
            Breadcrumb::add(Lang::get('slide_show::view.Slide setting'));
            $allTypeRepeat = Repeat::getAllTypeRepeat();
            $allTypeSlide = Slide::getAllTypeSlide();
            $allOptionSlide = Slide::getAllOptionSlide();
            $allLanguageSlide = Slide::getAllLanguageSlide();
            $dateNow = date('Y-m-d');
            $sizeImageValidate = CoreConfigData::getSizeImageValidate();
            $effectSlide = Slide::getEffectOption();
            return view('slide_show::list', compact(
                    'allTypeRepeat', 
                    'allTypeSlide', 
                    'dateNow', 
                    'sizeImageValidate',
                    'effectSlide',
                    'allOptionSlide',
                    'allLanguageSlide'
            ));
        } else {
            ViewCore::viewErrorPermission();
        }    
    }

    /**
     * get list slide by date
     * @param array
     * @return json
     */
    public function getSliderByDate(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::list-slider')) {
            $data = $request->all();
            $allHour = Slide::getHour();
            $response = array();
            $response['status'] = true;
            $validate = SliderListRequest::validateData($data);
            if ($validate->fails()) {
                $response['message_error'] = $validate->errors();
                $response['status'] = false;
                return response()->json($response);
            }
            $slides = Slide::getSliderByDate($data['date']);
            $response['content'] = view('slide_show::components.slide-list', ['slides' => $slides, 'allHour' => $allHour])->render();
            return response()->json($response);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    /**
     * get detail slide
     * @param array
     * @return slide
     */
    public static function detailSlide(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::list-slider')) {
            $data = $request->all();
            $response = array();
            $slide = Slide::getSlideById($data['id']);
            $urlImage = Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
            $allTypeRepeat = Repeat::getAllTypeRepeat();
            $allTypeSlide = Slide::getAllTypeSlide();
            $allFile = File::getAllFileForSlideRezise($slide, $urlImage);
            $isAllowRepeatHourly = View::checkAllowRepeatHourly($slide);
            $effectSlide = Slide::getEffectOption();
            if ($slide->option == Slide::OPTION_QUOTATIONS) {
                $slideQuotations = SlideQuotation::getSlideQuotation($slide);
            } else {
                $slideQuotations = null;
            }
            $optionBirthday = $slide->option == Slide::OPTION_BIRTHDAY; 
            $response['content'] = view('slide_show::components.detail', [
                'slide' => $slide, 
                'allTypeRepeat' => $allTypeRepeat, 
                'allTypeSlide' => $allTypeSlide, 
                'allFile' => $allFile, 
                'isAllowRepeatHourly' => $isAllowRepeatHourly,
                'effectSlide' => $effectSlide,
                'slideQuotations' => $slideQuotations,
                'optionBirthday' => $optionBirthday,
            ])->render();
            return response()->json($response);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    /**
     * create slide 
     * @param array
     * @return json
     */
    public static function createSlide(Request $request)
    {
        if (!Permission::getInstance()->isAllow('slide_show::list-slider')) {
            $response['status'] = false;
            ViewCore::viewErrorPermission();
        }
        $data = $request->all();
        $response = array();
        $response['status'] = true;
        $validateAdd = AddSlideRequest::validateData($data);
        if ($validateAdd->fails()) {
            $messageRepeatUnique = Session::get('messageUniqueRepeat');
            if (Session::has('messageUniqueRepeat')) {
                Session::forget('messageUniqueRepeat');
            }
            if ($messageRepeatUnique) {
                $response['repeat_error'] = $messageRepeatUnique;
            }

            $messageUniqueHour = Session::get('messageUniqueHour');
            if (Session::has('messageUniqueHour')) {
                Session::forget('messageUniqueHour');
            }
            if ($messageUniqueHour) {
                $response['hour_error'] = $messageUniqueHour;
            }
            $response['message_error'] = $validateAdd->errors();
            $response['status'] = false;
            return response()->json($response);
        }
        try {
            $result = Slide::insertSlide($data);
        } catch (Exception $ex) {
            $result['status'] = false;
            Log::info($ex);
        }
        
        if(!$result['status']) {
            $response['success'] = false;
            if (isset($data['id'])) {
                $response['message'] = Lang::get('slide_show::message.Edit slide error');
            } else {
                $response['message'] = Lang::get('slide_show::message.Create slide error');
            }
        } else {
            $response['success'] = true;
            if (isset($data['id'])) {
                $response['message'] = Lang::get('slide_show::message.Update slide success');
            } else {
                $response['message'] = Lang::get('slide_show::message.Create slide success');
            }
            $response['id'] = $result['id'];
        }
        return response()->json($response);
    }

    /**
     * get template image
     * @return view
     */
    function getTemplateImage() {
        Blade::setContentTags('<%', '%>');
        return view('slide_show::templates.image');
    }

    /**
     * load slide ajax
     * @return json
     */
    public function loadSlideAjax(Request $request)
    {
        if (!Session::has('password-slider')) {
            return false;
        }
        $response = array();
        $data = $request->all();
        $slide = Slide::getSlideShowNow();
        $fileOfSlide = File::getFileOfSlide($slide);
        $urlImage = Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
        $urlVideo = url('/') . '/' . Config::get('general.upload_folder') . '/' .
            File::PATH_VIDEO_DEFAULT.'/';
        // $videoDefault = CoreConfigData::getVideoDefault();
        $videoDefault = VideoDefault::getValueDefault();
        $secondPlaySlide = View::getSecondPlayVideo($slide);
        $secondToBirthday = View::getSecondToBirthday();
        $response['content'] = view('slide_show::templates.content-slide', 
            compact('slide', 'fileOfSlide', 'urlImage',
                'urlVideo', 'videoDefault', 'data', 'secondToBirthday'))->render();
        $response['secondPlaySlide'] = $secondPlaySlide;
        if($slide) {
            $title = $slide->title;
            $description = $slide->title;
        } else {
            $title = Lang::get('slide_show::view.Rikkeisoft Intranet');
            $description = Lang::get('slide_show::view.Rikkeisoft Intranet');
        }
        $response['title'] = $title;
        $response['description'] = $description;
        return response()->json($response);
    }

    /**
     * delete slide
     * @param array
     * @return json
     */
    public function deleteSlide(Request $request)
    {
        if (!Permission::getInstance()->isAllow('slide_show::list-slider')) {
            ViewCore::viewErrorPermission();
        }
        $data = $request->all();
        $slideId = $data['id'];
        $slide = Slide::getSlideById($slideId);
        $response = array();
        $response['status'] = true;
        if(!$slide) {
            $response = array();
            $response['status'] = false;
            return response()->json($response);
        }
        try {
            $status = Slide::deleteSlide($slide);
        } catch (Exception $ex) {
            $status = false;
            Log::info($ex);
        }
        if (!$status) {
            $response['status'] = false;
        }
        return response()->json($response);
    }

    /**
     * preview slide
     * @param int
     * @return view
     */
    public function previewSlide($id, Request $request)
    {
        $slide = Slide::getSlideById($id);

        if (!$slide) {
            ViewCore::viewErrorNotFound();
        }
        if (!Permission::getInstance()->isAllow('slide_show::list-slider')) {
            ViewCore::viewErrorPermission();
        }
        $fileOfSlide = File::getFileOfSlide($slide);
        $urlImage = Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
        $urlVideo = url('/') . '/' . Config::get('general.upload_folder') . '/' .  File::PATH_VIDEO_DEFAULT.'/';
        $secondPlaySlide = null;
        $isPreview = true;
        $secondToBirthday = View::getSecondToBirthday();
        // see fake data preview
        if(isset($request->fakeData) && $request->fakeData) {
            $fakeData = $request->fakeData;
            $user = Auth::user();
            $employee = $user->getEmployee();
            $contentConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA);
            $image = str_replace("?sz=50", "?sz=308", $user->avatar_url);
            if(!$image) {
                $image = url('slide_show/images/no-image.jpg');
            }
            $content = ViewSlideBirthday::partternSlideBirthday($employee, $contentConfig);
            return view('slide_show::index', compact('slide', 
                'fileOfSlide', 'urlImage', 'urlVideo', 
                'secondPlaySlide', 'isPreview', 'secondToBirthday',
                'fakeData', 'image', 'content'
                )
            );
        } else {
            $fakeData = 1;
        }
        return view('slide_show::index', compact('slide', 
                'fileOfSlide', 
                'urlImage', 
                'urlVideo', 
                'secondPlaySlide', 
                'isPreview', 
                'secondToBirthday',
                'fakeData')
        );
    }

    public function getTemplateInterval(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::list-slider')) {
            $data = $request->all();
            if (isset($data['id'])) {
                $slide = Slide::getSlideById($data['id']);
            } else {
                $slide = null;
            }
            $response = [];
            $status = View::checkAllowRepeatHourly($data);
            $allTypeRepeat = Repeat::getAllTypeRepeat();
            $response['content'] = view('slide_show::templates.repeat', ['status' => $status, 'slide' => $slide, 'allTypeRepeat' => $allTypeRepeat])->render();
            return response()->json($response);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    public function changePassword(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            $data = $request->all();
            $response = [];
            $response['status'] = false;
            $validate = SliderPasswordRequest::validateData($data);
            if ($validate->fails()) {
                $response['message_error'] = $validate->errors();
                return response()->json($response);
            }
            $status = Slide::updatePassword($data);
            if ($status) {
                $response['status'] = true;
            }
            return response()->json($response);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    public function checkPasswordSlider(Request $request)
    {
        $data = $request->all();
        $response = [];
        $response['status'] = false;
        $validate = SliderPasswordRequest::validateData($data);
        if ($validate->fails()) {
            $response['message_error'] = $validate->errors();
            return response()->json($response);
        }
        $config = CoreConfigData::where('key', 'slide_show.password')->first();
        if (!$config) {
            $response['password_error'] = Lang::get('slide_show::message.Password incorrect');
        } else {
            if ($data['password'] == $config ['value']) {
                Session::push('password-slider', $data['password']);
                $response['status'] = true;
                $response['url'] = route('slide_show::slide-show');
            } else {
                $response['password_error'] = Lang::get('slide_show::message.Password incorrect');
            }
        }
        return response()->json($response);
    }
    public function createVidelDefault()
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            Breadcrumb::add(Lang::get('slide_show::view.Create video default'));
            return view('slide_show::video.edit');
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    public function postVideoDefault(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            $data = $request->all();
            Breadcrumb::add(Lang::get('slide_show::view.Create video default'));
            $videoId = VideoDefault::uploadVideo($data);
            if ($videoId) {
                if (isset($data['id']) && $data['id']) {
                    $mgs = Lang::get('slide_show::message.Update video success');
                } else {
                    $mgs = Lang::get('slide_show::message.Create video success');
                }

                $messages = [
                    'success'=> [
                        $mgs,
                    ]
                ];
                return redirect()->route('slide_show::video-edit', ['id' => $videoId])
                    ->with('messages', $messages);
            }
            if (isset($data['id']) && $data['id']) {
                $mgs = Lang::get('project::message.Update video error');
            } else {
                $mgs = Lang::get('project::message.Create video error');
            }
            $messages = [
                'errors'=> [
                    $mgs,
                ]
            ];
            return redirect()->route('slide_show::create-video-default')->with('messages', $messages);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    public function detailVideo($id)
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            $video = VideoDefault::getVideoDefaultBygId($id);
            if (!$video) {
                return redirect()->route('slide_show::setting')
                    ->withErrors(Lang::get('project::message.Not found item.'));
            }
            Breadcrumb::add(Lang::get('slide_show::view.Detail video default'));
            return view('slide_show::video.edit', compact('video'));
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    public function setting()
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            Breadcrumb::add(Lang::get('slide_show::view.List video default'));
            $allVideo = VideoDefault::getGridData();
            $password = CoreConfigData::where('key', 'slide_show.password')->first();
            $birthday = CoreConfigData::where('key', 'slide_show.birthday_company')->first();
            return view('slide_show::video.list', compact(['allVideo', 'password', 'birthday']));
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    /*
     * delete video default
     */
    public function deleteVideoDefault()
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            $id = Input::get('id');
            $video = VideoDefault::getVideoDefaultBygId($id);
            if (! $video) {
                return redirect()->route('slide_show::setting')->withErrors(Lang::get('team::messages.Not found item.'));
            }
            $video->delete();
            // if ($video->file_name) {
            //     $common = new ViewCore();
            //     $path = VideoDefault::PATH_VIDEO_DEFAULT;
            //     $common->deleteFile(trim(Config::get('general.upload_storage_public_folder') . 
            //                         '/' . $path, '/').'/'.$video->file_name);
            // }
            $messages = [
                'success'=> [
                    Lang::get('team::messages.Delete item success!'),
                ]
            ];
            return redirect()->route('slide_show::setting')->with('messages', $messages);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    /*
     * get file for slide
     * @return json
     */
    public static function getFileForSlide(Request $request)
    {
        if (!$request->input('width_screen') || !$request->input('height_screen')) {
            return null;
        }
        $data = $request->all();
        $response = array();
        $slideId = $data['slide_id'];
        $slide = Slide::getSlideById($slideId);
        $fileOfSlide = File::getFileOfSlide($slide);
        $urlImage = Config::get('general.upload_folder') . '/' .  File::PATH_DEFAULT.'/';
        $response['content'] = view('slide_show::templates.image-slide', compact('slide', 'fileOfSlide', 'urlImage', 'data'))->render();
        return response()->json($response);
    }
    
    /**
     * check process run resize
     */
    public static function processCheck()
    {
        $response = [];
        if (RunBgSlide::isProcessAvail()) {
            $response['run'] = 1;
        } else {
            $response['run'] = 0;
        }
        return response()->json($response);
    }

    /**
     * change birthday company
     * @param array
     * @return json
     */
    public function changeBirthday(Request $request)
    {
        if (Permission::getInstance()->isAllow('slide_show::setting')) {
            $data = $request->all();
            $response = [];
            $response['status'] = false;
            $validate = BirthdayRequest::validateData($data);
            if ($validate->fails()) {
                $response['message_error'] = $validate->errors();
                return response()->json($response);
            }
            $status = Slide::updateTimeBirthday($data);
            if ($status) {
                $response['status'] = true;
            }
            return response()->json($response);
        } else {
            ViewCore::viewErrorPermission();
        }
    }

    /**
     * get template logo
     * @return view
     */
    function urlGetTemplateLogo() {
        Blade::setContentTags('<%', '%>');
        return view('slide_show::templates.logo');
    }

    /**
     * show birthday slide pattern
     * @return view
     */
    public function showBirthdayPattern() {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Manage birthday slide');
        return view('slide_show::components.slide_birthday_manage', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'title' => CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_TITLE),
            'content' => CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA),
        ]);
    }

    /**
     * save birthday slide pattern
     * @return view
     */
    public function saveBirthdayPattern(Request $request) {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Manage birthday slide');
        // validate
        $validator = Validator::make($request->only(['birthday']), [
                'birthday.title' => 'required',
                'birthday.content' => 'required',
            ], [
                'birthday.title.required' => Lang::get('slide_show::message.Birthday title is required'), 
                'birthday.content.required' => Lang::get('slide_show::message.Birthday content is required'), 
            ]);
        if ($validator->fails()) {
            return redirect()->route('slide_show::admin.slide.birthday.show')->withErrors($validator);
        }
        // save title slide birthday 
        $configTitle = CoreConfigData::getItem(Slide::BIRTHDAY_CONFIG_TITLE);
        $configTitle->value = trim($request->input('birthday')['title']);
        $configTitle->save();
        // save content slide birthday
        $configContent = CoreConfigData::getItem(Slide::BIRTHDAY_CONFIG_DATA);
        $configContent->value = trim($request->input('birthday')['content']);
        $configContent->save();
        // delete old data in slide birthday
        $employSlideBirth = ViewSlideBirthday::getEmployeeHavingBirthday();
        if($employSlideBirth) {
            $titleConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_TITLE);
            $contentConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA);
            // update slide title
            Slide::updateTitleSlideBirthday($titleConfig);
            // update slide content
            $birthdaySlideArray = array();
            foreach ($employSlideBirth as $slideItem) {
                $singleSlideUpdate = array();
                $singleSlideUpdate['id'] = $slideItem->slideBrithday_id;
                $singleSlideUpdate['content'] = ViewSlideBirthday::partternSlideBirthday($slideItem, $contentConfig);
                $birthdaySlideArray[] = $singleSlideUpdate;
            }
            foreach($birthdaySlideArray as $slideUpdate) {
                SlideBirthday::find($slideUpdate['id'])->update(['content' => $slideUpdate['content']]);
            }
        }
        $messages = [
            'success'=> [
                Lang::get('slide_show::view.Success'), 
            ]
        ];
        return redirect()->route('slide_show::admin.slide.birthday.show')->with('messages', $messages);
    }

    /**
     * preview birthday slide pattern
     * @return view
     */
    public function previewBirthdayPattern() {
        $user = Auth::user();
        $employee = $user->getEmployee();
        $contentConfig = CoreConfigData::getValueDb(Slide::BIRTHDAY_CONFIG_DATA);
        $image = str_replace("?sz=50", "?sz=308", $user->avatar_url);
        if(!$image) {
            $image = url('slide_show/images/no-image.jpg');
        }
        $content = ViewSlideBirthday::partternSlideBirthday($employee, $contentConfig);
        return view('slide_show::components.preview_slide_birth', [
                    'image' => $image, 'content' => $content,
                ]);
    }
}