<?php

/**
 * 街景入口类 
 * @example 
        <div id="streetview" style="display:none;width:400px; height:300px; "></div>
        <?php
            cls_StreetView::StreetView(array('lat'=>'23.003088673571295','lng'=>'113.72987204787934','divid'=>'streetview'));
        ?> 
        注意：
	       1.div的id与函数中传递进去的divid要一致。
	       2.要确保使用该代码前已经引用了jquery文件
 * @author lyq0328
 * @copyright 2014
 */

class cls_StreetView{   
    /**
     * 根据后台设定的街景类型显示街景
       Tencent：腾讯街景
       noview：关闭街景        
     */
    public static function view($Map){
        //获取后台街景设置
        $mconfigs = cls_cache::Read('mconfigs');
        //街景类型
        $streetViewType = empty($mconfigs['streetviewtype'])?'':trim($mconfigs['streetviewtype']);
        //关闭街景
        if($streetViewType == 'noview') return false;
        $className = "cls_".$streetViewType;         
        if(class_exists($className)){            
            $className::view($Map,$mconfigs);
        }    
    }
}

?>