<?php
namespace Rikkei\Tag\Seeds;

use DB;
use Rikkei\Tag\Model\Field;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\View\TagConst;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\EmplProjExperTag;

class ConvertTagSkillSheetSeeder extends CoreSeeder
{
    // các tag cần chuyển từ language sang framework
    private $tagConverts = ['Electron JS', 'ReactJS'];
    // các tag cần chuyển từ framework sang framework
    private $tagFrameworkConverts = [
        'Vue' => 'VueJS',
        'React' => 'ReactJS',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return;
        }
        $fields = Field::whereIn('code', ['language', 'framework'])
            ->get()->groupBy('code');
        // không tồn tại field language hoặc field framework
        if (count($fields) !== 2) {
            return;
        }
        $fieldLanguageId = $fields['language'][0]['id'];
        $fieldFrameworkId = $fields['framework'][0]['id'];

        // chuyển đổi tag ở language sang framework
        $this->convertTagLanguageToFramework($fieldLanguageId, $fieldFrameworkId);
        // chuyển đổi tag ở framework sang framework
        $this->convertTagFrameworkToFramework($fieldFrameworkId);
    }

    protected function convertTagLanguageToFramework($fieldLanguageId, $fieldFrameworkId)
    {
        $tagToLowers = []; // chuyển value về dạng chữ thường => dùng để kiểm tra sự tồn tại của tag cần chuyển
        $originalTags = []; // value ban đầu => dùng cho thêm mới bản ghi
        foreach ($this->tagConverts as $tagName) {
            $tagToLower = strtolower($tagName);
            $tagToLowers[] = $tagToLower;
            $originalTags[$tagToLower] = $tagName;
        }

        $collection = Tag::whereIn('field_id', [$fieldLanguageId, $fieldFrameworkId])
            ->whereIn('value', $this->tagConverts)
            ->groupBy('field_id', 'value')
            ->select('id', 'field_id', DB::raw('LOWER(value) AS value'), 'status')
            ->get();
        $tags = [];
        foreach ($collection as $tag) {
            $tags[$tag->value][$tag->field_id] = $tag;
        }

        $tagTexts = EmplProjExperTag::whereIn('tag_text', $this->tagConverts)
            ->whereNull('tag_id')
            ->where('type', 'lang')
            ->groupBy('tag_text')
            ->select(DB::raw('LOWER(tag_text) AS tag_text'))
            ->get()->groupBy('tag_text');

        foreach ($tagToLowers as $tagName) {
            // không có tag nhưng có text
            if (!isset($tags[$tagName][$fieldLanguageId]) && isset($tagTexts[$tagName])) {
                $tags[$tagName][$fieldLanguageId] = [];
            }
            // không có tag và cũng không có text tại phần project skillsheet
            if (isset($tags[$tagName]) && !isset($tags[$tagName][$fieldLanguageId])) {
                unset($tags[$tagName]);
            }
        }

        DB::beginTransaction();
        $deletedTags = [];
        $updatedTags = [];
        try {
            foreach ($tags as $tagValue => $tag) {
                $tagLanguageId = !empty($tag[$fieldLanguageId]) ? $tag[$fieldLanguageId]->id : null;
                $tagFrameworkId = !empty($tag[$fieldFrameworkId]) ? $tag[$fieldFrameworkId]->id : null;
                // chỉ tồn tại tag cần chuyển ở language => tạo mới bản ghi tag cần chuyển ở framework
                if ($tagFrameworkId === null) {
                    $tagFrameworkId = Tag::create([
                        'field_id' => $fieldFrameworkId,
                        'value' => $originalTags[$tagValue],
                        'status' => TagConst::TAG_STATUS_APPROVE,
                    ])->id;
                } else {
                    // cập nhật trạng thái approved nếu tag đó đang ở trạng thái review
                    if ((int)$tag[$fieldFrameworkId]->status !== TagConst::TAG_STATUS_APPROVE) {
                        $updatedTags[] = $tagFrameworkId;
                    }
                }
                // cập nhật tag ở language sang tag ở framework trong skill skillsheet
                EmployeeSkill::where('type', 'language')
                    ->where('tag_id', $tagLanguageId)
                    ->update([
                        'type' => 'frame',
                        'tag_id' => $tagFrameworkId,
                    ]);

                // tìm các project skillsheet mà ở framework đã có tag cần chuyển hoặc tồn tại tên tag cần chuyển rồi
                $projExperTagIds = EmplProjExperTag::where(function ($query) use ($tagFrameworkId, $tagValue) {
                    $query->where('tag_id', $tagFrameworkId)
                        ->orWhere('tag_text', $tagValue);
                })
                    ->where('type', 'other')
                    ->get()->pluck('proj_exper_id')->toArray();

                // cập nhật tag ở language sang tag ở framework trong project skillsheet nếu project đó chưa có tag cần chuyển trong framework
                EmplProjExperTag::where('type', 'lang')
                    ->where(function ($query) use ($tagLanguageId, $tagValue) {
                        $query->where('tag_text', $tagValue);
                        if ($tagLanguageId) {
                            $query->orWhere('tag_id', $tagLanguageId);
                        }
                    })
                    ->whereNotIn('proj_exper_id', $projExperTagIds)
                    ->update([
                        'type' => 'other',
                        'tag_id' => $tagFrameworkId,
                    ]);
                // xóa tag cần chuyển ở language trong project skillsheet nếu project đó đã có tag cần chuyển trong framework
                EmplProjExperTag::where('type', 'lang')
                    ->where(function ($query) use ($tagLanguageId, $tagValue) {
                        $query->where('tag_text', $tagValue);
                        if ($tagLanguageId) {
                            $query->orWhere('tag_id', $tagLanguageId);
                        }
                    })
                    ->whereIn('proj_exper_id', $projExperTagIds)
                    ->delete();
                $deletedTags[] = $tagLanguageId;
            }
            // xóa các tag cần chuyển có field là language
            Tag::whereIn('id', $deletedTags)->delete();
            // cập nhật tag sang approve
            Tag::whereIn('id', $updatedTags)->update(['status' => TagConst::TAG_STATUS_APPROVE]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    protected function convertTagFrameworkToFramework($fieldFrameworkId)
    {
        $tagsFrom = []; // các tag chuyển đi
        $tagsTo = []; // các tag chuyển đến
        $tagToLowers = []; // chuyển cả key lẫn value về dạng chữ thường => dùng để kiểm tra sự tồn tại của tag chuyển đến
        $originalTags = []; // chỉ chuyển cả key về dạng chữ thường => dùng cho tạo mới tag
        foreach ($this->tagFrameworkConverts as $tagFrom => $tagTo) {
            // đồng nhất key về dạng chữ thường
            $tagFrom = strtolower($tagFrom);
            $originalTags[$tagFrom] = $tagTo;
            $tagTo = strtolower($tagTo);
            $tagToLowers[$tagFrom] = $tagTo;
            $tagsFrom[] = $tagFrom;
            $tagsTo[] = $tagTo;
        }

        $collection = Tag::where('field_id', $fieldFrameworkId)
            ->whereIn('value', array_merge($tagsFrom, $tagsTo))
            ->select('id', DB::raw('LOWER(value) AS value'), 'status')
            ->get();
        foreach ($collection as $tag) {
            $collection[strtolower($tag->value)] = $tag;
        }

        $tagTexts = EmplProjExperTag::whereIn('tag_text', $tagsFrom)
            ->whereNull('tag_id')
            ->where('type', 'other')
            ->select(DB::raw('LOWER(tag_text) AS tag_text'))
            ->get()->groupBy('tag_text');

        $tags = [];
        foreach ($tagsFrom as $tagFrom) {
            if (isset($collection[$tagFrom])) { // tồn tại tag
                $tags[$tagFrom] = $collection[$tagFrom];
            } else {
                if (isset($tagTexts[$tagFrom])) { // không tồn tại tag nhưng có text tag
                    $tags[$tagFrom] = [];
                }
            }
        }

        DB::beginTransaction();
        $deletedTags = [];
        $updatedTags = [];
        try {
            foreach ($tags as $tagFromName => $tag) {
                $tagToName = $tagToLowers[$tagFromName];
                $tagFromId = !empty($tag) ? $tag->id : null;
                $tagToId = isset($collection[$tagToName]) ? $collection[$tagToName]->id : null;
                // chỉ tồn tại tag cần chuyển đi
                if ($tagToId === null) {
                    $tagToId = Tag::create([
                        'field_id' => $fieldFrameworkId,
                        'value' => $originalTags[$tagFromName],
                        'status' => TagConst::TAG_STATUS_APPROVE,
                    ])->id;
                } else {
                    // cập nhật trạng thái approved nếu tag đó đang ở trạng thái review
                    if ((int)$collection[$tagToName]->status !== TagConst::TAG_STATUS_APPROVE) {
                        $updatedTags[] = $tagToId;
                    }
                }
                // cập nhật tag trong skill skillsheet
                EmployeeSkill::where('type', 'frame')
                    ->where('tag_id', $tagFromId)
                    ->update(['tag_id' => $tagToId]);

                // tìm các project skillsheet mà ở framework đã có tag cần chuyển đến (theo tag_id) đến hoặc tồn tại tên tag (theo text_tag) cần chuyển đến rồi
                $projExperTagIds = EmplProjExperTag::where(function ($query) use ($tagToId, $tagToName) {
                    $query->where('tag_id', $tagToId)
                        ->orWhere('tag_text', $tagToName);
                    })
                    ->where('type', 'other')
                    ->get()->pluck('proj_exper_id')->toArray();

                // cập nhật tag ở framework trong project skillsheet nếu project đó chưa có tag cần chuyển đến ở framework
                EmplProjExperTag::where('type', 'other')
                    ->where(function ($query) use ($tagFromId, $tagFromName) {
                        $query->where('tag_text', $tagFromName);
                        if ($tagFromId) {
                            $query->orWhere('tag_id', $tagFromId);
                        }
                    })
                    ->whereNotIn('proj_exper_id', $projExperTagIds)
                    ->update(['tag_id' => $tagToId]);

                // xóa tag chuyển đi ở framework trong project skillsheet nếu project đó đã có tag cần chuyển đến ở framework
                EmplProjExperTag::where('type', 'other')
                    ->where(function ($query) use ($tagFromId, $tagFromName) {
                        $query->where('tag_text', $tagFromName);
                        if ($tagFromId) {
                            $query->orWhere('tag_id', $tagFromId);
                        }
                    })
                    ->whereIn('proj_exper_id', $projExperTagIds)
                    ->delete();
                $deletedTags[] = $tagFromId;
            }
            // xóa các tag cần chuyển đi
            Tag::whereIn('id', $deletedTags)->delete();
            // cập nhật tag sang approve
            Tag::whereIn('id', $updatedTags)->update(['status' => TagConst::TAG_STATUS_APPROVE]);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
