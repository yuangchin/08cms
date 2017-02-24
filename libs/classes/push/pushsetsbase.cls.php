<?php
/*
** 列表中列项目的数据处理类
** 含列的索引单元及内容单元
*/
!defined('M_COM') && exit('No Permisson');
class cls_pushsetsbase{
	protected $mc = 0;//会员中心
	public $A = array();//初始化参数存放、如paid，tbl(主表)等
	public $cfgs = array();//设置项配置
	
    function __construct($cfg = array()){
		$this->mc = defined('M_ADMIN') ? 0 : 1;
		$this->A = $cfg;
		if(empty($this->A['paid'])) exit('请指定推送位');
	}
	
	public function additem($key = '',$cfg = array()){
		if(!$key) return;
		$this->cfgs[$key] = $cfg;
	}
	
	//指定文档及指定设置项的数据处理
	public function set_one($key,$value,$data = array()){
		$args = func_get_args();
		$re = $this->call_method("user_$key",$args);//定制方法
		if($re == 'undefined'){
			$re = $this->com_method($key,$value,$data);//通用方法
		}
		return $re;
	}
	protected function call_method($func,$args = array()){
		if(method_exists($this,$func)){
			return call_user_func_array(array(&$this,$func),$args);
		}else return 'undefined';
	}
	
	protected function com_method($key,$value,$data = array()){
		//通用方法：按正整数，key为数据表字段名，文档主表
		//type：数据格式
		//sql：自定义sql，可以使用{value},{key}，及$data中的资料作为占位符
		
		global $db,$tblprefix;
		if(!isset($this->cfgs[$key]) || empty($data['pushid'])) return false;
		$cfg = $this->cfgs[$key];
		$value = $this->format_value($value,empty($cfg['type']) ? 'int+' : $cfg['type']);
		if(!isset($data[$key]) || stripslashes($value) == $data[$key]) return false;//如果值未改动，则不做更新
		
		if(!empty($cfgs['sql'])){
			$sql = key_replace($cfgs['sql'],array('value' => $value,'key' => $key) + $data);
		}else $sql = "UPDATE {$tblprefix}{$this->A['tbl']} SET $key='$value' WHERE pushid='{$data['pushid']}'";
		$db->query($sql);
	}
	
	protected function user_vieworder($key,$value,$data = array()){
		global $db,$tblprefix;
		if(!isset($this->cfgs[$key]) || empty($data['pushid'])) return;
		$cfg = $this->cfgs[$key];
		$value = cls_pusher::orderformat($value,$this->A['paid'],'vieworder');
		if(!isset($data[$key]) || stripslashes($value) == $data[$key]) return false;//如果值未改动，则不做更新
		$sql = "UPDATE {$tblprefix}{$this->A['tbl']} SET vieworder='$value' WHERE pushid='{$data['pushid']}' LIMIT 1";
		$db->query($sql);
	}
	
	protected function user_fixedorder($key,$value,$data = array()){
		global $db,$tblprefix;
		if(!isset($this->cfgs[$key])) return;
		$cfg = $this->cfgs[$key];
		$value = cls_pusher::orderformat($value,$this->A['paid'],'fixedorder');
		if(!isset($data[$key]) || stripslashes($value) == $data[$key]) return false;//如果值未改动，则不做更新
		$sql = "UPDATE {$tblprefix}{$this->A['tbl']} SET fixedorder='$value' WHERE pushid='{$data['pushid']}' LIMIT 1";
		$db->query($sql);
	}
	
	protected function format_value($value,$type = ''){
		$type || $type = 'int+';
		switch($type){
			case 'int+':
				$value = max(0,intval($value));
				break;
			case 'int':
				$value = intval($value);
				break;
			case 'bool':
				$value = empty($value) ? 0 : 1;
				break;
			case 'str':
				$value = trim(strip_tags($value));
				break;
			case 'date':
				$value = $value ? strtotime($value) : 0;
				break;
		}
		return $value;
	}
	
}
