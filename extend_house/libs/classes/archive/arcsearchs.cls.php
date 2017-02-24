<?php
class cls_arcsearchs extends cls_arcsearchsbase{
	protected function user_mj(){ //面积
		$key = substr(__FUNCTION__,5); 
		$this->user_other_fields($key);
	}
	
	protected function user_zj(){ //价格
		$key = substr(__FUNCTION__,5); 
		$this->user_other_fields($key);
	}
		
	protected function user_szlc(){ //楼层
		$key = substr(__FUNCTION__,5);
		$this->user_other_fields($key);
	}	
	
	protected function user_other_fields($key){ //面积 //价格 //楼层
		$keyfr = "{$key}fr"; $keyto = "{$key}to"; 
		$cfg = &$this->cfgs[$key];
		$this->init_item($keyfr,'int',1);
		$this->init_item($keyto,'int',1);
		if($this->nvalue[$keyfr]){
			$this->wheres[$keyfr] = (empty($cfg['pre'])?"a.":$cfg['pre']).$key.">='".$this->nvalue[$keyfr]."' ";
		}
		if($this->nvalue[$keyto]){
			$this->wheres[$keyto] = (empty($cfg['pre'])?"a.":$cfg['pre']).$key."<='".$this->nvalue[$keyto]."' ";
		} 
		$_keys_title = '';
		switch($key){
			case 'mj':
				$_keys_title = '面积';				
			break;
			case 'szlc':
				$_keys_title = '楼层';				
			break;
			default:
				$_keys_title = '价格';				
			break;
		}
		if(empty($cfg['hidden'])){
			$html = $_keys_title."<input class=\"text\" name=\"".$key."fr\" type=\"text\" value=\"".$this->nvalue[$keyfr]."\" size=\"2\" style=\"vertical-align: middle;\">";
			$html .= "~<input class=\"text\" name=\"".$key."to\" type=\"text\" value=\"".$this->nvalue[$keyto]."\" size=\"2\" style=\"vertical-align: middle;\">";
			$this->htmls[$key] = $html;
		}
	}	
	
	
	
	
	//筛选个人/中介的房源信息
	protected function user_mchid(){
		global $tblprefix;
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr			
			if($this->nvalue[$key] == 1){
				$this->wheres[$key] = "({$cfg['pre']}mid IN(SELECT mid FROM {$tblprefix}members_1) OR {$cfg['pre']}mid='0')";
			}elseif($this->nvalue[$key] == 2){
				$this->wheres[$key] = "{$cfg['pre']}mid IN(SELECT mid FROM {$tblprefix}members_2)";
			}
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-会员类型-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'1' => '个人','2' => '中介',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	
	//筛选会员/超级管理员发布的招聘信息
	protected function user_isfounder(){
		global $tblprefix;
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1',1);
		if($this->nvalue[$key] != -1){//针对性处理wherestr			
			if($this->nvalue[$key] == 1){
				$this->wheres[$key] = " (b.grouptype2 != '0' or b.isfounder = '1') ";
			}elseif($this->nvalue[$key] == 2){
				$this->wheres[$key] = " b.grouptype2 = '0' AND b.isfounder = '0'  ";
			}
		}
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-会员类型-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'1' => '管理员','2' => '会员',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	//筛选已被预约的房源信息
	protected function user_yuyue(){
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int-1');
		if(empty($cfg['hidden'])){
			$title = empty($cfg['title']) ? "-预约-" : $cfg['title'];
			$this->htmls[$key] = $this->input_select($key,array('-1' => $title,'0' => '未约','1' => '已约',),$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}
	
	protected function user_orderby_e(){//可以传入$cfg['options']
		$key = substr(__FUNCTION__,5);
		$cfg = &$this->cfgs[$key];
		$this->init_item($key,'int',1);
		if(empty($cfg['options'])){
			$title = empty($cfg['title']) ? "-排序方式-" : $cfg['title'];
			$cfg['options'] = array(
				0 => array($title,$this->A['orderby']),
				1 => array('按点击数',$cfg['pre'].'clicks DESC'),
				2 => array('按刷新时间',$cfg['pre'].'refreshdate DESC'),
				3 => array('按添加时间',$cfg['pre'].'createdate DESC'),
				4 => array('按指定排序',$cfg['pre'].'ccid41 DESC,a.vieworder,a.aid DESC'),
			);
		}
		$sarr = array();
		foreach($cfg['options'] as $k => $v){
			$sarr[$k] = $v[0];
			if($this->nvalue[$key] == $k) $this->orderby = $v[1];
		}
		if(empty($cfg['hidden'])){
			$this->htmls[$key] = $this->input_select($key,$sarr,$this->nvalue[$key]);
		}else $this->htmls[$key] = $this->input_hidden($key,$this->nvalue[$key]);
	}

	
	
}
