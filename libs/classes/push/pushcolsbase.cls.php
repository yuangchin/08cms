<?php
/*
** 列表中列项目的数据处理类
** 含列的索引单元及内容单元
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushcolsbase{
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
		if(!empty($this->cfgs[$key]['type'])){//内置专用类型的方法
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
			$arr = array('createdate' => '添加日期','refreshdate' => '刷新日期','updatedate' => '更新日期','enddate' => '失效日期',);
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
	
	//输入框text：在列表区作项目设置，w指定输入框的宽度
	protected function type_input($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//列表区内的保存
			$w = empty($cfg['w']) ? 4 : max(1,intval($cfg['w']));
			return $this->input_text("{$this->A['mfm']}[{$data['pushid']}][$key]",isset($data[$key]) ? $data[$key] : '',$w);
		}
	}
	
	//checkbox：在列表区作项目设置，w指定输入框的宽度
	//atitle：全选的标题
	protected function type_checkbox($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if(empty($cfg['width'])) $cfg['width'] = 40;
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])){
				$cfg['title'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall$key\" onclick=\"checkall(this.form,'{$this->A['mfm']}','chkall$key')\">";
				if(!empty($cfg['atitle'])) $cfg['title'] .= $cfg['atitle'];
			}
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			return $this->input_checkbox("{$this->A['mfm']}[{$data['pushid']}][$key]",empty($data[$key]) ? 0 : $data[$key]);
		}
	}
	
	//URL方法
	//winsize-窗口大小参数:如500,300,在url中使用
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
		$field = empty($this->A['fields']) ? cls_PushArea::Field($this->A['paid'],$key) : @$this->A['fields'][$key];
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
	
	//加载类型
	//empty：为空时显示内容
	protected function user_loadtype($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = empty($cfg['title']) ? '来源ID' : $cfg['title'];
		}else{
			$reval = $data['fromid']; $loadtype = @$data[$key];
			$tarr = array('11'=>array('手动添加','999999'), '21'=>array('自动推送','0033FF')); //'0'=>'列表加载',
			$title = isset($tarr[$loadtype]) ? $tarr[$loadtype][0] : '手动推送';
			$css = isset($tarr[$loadtype]) ? $tarr[$loadtype][1] : '333333';
			if(empty($reval) && $loadtype=='11') $reval = '手动添加'; //有不有这种情况:手动添加的,fromid不为空?
			$re = "<span title='$title' style='color:#$css'>$reval</span>";
			return $re;
		}
	}
	
	//普通字段,按$key来提取数据内容
	//empty：为空时显示内容
	protected function type_other($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			return isset($data[$key])&&!empty($data[$key]) ? $data[$key] : (isset($cfg['empty']) ? $cfg['empty'] : '-');
		}
	}
	
	//选择id
	protected function user_selectid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(empty($cfg['width'])) $cfg['width'] = 30;
		if(!isset($cfg['view'])) $cfg['view'] = 'S';
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = "<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form,'selectid','chkall')\">";
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			return "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[{$data['pushid']}]\" value=\"{$data['pushid']}\">";
		}
	}
	
	//标题
	protected function user_subject($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'L';
			!isset($cfg['view']) && $cfg['view'] = 'S';
			if(empty($cfg['title'])) $cfg['title'] = '标题';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			$re = (!empty($data['thumb']) ? '<font style="color:red">图&nbsp;</font>' : '');
			$len = empty($cfg['len']) ? 40 : $cfg['len'];
			if(!empty($data['thumb'])) $len -= 4;
			$re .= htmlspecialchars(cls_string::CutStr($data['subject'],$len));
			if(!empty($data['color'])) $re = "<font style=\"color:{$data['color']}\">$re</font>";
			if(!empty($data['url'])) $re = "<a href=\"{$data['url']}\" target=\"_blank\" title=\"".htmlspecialchars($data['subject'])."\">$re</a>";
			return $re;
		}
	}
	
	//详情
	protected function user_detail($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '编辑';
			if(empty($cfg["width"])) $cfg['width'] = 40;
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			return "<a href=\"?entry=extend&extend=push&paid={$this->A['paid']}&pushid={$data['pushid']}\" onclick=\"return floatwin('open_push$key',this)\">详情</a>";
		}
	}
	//共享
	protected function user_share($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		if(!($area = cls_PushArea::Config($this->A['paid'])) || empty($area['copyspace'])) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '共享';
			if(empty($cfg["width"])) $cfg['width'] = 40;
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$mtitle = '(0)';
			if($num = cls_pusher::copynum($data,$this->A['paid'])) $mtitle = "(<b>$num</b>)";
			return "<a href=\"?entry=extend&extend=push_share&paid={$this->A['paid']}&pushid={$data['pushid']}\" onclick=\"return floatwin('open_push$key',this)\">$mtitle</a>";
		}
	}
	
	//手动排序：在列表区作项目设置，w指定输入框的宽度
	protected function user_vieworder($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '排序';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//列表区内的保存
			$w = empty($cfg['w']) ? 3 : max(1,intval($cfg['w']));
			$value = in_array($data[$key],array(0,500)) ? '' : $data[$key];
			return $this->input_text("{$this->A['mfm']}[{$data['pushid']}][$key]",$value,$w);
		}
	}
	//固位排序：在列表区作项目设置，w指定输入框的宽度
	protected function user_fixedorder($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '固位';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//列表区内的保存
			$w = empty($cfg['w']) ? 3 : max(1,intval($cfg['w']));
			$value = in_array($data[$key],array(0,500)) ? '' : $data[$key];
			return $this->input_text("{$this->A['mfm']}[{$data['pushid']}][$key]",$value,$w);
		}
	}
		
	//是否有效
	protected function user_valid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '有效';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			global $timestamp;
			return ($data['checked'] && ($data['startdate'] < $timestamp) && (empty($data['enddate']) || $data['enddate'] > $timestamp)) ? 'Y' : (isset($cfg['empty']) ? $cfg['empty'] : '-');
		}
	}
	
}
