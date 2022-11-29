<?php

namespace Rikkei\Help\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Str;

class Help extends CoreModel {

    use SoftDeletes;

    protected $table = 'helps';
    protected $fillable = ['id', 'parent', 'parent', 'order', 'content', 'slug'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const ALL_HELPS = 'allhelps';
    
    const TYPE_VIEW = 'view';
    const TYPE_CREATE = 'create';
    const TYPE_EDIT = 'edit';
    
    /**
     * delete node and its children
     */
    public static function boot() {
        parent::boot();

        static::deleting(function($help) {
            foreach ($help->getChildren()->get() as $child){
                $child->delete();
            }            
        });
    }
    
    /**
     * get parent
     * @return parent
     */
    public function getParent() {
        return $this->belongsTo('Rikkei\Help\Model\Help', 'parent');
    }

    /**
     * get children
     * @return children
     */
    public function getChildren() {
        return $this->hasMany('Rikkei\Help\Model\Help', 'parent');
    }

    /**
     * get all helps
     * @return array of help
     */
    public static function getAllHelp() {       
        return self::buildTree(self::orderBy('order')->get()->toArray(),null);            
    }
    
    /**
     * build tree from flat array
     * @param type $elements
     * @param type $parentId
     * @return type
     */
    public static function buildTree($elements, $parentId) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                else{
                    $element['children'] = array();
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
    
    /**
     * build menu help
     * @param String $pageType type of page (create, edit, view)
     * @return json menu
     */
    public static function buildMenuTree($pageType){     
        if (CacheHelper::get(self::ALL_HELPS)) {            
            return json_encode(CacheHelper::get(self::ALL_HELPS));
        }
        
        $jstreeHelp = array();
        
        if($pageType != self::TYPE_VIEW){
            $helps = self::orderBy('parent')->orderBy('order')->get();
        }
        else{
            $helps = self::where('active', self::STATUS_ACTIVE)->orderBy('parent')->orderBy('order')->get();
        }        
        
        foreach ($helps as $help){
            if (!$help['parent']){
                $help['parent'] = '#';
            }
            if(count($help->getChildren()->get()) > 0){
                $jstreeHelp[] = ['id' => $help['id'], 'parent' => $help['parent'], 'text' => e($help['title']), 'type' => 'parent'];
            }
            else{
                $jstreeHelp[] = ['id' => $help['id'], 'parent' => $help['parent'], 'text' => e($help['title']), 'type' => 'leaf'];
            }            
        }          
        CacheHelper::put(self::ALL_HELPS, $jstreeHelp);
        
        return json_encode($jstreeHelp);
    }
   
    /**
     * get all status
     * 
     * @return array
     */
    public static function getAllStatus() {
        return [
            self::STATUS_INACTIVE => 'Disable',
            self::STATUS_ACTIVE => 'Enable',
        ];
    }

    /**
     * get status of item
     * 
     * @param array $allStatus
     * @return string
     */
    public function getLabelStatus(array $allStatus = []) {
        if (!$allStatus) {
            $allStatus = self::getAllStatus();
        }
        if (isset($allStatus[$this->active])) {
            return $allStatus[$this->active];
        }
        return null;
    }

    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = []) {
        $tbname = self::getTableName();
        $tbHelp = DB::table($tbname);
        DB::beginTransaction();
        try {
            // auto created_by
            if (!$this->created_by && Permission::getInstance()->getEmployee()) {
                $this->created_by = Permission::getInstance()->getEmployee()->id;
            }            
            
            // update active child               
            if($this->active == self::STATUS_INACTIVE){
                $descendant = self::getKeyArray($this->getDescendant());     
                if (count($descendant) > 0){
                    $tbHelp->whereIn('id', $descendant)->update(['active' => self::STATUS_INACTIVE]);
                }
            }
            
            // update active parent            
            if($this->active == self::STATUS_ACTIVE){
                $ancestors = $this->getAncestors();
                if(count($ancestors)>0){
                    $tbHelp->whereIn('id', $ancestors)->update(['active' => self::STATUS_ACTIVE]);
                }
            }                        
            // auto render slug
            $this->slug = $this->getHelpSlug();
            $result = parent::save($options);
            CacheHelper::forget(self::ALL_HELPS);
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * get help slug
     *  require id, parent, slug, title
     */
    public function getHelpSlug()
    {
        if (!$this->slug) {
            $slugParent = '';
            $parentItem = $this->parent;
            if ($parentItem) {
                $parentItem = self::select(['slug'])
                    ->where('id', $parentItem)
                    ->first();
                if ($parentItem) {
                    $slugParent = $parentItem->slug;
                }
            }
            $this->slug = Str::slug($slugParent . '-' . $this->title);
        } else {
            $this->slug = Str::slug($this->slug);
        }
        // render slug ultil not exits slug
        while(1) {
            $existsSlug = self::select(['id'])
                ->where('slug', $this->slug);
            if ($this->id) {
                $existsSlug->where('id', '!=', $this->id);
            }
            $existsSlug = $existsSlug->first();
            if ($existsSlug) {
                $this->slug = $this->slug . substr(md5(mt_rand() . time()), 0, 5);
            } else {
                break;
            }
        }
        return $this->slug;
    }

    /**
     * return all ancestors of selected node
     * @return array ancestors of node
     */
    public function getAncestors() {
        $ancestors = array();  

        $help = $this->getParent()->first();

        //find ancestors of node
        while ($help['id']) {
            $ancestors[] = $help['id'];   
            $help = $help->getParent()->first();
        }  

        return $ancestors;
    }
    
    /**
     * return all descendants of node
     * @param string $id node id
     * @return array $descendant
     */
    public function getDescendant() {
        $children = $this->getChildren()->get();        
        $descendant = array();

        if (count($children) > 0) {
            # It has children, let's get them.
            foreach ($children as $child) {
                # Add the child to the list of children, and get its subchildren
                $descendant[$child['id']] = $child->getDescendant();
            }
        }
        
        return $descendant;
    }

    /**
     * return key array of input array
     * @param array input array
     * @return array of keys
     */
    static function getKeyArray(array $array) {
        $keys = array();

        foreach ($array as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $keys = array_merge($keys, self::getKeyArray($value));
            }
        }

        return $keys;
    }

    /**
     * get help item by id or slug
     *
     * @param type $id
     */
    public static function getHelpItemByIdSlug($id = null)
    {
        if (!$id) {
            return new self();
        }
        if (is_numeric($id)) {
            $help = Help::find($id);
            if ($help && $help->active == self::STATUS_ACTIVE) {
                return $help;
            }
        }
        $help = self::where('slug', $id)
            ->first();
        if ($help && $help->active == self::STATUS_ACTIVE) {
            return $help;
        }
        return new self();
    }
}
