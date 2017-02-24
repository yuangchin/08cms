<?php
/* 
** 副件分类的方法汇总
** 配置储存于模板目录，应用缓存与数据源是同一个,数据源读取本地文件，不从扩展缓存(memcached)中读取。
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）。
*/
!defined('M_COM') && exit('No Permission');
class cls_fcatalogbase{
	
	# 读取配置，通常以缓存的方式来读取
	# 允许读取：全部配置数组，指定ID的配置，指定ID及KEY的配置
    public static function Config($fcaid = '',$Key = ''){
		$re = cls_cache::Read(cls_fcatalog::CacheName());
		if($fcaid){
			$fcaid = cls_fcatalog::InitID($fcaid);
			$re = isset($re[$fcaid]) ? $re[$fcaid] : array();
			if($Key){
				$re = isset($re[$Key]) ? $re[$Key] : '';
			}
		}
		return $re;
    }
	
	# 按副件分类读取字段配置
    public static function Field($fcaid = '',$FieldName = ''){
		$chid = cls_fcatalog::Config($fcaid,'chid');
		$re = cls_fchannel::Field($chid,$FieldName);
		return $re;
		
    }
	
	# 副件编辑/搜索/批量设置,显示地区选项Multi-Select --- 暂时放在这里
    public static function areaShow($fcaid, $Values='', $re='Edit', $FormVar='fmdata[farea]', $Title='关联地区'){
		$cfg = cls_fcatalog::Config($fcaid); 
		if(empty($cfg['farea'])) return;
		$DataStr = '';  $DataArr = array('0' => array('title' => $Title ));
		$coclasses =  cls_catalog::Config((int)$cfg['farea']);
		foreach($coclasses as $k => $v){
			if(!empty($v['level'])) continue;
			$DataStr .= "[$k,$v[pid],'".addslashes($v['title'])."',".(empty($v['unsel']) ? 0 : 1) . '],';
			$DataArr[$k] = $v;
		}
		if($re=='Search'){ //搜索
			return "<select style=\"vertical-align: middle;\" id=\"$FormVar\" name=\"$FormVar\">".umakeoption($DataArr,$Values)."</select>";
		}elseif($re=='Sets'){ //批量设置
			$opMod = "<select id=\"mode_$FormVar\" name=\"mode_$FormVar\" style=\"vertical-align: middle;\">".makeoption(array(0 => '重设',1 => '追加',2 => '移除',),1)."</select> &nbsp;";
			$opOpt = "<script>var data = [$DataStr];\n_08cms.fields.linkage('$FormVar', data, '$Values',40);</script>";
			trbasic("<input class=\"checkbox\" type=\"checkbox\" name=\"arcdeal[farea]\" value=\"1\">&nbsp;$Title",'arcfarea',$opMod.$opOpt,'');
		}elseif($re=='Checkbox'){  //checkbox添加编辑js函数Checkbox(40),40是可选择的数量。
            $diqu = array();
	        foreach($coclasses as $k=>$val){
		         if(empty($val['level']))$diqu[$k]=$val['title'];
	        }
	        $pieces = explode(",",$Values);
	        trbasic('选择地区','',makecheckbox('fmdata[farea][]',$diqu,$pieces,'',10),'');
			echo "<script>
			Checkbox(40);
			function Checkbox(value){
				var \$selected = $('[type=\"checkbox\"]:checked'),
				\$unselected = $('[type=\"checkbox\"]:not(:checked)'),
				 selectedLength = \$selected.length;
				\$unselected.prop('disabled', selectedLength >= value);
	            \$('form').on('change', '[type=\"checkbox\"]', function (e){
                var \$selected = $('[type=\"checkbox\"]:checked', e.delegateTarget),
                \$unselected = $('[type=\"checkbox\"]:not(:checked)', e.delegateTarget),
                 selectedLength = \$selected.length;
	            \$unselected.prop('disabled', selectedLength >= value);
                });	
            }</script>"; 
		}else{ //Edit,之前添加编辑使用的方法，采用联动下拉框的方法
			$validator = "<input type=\"hidden\" vid=\"$FormVar\" rule='must' />";
			$linkage = "<script>var data = [$DataStr];\n_08cms.fields.linkage('$FormVar', data, '$Values',40);</script>$validator";
			trbasic("<span style='color:#F00'>*</span> $Title",'',$linkage, '');			
		}
	}
	
