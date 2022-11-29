<?php

namespace Rikkei\SlideShow\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class SlideQuotation extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'slide_quotations';

    /**
     * get slide quotations
     * 
     * @param model $slide
     * @return collection
     */
    public static function getSlideQuotation($slide)
    {
        return self::select('content', 'author','id')
            ->where('slide_id', $slide->id)
            ->get();
    }
    
    /**
     * insert or update quotation of slide
     * 
     * @param model $slide
     * @param array $data
     * @throws \Rikkei\SlideShow\Model\Exception
     */
    public static function insertUpdateSlideQuotation($slide, array $data)
    {
        // process old data
        if (isset($data['old']) && count($data['old'])) {
            $dataOld = (array) $data['old'];
        } else {
            $dataOld = [];
        }
        $idsDelete = [];
        $slideQuotationOlds = self::getSlideQuotation($slide);
        DB::beginTransaction();
        try {
            if (count($slideQuotationOlds)) {
                foreach ($slideQuotationOlds as $item) {
                    if (isset($dataOld[$item->id]['content']) && 
                        trim($dataOld[$item->id]['content'])
                    ) {
                        // update old data
                        $item->content = trim($data['old'][$item->id]['content']);
                        if (isset($data['old'][$item->id]['author']) &&
                            trim($data['old'][$item->id]['author'])
                        ) {
                            $item->author = trim($data['old'][$item->id]['author']);
                        } else {
                            $item->author = null;
                        }
                        $item->save();
                    } else {
                        // delete old data
                        $idsDelete[] = $item->id;
                    }
                }
            }
            if (count($idsDelete)) {
                self::whereIn('id', $idsDelete)->delete();
            }
            // insert new data
            if (isset($data['new']) && count($data['new'])) {
                $dataInsert = [];
                $nowDate = Carbon::now()->format('Y-m-d H:i:s');
                foreach ($data['new'] as $item) {
                    if (isset($item['content']) && 
                        trim($item['content'])
                    ) {
                        $content = trim($item['content']);
                    } else {
                        continue;
                    }
                    if (isset($item['author']) &&
                        trim($item['author'])
                    ) {
                        $author = trim($item['author']);
                    } else {
                        $author = null;
                    }
                    $dataInsert[] = [
                        'slide_id' => $slide->id,
                        'content' => $content,
                        'author' => $author,
                        'created_at' => $nowDate
                    ];
                }
                if (count($dataInsert)) {
                    self::insert($dataInsert);
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * delete slide quotations
     * 
     * @param model $slide
     * @return type
     * @throws Exception
     */
    public static function deleteSlideQuotation($slide)
    {
        try {
            return self::where('slide_id', $slide->id)->delete();
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }
}