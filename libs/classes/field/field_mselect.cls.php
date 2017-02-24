<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 针对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_mselect extends cls_fieldconfig{
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		self::_fm_length();
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_vdefault();
		self::_fm_mode();
		self::_fm_search();
		self::_fm_innertext();
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
		
		# 表单之可选项设置
		self::$newField['fromcode'] = empty(self::$fmdata['fromcode']) ? 0 : 1;
		self::$newField['innertext'] = empty(self::$fmdata['innertext']) ? '' : trim(self::$fmdata['innertext']);
		if(empty(self::$newField['fromcode'])) self::$newField['innertext'] = str_replace("\r","",self::$newField['innertext']);
		
		# 默认值
		self::$newField['vdefault'] = str_replace('[##]',"\t",empty(self::$fmdata['vdefault']) ? '' : trim(self::$fmdata['vdefault']));
		
	}
	# 表单之数库表字段长度
	# 对于会员通用字段，在具体模型字段编辑时，此项不显示及修改，只可在通用字段编辑时可改。
    protected static function _fm_length(){
		if(in_array(self::$SourceType,array('mchannel')) && !empty(self::$oldField['iscommon']) && !empty(self::$SourceID)) return;
		$Value = self::$isNew ? '' : self::$oldField['length'];
		trbasic('数据表字段长度','fmdata[length]',$Value,'text',array('guide'=>'设定范围1-255', 'validate' => makesubmitstr('fmdata[length]',0,0,1,255,'int')));
	}
	# 表单之表单控件模式
    protected static function _fm_mode(){
		$Value = empty(self::$oldField['mode']) ? 0 : 1;
		trbasic('表单控件模式','',makeradio('fmdata[mode]',array(0 => '多选列表',1 => '多选框(checkbox)'),$Value),'');
	}
	# 表单之可选项设置
    protected static function _fm_innertext(){
		$fromcodestr = OneCheckBox('fmdata[fromcode]','来自代码返回数组',empty(self::$oldField['fromcode']) ? 0 : 1);
		$Value = self::$isNew ? '' : self::$oldField['innertext'];
		$guide = '每行填写一个选项，';
		$guide .= '格式1：选项值（同时为显示标题），格式2：选项值=选项显示标题。';
		$guide .= '<br> 如选 来自代码返回数组，请填写PHP代码，使用return array(数组内容);得到选择内容。<br>如使用扩展函数，请定义到'._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php';
		trbasic('选择内容设置<br>'.$fromcodestr,'fmdata[innertext]',$Value,'textarea',array('guide'=>$guide));
	}
	
}
