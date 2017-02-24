<?php
/*
** 列表中列项目的数据处理类
** 含列的索引单元及内容单元
*/
!defined('M_COM') && exit('No Permisson');
class cls_cucolsbase extends cls_cubasic{

	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $cfgs = array();//列项目配置
	public $titles = array();//索引行的title数组
	public $groups = array();//分组配置信息
	
    function __construct($cfg = array()){
		parent::__construct($cfg);
		$this->A = $cfg;	
	}
	protected function call_method($func,$args = array()){////可以额外增加传参
		if(method_exists($this,$func)){
			return call_user_func_array(array(&$this,$func),$args);
		}else return 'undefined';
	}
	
	//type：定义类型之后，会启用function type_{type}()来处理,date,bool,url,select等方法
	//title：索引行标题
	//width：列宽度
	//side：左右位置(L/R/C)，默认为C
	//view：列是否隐藏，空(显示但可隐藏)/S(不能隐藏)/H(默认隐藏)
	//url：链接url，允许任何当前行资料使用占位符{xxxx}调用
	//winsize-窗口大小参数:如500,300,在url中使用
	//mtitle：正文标题，或url显示标题
	//umode：url打开方式,0(默认浮动窗)/1(新窗口)/2(本窗口)
	//len：正文内容截取长度，如subject
	//num：多选项的显示数量
	//empty：为空时的显示内容
	//fmt：时间方法的格式化参数
	
	//完全定制方法：user_$key，此为优先使用的方法
	//系统内置方法：type_$key，在以下情况下调用：定义了type、未定制的类系、其它未指定方法的显示项
	function additem($key = '',$cfg = array()){ 
		//会将索引行的内容先处理好
		if(!$key) return;
		$this->cfgs[$key] = $cfg; 
		$re = $this->call_method("user_$key",array(1));//定制方法
		if($re == 'undefined'){ 
			$re = $this->type_method($key,1);//按类型的方法
		}
		return $re;
	}
	
	function fetch_one_row($data = array()){//返回单个文档的数据
		$mains = array();//初始化及清空上一行的数据
		foreach($this->cfgs as $key => $cfg){
			$mains[$key] = $this->one_item($key,$data);
		}
		$mains = $this->deal_group($mains,'main');
		return $mains;
	}
	
	function fetch_top_row(){//返回索引行的数据
		return $this->deal_group($this->titles,'top');
	}
	
	function addgroup($mainstyle,$topstyle = ''){//增加分组
		if(preg_match_all("/\{(\w+?)\}/is",$mainstyle,$matches)){
			if($keys = array_unique($matches[1])){
				foreach($this->groups as $k => $v){
					if(array_intersect($keys,$v['keys'])) return;
				}
				$this->groups[$keys[0]] = array(
				'keys' => $keys,
				'mainstyle' => $mainstyle,
				'topstyle' => $topstyle,
				);
			}
		}
	}
	
	protected function deal_group($source = array(),$type = 'top'){
		if(!$source) return $source;
		$var_style = $type.'style';
		if($this->groups){
			foreach($source as $k => $v){
				if(!empty($this->groups[$k])){
					$na = array();
					foreach($this->groups[$k]['keys'] as $gk){
						if(isset($source[$gk])) $na[$gk] = $source[$gk];
						if($gk != $k) unset($source[$gk]);
					}
					$source[$k] = key_replace($this->groups[$k][$var_style],$na);
				}
			}
		}
		return $source;
	}
	
	protected function type_method($key = '',$mode = 0,$data = array()){
		if(!empty($this->cfgs[$key]['type'])){ //内置专用类型的方法,如bool,url,field等
			$re = $this->call_method("type_{$this->cfgs[$key]['type']}",array($key,$mode,$data));
			if($re == 'undefined') $re = $mode ? '' : false;
		}else $re = $this->type_other($key,$mode,$data);//普通字段,按$key来提取数据内容
		if($mode && $re === false) $re = '';
		return $re;
	}
	
	//返回单列内容
	protected function one_item($key = '',$data = array()){//处理单列内容
		if(!isset($this->cfgs[$key])) return '';
		$cfg = $this->cfgs[$key];
		
		$re = $this->call_method("user_$key",array(0,$data));//定制方法
		if($re == 'undefined'){
			$re = $this->type_method($key,0,$data);//按类型的方法
		}
		if(!empty($cfg['prefix'])) $re = key_replace($cfg['prefix'],$data).$re;
		if(!empty($cfg['suxfix'])) $re .= key_replace($cfg['suxfix'],$data);
		return $re;
	}
	protected function del_item($key){
		unset($this->cfgs[$key]);
		return false;
	}
	
	// 架构字段可不带title参数,可从cname自动获得; 常用字段checked,mname也可默认
	protected function top_title($key,$cfg){
		if(!empty($cfg['title'])){
			$re = $cfg['title'];
		}elseif(isset($this->fields[$key])){
			$re = $this->fields[$key]['cname'];
		}elseif($key=='checked'){
			$re = '审核';
		}elseif($key=='mname'){
			$re = '会员';
		}else{
			$re = $key;	
		}
		return $re;
	}
	
	protected function input_text($varname,$value = '',$width = 4){
		if(!$varname) return $value;
		return "<input type=\"text\" size=\"$width\" id=\"$varname\" name=\"$varname\" value=\"".mhtmlspecialchars($value)."\" />\n";
	}
	protected function input_checkbox($varname,$value = 0,$chkedvalue = 1){
		if(!$varname) return $value;
		return "<input type=\"hidden\" name=\"$varname\" value=\"\"><input type=\"checkbox\" class=\"checkbox\" name=\"$varname\" value=\"$chkedvalue\"".($value == $chkedvalue ? ' checked' : '').">\n";
	}
	
