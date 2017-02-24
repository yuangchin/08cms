<?php
/**
* 有关类目节点、会员节点的处理方法
* 
*/
defined('M_COM') || exit('No Permission');
class cls_node{

	/**
	 * 根据节点$cnstr，返回节点名称
	 * Demo: cnode_cname('caid=95&ccid1=93')  -=>  行情=>家具
	 *
	 * @param  string  $cnstr 类型
	 * @return string  $ret   返回的节点名称
	 */
	public static function cnode_cname($cnstr){
		parse_str($cnstr,$idsarr);
		$ret = '';
		foreach($idsarr as $k => $v){
			$item = $k == 'caid' ? cls_cache::Read('catalog',$v) : cls_cache::Read('coclass',str_replace('ccid','',$k),$v);
			$ret .= ($ret ? '=>' : '').@$item['title'];
		}
		unset($item,$idsarr);
		return $ret;
	}
	
	
	/**
	 * 根据节点字串，返回会员节点信息
	 * Demo: read_mcnode('ccid1=210')
	 *
	 * @param  string  $cnstr 节点字串
	 * @return array   $ret   返回的节点信息(包含名称，模版名等信息)
	 */
	public static function read_mcnode($cnstr){
		$arr = cls_cache::Read('mcnodes');
		if(empty($arr[$cnstr])) return array();
		$ret = $arr[$cnstr];
		unset($arr);
		return LoadMcnodeConfig($ret);
	}
	
	/**
	 * 根据会员节点信息，得到节点字串
	 *
	 * @param  array   $temparr 会员节点信息
	 * @return string  $cnstr   节点字串
	 */
	public static function mcnstr($temparr){
		$cotypes = cls_cache::Read('cotypes');
		$grouptypes = cls_cache::Read('grouptypes');
		$vararr = array('caid','mcnid');
		foreach($cotypes as $k => $v) !$v['self_reg'] && $vararr[] = 'ccid'.$k;
		foreach($grouptypes as $k => $v) !$v['issystem'] && $vararr[] = 'ugid'.$k;
		$cnstr = '';
		foreach($temparr as $k => $v){
			if(in_array($k,$vararr) && $v = max(0,intval($v))){
				$cnstr = $k.'='.$v;
				break;
			}
		}
		return $cnstr;
	}
	
	/**
	 * 根据节点字串，返回节点信息
	 *
	 * @param   string  $cnstr   节点字串
	 * @param  int		$NodeMode  是否手机节点
	 * @return  array   $cnode 		节点信息
	 */
	public static function cnodearr($cnstr,$NodeMode = 0){
		if(!($cnode = self::read_cnode($cnstr,$NodeMode))) return array();
		cls_url::view_cnurl($cnstr,$cnode);
		return $cnode;
	}
	
	/**
	 * 根据会员节点字串，返回节点信息
	 *
	 * @param  string  $cnstr  节点key如(ccid1=210)
	 * @return array   $cnode  节点信息
	 */
	public static function mcnodearr($cnstr){ 
		if(!($cnode = self::read_mcnode($cnstr))){
			return array();
		}
		$cnode['cname'] = $cnode['alias'];
		cls_url::view_mcnurl($cnstr,$cnode);
		return $cnode;
	}
	
	/**
	 * 根据节点字串，返回节点信息
	 * Demo: cls_node::read_cnode('ccid1=210')
	 *
	 * @param  string  $cnstr 节点字串
	 * @return array   $ret   返回的节点信息(包含名称，模版名等信息)
	 */
	public static function read_cnode($cnstr,$NodeMode = 0){
		if(!$cnstr) return array();
		$na = cls_cache::Read($NodeMode ? 'o_cnodes' : 'cnodes');
		if(empty($na[$cnstr])) return array();
		$re = $na[$cnstr];
		$re['nodemode'] = $NodeMode;//将是否手机节点作为节点内的标记
		return LoadCnodeConfig($re);
	}
	
	/**
	 * 得到类目节点生成静态的文件格式(相对系统根目录)，格式有唯一变量{page}(页码)留到分页时解释
	 *
	 * @param  string  $cnstr  节点字串
	 * @param  int     $addno  附加页
	 * @return array   &$cnode 节点配置信息
	 * @return string  类目节点生成静态的文件格式
	 */
	public static function cn_format($cnstr,$addno,&$cnode){//含{$page}的节点文件(相对系统根目录)
		global $cn_urls;
		if(!$cnstr || !$cnode || !empty($cnode['NodeMode'])) return '';
		if(!isset($cnode['_cf'])){
			$cndirarr = CnodeFormatDirArray($cnstr);
			for($i = 0;$i <= @$cnode['addnum'];$i ++){
				$u = empty($cnode['cfgs'][$i]['url']) ? (empty($cn_urls[$i]) ? '{$cndir}/index'.($i ? $i : '').'_{$page}.html' : $cn_urls[$i]) : $cnode['cfgs'][$i]['url'];
				$cnode['_cf'][$i] = cls_url::m_parseurl($u,$cndirarr);
			}
		}
		return isset($cnode['_cf'][$addno]) ? $cnode['_cf'][$addno] : '';
	}
	
	/**
	 * 根据会员节点字串，得到节点名称(不包含自定义节点)
	 *
	 * @param  string  $cnstr  节点字串
	 * @return string  $title  节点名称
	 */
	public static function mcnode_cname($cnstr){
		$arr = explode('=',$cnstr);
		if(!($mcnvar = trim(@$arr[0])) || !($mcnid = max(0,intval(@$arr[1]))) || ($mcnvar == 'mcnid')) return '';
		if($mcnvar == 'caid'){
			$tvar = 'title';
			$narr = cls_cache::Read('catalogs');
		}elseif(in_str('ccid',$mcnvar)){
			$tvar = 'title';
			$narr = cls_cache::Read('coclasses',str_replace('ccid','',$mcnvar));
		}elseif(in_str('ugid',$mcnvar)){
			$tvar = 'cname';
			$narr = cls_cache::Read('usergroups',str_replace('ugid','',$mcnvar));
		}
		return $narr[$mcnid][$tvar];
	}


