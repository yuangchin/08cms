<?php
/* 
** 会员空间专用的方法(也可能是其它针对指定会员的方法)，是Mspace.cls.php的基类
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_Mspacebase{
	static protected $UcalssesArray = array(); # 暂存不同会员的个人分类资料，以备重用
	static protected $McatalogsArray = array(); # 暂存不同方案的空间栏目数组，以备重用
	
	/**
	 * 会员空间类目页url
	 *
	 * @param  array	$info		指定会员的主表信息数组
	 * @param  array	$params		指定的更多属性，如mcaid(空间栏目)，addno(附加页)，ucid(空间栏目内的个人分类)
	 * @param  bool		$dforce		强制返回动态格式
	 * @return string      			返回会员空间url
	 */
	public static function IndexUrl($info,$params = array(),$dforce = false){//$dforce强制动态
		if(!$info['mid']) return '';
		if(!$dforce && array_diff_key($params,array('mid' => '','mcaid' => '','ucid' => '','addno' => '',))) $dforce = true;
		if(!$dforce && (empty($info['mspacepath']) || empty($info['msrefreshdate']))) $dforce = true; #未设置静态目录或未生成静态
		$mindex = MspaceIndexFormat($info,$params,$dforce,1); 
		$mindex = $dforce ? cls_env::mconfig('cms_abs').$mindex : cls_url::view_url($mindex); //动态页不要绑域名
		return $mindex; // cls_url::view_url(MspaceIndexFormat($info,$params,$dforce,1));
	}
	
	/**
	 * 会员空间是否允许静态
	 *
	 * @param  array	$info		指定会员的主表信息数组
	 * @return string      			返回不允许生成静态的原因，允许生成静态时返回false
	 */
	public static function AllowStatic($info){
		if(empty($info['mid'])) return '未指定会员';
		if(empty($info['mspacepath'])) return "会员{$info['mid']}未设置空间静态目录";
		$mspacepmid = cls_env::mconfig('mspacepmid');
		if(!$mspacepmid || cls_Permission::noPmReason($info,$mspacepmid)){ # 空间静态权限
			return "会员{$info['mid']}没有生成静态空间的权限";
		}
		return false;
	}
	
	# 会员空间加载空间主会员资料，可直接在模板内用原始标签调用
	# $ttl缓存周期，单位:秒
	# $ischeck 是否读取未审核会员(cls_MspaceIndexBase::_MainData使用了0参数)
	public static function LoadMember($mid = 0, $ttl = 60, $ischeck=1){
		global $db,$tblprefix;
		$re = array();
		if(!($mid = max(0,intval($mid)))) return $re;
		$checkstr = $ischeck ? "AND m.checked=1" : "";
		if($re = $db->fetch_one("SELECT m.*,s.* FROM {$tblprefix}members m INNER JOIN {$tblprefix}members_sub s ON s.mid=m.mid WHERE m.mid='$mid' $checkstr",$ttl)){
			if($InfoChannel = $db->fetch_one("SELECT * FROM {$tblprefix}members_{$re['mchid']} WHERE mid='$mid'",$ttl)){
				$re = array_merge($re,$InfoChannel);
			}
			$re['mspacehome'] = cls_Mspace::IndexUrl($re);
			cls_url::arr_tag2atm($re,'m'); # 转换html字段内的附件url
		}
		return $re;
	}
	
	# 取得会员空间的文档内容页模板
	# $mtcid空间模板方案id，$chid文档模型id，$addno附加页id
    public static function ArchiveTplname($mtcid = 0,$chid = 0,$addno = 0){
		if(!$mtcid) return '';
		$arctpls = cls_mtconfig::Config($mtcid,'arctpls');
		$addno = max(0,intval($addno));
		$chid = max(0,intval($chid));
		$type = $addno ? "ex$addno" : 'archive';
		return empty($arctpls[$type][$chid]) ? '' : $arctpls[$type][$chid];
    }
	
	# 取得会员空间的栏目页模板
	# $mtcid空间模板方案id，$Params:需要包含mcaid,addno的值
    public static function IndexTplname($mtcid = 0,$Params = array()){
		if(!($mtcid = max(0,intval($mtcid)))) return '';
		$_msTpls = cls_mtconfig::Config($mtcid,'setting');
		if(empty($Params['mcaid'])){ # 首页
			$tplname = @$_msTpls[0]['index'];
		}else{ # 栏目页
			$tplname = @$_msTpls[$Params['mcaid']][empty($Params['addno']) ? 'index' : 'list'];
		}
		return $tplname ? $tplname : '';
    }
	
	# 获取指定会员的个人分类资料
	# $ttl缓存周期，单位:秒
	public static function LoadUclasses($mid = 0,$ttl = 60){
		if(!($mid = max(0,intval($mid)))) return array();
		if(isset(self::$UcalssesArray[$mid])){
			return self::$UcalssesArray[$mid];
		}else{
			global $db,$tblprefix;
			$re = array();
			$na = $db->ex_fetch_array("SELECT * FROM {$tblprefix}uclasses WHERE mid='$mid' ORDER BY vieworder",$ttl);
			foreach($na as $v){
				$re[$v['ucid']] = $v;
			}
			self::$UcalssesArray[$mid] = $re;
			return $re;
		}
	}
	
	# 获取指定模板方案的空间栏目数组
	public static function LoadMcatalogs($mtcid = 0){
		if(!($mtcid = max(0,intval($mtcid)))) return array();
		if(isset(self::$McatalogsArray[$mtcid])){
			return self::$McatalogsArray[$mtcid];
		}else{
			$re = array();
			if($_msTpls = cls_mtconfig::Config($mtcid,'setting')){
				if($mcatalogs = cls_mcatalog::Config()){
					$re = array_intersect_key($mcatalogs,$_msTpls);
				}
			}
			self::$McatalogsArray[$mtcid] = $re;
			return $re;
		}
	}
	
	# 空间栏目页中补充可用于原始标签调用的资料数组
	# $info为空间主会员资料，$Params包含mcaid,ucid,addno等页面参数
	public static function IndexAddParseInfo($info = array(),$Params=array()){
		if(empty($info['mid'])) return array();
		$nowMcatalogs = cls_Mspace::LoadMcatalogs($info['mtcid']);
		if(!empty($Params['ucid'])){//两种属性
			$nowUclasses = cls_Mspace::LoadUclasses($info['mid']);
			if(!empty($nowUclasses[$Params['ucid']])){
				$re = $nowUclasses[$Params['ucid']];
				$re['mcatalog'] = @$nowMcatalogs[$re['mcaid']]['title'];
				$re['uclass'] = $re['title'];
			}
		}elseif(!empty($Params['mcaid'])){
			if(!empty($nowMcatalogs[$Params['mcaid']])){
				$re = $nowMcatalogs[$Params['mcaid']];
				$re['mcatalog'] = $re['title'];
				$re['uclass'] = '';
			}
		}else{
			$re = array('mcatalog' => '','uclass' => '',);
		}
		foreach(array(0,1) as $k){
			$Params['addno'] = $k; 
			$re['indexurl'.($k ? $k : '')] = cls_Mspace::IndexUrl($info,$Params);
		}
		return $re;
	}
	
	# 生成(更新)指定会员($mid)的静态空间
	public static function ToStatic($mid = 0){
		if(!($info = cls_Mspace::LoadMember($mid))) return '未指定会员';
		if($re = cls_Mspace::AllowStatic($info)) return $re; # 指定会员是否允许生成静态空间
		
		$arr = array();
		
		# 生成空间首页
		$arr[] = cls_MspaceIndex::Create(array('mid' => $mid,'inStatic' => true));
		
		# 生成空间栏目页
		$nowMcatalogs = cls_Mspace::LoadMcatalogs($info['mtcid']);
		foreach($nowMcatalogs as $k => $v){
			if(!empty($v['dirname'])){
				foreach(array(0,1) as $x){
					$arr[] = cls_MspaceIndex::Create(array('mid' => $mid,'mcaid' => $k,'ucid' => 0,'addno' => $x,'inStatic' => true));	
				}
			}
		}
		
		# 生成每个栏目下的个人分类页
		$nowUclasses = cls_Mspace::LoadUclasses($mid);
		foreach($nowUclasses as $k => $v){
			if(!empty($nowMcatalogs[$v['mcaid']]['dirname'])){
				foreach(array(0,1) as $x){
					$arr[] = cls_MspaceIndex::Create(array('mid' => $mid,'mcaid' => $v['mcaid'],'ucid' => $k,'addno' => $x,'inStatic' => true));	
				}
			}
		}
		
		# 统计生成消息
		$num = 0;$size = 0;$time = 0;
		foreach($arr as $k => $v){
			if(empty($v['error'])){
				$num += $v['num'];
				$size += $v['size'];
				$time += $v['time'];
			}
		}
		return "共生成 $num 个文档，$size 字节，用时 $time 秒";
	}
	
	
	
}