	# 对ID进行初始格式化
    public static function InitID($fcaid = ''){
		$fcaid = empty($fcaid) ? '' : trim(strtolower($fcaid));
		return cls_string::ParamFormat($fcaid);
	}
	
	/**
     * 获取新的fcaid
     * 用于兼容之前整形数据的fcaid
     * 
     * @param  int    $fcaid 输入旧的fcaid
     * @return string        返回新的fcaid
     * 
     */
    public static function getNewFcaid( $fcaid )
    {
		if(is_numeric($fcaid)){
			return 'fcatalog' . (int)$fcaid;
		}else{
			return cls_string::ParamFormat($fcaid);
		}
    }
	
	# 缓存名称
    public static function CacheName(){
		return 'fcatalogs';
    }
	
	# 返回 ID=>名称 的列表数组
	public static function fcaidsarr($chid = 0){
		$CacheArray = cls_cache::Read(cls_fcatalog::CacheName());
		$narr = array();
		foreach($CacheArray as $k => $v) if(!$chid || $chid == $v['chid']) $narr[$k] = $v['title']."($k)";
		return $narr;
	}
	
	# 显示 [关联地区]的类目列表Select选项
	public static function fAreaCoType($val=''){
        $key = 'farea';
		$arr = array(0 => '不关联地区');
		$cotypes = cls_cache::Read('cotypes'); 
        foreach($cotypes as $k => $v){ 
			if(empty($v['self_reg'])) $arr[$k] = "($k) - ".$v['cname'];
		}
		trbasic('关联地区类系',"fmdata[$key]",makeoption($arr,$val),'select');	
	}
	
	# 检查新定义的fcaid是否合法
	public static function CheckNewID($fcaid = ''){
		if(!($fcaid = cls_fcatalog::InitID($fcaid))) return '唯一标识不能为空';
		if(!preg_match("/[a-z]+\w+/",$fcaid)) return '头字符应为字母，其它字符应为字母、数字或_';
		if(cls_fcatalog::InitialOneInfo($fcaid)) return '指定的唯一标识被占用';
		return '';
	}
	
	# 更新缓存，按字段缓存名，提供给cls_CacheFile使用
	public static function UpdateCache(){
		cls_fcatalog::SaveInitialCache();
	}
	
	# 更新模板中的完全数据源，相当于更新数据表
	public static function SaveInitialCache($CacheArray = ''){
		if(!is_array($CacheArray)){ # 来自传入的配置数组
			$CacheArray = cls_fcatalog::InitialInfoArray();
		}
		
		cls_Array::_array_multisort($CacheArray,'vieworder',true);# 以vieworder重新排序
		$CacheArray = cls_catalog::OrderArrayByPid($CacheArray,''); # 以pid为结构进行排序
		
		cls_CacheFile::Save($CacheArray,cls_fcatalog::CacheName());
	}
	
	# 动态的资料数组，直接来自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	# pid为-1时，不限制父分类
	public static function InitialInfoArray($pid = -1){
		
		$CacheArray = cls_cache::Read(cls_fcatalog::CacheName(),'','',1);
		if($pid != -1){
			$pid = cls_fcatalog::InitID($pid);
			foreach($CacheArray as $k => $v){
				if($v['pid'] != $pid) unset($CacheArray[$k]);
			}
		}
		return $CacheArray;
	}
	
