<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_texts extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_min_max();
		self::_fm_innertext();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		
		# 输入值字节长度限制
		foreach(array('min','max') as $key){
			self::$newField[$key] = max(0,intval(self::$fmdata[$key]));
		}
		
		# 每条记录的内容项
		self::$newField['innertext'] = str_replace("\r","",empty(self::$fmdata['innertext']) ? '' : trim(self::$fmdata['innertext']));
		
	}
	# 表单之允许输入的记录条数
    protected static function _fm_min_max(){
		$ValueMin = empty(self::$oldField['min']) ? '' : self::$oldField['min'];
		$ValueMax = empty(self::$oldField['max']) ? '' : self::$oldField['max'];
		trrange('输入值字节长度限制', array('fmdata[min]',$ValueMin,'','&nbsp; -&nbsp; ',5, 'validate' => makesubmitstr('fmdata[min]',0,'int',0,10,'int')),
								array('fmdata[max]',$ValueMax,'','',5, 'validate' => makesubmitstr('fmdata[max]',0,'int',0,10,'int')));
	}
	# 表单之可选项设置:每条记录的内容项
    protected static function _fm_innertext(){
		$Value = self::$isNew ? '' : self::$oldField['innertext'];
		$guide = '设定每条记录的输入项，每行一项，每行的格式为：项标题|最小字节|最大字节';
		trbasic('每条记录的内容项','fmdata[innertext]',$Value,'textarea',array('guide'=>$guide, 'validate' => makesubmitstr('fmdata[innertext]',1,0,2)));
	}
	
	
	
}
