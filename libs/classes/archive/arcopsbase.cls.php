<?php
/**
 *  arcopsbase.cls.php  文档列表管理中的批量操作基类	 
 *
 *
 * @copyright			(C) 2008-2013 08cms
 * @license				http://www.08cms.com/license/
 * @lastmodify			2013-2-23
 */
!defined('M_COM') && exit('No Permisson');
class cls_arcopsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $cfgs = array();//项目配置
	public $arc	= NULL;//批量操作中的指定文档，只在数据保存时使用
	public $channel = array();//当前模型
	public $recnt = array();//统计各操作,用于提示,readd,valid
	
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;	
		if(empty($this->A['chid']) || !($this->channel = cls_channel::Config($this->A['chid']))) exit('请指定文档类型');
	}
	
	
	/**
	* 添加批量操作项目
	*
	* @ex $oL->o_additem('validperiod',array('value' => 30));;
	*
	* @param    string     $key  项目关键字 可以自己定制项目，也可以是以下值 等等
						delete：删除操作
						delbad：删除（扣积分）操作
						check：审核操作
						uncheck：解审核操作
						readd：重发布操作
						autoletter：自动首字母
						autoabstract：自动摘要
						autothumb：自动缩略图
						autokeyword：自动关键字
						static：保持格式静态
						nstatic：更新格式静态
						caid：设置栏目操作
						ccid$k 设置类系
						vieworder 排序操作
						validperiod 有效期操作 
	* @param    array      $cfg  项目配置参数 可选，默认为空 
						type：定义类型之后，会启用function type_{type}()来处理,date,bool,url,select,checkbox,input方法
						
	
	//完全定制方法：user_$key，此为优先使用的方法
	//系统内置方法：type_$key，在以下情况下调用：定义了type、未定制的类系、其它未指定方法的显示项
	*/
	
	public function additem($key,$cfg = array()){
		$this->cfgs[$key] = $cfg;
		return $this->one_item($key,0);
	}
	//返回推送项的显示html
	public function view_one_push($key){
		if(!isset($this->cfgs[$key]) || @$this->cfgs[$key]['bool'] != 2) return '';
		$re = $this->view_one($key);
		$this->del_item($key);
		return $re;		
	}	
	//返回单选项的显示html
	public function view_one_bool($key){
		if(!isset($this->cfgs[$key]) || @$this->cfgs[$key]['bool'] != 1) return '';
		$re = $this->view_one($key);
		$this->del_item($key);
		return $re;		
	}	
	
	//显示单行的操作项
	public function view_one_row($key){
		if(!isset($this->cfgs[$key]) || !empty($this->cfgs[$key]['bool'])) return false;
		$re = $this->view_one($key);//直接显示
		$this->del_item($key);
		return $re;		
	}
	
	//单项保存
	public function save_one($key){
		if(!isset($this->cfgs[$key])) return false;
		return $this->one_item($key,2);
	}
	
	protected function call_method($func,$args = array()){
		if(method_exists($this,$func)){
			return call_user_func_array(array(&$this,$func),$args);
		}else return 'undefined';
	}
	
	//操作项方法需要：0、初始化 1、显示 2、数据处理
	protected function one_item($key,$mode = 0){
		$re = $this->call_method("user_$key",array($mode));//定制方法
		if($re == 'undefined'){
			if('ccid' == substr($key,0,4)){
				$re = $this->type_ccid($key,$mode);
			}elseif('push_' == substr($key,0,5)){
				$re = $this->type_push($key,$mode);
			}
		}
		return $re;
	}
	
	//单项显示
	protected function view_one($key){
		$re = $this->one_item($key,1);
		if($re == 'undefined') $re = '';
		return $re ? $re : '';
	}
	
	//是否一个已定义的操作项目
	protected function is_item($key = ''){
		$fmdata = &$GLOBALS[$this->A['ofm']];
		return empty($fmdata[$key]) ? false : true;
	}
	
	//清除一个操作项
	protected function del_item($key){
		unset($this->cfgs[$key]);
		return false;
	}
	
	protected function input_checkbox($key = '',$title = '',$ischeck = 0,$addstr = ''){
		//ischeck：1-单选项目的checkbox，0-分行项目的checkbox
		$re = '';
		if(!$key || !$title) return $re;
		if(!$ischeck) $re .= "{$title} ";
		$re .= "<input class=\"checkbox\" type=\"checkbox\" id=\"{$this->A['ofm']}[$key]\" name=\"{$this->A['ofm']}[$key]\" value=\"1\" $addstr>";
		if($ischeck) $re .= "<label for=\"{$this->A['ofm']}[$key]\">{$title}</label> ";
		return $re;
	}
	
	//推送
	protected function type_push($key,$mode = 0){
		if(!cls_PushArea::Config($key)) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 2;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = cls_pusher::AllTitle($key,1,1);
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->push($key);
		}
	}
	
	protected function type_ccid($key,$mode = 0){
		$cotypes = cls_cache::Read('cotypes');
		if(!($coid = max(0,intval(str_replace('ccid','',$key)))) || empty($cotypes[$coid]) || !in_array($coid,$this->A['coids']) || $cotypes[$coid]['self_reg']) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			$v = $cotypes[$coid];
			if(empty($cfg['title'])) $cfg['title'] = "设置{$v['cname']}";
			isset($v['asmode']) ||  $v['asmode']='';
			isset($v['emode']) || $v['emode']='';
			$na = array('coid' => $coid,'chid' => $this->A['chid'],'max' => $v['asmode'],'emode' => $v['emode'],'evarname' => "{$this->A['opre']}{$key}date",);
			foreach($cfg as $k => $v){//以下变量可以通过$cfg传入
				if(in_array($k,array('chid','addstr','max','emode','ids','guide',))) $na[$k] = $v;
			}
			$na['addstr'] = '-取消-';
			tr_cns($this->input_checkbox($key,$cfg['title']),"{$this->A['opre']}$key",$na,1);
		}elseif($mode == 2){//数据
			if(!isset($cfg['limit'])||empty($GLOBALS[$this->A['opre'].$key])){
				$this->arc->set_ccid(@$GLOBALS["mode_".$this->A['opre'].$key],$GLOBALS[$this->A['opre'].$key],$coid,@$GLOBALS[$this->A['opre'].$key.'date']);
			}else{ //类系限额操作
				$do = 1; 
				if(!isset($this->recnt['reccids'][$coid])){
					$this->recnt['reccids'][$coid]['title'] = $cfg['title'];
					$this->recnt['reccids'][$coid]['do'] = 0; //操作数
					$this->recnt['reccids'][$coid]['skip'] = 0; //忽略数
				}
				if($this->recnt['reccids'][$coid]['do']>=$cfg['limit']){ //超过数量
					$this->recnt['reccids'][$coid]['skip']++; 
					return false;
				}
				if($do){
					$this->recnt['reccids'][$coid]['do']++;
					$this->arc->set_ccid(@$GLOBALS["mode_".$this->A['opre'].$key],$GLOBALS[$this->A['opre'].$key],$coid,@$GLOBALS[$this->A['opre'].$key.'date']);
				}
			}
		}
	}
	
	protected function user_caid($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '设置栏目';
			$na = array('coid' => 0,'chid' => $this->A['chid'],);
			foreach($cfg as $k => $v){//以下变量可以通过$cfg传入
				if(in_array($k,array('chid','addstr','ids','guide',))) $na[$k] = $v;
			}
			tr_cns($this->input_checkbox($key,$cfg['title']),"{$this->A['opre']}$key",$na);
		}elseif($mode == 2){//数据
			$this->arc->arc_caid($GLOBALS[$this->A['opre'].$key]);
		}
	}
	
	protected function user_validperiod($mode = 0){
		//value：初始输入天数
		//通常只在管理后台使用这个方法，对应会员中心的上架与下架
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '重设有效期(天)';
			if(empty($cfg['guide'])) $cfg['guide'] = '请输入有效天数(如30)，0：即时失效，-1：永久有效';
			$na = array('w' => 5);
			foreach($cfg as $k => $v){//以下变量可以通过$cfg传入
				if(in_array($k,array('guide','w',))) $na[$k] = $v;
			}
			trbasic($this->input_checkbox($key,$cfg['title']),"{$this->A['opre']}$key",empty($cfg['value']) ? '' : $cfg['value'],'text',$na);
		}elseif($mode == 2){//数据
			$days = max(-1,intval(@$GLOBALS[$this->A['opre'].$key]));
			$this->arc->setend($days);
		}
	}
	protected function user_vieworder($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '排序优先级';
			$na = array('w' => 5);
			foreach($cfg as $k => $v){//以下变量可以通过$cfg传入
				if(in_array($k,array('guide','w',))) $na[$k] = $v;
			}
			trbasic($this->input_checkbox($key,$cfg['title']),"{$this->A['opre']}$key",'','text',$na);
		}elseif($mode == 2){//数据
			$this->arc->updatefield($key,$GLOBALS[$this->A['opre'].$key]);
		}
	}
	protected function user_dpmid($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '附件下载权限';
			$na = array();
			foreach($cfg as $k => $v){//以下变量可以通过$cfg传入
				if(in_array($k,array('guide',))) $na[$k] = $v;
			}
			trbasic($this->input_checkbox($key,$cfg['title']),"{$this->A['opre']}$key",makeoption(array('-1' => '继承栏目') + pmidsarr('down'),-1),'select',$na);
		}elseif($mode == 2){//数据
			$this->arc->updatefield($key,$GLOBALS[$this->A['opre'].$key]);
		}
	}
	
	protected function user_delete($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('adel')) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除';
			return $this->input_checkbox($key,$cfg['title'],1,'onclick="deltip()"');
		}elseif($mode == 2){//数据
			$this->arc->arc_delete();
		}
	}	
	protected function user_delbad($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('adel')) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除(扣积分)'; 
			//this,'确认删除？用此方式删除要扣除发布人的相关积分。'
			//input_checkbox()考虑带个参数进去，作title；或改deltip()加个自定义提示。
			return $this->input_checkbox($key,$cfg['title'],1,"onclick=\"deltip()\"");
		}elseif($mode == 2){//数据
			$this->arc->arc_delete(1);
		}
	}
	
	protected function user_check($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('acheck')) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '审核';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->arc_check(1);
		}
	}	
	protected function user_uncheck($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('acheck')) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '解审';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->is_item('check')) return false;//不跟check同时执行
			$this->arc->arc_check(0);
		}
	}
	// limit:限额(条),time时间间隔为(分钟),
	protected function user_readd($mode = 0){
		global $timestamp; 
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '刷新';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if(!isset($cfg['limit'])){
				$this->arc->readd();
			}else{
				$do = 1; 
				if(!isset($cfg['time'])) $cfg['time'] = 0;
				if(!isset($this->recnt['readd'])){
					$this->recnt['readd']['do'] = 0; //操作数
					$this->recnt['readd']['skip'] = 0; //忽略数
				}
				if($this->recnt['readd']['do']>=$cfg['limit']){ //超过数量
					$this->recnt['readd']['skip']++; 
					return false;
				}elseif($cfg['time']&&($timestamp-$this->arc->archive['refreshdate']<$cfg['time']*60)){
					$this->recnt['readd']['skip']++;
					return false; //$do = 0; //不处理
				}
				if($do){
					$this->recnt['readd']['do']++;
					$this->arc->readd();
					//更新会员表存放已刷新次数的字段	refreshes
					isset($cfg['fieldname']) &&	$this->arc->update_refreshes($cfg['fieldname']);
				}
			}
		}
	}
	// limit:限额(条),days上架期限(天),	
	protected function user_valid($mode = 0){
		global $timestamp; 
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '上架';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$days = empty($cfg['days']) ? -1 : max(1,intval($cfg['days']));
			if(!isset($cfg['limit'])){
				$this->arc->setend($days);
			}else{
				$do = 1; 
				if(!isset($this->recnt['valid'])){
					$this->recnt['valid']['do'] = 0; //操作数
					$this->recnt['valid']['skip'] = 0; //忽略数
				}
				if($this->recnt['valid']['do']>=$cfg['limit']){ //超过数量
					$this->recnt['valid']['skip']++; 
					return false; 
				}elseif(empty($this->arc->archive['enddate']) || ($timestamp<$this->arc->archive['enddate'])){
					$this->recnt['valid']['skip']++;
					return false; //$do = 0; //不处理
				}
				if($do){
					$this->recnt['valid']['do']++;
					$this->arc->setend($days);
				}
			}
		}
	}	
	protected function user_unvalid($mode = 0){
		$key = substr(__FUNCTION__,5);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '下架';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->is_item('valid')) return;//不跟valid同时执行
			$this->arc->setend(0);
		}
	}
	
	protected function user_incheck($mode = 0){
		$key = substr(__FUNCTION__,5);
		if($this->A['isab'] != 1) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '辑内有效';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->incheck(1,$this->A['arid'],$this->A['pid']);
		}
	}	
	protected function user_unincheck($mode = 0){
		$key = substr(__FUNCTION__,5);
		if($this->A['isab'] != 1) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '辑内无效';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->is_item('incheck')) return;//不跟check同时执行
			$this->arc->incheck(0,$this->A['arid'],$this->A['pid']);
		}
	}
		
	protected function user_inclear($mode = 0){
		$key = substr(__FUNCTION__,5);
		if($this->A['isab'] != 1) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '清除';
			return $this->input_checkbox($key,$cfg['title'],1,"onclick=\"deltip()\" title=\"您所选条目将取消关联，同类此操不再提示！继续？\"");	
		}elseif($mode == 2){//数据
			$this->arc->exit_album($this->A['arid'],$this->A['pid']);
		}
	}
	protected function user_autoletter($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(empty($this->channel['autoletter'])) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '自动首字母';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->autoletter();
		}
	}	
	protected function user_static($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$enablestatic = cls_env::mconfig('enablestatic');
			$splitbls = cls_cache::Read('splitbls');
			$spstatic = $splitbls[$this->channel['stid']]['nostatic']; //$this->channel['stid']; //主表分表ID
			$canstatic = $enablestatic && empty($spstatic);
			$cfg['bool'] = $canstatic; //总设置:开启静态 且 分表管理>>关闭静态>>未勾选
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '保持格式静态';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->arc_static(1);
		}
	}	
	protected function user_nstatic($mode = 0){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$enablestatic = cls_env::mconfig('enablestatic');
			$splitbls = cls_cache::Read('splitbls');
			$spstatic = $splitbls[$this->channel['stid']]['nostatic']; //$this->channel['stid']; //主表分表ID
			$canstatic = $enablestatic && empty($spstatic);
			$cfg['bool'] = $canstatic; //总设置:开启静态 且 分表管理>>关闭静态>>未勾选
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '更新格式静态';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->is_item('static')) return;//不跟static同时执行
			$this->arc->arc_static(0);
		}
	}	
	protected function user_autoabstract($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(empty($this->channel['autoabstract'])) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '自动摘要';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->autoabstract();
		}
	}	
	protected function user_autothumb($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(empty($this->channel['autothumb'])) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '自动缩略图';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$c_upload = cls_upload::OneInstance();
			$this->arc->autothumb();
			if(!empty($c_upload->ufids)){
				$c_upload->closure(1, $this->arc->aid);
				$c_upload->ufids = array();
			}
		}
	}	
	protected function user_autokeyword($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(empty($this->channel['autokeyword'])) return $this->del_item($key);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '自动关键词';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->arc->autokeyword();
		}
	}
}