	# 动态的单个资料，直接自初始数据源(如数据库/缓存)，用于后台管理及实时非缓存调用
	public static function InitialOneInfo($id){
		
		$id = cls_fcatalog::InitID($id);
		$CacheArray = cls_fcatalog::InitialInfoArray();
		return empty($CacheArray[$id]) ? array() : $CacheArray[$id];
		
	}
	# 新增或存入一条配置到初始数据源
	public static function ModifyOneConfig($nowID,$newConfig = array(),$isNew = false){
		
		$nowID = cls_fcatalog::InitID($nowID);
		if($isNew){
			$newConfig['title'] = trim(strip_tags(@$newConfig['title']));
			if(!$newConfig['title']) cls_message::show('分类资料不完全');
			if($re = cls_fcatalog::CheckNewID($nowID)) cls_message::show($re);
			$oldConfig = cls_fcatalog::_OneBlankInfo($nowID);
		}else{
			if(!($oldConfig = cls_fcatalog::InitialOneInfo($nowID))) cls_message::show('请指定正确的副件分类。');
			$nowID = $oldConfig['fcaid'];
		}	
		
		# 格式化数据
		if(isset($newConfig['pid'])){
			$newConfig['pid'] = cls_fcatalog::InitID($newConfig['pid']);
			if(!cls_fcatalog::InitialOneInfo($newConfig['pid'])) $newConfig['pid'] = '';
		}
		if(isset($newConfig['apmid'])){
			$newConfig['apmid'] = empty($newConfig['apmid']) ? 0 : (int)$newConfig['apmid'];
		}
		if(isset($newConfig['customurl'])){
			$newConfig['customurl'] = preg_replace("/^\/+/",'',trim($newConfig['customurl']));
		}
		
		# 赋值
		$InitConfig = cls_fcatalog::_OneBlankInfo($nowID); # 完全的配置结构
		foreach($InitConfig as $k => $v){
			if(in_array($k,array('fcaid'))) continue;
			if(isset($newConfig[$k])){ # 赋新值
				$oldConfig[$k] = $newConfig[$k];
			}elseif(!isset($oldConfig[$k])){ # 新补的字段
				$oldConfig[$k] = $v;
			}
		}		
		
		# 保存
		$CacheArray = cls_fcatalog::InitialInfoArray();
		$CacheArray[$nowID] = $oldConfig;
		cls_fcatalog::SaveInitialCache($CacheArray);
		
		return $nowID;
		
	}
	
	public static function SetFtype($ftype = 0,array $IDs){
		$CacheArray = cls_fcatalog::InitialInfoArray();
		$ftype = empty($ftype) ? 0 : 1;
		foreach($IDs as $ID){
			if(!empty($CacheArray[$ID])){
				$CacheArray[$ID]['checked'] = 1;
				$CacheArray[$ID]['ftype'] = $ftype;
			}
		}
		cls_fcatalog::SaveInitialCache($CacheArray);
	}
	
	public static function DeleteOne($fcaid,$ForceDelete = 0){
		global $db,$tblprefix;
		
		$fcaid = cls_fcatalog::InitID($fcaid);
		if(!$fcaid || !($fcatalog = cls_fcatalog::InitialOneInfo($fcaid))) return '请指定正确的副件分类。';
		if($ForceDelete){//强制删除本分类下的所有副件及子分类
			
			# 删除子类
			if($pInfoArray = cls_fcatalog::InitialInfoArray($fcaid)){
				foreach($pInfoArray as $k => $v){
					cls_fcatalog::DeleteOne($k,$ForceDelete);
				}
			}
			
			# 删除当前分类内的副件
			$arc = new cls_farcedit;
			$query = $db->query("SELECT aid FROM {$tblprefix}farchives WHERE fcaid='$fcaid'");
			while($r = $db->fetch_array($query)){
				$arc->set_aid($r['aid']);
				$arc->arc_delete();
			}
		}else{
			if($pInfoArray = cls_fcatalog::InitialInfoArray($fcaid)){
				return '请先删除分类内的子分类。';
			}
			if($db->result_one("SELECT COUNT(*) FROM {$tblprefix}farchives WHERE fcaid='$fcaid'")){
				return '请先删除分类内的副件。';
			}
		}
		
		# 删除广告缓存，模板标签等
		_08_Advertising::DelOneAdv($fcaid);
		
		# 删除副件分类配置
		$CacheArray = cls_fcatalog::InitialInfoArray();
		unset($CacheArray[$fcaid]);
		cls_fcatalog::SaveInitialCache($CacheArray);
	}
	
