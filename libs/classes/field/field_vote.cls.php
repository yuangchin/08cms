<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_vote extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_min();
		self::_fm_max();
		self::_fm_nohtml();
		self::_fm_mode();
		self::_fm_length();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		
		# 最多允许添加几个投票,每个投票最多几个选项
		foreach(array('min','max') as $key){
			self::$newField[$key] = max(1,intval(self::$fmdata[$key]));
		}
		
	}
	# 表单之最多允许添加几个投票
    protected static function _fm_min(){
		$Value = empty(self::$oldField['min']) ? 1 : self::$oldField['min'];
		trbasic('最多允许添加几个投票','fmdata[min]',$Value,'text', array('validate' => makesubmitstr('fmdata[min]',1,'int',1,50,'int')));
	}
	# 表单之每个投票最多几个选项
    protected static function _fm_max(){
		$Value = empty(self::$oldField['max']) ? 1 : self::$oldField['max'];
		trbasic('每个投票最多几个选项','fmdata[max]',$Value,'text', array('validate' => makesubmitstr('fmdata[max]',1,'int',1,20,'int')));
	}
	# 表单之禁止游客投票
    protected static function _fm_nohtml(){
		$Value = self::$isNew ? 0 : self::$oldField['nohtml'];
		trbasic('禁止游客投票','fmdata[nohtml]',$Value,'radio');
	}
	# 表单之不能重复投票
    protected static function _fm_mode(){
		$Value = empty(self::$oldField['mode']) ? 0 : 1;
		trbasic('不能重复投票','fmdata[mode]',$Value,'radio');
	}
	# 表单之重复投票时间间隔(分钟)
    protected static function _fm_length(){
		$Value = self::$isNew ? '' : self::$oldField['length'];
		trbasic('重复投票时间间隔(分钟)','fmdata[length]',$Value,'text', array('validate' => makesubmitstr('fmdata[length]',0,'int',0,300,'int')));
	}
	

}
