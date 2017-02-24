<?php
/**
 * 交互列表管理中的批量操作基类	 
 */
!defined('M_COM') && exit('No Permisson');
class cls_cuopsbase extends cls_cubasic{

	public $A = array();//初始化参数存放、如chid，pre(主表前缀),tbl(主表),stid(主表id)
	public $cfgs = array();//项目配置
	public $actcu	= NULL;//批量操作中的指定交互，只在数据保存时使用
	public $mchannel = array();//当前模型

	public $recnt = array();//统计各操作,用于提示,readd,valid
	public $cnt_msgs = array();//用于直接显示字符串的提示,readd,valid,refresh
	
    function __construct($cfg = array()){
		parent::__construct($cfg);
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;
	}
	
	/**
	* 添加批量操作项目
	*
	* @ex $oL->o_additem('validperiod',array('value' => 30));;
	*
	* @param    string     $key  项目关键字 可以自己定制项目，也可以是以下值 等等
						delete：删除交互操作
						delbad：删除(扣分)
						check：审核交互操作
						uncheck：解审核操作
	* @param    array      $cfg  项目配置参数 可选，默认为空 
						type：定义类型之后，会启用function type_{type}()来处理
	//完全定制方法：user_$key，此为优先使用的方法
	//系统内置方法：type_$key，在以下情况下调用：定义了type、未定制的类系、其它未指定方法的显示项
	*/
	function additem($key,$cfg = array()){
		//title：项目名称
		//bool：是否单选项，取值：0或1
		//guide：提示说明，可用于独占一行的项目
		//w：单行文本框的宽度
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
	function view_one_bool($key){
		if(!isset($this->cfgs[$key]) || @$this->cfgs[$key]['bool'] != 1) return '';
		$re = $this->view_one($key);
		$this->del_item($key);
		return $re;		
	}	
	
	//显示单行的操作项
	function view_one_row($key){
		if(!isset($this->cfgs[$key]) || !empty($this->cfgs[$key]['bool'])) return false;
		$re = $this->view_one($key);//直接显示
		$this->del_item($key);
		return $re;		
	}
	
	//单项保存
	function save_one($key){
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
			if('ugid' == substr($key,0,4)){
				$re = $this->type_ugid($key,$mode);
			}elseif('push' == substr($key,0,4)){
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
	
	//是否一个已选择的操作项目，只用于提交之后的判断
	protected function isSelectedItem($key = ''){
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
			cls_commu::push($this->cuid,$this->actcu['cid'],$key);
		}
	}
	
	//exkey: 用于同时删除回复,同时删除exkey=$cid
	protected function user_delete($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('mdel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除';
			return $this->input_checkbox($key,$cfg['title'],1,'onclick="deltip()"');
		}elseif($mode == 2){//数据
			$this->delete($this->actcu['cid'],@$cfg['exkey']);
		}
	}	
	
	//exkey: 用于同时删除回复,同时删除exkey=$cid
	protected function user_delbad($mode = 0){
		$key = substr(__FUNCTION__,5);
		if(!$this->mc && !allow_op('adel')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '删除(扣积分)'; 
			return $this->input_checkbox($key,$cfg['title'],1,"onclick=\"deltip()\"");
		}elseif($mode == 2){//数据
			$this->setCrids('dec', $this->actcu['mid']); //扣积分
			$this->delete($this->actcu['cid'],@$cfg['exkey']);
		}
	}
	
	protected function user_check($mode = 0){
		$key = substr(__FUNCTION__,5);
		//if(!$this->mc && !allow_op('mcheck')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '审核';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			$this->db->update($this->table(), array('checked' => '1'))->where('cid='.$this->actcu['cid'])->exec();
		}
	}	
	protected function user_uncheck($mode = 0){
		$key = substr(__FUNCTION__,5);
		//if(!$this->mc && !allow_op('mcheck')) return $this->del_item($key);
		$cfg = &$this->cfgs[$key];
		if(!$mode){//初始化
			$cfg['bool'] = 1;
		}elseif($mode == 1){//显示
			if(empty($cfg['title'])) $cfg['title'] = '解审';
			return $this->input_checkbox($key,$cfg['title'],1);
		}elseif($mode == 2){//数据
			if($this->isSelectedItem('check')) return false;//不跟check同时执行
			$this->db->update($this->table(), array('checked' => '0'))->where('cid='.$this->actcu['cid'])->exec();
		}
	}
		
}

