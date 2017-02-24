<?php
/**
 *  arccolsbase.cls.php 列表中列项目的数据处理和显示基类	 
 *
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */
!defined('M_COM') && exit('No Permisson');
class cls_arccolsbase{
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
	
	
	
	/**
	* 添加列表项目
	*
	* @ex  $oL->m_additem('clicks',array('type' => 'input','title'=>'点击数','width'=>50,'view'=>'H','w' => 3,));
	*
	* @param    string     $key  项目关键字 默认为空key的值一般为数据库某个字段的名称，系统已经内置某些字段的显示和处理方法。key也可以是自己扩展的名称，但要同时扩展这个项目的显示和处理方法
	* @param    array      $cfg  项目配置参数 可选，默认为空 
						type：定义类型之后，会启用function type_{type}()来处理,date,bool,url,select,checkbox,input方法
						title：索引行标题
						width：列宽度
						side：左右位置(L/R/C)，默认为C
						view：列是否隐藏，空(显示但可隐藏)/S(不能隐藏)/H(默认隐藏)
						url：链接url，允许任何当前行资料使用占位符{xxxx}调用
						winsize-窗口大小参数:如500,300,在url中使用
						mtitle：正文标题，或url,other等方法的显示标题;如:{dj}元/M2,{zj}万元
						umode：url打开方式,0(默认浮动窗)/1(新窗口)/2(本窗口)
						coid：类系id
						len：正文内容截取长度，如subject
						icon：类系是否按图标显示
						num：多选项的显示数量
						empty：为空时的显示内容
						fmt：时间方法的格式化参数
						aclass：<a>样式,url,subject,ccid中的<a>使用
						onclick：type_image中使用,点击图片,对checkbox进行选中操作,默认为1
						showEnd: =0/1,针对type_date(时间)处理 按到期时间方式特殊显示(分颜色或显示<永久>), 默认enddate按此方式显示
	
	//完全定制方法：user_$key，此为优先使用的方法
	//系统内置方法：type_$key，在以下情况下调用：定义了type、未定制的类系、其它未指定方法的显示项
	*/
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
		if('ccid' == substr($key,0,4)){//通用的类系方法
			if(strlen($key)<9) $re = $this->type_ccid($key,$mode,$data);
			//类系过期时间处理
			else $re = $this->type_cciddate($key,$mode,$data); 
		}elseif(!empty($this->cfgs[$key]['type'])){//内置专用类型的方法
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
	//showEnd:按到期时间方式特殊显示(分颜色或显示<永久>), 默认enddate按此方式显示
	protected function type_date($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$arr = array('createdate' => '添加日期','refreshdate' => '刷新日期','updatedate' => '更新日期','enddate' => '失效日期',);
			if(empty($cfg['title']) && isset($arr[$key])) $cfg['title'] = $arr[$key];
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			// enddate默认按isenddate方式显示，显示<永久>, 或分颜色显示过期如否
			$showEnd = isset($cfg['showEnd']) ? $cfg['showEnd'] : ($key=='enddate' ? 1 : 0);
			global $timestamp;
			$null = isset($cfg['empty']) ? $cfg['empty'] : ($showEnd ? '&lt;永久&gt;' : '-');
			$fmt = isset($cfg['fmt']) ? $cfg['fmt'] : 'Y-m-d';
			$sval = date($fmt,$data[$key]);
			if($showEnd){
				$cval = date($fmt,$timestamp);
				if($cval>$sval){ $sval = "<span style='color:#FF0000'>$sval</span>"; } //已经过期:红色
				elseif($cval==$sval){ $sval = "<span style='color:#0000FF'>$sval</span>"; } //当天过期:蓝色
			}
			return empty($data[$key]) ? $null : $sval;
		}
	}
	
