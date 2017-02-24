<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_map extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_mode();
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_vdefault();
		self::_fm_search();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		if(self::$newField['vdefault'] = empty(self::$fmdata['vdefault']) ? '' : trim(self::$fmdata['vdefault'])){
			list($lng, $lat) = explode(',', self::$newField['vdefault']);
			if(is_numeric($lng) && is_numeric($lat)){
				$lng = floatval($lng); $lat = floatval($lat);
				if($lng < -90 || $lng > 90 || $lat < -180 || $lat > 180){
					self::$newField['vdefault'] = '';
				}else{
					self::$newField['vdefault'] = $lng.','.$lat;
				}
			}else{
				self::$newField['vdefault'] = '';
			}
		}
		# 如果默认值等于系统默认值，则留空数值，利于打包
		if(self::$newField['vdefault'] == cls_env::GetG('init_map')) self::$newField['vdefault'] = '';
	}
	# 表单之地图类型
    protected static function _fm_mode(){
		trbasic('地图类型','',makeradio('fmdata[mode]',array(0 => 'baidu',),0),'');
	}
	# 表单之默认输入值
    protected static function _fm_vdefault(){
		$Value = self::$isNew ? '' : self::$oldField['vdefault'];
		if(empty($Value)) $Value = cls_env::GetG('init_map');
		trbasic('地图初始定位坐标','',"<input class=\"btnmap\" type=\"button\" onmouseover=\"this.onfocus()\" onfocus=\"_08cms.map.setButton(this,'marker','fmdata[vdefault]','','13','$Value');\" /> <label for=\"fmdata[vdefault]\">纬度,经度：</label><input type=\"text\" id=\"fmdata[vdefault]\" name=\"fmdata[vdefault]\" value=\"$Value\" style=\"width:150px\">",'',
		array('guide'=>'输入初始定位位置：纬度,经度。如留空则为系统默认位置','w'=>50));
	}	
	
}
