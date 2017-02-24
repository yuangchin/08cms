<?PHP
/**
* [空间类目导航] 标签处理类
* 对cls_Parse负责，模板设计不会直接接触标签处理类
*/

defined('M_COM') || exit('No Permission');
abstract class cls_Tag_MnownavBase extends cls_TagParse{
	
	protected function TagReSult(){
		$ReturnArray = array();
		$Mtcid = (int)cls_Parse::Get('_da.mtcid');
		$nowMcatalogs = cls_Mspace::LoadMcatalogs($Mtcid);
		$NowCaid = (int)cls_Parse::Get('_da.mcaid');
		if($NowCaid && ($Info = @$nowMcatalogs[$NowCaid])){
			$ps = array('mcaid' => $NowCaid);
			foreach(array(0,1) as $k){
				$ps['addno'] = $k;
				$Info['indexurl'.($k ? $k : '')] = cls_Mspace::IndexUrl(cls_Parse::Get('_da'),$ps);
			}
			$Info['sn_row'] = $i = empty($i) ? 1 : $i+1;
			$ReturnArray[] = $Info;
		}
		$NowUcid = (int)cls_Parse::Get('_da.ucid');
		if($NowUcid){
			$nowUclasses = cls_Mspace::LoadUclasses((int)cls_Parse::Get('_da.mid'));
			if($Info = @$nowUclasses[$NowUcid]){
				$ps = array('mcaid' => $Info['mcaid'],'ucid' => $Info['ucid'],);
				foreach(array(0,1) as $k){
					$ps['addno'] = $k;
					$Info['indexurl'.($k ? $k : '')] = cls_Mspace::IndexUrl(cls_Parse::Get('_da'),$ps);
				}
				$Info['sn_row'] = $i = empty($i) ? 1 : $i+1;
				$ReturnArray[] = $Info;
			}
		}
		return $ReturnArray;
	}
}
