<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Test\View\ViewTest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Category extends CoreModel
{
    protected $table = 'ntest_categories';
    protected $tblLang = 'ntest_category_lang';
    protected $fillable = ['type_cat', 'is_temp'];

    /**
     * get questions that belongs to
     * @return type
     */
    public function questions()
    {
        return $this->belongsToMany('\Rikkei\Test\Models\Question', 'ntest_question_category', 'cat_id', 'question_id');
    }

    /**
     * join table language
     *
     * @param builder $builder
     * @param string $langCode
     * @return builder
     */
    public static function joinLang($builder = null, $langCode = null)
    {
        $instance = new static;
        $query = $builder === null ? $instance->newQuery() : $builder;
        $query->join($instance->tblLang, 'ntest_category_lang.cat_id', '=', $instance->table . '.id')
            ->groupBy($instance->table . '.id');
        if ($langCode) {
            $query->where('lang_code', $langCode);
        }

        return $query;
    }

    /**
     * add category language
     *
     * @param string $langCode
     * @param string $name
     * @return object
     */
    public function addLang($langCode, $name)
    {
        $catLang = DB::table($this->tblLang)->where('cat_id', $this->id)
            ->where('lang_code', $langCode)
            ->first();
        if ($catLang) {
            $catLang->update(['name' => $name]);
        } else {
            $catLang = DB::table($this->tblLang)->insert([
                'cat_id' => $this->id,
                'lang_code' => $langCode,
                'name' => $name,
            ]);
        }
        return $catLang;
    }

    /**
     * create category on row (file)
     * @param type $row
     * @param type $questionId
     * @return type
     */
    public static function addAndCollect($row, $questionId, $langCode = null)
    {
        $arrayCats = [];
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        foreach (ViewTest::ARR_CATS as $key => $type) {
            $name = trim($row->{$type}, " ,\t\n\r\x0B");
            if (!$name) {
                continue;
            }
            $nameLower = mb_strtolower($name, 'utf-8');
            //check if not exits
            $cat = self::joinLang()
                ->where(DB::raw('LOWER(name)'), $nameLower)
                ->where('type_cat', $key)
                ->first();
            //create category
            if (!$cat) {
                $cat = self::create([
                    'type_cat' => $key,
                    'is_temp' => 1
                ]);
                $cat->addLang($langCode, $name);
            }
            $cat->questions()->attach($questionId);

            //add to collection
            $arrayCats[$key] = [];
            if (!isset($arrayCats[$key][$cat->id])) {
                $arrayCats[$key][$cat->id] = $name;
            }
        }
        return $arrayCats;
    }

    /**
     * add or update category
     * @param array $data
     * @param string $langCode
     */
    public static function addIfNotExists($data, $langCode = null, $testID = null)
    {
        if (!$langCode) {
            $langCode = Session::get('locale');
        }
        $name = $data['name'];
        $typeCat = $data['type_cat'];
        $nameLower = mb_strtolower($name, 'utf-8');
        $cat = self::joinLang()
            ->select('id', 'name', 'type_cat')
            ->where(DB::raw('LOWER(name)'), $nameLower)
            ->where('type_cat', $typeCat)
            ->first();
        
        if ($cat) {
            return $cat;
        }
        
        $data['is_temp'] = 1;
        // if type4, is_temp = testID
        if ($testID && in_array($typeCat, ViewTest::ARR_TYPE_4)) {
            $data['is_temp'] = $testID;
        }
        $cat = self::create($data);
        $cat->addLang($langCode, $name);
        $cat->name = $name;
        return $cat;
    }
}
