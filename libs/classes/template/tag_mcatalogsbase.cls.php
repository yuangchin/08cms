<?PHP
/**
* [空间类目列表] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_McatalogsBase extends cls_TagParse{

	# 返回数据结果
	protected function TagReSult(){
		$DataParams = cls_Parse::Get('_da');
		$ReturnArray = array();
		$nowMcatalogs = cls_Mspace::LoadMcatalogs(@$DataParams['mtcid']); # 取得某空间方案的所有空间栏目
		if(empty($this->tag['listby'])){//全部栏目
			if(empty($this->tag['casource'])){
				foreach($nowMcatalogs as $k => $v) $ReturnArray[] = $v;
			}elseif($this->tag['casource'] == 1){//指定栏目
				if(!empty($this->tag['caids'])){
					$caids = explode(',',$this->tag['caids']);
					foreach($nowMcatalogs as $k => $v) if(in_array($k,$caids)) $ReturnArray[] = $v;
				}
			}
			foreach($ReturnArray as $k => $v){
				$Params = array('mcaid' => $v['mcaid']);
				foreach(array(0,1) as $x){
					$Params['addno'] = $x;
					$v['indexurl'.($x ? $x : '')] = cls_Mspace::IndexUrl($DataParams,$Params);
				}
				$v['sn_row'] = $i = empty($i) ? 1 : ++ $i;
				$ReturnArray[$k] = $v;
			}
		}else{ # 手动指定或激活栏目
			if($id = empty($this->tag['ucsource']) ? (int)cls_Parse::Get('_a.mcaid') : (int)$this->tag['ucsource']){
				$nowUclasses = cls_Mspace::LoadUclasses($DataParams['mid']);
				foreach($nowUclasses as $k => $v){
					if($v['mcaid'] == $id){
						$Params = array('mcaid' => $v['mcaid'],'ucid' => $v['ucid'],);
						foreach(array(0,1) as $x){
							$Params['addno'] = $x;
							$v['indexurl'.($x ? $x : '')] = cls_Mspace::IndexUrl($DataParams,$Params);
						}
						$v['sn_row'] = $i = empty($i) ? 1 : ++ $i;
						$ReturnArray[] = $v;
					}
				}
			}
		}
		return $ReturnArray;
	}
}
