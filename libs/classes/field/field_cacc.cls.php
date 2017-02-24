<?php
/* 
** 不同类型的字段的配置，使用方法汇总
** 对cls_fieldconfig的同名方法的扩展样例 ：public static function ex_demo()
*/
!defined('M_COM') && exit('No Permission');
class cls_field_cacc extends cls_fieldconfig{
	
	
	# 表单之不同类型字段组合编辑区块
    public static function _fm_custom_region(){
		
		self::_fm_coid();
		self::_fm_innertext();
		self::_fm_cnmode();
		self::_fm_mode();
		self::_fm_notnull();
		self::_fm_guide();
		self::_fm_vdefault();
		self::_fm_search();
		self::_fm_cfgs();
	}
	# 储存之不同类型字段的数据处理
    public static function _sv_custom_region(){
		
		# 类系选择
		if(self::$isNew){
			self::$newField['coid'] = empty(self::$fmdata['coid']) ? 0 : max(0,intval(self::$fmdata['coid']));
		}else{
			self::$newField['coid'] = self::$oldField['coid'];
		}
		
		# 类目id数组来源于代码返回值
		self::$newField['innertext'] = empty(self::$fmdata['innertext']) ? '' : trim(self::$fmdata['innertext']);
		
		# 默认值
		self::$newField['vdefault'] = str_replace('[##]',",",empty(self::$fmdata['vdefault']) ? '' : trim(self::$fmdata['vdefault']));
		
		# 分类的选择方式
		self::$newField['cnmode'] = empty(self::$fmdata['cnmode']) ? 0 : max(2,intval(self::$fmdata['cnmode']));
	}
	# 表单之来源类系
    protected static function _fm_coid(){
		$Value = self::$isNew ? (empty(self::$fmdata['coid']) ? 0 : self::$fmdata['coid']) : self::$oldField['coid'];
		$coidsarr = array(0 => '栏目') + cls_cotype::coidsarr(1,1);
		trbasic('来源类系','',$coidsarr[$Value],'');
		if(self::$isNew) trhidden('fmdata[coid]',$Value);
	}
	# 表单之可选项设置:类目id数组来源于代码返回值
    protected static function _fm_innertext(){
		$Value = self::$isNew ? '' : self::$oldField['innertext'];
		trbasic('类目id数组来源于代码返回值','fmdata[innertext]',$Value,'textarea',array('guide' => '请填写PHP代码，使用return array(数组内容);得到选择内容。留空则默认为所有栏目或类系中所有分类。<br>如使用扩展函数，请定义到'._08_EXTEND_DIR.DS._08_LIBS_DIR.DS.'functions'.DS.'custom.fun.php',));
	}
	# 表单之分类的选择方式
    protected static function _fm_cnmode(){
		# 推送分类只能单选
		if((self::$SourceType == 'pusharea') && !empty(self::$oldField['ename']) && in_array(self::$oldField['ename'],array('classid1','classid2'))) return;
		
		$arr = array(0 => '单选',);
		for($i = 2;$i < 21;$i ++) $arr[$i] = "{$i}选";
		$Value = self::$isNew ? 0 : self::$oldField['cnmode'];
		
		
		if(in_array(self::$SourceType,array('mchannel')) && !empty(self::$oldField['iscommon'])){ # 会员通用字段的选择模式不可更改
			trbasic('分类的选择方式','',$arr[$Value],'');
			trhidden('fmdata[cnmode]',$Value);
		}else trbasic('分类的选择方式','',makeradio('fmdata[cnmode]',$arr,$Value),'',array('guide'=>'请谨慎操作！！多选会影响某些查询效率，单选与多选间切换将更新数据库的大量数据。<br>多选转为单选时，将只保留第一个原有选择，且不可恢复。'));
	}
	# 表单之分类选择列表模式
    protected static function _fm_mode(){
		$Value = self::$isNew ? 0 : self::$oldField['mode'];
		$vmodearr = array('0' => '普通选择列表','2' => '多级联动','3' => '多级联动(ajax)',);
		trbasic('分类选择列表模式','',makeradio('fmdata[mode]',$vmodearr,$Value),'');
	}
	
}
