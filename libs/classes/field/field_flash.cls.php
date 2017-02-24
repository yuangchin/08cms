<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_flash extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_mode();
		self::_fm_rpid();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		self::$newField['mode'] = empty(self::$fmdata['mode']) ? 0 : 1;
	}
	# 表单之表单控件显示播放器列表
    protected static function _fm_mode(){
		$Value = empty(self::$oldField['mode']) ? 0 : 1;
		trbasic('表单控件显示播放器列表','fmdata[mode]',$Value,'radio');
	}
	
}
