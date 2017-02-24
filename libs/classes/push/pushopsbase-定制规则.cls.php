<?php
/*
** 文档列表管理中的批量操作类
** 含操作项的展示及数据处理
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushopsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放
	public $cfgs = array();//项目配置
	public $area = array();//当前推荐位
	public $push = array();//单条推送信息资料
	
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;	
		if(empty($this->A['paid']) || !($this->area = cls_PushArea::Config($this->A['paid']))) exit('请指定推送位');
	}
	
	//单项初始化
	public function additem($key,$cfg = array()){
		//title：项目名称
		//bool：是否单选项，取值：0或1
		//guide：提示说明，可用于独占一行的项目
		//w：单行文本框的宽度
		$this->cfgs[$key] = $cfg;
		return $this->one_item($key,0);
	}
	//返回单选项的显示html
	public function view_one_bool($key){
		if(!isset($this->cfgs[$key]) || empty($this->cfgs[$key]['bool'])) return '';
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
			if('classid' == substr($key,0,7)){
				$re = $this->type_classid($key,$mode);
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
		if(!$ischeck) $re .= "{$title} &nbsp;";
		$re .= "<input class=\"checkbox\" type=\"checkbox\" id=\"{$this->A['ofm']}[$key]\" name=\"{$this->A['ofm']}[$key]\" value=\"1\" $addstr>";
		if($ischeck) $re .= "<label for=\"{$this->A['ofm']}[$key]\">{$title}</label> &nbsp;";
		return $re;
	}
	
	protected function type_classid($key,$mode = 0){
		$field = cls_PushArea::Field($this->A['paid'],$key);
		if(!$field || !in_array($field['datatype'],array('select','mselect','cacc',))) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
		}elseif($mode == 1){//显示
			$a_field = new cls_field;
			$cfg = array_merge($field,$cfg);
			$a_field->init($cfg);
			$varr = $a_field->varr('_'.$this->A['opre']);
			trspecial($this->input_checkbox($key,'设置'.$cfg['cname']),$varr);
		}elseif($mode == 2){//数据
			cls_pusher::setclassid($this->push['pushid'],@$GLOBALS[$this->A['opre'].$key],$key,$this->A['paid']);
		}
	}
	protected function user_refresh($mode = 0){
		$key = substr(__FUNCTION__,5);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '同步来源';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			echo "{$this->push['pushid']},{$this->A['paid']}";
			cls_pusher::Refresh($this->push['pushid'],$this->A['paid']);
		}
	}	
	
	protected function user_delete($mode = 0){
		$key = substr(__FUNCTION__,5);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除';
			return $this->input_checkbox($key,$cfg['title'],1,'onclick="deltip()"');
		}elseif($mode == 2){//数据
			cls_pusher::delete($this->push['pushid'],$this->A['paid']);
		}
	}	
	protected function user_check($mode = 0){
		$key = substr(__FUNCTION__,5);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '审核';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			cls_pusher::check($this->push);
		}
	}	
	protected function user_uncheck($mode = 0){
		$key = substr(__FUNCTION__,5);
		
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '解审';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->is_item('check')) return false;//不跟check同时执行
			cls_pusher::uncheck($this->push);
		}
	}
}
