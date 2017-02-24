<?php

/**
 * 腾讯街景类
 * @param array    $Map                 街景地图数据
 * @param float    $Map['lat']          坐标纬度 
 * @param float    $Map['lng']          坐标经度
 * @param string   $Map['divId']        街景所在div的ID
 * @param int      $Map['type']         坐标来源数据所属服务商。可选值为 :
                                            1:gps经纬度，2:搜狗经纬度，3:百度经纬度
                                            4:mapbar经纬度，5:google经纬度，6:搜狗墨卡托 
 * @param int      $Map['heading']      偏航角：与正北方向的夹角，顺时针一周为360度（默认360）
 * @param int      $Map['pitch']        俯仰角：简单的说就是抬头或低头的角度。
                                        水平为0度，低头为0至90度，抬头为0至-90度。（默认9）
 * @param int      $Map['zoom']         缩放：分为1至4级，像望远镜一样，4级放得最大，看得最远（默认1）
 * @author lyq0328
 * @copyright 2014
 */

class cls_Tencent{
    /**
     *街景 
     */
    public static function view($Map,$Mconfigs){
        //街景密钥
        $streetViewKey = empty($Mconfigs['streetviewkey'])?'':trim($Mconfigs['streetviewkey']);
        if(empty($streetViewKey)) return;
        if(empty($Map) || empty($Map['divid'])) return false;
        //检查是否设置坐标，无则返回
        if(false === self::isSetLatLng($Map)) return false;
        //设置街景所需JS变量
        self::setViewJsParam($Map,$streetViewKey);
        //显示街景
        self::streetViewJs();        
    }
    
    /**
     * 文档没有地图坐标，则返回（没必要默认一个坐标）
     * @param float    $Map['lat']            坐标纬度 
     * @param float    $Map['lng']            坐标经度 
     * @param string   $Map['divId']          街景所在div的ID
     */
    protected function isSetLatLng($Map){
        echo "<script type='text/javascript'>var DivId = '".(empty($Map['divid'])?'':$Map['divid'])."';</script>";
        if(empty($Map['lat']) || empty($Map['lng'])) {
            echo "<script type=\"text/javascript\">$('#'+DivId).html('该文档没有地图坐标。');</script>";     
            return false;      
        } 
    }
    
    /**
     * 设置JS需要的变量 以及显示div
     * @param array    $Map                   街景地图数据
     * @param float    $Map['lat']            坐标纬度 
     * @param float    $Map['lng']            坐标经度
     * @param string   $Map['divId']          街景所在div的ID
     * @param int      $Map['type']           坐标来源数据所属服务商。可选值为 :
                                                1:gps经纬度，2:搜狗经纬度，3:百度经纬度
                                                4:mapbar经纬度，5:google经纬度，6:搜狗墨卡托 
     * @param int      $Map['heading']        偏航角：与正北方向的夹角，顺时针一周为360度（默认360）
     * @param int      $Map['pitch']          俯仰角：简单的说就是抬头或低头的角度。
                                              水平为0度，低头为0至90度，抬头为0至-90度。（默认9）
     * @param int      $Map['zoom']           缩放：分为1至4级，像望远镜一样，4级放得最大，看得最远（默认1）
     */
    protected function setViewJsParam($Map,$streetViewKey){
        echo    "<script src='http://map.qq.com/api/js?v=2.exp&key=".$streetViewKey."&libraries=convertor'></script>";
        echo    "<script type='text/javascript'>
                    var Lat = '".(empty($Map['lat'])?'':$Map['lat'])."';
                    var Lng = '".(empty($Map['lng'])?'':$Map['lng'])."';                    
                    var Type = ".(empty($Map['type'])?3:min(6,intval($Map['type'])))."; 
                    var Heading = ".(empty($Map['heading'])?360:min(360,intval($Map['heading'])))."; 
                    var Pitch = ".(empty($Map['pitch'])?9:max(-90,intval($Map['pitch'])))."; 
                    var Zoom = ".(empty($Map['zoom'])?1:max(1,intval($Map['zoom']))).";            
                    $('#'+DivId).css('display','block');           
                </script>";
    }
    
    
    /**
     * 在指定的DIV中显示街景
     * JS：
       translate(points:LatLng | Point | Array.<LatLng> | Array.<Point>, type:Number, callback:Function)
       将其他地图服务商的坐标批量转换成搜腾讯地图经纬度坐标
     */    
    protected function streetViewJs(){
        echo <<<EOT
            <script type="text/javascript">
    			qq.maps.convertor.translate(new qq.maps.LatLng(Lat,Lng),Type,function(res){	
    				//转换之后的坐标
    				var coordinates = String(res[0]);
    			 	coordinates = coordinates.split(',');
    				var	center = new qq.maps.LatLng(coordinates[0],coordinates[1]);	
    				
    				pano_service = new qq.maps.PanoramaService();			
    				var radius;
    				pano_service.getPano(center, radius, function (result){			
    					var pano = new qq.maps.Panorama(document.getElementById(DivId), {
    						pano: result.svid,
    						disableFullScreen: false,
    						disableMove: false,
    						pov:{
    							heading:Heading,
    							pitch:Pitch
    						},
    						zoom:Zoom
    					});
    				});
    		
    			})
            </script>
EOT;
    }
    
    
}


?>