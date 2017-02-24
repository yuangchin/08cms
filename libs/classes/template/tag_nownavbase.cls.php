<?PHP
/**
* [类目导航] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_NownavBase extends cls_TagParse{
	
	
	protected function TagReSult(){
		parse_str(cls_cnode::cnstr(cls_Parse::Get('_a')),$idsarr);
		$ReturnArray = array();
		$navstr = '';
		$midarr = array();
		$coids = empty($this->tag['coids']) ? '' : explode(',',$this->tag['coids']);
		foreach($idsarr as $k => $v){
			if(!$coids || in_array($k,$coids)){
				$coid = $k == 'caid' ? 0 : intval(str_replace('ccid','',$k));
				$pids = cls_catalog::Pccids($v,$coid,1);
				foreach($pids as $id){
					$midarr[$k] = $id;
					if($item = $this->OneNav($midarr,$coid)){
						$item['sn_row'] = $i = empty($i) ? 1 : ++ $i;
						$ReturnArray[] = $item;
					}
				}
			}
		}
		return $ReturnArray;
	}
	protected function OneNav($midarr,$coid=0){
		$item = $coid ? cls_cache::Read('coclass',$coid,$midarr['ccid'.$coid]) : cls_cache::Read('catalog',$midarr['caid']);
		$cnstr = cls_cnode::cnstr($midarr);
		$cnode = cls_node::cnodearr($cnstr,defined('IN_MOBILE'));
		cls_node::re_cnode($item,$cnstr,$cnode);
		if(!cls_tpl::cn_tplname($cnstr,$cnode,0) && cls_tpl::cn_tplname($cnstr,$cnode,1)) $item['indexurl'] = $item['indexurl1'];
		return $item;
	}
	
	
}
