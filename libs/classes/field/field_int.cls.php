<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_int extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_vdefault();
		self::_fm_min_max();
		self::_fm_regular();
		self::_fm_search();
		self::_fm_cfgs();
		
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		foreach(array('vdefault','min','max') as $key){
			self::$newField[$key] = trim(self::$fmdata[$key]);
			if(self::$newField[$key] != '') self::$newField[$key] = intval(self::$newField[$key]);
		}
	}
	# 表单之默认输入值
    protected static function _fm_vdefault(){
		$Value = self::$isNew ? '' : self::$oldField['vdefault'];
		trbasic('默认输入值','fmdata[vdefault]',$Value,'text',array('validate'=>makesubmitstr('fmdata[vdefault]',0,'int',0,11)));
	}
	# 表单之输入值范围限制
    protected static function _fm_min_max(){
		$ValueMin = empty(self::$oldField['min']) ? '' : self::$oldField['min'];
		$ValueMax = empty(self::$oldField['max']) ? '' : self::$oldField['max'];
		trrange('输入值范围限制', array('fmdata[min]',$ValueMin,'','&nbsp; -&nbsp; ',5, 'validate' => makesubmitstr('fmdata[min]',0,'int',0,11)),
								array('fmdata[max]',$ValueMax,'','',5, 'validate' => makesubmitstr('fmdata[max]',0,'int',0,11)));
	}
	
		
}