	/**
	 * 说明：
	 *
	 * @param  string  $cnstr  节点字串
	 * @param  int     $addno  附加页
	 * @return ---     ---     ---
	 */
	public static function mcn_format($cnstr = '',$addno = 0){//含{$page}的节点文件(相对系统根目录)
		global $memberdir,$homedefault;
		if(!$cnstr) return $memberdir.'/'.$homedefault;
		$cnode = self::read_mcnode($cnstr);
		return $memberdir.'/'.cls_url::m_parseurl(empty($cnode['cfgs'][$addno]['url']) ? '{$cndir}/index'.($addno ? $addno : '').'_{$page}.html' : $cnode['cfgs'][$addno]['url'],array('cndir' => mcn_dir($cnstr),));
	}
	
	/**
	 * 取出所有关联类目的ID及标题，并通过$listby指定其中一个类目取出其完全资料
	 * 以caid=2&ccid1=5为例，$listby为-1时，以最后一个类目(ccid1=5)为完全资料，0则栏目(caid=2)为完全资料，1则分类(ccid1=5)为完全资料
	 *
	 * @param  string  $cnstr   节点字串
	 * @param  int     $listby  所列信息选项 -1以最后一个类目为完全资料,0指定列栏目，x(数字)以某类系(x)中的分类为完全资料
	 * @return array   $re      
	 */
	public static function cn_parse($cnstr,$listby=-1){
		parse_str($cnstr,$idsarr);
		$num = count($idsarr);
		$re = array();
		$i = 0;
		foreach($idsarr as $k => $v){
			$i ++;
			$coid = $k == 'caid' ? 0 : intval(str_replace('ccid','',$k));
			if($item = $coid ? cls_cache::Read('coclass',$coid,$v) : cls_cache::Read('catalog',$v)){
				$re[$coid ? "ccid$coid" : 'caid'] = $v;//id
				$re[$coid ? 'ccid'.$coid.'title' : 'catalog'] = $item['title'];//标题
				if((($listby == -1) && $i == $num) || (($listby >=0) && $listby == $coid)){//完全资料
					$re += $item;
				}
			}
		}
		return $re;
	}
	
	/**
	 * 列出会员节点所有 列栏/类系/组系 项目相关信息
	 *
	 * @param  string  $cnstr   节点字串
	 * @return array   $ret     列栏/类系/组系 相关信息
	 */
	public static function m_cnparse($cnstr){//得到初始的资料
		$var = array_map('trim',explode('=',$cnstr));
		$ret = array($var[0] => $var[1]);
		if($var[0] == 'mcnid'){
		}elseif($var[0] == 'caid'){
			$ret += cls_cache::Read('catalog',$var[1],0);
		}elseif(in_str('ccid',$var[0])){
			$ret += cls_cache::Read('coclass',str_replace('ccid','',$var[0]),$var[1]);
		}elseif(in_str('ugid',$var[0])){
			$ret += cls_cache::Read('usergroup',str_replace('ugid','',$var[0]),$var[1]);
		}
		if(empty($ret['cname'])) $ret['cname'] = @$ret['title'];
		return $ret;
	}
	
	/**
	 * 将类目节点中的相关值传递给具体资料数组$item
	 *
	 * @param  array   &$item  具体资料数组，在前台为原始标识的数据来源
	 * @param  string  $cnstr  节点字串
	 * @param  array   &$cnode 节点配置信息，此前需要已经生成节点各附加页的url
	 * @return ---     ---     ---
	 */
	public static function re_cnode(&$item,$cnstr,&$cnode){
		if(!isset($cnode['indexurl'])) cls_url::view_cnurl($cnstr,$cnode);
		for($i = 0;$i <= @$cnode['addnum'];$i ++) $item['indexurl'.($i ? $i : '')] = $cnode['indexurl'.($i ? $i : '')];
		$item['alias'] = empty($cnode['alias']) ? @$item['title'] : $cnode['alias'];
		$item['rss'] = cls_url::view_url('rss.php'.($cnstr ? "?$cnstr" : ''),FALSE);
	}

	function AddOneCnode($cnstr,$tid = 0,$oldupdate = 0,$NodeMode = 0){//$NodeMode为1常规节点，1为手机节点
		global $cn_max_addno,$db,$tblprefix,$timestamp;
		$tbl = $NodeMode ? 'o_cnodes' : 'cnodes';
		$NodeArray = cls_cache::Read($tbl);
		if(!$cnstr) return false;
		if(empty($NodeArray[$cnstr])){
			parse_str($cnstr,$arr);
			if(!$arr) return false;
			$sqlstr = "ename='$cnstr',cnlevel='".count($arr)."',tid='$tid'";
			foreach($arr as $k => $v) $sqlstr .= ",$k='$v'";
			if(!$NodeMode){
				$needstatics = '';for($i = 0;$i <= $cn_max_addno;$i ++) $needstatics .= $timestamp.',';
				$sqlstr .= ",needstatics='$needstatics'";
			}
			$db->query("INSERT INTO {$tblprefix}$tbl SET $sqlstr",'SILENT');
		}elseif($oldupdate) $db->query("UPDATE {$tblprefix}$tbl SET tid='$tid' WHERE ename='$cnstr' AND keeptid=0");
		return true;
	}

}
