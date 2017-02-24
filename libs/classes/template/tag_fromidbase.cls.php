<?PHP
/**
* [指定ID资料] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_FromidBase extends cls_TagParse{

	# 返回数据结果
	protected function TagReSult(){
			
		if(empty($this->tag['type'])) return '';
		$Nowid = empty($this->tag['id']) ? cls_Parse::Get('_a.'.$this->tag['type']) : $this->tag['id'];
		$Nowid = max(0,intval($Nowid));
		if(!$Nowid) return '';
		
		if($this->tag['type'] == 'chid'){
			return cls_channel::Config($Nowid);
		}elseif($this->tag['type'] == 'mchid'){
			return cls_cache::Read('mchannel',$Nowid);
		}elseif($this->tag['type'] == 'mctid'){
			return cls_cache::Read('mctype',$Nowid);
		}elseif($this->tag['type'] == 'caid'){
			return cls_cache::Read('catalog',$Nowid);
		}elseif(in_str('ccid',$this->tag['type'])){
			if(!$coid = max(0,intval(str_replace('ccid','',$this->tag['type'])))) return '';
			return cls_cache::Read('coclass',$coid,$Nowid);
		}elseif(in_str('grouptype',$this->tag['type'])){
			if(!$gtid = max(0,intval(str_replace('grouptype','',$this->tag['type'])))) return '';
			return cls_cache::Read('usergroup',$gtid,$Nowid);
		}
	}
}
