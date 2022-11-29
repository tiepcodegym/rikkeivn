<?php

namespace Rikkei\Sales\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CssView extends Model
{
    protected $table = 'css_view';
    
    use SoftDeletes;
    
    /**
     * Insert into table css_view
     * @param array $data
     * @return int
     */
    public function insert($data){
        try {
            $cssView = new CssView();
            $cssView->css_id = $data["css_id"];
            $cssView->name = $data["name"];
            $cssView->ip_address = $data["ip_address"];
            
            $cssView->save();        
            
            return $cssView->id;
        } catch (Exception $ex) {
            throw $ex;
        }
        
    }
    
    /**
     * Check viewed by css_id and ip_address
     * @param array $data
     * @return boolean
     */
    public function isViewed($data) {
        $count = self::where('css_id',$data["css_id"])
                ->where('ip_address',$data["ip_address"])
                ->where('name',$data['name'])
                ->count();
        if($count > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Get css view count
     * @param type $cssId
     * @return int
     */
    public function getCountByCss($cssId){
        return self::where("css_id",$cssId)->count();
    }
    
    
    /**
     * Get Css view list by Css
     * @param int $cssId
     * @param int $perPage
     * @return object list css view
     */
    public function getCssViewsByCss($cssId, $order, $dir){
        return self::join('css', 'css.id', '=', 'css_view.css_id')
                ->join('employees', 'employees.id', '=', 'css.employee_id')
                ->where("css_view.css_id",$cssId)
                ->orderBy($order, $dir)
                ->groupBy('css_view.id')
                ->select('css_view.*', 'employees.name as sale_name', 'css.sale_name_jp');
    }
}
