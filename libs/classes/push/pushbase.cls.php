<?php
/*
** 推送信息手动添加或编辑的操作类
** sv方法中设置return_error表示出错时返回error，而不是用message跳出
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushbase{
	protected $mc = 0;//会员中心
	public $isadd = 0;//添加模式
	public $pushid = 0;//推送信息id
	public $paid = 0;//推送位id
	public $fmdata = 'fmdata';//form中的数组名称//允许自行设置
	public $predata = array();//预定资料数组
	public $area = array();//推送位配置
	public $fields = array();//推送字段
	public $fields_did = array();//暂存已处理过的字段
	
    function __construct(){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		
		$this->paid = cls_env::GetG('paid');
		$this->paid = cls_PushArea::InitID($this->paid);
		$this->pushid = cls_env::GetG('pushid');
		$this->pushid = empty($this->pushid) ? 0 : max(0,intval($this->pushid));
		
		$this->isadd = $this->pushid ? 0 : 1;
		$this->area = cls_PushArea::Config($this->paid);//推送位配置
		$this->fields = cls_PushArea::Field($this->paid);//推送字段
		if($this->area){
			$this->predata = cls_pusher::oneinfo($this->pushid,$this->paid,true);
		}
    }
	
	//$return_error为1时，不跳出，返回错误信息
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
		if($this->mc){
			if(!defined('M_COM')) exit('No Permission');
		}else{
			if(!defined('M_COM') || !defined('M_ADMIN')) exit('No Permission');
			aheader();
			$curuser = cls_UserMain::CurUser();
			if($re = $curuser->NoBackFunc('normal')) $this->message($re);
		}
		echo "<title>推送".($this->isadd ? '添加' : '详情')."</title>";
	}
	function pre_check(){
		if(!$this->paid || !$this->area) $this->message('请指定正确的推送位');
		if($this->pushid && !$this->predata) $this->message('请指定正确的推送信息');
		//权限分析??????????????????
		return;
	}	
	
	function fm_header($title = '',$url = ''){
		$title || $title = $this->area['cname'].'&nbsp; -&nbsp; 详情';
		if($url){
			if(!in_str('paid=',$url)) $url .= "&paid={$this->paid}"; 
			if($this->pushid){//添加时需要传入pushid
				if(!in_str('pushid=',$url)) $url .= "&pushid={$this->pushid}"; 
			}
			tabheader($title,'pushdetial',$url,2,1,1);
		}else{
			tabheader($title);
		}
	}
	function fm_footer($button = '',$bvalue = ''){
		tabfooter($button,$button ? ($bvalue ? $bvalue : ($this->isadd ? '添加' : '提交')) : '');
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
		$subject_table || $subject_table = cls_pusher::tbl($this->paid);
	}	
	
	//展示多个属性项
	//可选项目array('startdate','enddate',)
	function fm_params($incs = array()){
		if(empty($incs)) $incs = array('startdate','enddate','norefresh',);
		foreach($incs as $k) $this->fm_param($k);
	}
	
	//展示指定的属性项，可选项目array('startdate','enddate',)
	function fm_param($ename){
		global $timestamp;
		switch($ename){
			case 'startdate':
				trbasic('生效日期',"{$this->fmdata}[startdate]",empty($this->predata['startdate']) ? '' : date('Y-m-d',$this->predata['startdate']),'calendar');
			break;
			case 'enddate':
				trbasic('截止日期',"{$this->fmdata}[enddate]",empty($this->predata['enddate']) ? '' : date('Y-m-d',$this->predata['enddate']),'calendar',array('guide'=>'为空则表示长期有效'));
			break;
			case 'norefresh':
				trbasic('后续更新设置','',OneCheckBox("{$this->fmdata}[norefresh]",'以后不再从来源内容自动更新',empty($this->predata['norefresh']) ? 0 : 1),'');
			break;
		}
	}
	
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
				$str = $this->isadd ? 'pushadd' : 'pushdetail';
				if(is_file(M_ROOT."dynamic/aguides/{$str}_{$this->paid}.php")) $str .= "_{$this->paid}";
				$type = 0;
			}
			a_guide($str,$type);
		}
	}
	
	//分析当前会员的发布权限及管理权限，在fm_pre_cns之后执行
	function sv_allow($return_error = 0){
		//$curuser = cls_UserMain::CurUser();
		//if(!$this->mc && !$this->admin_pm($this->predata['caid'])) return $this->message('您没有指定栏目的后台管理权限',axaction(2,M_REFERER),$return_error);
		//if($this->isadd && ($re = $curuser->arcadd_nopm($this->chid,$this->predata))) $this->message($re,axaction(2,M_REFERER),$return_error);
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
		$fmdata = &$GLOBALS[$this->fmdata];
		if(isset($fmdata[$ename]) && $v = @$this->fields[$ename]){
			$cfg && $v = array_merge($v,$cfg);
			cls_pusher::SetArea($this->paid);
			if($re = cls_pusher::onefield($v,$fmdata[$ename],isset($this->predata[$ename]) ? $this->predata[$ename] : '')){//捕捉出错信息
				cls_pusher::rollback();
				return $this->message($re,axaction(2,M_REFERER),$return_error);
			}
		}
	}
	
	//处理多个属性项
	//可选项目array('startdate','enddate',)
	function sv_params($incs = array()){
		if(empty($incs)) $incs = array('startdate','enddate','norefresh',);
		foreach($incs as $k) $this->sv_param($k);
	}
	
	//处理指定的属性项，可选项目array('startdate','enddate',)
	function sv_param($ename){
		global $timestamp;
		$fmdata = &$GLOBALS[$this->fmdata];
		if($ename && isset($fmdata[$ename])){
			cls_pusher::SetArea($this->paid);
			if($ename == 'startdate'){//开始日期
				cls_pusher::onedbfield($ename,empty($fmdata[$ename]) ? 0 : strtotime($fmdata[$ename]),isset($this->predata[$ename]) ? $this->predata[$ename] : 0);
			}elseif($ename == 'enddate'){//结束时间
				cls_pusher::onedbfield($ename,empty($fmdata[$ename]) ? 0 : strtotime($fmdata[$ename]),isset($this->predata[$ename]) ? $this->predata[$ename] : 0);
			}elseif($ename == 'norefresh'){//关闭更新
				cls_pusher::onedbfield($ename,empty($fmdata[$ename]) ? 0 : 1,isset($this->predata[$ename]) ? $this->predata[$ename] : 0);
			}
		}
	}
	
	function sv_fail($return_error = 0){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1);
		return $this->message('文档添加失败',axaction(2,M_REFERER),$return_error);
	}
	
	//执行自动操作及更新以上变更
	function sv_update(){
		if($this->isadd){ //设置：11.手动添加
			cls_pusher::onedbfield('loadtype',11);
		}
		cls_pusher::SetArea($this->paid);
		cls_pusher::updatedb($this->pushid);
	}
	
	//上传处理
	function sv_upload(){
		$c_upload = cls_upload::OneInstance();
		$c_upload->closure(1,$this->pushid,'pushs');
		$c_upload->saveuptotal(1);
	}
	
		
	//结束时需要的事务， 如：操作记录及成功提示
	function sv_finish(){
		$modestr = $this->isadd ? '添加' : '修改';
		$this->mc || adminlog($modestr.'推送信息');
		$this->message('推送信息'.$modestr.'完成',axaction(6,M_REFERER));
	}
}