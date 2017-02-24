<?php
/**
 * 交互列表管理的操作基类	 
 */
!defined('M_COM') && exit('No Permisson');
class cls_culistbase extends cls_cubasic{

	//搜索有关
	public $oS	= NULL;//搜索项处理的对象
	public $sqlall = '';//完整sql字串
	public $acount = 0;//信息总数的统计
	public $filterstr = '';//筛选参数在url中的传递字串
	
	public $oC	= NULL;//列数据处理的对象
	public $oO	= NULL;//批量项目处理的对象
	public $rs	= array();//在数据储存端暂存所选列表数据资料
	
	public $tomid = 'tomid'; //交互表中,接收者会员id的字段,不是这个,请传递过来
			
    /**
	 * 构造函数初始化设置
	 */
	
	// list下，pchid: 用于列表条件
	function __construct($cfg = array()){
		parent::__construct($cfg);
		isset($cfg['tomid']) && $this->tomid = $cfg['tomid'];
		global $db,$tblprefix;
		$curuser = cls_UserMain::CurUser();
		//if(empty($this->A['mode'])) $this->A['mode'] = '';//操作模式设置：''为普通管理列表，'pushload'为推送位加载管理
		
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
		
		//sql查询初始化，where、from、select可留空，可附加一部分，where要以“[ AND ]开始”
		$_select = "SELECT cu.*,cu.createdate AS cucreate,cu.mid as cu_mid,cu.mname as cu_mname";	
		// 注意:与文档连表时,文档的createdate会覆盖交互的字段, 处理:cu.createdate AS cucreate
		//      与会员连表时,会员的mid,mname会覆盖交互的字段, 处理:cu.mid as cu_mid,cu.mname as cu_mname 
		$_from = "FROM {$tblprefix}{$this->cucfgs['tbl']} cu";
		if($this->ptype=='m'){ //管理员 管理 给会员的交互
			$selectsql = "$_select ".murl_fields('m.');
			$wheresql = " WHERE m.mchid={$this->pchid} ";
			$fromsql = " $_from INNER JOIN {$tblprefix}members m ON m.mid=cu.{$this->tomid}";
		}elseif($this->ptype=='a'){ // 管理员 管理 给文档的交互
			$selectsql = "$_select ".aurl_fields('a.');
			$wheresql = ' WHERE 1=1 '; // $this->cucfgs['tbl']
			if(!empty($this->A['caid']) && $cnsql = cnsql(0,sonbycoid($this->A['caid']),'a.')) $wheresql .= " AND $cnsql";
			$fromsql = " $_from INNER JOIN {$tblprefix}".atbl($this->pchid)." a ON a.aid=cu.aid";
		}elseif($this->ptype=='e'){ //网站留言,类目交互
			$selectsql = $_select;
			$wheresql = " WHERE 1=1 ";
			$fromsql = $_from;
		}elseif($this->ptype=='u'){ //自定以where、from、select
			$selectsql = $cfg['select'];
			$wheresql = " WHERE 1=1 ".$cfg['where'];
			$fromsql = $cfg['from'];
		}
		$_arr = array('select'=>$selectsql,'where'=>$wheresql,'from'=>$fromsql);
		if($this->ptype == 'u'){
			foreach($_arr as $k=>$v){
				$this->A[$k] = $v;	
			}		
		}else{
			foreach($_arr as $k=>$v){
				$this->A[$k] = $v.' '.$this->A[$k];	
			}
		}
		
		//批量设置
		if(empty($this->A['ofm'])) $this->A['ofm'] = 'arcdeal';//操作选择项的表单数据数组名，如：选择了设置栏目，arcdeal['caid']=1
		if(empty($this->A['opre'])) $this->A['opre'] = 'arc';//设置值的参数前缀，如：设置栏目id为23时，arccaid=23
		
		$cfg = array();
		foreach($this->A as $k => $v){
			if(!in_array($k,array('url','cbsMore','MoreSet','where','from','select',))) $cfg[$k] = $v;
		}
		
		//搜索项处理的对象
		$this->oS = new cls_cuschs($cfg);
		//列数据处理的对象
		$this->oC = new cls_cucols($cfg);
		//批量处理的对象
		$this->oO = new cls_cuops($cfg);
    }
	
	/**
	* 筛选添加项目                                    
	*/
	
