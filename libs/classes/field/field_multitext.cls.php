<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_multitext extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_notnull();
		self::_fm_regular();
		self::_fm_guide();
		self::_fm_mode();
		self::_fm_min_max();
		self::_fm_nohtml();
		self::_fm_rpid();
		self::_fm_filter();
		self::_fm_wmid();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		self::$newField['mode'] = empty(self::$fmdata['mode']) ? 0 : 1;
		foreach(array('min','max') as $key){
			self::$newField[$key] = max(0,intval(self::$fmdata[$key]));
			self::$newField[$key] = empty(self::$newField[$key]) ? '' : self::$newField[$key];
		}
	}
	# 表单之表单控件模式
    protected static function _fm_mode(){
		$Value = empty(self::$oldField['mode']) ? 0 : 1;
		trbasic('表单控件模式','',makeradio('fmdata[mode]',array(0 => '常规尺寸',1 => '加大尺寸'),$Value),'');
	}
	# 表单之输入值字节长度限制
    protected static function _fm_min_max(){
		$ValueMin = empty(self::$oldField['min']) ? '' : self::$oldField['min'];
		$ValueMax = empty(self::$oldField['max']) ? '' : self::$oldField['max'];
		trrange('输入值字节长度限制', array('fmdata[min]',$ValueMin,'','&nbsp; -&nbsp; ',5, 'validate' => makesubmitstr('fmdata[min]',0,'int')),
								array('fmdata[max]',$ValueMax,'','',5, 'validate' => makesubmitstr('fmdata[max]',0,'int')));
	}
	# 表单之提交前过滤
    protected static function _fm_filter(){
		$Value = self::$isNew ? 0 : self::$oldField['filter'];
    	trbasic('提交前过滤','fmdata[filter]',makeoption(array(0=>'不过滤', 1=>'保护性过滤HTML'),$Value),'select');
	}
	
	protected static function _fm_regular(){
	    $Value = self::$isNew ? 0 : self::$oldField['regular'];
		trbasic('不良关键字显示提示','fmdata[regular]',$Value,'radio',array('guide'=>'仅发布或修改时js显示提示，其它限制请自定义处理，或和标签调用一起使用。'));
	}
	
}
