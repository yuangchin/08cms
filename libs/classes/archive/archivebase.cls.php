<?php
/**
 *  archivebase.cls.php 单个文档添加或编辑的操作基类 
 *		sv方法中设置return_error表示出错时返回error，而不是用message跳出
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */

!defined('M_COM') && exit('No Permisson');
class cls_archivebase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如chid，caid(栏目)
	public $isadd = 0;//添加模式
	public $aid = 0;//文档id
	public $chid = 0;//文档模型id
	public $caid = 0;//文档栏目id
	public $fmdata = 'fmdata';//form中的数组名称//允许自行设置
	public $predata = array();//预定资料数组
	public $channel = array();//文档模型
	public $fields = array();//模型字段
	public $stid = 0;//主表分表id
	public $coids = array();//模型所在分表包含的类系字段
	public $coids_showed = array();//已经显示了的类系字段
	public $fields_did = array();//暂存已处理过的字段
	public $arc = NULL;//文档类
	
	
	/**
	 * 获取单个文档的模型
	 */
	public function get_chid(){
		return $this->chid;
	}
	
	/**
	 * 构造函数初始化设置
	 * $cfg['chid'] : 指定文档模型, 未指定从GET等global中找
	 * $cfg['caid'] : 指定文档栏目, 未指定从GET/POST等global中找
	 */
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		//$isadd不要指定或通过url传递，通过是否指定aid来识别，不传的话为添加模式。0为详情编辑，1为文档添加
		$this->isadd = empty($GLOBALS['aid']) ? 1 : 0;
		$this->A = $cfg;
		if($this->isadd){ //添加时需要
			if(isset($cfg['chid'])) $this->chid = $cfg['chid'];
			if(isset($cfg['caid'])) $this->caid = $cfg['caid'];
		}
    }
	
	/**
	* 做多个文档批量处理时选择性复位对象的某些变量，继续下一个处理
	*
	* @param    array     $vars  $vars需要初始化的变量，留空则默认为array('aid','predata','fields_did',)
	* 
	*/
	function next_init($vars = array()){
		$vars || $vars = array('aid','predata','fields_did',);
		foreach($vars as $var){
			if($var == 'fmdata'){
				$this->$var = 'fmdata';
			}if($var == 'arc'){
				$this->$var = NULL;
			}elseif(in_array($var,array('aid','chid','stid',))){
				$this->$var = 0;
			}else $this->$var = array();
		}
	}
	
	/**
	* 弹窗对话框
	*
	* @param    string     $str  提示字符串 默认为空
	* @param    string     $url  跳转url ，默认为空
	* @param    int        $return_error  跳转url ，默认为0 $return_error为1时，不跳出，返回错误信息
	*							
	*/
	function message($str = '',$url = '',$return_error = 0){
		if($return_error){
			return $str;
		}else{
			call_user_func('cls_message::show',$str,$url);
		}
	}
	function setvar($key,$var){
		$this->$key = $var;	
	}
	
	function top_head(){
		$curuser = cls_UserMain::CurUser();
		if($this->mc){
			if(!defined('M_COM')) exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			if($re = $curuser->NoBackFunc('normal')) $this->message($re);
		}
		echo "<title>内容".($this->isadd ? '添加' : '详情')."</title>";
	}
	
	/**
	 * 读取现有可用资料，如模型、字段、及文档
	 */
	function read_data(){
		$this->find_chid();
		$this->read_archive();
		$this->read_cfg();
		return;
	}
	
	/**
	 * 获取文档模型
	 */	
	function find_chid(){
		if(empty($this->chid)){
			$this->chid = empty($GLOBALS['chid']) ? 0 : max(0,intval($GLOBALS['chid']));
		}
		if(!$this->chid && !empty($GLOBALS['caid'])){
			if(($catalog = cls_cache::Read('catalog',$GLOBALS['caid'])) && $chids = array_filter(explode(',',$catalog['chids']))) $this->chid = current($chids);
			// 前一句, array_filter后键值不变, 可能第一个为空则从1开始, 所以用current取第一个； 一般指定了模型或重url中带过来了,所以这里一般用不到
		}
		if($this->chid && !($this->channel = cls_channel::Config($this->chid))){
			$this->chid = 0;
			$this->message('请指定文档类型。');
		}
		return;
	}
	
	/**
	 * 获取文档的数据赋值给$this->predata
	 */	
	function read_archive(){
		if($this->isadd) return;
		if(!($aid = max(0,intval($GLOBALS['aid'])))) $this->message('请指定文档。');
		if(empty($this->arc)){
			$this->arc = new cls_arcedit;
			if(!($this->aid = $this->arc->set_aid($aid,array('chid'=>$this->chid,'ch'=>1,'au'=>1,)))) $this->message('请指定文档。');
			$this->chid || $this->chid = $this->arc->archive['chid'];
			$this->predata = &$this->arc->archive;
			if($re = $this->NoBackPm($this->predata['caid'])) $this->message($re);
	}
		return;
	}
	
	/**
	* 管理角色的栏目管理权限，仅在管理后台中使用
	*
	* @param    int     $caid  栏目ID 默认为0
	*							
	*/
	function NoBackPm($caid = 0){
		$curuser = cls_UserMain::CurUser();
		if($this->mc) return '';
		if(!$caid) return '请指定栏目';
		return $curuser->NoBackPmByTypeid($caid,'caid');
	}	
	
	/**
	* 会员中心只能编辑本人发布的文档
	*							
	*/
	function allow_self(){
		$curuser = cls_UserMain::CurUser();
		if($this->isadd) return;
		if($this->predata['mid'] != $curuser->info['mid']) $this->message('对不起，请选择您自已的文档。');
	}
	
	/**
	 * 读取文档模型的字段设置
	 */
	function read_cfg(){
		$splitbls = cls_cache::Read('splitbls');
		if(!($this->channel = cls_channel::Config($this->chid))) $this->message('请指定文档类型。');
		$this->fields = cls_cache::Read('fields',$this->chid);
		$this->stid = $this->channel['stid'];
		$this->coids = empty($splitbls[$this->stid]['coids']) ? array() : $splitbls[$this->stid]['coids'];
		return;
	}
	
	
	/**
	* 会员中心-组合提示信息
	*
	* @param    array     $cfg  配置参数 默认为空
	*						cfg[limit]=array(800,32),总共可发布数800,已发布数量32
	*						cfg[daymax]=array(200,5),当天可发布数200,已发布数量5
	* 						cfg[voild]=array(200,12,90),总共可上架数200,已上架数量12,上架期限90天
	* @param    string    $msg  提示信息字符串 默认为空
	*							
	*/
	function getmtips($cfg=array(),$msg=''){
		if($msg) $msg = "<br/>$msg"; // && !strstr($msg,'<li>')
		if(!empty($cfg['check'])){ 
			$curuser = cls_UserMain::CurUser();
			$cancheck = $this->channel['autocheck'];
			if(intval($cancheck)<0){
				$cancheck = $curuser->noPm(-$this->channel['autocheck']); 
				if($cancheck) $msg .= "<br/>您发布的信息没有 <span class='tipm_bred'>审核</span> 权限，(原因:{$cancheck})。";
				else $msg .= "<br/>您所在的会员组 发布的信息拥有 <span class='tipm_bred'>直接审核</span> 权限。";
			}else{
				if($cancheck) $msg .= "<br/>您所在的会员组 发布的信息拥有 <span class='tipm_bred'>直接审核</span> 权限。";
				else $msg .= "<br/>您发布的信息没有 <span class='tipm_bred'>审核</span> 权限，(原因:系统禁止审核)。";
			}
		}
		$a = array('limit'=>'发布','daymax'=>'发布','valid'=>'上架',);
		foreach($a as $key=>$title){
			if(isset($cfg[$key])){  
				$total = $cfg[$key][0];
				$pub = $cfg[$key][1];
				$msg .= "<br/>你所在的会员组 ".($key=='daymax' ? '24小时内' : '')."总共可{$title} <span class='tipm_bred'>$total</span> 条；已{$title} <span class='tipm_bred'>$pub</span> 条；还可{$title} <span class='tipm_bred'>".($total - $pub)."</span> 条。";
				if($key=='valid'){ 
					if(($total-$pub)>0){ 
						$msg .= empty($cfg[$key][2]) ? "" : " 上架期限为：<span class='tipm_bred'>".$cfg[$key][2]."</span>天。";
					}else{
						$msg .= " 上架超额信息会放在仓库,<span class='tipm_bred'>不会显示</span>在前台。";
					}
				}			}
		}
		$msg && $msg = substr($msg,5);
		return $msg;
	}
	
	/**
	* 栏目或分类的预处理
	*							
	*/
	function fm_pre_cns(){
		if(!$this->isadd) return;//仅添加时需要
		$cotypes = cls_cache::Read('cotypes');
		if(empty($this->caid)){
			$this->predata['caid'] = empty($GLOBALS['caid']) ? 0 : max(0,intval($GLOBALS['caid']));
		}else{
			$this->predata['caid'] = $this->caid;	
		}
		foreach($cotypes as $k => $v){
			if(!$v['self_reg']  && in_array($k,$this->coids)){
				$this->predata['ccid'.$k] = empty($GLOBALS['ccid'.$k]) ? '' : trim($GLOBALS['ccid'.$k]);
			}
		}
		$this->predata = array_filter($this->predata);
		return;
	}
	
	/**
	* 分析当前会员的发布权限及管理权限，在fm_pre_cns之后执行
	*							
	*/
	function fm_allow(){
		$curuser = cls_UserMain::CurUser();
		if(!$this->mc && !empty($this->predata['caid']) && $re = $this->NoBackPm($this->predata['caid'])) $this->message($re);
		if($this->isadd && ($re = $curuser->arcadd_nopm($this->chid,$this->predata))) $this->message($re);
	}
	
	
	/**
	* 指定了所属合辑的处理
	*
	* @param    string    $pidkey  合辑字段名 默认为pid
	* @param    int       $mc      标识链接是指向前台还是会员空间	
	*						 1  指向会员空间
	*	   					 0  指向前台（默认）
	* exurl 用于扩展,一般在etools中实现，如：etools/ajax.php?action=ajax_arc_mylist
	*/
	function fm_album($pidkey = 'pid',$mc=0,$exurl=''){
		if(!$pidkey) return;
		$p_album = $this->fm_find_album($pidkey);
		$this->fm_view_album($pidkey,$p_album,$mc,$exurl);
	}
	
	/**
	* 分析是否指定了所属合辑
	*
	* @param    string    $pidkey  合辑字段名 默认为pid
	* 如果 pid=-1，则选所属合辑
	*/
	function fm_find_album($pidkey = 'pid'){
		global $db,$tblprefix; //$pid=-1,选所属合辑
		$pid = empty($GLOBALS[$pidkey]) ? @$this->predata[$pidkey] : max(-1,intval($GLOBALS[$pidkey]));
		if($pid==-1) return $pid;
		if(!$pid || !($ntbl = atbl($pid,2)) || !$p_album = $db->fetch_one("SELECT * FROM {$tblprefix}$ntbl WHERE aid='$pid'")) $p_album = array();
		return $p_album;
	}
	
	/**
	* 显示合辑信息
	*
	* @param    string    $pidkey  合辑字段名 默认为pid
	* 如果 pid=-1，则选所属合辑
	* exurl 用于扩展,一般在etools中实现，如：etools/ajax.php?action=ajax_arc_mylist
	*/
	function fm_view_album($pidvar = 'pid',$p_album = array(),$mc=0,$exurl=''){
		if($p_album==-1){ //选所属合辑
			$pchid = max(0,intval(@$GLOBALS['pchid']));
			if(empty($pchid)) return;
			$p_channel = cls_channel::Config($pchid);
			trhidden("{$this->fmdata}[$pidvar]",'');
			trbasic('<font color="red"> * </font>所属 - '.$p_channel['cname'],$this->fmdata.'[pid_label]','','text',array('w'=>60,'validate'=>'rule="text" must="1" mode="" rev="所属'.$p_channel['cname'].'"','guide'=>'可以输入标题进行搜索'));	
			?> 
			<script type="text/javascript">
			var fmdata = '<?php echo $this->fmdata; ?>';
			function createobj(element,type,value,id){
				var e = document.createElement(element);
				e.type = type;
				e.value = value;
				e.id = id;
				return e;
			}
			function set_select(obj,value,dochange){
				if(obj==null) return;
				for(var j=0;j<obj.options.length;j++){
					if(obj.options[j].value == value){
						obj.options[j].selected = true;	
						if(dochange && obj.onchange)obj.onchange();
					}	
				}
			}
			function closediv(){
				//alert('['+pid.value+']');
				divin.nextSibling.style.display = 'none';
				divin.style.display="none";
				if(pid.value.length==0 || pid.value=='0'){ 
					plable.value = '';
					//plable.onfocus();
					plable.onblur();
				} // ?? 会不会，此项为空也可提交？
			}
			var plable = $id(fmdata+'[pid_label]');
			plable.setAttribute('autocomplete','off');
			var divout = document.createElement('DIV');
			var pid = document.getElementsByName('fmdata[<?php echo $pidvar; ?>]')[0];
			with(divout.style){position = 	'relative';left = 0+'px';top = 0+'px';zIndex = 100;}
			var showdiv = "	<div style=\"border: 1px solid rgb(102, 102, 102); position: absolute; z-index: 1000; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\" id=\"SuggestionDiv\"></div><iframe frameborder=\"0\" style=\"border: 0px solid rgb(102, 102, 102); position: absolute; z-index: 100; overflow-y: scroll; height: 300px; width: 500px; background-color: rgb(255, 255, 255);display:none;\"></iframe>";
			divout.innerHTML = showdiv;
			plable.parentNode.insertBefore(divout,plable.nextSibling);
			var divin = $id('SuggestionDiv');
			var aj=Ajax("HTML","loading");
			plable.onkeyup = function(){
				var keywords = plable.value;
				//$exurl用于扩展
				var urlbase = '<?php echo empty($exurl) ? 'ajax=arc_list' : "$exurl"; ?>';
				var urlpara = '&chid=<?php echo $pchid; ?>&keywords='+encodeURIComponent(keywords);
				var urlfull = CMS_ABS + uri2MVC(urlbase+urlpara);
				//console.log(urlfull); // &datatype=js|json 
				aj.post(urlfull,'',function(re){
					eval("var s = "+re+";"); 
					divin.style.display = '';
					divin.nextSibling.style.display = '';
					var str="<table width=\"480px\" cellspacing=\"0\" cellpadding=\"4\" border=\"0\" bgcolor=\"#ffffff\" class=\"search_select\" id=\"Suggestion\" style=\"top: -1px;\";><tbody><tr>";
					str += "<td height=\"16\" align=\"center\" style=\"color: rgb(153, 153, 153); padding-left: 3px; background-repeat: repeat-x; background-position: center center;\" >请点击选择（没有资料请 请点[关闭]或重新输入关键词）</td>";
					str += "<td align='right'><a style=\"cursor:pointer;text-decoration:none; color:red;\" onclick=\"closediv()\">关闭</a></td></tr>"
					for(i=0;i<s.length;i++){
						str+="<tr onclick=\"sendaid("+i+")\" style=\"cursor:pointer; \"><td style=\"color:#09C;padding: 8px;\" >"+s[i].subject+"</td><td style=\"width:100px;padding: 8px;\" align='right'>时间："+s[i].create+"</td></tr>";	
					}
					if(s.length == 0){
						str += "<tr style=\"cursor:pointer\"><td index=\"1\" style=\"padding: 5px; color: rgb(51, 51, 51);\" ><span style=\"color: rgb(0, 101, 181);width:320px; display:block; float:left;\">无相关信息，请重新输入关键词！</span></td></tr>";	
					}
					str+="</tbody></table>";
					divin.innerHTML = str;
					
				function sendaid(i){
					pid.value = s[i].aid;
					plable.value = s[i].subject;
					divin.style.display="none";
					divin.nextSibling.style.display = 'none';
					plable.onfocus(); //没有这句,如果为空状态下选一个项目,选取后认证提示不会消失
				}
				window.sendaid=sendaid;	
				});
			}
			</script>
			<?php
			
		}elseif($p_album){
			$p_channel = cls_channel::Config($p_album['chid']);
			trhidden("{$this->fmdata}[$pidvar]",$p_album['aid']);
			$url = $mc ? cls_Mspace::IndexUrl($p_album) : cls_ArcMain::Url($p_album);
			trbasic('所属 - '.$p_channel['cname'],'',"<a href=\"".$url."\" target=\"_blank\">".mhtmlspecialchars($p_album['subject'])."</a>",'');
		}
	}
	
	// cfg['hidden'] = 1 : 隐藏[高级设置]
	// $cfg['hidstr'] : 高级设置的提示信息
	function fm_header($title = '',$url = '',$cfg = array()){
		if(!empty($cfg['hidden'])){ 
			global $setMoreFlag;
			$cfg['hidstr'] = empty($cfg['hidstr']) ? "高级设置" : $cfg['hidstr'];
			$setMoreFlag = str_replace('.','',microtime(1));
			$title = "<span id='setMore_$setMoreFlag' style='display:inline-block;float:right;cursor:pointer' onclick='setMoerInfo(\"$setMoreFlag\",".$this->mc.")'> $cfg[hidstr] </span>$title";
		}
		$title || $title = $this->channel['cname'].'&nbsp; -&nbsp; 详情';
		if($url){
			if($this->isadd){
				if(!in_str('chid=',$url)) $url .= "&chid={$this->chid}"; 
			}else{ //str_replace()避免与caid=冲突
				if(!in_str('aid=',str_replace('caid=','',$url))) $url .= "&aid={$this->aid}"; 
			}
			tabheader($title,'archivedetial',$url,2,1,1);
			echo "<input type='hidden' name='fmsend_reload_flag' value='".TIMESTAMP.'_'.cls_string::Random(6, 8)."' />";
		}else{
			tabheader($title);
		}
	}
	function fm_footer($button = '',$bvalue = '',$addstr=''){
		tabfooter($button,$button ? ($bvalue ? $bvalue : ($this->isadd ? '添加' : '提交')) : '',$addstr);
		global $setMoreFlag; //处理隐藏[高级设置]的初始化js
		if(!empty($setMoreFlag)){
			echo '<script type="text/javascript">setMoerInfo("'.$setMoreFlag.'",'.$this->mc.')</script>';
			$setMoreFlag = '';	
		}
	}
	
	
	/**
	* 处理栏目，通过传入数组，可指定特别的展示需求，如array('ids' => 5,'hidden' => 1)等
	*
	* @ex  $oA->fm_caid();
	*
	* @param    array    $cfg  多选配置参数 可为多个值 默认为空  ids为指定栏目的ID号 hidden为固定栏目
	*					
	*/
	function fm_caid($cfg = array()){
		isset($this->predata['caid']) || $this->predata['caid']=0;
		//if(!empty($cfg['topid'])){
			//$cfg['ids'] = cls_catalog::son_ccids($cfg['topid'],0); 
		//}
		//过滤掉hidden的指定和ids的指定
		if(!array_key_exists('hidden',$cfg) && !array_key_exists('ids',$cfg)){ 
			 $pid = $this->find_topcaid(); //吧第一层过滤掉。
			 $cfg['ids'] = cls_catalog::son_ccids($pid,0); 
		}
		$cfg = array_merge(
		array('value' => $this->predata['caid'],
		'chid' => $this->chid,
		'hidden' => empty($this->predata['caid']) || ($this->mc)? $this->isadd : !$this->isadd ? 0 : 1,
		'notblank' => 1,
		),
		$cfg);
		if($cfg['hidden']===1) echo "\n<input type=\"hidden\" name=\"{$this->fmdata}[caid]\" value=\"$cfg[value]\">\n";
		else tr_cns('所属栏目',"{$this->fmdata}[caid]",$cfg);
	}
	
	
	/**
	* 查找当前栏目的顶级栏目
	*
	*/
	function find_topcaid(){
		$catalogs = cls_cache::Read('catalogs');
		$caid = $this->predata['caid'];
		$pid = 0;
		while($arr = array_intersect_key($catalogs,array($caid=>'0',))){
			$pid = @$arr[$caid]['pid'];
			if(!$pid) break;
			$caid = $pid;
		}
		return $caid;
	}
	
	/**
	*处理分类，$coids类系id数组，如array(3,4,5)，为空则管理后台处理所有，会员中心不处理任何类系
	* 后台如果为空,前面显示过的类系，不再显示
	* @ex  $oA->fm_ccids(array());
	*
	* @param    array    $coids  多选类系ID参数 可为多个值 默认为空
	*					
	*/
	function fm_ccids($coids = array()){
		if($coids){
			foreach($coids as $coid){ 
				if(!in_array($coid,$this->coids_showed)){
					$this->fm_ccid($coid);
					$this->coids_showed[] = $coid;
				}
			}
		}elseif(!$this->mc){
			$cotypes = cls_cache::Read('cotypes');
			foreach($cotypes as $coid => $v){
				if(empty($v['self_reg']) && in_array($coid,$this->coids) && !in_array($coid,$this->coids_showed)){ 
					$this->fm_ccid($coid);
					$this->coids_showed[] = $coid;
				}
			}
		}
	}
	
	//处理单个分类
	//cfg带入传入的配置，以传入的配置优先
	function fm_ccid($coid = 0,$cfg = array()){
		$cotypes = cls_cache::Read('cotypes');
		if($coid && in_array($coid,$this->coids) && ($v = @$cotypes[$coid]) && empty($v['self_reg'])){
			$cfg = array_merge(
			array(
			'value' => empty($this->predata['ccid'.$coid]) ? 0 : $this->predata['ccid'.$coid],
			'coid' => $coid,
			'chid' => $this->chid,
			'max' => $v['asmode'],
			'notblank' => $v['notblank'],
			'hidden' => empty($this->predata['ccid'.$coid]) || !$this->isadd ? 0 : 1,
			'emode' => $v['emode'],
			'evarname' => "{$this->fmdata}[ccid{$coid}date]",
			'evalue' => @$this->predata["ccid{$coid}date"] ? date('Y-m-d',$this->predata["ccid{$coid}date"]) : '',
			'guide'=> $v['emode'] ? '截止日期为空则表示长期有效' : '',
			),
			$cfg);
			tr_cns($v['cname'],"{$this->fmdata}[ccid$coid]",$cfg);
		}
	}
	
	//展示文档字段
	//$arr为空，展示所有有效字段。$noinc=1，指排除$arr中的字段，否则为指定包含。
	function fm_fields($arr = array(),$noinc = 0){
		if(!$arr || $noinc){
			foreach($this->fields as $k => $v){
				if(!$arr || !in_array($k,$arr)) $this->fm_field($k);
			}
		}else{
			foreach($arr as $k) $this->fm_field($k);
		}
	}	
	//展示其它剩余字段,用于后续增加字段的自动展示
	function fm_fields_other($nos = array()){
		foreach($this->fields as $k => $v){
			if(!in_array($k,$this->fields_did) && (!$nos || !in_array($k,$nos))) $this->fm_field($k);
		}
	}
	
	//单个文档字段展示
	//cfg带入传入的配置，以传入的配置优先
	function fm_field($ename,$cfg = array()){
		$this->fm_subject_unique();
		if(!empty($this->fields[$ename]) && $this->fields[$ename]['available'] && !in_array($ename,$this->fields_did)){
			$a_field = new cls_field;
			$cfg = array_merge($this->fields[$ename],$cfg);
			$a_field->init($cfg,isset($this->predata[$ename]) ? $this->predata[$ename] : '');
			$a_field->isadd = $this->isadd;
			$a_field->trfield($this->fmdata);
			$this->fields_did[] = $ename;
			unset($a_field);
		}
	}
	
	//标题重名判断的文档主表
	function fm_subject_unique(){
		global $subject_table;
		$subject_table || $subject_table = atbl($this->chid);
	}	
	
	//展示多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	//可选项目array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	function fm_params($incs = array(), $cfg=array()){
		if(empty($incs)) $incs = $this->mc ? array('ucid') : array('createdate','clicks','jumpurl','customurl','relate_ids',);
		foreach($incs as $k) $this->fm_param($k, $cfg);
	}
	
	//展示指定的属性项，可选项目array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',enddate)
	//cfg[addnums]：显示的文档模板addnum，为空则为所有
	function fm_param($ename, $cfg=array()){
		global $timestamp;
		switch($ename){
			case 'enddate':
				trbasic('结束时间',"{$this->fmdata}[enddate]",date('Y-m-d',isset($this->predata['enddate']) ? $this->predata['enddate'] : $timestamp),'calendar');
			break;
			case 'jumpurl':
				trbasic('跳转URL',"{$this->fmdata}[jumpurl]",isset($this->predata['jumpurl']) ? cls_url::view_url($this->predata['jumpurl'],false) : '','text',array('guide'=>'请输入以http://开头的完整url。指定跳转后，所有该文档的url均为该地址。','w'=>50));
			break;
			case 'createdate':
				trbasic('添加时间',"{$this->fmdata}[createdate]",date('Y-m-d',isset($this->predata['createdate']) ? $this->predata['createdate'] : $timestamp),'calendar');//修改添加时间
			break;
			case 'clicks':
				trbasic('点击数',"{$this->fmdata}[clicks]",isset($this->predata['clicks']) ? $this->predata['clicks'] : 0,'text',array('guide'=>'文档的点击数量，添加是为0则按模型设置随机数','validate'=>' rule="int"'));
			break;
			case 'arctpls':
				$arctpls = explode(',',isset($this->predata['arctpls']) ? $this->predata['arctpls'] : '');				
				$guide = '管理模版库：[模板风格]→[模板设置]→['.cls_mtpl::mtplGuide('archive',true).']<br>模板绑定：[模板风格]→[模板绑定]→[<a href="?entry=tplconfig&action=tplchannel&isframe=1" target="_blank">文档内容页</a>]';
				trbasic('文档内容模板',"{$this->fmdata}[arctpls][0]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('archive',$this->chid),@$arctpls[0]),'select',array('guide'=>$guide));
				$arc_tpl = cls_tpl::arc_tpl(@$this->chid,@$predata['caid']);
				for($i = 1;$i <= @$arc_tpl['addnum'];$i ++){ 
					if(empty($cfg['addnums']) || in_array($i,$cfg['addnums'])){
						trbasic('附加页'.$i.'模板',"{$this->fmdata}[arctpls][$i]",makeoption(array('' => '不设置') + cls_mtpl::mtplsarr('archive',$this->chid),@$arctpls[$i]),'select');
					}else{
						trhidden("{$this->fmdata}[arctpls][$i]",@$arctpls[$i]);	
					}
				}
				unset($arctpls);
			break;
			case 'customurl':
				trbasic('文档页静态保存格式',"{$this->fmdata}[customurl]",isset($this->predata['customurl']) ? $this->predata['customurl'] : '','text',array('guide'=>'留空为默认格式，{$topdir}顶级栏目目录，{$cadir}所属栏目目录，{$y}年 {$m}月 {$d}日 {$h}时 {$i}分 {$s}秒 {$chid}模型id  {$aid}文档id {$page}分页页码 {$addno}附加页id，id之间建议用分隔符_或-连接。','w'=>50));
			break;
			case 'relate_ids':
				aboutarchive(isset($this->predata['relatedaid']) ? $this->predata['relatedaid'] : '');
			break;
			case 'dpmid':
				trbasic('附件下载权限设置',"{$this->fmdata}[dpmid]",makeoption(array('-1' => '继承类目权限') + pmidsarr('down'),isset($this->predata['dpmid']) ? $this->predata['dpmid'] : -1),'select');
			break;
			case 'ucid':
				if($this->mc){
					$curuser = cls_UserMain::CurUser();
					$nowUclasses = cls_Mspace::LoadUclasses($curuser->info['mid'],0);
					$ucidsarr = array();
					foreach($nowUclasses as $k => $v) if(!$v['cuid']) $ucidsarr[$k] = $v['title'];
					if($ucidsarr){
						trbasic('我的分类',"{$this->fmdata}[ucid]",makeoption(array(0 => '不设置分类') + $ucidsarr,isset($this->predata['ucid']) ? $this->predata['ucid'] : 0),'select');
					}
				}
			break;
			case 'subjectstr':
				trhidden("{$this->fmdata}[subjectstr]",empty($this->predata['subject'])?'':$this->predata['subject']);
			break;
		}
			
	}
	
	
	/**
	*显示验证码
	*
	* @param    string    $type  验证码类型  默认为archive
	*						type的值可以在/dynamic/syscache/cfregcodes.cac.php里配置
	*/
	//显示验证码
	function fm_regcode($type = 'archive'){
		if($type && $this->isadd && $this->mc){
			tr_regcode($type);
		}
	}
	
	//管理后台：$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
	function fm_guide_bm($str = '',$type = 0){
		if($this->mc){
			m_guide($str,$type ? $type : '');
		}else{
			if(!$str){
				$str = $this->isadd ? 'archiveadd' : 'archivedetail';
				if(is_file(M_ROOT."dynamic/aguides/{$str}_{$this->chid}.php")) $str .= "_{$this->chid}";
				$type = 0;
			}
			a_guide($str,$type);
		}
	}
	
	//处理验证码
	function sv_regcode($type = 'archive',$return_error = 0){
		global $regcode;
		if($type && $this->isadd && $this->mc){
			if(!regcode_pass($type,empty($regcode) ? '' : trim($regcode))) return $this->message('验证码错误',axaction(2,M_REFERER),$return_error);
		}
	}
	
	//分析当前会员的发布权限及管理权限，在fm_pre_cns之后执行
	function sv_allow($return_error = 0){
		$curuser = cls_UserMain::CurUser();
		if(!$this->mc && $re = $this->NoBackPm($this->predata['caid'])) $this->message($re,axaction(2,M_REFERER),$return_error);
		if($this->isadd && ($re = $curuser->arcadd_nopm($this->chid,$this->predata))) $this->message($re,axaction(2,M_REFERER),$return_error);
	}
		
	//添加时的类目预处理及异常分析
	//$incs:指定只处理某些类系
	function sv_pre_cns($incs = array(),$return_error = 0){
		if(!$this->isadd) return;//仅添加时需要
		$cotypes = cls_cache::Read('cotypes');
		foreach(array_merge(array(0),array_keys($cotypes)) as $k){
			if(!$incs || in_array($k,$incs)){
				if($re = $this->sv_pre_cn($k,array(),$return_error)) return $re;
			}
		}
	}
	//处理单个类目
	//cfg带入传入的配置，以传入的配置优先
	function sv_pre_cn($coid = 0,$cfg = array(),$return_error = 0){
		if(!$this->isadd) return;//仅添加时需要
		$fmdata = &$GLOBALS[$this->fmdata];
		if(!$coid){
			if(empty($fmdata['caid']) || !cls_cache::Read('catalog',$fmdata['caid'])) return $this->message('请指定正确的栏目',axaction(2,M_REFERER),$return_error);
			$this->predata['caid'] = $fmdata['caid'];
			if(!$this->mc && $re = $this->NoBackPm($this->predata['caid'])) $this->message($re,axaction(2,M_REFERER),$return_error);
		}elseif(in_array($coid,$this->coids) && isset($fmdata["ccid$coid"])){
			$cotypes = cls_cache::Read('cotypes');
			if(($v = $cotypes[$coid]) && !$v['self_reg']){
				$cfg && $v = array_merge($v,$cfg);
				$fmdata["ccid$coid"] = empty($fmdata["ccid$coid"]) ? '' : $fmdata["ccid$coid"];
				if($v['notblank'] && !$fmdata["ccid$coid"]) return $this->message("请设置 $v[cname] 分类",axaction(2,M_REFERER),$return_error);
				if($fmdata["ccid$coid"]) $this->predata['ccid'.$coid] = $fmdata["ccid$coid"];
				if($v['emode']){
					$fmdata["ccid{$coid}date"] = !cls_string::isDate($fmdata["ccid{$coid}date"]) ? 0 : trim($fmdata["ccid{$coid}date"]);
					!$fmdata["ccid$coid"] && $fmdata["ccid{$coid}date"] = 0;
					if($fmdata["ccid$coid"] && !$fmdata["ccid{$coid}date"] && $v['emode'] == 2) return $this->message("请设置 $v[cname] 分类期限",axaction(2,M_REFERER),$return_error);
				}
			}
		}
	}
	
	//添加一个文档记录	
	function sv_addarc(){
		global $m_cookie;
		if(!$this->isadd) return 0;//仅添加时需要
		$fmpost = @$GLOBALS['fmsend_reload_flag']; //表单提交过来的,每次不同；但是通过刷新页面提交的,会与上一次不同；此值不显示,不入数据库；仿照$fmdata = &$GLOBALS[$this->fmdata];取值。
		$fmcook = empty($m_cookie['fmsend_reload_flag']) ? '-' : $m_cookie['fmsend_reload_flag']; 
		if($fmpost!=$fmcook){ 
			msetcookie('fmsend_reload_flag', $fmpost, 86400); //保存，用于判断是否通过刷新页面提交的。
			empty($this->arc) && $this->arc = new cls_arcedit;
			$this->aid = $this->arc->arcadd($this->chid,$this->predata['caid']);
			return $this->aid;
		}else{ 
			return 0;	
		}
	}
	
	//添加文档记录之后，更新内容时出现异常的话，需要删除添加好的文档记录
	function sv_rollback(){
		if($this->aid && $this->isadd){
			global $db,$tblprefix;
			$c_upload = cls_upload::OneInstance();
			$db->query("DELETE FROM {$tblprefix}archives_sub WHERE aid='{$this->aid}'");
			$db->query("DELETE FROM {$tblprefix}".atbl($this->chid)." WHERE aid='{$this->aid}'");
			$db->query("DELETE FROM {$tblprefix}archives_{$this->chid} WHERE aid='{$this->aid}'");
			$c_upload->closure(1);
		}
	}
	
	//批量类系处理，可传入需要的类系
	function sv_cns($incs = array(),$return_error = 0){
		$cotypes = cls_cache::Read('cotypes');
		foreach(array_merge(array(0),array_keys($cotypes)) as $k){
			if(!$incs || in_array($k,$incs)){
				if($re = $this->sv_cn($k,array(),$return_error)) return $re;
			}
		}
	}
	
	//单个类系处理
	//cfg带入传入的配置，以传入的配置优先
	function sv_cn($coid = 0,$cfg = array(),$return_error = 0){
		$fmdata = &$GLOBALS[$this->fmdata];
		if(!$coid){
			if(isset($fmdata['caid'])) $this->arc->arc_caid($fmdata['caid']);
		}else{
			$cotypes = cls_cache::Read('cotypes');
			if(isset($fmdata["ccid$coid"]) && in_array($coid,$this->coids)){
				if(($v = @$cotypes[$coid]) && !$v['self_reg']){
					$cfg && $v = array_merge($v,$cfg);
					if($v['notblank'] && !$fmdata["ccid$coid"]) return $this->message("请设置 $v[cname] 分类",M_REFERER,$return_error);
					$this->arc->arc_ccid($fmdata["ccid$coid"],$coid,$v['emode'] ? @$fmdata["ccid{$coid}date"] : 0);
				}
			}
		}
	}
	
	function sv_fields($nos = array(),$return_error = 0){//$nos设置排除字段
		foreach($this->fields as $k => $v){
			if(!$nos || !in_array($k,$nos)){
				if($re = $this->sv_field($k,array(),$return_error)) return $re;
			}
		}
	}
	
	//单个字段处理，可以指定字段某个配置参数
	function sv_field($ename,$cfg = array(),$return_error = 0){
		global $sptype,$spsize;
		$fmdata = &$GLOBALS[$this->fmdata];
		if(isset($fmdata[$ename]) && $v = @$this->fields[$ename]){
			$c_upload = cls_upload::OneInstance();
			$cfg && $v = array_merge($v,$cfg);
			if($v['datatype'] == 'htmltext' && $sptype == 'auto'){
				$spsize = empty($spsize) ? 5*1024 : $spsize*1024;
				$fmdata[$ename] = SpBody($fmdata[$ename],$spsize,'[##]');
			}
			
			$a_field = new cls_field;
			$a_field->init($v,isset($this->predata[$ename]) ? $this->predata[$ename] : '');
			$fmdata[$ename] = $a_field->deal($this->fmdata,''); //这里要直接返回错误信息,否则不能执行sv_rollback()
			if($a_field->error){//捕捉出错信息
				$this->sv_rollback();
				return $this->message($a_field->error,axaction(2,M_REFERER),$return_error);
			}
			unset($a_field);
			
			if($ename == 'keywords') $fmdata[$ename] = cls_string::keywords($fmdata[$ename],@$this->predata[$ename]);
			$this->arc->updatefield($ename,$fmdata[$ename],$v['tbl']);
			if($arr = multi_val_arr($fmdata[$ename],$v)) foreach($arr as $x => $y) $this->arc->updatefield($ename.'_'.$x,$y,$v['tbl']);
		}
	}
	
	//处理多个属性项，管理后台默认为array('createdate','clicks','jumpurl','customurl','relate_ids')，会员中心默认为array('jumpurl','ucid')
	function sv_params($incs = array()){
		if(empty($incs)) $incs = $this->mc ? array('ucid') : array('createdate','clicks','jumpurl','customurl','relate_ids',);
		foreach($incs as $k) $this->sv_param($k);
	}
	
	//处理指定的属性项，可选项目array('jumpurl','ucid','createdate','clicks','arctpls','customurl','dpmid','relate_ids',)
	function sv_param($ename){
		global $timestamp;
		$fmdata = &$GLOBALS[$this->fmdata];
		if($ename == 'relate_ids' && !empty($GLOBALS['relatedaid'])) $this->arc->autorelated();
		if($ename && isset($fmdata[$ename])){
			if($ename == 'createdate'){//添加时间
				$fix = $this->isadd ? $timestamp : @$this->arc->archive['createdate'];
				$fix = $fix - strtotime(date('Y-m-d',$fix));
				$this->arc->updatefield($ename,empty($fmdata[$ename]) ? $timestamp : strtotime($fmdata[$ename])+$fix);
			}elseif($ename == 'enddate'){//结束时间
				$this->arc->updatefield($ename,empty($fmdata[$ename]) ? 0 : strtotime($fmdata[$ename]));
			}elseif($ename == 'arctpls'){//自定模板
				$this->arc->updatefield($ename,implode(',',$fmdata[$ename]));
			}elseif($ename == 'customurl'){//自定静态url
				$this->predata['nokeep'] = $this->arc->updatefield($ename,trim($fmdata[$ename]));
			}elseif($ename == 'jumpurl'){//跳转url
				$this->arc->updatefield($ename,cls_url::save_url(trim($fmdata[$ename])));
			}elseif($ename == 'subjectstr'){//标题的首字母组合以及全拼
				if(strcmp($fmdata['subject'],$fmdata['subjectstr']) != 0){
					$fmdata['subject'] = str_replace('\\','',$fmdata['subject']);
					$this->arc->updatefield($ename,cls_string::Pinyin($fmdata['subject'],1));
				}				
			}else{//个人分类ucid、点击数clicks、下载权限dmpid
				$this->arc->updatefield($ename,max(0,intval($fmdata[$ename])));
			}
		}
	}
	
	//文档记录未添加成功的处理
	function sv_fail($return_error = 0){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1);
		return $this->message('文档添加失败',axaction(2,M_REFERER),$return_error);
	}
	
	//执行自动操作及更新以上变更
	function sv_update(){
		$this->isadd && $this->arc->autocheck();
		$this->isadd && $this->arc->autoclick(); //默认点击数
		$this->arc->auto();
                $chid = isset($this->chid)?$this->chid:'';
		$this->arc->updatedb($chid);
		if($this->isadd){ 
			$this->arc->autopush(); //自动推送
		}
	}
	
	//文档添加或修改成功后的上传处理
    //furl:附件地址; 把一个单图字段转化为多图字段,上传时,分离保存成多个文档,需要传furl参数
	function sv_upload($furl=''){
		$c_upload = cls_upload::OneInstance();
        $paras = $furl ? array('aid'=>$this->aid,'url'=>$furl) : $this->aid;
		$c_upload->closure(1,$paras);
		$c_upload->saveuptotal(1);
	}
	
	//要指定合辑id变量名$pidkey或合辑pid(数字)、合辑项目$arid
	function sv_album($pidkey = 'pid',$arid = 0){
		if($pidkey && $arid = (int)$arid){
			if(is_numeric($pidkey)){
				$pidval = $pidkey;
			}else{
				$fmdata = &$GLOBALS[$this->fmdata];
				$pidval = intval(@$fmdata[$pidkey]);	
			}
			if(!empty($pidval)) $this->arc->set_album($pidval,$arid);
		}
	}
	
	//最后执行自动静态
	function sv_static(){
		//$this->arc->autostatic(empty($this->predata['nokeep']) ? 1 : 0);
		//sv_album()等操作,并没有更新$this->arc等资料; 所以重新new一个cls_arcedit来更新静态
		$arc = new cls_arcedit;
		$arc->set_aid($this->aid,array('chid'=>$this->chid));
		$arc->autostatic(empty($this->predata['nokeep']) ? 1 : 0);
	}
		
	/*结束时需要的事务， 如：操作记录及成功提示
	 *@param $arr_direct 跳转配置数组 详情见cls_message::show（）的参数设置
	 *@param $msg        显示的信息字符串，默认添加成功
	 *
	 */
	function sv_finish($arr_direct=array(),$msg=NULL){
		$modestr = $this->isadd ? '添加' : '修改';
		$this->mc || adminlog($modestr.'文档');
		if($this->isadd && $arr_direct) {
			$msg = empty($msg) ? '添加成功' : $msg;
			cls_message::show('<br/>'.$msg,$arr_direct);
		}
		$this->message('文档'.$modestr.'完成',axaction(6,M_REFERER));
	}
	
	//把多图转化为文档
	//cfgs:pid/pfield,chid,caid,arid
	//未考虑:把图片属性存为某个字段属性
	static function sv_images2arcs($fmdata=array(),$field='thumb',$cfgs=array(),$key=''){
		cls_env::SetG('fmdata',$fmdata);
		$key || $key = $field;
		$oA = new cls_archive($cfgs); 
		$oA->isadd = 1;
		$oA->read_data();
		$oA->setvar('coids',array(0));
		$fields = &$oA->fields;
		$oA->sv_pre_cns(array());
		$msg = array(); $fimg = ''; //第一个图
		$_a = explode("\n",str_replace(array("\r","\r\r"),array("\n","\n"),$fmdata[$key]));
		$umax = empty($cfgs['max']) ? 50 : $cfgs['max']; $ucnt=0;
		foreach($_a as $val){
		if(!empty($val)){
			$fmdata[$field] = $val; 
			if(strpos($fmdata[$field],'|')>0){
				$_pica = explode('|',$fmdata[$field]);
				$fmdata[$field] = $_pica[0];
			}
			$fmdata[$field] = str_replace(array('##'," ","\t","\r","\n"),'',$fmdata[$field]);
			cls_env::SetG("fmdata.$field",$fmdata[$field]);
			$oA->arc = new cls_arcedit;
			$oA->aid = $oA->arc->arcadd($oA->chid,$oA->caid);
			if(!$oA->aid){
				$msg[] = $oA->sv_fail(1); 
			}else{
				$ucnt++; if($ucnt>$umax) break;
				$msg[] = $oA->sv_cns(array(),1); 
				$msg[] = $oA->sv_fields(array(),1); 
				$oA->sv_update(); 
				$oA->sv_upload($fmdata[$field]); 
				$pidkey = isset($cfgs['pid']) ? $cfgs['pid'] : $cfgs['pfield'];
				if($pidkey && $cfgs['arid']) $oA->sv_album($pidkey,$cfgs['arid']); 
				$oA->sv_static();
				if(empty($fimg) && !empty($oA->arc->archive[$field])) $fimg = $oA->arc->archive[$field]; 
			} 
			unset($oA->arc); // ??? 
		}  } //print_r($msg); die('xxx');
		return array($msg,$fimg);
	}
}
?>
