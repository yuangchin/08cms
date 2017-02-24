<?
!defined('M_COM') && exit('No Permission');
class cls_pagecache{
	private $cachefile = '';
	private $cfg = array();//针对当前页面的有效方案
	private $needsave = false;
	private $page = 1;
	private $typeid = 1;
	function read($query_arr = array(),$adds = array()){
		if(_08_DEBUGTAG) return;//调试模式
		
		foreach(array('typeid','page',) as $k){
			$this->$k = max(1,intval(@$adds[$k]));
		}
		
		unset($query_arr['page']); # 如果query_arr有页码信息，清除掉
		if(!empty($adds['is_p']) && $this->typeid == 9) return;//不需要缓存的js
		if(!($pc = cls_cache::Read('pagecaches',$this->typeid))) return;
		
		$query_string = $this->arr2str($query_arr); 
		foreach($pc as $k => $v){
			if($this->page < $v['pagefrom']) continue;
			if($this->page > $v['pageto']) continue;
			if(defined('IN_MOBILE') && !empty($v['nomobile'])) continue;
			if(in_array($this->typeid,array(2,8))){//有关文档的
				if($v['indays'] && !empty($adds['initdate']) && $adds['initdate'] < TIMESTAMP - $v['indays'] * 24 * 3600) continue;
				if($v['chids'] && $chids = array_filter(explode(',',$v['chids']))){
					if(!empty($adds['chid']) && !in_array($adds['chid'],$chids)) continue;
				}
			}
			if($v['instr'] && $strs = array_filter(explode(',',$v['instr']))){
				if(!empty($v['instrall'])){
					$valid = true;
					foreach($strs as $str){
						$valid = $valid && in_str($str,$query_string);
						if(!$valid) break;
					}
				}else{
					$valid = false;
					foreach($strs as $str){
						if(in_str($str,$query_string)){
							$valid = true;
							break;
						}
					}
				
				}
				if(!$valid) continue;
			}
			if(@$v['nostr']){
				if(in_str('*',$v['nostr'])){
					if($query_string) continue;
				}elseif($strs = array_filter(explode(',',$v['nostr']))){
					if(@$v['nostrall']){
						$valid = true;
						foreach($strs as $str){
							$valid = $valid && in_str($str,$query_string);
							if(!$valid) break;

						}
					}else{
						$valid = false;
						foreach($strs as $str){
							if(in_str($str,$query_string)){
								$valid = true;
								break;
							}
						}
					
					}
					if($valid) continue;
				}
			}
			$this->cfg = $v;
			break;
		}
		if(!$this->cfg) return;
		if($this->cfg['period']){
			$query_string || $query_string = 'index';
			$query_string = md5($query_string.'_'.cls_env::mconfig('authkey'));
			$this->cachefile = cls_cache::HtmlcacDir($this->typeid,$query_string{0}).$query_string{1}.'/'.$query_string.'_'.$this->page.(defined('IN_MOBILE') ? '_mob' : '').'.html';
			if(is_file($this->cachefile) && filemtime($this->cachefile) > TIMESTAMP - $this->cfg['period']){
				return file2str($this->cachefile);
			}else $this->needsave = ISROBOT ? 0 : 1;
		}
		return;
	}
	function save($content = ''){
		if(!$this->needsave || !$this->cachefile) return;
		str2file($content,$this->cachefile);
	}
	function arr2str($arr,$pre = ''){
		$re = '';
		if(is_array($arr)){
			foreach($arr as $k => $v){
				$key = $pre ? $pre."[$k]" : $k;
				if(is_array($v)){
					$re .= ($re ? '&' : '').$this->arr2str($v,$key);
				}else $re .= ($re ? '&' : '')."$key=$v";
			}
		}
		return $re;
		
	}
}