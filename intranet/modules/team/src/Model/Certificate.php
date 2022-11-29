<?php
namespace Rikkei\Team\Model;

use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class Certificate extends CoreModel
{
    
    use SoftDeletes;
    
    const KEY_CACHE = 'cetificate_skill';
    const ROUTER_REPORT = 'team::team.report.certificates';
    /*
     * flag type certificate
     */
    const TYPE_LANGUAGE = 1;
    const TYPE_CETIFICATE = 2;
    const TYPE_SOFT = 3;
    const TYPE_OTHER = 4;
    const TYPE_ALL = 10;

    const STATUS_PLAN = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETE = 2;
    const STATUS_CANCEL = 3;
    const GROUP_MAIL_DAO_TAO = 'daotao@rikkeisoft.com';

    protected $table = 'certificates';

    /**
     * save model cetificate
     * 
     * @param array $cetificates
     * @param int $type
     * @return array
     * @throws Exception
     */
    public static function saveItems($cetificates = [], $type = null)
    {
        if (! $cetificates) {
            return;
        }
        if (! $type) {
            $type = self::TYPE_LANGUAGE;
        }
        $typeCetificates = self::getAllType();
        $tblName = $typeCetificates[$type];
        $cetificateIds = [];
        try {
            foreach ($cetificates as $key => $cetificateData) {
                if (! isset($cetificateData[$tblName]) || ! $cetificateData[$tblName]) {
                    continue;
                }
                $cetificateData = $cetificateData[$tblName];
                if (isset($cetificateData['id']) && $cetificateData['id']) {
                    if ( ($cetificate = self::find($cetificateData['id'])) &&
                        $cetificate->type == $type) {
                        $cetificateIds[$key] = $cetificateData['id'];
                    } else {
                        continue;
                    }
                    unset($cetificateData['id']);
                } else {
                    $cetificate = new self();
                }
                $validator = Validator::make($cetificateData, [
                    'name' => 'required|max:255',
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->send();
                }
                if (isset($cetificateData['image_path']) && $cetificateData['image_path']) {
                    $cetificate->image = $cetificateData['image_path'];
                } else if (isset($cetificateData['image']) && $cetificateData['image']) {
                    $urlEncode = preg_replace('/\//', '\/', URL::to('/'));
                    $image = preg_replace('/^' . $urlEncode . '/', '', $cetificateData['image']) ;
                    $image = trim($image, '/');
                    if (preg_match('/^' . Config::get('general.upload_folder') . '/', $image)) {
                        $cetificate->image = $image;
                    }
                }
                unset($cetificateData['image_path']);
                unset($cetificateData['image']);
                $cetificate->setData($cetificateData);
                $cetificate->type = $type;
                $cetificate->save();
                $cetificateIds[$key] = $cetificate->id;
            }
            CacheHelper::forget(self::KEY_CACHE);
            return $cetificateIds;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get all language format json
     * 
     * @return string
     */
    public static function getAllFormatJson($type = null)
    {
        if (! $type) {
            $type = self::TYPE_LANGUAGE;
        }
        if ($languages = CacheHelper::get(self::KEY_CACHE)) {
            return $languages;
        }
        $languages = self::select('id', 'name', 'image')
            ->where('type', $type)
            ->orderBy('name')->get();
        if (! count($languages)) {
            return '[]';
        }
        $result = '[';
        foreach ($languages as $language) {
            $result .= '{';
            $result .= '"id": "' . $language->id . '",';
            $result .= '"label": "' . $language->name . '",';
            $result .= '"image": "' . View::getLinkImage($language->image) . '"';
            $result .= '},';
        }
        $result = trim($result, ',');
        $result .= ']';
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * get all type cetificate
     * 
     * @return array
     */
    public static function getAllType()
    {
        return [
            self::TYPE_LANGUAGE => 'language',
            self::TYPE_CETIFICATE => 'cetificate',
            self::TYPE_SOFT => 'soft',
            self::TYPE_OTHER => 'other',
        ];
    }
    
    /**
     * check cetificate is language
     * 
     * @return boolean
     */
    public function isTypeLanguage()
    {
        return $this->type == self::TYPE_LANGUAGE;
    }
    
    /**
     * check cetificate is cetificate
     * 
     * @return boolean
     */
    public function isTypeCetificate()
    {
        return $this->type == self::TYPE_CETIFICATE;
    }

    /**
     * Get list type
     *
     * @return array
     */
    public static function labelAllType()
    {
        return [
            self::TYPE_LANGUAGE => trans('team::profile.Language'),
            self::TYPE_CETIFICATE => trans('team::profile.Major'),
            self::TYPE_SOFT => trans('team::profile.Soft skill'),
            self::TYPE_OTHER => trans('core::view.Other'),
        ];
    }

    /**
     * search all certificate
     *
     * @return collection
     */
    public static function searchSchoolAutocomplete($search)
    {
        return self::select(['name as label'])
            ->where('name', 'LIKE', "%{$search}%")
            ->whereNull('deleted_at')
            ->orderBy('label', 'ASC')
            ->limit(10)
            ->get();
    }

    /**
     * check then save certificate suggest after save employee certificate
     *
     * @param type $name
     * @return \self
     */
    public static function checkAndSaveFromCerfiticate($name)
    {
        $item = self::select(['id'])->where('name', $name)->first();
        if ($item) {
            return $item;
        }
        $item = new self();
        $item->setData([
            'name' => $name,
        ])->save();
        return $item;
    }

    public static function getAll()
    {
        return self::select(['id','name'])->whereNull('deleted_at')->get()->toArray();
    }

    /**
     * @return array option
     */
    public static function getOptionStatus()
    {
        return [
            self::STATUS_PLAN => Lang::get('team::profile.Request not sent'),
            self::STATUS_PROCESSING => Lang::get('team::profile.Awaiting approval'),
            self::STATUS_COMPLETE => Lang::get('team::profile.Approved'),
            self::STATUS_CANCEL => Lang::get('team::view.Reject')
        ];
    }
}
