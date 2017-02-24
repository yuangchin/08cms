<?php
/*
** 推送列表操作的管理界面类
** 
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushsbase{
	
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放
	public $area = array();//当前推送位设置
	
	//搜索有关
	public $oS	= NULL;//搜索项处理的对象
	public $sqlall = '';//完整sql字串
	public $sqlnum = '';//数量统计sql
	public $filterstr = '';//筛选参数在url中的传递字串
	
	//内容列表
	public $oC	= NULL;//列数据处理的对象
	
	//批量操作
	public $oO	= NULL;//批量项目处理的对象
	public $rs	= array();//在数据储存端暂存所选列表数据资料
	
	//设置项操作
	public $oE	= NULL;//列表中设置项处理的对象
	
    function __construct($cfg = array()){
		global $db,$tblprefix;
		
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;
		
		//初始化处理
		if(empty($this->A['paid']) || !($this->area = cls_PushArea::Config($this->A['paid']))) $this->message('请指定推送位');
		if(empty($this->A['url'])) $this->message('请添写表单提交URL');
		$this->A['tbl'] = cls_PushArea::ContentTable($this->A['paid']);
		if(empty($this->A['backallow']) && !$this->mc) $this->A['backallow'] = 'normal';
		if(!in_str('paid=',$this->A['url'])) $this->A['url'] .= "&paid={$this->A['paid']}"; 
		
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
		
		
		//sql查询初始化，where、from、select要么完全重写，要么留空
		if(empty($this->A['pre'])) $this->A['pre'] = 'p.';
		if(empty($this->A['where'])) $this->A['where'] = '';
		if(empty($this->A['from'])) $this->A['from'] = "{$tblprefix}{$this->A['tbl']} ".substr($this->A['pre'],0,-1);
		if(empty($this->A['select'])) $this->A['select'] = "{$this->A['pre']}*";
		
		//批量设置
		if(empty($this->A['ofm'])) $this->A['ofm'] = 'ofm';//操作选择项的表单数据数组名，如：选择了设置栏目，ofm['caid']=1
		if(empty($this->A['opre'])) $this->A['opre'] = 'opre_';//设置值的参数前缀，如：设置栏目id为23时，opre_caid=23
		
		$cfg = array();
		foreach($this->A as $k => $v){
			if(!in_array($k,array('url','cols','cbsMore','MoreSet',))) $cfg[$k] = $v;
		}
		
		//搜索项处理的对象
		$this->oS = new cls_pushsearchs($cfg);
		//列数据处理的对象
		$this->oC = new cls_pushcols($cfg);
		//列表中设置项处理的对象
		$this->oE = new cls_pushsets($cfg);
		//批量设置
		$this->oO = new cls_pushops($cfg);
    }
	
	function setvar($key,$var){
		$this->$key = $var;	
	}
	
	function message($str = '',$url = ''){
		call_user_func('cls_message::show',$str,$url);
	}

	function top_head(){
		if($this->mc){
			!defined('M_COM') && exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			$curuser = cls_UserMain::CurUser();
			if($re = $curuser->NoBackFunc($this->A['backallow'])) $this->message($re);
		}
		echo "<title>内容管理 - {$this->area['cname']}</title>";
	}
	
	//管理后台：参数格式($str,$type)，$type默认为0时$str为帮助缓存标记，1表示$str为文本内容
	//会员中心：参数格式($str,$type)，$str可以输入会员中心帮助标识或直接的文本内容，$type默认为0直接显示内容，tip-可隐藏的提示框，fix-固定的提示框
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
	
	//hidden以隐藏域将某个搜索值固定下来,类似url固定参数
	//title标题
	function s_additem($key = '',$cfg = array()){//可追加$key、$cfg之外的传参
		$this->oS->additem($key,$cfg);
	}
	
	function s_header(){
		echo form_str('arcs_'.md5($this->A['url']),$this->A['url']);
		tabheader_e();
		echo "<tr><td class=\"".($this->mc ? 'item2' : 'txt txtleft')."\">\n";
		trhidden('page',$this->A['page']);
	}
	
	function s_deal_str(){//将oS中处理结果转到当前对象中
		$this->s_sqlstr();
		$this->s_filterstr();
	}
	
	//处理sql
	function s_sqlstr(){
		$wherestr = empty($this->A['where']) ? '' : " AND {$this->A['where']}";
		foreach($this->oS->wheres as $k => $v) $wherestr .= " AND $v";
		if($wherestr) $wherestr = " WHERE ".substr($wherestr,5);
		$this->sqlall = "SELECT ".$this->A['select'].' FROM '.$this->A['from'].$wherestr.(empty($this->area['copyspace']) ? '' : ' GROUP BY copyid').' ORDER BY '. $this->oS->orderby;
		$this->sqlnum = 'SELECT '.(empty($this->area['copyspace']) ? 'COUNT(*)' : 'COUNT(DISTINCT copyid)').' FROM '.$this->A['from'].$wherestr;
	}
	
	//筛选参数在url中的传递
	function s_filterstr(){
		foreach($this->oS->filters as $k => $v){
			$this->filterstr .= "&$k=".(is_numeric($v) ? $v : rawurlencode($v));
		}
	}
	
	function s_view_one($key = ''){
		if(empty($key) || empty($this->oS->htmls[$key])) return;
		echo $this->oS->htmls[$key].' &nbsp;';
		unset($this->oS->htmls[$key]);
	}
	
	function s_view_array($incs = array()){
		if($incs){
			foreach($incs as $k) $this->s_view_one($k);
		}else{
			foreach($this->oS->htmls as $k => $v) $this->s_view_one($k);
		}
	}
	function s_adv_point(){
		echo strbutton('bfilter','筛选');
		echo "\n &nbsp;<input class='checkbox' type='checkbox' name='cbsMore' id='cbsMore' value='1' onclick=\"display('boxMore')\"".($this->A['cbsMore'] ? "checked = 'checked'" : " ")."/>高级选项</label>";	
		echo "\n<div id='boxMore'".(!$this->A['cbsMore'] ? " style='display:none'>" : " style='display:'>");
		$this->A['MoreSet'] = 1;
	}
	
	function s_footer(){
		if(empty($this->A['MoreSet'])){
			echo strbutton('bfilter','筛选');
		}else echo "</div>";//高级区结尾
		tabfooter();
		unset($this->oS);
	}

	function m_header($title = '',$addmode = 0){//$addmode=1时在默认的标题后添加$title内容，否则$title替换默认title
		if(!$title || $addmode){
			$pcfg = cls_PushAreaBase::Config($this->A['paid']);
			if($pcfg['sourcetype'] == 'catalogs'){ //类目推送, 默认跳转到类目管理页
				if(empty($pcfg['sourceid'])){
					$_aurl = "?entry=catalogs&action=catalogedit";
					$_titile = "栏目管理";
				}else{
					$_aurl = "?entry=coclass&action=coclassedit&coid={$pcfg['sourceid']}";
					$_cotypes = cls_cache::Read('cotypes'); 
					$_titile = "[{$_cotypes[$pcfg['sourceid']]['cname']}]管理";
				}
			}else{
				$_aurl = "?entry=extend&extend=push&paid={$this->A['paid']}";
				$_titile = empty($pcfg['forbid_useradd']) ? "手动添加" : ''; //设置为:[禁止手动添加]的,不出这个连接
			}
			$tt = "{$this->area['cname']}-推送管理";
			$_titile && $tt .= " &nbsp;>><a href=\"$_aurl\" onclick=\"return floatwin('open_push_{$this->A['paid']}',this)\">$_titile</a>";
		}
		$title = $addmode ? ($tt.$title) : ($title ? $title : $tt);
		tabheader($title,'','',20);
	}
	function m_additem($key = '',$cfg = array()){//增加列表中的列
		if(!$key) $this->message('请列项目的key不能为空');
		if($this->oC->additem($key,$cfg) == 'undefined') $this->message("列项目{$key}未找到处理方法");
	}
	
	function m_addgroup($mainstyle,$topstyle = ''){//增加分组
		if(!$mainstyle) return;
		$this->oC->addgroup($mainstyle,$topstyle);
	}
	
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
				$re[] = cls_pusher::ViewOneInfo($r);
			}
			$this->rs = $re;
		}
		return $this->rs;
	}
			
	//处理列表区数据及显示html
	function m_view_main(){
		$rs = $this->m_db_array();
		if($this->A['cols']){//将所有项都通过一种定义格式封装起来，作为一个项目输出
			$html = '';$i = 0;
			foreach($rs as $k => $v){
				if(!($i % $this->A['cols'])) $html .= "<tr".($this->mc ? '' : " class=\"txt\"").">\n";
				$html .= "<td class=\"".($this->mc ? 'item2' : 'txtL')."\">".$this->m_one_row($v)."</td>\n";
				$i ++;
				if(!($i % $this->A['cols'])) $html .= "</tr>\n";
			}
			if($i-- % $this->A['cols']) $html .= "</tr>\n";
			echo $html;
		}else{
			foreach($rs as $k => $v){
				echo $this->m_one_row($v);
			}
		}
	}
	
	function m_one_row($data = array()){//处理单个文档的数据
		$narr = $this->oC->fetch_one_row($data);
		if($this->A['cols']){//将所有项都通过一种定义格式封装起来，作为一个项目输出
			if(empty($this->A['mcols_style'])) $this->A['mcols_style'] = '{selectid} &nbsp;{subject}';
			return key_replace($this->A['mcols_style'],$narr);
		}else{
			$cfgs = $this->oC->cfgs;
			
			//完整一行的hmtl
			$re = "<tr".($this->mc ? '' : " class=\"txt\"").">\n";
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

	//每格内容的样式应用
	function m_view_td($content = '',$cfg = array()){
		$width = empty($cfg['width']) ? '' : " w{$cfg['width']}";
		$class = $this->mc ? 'item' : 'txt';
		if(!empty($cfg['side']) && in_array($cfg['side'],array('L','R',))){
			$class = $this->mc ? ($cfg['side'] == 'L' ? 'item2' : 'item1') : ($cfg['side'] == 'L' ? 'txtL' : 'txtR');
		}
		$class .= empty($cfg['width']) ? '' : " w{$cfg['width']}";
		return "<td class=\"$class\">$content</td>\n";
	} 
	
	function m_footer(){//列表区底部
		global $db;
		tabfooter();
		$counts = $db->result_one($this->sqlnum);
		$multi = multi($counts,$this->A['rowpp'],$this->A['page'],$this->A['url'].$this->filterstr);
		echo $multi;
		foreach(array('oC','sqlall','sqlnum','filterstr',) as $k) unset($this->$k);
	}
	
	function o_additem($key,$cfg = array()){
		$re = $this->oO->additem($key,$cfg);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
	}
	
	function o_header($title = '',$addmode = 0){//$addmode=1时在默认的标题后添加$title内容，否则$title替换默认title
		if(!$title || $addmode){
			$tt = "{$this->area['cname']}-批量操作";
		}
		$title = $addmode ? ($tt.$title) : ($title ? $title : $tt);
		tabheader($title,'','',10);
	}
	
	
	//返回单个单选操作项的显示内容
	function o_view_one_bool($key){
		$re = $this->oO->view_one_bool($key);
		if($re == 'undefined') $this->message("批量操作项{$key}未找到处理方法");
		return $re;
	}
	//单选操作项的批量展示
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
		$addstr = ' &nbsp;'.OneCheckBox('needorefresh','同时更新排序',1);
		tabfooter($button,$button ? ($bvalue ? $bvalue : '提交') : '',$addstr);
	}
	
	function o_end_form($button = '',$bvalue = ''){
		//echo $this->mc ? '<br>' : '<br><br>';
		echo "\n<div align=\"center\" style='display:block;clear:both;'>".strbutton($button,$button ? ($bvalue ? $bvalue : '提交') : '')."</div>\n</form>\n";
	}
	
	function sv_header(){
		if(empty($GLOBALS[$this->A['mfm']])){
			if(empty($GLOBALS[$this->A['ofm']])) $this->message('请选择操作项目',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
			if(empty($GLOBALS['selectid'])) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		}
	}
	
	// msg 用于自定义操作中，获得的额外信息，如[限额已满]信息
	function sv_footer($msg=''){
		$c_upload = cls_upload::OneInstance();
		$c_upload->saveuptotal(1);
		$this->mc || adminlog('推送批量管理','推送列表管理操作');
		$url = $this->A['url']."&page={$this->A['page']}".$this->filterstr;
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
			if(!empty($mfm[$r['pushid']])){
				foreach($mfm[$r['pushid']] as $key => $val){
					$this->oE->set_one($key,$val,$r);
				}
			}
		}
	}
	
	//处理合辑加载事务
	function sv_o_load(){
		if(empty($GLOBALS['selectid'])) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['isab'] != 2) $this->message('不是合辑加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$arc = &$this->oO->arc;
		if(empty($arc)) $arc = new cls_arcedit;
		foreach($GLOBALS['selectid'] as $aid){
			$arc->set_aid($aid,$this->A['multi_chid'] ? array() : array('chid' => $this->A['chid']));
			$arc->set_album($this->A['pid'],$this->A['arid']);
		}
		
		$this->mc || adminlog('合辑加载操作','文档列表管理操作');
		$this->message('批量操作完成',axaction(5,$this->A['url']."&page={$this->A['page']}".$this->filterstr));
	}
	//处理合辑加载事务
	function sv_o_pushload(){
		if(empty($GLOBALS['selectid'])) $this->message('请选择文档',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		if($this->A['isab'] != 3) $this->message('不是推送位加载操作',$this->A['url']."&page={$this->A['page']}".$this->filterstr);
		
		$arc = &$this->oO->arc;
		if(empty($arc)) $arc = new cls_arcedit;
		foreach($GLOBALS['selectid'] as $aid){
			$arc->set_aid($aid,$this->A['multi_chid'] ? array() : array('chid' => $this->A['chid']));
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
	
	function sv_o_all(){
		$ofm = @$GLOBALS[$this->A['ofm']];
		$selectid = @$GLOBALS['selectid'];
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			foreach($rs as $r){
				if(!in_array($r['pushid'],$selectid)) continue;
				$this->oO->push = &$r;
				
				if(!empty($ofm['delete'])){//删除则不继续其它操作
					$this->sv_o_one('delete');
					continue;
				}
				
				foreach($ofm as $key => $v){
					$this->sv_o_one($key);
				}
				cls_pusher::updatedb($r['pushid']);//单条记录更新
			}
		}
		
		if(@$GLOBALS['needorefresh']){//选中了同时更新排序
			cls_pusher::ORefreshPaid($this->A['paid']);
		}
	}
    
    /**
     * 获取推送位类型（select数据格式使用）
     * 
     * @return array $selectDatas 返回select数据格式使用推送位类型
     * @since  nv50
     */
    public static function getPushTypesInSelect()
    {
        $selectDatas = array(0 => '请先选择');
        $push_types = cls_cache::Read('pushtypes');
        foreach ( $push_types as $push_type ) 
        {
            $selectDatas[$push_type['ptid']] = $push_type['title'];
        }
        
        return $selectDatas;
    }
	
    
    /**
     * 从推送位类型ID获取推送位列表（select数据格式使用）
     * 
     * @return array $selectDatas 返回select数据格式使用推送位类型
     * @since  nv50
     */
    public static function getPushAreasInSelect( $ptid )
    {
        $ptid = (int) $ptid;
        $selectDatas = array(0 => '请先选择');
        $pushareas = cls_PushArea::Config();
        
        foreach ( $pushareas as $k => $v ) 
        {
            if ( $v['ptid'] == $ptid )
            {
                $selectDatas[$k] = $v['cname'];
            }            
        }
        return $selectDatas;
    }
}