	function s_additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		$this->oS->additem($key,$cfg);
	}
	
	/**
	* 筛选表单头部
	*/
	function s_header(){
		echo form_str('cus_'.md5($this->A['url']),$this->A['url']);
		tabheader_e();
		echo "<tr><td class=\"".($this->mc ? 'item2' : 'txt txtleft')."\">\n";
		trhidden('page',$this->A['page']);
	}
	
	/**
	* 筛选字符串sql语句的组装和查询处理
	*/
	function s_deal_str(){//将oS中处理结果转到当前对象中
		$this->s_sqlstr();
		$this->s_filterstr();
	}
	
	/**
	* sql处理
	*/
	function s_sqlstr(){
		global $db,$tblprefix;
		if(!empty($this->oS->wheres)){ 
			foreach($this->oS->wheres as $k => $v) $this->A['where'] .= " AND $v";//搜索附加产生的where因素
		}
		if(!$this->acount = $db->result_one('SELECT COUNT(*) '.$this->A['from'].$this->A['where'])){
			$this->acount = 0;
		}
		$this->sqlall = $this->A['select'].$this->A['from'].$this->A['where'].' ORDER BY '. $this->oS->orderby;
	}

	/**
	* 筛选字符串的url组装
	*/
	function s_filterstr(){
		foreach($this->oS->filters as $k => $v){
			$this->filterstr .= "&$k=".(is_numeric($v) ? $v : rawurlencode($v));
		}
	}
	
	
	/**
	* 列表单个项目的显示
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
	* @param    array    $incs  项目关键字数组 默认为空                                 
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
	*/
	function s_adv_point(){
		echo strbutton('bfilter','筛选');
		echo "\n &nbsp;<input class='checkbox' type='checkbox' name='cbsMore' id='cbsMore' value='1' onclick=\"display('boxMore')\"".($this->A['cbsMore'] ? "checked = 'checked'" : " ")."/>高级选项</label>";	
		echo "\n<div id='boxMore'".(!$this->A['cbsMore'] ? " style='display:none'>" : " style='display:'>");
		$this->A['MoreSet'] = 1;
	}
	
	/**
	* 筛选表单尾部                                
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
	* @param    string    $title    列表标题 默认为内置的title
	* @param    int       $pid      附加交互对象连接,pid为交互对象ID（如评论的新闻标题ID）
	* @param    string    $exstr    附加信息  
	$ Demo : $oL->m_header('', $aid, $aid ? " &nbsp; &nbsp; <a href='?entry=extend&extend=$extend='>全部评论&gt;&gt;</a>" : '');                          
	*/
	function m_header($title = '', $pid=0, $exstr = ''){
		$title = $title ? $title : $this->cucfgs['cname'].' 列表';
		if(!empty($pid)){
			$title = "[".$this->getPLink($pid, array())."] $title";	
		}
		if(!empty($exstr)){
			$title .= $exstr; 
		}
		tabheader($title,'','',20);
	}
	
	
	/**
	* 列表添加项目
	* @ex  s_additem('subject',array('title'=>'标题','hidden'=>1,));
	* @param    string    $key  项目关键字 默认为空
	* @param    array     $cfg  项目配置参数 ，可选，默认为空
	*					可为 title项目名称, hidden将某个搜索值固定下来, url跳转链接等等                                  
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
			while($r = $db->fetch_array($query)){ 
				if($this->fhidden){ //要做隐藏处理的字段，特殊处理
					foreach($this->fhidden as $_k){
						$r[$_k]	= cls_string::SubReplace($r[$_k]);
					}
				}
				$re[] = $r;
			}
			$this->rs = $re;
		}
		return $this->rs;
	}
	
	/**
	* 处理列表区数据及显示html                                     
	*/		
	function m_view_main(){
		$rs = $this->m_db_array();
		foreach($rs as $k => $v){
			echo $this->m_one_row($v);
		}
	}
	
	/**
	* 处理单个交互的数据                                   
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
	*/
	function m_footer(){
		global $db;
		tabfooter();
		echo multi($this->acount,$this->A['rowpp'],$this->A['page'],$this->A['url'].$this->filterstr);
		foreach(array('oC','sqlall','acount','filterstr',) as $k) unset($this->$k);
	}
	
	/**
	* 批量操作项添加项目
	* @ex  $oL->o_additem('delete',array('skip'=>1));
	* @param    string    $key  项目关键字 默认为空
	* @param    array     $cfg  项目配置参数 ，可选，默认为空
	*                        可为 skip=>1  忽略此项目 用于交互中心,动态算出当前交互是否有此操作权限         
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
		if(!$this->A['cuid']) return;
		$na = cls_pusher::paidsarr('commus',$this->A['cuid']);
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
	*返回单个单选操作项的显示内容                 
	*/
	function o_view_one_bool($key){
		$re = $this->oO->view_one_bool($key);
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
	*单选操作项的批量展示
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
		if(empty($GLOBALS['selectid'])) $this->message('请选择交互',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
	}
	
	// msg 用于自定义操作中，获得的额外信息，如[限额已满]信息
	function sv_footer($msg=''){
		$this->mc || adminlog('交互批量管理','交互列表管理操作');
		$url = $this->A['url']."&page={$this->A['page']}".$this->filterstr;
		$exmsg = $msg;
		if(!empty($this->oO->cnt_msgs)){
			foreach($this->oO->cnt_msgs as $imsg){
				$exmsg .= '<br>'.$imsg;
			}
		}
		$this->message('批量操作完成'.$exmsg,$url);
	}
	
	//处理推送加载事务
	function sv_o_pushload(){
		$selectid = @$GLOBALS['selectid'];
		if(empty($selectid)) $this->message('请选择交互记录',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['mode'] != 'pushload') $this->message('不是推送位加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$auser = &$this->oO->auser;
		if(empty($auser)) $auser = new cls_userinfo;
		
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		foreach($rs as $r){
			if(!in_array($r['cid'],$selectid)) continue;
			$auser->activeuser($r['cid']);
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
	*/
	function sv_o_all($cfg=array()){
		$ofm = @$GLOBALS[$this->A['ofm']];
		$selectid = @$GLOBALS['selectid'];
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			$actcu = &$this->oO->actcu;
			foreach($rs as $r){ 
				if(!in_array($r['cid'],$selectid)) continue;
				$actcu = $this->getRow($r['cid']);
				if(!empty($ofm['delete'])){//删除则不继续其它操作
					$this->sv_o_one('delete');
					continue;
				}
				foreach($ofm as $key => $v){ 
					$this->sv_o_one($key);
				}
				//$auser->updatedb();
			}
		}
	}
}
