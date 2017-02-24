<?php
/* 
** 权限分析相关方法汇总
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_PermissionBase{
	/**
	 * 根据权限方案分析操作权限，返回无权限的权限
	 *
	 * @param  array  $info  会员的主表信息，需要包含会员组信息，认证信息
	 * @param  int    $pmid  权限方案ID
	 * @return string $str   无权限时将返回原因，有权限则返回''
	 */
	function noPmReason($info = array(),$pmid=0){
		$str = '';
		if($re = _mem_noPm($info,$pmid)){//无权限，返回原因
			if(!empty($re['nouser'])){
				$str = '会员未登录无权限';
			}elseif(!empty($re['nouser'])){
				$str = '无效的权限方案';
			}else{
				if(!empty($re['mctids_and']) || !empty($re['mctids_or'])){
					$_str = '';
					$mctids = !empty($re['mctids_or']) ? $re['mctids_or'] : $re['mctids_and'];
					$mctypes = cls_cache::Read('mctypes');
					foreach($mctids as $k) empty($mctypes[$k]) || $_str .= ($_str ? (!empty($re['mctids_or']) ? '"或"' : '"及"') : '').$mctypes[$k]['cname'];
					$_str && $str .= '<br>需要以下认证：'.$_str;
				}
				if(!empty($re['nougids'])){//允许的会员组
					if(in_array('-',$re['nougids'])){
						$_str = '<br>不允许所有会员组';
					}else{
						$_str = '';
						$grouptypes = cls_cache::Read('grouptypes');
						foreach($grouptypes as $k => $v){
							if(!$v['forbidden']){
								$ugs = cls_cache::Read('usergroups',$k);
								foreach($ugs as $x => $y) in_array($x,$re['nougids']) && $_str .= ($_str ? '"或"' : '').$y['cname'];
							}
						}
					}
					$_str && $str .= '<br>以下组有权限：'.$_str;
				}
				if(!empty($re['inugids'])){//禁止的会员组
					$_str = '';
					$grouptypes = cls_cache::Read('grouptypes');
					foreach($grouptypes as $k => $v){
						if(!$v['forbidden']){
							$ugs = cls_cache::Read('usergroups',$k);
							foreach($ugs as $x => $y) in_array($x,$re['inugids']) && $_str .= ($_str ? '"或"' : '').$y['cname'];
						}
					}
					$_str && $str .= '<br>以下组被禁止：'.$_str;
				}
				$str && $str = substr($str,4);
			}
		}
		return $str;
	}
	
	
}