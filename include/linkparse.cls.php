<?php
class linkparse{
	var $html = '';
	var $links = array();
	var $reflink = '';
	var $rpid = 0;
	var $wmid = 0;
	var $jumpfile = '';
	function __construct(){
		$this->linkparse();
	}
	function linkparse(){
	}
	function setsource($html,$reflink,$rpid=0,$wmid=0,$jumpfile=''){
		$this->html = $html;
		$this->reflink = $reflink;
		$this->rpid = $rpid;
		$this->wmid = $wmid;
		$this->jumpfile = $jumpfile;
	}
	function handlelinks(){//从文本中提取多个url进行处理
		$links = array();
		$aregions = array();
		$regex = "/<a(.+?)href\s*=\s*(\"(.+?)\"|'(.+?)'|(.+?)(\s|\/?>))/is";
		if(preg_match_all($regex,$this->html,$matches)){
			$aregions = array_filter(array_unique(array_merge($matches[3],$matches[4],$matches[5])));
			foreach($aregions as $aregion){
				$nregion = fillurl($aregion,$this->reflink);
				$links[] = $nregion;
				$regex1 = preg_quote($aregion,'/');
				$regex1 = "/href[ ]*=[ |'|\"]*".$regex1."[ |'|\"]+/is";
				$this->html = preg_replace($regex1,"href=\"$nregion\" ",$this->html);
			}
		}
		$regex = "/<[img|embed]([^<|>]+?)src\s*=\s*(\"(.+?)\"|'(.+?)'|(.+?)(\s|\/?>))/is";
		if(preg_match_all($regex,$this->html,$matches)){
			$aregions = array_filter(array_unique(array_merge($matches[3],$matches[4],$matches[5])));
			foreach($aregions as $aregion){
				$nregion = fillurl($aregion,$this->reflink);
				$nregion = $this->remotefile($nregion,1);
				$links[] = $nregion;
				$regex1 = preg_quote($aregion,'/');
				$regex1 = "/src[ ]*=[ |'|\"]*".$regex1."[ |'|\"]+/is";
				$this->html = preg_replace($regex1,"src=\"$nregion\" ",$this->html);
			}
		}
		$this->links = $links;
		unset($links,$regex,$matches,$aregions,$nregion);
	}
	function handlelink($link){//指定为链接类型的字段的url处理，属于单个附件
		if(!$link) return '';
		$link = fillurl($link,$this->reflink);
		$link = $this->remotefile($link);
		return $link;
	}
	function remotefile($remotefile,$fromstr = 0){
		$c_upload = cls_upload::OneInstance();
		$re = $c_upload->remote_upload($remotefile,$this->rpid,$this->wmid,$this->jumpfile);
		if($fromstr && strpos($re['remote'],':/') === false) $re['remote'] = '<!cmsurl />'.$re['remote'];//只有文本中的url才需要加上<!cmsurl />标记
		return $re['remote'];
	}		
}

?>
