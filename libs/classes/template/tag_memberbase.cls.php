<?PHP
/**
* [单个会员] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_MemberBase extends cls_TagParse{
	
	
	protected function TagReSult(){
		$Nowid = intval(empty($this->tag['id']) ? cls_Parse::Get('_a.mid') : $this->tag['id']);
		if($Nowid == '-1') $Nowid = self::$curuser->info['mid'];
		if(!empty($this->tag['arid'])){
			if(!$Nowid) $this->TagThrowException("请指定相关会员ID");
			if(!($abrel = cls_cache::Read('abrel',$this->tag['arid']))) $this->TagThrowException("请指定正确的合辑项目arid");
			if($abrel['tbl']){
				$Nowid = self::$db->result_one("SELECT pid FROM ".self::$tblprefix.$abrel['tbl']." WHERE inid='$Nowid'");
			}else $Nowid = self::$db->result_one("SELECT pid".$this->tag['arid']." FROM ".self::$tblprefix.($abrel['source'] ? 'members' : 'archives')." WHERE ".($abrel['source'] ? 'mid' : 'aid')."='$Nowid'");
			if(!$Nowid) $this->TagThrowException("未找到相关联的会员");
		}
		
		$auser = new cls_userinfo;
		$auser->activeuser($Nowid,empty($this->tag['detail']) ? 0 : 1,intval(@$this->tag['ttl']));
		if(@$auser->info['checked'] != 1){ # 未审会员按游客资料
			$ReturnArray = cls_userinfo::nouser_info();
		}elseif(!empty($this->tag['chids']) && $this->tag['chids']!=$auser->info['mchid']){ //指定会员模型,但不符合条件,按游客处理
			$ReturnArray = cls_userinfo::nouser_info();
		}else{
			$ReturnArray = $auser->info;
		}
		unset($auser);
		
		$ReturnArray = $this->TagOneRecord($ReturnArray);
		return $ReturnArray;//可能出现游客资料
	}
	
	
	# 返回结果中的单条记录的处理
	protected function TagOneRecord($OneRecord){
		cls_UserMain::Parse($OneRecord);
		return $OneRecord;
	}
	
	
}
