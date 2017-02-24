<?php
/*
** 列表中列项目的数据处理类
** 含列的索引单元及内容单元
*/
!defined('M_COM') && exit('No Permisson');
class cls_memcolsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $cfgs = array();//列项目配置
	public $titles = array();//索引行的title数组
	public $groups = array();//分组配置信息
	
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
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
	//coid：类系id
	//len：正文内容截取长度，如subject
	//icon：类系是否按图标显示
	//num：多选项的显示数量
	//empty：为空时的显示内容
	//fmt：时间方法的格式化参数
	
	//完全定制方法：user_$key，此为优先使用的方法
	//系统内置方法：type_$key，在以下情况下调用：定义了type、未定制的类系、其它未指定方法的显示项
	public function additem($key = '',$cfg = array()){
		//会将索引行的内容先处理好
		if(!$key) return;
		$this->cfgs[$key] = $cfg;
		$re = $this->call_method("user_$key",array(1));//定制方法
		if($re == 'undefined'){
			$re = $this->type_method($key,1);//按类型的方法
		}
		return $re;
	}
	
	public function fetch_one_row($data = array()){//返回单个文档的数据
		$mains = array();//初始化及清空上一行的数据
		foreach($this->cfgs as $key => $cfg){
			$mains[$key] = $this->one_item($key,$data);
		}
		$mains = $this->deal_group($mains,'main');
		return $mains;
	}
	
	public function fetch_top_row(){//返回索引行的数据
		return $this->deal_group($this->titles,'top');
	}
	
	public function addgroup($mainstyle,$topstyle = ''){//增加分组
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
		if('ugid' == substr($key,0,4)){//会员组的通用方法
			$re = $this->type_ugid($key,$mode,$data);
		}elseif('mctid' == substr($key,0,5)){//会员认证的通用方法
			$re = $this->type_mctid($key,$mode,$data);
		}elseif(!empty($this->cfgs[$key]['type'])){//内置专用类型的方法,如bool,url,field等
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
	protected function top_title($key,$cfg){
		$re = empty($cfg['title']) ? $key : $cfg['title'];
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
	
	//时间方法
	//fmt:时间方法的格式化参数
	protected function type_date($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$arr = array('regdate' => '注册日期','lastvisit' => '上次登录',);
			if(empty($cfg['title']) && isset($arr[$key])) $cfg['title'] = $arr[$key];
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			return empty($data[$key]) ? (isset($cfg['empty']) ? $cfg['empty'] : '-') : date(isset($cfg['fmt']) ? $cfg['fmt'] : 'Y-m-d',$data[$key]);
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
	
	//weixin配置链接
	//mcache-默认菜单缓存ID
	// $oL->m_additem('weixin',array('type'=>'weixin','mcache'=>'new_car_dealers'));
	protected function type_weixin($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = '微信';
		}else{
			$mid = $data['mid'];
			$wecfg = cls_w08Basic::getConfig($mid, 'mid');
			$mcache = $cfg['mcache'];
			if(!empty($wecfg)){
				return "<a href='?entry=weappid&action=config&mid=$mid&isframe=1' target='_blank'><span style='color:#03F'>管理</span></a>";
			}else{
				return "<a href='?entry=weixin&action=appadd&mid=$mid&tab=$mcache' onclick=\"return floatwin('open_weixinm',this)\" target='_blank'>配置</a>";	
			}
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
		$field = empty($this->A['fields']) ? cls_cache::Read('field',$this->A['chid'],$key) : @$this->A['fields'][$key];
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
	
	//显示会员组，排除管理组系
	//title-索引行标题
	protected function type_ugid($key = '',$mode = 0,$data = array()){
		$grouptypes = cls_cache::Read('grouptypes');
		$gtid = max(0,intval(str_replace('ugid','',$key)));
		if(!$gtid || ($gtid== 2) || empty($grouptypes[$gtid])) return $this->del_item($key);
		if(!($ugidsarr = ugidsarr($gtid,$this->A['mchid'],1))) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = $grouptypes[$gtid]['cname'];
			if(!isset($cfg['view'])) $cfg['view'] = $this->A['mchid'] ? '' : 'H';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = empty($ugidsarr[$data["grouptype$gtid"]]) ? '' : $ugidsarr[$data["grouptype$gtid"]];
			$re || $re = isset($cfg['empty']) ? $cfg['empty'] : '-';
			return $re;
		}
	}
	
	//显示会员认证
	//title-索引行标题
	protected function type_mctid($key = '',$mode = 0,$data = array()){
		$mctypes = cls_cache::Read('mctypes');
		if(!($mctid = max(0,intval(str_replace('mctid','',$key)))) || empty($mctypes[$mctid]['available'])) return $this->del_item($key);
		if($this->A['mchid'] && !in_array($this->A['mchid'],explode(',',$mctypes[$mctid]['mchids']))) return $this->del_item($key);//当前模型不需要的认证
		
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = $mctypes[$mctid]['cname'];
			isset($cfg['view']) || $cfg['view'] = 'H';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			return empty($data["mctid$mctid"]) ? '-' : 'Y';
		}
	}
	
	
	//类系的通用方法
	//title-标题，icon是否按图标显示，num多选项时最大显示数量
	//url参数,设置连接,用于购买类系等设置入口
	//winsize-窗口大小参数:如500,300,在url中使用
	//aclass-<a>样式
	//custom 自定义字段 用于自定义字段关联类系的情况
	private function type_ccid_final($key = '',$mode = 0,$data = array(),$custom=''){
		$cotypes = cls_cache::Read('cotypes');
		if(!($coid = max(0,intval(str_replace('ccid','',$key)))) || empty($cotypes[$coid]) || $cotypes[$coid]['self_reg']) return $this->del_item($key);
		
		!empty($custom) && $key = $custom;
		$cfg = &$this->cfgs[$key];
		$cfg['coid'] = $coid;
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = $cotypes[$coid]['cname'];
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$color = empty($cfg['color']) ? '' : $cfg['color'];
			$icon = empty($cfg['icon']) ? 0 : 1;
			$num = empty($cfg['num']) ? 0 : $cfg['num'];
			isset($cotypes[$coid]['asmode']) || $cotypes[$coid]['asmode']='';
			$re = cls_catalog::cnstitle(@$data[$key],$cotypes[$coid]['asmode'],cls_cache::Read('coclasses',$coid),$num,$icon);
			$re || $re = isset($cfg['empty']) ? $cfg['empty'] : '-';
			$addstr = empty($cfg['umode']) ? "onclick=\"return floatwin('open_arc$key',this".(empty($cfg['winsize']) ? '' : ','.$cfg['winsize']).")\"".($color ? " style=\"color:$color\"" : "") : ($cfg['umode'] == 1 ? "target=\"_blank\"" : '').($color ? " style=\"color:$color\"" : "");
			isset($cfg['url']) && $re = "<a ".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".key_replace($cfg['url'],$data)."\" {$addstr}>$re</a>";
			return $re;
		}
	}
	
	
	//普通字段,按$key来提取数据内容
	//empty：为空时显示内容
	protected function type_other($key = '',$mode = 0,$data = array()){
		$field = empty($this->A['fields']) ? cls_cache::Read('mfield',$this->A['mchid'],$key) : @$this->A['fields'][$key];
		//自定义字段关联类系处理
		if( @$field['coid'] && $field['datatype'] == 'cacc'){
			 $coid = max(0,intval($field['coid']));
			 return $this->type_ccid_final("ccid$coid",$mode,$data,$key);
		}
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			return isset($data[$key]) ? $data[$key] : (isset($cfg['empty']) ? $cfg['empty'] : '-');
		}
	}
	
	/*会员ID动态地址
	* @example  $oL->m_additem('mid');
				$oL->m_additem('mid',array('url'=>"{$cms_abs}mspace/index.php?mid={mid}",'title'=>'ID')); //指定链接
				$oL->m_additem('mid',array('url'=>"#",'title'=>'ID')); //不要链接
    *
	*/
	protected function user_mid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
			
		if($mode){//处理列表区索引行
			$this->titles[$key] =  isset($cfg["title"])&&!empty($cfg["title"]) ? $cfg["title"] : ' ID' ;
		}else{
			$re = $data[$key];
			if(@$cfg['url']!='#'){  // 不需要url链接
				$cms_abs = cls_env::mconfig('cms_abs');
				$re = 	"<a target='_blank' title='点击查看动态地址'".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".(isset($cfg['url']) ? key_replace($cfg['url'],$data) : $cms_abs.'mspace/index.php?mid='.$re)."\" >$re</a>";	
			}
			return $re;	
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
			return "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$data[mid]]\" value=\"$data[mid]\">";
		}
	}
	
	//会员名称，默认带上空间url
	//nourl:不需要空间url，1:不需要空间url，array(1,2,3):mchid为1,2,3的不要空间url
	//field:显示的字段名称，默认为mname
	//pic:根据输入的图片字段判断是否存在内容而显示“图”字样
	protected function user_subject($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'L';
			isset($cfg['view']) || $cfg['view'] = 'S';
			if(empty($cfg['title'])) $cfg['title'] = '名称';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			
			$len = empty($cfg['len']) ? 40 : $cfg['len'];
			$re = $data['isfounder'] && $cfg['field'] == 'mname' ? '[创始人]' : '';
			$re .= $data[$cfg['field']];
			$re = htmlspecialchars(cls_string::CutStr($re,$len));
			if(empty($cfg['nourl']) || (is_array($cfg['nourl']) && !in_array($data['mchid'],$cfg['nourl']))){			
				$re = "<a href=\"".cls_Mspace::IndexUrl($data)."\" target=\"_blank\" title=\"{$data[$cfg['field']]}\">".(empty($cfg['pic'])?'':(empty($data[$cfg['pic']])?'':"<font style=\"color:red;\">图</font>"))."$re</a>";
			}
			return $re;
		}
	}
	
	//会员类型
	//如果脚本为指定模型，则自动隐藏
	protected function user_mchid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		if($this->A['mchid']) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '会员类型';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = ($mchannel = cls_cache::Read('mchannel',$data['mchid'])) ? $mchannel['cname'] : '';
			$re || $re = isset($cfg['empty']) ? $cfg['empty'] : '-';
			return $re;
		}
	}
	
	//注册IP
	//如果脚本为指定模型，则自动隐藏
	protected function user_regip($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		#if($this->A['mchid']) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '注册IP';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			return empty($data['regip']) ? '' : $data['regip'];
		}
	}
	
	//会员中心代管
	protected function user_trustee($mode = 0,$data = array()){
		global $cms_abs,$g_apid;
		$curuser = cls_UserMain::CurUser();
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		
		if($curuser->NoBackFunc('trusteeship')) return $this->del_item($key);
		
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'C';
			isset($cfg['view']) || $cfg['view'] = 'S';
			empty($cfg['width']) && $cfg['width'] = '60';
			if(empty($cfg['title'])) $cfg['title'] = '会员中心';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			return cls_Permission::noPmReason($data,@$g_apid) && !$curuser->info['isfounder'] ? '-' : "<a href=\"{$cms_abs}adminm.php?from_mid=$data[mid]\" target=\"_blank\">代管</a>";
		}
	}
	
	//静态空间
	protected function user_static($mode = 0,$data = array()){
		global $mspacepmid;
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'C';
			isset($cfg['view']) || $cfg['view'] = 'S';
			empty($cfg['width']) && $cfg['width'] = '60';
			if(empty($cfg['title'])) $cfg['title'] = '静态';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			$static_state = empty($data['mspacepath']) ? "<a href=\"?entry=extend&extend=memberstatic&mid={$data['mid']}\" onclick=\"return floatwin('open_mem$key',this)\"><b>生成</b></a>" : "<a href=\"?entry=extend&extend=memberstatic&mid={$data['mid']}\" onclick=\"return floatwin('open_mem$key',this)\">更新</a>- <a href=\"?entry=extend&extend=memberstatic&mid={$data['mid']}&bsubmit=1&fmdata[mspacepath]=''\">删除</a>";
			return $mspacepmid && !cls_Permission::noPmReason($data,$mspacepmid) ? $static_state : '无权限';
		}
	}
}
