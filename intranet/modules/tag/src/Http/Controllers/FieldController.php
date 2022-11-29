<?php

namespace Rikkei\Tag\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\View\TagGeneral;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Menu;

class FieldController extends Controller 
{
    /**
     * view manage field
     */
    public function index()
    {
        Menu::setActive('project', 'tag/field/manage');
        return view('tag::field.manage.index', [
            'fieldsPath' => Field::getFieldPath(TagConst::SET_TAG_PROJECT)
        ]);
    }
    
    /**
     * get a item field
     */
    public function getItem()
    {
        $id = Input::get('id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $response['success'] = 1;
            if (!CacheHelper::has(Field::KEY_CACHE_LIST, TagConst::SET_TAG_PROJECT)) {
                $response['fieldsPath'] = Field::getFieldPath(TagConst::SET_TAG_PROJECT);
            }
            return response()->json($response);
        }
        $field = Field::getItemResponse($id);
        if (!$field) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['field'] = $field->toArray();
        if (!CacheHelper::has(Field::KEY_CACHE_LIST, TagConst::SET_TAG_PROJECT)) {
            $response['fieldsPath'] = Field::getFieldPath(TagConst::SET_TAG_PROJECT);
        }
        return response()->json($response);
    }
    
    /**
     * save field
     */
    public function save()
    {
        $id = Input::get('item.id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $field = new Field();
            $isCreateNew = true;
        } else {
            $field = Field::find($id);
            if (!$field) {
                $response['success'] = 0;
                $response['message'] = Lang::get('core::message.Not found item');
                return response()->json($response);
            }
            $isCreateNew = false;
        }
        $dataItem = (array)Input::get('item');
        $validator = Validator::make($dataItem, [
            'name' => 'required|max:255'
        ]);
        if ($validator->fails()) {
            $response['success'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        $parentOld = $field->parent_id;
        $dataItem = TagGeneral::arrayEmptyToNull($dataItem);
        $field->setData($dataItem);
        if (!$field->parent_id) {
            $field->parent_id = $field->set;
        }
        $parentNew = $field->parent_id;
        if ($parentNew != $parentOld) {
            $field->sort_order = 0;
        }
        try {
            $field->save();
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Save data success');
            $response['field'] = [
                'id' => $field->id,
                'name' => $field->name,
                'parent_id' => $field->parent_id,
                'is_new' => $isCreateNew,
                'parent_id_old' => $parentOld,
                'color' => $field->color,
                'type' => $field->type
            ];
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * delete field
     */
    public function delete()
    {
        $id = Input::get('id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $field = Field::find($id);
        if (!$field) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            $field->delete();
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Delete field <b>:name</b> success',
                ['name' => $field->name]);
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * get a item field
     */
    public function getTagItem()
    {
        $id = Input::get('id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $field = Field::getItemResponse($id);
        if (!$field) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['tag'] = Tag::getTagsOfField($field);
        $response['field'] = [
            'color' => $field->color,
            'type' => $field->type
        ];
        if (!CacheHelper::has(Field::KEY_CACHE_LIST, TagConst::SET_TAG_PROJECT)) {
            $response['fieldsPath'] = Field::getFieldPath(TagConst::SET_TAG_PROJECT);
        }
        return response()->json($response);
    }
    
    /**
     * delete tag of field
     */
    public function tagDelete()
    {
        $id = Input::get('id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $tag = Tag::find($id);
        if (!$tag) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            $tag->delete();
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Delete tag <b>:name</b> success',
                ['name' => $tag->value]);
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * add new tag of field
     */
    public function tagAdd()
    {
        $fieldId = Input::get('field_id');
        $tagName = Input::get('tag_name');
        $response = [];
        if (!$fieldId || !is_numeric($fieldId) || !$tagName) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $field = Field::find($fieldId);
        if (!$field) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            $result = Tag::addTagForField($fieldId, $tagName);
            if (!$result) {
                $response['success'] = 0;
                $response['message'] = Lang::get('tag::message.Tag <b>:name</b> exists',
                    ['name' => $tagName]);
                return response()->json($response);
            }
            $response['success'] = 1;
            $response['tagItem'] = [
                'id' => $result->id
            ];
            $response['message'] = Lang::get('tag::message.Add tag <b>:name</b> success',
                ['name' => $tagName]);
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * add new tag of field
     */
    public function tagSave()
    {
        $tagId = Input::get('tag_id');
        $tagName = Input::get('tag_name');
        $response = [];
        if (!$tagId || !is_numeric($tagId) || !$tagName) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $tag = Tag::find($tagId);
        if (!$tag) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            $result = Tag::saveTag($tag, $tagName);
            if (!$result) {
                $response['success'] = 0;
                $response['message'] = Lang::get('tag::message.Tag <b>:name</b> exists',
                    ['name' => $tagName]);
                return response()->json($response);
            }
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Save tag <b>:name</b> success',
                ['name' => $tagName]);
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * approve tag review
     */
    public function tagApprove()
    {
        $id = Input::get('id');
        $response = [];
        if (!$id || !is_numeric($id)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        $tag = Tag::find($id);
        if (!$tag) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Not found item');
            return response()->json($response);
        }
        try {
            $tag->approveTag();
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Tag <b>:name</b> approve success',
                ['name' => $tag->value]);
            $response['tagItem'] = [
                'id' => $tag->id,
                'value' => $tag->value
            ];
            return response()->json($response);
        } catch (Exception $ex) {
            $response['success'] = 0;
            $response['message'] = Lang::get('core::message.Error system');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * count tag of fields
     */
    public function tagCount()
    {
        $fieldIds = Input::get('fieldIds');
        if (!$fieldIds) {
            return response()->json(['count_tag_review' => null]);
        }
        $fieldIds = explode('-', $fieldIds);
        if (!count($fieldIds)) {
            return response()->json(['count_tag_review' => null]);
        }
        return response()->json([
            'count_tag_review' => Tag::countTagReviewOfFields($fieldIds)
        ]);
    }
    
    /**
     * submit tag review link
     */
    public function tagReviewLink()
    {
        $validator = Validator::make(Input::get(), [
            'tagOrg' => 'required|integer',
            'tagAs' => 'required|integer',
        ]);
        $response = [];
        if ($validator->fails()) {
            $response['success'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        $tagOrg = Input::get('tagOrg');
        $tagAs = Input::get('tagAs');
        try {
            Tag::tagAlias($tagOrg, $tagAs);
            $response['success'] = 1;
            $response['message'] = Lang::get('tag::message.Link tag success');
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['success'] = 0;
            $response['message'] = 'System error';
            return response()->json($response);
        }
    }
}