	# 管理后台的左侧展开菜单的显示
	public static function BackMenuCode(){
		$curuser = cls_UserMain::CurUser();
		$a_fcaids = $curuser->aPermissions('fcaids');
		
		//副件分类：不再使用管理节点配置，强制父分类仅仅是展示节点
		$fcatalogs = cls_cache::Read('fcatalogs');
		$fcaids = array_keys($fcatalogs);
		if(!in_array('-1',$a_fcaids)){//管理角色权限设置
			$fcaids = array_intersect($fcaids,$a_fcaids);//有效的节点
			$v_fcaids = $fcaids;//需要展示的节点
			foreach($fcaids as $v) if(!empty($fcatalogs[$v]['pid'])) $v_fcaids[] = $fcatalogs[$v]['pid'];//有效节点的上级节点需要展示出来
			$v_fcaids = array_unique($v_fcaids);
		}else $v_fcaids = $fcaids;
		
		$na = array();
		if(!$curuser->NoBackFunc('freeinfo')){ # 副件架构管理权限
			$na[0] = array('title' => '副件架构','level' => 0,'active' => 1,);
		}
		foreach($fcatalogs as $k => $v){
			if(!in_array($k,$v_fcaids)) continue;
			$na[$k] = array('title' => $v['title'],'level' => $v['pid'] ? 1 : 0,'active' => in_array($k,$fcaids) && $v['pid'] ? 1 : 0,);
		}
		return ViewBackMenu($na,1);
	}
	
	# 管理后台的左侧单个分类的管理节点展示(ajax请求)
	public static function BackMenuBlock($fcaid){
		$UrlsArray = cls_fcatalog::BackMenuBlockUrls($fcaid);
		return _08_M_Ajax_Block_Base::getInstance()->OneBackMenuBlock($UrlsArray);
	}
	
	
	# 管理后台的左侧单个分类的管理节点url数组，可以根据需要在应用系统进行扩展
	protected static function BackMenuBlockUrls($fcaid){
		$UrlsArray = array();
		$fcaid = cls_fcatalog::InitID($fcaid);
		if(!$fcaid){
			$UrlsArray['副件分类'] = "?entry=fcatalogs&action=fcatalogsedit";
			$UrlsArray['副件模型'] = "?entry=fchannels&action=fchannelsedit";
		}elseif($fcatalog = cls_cache::Read('fcatalog',$fcaid)){
			if(!empty($fcatalog['pid'])){
				$suffix = $fcaid ? "&fcaid=$fcaid" : '';
				$TypeTitle = empty($fcatalog['ftype']) ? '副件' : '广告';
				$UrlsArray[$TypeTitle.'管理'] = "?entry=extend&extend=farchives$suffix";
				$UrlsArray[$TypeTitle.'添加'] = "?entry=extend&extend=farchiveadd$suffix";
				if(!empty($fcatalog['ftype'])){
					$UrlsArray['广告模板'] = "?entry=extend&extend=adv_management&src_type=other$suffix";
				}
			}
		}
		return $UrlsArray;
	}
	
	# 一条新建记录的初始化数据
	protected static function _OneBlankInfo($ID = 0){
		return array(
			'fcaid' => cls_fcatalog::InitID($ID),
			'title' => '',
			'pid' => '0',
			'vieworder' => '0',
			'chid' => '1',
			'autocheck' => '0',
			'apmid' => '0',
			'nodurat' => '0',
			'customurl' => '',
			'content' => '',
			'ftype' => '0',
			'farea' => '0',
			'params' => '',
			'checked' => '1',
		);
	}
	
	
}