	//交互对象名称：针对[文档,会员]使用
	//addno:指定使用哪个附加页的url
	//aclass-<a>样式
	// empty($pid) && $oL->m_additem('subject',array('title'=>'被评公司','len'=>40,'field'=>'company'));
	protected function user_subject($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5); 
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'L';
			isset($cfg['view']) || $cfg['view'] = 'S';
			if(empty($cfg['title'])) $cfg['title'] = $this->ptype=='m' ? '被评会员' : '被评文档'; 
			$this->titles[$key] = $this->top_title($key,$cfg); 
		}else{ 
			return @$this->getPLink($data, $cfg); 
		}
	}
	
	//时间方法
	//fmt:时间方法的格式化参数
	//showEnd:按到期时间方式特殊显示(分颜色或显示<永久>), 默认enddate按此方式显示
	protected function type_date($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$arr = array('cucreate' => '添加时间',);
			if(empty($cfg['title']) && isset($arr[$key])) $cfg['title'] = $arr[$key];
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			// enddate默认按showEnd方式显示，显示<永久>, 或分颜色显示过期如否
			$showEnd = isset($cfg['showEnd']) ? $cfg['showEnd'] : ($key=='enddate' ? 1 : 0);
			$timestamp = TIMESTAMP;
			$null = isset($cfg['empty']) ? $cfg['empty'] : ($showEnd ? '&lt;永久&gt;' : '-');
			$fmt = isset($cfg['fmt']) ? $cfg['fmt'] : 'Y-m-d';
			$sval = date($fmt,intval($data[$key]));
			if($showEnd){
				$cval = date($fmt,$timestamp);
				if($cval>$sval){ $sval = "<span style='color:#FF0000'>$sval</span>"; } //已经过期:红色
				elseif($cval==$sval){ $sval = "<span style='color:#0000FF'>$sval</span>"; } //当天过期:蓝色
			}
			return empty($data[$key]) ? $null : $sval;
		}
	}
	
	//布尔值的方法：显示Y/-
	protected function type_bool($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			return empty($data[$key]) ? (isset($cfg['empty']) ? $cfg['empty'] : '-') : 'Y';
		}
	}
	
	//URL方法
	//winsize-窗口大小参数:如500,300,在url中使用
	//umode:打开方式，0为浮动窗打开，1为新窗口打开
	protected function type_url($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$addstr = empty($cfg['umode']) ? "onclick=\"return floatwin('open_arc$key',this".(empty($cfg['winsize']) ? '' : ','.$cfg['winsize']).")\"" : ($cfg['umode'] == 1 ? "target=\"_blank\"" : '');
			return "<a href=\"".key_replace($cfg['url'],$data)."\" {$addstr}>".(empty($cfg['mtitle']) ? (isset($data[$key]) ? $data[$key] : $key) : key_replace($cfg['mtitle'],$data))."</a>";
		}
	}
	
	//多选字段方法
	protected function type_field($key = '',$mode = 0,$data = array()){
		@$field = $this->fields[$key]; 
		if(!$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			if(empty($cfg['title'])) $cfg['title'] = $field['cname'];
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$num = empty($cfg['num']) ? 0 : $cfg['num'];
			return empty($data[$key]) ? (isset($cfg['empty']) ? $cfg['empty'] : '-') : view_field_title($data[$key],$field,$num);
		}
	}
	
	//普通字段,按$key来提取数据内容
	//empty：为空时显示内容
	//mtitle: 显示模版
	protected function type_other($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{ 
			$len = empty($cfg['len']) ? 40 : $cfg['len'];
			$dre = htmlspecialchars(cls_string::CutStr(@$data[$key],$len));
			if(isset($cfg['mtitle'])){
				$re = key_replace($cfg['mtitle'],$data);
			}else{
				$re = empty($data[$key]) ?  (isset($cfg['empty']) ? $cfg['empty'] : '-') : $dre;
			}
			return $re;
		}
	}
	
	//回复数量统计
	//winsize-窗口大小参数:如500,300,在url中使用
	//aclass-<a>样式
	//empty - $data[$key]为空时的替换值
	//tpl: 显示模版(如: “[{num}]”,num为占位符)
	protected function user_recounts($mode = 0,$data = array()){ 
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		//isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			if(empty($cfg['title'])) $cfg['title'] = '回复'; 
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			global $tblprefix;
			$field = empty($cfg['field']) ? 'tocid' : $cfg['field']; 
			$nums = $this->db->select('COUNT(*)')->from(self::table())->where(array($field=>$data['cid']))->exec()->fetch();
			$nums = $nums['COUNT(*)'];
			$nums = empty($nums)? (isset($cfg['empty']) ? $cfg['empty'] : '0') : $nums;
			$tpl = isset($cfg['tpl']) ? $cfg['tpl'] : '[{num}]';
			$re = str_replace('{num}',$nums,$tpl);
			if(empty($cfg['url'])) return $re;
			$addstr = empty($cfg['umode']) ? "onclick=\"return floatwin('open_arc$key',this".(empty($cfg['winsize']) ? '' : ','.$cfg['winsize']).")\"" : ($cfg['umode'] == 1 ? "target=\"_blank\"" : '');
			return "<a ".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".key_replace($cfg['url'],$data)."\" {$addstr}>$re</a>";
			
		}
	}
	
	//选择id
	protected function user_selectid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		isset($cfg['view']) || $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'selectid','chkall')\">";
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			return "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$data[cid]]\" value=\"$data[cid]\">";
		}
	}

}
