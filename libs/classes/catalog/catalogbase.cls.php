<?php
/**
* 有关栏目(coid=0)与分类的处理方法
* 本类为cls_catalog的基类
* 以后规划本类拆分出cls_CatalogConfig作为后台栏目配置专用，简化当前基类，利用前台调用。
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
class cls_catalogbase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($coid = 0,$ccid = 0,$Key = ''){
		$coid = (int)$coid;
		$re = $coid ? cls_cache::Read('coclasses',$coid) : cls_cache::Read('catalogs');
		if($ccid){
			$ccid = (int)$ccid;
			$re = isset($re[$ccid]) ? $re[$ccid] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	public static function Table($coid = 0,$NoPre = false){
		$coid = (int)$coid;
		return ($NoPre ? '' : '#__').($coid ? "coclass$coid" : 'catalogs');
	}
	
	# 返回 ID=>名称 的列表数组，来源为栏目或分类资料数组
	public static function ccidsarrFromArray(array $SourceArray,$chid = 0,$nospace = 0){
		$re = array();
		foreach($SourceArray as $k => $v){
			if(!$chid || in_array($chid,explode(',',$v['chids']))){
				if(!$nospace){
					$v['title'] = str_repeat('&nbsp; &nbsp; ',$v['level']).$v['title'];
				}
				$re[$k] = $v['title'];
			}
		}
		return $re;
	}
	
	# 推送分类到指定推送位
	public static function push($coid,$ccid,$paid){
		if($Config = cls_catalog::Config($coid,$ccid)){
			return cls_pusher::push($Config,$paid);
		}else return false;
	}
	
	# 返回 ID=>名称 的列表数组，来源为指定的类系ID
	public static function ccidsarr($coid = 0,$chid = 0,$nospace = 0){
		$coid = (int)$coid;
		if($coid){
			$cotypes = cls_cache::Read('cotypes');
			if(empty($cotypes[$coid])) return array();
			if($cotypes[$coid]['self_reg']) $chid = 0;
		}
		$SourceArray = cls_catalog::Config($coid);
		return cls_catalog::ccidsarrFromArray($SourceArray,$chid,$nospace);
	}
	
	public static function Key($coid = 0){
		$coid = (int)$coid;
		return $coid ? 'ccid' : 'caid';
	}
	
	# 更新缓存
	public static function UpdateCache($coid = 0){
		$coid = (int)$coid;
		$CacheArray = cls_catalog::CacheArray($coid);
		$cndirnames = $cnsonids = array();
		foreach($CacheArray as $k => $v){
			if(empty($k) || (!empty($v['pid']) && empty($CacheArray[$v['pid']]))){ //父分类被关闭或数据库手动删除
				unset($CacheArray[$k]);
				continue;
			}
			if($k==$v['pid']){ //数据出问题，出现$v['ccid']==$v['pid']情况，导致死循环
				unset($CacheArray[$k]);	
			}
			$TopID = cls_catalog::cn_upid($k,$CacheArray);
			$cndirnames[$k] = array('s' => $v['dirname']);
			if($TopID != $k) $cndirnames[$k]['p'] = $CacheArray[$TopID]['dirname'];
			if(!empty($v['customurl'])) $cndirnames[$k]['u'] = $v['customurl'];
			if($pids = cls_catalog::PccidsByAarry($k,$CacheArray)){
				foreach($pids as $x){
					$cnsonids[$x][] = $k;
				}
			}
		}
		foreach($cnsonids as $k => $v){
			$cnsonids[$k] = implode(',',$v);
		}
		cls_CacheFile::Save($cnsonids,"cnsonids$coid"); //整站未搜索到…，sonbycoid()这个里面是否在使用？
		cls_CacheFile::Save($cndirnames,"cndirnames$coid");
		cls_CacheFile::Save(cls_catalog::DirnameArray(),"cn_dirnames");
		cls_CacheFile::Save($CacheArray,$coid ? "coclasses$coid" : 'catalogs');
		
		
	}
	# 从数据库获得生成缓存所需要的数组
	public static function CacheArray($coid = 0){
		$coid = (int)$coid;
		$CacheArray = array();
		$db = _08_factory::getDBO();
		$db->select('*')->from(cls_catalog::Table($coid))->where(array('closed' => 0))->order('trueorder,'.cls_catalog::Key($coid))->exec();
		$UnsetVars = cls_catalog::_CacheUnsetVars($coid);
		while($r = $db->fetch()){
			if($coid){
				cls_CacheFile::ArrayAction($r,'conditions','unserialize');
			}
			
			if(!empty($UnsetVars['Del'])){
				foreach($UnsetVars['Del'] as $z){
					unset($r[$z]);
				}
			}
			if(!empty($UnsetVars['DelEmpty'])){
				foreach($UnsetVars['DelEmpty'] as $z){
					if(empty($r[$z])) unset($r[$z]);
				}
			}
			cls_url::arr_tag2atm($r,$coid ? 'cc' : 'ca');
			$CacheArray[$r[cls_catalog::Key($coid)]] = $r;
		}
		return $CacheArray;
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	# 按父子结构进行排序
	public static function InitialInfoArray($coid = 0,$IncludeClosed = 0,$orderby = 'trueorder'){
		$coid = (int)$coid;
		$re = array();
		$db = _08_factory::getDBO();
		$db->select('*')->from(cls_catalog::Table($coid));
		if(!$IncludeClosed) $db->where(array('closed' => 0));
		$db->order($orderby.','.cls_catalog::Key($coid))->exec();
		$TrueOrderArray = array();
		$NeedOrder = $orderby != 'trueorder' ? true : false; # 生成缓存时强制重新排序
		while($r = $db->fetch()){
			if($coid){
				cls_CacheFile::ArrayAction($r,'conditions','unserialize');
			}			
			
			$re[$r[cls_catalog::Key($coid)]] = $r;
			
			# 分析是否需要重新排序，如果已经排好序，trueorder是唯一的，否则需要重新排序
			if(!$NeedOrder){
				if(in_array($r['trueorder'],$TrueOrderArray)){
					$NeedOrder = true;
				}else $TrueOrderArray[] = $r['trueorder'];
			}
		}
		
		if($NeedOrder){ # 需要重新排序
			$re = cls_catalog::OrderArrayByPid($re,0);
		}
		return $re;
	}
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($coid = 0,$id = 0){
		if(!($id = (int)$id)) return array();
		$coid = (int)$coid;
		$db = _08_factory::getDBO();
		$re = $db->select('*')->from(cls_catalog::Table($coid))->where(array(cls_catalog::Key($coid) => $id))->exec()->fetch();
		return $re ? $re : array();
	}
	/**
	 * 将分类数组$SourceArray中的索引ID根据上下级嵌套的关系(pid)进行重新排序，返回排序后的索引ID数组/详细资料数组
	 * 注意：本方法比较费资源(可能包含次数较多的循环与递归)，请注意使用场合，通常用于非缓存方式的数据处理。
	 *
	 * @param  array  $SourceArray  	来源数组，需要是pid来表示父子结构的数组
	 * @param  int/string    $Pid				指定父id,通常0-从顶级开始
	 * @param  int    $OnlyReturnID		为1时返回索引ID数组，否则返回详细资料数组
	 * @return array  $OrderIDs  		返回结果数组
	 */
	public static function OrderArrayByPid(array $SourceArray,$Pid = 0,$OnlyReturnID = 0){
		$OrderArray = array();
		foreach($SourceArray as $k => $v){
			if($v['pid'] == $Pid){
				if($OnlyReturnID){
					$OrderArray[] = $k;
				}else{
					$OrderArray[$k] = $v;
				}
				if($re = cls_catalog::OrderArrayByPid($SourceArray,$k,$OnlyReturnID)){
					foreach($re as $_k => $_v){
						if($OnlyReturnID){
							$OrderArray[] = $_v;
						}else{
							$OrderArray[$_k] = $_v;
						}
					}
				}
			}
		}
		return $OrderArray;
	}
	
	/**
	 * 在$SourceArray中取得$Pid及其子分类ID，
	 * 注意：本方法比较费资源(可能包含次数较多的循环与递归)，请注意使用场合，通常用于非缓存方式的数据处理。
	 *
	 * @param  array  $SourceArray  来源数组，数组中以pid记录了父id属性
	 * @param  int    $Pid		    指定的父ID
	 * @return array  				返回包含自身ID及其所有子分类ID(递归)
	 */
	public static function cnsonids($Pid,$SourceArray){
		if(!$Pid) return array();
		return array_merge(array($Pid),cls_catalog::OrderArrayByPid($SourceArray,$Pid,1));
	}
    /**
     * 取得栏目及各类系类目所占用的dirname(静态路径)数组
     * 
     * @return array  				返回栏目及各类系类目所占用的dirname数组
     */ 
	public static function DirnameArray(){
        $db = _08_factory::getDBO();
        $tblprefix = cls_envBase::getBaseIncConfigs('tblprefix');
		$cotypes = cls_cache::Read('cotypes');
		$vars = array_keys($cotypes);
		$vars[] = 0;
		$ret = array();
		foreach($vars as $k => $v){
			$query = $db->query("SELECT dirname FROM $tblprefix".($k ? "coclass$k" : "catalogs"),'SILENT');
			while($r = @$db->fetch_array($query)) in_array($r['dirname'],$ret) || $ret[] = $r['dirname'];
		}
		return $ret;
	}

	/**
	 * 说明：通过自动条件类系 组sql子句
	 * Demo: self_sqlstr(5,180,'a.')  -=>  ((a.zj >= '1000' and a.zj <= '2000'))
	 *
	 * @param  int     $coid   类系项目ID
	 * @param  int     $ccids  类系ID
	 * @param  string  $pre    sql字段前缀,如a.
	 * @return string  $sqlstr 组成后的sql子句
	 */
	public static function SelfClassSql($coid,$ccids,$pre = ''){
		global $timestamp;
		$sqlstr = '';
		if(empty($ccids)) return $sqlstr;
		if(!is_array($ccids)) $ccids = array($ccids);
		$multi = 0;
		foreach($ccids as $ccid){
			$sqlstr1 = '';
			if(!($coclass = cls_cache::Read('coclass',$coid,$ccid)) || empty($coclass['conditions'])) continue;
			foreach(array('createdate','clicks','prices',) as $var){
				if(isset($coclass['conditions'][$var.'from'])) $sqlstr1 .= ($sqlstr1 ? ' AND ' : '').$pre.$var.">='".$coclass['conditions'][$var.'from']."'";
				if(isset($coclass['conditions'][$var.'to'])) $sqlstr1 .= ($sqlstr1 ? ' AND ' : '').$pre.$var."<'".$coclass['conditions'][$var.'to']."'";
			}
			if(isset($coclass['conditions']['indays'])) $sqlstr1 .= ($sqlstr1 ? ' AND ' : '').$pre."createdate>='".($timestamp - 86400 * $coclass['conditions']['indays'])."'";
			if(isset($coclass['conditions']['outdays'])) $sqlstr1 .= ($sqlstr1 ? ' AND ' : '').$pre."createdate<'".($timestamp - 86400 * $coclass['conditions']['outdays'])."'";
			if(isset($coclass['conditions']['sqlstr'])){
				$coclass['conditions']['sqlstr'] = stripslashes(str_replace('{$pre}',$pre,$coclass['conditions']['sqlstr']));
				$sqlstr1 .= ($sqlstr1 ? ' AND ' : '').'('.$coclass['conditions']['sqlstr'].')';
			}
			($sqlstr1 && $sqlstr) && $multi = 1;
			$sqlstr1 && $sqlstr .= ($sqlstr ? ' OR ' : '').'('.$sqlstr1.')';
		}
		$multi && $sqlstr = '('.$sqlstr.')';
		return $sqlstr;
	}
    /**
     * 追溯指定分类的所有上级id
     * 
     * @param int		$ccid		指定分类id
     * @param string	$coid		指定类系id，如栏目则为0
     * @param string	$self		是否包含指定id本身
     * @return array  				返回所有父id数组，排序由上而下
     */ 
	public static function Pccids($ccid = 0,$coid = 0,$self = 0){
		$re = array();
		if(!$ccid) return $re;
		if($arr = cls_catalog::Config($coid)){
			$ccid0 = $ccid;
			for($i = @$arr[$ccid0]['level']; $i > 0; $i--) $re[] = $ccid = $arr[$ccid]['pid'];
			count($re) > 1 && $re = array_reverse($re);
			if($self == 1) $re[] = $ccid0;
		}
		return $re;
	}
	
    /**
     * 重写类目表的tureorder排序字段
     * 
     * @param string	$coid		指定类系id，如栏目则为0
     */ 
	public static function DbTrueOrder($coid=0){
		$coid = (int)$coid;
		$na = cls_catalog::InitialInfoArray($coid,1,'vieworder');
		$db = _08_factory::getDBO();
		$i = 0;
		foreach($na as $k => $v){
			if($v['trueorder'] != $i){
				$db->update(cls_catalog::Table($coid),array('trueorder' => $i))->where(array(cls_catalog::Key($coid) => $k))->exec();
			}
			$i ++;
		}
	}
	
    /**
     * 指定id的所有上级id（通过传入的原始数组获取）
     * 
     * @param int		$ccid		指定分类id
     * @param array		$cnArray	栏目或某类系分类的缓存数组
     * @return array  				返回所有父id数组，排序由下而上
     */ 
	public static function PccidsByAarry($ccid,$cnArray = array()){
		$re = array();
		while(isset($cnArray[$ccid]['pid'])){ 
			//数据出问题，出现$v['ccid']==$v['pid']情况，导致死循环
			if($ccid==$cnArray[$ccid]['pid']){ 
				unset($cnArray[$ccid]); 
				continue; 
			}else{
				$re[] = $ccid = $cnArray[$ccid]['pid'];
			}
		}
		return $re;
	}
		
	/**
	 * 取得指定类目的所有下级id(仅下级)
	 *
	 * @param int $nowid 当前类目id
	 * @param int $coid 类系id，0指栏目
	 * @return array 返回$nowid的所有下级类目
	 */
	public static function son_ccids($nowid,$coid = 0){
		$re = array();
		if(!$nowid) return $re;
		if($sonids = sonbycoid($nowid,$coid,0)){
			$na = cls_catalog::Config($coid);
			foreach($sonids as $k){
				if(@$na[$k]['pid'] == $nowid) $re[] = $k;
			}
		}
		return $re;
	}
	
	/**
	 * 取得某个类目的指定级(level)的上级类目
	 *
	 * @param int $nowid 当前类目id
	 * @param int $coid 类系id，0指栏目
	 * @param int $level 第几级，0指顶级
	 * @return int 返回第$level级的上级类目id
	 */
	public static function p_ccid($nowid,$coid = 0,$level = 0){
		if(!$nowid) return 0;
		if(!($na = cls_catalog::Config($coid))) return 0;
		return cls_catalog::cn_upid($nowid,$na,$level);
	}
	
	/**
	 * 说明：返加指定第几级(level)的父id，
	 *
	 * @param  int      $id     
	 * @param  array    &$arr   
	 * @param  int      $level，如level=0(顶级)，表示返回指定id的顶级父id，当id本身为顶级时，返回id本身
	 * @return int      ---      
	 */
	public static function cn_upid($id,&$arr,$level=0){
		if(empty($arr[$id])) return 0;
		return $arr[$id]['level'] < $level ? 0 : (empty($arr[$id]['pid']) || $arr[$id]['level'] == $level ? $id : cls_catalog::cn_upid($arr[$id]['pid'],$arr,$level));
	}

	/**
	 * 获取类系名称或图标
	 *
	 * @param  int    $id        类系id
	 * @param  bool   $mode      是否为多选方式
	 * @param  array  $sarr      类系数组
	 * @param  int    $num       最多多少个
	 * @param  bool   $showmode  是否为图标
	 * @return strin  $ret       类系名称或图标
	 */
	public static function cnstitle($id,$mode,$sarr,$num=0,$showmode=0){
		if(!$id || !$sarr) return '';
		if(!$mode && !$showmode) return @$sarr[$id]['title'];
		$ids = array_filter(explode(',',$id));
		$ret = '';$i = 0;
		foreach($ids as $k){
			if($num && $num >= $i) break;
			$ret .= $showmode ? '<img src="'.@$sarr[$k]['icon'].'" title="'.@$sarr[$k]['title'].'" width="20" height="20" />' : ','.@$sarr[$k]['title'];
		}
		return $showmode ? $ret : substr($ret,1);
	}
	
	// 当顶级只有一个选项时，隐藏顶级类别，可少选一个select。
	// 有没有这个情况，这个顶级本身可选,子类别也可选？
	// 一般用于 _08cms.fields.linkage()，多级下拉...
	public static function uccidstop(&$arr){
		if(empty($arr)) return;
		$f0 = 0; $f1 = 0;
		foreach($arr as $k=>$v){ 
			if(empty($v['pid'])) $f0++;
			if($v['pid']>0) $f1++;
			if($f0>1) break;
		} 
		$pid = 0; 
		if($f0==1 && $f1>0){  //只有个顶级类别，且含有子类别
			foreach($arr as $k=>$v){
				if(empty($v['pid'])){ 
					if(empty($v['unsel'])) break; //如果可选，则跳出来，不处理了。
					unset($arr[$k]);
					$pid = $k;
				}else{ 
					if($pid && $v['pid']==$pid){ 
						$arr[$k]['pid'] = 0;
					}
				}
			}
		} //print_r($arr);
	}
	
	/**
	 * 获取符合条件的类系/栏目数组
	 *
	 * @param  int    $coid     类系项目id
	 * @param  int    $chid     模型ID
	 * @param  int    $framein  包含结构性分类
	 * @param  int    $nospace  是否加空格'&nbsp; '
	 * @param  int    $viewp    0-根据catahidden清掉无效类目，1-需要pid资料，不清除无效类目但设为unsel,-1完全清除无效类目
	 * @param  int    $id       指定的栏目ID值或者类系ID值，20121204 新增
	 * @return array  $caccnt   返回的数组，仅包含id，
	 */
	public static function uccidsarr($coid,$chid = 0,$framein = 0,$nospace = 0,$viewp = 0,$id=0){
		global $catahidden;
		$cotypes = cls_cache::Read('cotypes');
		$rets = array();
		if($coid && empty($cotypes[$coid])) return $rets;
		if($id){
			$idsr = sonbycoid($id ,!$coid?0:$coid,1);
			#$idsr = sonbycoid($id ,!$coid?0:$coid,0); //找出指定栏目或类系所有子栏目或子类系(不包含父ID)
			//获取父ID的初始级别
			#$r = cls_cache::Read('catalog',$id,'');
			#$level = $r['level'];
		}
	
		$arr = cls_catalog::Config($coid);
		foreach($arr as $k => $v){
			$ccprefix = '';
			if($id){
				#$levtemp = $level;   //指定栏目的初始级别；
				if(in_array($k,$idsr)){
					if(isset($v['letter']) && $coid && $v['pid']==0 && $v['letter']){
						$ccprefix = $v['letter'].' ';
					}
					$rets[$k]['title'] = ($nospace ? '' : str_repeat('&nbsp; ',$v['level'])).$ccprefix.$v['title'];
					$ids = !empty($v['chids']) ? explode(',',$v['chids']) : array();
					if(($chid && !in_array($chid,$ids)) || (!$framein && $v['isframe'])){//不可选的类目
						if((!$catahidden && $viewp != -1) || $viewp == 1){
							$rets[$k]['unsel'] = 1;
						}else unset($rets[$k]);
					}
					if($viewp == 1){   //显示pid
						 $rets[$k]['pid'] = $v['pid'];
						 //将指定ID的pid赋值为0
						 $rets[$id]['pid'] = 0;
					}
					#$rets[$k]['pid'] = $v['pid'];
					//初始级别+1 指向下一个级别，将父ID设为0
					#$rets[$k]['level'] = $v['level'];
					#if($rets[$k]['level'] == ($levtemp+1)) $rets[$k]['pid'] = 0;
					//将指定级别的pid赋值为0
	
				}
			}else{
				if(isset($v['letter']) && $coid && $v['pid']==0 && $v['letter']){
					$ccprefix = $v['letter'].' ';
				}
				$rets[$k]['title'] = ($nospace ? '' : str_repeat('&nbsp; ',$v['level'])).$ccprefix.$v['title'];
				$ids = !empty($v['chids']) ? explode(',',$v['chids']) : array();
				if(($chid && !in_array($chid,$ids)) || (!$framein && $v['isframe'])){//不可选的类目
					if((!$catahidden && $viewp != -1) || $viewp == 1){
						$rets[$k]['unsel'] = 1;
					}else unset($rets[$k]);
				}
				if($viewp == 1) $rets[$k]['pid'] = $v['pid'];
			}
		}
		return $rets;
	}
	# 缓存数组需要排除的字段
	protected static function _CacheUnsetVars($coid = 0){
		$coid = (int)$coid;
		$UnsetVars = array();
		
		# 删除为空的值
		if($coid){
			$UnsetVars['DelEmpty'] = array('groups','trueorder','closed','conditions',);
		}else{
			$UnsetVars['DelEmpty'] = array('trueorder','closed','dpmid','ftaxcp',);
		}
		
		# 不管是否为空，强行删除
		$UnsetVars['Del'] = array();
		
		return $UnsetVars;
	}
		
}
