<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_date extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_mode();
		self::_fm_vdefault();
		self::_fm_min_max();
		self::_fm_search();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		foreach(array('vdefault','min','max') as $key){
			self::$newField[$key] = trim(self::$fmdata[$key]);
			self::$newField[$key] = (self::$newField[$key] && (cls_string::isDate(self::$newField[$key]) || cls_string::isDate(self::$newField[$key], 1))) ? strtotime(self::$newField[$key]) : '';
		}
	}
	# 表单之日期格式
    protected static function _fm_mode(){
		$Value = self::$isNew ? 0 : self::$oldField['mode'];
		trbasic('日期格式','',makeradio('fmdata[mode]',array(0 => '仅有日期', 1 => '日期时间'),$Value),'');
	}
	# 表单之默认输入值
    protected static function _fm_vdefault(){
		$Value = empty(self::$oldField['vdefault']) ? '' : date(empty(self::$oldField['mode']) ? 'Y-m-d' : 'Y-m-d H:i:s',self::$oldField['vdefault']);
		trbasic('默认输入值','', '<input type="text" id="fmdata[vdefault]" name="fmdata[vdefault]" value="'.$Value.'" onfocus="DateControl({format:\'fmdata[mode]\'})" class="Wdate" style="width:152px" rule="text" mode="date" />','');
	}	
	# 表单之输入范围
    protected static function _fm_min_max(){
		$ValueMin = empty(self::$oldField['min']) ? '' : date(empty(self::$oldField['mode']) ? 'Y-m-d' : 'Y-m-d H:i:s',self::$oldField['min']);
		$ValueMax = empty(self::$oldField['max']) ? '' : date(empty(self::$oldField['mode']) ? 'Y-m-d' : 'Y-m-d H:i:s',self::$oldField['max']);
		trbasic('输入日期范围限制','',	'<input type="text" id="fmdata[min]" name="fmdata[min]" value="'.$ValueMin.'" onfocus="DateControl({format:\'fmdata[mode]\'})" class="Wdate" style="width:152px" rule="text" mode="date" /> - ' .
										'<input type="text" id="fmdata[max]" name="fmdata[max]" value="'.$ValueMax.'" onfocus="DateControl({format:\'fmdata[mode]\'})" class="Wdate" style="width:152px" rule="text" mode="date" />','');
	}	
	
	
}