	//类系到期时间  调用 $oL->m_additem("ccid{$k}date",array('view'=>'H','title'=>'置顶到期','empty'=>'永久'));
	//fmt:时间方法的格式化参数
	protected function type_cciddate($key = '',$mode = 0,$data = array()){
		$cotypes = cls_cache::Read('cotypes');
		if(!($coid = max(0,intval(str_replace(array('ccid','date'),'',$key)))) || empty($cotypes[$coid]) || !in_array($coid,$this->A['coids']) || $cotypes[$coid]['self_reg']) return $this->del_item($key);
		#if($cotypes[$coid]['emode']<=0 ) return '-';
		
		$cfg = &$this->cfgs[$key];
		$cfg['coid'] = $coid;	
		if($mode){//处理列表区索引行
			$cfg['title'] = empty($cfg['title']) ? '类系到期' : $cfg['title'];
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			return empty($data[$key]) ? ((isset($cfg['empty']) && !empty($data['ccid'.$coid])) ? $cfg['empty'] : '-') : date(isset($cfg['fmt']) ? $cfg['fmt'] : 'Y-m-d',$data[$key]);
		}
	}
	
	//布尔值的方法：显示Y/-
	//cfg['mtitle'] = '审核' 或 'OK'
	protected function type_bool($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$mtitle = isset($cfg['mtitle']) ? key_replace($cfg['mtitle'],$data) : 'Y';
			return empty($data[$key]) ? (isset($cfg['empty']) ? $cfg['empty'] : '-') : $mtitle;
		}
	}
	
	//输入框text：在列表区作项目设置，w指定输入框的宽度
	protected function type_input($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//列表区内的保存
			$w = empty($cfg['w']) ? 4 : max(1,intval($cfg['w']));
			return $this->input_text("{$this->A['mfm']}[{$data['aid']}][$key]",isset($data[$key]) ? $data[$key] : '',$w);
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
			return $this->input_checkbox("{$this->A['mfm']}[{$data['aid']}][$key]",empty($data[$key]) ? 0 : $data[$key]);
		}
	}
	
	//weixin配置链接
	//mcache-默认菜单缓存ID
	// $oL->m_additem('weixin',array('type'=>'weixin','mcache'=>'loupan'));
	protected function type_weixin($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = '微信';
		}else{
			$aid = $data['aid'];
			$wecfg = cls_w08Basic::getConfig($aid, 'aid');
			$mcache = $cfg['mcache'];
			if(!empty($wecfg)){
				return "<a href='?entry=weappid&action=config&aid=$aid&isframe=1' target='_blank'><span style='color:#03F'>管理</span></a>";
			}else{
				return "<a href='?entry=weixin&action=appadd&aid=$aid&tab=$mcache' onclick=\"return floatwin('open_weixina',this)\" target='_blank'>配置</a>";	
			}
		}
	}
	
	//URL方法
	//winsize-窗口大小参数:如500,300,在url中使用
	//aclass-<a>样式
	//empty - $data[$key]为空时的替换值
	protected function type_url($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$addstr = empty($cfg['umode']) ? "onclick=\"return floatwin('open_arc$key',this".(empty($cfg['winsize']) ? '' : ','.$cfg['winsize']).")\"" : ($cfg['umode'] == 1 ? "target=\"_blank\"" : '');
			if(isset($cfg['empty']) && empty($data[$key])) $data[$key] = $cfg['empty'];
			return "<a ".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".key_replace($cfg['url'],$data)."\" {$addstr}>".(empty($cfg['mtitle']) ? (isset($data[$key]) ? $data[$key] : $key) : key_replace($cfg['mtitle'],$data))."</a>";
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
	
	//类系的通用方法
	//title-标题，icon是否按图标显示，num多选项时最大显示数量
	//url参数,设置连接,用于购买类系等设置入口
	//winsize-窗口大小参数:如500,300,在url中使用
	//aclass-<a>样式
	protected function type_ccid($key = '',$mode = 0,$data = array()){
		$cotypes = cls_cache::Read('cotypes');
		if(!($coid = max(0,intval(str_replace('ccid','',$key)))) || empty($cotypes[$coid]) || !in_array($coid,$this->A['coids']) || $cotypes[$coid]['self_reg']) return $this->del_item($key);
		
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
	
	//图片显示
	//cfgs[onclick]不为空时，调用js
	public function type_image($key = '',$mode = 0,$data = array()){
		global $cms_abs;
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$thumb = view_checkurl($data[$key]);
			$cfg['onclick'] = isset($cfg['onclick']) ? $cfg['onclick'] : 1;
			$_onclick = empty($cfg['onclick'])?'':"onclick=\"_img_affect_checkbox($data[aid]);\"";
			return "<img src=\"".$thumb."\" style=\"float:left;display:block; \" width=\"".$cfg['width']."\"  height=\"".$cfg['height']."\" ".$_onclick.">";
		}	
	}

	/**
	 * 数字字段：将科学计数法处理为普通数字显示，decimals(小数位数)，dec_point(小数分割符)，thousands_sep(千数分隔符)
	 * $oL->m_additem("zj",array('type'=>'number','mtitle'=>'{zj}万元'));//170000000万元
	 * $oL->m_additem("zj",array('type'=>'number','thousands_sep'=>',','mtitle'=>'{zj}万元'));//170,000,000
	 */	
	public function type_number($key='',$mode=0,$data=array()){
		$cfg = &$this->cfgs[$key];		
		if($mode){//处理列表区索引行
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{
			$decimals = isset($cfg['decimals']) ? intval($cfg['decimals']) : 0;
			$dec_point = isset($cfg['dec_point']) ? $cfg['dec_point'] : '.';
			$thousands_sep = isset($cfg['thousands_sep']) ? $cfg['thousands_sep'] : '';			
			$data[$key] = number_format($data[$key],$decimals,$dec_point,$thousands_sep);			
			return isset($cfg['mtitle']) ? key_replace($this->cfgs[$key]['mtitle'],$data) : $data[$key];
		}
	}
	
	//普通字段,按$key来提取数据内容
	//empty：为空时显示内容
	//mtitle: 显示模版,如:{dj}元/M2,{zj}万元
	protected function type_other($key = '',$mode = 0,$data = array()){
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$empty = (isset($cfg['empty']) ? $cfg['empty'] : '-');
			$data[$key] = empty($data[$key]) ? $empty : (isset($cfg['mtitle']) ? key_replace($cfg['mtitle'],$data): $data[$key]);
			$len = empty($cfg['len']) ? '' : $cfg['len'];
			return $len ? htmlspecialchars(cls_string::CutStr($data[$key],$len)) : $data[$key];
		}
	}
	
	/*文档ID动态地址
	* @example  $oL->m_additem('aid'); //默认,文档动态链接
				$oL->m_additem('aid',array('url'=>"{$cms_abs}archive.php?aid={aid}",'title'=>'ID','mtitle'=>'[{aid}]'));
				$oL->m_additem('mid',array('url'=>"#",'title'=>'ID')); //不要链接
				$oL->m_additem('mid',array('mc'=>"1",'title'=>'ID')); //会员空间动态链接
    *
	*/
	protected function user_aid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			$this->titles[$key] =  isset($cfg["title"])&&!empty($cfg["title"]) ? $cfg["title"] : 'ID' ;
		}else{
			$cms_abs = cls_env::mconfig('cms_abs');
			$re = $data[$key];
			if(!empty($cfg['mc'])){ //会员空间动态连接    
				$re = "<a target='_blank' title='会员空间动态地址'".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".(isset($cfg['url']) ? key_replace($cfg['url'],$data) : $cms_abs.'mspace/archive.php?mid='.$data['mid'].'&aid='.$re)."\" >$re</a>";
			}elseif(@$cfg['url']!='#'){  // 不需要url链接
				$re = "<a target='_blank' title='点击查看动态地址'".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_url_pub'")." href=\"".(isset($cfg['url']) ? key_replace($cfg['url'],$data) : $cms_abs.'archive.php?aid='.$re)."\" >$re</a>";
			}
			return $re;			
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
			return "<input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$data[aid]]\" value=\"$data[aid]\">";
		}
	}
	
	//文档标题
	//addno:指定使用哪个附加页的url
	//aclass-<a>样式
	//nothumb:默认为空,显示标记; 设置为1时,不显示红色缩略图标记(本身就是图片列表或没有启用thumb字段时使用)
	protected function user_subject($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){//处理列表区索引行
			empty($cfg['side']) && $cfg['side'] = 'L';
			!isset($cfg['view']) && $cfg['view'] = 'S';
			if(empty($cfg['title'])) $cfg['title'] = '标题';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			$re = (empty($cfg['nothumb']) && !empty($data['thumb']) ? '<font style="color:red">图&nbsp;</font>' : '');
			$addno = empty($cfg['addno']) ? 0 : max(0,intval($cfg['addno']));
			$url = '';
			if(empty($cfg['url'])){
				if(!empty($cfg['mc'])){  //会员空间    
					cls_ArcMain::Url($data,-1);
					$url = $data['marcurl'];
				}
				else $url = cls_ArcMain::Url($data,$addno);
			}elseif($cfg['url'] == '#'){  // 不需要url链接
				if(!empty($data['color'])) $re .= "<span style=\"color:{$data['color']}\">";
				$len = empty($cfg['len']) ? 40 : $cfg['len'];
				if(!empty($data['thumb'])) $len -= 4;
				$re .= htmlspecialchars(cls_string::CutStr($data['subject'],$len))."</span>";
				return $re;
			}else $url = key_replace($cfg['url'],$data); //可以自定义url格式
			$re .= "<a ".(isset($cfg['aclass']) ? "class='$cfg[aclass]'" : "class='scol_subject'")." href=\"$url\" target=\"_blank\"";
			
			if(!empty($data['color'])) $re .= " style=\"color:{$data['color']}\"";
			
			$len = empty($cfg['len']) ? 40 : $cfg['len'];
			if(!empty($data['thumb'])) $len -= 4;
			$re .= " title=\"".htmlspecialchars($data['subject'])."\">".htmlspecialchars(cls_string::CutStr($data['subject'],$len))."</a>";
			return $re;
		}
	}
	
	/**
	 *栏目处理
	 */
	protected function user_caid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if($mode){
			if(empty($cfg['title'])) $cfg['title'] = '栏目';
			$this->titles[$key] =  $this->top_title($key,$cfg);
		}else{
			$re = ($catalog = cls_cache::Read('catalog',$data['caid'])) ? $catalog['title'] : '';
			$re || $re = isset($cfg['empty']) ? $cfg['empty'] : '-';
			return $re;
		}
	}
	
	//是否有效
	//cfg['mtitle'] = '上架' 或 '有效'
	protected function user_valid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '有效';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			global $timestamp;
			$mtitle = isset($cfg['mtitle']) ? key_replace($cfg['mtitle'],$data) : 'Y';
			return empty($data['enddate']) || $data['enddate'] > $timestamp ? $mtitle : (isset($cfg['empty']) ? $cfg['empty'] : '-');
		}
	}
	
	//模型列表 $oL->m_additem('chid',array('title'=>'模型',));
	protected function user_chid($mode = 0,$data = array()){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];	
		if($mode){//处理列表区索引行
			if(empty($cfg["title"])) $cfg['title'] = '模型';
			$this->titles[$key] = $this->top_title($key,$cfg);
		}else{//处理列表区内容
			isset($channels) || $channels = array();
			$channels = cls_channel::Config();
			return empty($data['chid']) ? '-' : @$channels[$data['chid']]['cname'];
		}
	}
}
