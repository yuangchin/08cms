<?PHP
/**
* [视频列表/单个视频/Flash列表/单个Flash/单个文件下载/文件列表下载] 标签共同处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_MediasBase extends cls_TagParse{
	
	# 返回数据结果
	protected function TagReSult(){
		if(in_array($this->tag['tclass'],array('medias','flashs','files',))){
			return $this->TagAtmArray();
		}else{
			return $this->TagOneAtm();
		}
	}
	
	protected function TagAtmArray(){
		$ReturnArray = array();
		$AtmArray = @array_slice(unserialize($this->tag['tname']),$this->TagInitStart(),$this->TagInitLimits(),TRUE);
		if(!empty($AtmArray)){
			foreach($AtmArray as $k => $v){
				$Info = array();
				$Info['fid'] = $k;
				$Info['url'] = cls_url::tag2atm($v['remote']);
				$Info['title'] = $v['title'];
				$Info['sn_row'] = $i = empty($i) ? 1 : ++ $i;
				$Info['aid'] = (int)cls_Parse::Get('a.aid');
				if(in_array($this->tag['tclass'],array('medias','flashs',))){
					$type = substr($this->tag['tclass'],0,5);		
					$Info['player'] = empty($v['player']) ? 0 : $v['player'];
					$Info['playbox'] = $this->PlayerBox($Info,$type);
				}
				$ReturnArray[] = $Info;
			}
		}
		return $ReturnArray;
	}
		
	protected function TagOneAtm(){
		$NeedPlay = in_array($this->tag['tclass'],array('media','flash',)); 
		$Info = array();
		$Info['aid'] = (int)cls_Parse::Get('a.aid');
		if(empty($this->tag['tname'])){
			$Info['url'] = cls_Parse::Get('a.url');
			if($NeedPlay) $Info['player'] = (int)cls_Parse::Get('a.player');
		}else{
			$TempArray = explode('#',$this->tag['tname']);
			$Info['url'] = cls_url::tag2atm($TempArray[0]);
			if($NeedPlay) $Info['player'] = empty($TempArray[1]) ? 0 : (int)$TempArray[1];
			unset($TempArray);
		}
		if($NeedPlay) $Info['playbox'] = $this->PlayerBox($Info,$this->tag['tclass']);
		return $Info;
	}
	
	protected function PlayerBox(&$Info = array(),$type = 'media'){
		if(empty($Info['url'])) return '';
		
		$Content = '';
		$players = cls_cache::Read('players');
		$plid = empty($Info['player']) ? 0 : (int)$Info['player'];
		if(!$plid){
			$ext = strtolower(mextension($Info['url']));
			foreach($players as $k => $player){
				if($player['available'] && ($player['ptype'] == $type) && in_array($ext,array_filter(explode(',',$player['exts'])))){
					$plid = $k;
					break;
				}
			}
		}
		if($plid){
			//$Info['tplurl'] = cls_Parse::Get('a.tplurl'); 
			$btags = cls_cache::Read('btags'); 
			$Info['tplurl'] = $btags['tplurl'];  
			$Info['cms_abs'] = self::$cms_abs;
			$Info['width'] = empty($this->tag['width']) ? '100%' : (int)$this->tag['width'];
			$Info['height'] = empty($this->tag['height']) ? '100%' : (int)$this->tag['height'];
			$player = $players[$plid];
			$Content = sqlstr_replace($player['template'],$Info);
		}
		return $Content;
	
	}
}
