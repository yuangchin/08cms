<?php
/**
 *  membersbase.cls.php 会员列表管理的操作基类	 
 *
 *  跟文档操作基类有所区别的是：不处理多行多列及列表区设置项
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */
!defined('M_COM') && exit('No Permisson');
class cls_membersbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $mchannel = array();//当前模型
	
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
		
		
    /**
	 * 构造函数初始化设置
	 */
	function __construct($cfg = array()){
		global $db,$tblprefix;
		
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;
		$this->A['tbl'] = 'members';
		if(empty($this->A['url'])) $this->message('请添写表单提交URL');
		if(empty($this->A['mode'])) $this->A['mode'] = '';//操作模式设置：''为普通管理列表，'pushload'为推送位加载管理
		
		if(!$this->A['mode']){//普通列表模式
			if(!empty($this->A['mchid'])){
				if(!($this->mchannel = cls_cache::Read('mchannel',$this->A['mchid']))) $this->message('请指定会员类型');
				if(!in_str('mchid=',$this->A['url'])) $this->A['url'] .= "&mchid={$this->A['mchid']}"; 
			}else $this->A['mchid'] = 0;
			if(empty($this->A['backallow']) && !$this->mc) $this->A['backallow'] = 'member';//后台管理权限
		}elseif($this->A['mode'] == 'pushload'){//推送加载模式
			if(empty($this->A['paid']) || !($pusharea = cls_PushArea::Config($this->A['paid'])) || $pusharea['sourcetype'] != 'members') exit('请指定正确的推送位');
			$this->A['mchid'] = $pusharea['sourceid'];//会员模型chid
			if(!($this->mchannel = cls_cache::Read('mchannel',$this->A['mchid']))) $this->message('请指定会员类型');
			if(!in_str('paid=',$this->A['url'])) $this->A['url'] .= "&paid={$this->A['paid']}"; 
			if(empty($this->A['backallow']) && !$this->mc) $this->A['backallow'] = 'normal';//后台管理权限
		}
		
		
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
		if(empty($this->A['mfm'])) $this->A['mfm'] = 'fmdata';	//列表区中设置项的表单数据数组名
		
		//sql查询初始化，where、from、select要么完全重写，要么留空
		
		if(empty($this->A['pre'])) $this->A['pre'] = 'm.';
		if(!$this->A['mode']){//普通列表模式
			if(empty($this->A['where'])) $this->A['where'] = '';
			if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1);
			if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		}elseif($this->A['mode'] == 'pushload'){//推送加载模式
			if(empty($this->A['where'])) $this->A['where'] = cls_pusher::InitWhere($this->A['paid'],$this->A['pre']);
			if(empty($this->A['from'])) $this->A['from'] = cls_pusher::InitFrom($this->A['paid'],$this->A['pre']);
			if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		}
		
		//批量设置
		if(empty($this->A['ofm'])) $this->A['ofm'] = 'arcdeal';//操作选择项的表单数据数组名，如：选择了设置栏目，arcdeal['caid']=1
		if(empty($this->A['opre'])) $this->A['opre'] = 'arc';//设置值的参数前缀，如：设置栏目id为23时，arccaid=23
		
		$cfg = array();
		foreach($this->A as $k => $v){
			if(!in_array($k,array('url','cbsMore','MoreSet','where','from','select',))) $cfg[$k] = $v;
		}
		
		//搜索项处理的对象
		$this->oS = new cls_memsearchs($cfg);
		//列数据处理的对象
		$this->oC = new cls_memcols($cfg);
		//批量处理的对象
		$this->oO = new cls_memops($cfg);
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

	function top_head(){
		$curuser = cls_UserMain::CurUser();
		if($this->mc){
			!defined('M_COM') && exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			if($re = $curuser->NoBackFunc($this->A['backallow'])) $this->message($re);
		}
		include_once _08_INCLUDE_PATH.'mem_static.fun.php';
		echo "<title>会员管理".($this->A['mchid'] ? '-'.$this->mchannel['cname'] : '')."</title>";
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
				$str = 'membersedit';
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
		echo form_str('mems_'.md5($this->A['url']),$this->A['url']);
		tabheader_e();
		echo "<tr><td class=\"".($this->mc ? 'item2' : 'txt txtleft')."\">\n";
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
			if(in_array($this->A['mode'],array('pushload',))){//排除已加载的文档
				if($this->acount && $loadeds = $this->s_loaded_ids()){
					//处理总数
					$this->acount -= count($loadeds);
					$this->acount = max(0,intval($this->acount));
					//处理wherestr
					$wherestr .= " AND {$this->A['pre']}mid ".multi_str($loadeds,1);
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
		global $db,$tblprefix;
		if($this->A['mode'] == 'pushload'){//排除已加载的文档
			$sqlstr = "SELECT DISTINCT fromid AS ID FROM {$tblprefix}".cls_PushArea::ContentTable($this->A['paid']);
		}else return array();
		
		$re = array();
		$query = $db->query($sqlstr);
		while($r = $db->fetch_array($query)) $re[] = $r['ID'];
		return $re;
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
		echo $this->oS->htmls[$key].' &nbsp;';
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
	
	
	/**
	* 显示筛选和高级筛选按钮
	*                                  
	*/
	function s_adv_point(){
		echo strbutton('bfilter','筛选');
		echo "\n &nbsp;<input class='checkbox' type='checkbox' name='cbsMore' id='cbsMore' value='1' onclick=\"display('boxMore')\"".($this->A['cbsMore'] ? "checked = 'checked'" : " ")."/>高级选项</label>";	
		echo "\n<div id='boxMore'".(!$this->A['cbsMore'] ? " style='display:none'>" : " style='display:'>");
		$this->A['MoreSet'] = 1;
	}
	
	/**
	* 筛选表单尾部
	*                                  
	*/
	function s_footer(){
		if(empty($this->A['MoreSet'])){
			echo strbutton('bfilter','筛选');
		}else echo "</div>";//高级区结尾
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
			$tt = empty($this->A['mchid']) ? '会员列表' : ($this->mchannel['cname'].' 列表');
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
	*
	*                                     
	*/		
	function m_view_main(){
		$rs = $this->m_db_array();
		foreach($rs as $k => $v){
			echo $this->m_one_row($v);
		}
	}
	
	/**
	* 处理单个会员的数据
	*                                    
	*/		
	function m_one_row($data = array()){
		$narr = $this->oC->fetch_one_row($data);
		$cfgs = $this->oC->cfgs;
		
		//完整一行的hmtl
		$re = "<tr".($this->mc ? '' : " class=\"txt\"").">\n";
		foreach($narr as $key => $val){
			$re .= $this->m_view_td($val,$cfgs[$key]);
		}	
		$re .= "</tr>\n";
		return $re;
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
	function m_footer(){
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
		if($re === 'undefined') $this->message("批量操作项{$key}未找到处理方法");
	}
	
	
	/**
	* 添加批量操作推送位项目
	*
	* @param    array     $noincs  推送位id数组
	*                          
	*/
	function o_addpushs($noincs = array()){
		if(!$this->A['mchid']) return;
		$na = cls_pusher::paidsarr('members',$this->A['mchid']);
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
	
	//表单提交后的预检查
	function sv_header(){
		if(empty($GLOBALS[$this->A['ofm']])) $this->message('请选择操作项目',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if(empty($GLOBALS['selectid'])) $this->message('请选择会员',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
	}
	
	// msg 用于自定义操作中，获得的额外信息，如[限额已满]信息
	function sv_footer($msg=''){
		$this->mc || adminlog('会员批量管理','会员列表管理操作');
		$url = $this->A['url']."&page={$this->A['page']}".$this->filterstr;
		#if($this->A['isab'] == 2) $url = axaction(6,$url);
		$this->message('批量操作完成'.(empty($msg) ? '' : "<br>$msg"),$url);
	}
	
	//处理推送加载事务
	function sv_o_pushload(){
		$selectid = @$GLOBALS['selectid'];
		if(empty($selectid)) $this->message('请选择会员',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['mode'] != 'pushload') $this->message('不是推送位加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$auser = &$this->oO->auser;
		if(empty($auser)) $auser = new cls_userinfo;
		
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		foreach($rs as $r){
			if(!in_array($r['mid'],$selectid)) continue;
			$auser->activeuser($r['mid']);
			$auser->push($this->A['paid']);
		}
		$this->mc || adminlog('推送位加载操作','文档列表管理操作');
		$this->message('批量操作完成',axaction(5,$this->A['url']."&page={$this->A['page']}".$this->filterstr));
	}

	function sv_o_one($key){
		$re = $this->oO->save_one($key);
		if($re === 'undefined') $this->message("批量操作项{$key}未找到处理方法");
		return $re;
	}
	
	
	/**
	*列表操作项数据处理
	*                  
	*/
	function sv_o_all($cfg=array()){//删除时要同步处理uc会员
		global $enable_uc;
		$ofm = @$GLOBALS[$this->A['ofm']];
		$selectid = @$GLOBALS['selectid'];
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			$auser = &$this->oO->auser;
			if(empty($auser)) $auser = new cls_userinfo;
			$ucdels = array();
			foreach($rs as $r){
				if(!in_array($r['mid'],$selectid)) continue;
				$auser->activeuser($r['mid'],1);
				if(!empty($ofm['delete'])){//删除则不继续其它操作
                    # 同步删除WINDID会员，注：该处必须先删除服务端数据再删除本系统
                    cls_WindID_Send::getInstance()->deleteUser($r['mid']);
                    cls_ucenter::delete(array($auser->info['mname']));//同时删除uc的会员
					$this->sv_o_one('delete');
					continue;
				}
				foreach($ofm as $key => $v){
					$this->sv_o_one($key);
				}
				$auser->updatedb();
			}
		}
	}
}
