<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_text extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_length();
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_vdefault();
		self::_fm_mode();
		self::_fm_min_max();
		self::_fm_nohtml();
		self::_fm_mlimit();
		self::_fm_regular();
		self::_fm_rpid();
		self::_fm_search();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		
		# 数据表字段长度
		if(self::$isNew || isset(self::$fmdata['length'])){
			self::$newField['length'] = empty(self::$fmdata['length']) ? 10 : min(255,max(1,intval(self::$fmdata['length'])));
		}else{
			self::$newField['length'] = self::$oldField['length'];
		}
		
		# 输入值字节长度限制
		foreach(array('min','max') as $key){
			self::$newField[$key] = max(0,intval(self::$fmdata[$key]));
			self::$newField[$key] = empty(self::$newField[$key]) ? '' : self::$newField[$key];
		}
		
		# 默认值
		self::$newField['vdefault'] = empty(self::$fmdata['vdefault']) ? '' : trim(self::$fmdata['vdefault']);
		
	}
	# 表单之数库表字段长度
    protected static function _fm_length(){
		if(in_array(self::$SourceType,array('mchannel')) && !empty(self::$oldField['iscommon']) && !empty(self::$SourceID)) return;
		$Value = self::$isNew ? '' : self::$oldField['length'];
		trbasic('数据表字段长度','fmdata[length]',$Value,'text',array('guide'=>'设定范围1-255', 'validate' => makesubmitstr('fmdata[length]',0,0,1,255,'int')));
	}
	# 表单之默认输入值
    protected static function _fm_vdefault(){
		$Value = self::$isNew ? '' : self::$oldField['vdefault'];
		trbasic('默认输入值','fmdata[vdefault]',$Value,'text',array('w'=>50));
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
	# 表单之输入格式限制
    protected static function _fm_mlimit(){
		$limitarr = array(
			'' => '不限格式',
			'int' => '整数',
			'number' => '数字',
			'letter' => '字母',
			'numberletter' => '字母与数字',
			'tagtype' => '字母开始的字母数字下划线',
			'date' => '日期',
			'email' => 'E-mail',
		);
		$Value = self::$isNew ? '' : self::$oldField['mlimit'];
		trbasic('输入格式限制','fmdata[mlimit]',makeoption($limitarr,$Value),'select');
	}
	
	
	
		
}
