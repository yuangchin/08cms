<?php
/**
 *  archivesbase.cls.php 文档列表管理的操作基类	 
 *
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */

!defined('M_COM') && exit('No Permisson');
class cls_archivesbase{
	
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $channel = array();//当前模型
	public $album = array();//指定合辑pid的资料
	
	
	//搜索有关
	public $oS	= NULL;//搜索项处理的对象
	public $sqlall = '';//完整sql字串
	public $acount = 0;//信息总数的统计
	public $filterstr = '';//筛选参数在url中的传递字串
	
	//内容列表
	public $oC	= NULL;//列数据处理的对象
	
	//批量操作
	public $oO	= NULL;//批量项目处理的对象
	public $rs	= array();//在数据储存端暂存所选列表数据资料
	
	//设置项操作
	public $oE	= NULL;//列表中设置项处理的对象
	
	
	/**
	 * 构造函数初始化设置
	 */
    function __construct($cfg = array()){
		global $db,$tblprefix;
		
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;
		
		//模型与分表
		$splitbls = cls_cache::Read('splitbls');
		if(empty($this->A['chid']) || !($this->channel = cls_channel::Config($this->A['chid']))) $this->message('请指定文档类型');
		if(empty($this->A['url'])) $this->message('请添写表单提交URL');
		$this->A['stid'] = $this->channel['stid'];
		$this->A['tbl'] = 'archives'.$this->channel['stid'];
		if(empty($this->A['coids'])) $this->A['coids'] = empty($splitbls[$this->A['stid']]) ? array() : $splitbls[$this->A['stid']]['coids'];
		$this->A['multi_chid'] = empty($this->A['multi_chid']) ? 0 : 1;//兼容同主表的多模型管理，此时chid是配置样例
		if(empty($this->A['backallow']) && !$this->mc) $this->A['backallow'] = 'normal';//后台管理权限
		if(empty($this->A['pid'])) $this->A['pid'] = 0;//合辑id
		if(empty($this->A['isab'])) $this->A['isab'] = 0;//操作模式设置：0为普通管理列表，1为辑内管理列表，2为加载内容列表，3为推送位加载管理
		if(!in_str('chid=',$this->A['url']) && $this->A['isab'] < 3) $this->A['url'] .= "&chid={$this->A['chid']}"; 
		
		//高级搜索选项与infloat
		$this->A['cbsMore'] = $GLOBALS['cbsMore'] = empty($GLOBALS['cbsMore']) ? 0 : intval($GLOBALS['cbsMore']);//是否显示高级搜索项
		if($this->A['cbsMore']) $this->filterstr = "&cbsMore={$this->A['cbsMore']}";
		$this->A['MoreSet'] = 0;//是否出现高级选项
		$this->filterstr .= empty($GLOBALS['infloat']) ? '' : "&infloat=1"; //是否传递infloat
		
		//当前页码
		$this->A['page'] = &$GLOBALS['page'];
		$this->A['page'] = empty($this->A['page']) ? 1 : max(1,intval($this->A['page']));
		if(submitcheck('bfilter')) $this->A['page'] = 1;
		
		//列表区块
		global $mrowpp,$atpp;
		$this->A['rowpp'] = empty($this->A['rowpp']) ? ($this->mc ? $mrowpp : $atpp) : max(1,intval($this->A['rowpp']));//每页展示的条数
		$this->A['cols'] = empty($this->A['cols']) ? 0 : max(0,intval($this->A['cols']));
		$this->A['cols'] = $this->A['cols'] < 2 ? 0 : min(10, $this->A['cols']);
		if($this->A['cols']) $this->A['rowpp'] = ceil($this->A['rowpp'] / $this->A['cols']) * $this->A['cols'];
		if(empty($this->A['mfm'])) $this->A['mfm'] = 'fmdata';	//列表区中设置项的表单数据数组名
		
		//不同操作模式下的预警告处理
		if(in_array($this->A['isab'],array(1,2))){//辑内管理或加载
			if(empty($this->A['pid'])) $this->message('请指定合辑id');
			if(empty($this->A['arid'])) $this->message('请指定合辑项目id');
			if($abrel = cls_cache::Read('abrel',$this->A['arid'])){
				$this->A['abtbl'] = $abrel['tbl'];//合辑关系的记录表
			}else $this->message('请指定正确的合辑项目id');
			$this->pid_allow($this->A['pid'],@$this->A['pids_allow']);
			if(!in_str('pid=',$this->A['url'])) $this->A['url'] .= "&pid={$this->A['pid']}";
			if($this->A['abtbl'] && empty($this->A['bpre'])) $this->A['bpre'] = 'b.';//独立合辑关系表的前缀,
			if(($ntbl = atbl($this->A['pid'],2)) && $this->album = $db->fetch_one("SELECT * FROM {$tblprefix}$ntbl WHERE aid='{$this->A['pid']}'")){//读取合辑资料，只保留aid,subject,mid及url资料
				cls_ArcMain::Parse($this->album);
				foreach($this->album as $k => $v) if(!in_array($k,array('aid','caid','subject','mid','mname')) && !in_str('arcurl',$k)) unset($this->album[$k]);
			}else $this->message('请指定正确的合辑');
		}elseif($this->A['isab'] == 3){
			if(empty($this->A['paid'])) $this->message('请指定推送位id');
			if(!in_str('paid=',$this->A['url'])) $this->A['url'] .= "&paid={$this->A['paid']}";
		}
		
		//sql查询初始化，where、from、select要么完全重写，要么留空
		if(empty($this->A['pre'])) $this->A['pre'] = 'a.';
		if(empty($this->A['isab'])){//普通文档管理
			if(empty($this->A['where'])) $this->A['where'] = '';
			if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1);
			if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		}elseif($this->A['isab'] == 1){//辑内管理列表
			if($this->A['abtbl']){//独立合辑关系表
				if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*,{$this->A['bpre']}*";
				if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['abtbl']} ".substr($this->A['bpre'],0,-1)." INNER JOIN {$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1)." ON {$this->A['pre']}aid={$this->A['bpre']}inid";
				if(empty($this->A['where'])) $this->A['where'] = "{$this->A['bpre']}pid='{$this->A['pid']}'";
			}else{//内置合辑关系表
				if(empty($this->A['where'])) $this->A['where'] = "{$this->A['pre']}pid{$this->A['arid']}='{$this->A['pid']}'";
				if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1);
				if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
			}
		}elseif($this->A['isab'] == 2){//合辑加载列表
			//注：先查询出NOT IN()里IDs, 比直接用SELECT子句，平均要快上10倍左右	
			if($this->A['abtbl']){//独立合辑关系表
				if(empty($this->A['where'])){ 
					$subids = cls_DbOther::SubSql_InIds('inid', "{$this->A['abtbl']}", "pid='{$this->A['pid']}'");
					$this->A['where'] = "{$this->A['pre']}aid NOT IN($subids)";
				}
			}else{
				if(empty($this->A['where'])){
					$subids = cls_DbOther::SubSql_InIds('aid', "{$this->A['tbl']}", "pid{$this->A['arid']}='{$this->A['pid']}'");
					$this->A['where'] = "{$this->A['pre']}aid NOT IN($subids)";	
				}
			}
			if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1);
			if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		}elseif($this->A['isab'] == 3){//推送加载列表
			if(empty($this->A['where'])){
				$this->A['where'] = cls_pusher::InitWhere($this->A['paid'],$this->A['pre']);
				//对推送设置：附加过滤SQL里面含有{$tblprefix}进行处理
				$this->A['where'] = str_replace('{$tblprefix}',$tblprefix,$this->A['where']);
			}
			if(empty($this->A['from'])) $this->A['from'] = cls_pusher::InitFrom($this->A['paid'],$this->A['pre']);
			if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		}
		
		//批量设置
		if(empty($this->A['ofm'])) $this->A['ofm'] = 'arcdeal';//操作选择项的表单数据数组名，如：选择了设置栏目，arcdeal['caid']=1
		if(empty($this->A['opre'])) $this->A['opre'] = 'arc';//设置值的参数前缀，如：设置栏目id为23时，arccaid=23
		
		$cfg = array();
		foreach($this->A as $k => $v){
			if(!in_array($k,array('url','cols','cbsMore','MoreSet','where','from','select',))) $cfg[$k] = $v;
		}
		
		//搜索项处理的对象
		$this->oS = new cls_arcsearchs($cfg);
		//列数据处理的对象
		$this->oC = new cls_arccols($cfg);
		//列表中设置项处理的对象
		$this->oE = new cls_arcsets($cfg);
		//批量设置
		$this->oO = new cls_arcops($cfg);
    }
	
	/**
	 * 查找当前栏目的顶级栏目
	 */
	function find_topcaid(){
		$catalogs = cls_cache::Read('catalogs');
		$caid = $GLOBALS['caid'];
		$pid = 0;
		while($arr = array_intersect_key($catalogs,array($caid=>'0',))){
			$pid = @$arr[$caid]['pid'];
			if(!$pid) break;
			$caid = $pid;
		}
		return $caid;
	}
	
	/**
	* 绑定项目
	*
	* @param    string     $key  项目关键字
	* @param    string     $var  项目变量名
	* 
	*/
	function setvar($key,$var){
		$this->$key = $var;	
	}
	
	
	/**
	* 弹窗对话框
	*
	* @param    string     $str  提示字符串 默认为空
	* @param    string     $url  跳转url ，默认为空
	* 
	*/
	function message($str = '',$url = ''){
		call_user_func('cls_message::show',$str,$url);
	}
	
	
	/**
	* 用于会员中心合辑权限判断
	*
	* @param    int        $pid         该文档的合辑ID 默认为空
	* @param    string     $allow_pids  允许管理的合辑id ，默认为空
	* 
	*/
	function pid_allow($pid = 0,$allow_pids = ''){
		if(!$this->mc || !$pid) return;
		if(empty($allow_pids)) $this->message('请输入允许管理的合辑id范围');
		if($allow_pids=='-1'){
			;//可用于会员中心，可加载别人的信息做合辑资料
		}elseif($allow_pids == 'self'){//指定为自已发布的合辑
			global $db,$tblprefix;
			$curuser = cls_UserMain::CurUser();
			if($curuser->info['mid'] != $db->result_one("SELECT mid FROM {$tblprefix}".atbl($pid,2)."  WHERE aid='$pid'")) $this->message('您只能管理自已发布的内容');
		}elseif($allow_pids = explode(',',$allow_pids)){
			if(!in_array($pid,$allow_pids)) $this->message('您没有当前合辑的管理权限');
		}else $this->message('请输入允许管理的合辑id范围');
	}

	function top_head(){
		$curuser = cls_UserMain::CurUser();
		if($this->mc){
			!defined('M_COM') && exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			if($re = $curuser->NoBackFunc($this->A['backallow'])) $this->message($re);
		}
		echo "<title>内容管理 - {$this->channel['cname']}</title>";
	}
	
	
	/**
	* 提示项
	*
	* @param    string     $str  为帮助标识或直接的文本内容 默认为空，
	*							
	* @param    string     $type 提示类型 ，默认为0 
	*							 =0     当会员中心 直接显示$str的内容  当管理后台 显示$str为帮助缓存标记的内容
	*							 >0     当管理后台 直接显示$str的内容
	*							 =tip   可隐藏的提示框只有会员中心有
	*							 =fix	固定的提示框只有会员中心有
	* 
	*/
	function guide_bm($str = '',$type = 0){
		if($this->mc){
			m_guide($str,$type ? $type : '');
		}else{
			if(!$str){
				$str = 'archivesedit';
				$type = 0;
			}
			a_guide($str,$type);
		}
	}
	
	
	/**
	* 筛选添加项目
	*
	* @ex  $oL->s_additem('keyword',array('fields' => array(),));
	*
	* @param    string    $key  项目关键字 默认为空
	* @param    array     $cfg  项目配置参数 ，可选，默认为空
	*                                     
	*/
	function s_additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		$this->oS->additem($key,$cfg);
	}
	
	
	/**
	* 筛选表单头部
	*
	*/
	function s_header(){
		echo form_str('arcs_'.md5($this->A['url']),$this->A['url']);
		tabheader_e();
		echo "<tr><td class=\"".($this->mc ? 'item2' : 'txt txtleft')."\">\n<div class='search_area'>\n";
		trhidden('page',$this->A['page']);
	}
	
	
	/**
	* 筛选字符串sql语句的组装和查询处理
	*
	*/
	function s_deal_str(){//将oS中处理结果转到当前对象中
		$this->s_sqlstr();
		$this->s_filterstr();
	}
	
	/**
	* sql处理
	*
	*/
	function s_sqlstr(){
		global $db,$tblprefix;
		$wherestr = empty($this->A['where']) ? '' : " AND {$this->A['where']}";
		if(empty($this->oS->no_list)){
			foreach($this->oS->wheres as $k => $v) $wherestr .= " AND $v";//搜索附加产生的where因素
			if(!$this->acount = $db->result_one('SELECT COUNT(*) FROM '.$this->A['from'].($wherestr ? " WHERE ".substr($wherestr,5) : ''))){
				$this->acount = 0;
			}
			// 排除已加载的文档
			if(in_array($this->A['isab'],array(3))){ // isab=2,初始化，已经处理：array(2,3)->array(3)
				if($this->acount && $loadeds = $this->s_loaded_ids()){
					//处理总数
					$this->acount -= count($loadeds);
					$this->acount = max(0,intval($this->acount));
					//处理wherestr
					$wherestr .= " AND {$this->A['pre']}aid ".multi_str($loadeds,1);
				}
			}
			if($wherestr) $wherestr = " WHERE ".substr($wherestr,5);
		}else{
			$wherestr = ' WHERE 0';
			$this->acount = 0;
		}
		$this->sqlall = "SELECT ".$this->A['select'].' FROM '.$this->A['from'].$wherestr.' ORDER BY '. $this->oS->orderby;
	}
	
	/**
	* 用于合辑的逻辑处理，过滤掉合辑加载列表里已经加载过的id
	*
	*/
	function s_loaded_ids(){//获取已加载过的id
		if($this->A['isab'] == 2){//排除已加载的文档
			if($this->A['abtbl']){//独立合辑关系表
				return cls_DbOther::SubSql_InIds('inid', $this->A['abtbl'], "pid='{$this->A['pid']}'", '');
			}else{
				return cls_DbOther::SubSql_InIds('aid', $this->A['tbl'], "pid{$this->A['arid']}='{$this->A['pid']}'", '');
			}
		}elseif($this->A['isab'] == 3){//排除已加载的文档
			return cls_DbOther::SubSql_InIds('fromid', cls_PushArea::ContentTable($this->A['paid']), "", '');
		}else return array();
	}	

	/**
	* 筛选字符串的url组装
	*
	*/
	function s_filterstr(){
		foreach($this->oS->filters as $k => $v){
			$this->filterstr .= "&$k=".(is_numeric($v) ? $v : rawurlencode($v));
		}
	}
	
	
	/**
	* 列表单个项目的显示
	*
	* @param    string    $key  项目关键字 默认为空
	* @return   html   输出html字符串                                   
	*/
	function s_view_one($key = ''){
		if(empty($key) || empty($this->oS->htmls[$key])) return;
		echo $this->oS->htmls[$key].' ';
		unset($this->oS->htmls[$key]);
	}
	
	/**
	* 列表多个项目的显示
	*
	* @param    array    $incs  项目关键字数组 默认为空
	*                                   
	*/
	function s_view_array($incs = array()){
		if($incs){
			foreach($incs as $k) $this->s_view_one($k);
		}else{
			foreach($this->oS->htmls as $k => $v) $this->s_view_one($k);
		}
	}
	
	
	function s_adv_point(){
		echo strbutton('bfilter','筛选');
		echo "\n <label><input class='checkbox' type='checkbox' name='cbsMore' id='cbsMore' value='1' onclick=\"display('boxMore')\"".($this->A['cbsMore'] ? "checked = 'checked'" : " ")."/>高级选项</label>";	
		echo "\n<div id='boxMore'".(!$this->A['cbsMore'] ? " style='display:none'>" : " style='display:'>");
		$this->A['MoreSet'] = 1;
	}
	
	
	function s_footer(){
		if(empty($this->A['MoreSet'])){
			echo strbutton('bfilter','筛选');
		}else echo "</div></div>";//高级区结尾
		tabfooter();
		unset($this->oS);
	}
	
	
	/**
	* 列表头部
	*
	* @param    string    $title    列表标题 默认为内置的title
	* @param    int       $addmode  标题模式 默认为0
	*							  =0   上面的$title替换掉默认的
	*							  =1   在默认的标题后添加$title内容
	*                                   
	*/
	function m_header($title = '',$addmode = 0){//$addmode=1时在默认的标题后添加$title内容，否则$title替换默认title
		if(!$title || $addmode){
			$tts = (!empty($GLOBALS['caid']) && $catalog = cls_cache::Read('catalog',$GLOBALS['caid'])) ? $catalog['title'] : $this->channel['cname'];
			if(empty($this->A['isab'])){
				$tt = "$tts 内容管理";
			}elseif($this->A['isab'] == 1){
				$tt = "[{$this->album['subject']}] 内的 $tts";
			}elseif($this->A['isab'] == 2){
				$tt = "[{$this->album['subject']}] 加载 $tts";
			}elseif($this->A['isab'] == 3){
				$tt = "[推送位] 加载 $tts";
			}
		}
		$title = $addmode ? ($tt.$title) : ($title ? $title : $tt);
		tabheader($title,'','',20);
	}
	
	
	/**
	* 列表添加项目
	*
	* @ex  s_additem('subject',array('title'=>'标题','hidden'=>1,));
	*
	* @param    string    $key  项目关键字 默认为空
	* @param    array     $cfg  项目配置参数 ，可选，默认为空
	*						可为 title项目名称, hidden将某个搜索值固定下来, url跳转链接等等
	*                                     
	*/
	function m_additem($key = '',$cfg = array()){//增加列表中的列
		if(!$key) $this->message('请列项目的key不能为空');
		if($this->oC->additem($key,$cfg) == 'undefined') $this->message("列项目{$key}未找到处理方法");
	}
	
	function m_addgroup($mainstyle,$topstyle = ''){//增加分组
		if(!$mainstyle) return;
		$this->oC->addgroup($mainstyle,$topstyle);
	}
	
	/**
	* 列表项目的显示和隐藏
	*
	*                                     
	*/
	function m_view_top(){
		if($this->A['cols']) return;//多行多列的模式不需要使用这个方法
		$narr = $this->oC->fetch_top_row();
		$cfgs = $this->oC->cfgs;
		foreach($narr as $k => $v){//处理索引行的显示方式
			$narr[$k] = $this->m_top_mode($v,$cfgs[$k]);
		}
		trcategory($narr);
	}
	
	function m_top_mode($str = '',$cfg = array()){
		$re = '|';
		if(!empty($cfg['side']) && in_array($cfg['side'],array('L','R'))){
			$re .= $cfg['side'];
		}
		$re .= '|';
		if(!empty($cfg['view']) && in_array($cfg['view'],array('S','H'))){
			$re .= $cfg['view'];
		}
		if($re == '||') $re = '';
		return $str.$re;
	}
	
	/**
	* 列表分页sql处理
	*                                    
	*/
	function m_db_array(){
		if(empty($this->rs)){
			global $db;
			$pagetmp = $this->A['page'];
			do{
				$query = $db->query("{$this->sqlall} LIMIT ".(($pagetmp - 1) * $this->A['rowpp']).",{$this->A['rowpp']}");
				$pagetmp--;
			} while(!$db->num_rows($query) && $pagetmp);
			$re = array();
			while($r = $db->fetch_array($query)) $re[] = $r;
			$this->rs = $re;
		}
		return $this->rs;
	}
			
	/**
	* 处理列表区数据及显示html
	* $cfg[trclass]：行的css  
	$ $cfg[divclass]：div[单元格]的css  
	*                                     
	*/
	function m_view_main($cfg=array()){
		$rs = $this->m_db_array();
		if($this->A['cols']){//将所有项都通过一种定义格式封装起来，作为一个项目输出
			$html = '';$i = 0;
            $width = floor(100/($this->A['cols'])).'%';
			$cnt = count($rs);
			$_cnt = ceil($cnt/$this->A['cols']) * $this->A['cols'];
			$addnum = $_cnt - $cnt;
			while($addnum){$rs[] = null;$addnum--;}
			foreach($rs as $k => $v){
				$trclass = empty($cfg['trclass']) ? ($this->mc ? '' : " class=\"txt\"") : " class=\"$cfg[trclass]\"";
				$divclass = empty($cfg['divclass']) ? '' : " class=\"$cfg[divclass]\"";
				if(!($i % $this->A['cols'])) $html .= "<tr $trclass>\n";
				$html .= "<td width=\"$width\" class=\"".($this->mc ? 'item2' : 'txtL')."\"><div $divclass style=\"width:100%;margin:0 auto;\">".(empty($v)?'':$this->m_one_row($v))."</div></td>\n";
				$i ++;
				if(!($i % $this->A['cols'])) $html .= "</tr>\n";
			}
			if($i-- % $this->A['cols']) $html .= "</tr>\n";
			echo $html;
		}else{
			foreach($rs as $k => $v){
				echo $this->m_one_row($v, $cfg);
			}
		}
	}
	
	/**
	* 处理单个文档的数据
	* $cfg[trclass]：行的css                          
	*/	
	function m_one_row($data=array(), $cfg=array()){//处理单个文档的数据
		$narr = $this->oC->fetch_one_row($data);
		if($this->A['cols']){//将所有项都通过一种定义格式封装起来，作为一个项目输出
			if(empty($this->A['mcols_style'])) $this->A['mcols_style'] = '{selectid} &nbsp;{subject}';
			return key_replace($this->A['mcols_style'],$narr);
		}else{
			$cfgs = $this->oC->cfgs;
			//完整一行的hmtl
			$trclass = empty($cfg['trclass']) ? ($this->mc ? '' : " class=\"txt\"") : " class=\"$cfg[trclass]\"";
			$re = "<tr $trclass>\n";
			foreach($narr as $key => $val){
				$re .= $this->m_view_td($val,$cfgs[$key]);
			}	
			$re .= "</tr>\n";
			return $re;
		}
	}
	function m_mcols_style($style = ''){
		$this->A['mcols_style'] = $style;
	}

	
	/**
	* 项目内容的样式应用
	*                                    
	*/
	function m_view_td($content = '',$cfg = array()){
		$width = empty($cfg['width']) ? '' : " w{$cfg['width']}";
		$class = $this->mc ? 'item' : 'txt';
		if(!empty($cfg['side']) && in_array($cfg['side'],array('L','R',))){
			$class = $this->mc ? ($cfg['side'] == 'L' ? 'item2' : 'item1') : ($cfg['side'] == 'L' ? 'txtL' : 'txtR');
		}
		$class .= empty($cfg['width']) ? '' : " w{$cfg['width']}";
		return "<td class=\"$class\">$content</td>\n";
	} 
	
	/**
	* 列表区底部
	*                                    
	*/
	function m_footer(){//列表区底部
		global $db;
		tabfooter();
		echo multi($this->acount,$this->A['rowpp'],$this->A['page'],$this->A['url'].$this->filterstr);
		foreach(array('oC','sqlall','acount','filterstr',) as $k) unset($this->$k);
	}
	
	
	/**
	* 批量操作项添加项目
	*
	* @ex  $oL->o_additem('delete',array('skip'=>1));
	*
	* @param    string    $key  项目关键字 默认为空
	* @param    array     $cfg  项目配置参数 ，可选，默认为空
	*                        可为 skip=>1  忽略此项目 用于会员中心,动态算出当前会员是否有此操作权限         
	*/
	function o_additem($key,$cfg = array()){
		if(!empty($cfg['skip'])) return;
		$re = $this->oO->additem($key,$cfg);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
	}
	
	
	/**
	* 添加批量操作推送位项目
	*
	* @param    array     $noincs  推送位id数组
	*                          
	*/
	function o_addpushs($noincs = array()){
		$caid = empty($GLOBALS['caid']) ? 0 : max(0,intval($GLOBALS['caid']));
		$na = cls_pusher::paidsarr('archives',$this->A['chid'],$caid);
		foreach($na as $k => $v) in_array($k,$noincs) || $this->o_additem($k);
	}
	
	
	function o_header($title = ''){
		tabheader($title ? $title : '批量操作');
	}
	
	
	/**
	*返回单个推送项的显示内容
	*
	* @param    string   $key  推送位项目关键字
	* @return   html    返回html字符串                      
	*/
	function o_view_one_push($key){
		$re = $this->oO->view_one_push($key);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
		return $re;
	}
	
	
	/**
	*推送项的批量展示
	*
	* @param    string   $key  推送位项目关键字
	* @return   html    返回html字符串                      
	*/
	function o_view_pushs($title = '',$incs = array(),$numpr = 5){
		//$numpr每行显示数量
		$html = '';$i = 0;
		$incs || $incs = array_keys($this->oO->cfgs);
		foreach($incs as $k){
			if($re = $this->o_view_one_push($k)){
				if($numpr && $i && !($i % $numpr)) $html .= '<br>';
				$i ++;
				$html .= $re;
			}
		}
		if($html){
			$title || $title = '选择推送位';
			trbasic($title,'',$html,'');
		}
	}
	
	/**
	*返回单个单选操作项的显示内容
	*                  
	*/
	function o_view_one_bool($key){
		$re = $this->oO->view_one_bool($key);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
		return $re;
	}
	
	/**
	*单选操作项的批量展示
	*
	* @param    string   $title  单选项标题
	* @return   html    返回html字符串                      
	*/
	function o_view_bools($title = '',$incs = array(),$numpr = 5){
		//$numpr每行显示数量
		$html = '';$i = 0;
		$incs || $incs = array_keys($this->oO->cfgs);
		foreach($incs as $k){
			if($re = $this->o_view_one_bool($k)){
				if($numpr && $i && !($i % $numpr)) $html .= '<br>';
				$i ++;
				$html .= $re;
			}
		}
		if($html){
			$title || $title = '选择操作项目';
			trbasic($title,'',$html,'');
		}
	}
	//单个单行操作项的显示，注意：不是返回数据，而是直接显示
	function o_view_one_row($key){
		$re = $this->oO->view_one_row($key);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
	}
	
	//单行批量操作项的批量显示
	function o_view_rows($incs = array()){
		$incs || $incs = array_keys($this->oO->cfgs);
		foreach($incs as $k) $this->o_view_one_row($k);
	}
	
	function o_footer($button = '',$bvalue = ''){
		tabfooter($button,$button ? ($bvalue ? $bvalue : '提交') : '');
	}
	
	function o_end_form($button = '',$bvalue = ''){
		//echo $this->mc ? '<br>' : '<br><br>';
		echo "\n<div align=\"center\" style='display:block;clear:both;'>".strbutton($button,$button ? ($bvalue ? $bvalue : '提交') : '')."</div>\n</form>\n";
	}
	
	//新增nolist参数 用于限制特殊的类系被处理，防止用户恶意在表单添加类系的情况 调用方式 sv_header(array(10,11,12)
	function sv_header($nolist=array()){
		if(empty($GLOBALS[$this->A['mfm']])){
			if(empty($GLOBALS[$this->A['ofm']])) $this->message('请选择操作项目',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
			if(empty($GLOBALS['selectid'])) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		}
		if($nolist) foreach(@$this->oO->A['coids'] as $k=>$v) if(in_array($v,$nolist)) unset($this->oO->A['coids'][$k]);
	}
	
	// msg 用于自定义操作中，获得的额外信息，如[限额已满]信息
	function sv_footer($msg=''){
		$c_upload = cls_upload::OneInstance();
		$c_upload->saveuptotal(1);
		$this->mc || adminlog('文档批量管理','文档列表管理操作');
		$url = $this->A['url']."&page={$this->A['page']}".$this->filterstr;
		if($this->A['isab'] == 2) $url = axaction(6,$url);
		if(!empty($this->oO->recnt['readd'])){ 
			if(!$this->oO->recnt['readd']['do']){ //未处理任何数据
				$msg = ($msg ? "$msg<br>" : '')."[刷新]操作未成功！刷新:".$this->oO->recnt['readd']['do']."条; ";
			}elseif(!$this->oO->recnt['readd']['skip']){ //全部成功
				$msg = ($msg ? "$msg<br>" : '')."[刷新]操作成功！刷新:".$this->oO->recnt['readd']['do']."条; ";
			}else{ //部分成功
				$msg = ($msg ? "$msg<br>" : '')."[刷新]操作部分未成功！成功:".$this->oO->recnt['readd']['do']."条; 忽略:".$this->oO->recnt['readd']['skip']."条;";
			}
		}
		if(!empty($this->oO->recnt['valid'])){ 
			if(!$this->oO->recnt['valid']['do']){ //未处理任何数据
				$msg = ($msg ? "$msg<br>" : '')."[上架]操作未成功！上架:".$this->oO->recnt['valid']['do']."条; ";
			}elseif(!$this->oO->recnt['valid']['skip']){ //全部成功
				$msg = ($msg ? "$msg<br>" : '')."[上架]操作成功！上架:".$this->oO->recnt['valid']['do']."条; ";
			}else{ //部分成功
				$msg = ($msg ? "$msg<br>" : '')."[上架]操作部分未成功！成功:".$this->oO->recnt['valid']['do']."条; 忽略:".$this->oO->recnt['valid']['skip']."条;";
			}
		}
		if(!empty($this->oO->recnt['reccids'])){ //类系限额操作
		foreach($this->oO->recnt['reccids'] as $ccid=>$v){
			if(!$v['do']){ //未处理任何数据
				$msg = ($msg ? "$msg<br>" : '')."[$v[title]]操作未成功！限额已满; ";
			}elseif(!$v['skip']){ //全部成功
				$msg = ($msg ? "$msg<br>" : '')."[$v[title]]操作成功！共:".$v['do']."条设置成功; ";
			}else{ //部分成功
				$msg = ($msg ? "$msg<br>" : '')."[$v[title]]操作部分未成功！成功:".$v['do']."条; 忽略:".$v['skip']."条;";
			}
		}}
		$this->message('批量操作完成'.(empty($msg) ? '' : "<br>$msg"),$url);
	}
	
	function sv_e_additem($key,$cfg = array()){
		$this->oE->additem($key,$cfg);
	}
	
	//列表中设置项的数据处理
	function sv_e_all(){
		$mfm = @$GLOBALS[$this->A['mfm']];
		$rs = $this->m_db_array();
		foreach($rs as $r){
			if(!empty($mfm[$r['aid']])){
				foreach($mfm[$r['aid']] as $key => $val){
					$this->oE->set_one($key,$val,$r);
				}
			}
		}
	}
	
	//处理合辑加载事务
	function sv_o_load(){
		$selectid = @$GLOBALS['selectid'];
		if(empty($selectid)) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['isab'] != 2) $this->message('不是合辑加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$arc = &$this->oO->arc;
		if(empty($arc)) $arc = new cls_arcedit;
		
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		foreach($rs as $r){
			if(!in_array($r['aid'],$selectid)) continue;
			$arc->set_aid($r['aid'],$this->A['multi_chid'] ? array() : array('chid' => $this->A['chid']));
			$arc->set_album($this->A['pid'],$this->A['arid']);
		}
		
		$this->mc || adminlog('合辑加载操作','文档列表管理操作');
		$this->message('批量操作完成',axaction(5,$this->A['url']."&page={$this->A['page']}".$this->filterstr));
	}
	//处理合辑加载事务
	function sv_o_pushload(){
		$selectid = @$GLOBALS['selectid'];
		if(empty($selectid)) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['isab'] != 3) $this->message('不是推送位加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$arc = &$this->oO->arc;
		if(empty($arc)) $arc = new cls_arcedit;
		
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		foreach($rs as $r){
			if(!in_array($r['aid'],$selectid)) continue;
			$arc->set_aid($r['aid'],$this->A['multi_chid'] ? array() : array('chid' => $this->A['chid']));
			$arc->push($this->A['paid']);
		}
		
		$this->mc || adminlog('推送位加载操作','文档列表管理操作');
		$this->message('批量操作完成',axaction(5,$this->A['url']."&page={$this->A['page']}".$this->filterstr));
	}

	function sv_o_one($key){
		$re = $this->oO->save_one($key);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
		return $re;
	}
	
	
	/**
	*列表操作项数据处理
	*                  
	*/
	function sv_o_all($cfg=array()){
		$ofm = @$GLOBALS[$this->A['ofm']];
		$selectid = @$GLOBALS['selectid'];
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			$arc = &$this->oO->arc;
			if(empty($arc)) $arc = new cls_arcedit;
			foreach($rs as $r){
				if(!in_array($r['aid'],$selectid)) continue;
				$arc->set_aid($r['aid'],$this->A['multi_chid'] ? array() : array('chid' => $this->A['chid']));
				if(!empty($ofm['delete'])){//删除则不继续其它操作
					$this->sv_o_one('delete');
					continue;
				}elseif(!empty($ofm['delbad'])){//删除(扣积分)则不继续其它操作
					$this->sv_o_one('delbad');
					continue;
				}
				foreach($ofm as $key => $v){
					$this->sv_o_one($key);
				}
				$arc->updatedb();
			}
		}
	}
/*** 以下部分脚本可直接复制到管理脚本中取代sv_all()，以深入定制，

		$ofm = ${$oL->A['ofm']};
		$rs = $oL->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			$arc = &$oL->oO->arc;
			if(empty($arc)) $arc = new cls_arcedit;
			
			foreach($rs as $r){
				if(!in_array($r['aid'],$selectid)) continue;
				$arc->set_aid($r['aid'],$oL->A['multi_chid'] ? array() : array('chid' => $oL->A['chid']));
				if(!empty($ofm['delete'])){//删除则不继续其它操作
					$oL->sv_one('delete');
					continue;
				}
				foreach($ofm as $key =>$v){
					$oL->sv_one($key);
				}
				$arc->updatedb();
			}
		}
*/	
	
	
}
