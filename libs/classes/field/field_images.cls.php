<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_images extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		
		self::_fm_notnull();
		self::_fm_min_max();
		self::_fm_guide();
		self::_fm_rpid();
		self::_fm_wmid();
		parent::_fm_autoCompression();
		self::_fm_search();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		foreach(array('min','max') as $key){
			self::$newField[$key] = max(0,intval(self::$fmdata[$key]));
			self::$newField[$key] = empty(self::$newField[$key]) ? '' : self::$newField[$key];
		}
	}
	# 表单之附件数量限制
    protected static function _fm_min_max(){
		$ValueMin = empty(self::$oldField['min']) ? '' : self::$oldField['min'];
		$ValueMax = empty(self::$oldField['max']) ? '' : self::$oldField['max'];
		trrange('附件数量限制', array('fmdata[min]',$ValueMin,'','&nbsp; -&nbsp; ',5, 'validate' => makesubmitstr('fmdata[min]',0,'int')),
								array('fmdata[max]',$ValueMax,'','',5, 'validate' => makesubmitstr('fmdata[max]',0,'int')));
	}
    
    # 表单之搜索条件,在图集中改为图片属性2的开关
    protected static function _fm_search(){
			$issearcharr = array('0' => '关闭','1' => '开启',);
			$Value = self::$isNew ? 0 : self::$oldField['issearch'];
			trbasic('图片属性2','',makeradio('fmdata[issearch]',$issearcharr,$Value),'',array('guide'=>'图片属性2开关，默认为关闭状态；图片属性2的名称请在下面配置imgComment。'));
	}
    
}
