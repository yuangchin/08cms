<?PHP
/**
* [单个文档列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_ArchiveBase extends cls_TagParse{
	
	
	protected function TagReSult(){
		$Nowid = intval(empty($this->tag['id']) ? cls_Parse::Get('_a.aid') : $this->tag['id']);
		if($Nowid && !empty($this->tag['arid'])){
			if(!$abrel = cls_cache::Read('abrel',(int)$this->tag['arid'])){
				$this->TagThrowException("请指定正确的合辑项目arid");	
			}
			if(!empty($abrel['tbl'])){
				$Nowid = self::$db->result_one("SELECT pid FROM ".self::$tblprefix.$abrel['tbl']." WHERE inid='$Nowid'");
			}elseif(!empty($abrel['source'])){
				$Nowid = self::$db->result_one("SELECT pid".$this->tag['arid']." FROM ".self::$tblprefix."members WHERE mid='$Nowid'");
			}elseif($ntbl = atbl($Nowid,2)){
				$Nowid = self::$db->result_one("SELECT pid".$this->tag['arid']." FROM ".self::$tblprefix."$ntbl WHERE aid='$Nowid'");
			}else $Nowid = 0;
		}
		if(!$Nowid) $this->TagThrowException("未找指定或激活的id");	
		
		$arc = new cls_arcedit;
		if(!$arc->set_aid($Nowid,array('chid'=>intval(@$this->tag['chid']),'ch'=>@$this->tag['detail'],'au'=>0,'nodemode'=>defined('IN_MOBILE'),'ttl'=>intval(@$this->tag['ttl']),))){
			$this->TagThrowException("未找到指定的文档");	
		}
		$ReturnArray = $arc->archive;
		unset($arc);
		
		$ReturnArray = $this->TagOneRecord($ReturnArray); # 返回结果的单条记录处理
		return $ReturnArray;
	}
	
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		cls_ArcMain::Parse($OneRecord);
		return $OneRecord;
	}
	
	
}
