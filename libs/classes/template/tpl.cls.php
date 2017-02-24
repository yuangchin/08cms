<?php
/**
* 与模板相关的方法汇集
* 
*/
class cls_tpl{
	
	// 基础模版-扩展模版:关联路径
	// flag: dir=当前模版, base=基础模版, get=由get:isbase参数判断
	public static function rel_path($tname,$flag='dir'){
		$tplbase = cls_env::GetG('templatebase');
		if($flag=='get' && empty($tplbase)){ 
			$flag = 'dir'; // 当前是基础模版,定位到当前模版
		}elseif($flag=='get'){
			$flag = cls_env::GetG('isbase') ? 'base' : 'dir';	
		}
		return M_ROOT.'template'.DS.cls_env::GetG('template'.$flag).DS.'tpl'.DS.$tname;
	}
	
	/**
	 * 加载模版
	 *
	 * @param  string  $str  模版名
	 * @param  int     $rt   是否置换页面内的区块标签
	 * @return string  $str  模版文件内容
	 */
	public static function load($tplname,$ReplaceRtag = true){
		_08_FilesystemFile::filterFileParam($tplname);
		$tpl = @file2str(self::TemplateTypeDir('tpl').$tplname);
		//扩展模版,处理继承:优先使用扩展模版
		if($templatebase = cls_env::GetG('templatebase')){
			if(file_exists($path = self::rel_path($tplname))){
				$tpl = @file2str($path); 
			}
		}
		if($ReplaceRtag) $tpl = self::ReplaceRtag($tpl);
		return $tpl;
	}
	
	
	/**
	 * 替换模板内容字串中的区块标签
	 *
	 * @param  string  $Content  	来源字串
	 * @return string    
	 */
	public static function ReplaceRtag($Content){
		$Content = preg_replace("/\{tpl\\$(.+?)\}/ies", "self::rtagval('\\1')",$Content);
		return $Content;
	}
	
	/**
	 * 获取指定区块标签中的模板内容
	 *
	 * @param  string  $tname  	区块标签名称
	 * @return string  $str		返回区块标签中的模板内容
	 */
	public static function rtagval($tname){
		$TplContent = '';
		if($rtag = cls_cache::ReadTag('rtag',$tname)){
			if(empty($rtag['disabled']) && isset($rtag['template'])){
				$TplContent = self::load($rtag['template'],true);
			}
		}else $TplContent = "{tpl\$$tname}"; # 使用了不存在的区块标签
		return $TplContent;
	}
	
	/**
	 * 当前模板目录中不同类型内容的存储子目录
	 *
	 * @param  string  $Type		类型：config(配置)，tpl(模板页面及区块标签内的模板)，tag(模板标签)，function(模板函数)，css(Css目录)，js(JS目录)
	 * @param  bool    $OnlySelf   	(true)返回子目录名称本身，否则(flase)返回完整目录
	 * @return string  $str    		模板目录不同类型内容的存储子目录
	 */
	public static function TemplateTypeDir($Type = 'tpl',$OnlySelf = false){
		$css_dir = cls_env::GetG('css_dir');
		$js_dir = cls_env::GetG('js_dir');
		$TypeArray = array(
			'config' => 'config',
			'tpl' => 'tpl',
			'tag' => 'tag',
			'function' => 'function',
			'tpl_model' => 'tpl_model',
			'css' => $css_dir ? $css_dir : 'css',
			'js' => $js_dir ? $js_dir : 'js',
		);
		$TypeDir = empty($TypeArray[$Type]) ? '' : $TypeArray[$Type];
		if(!$OnlySelf){
			$templatedir = cls_env::GetG('templatedir');
			$templatebase = cls_env::GetG('templatebase'); //扩展模版,除如下目录定位到基础模版目录
			if(!empty($templatebase) && !in_array($Type,array('js','css'))){
				$templatedir = $templatebase;
			} //echo $templatedir.", ";
			_08_FilesystemFile::filterFileParam($templatedir);
			$TypeDir = M_ROOT.'template'.DS.$templatedir.DS.($TypeDir ? $TypeDir.DS : '');
		}
		return $TypeDir;
	}
	
	/**
	 * 说明：
	 *
	 * @param  int     		$chid   文档模型ID
	 * @param  int     		$caid   栏目ID
	 * @param  int		    $Nodemode  是否手机版
	 * @return array   		返回文档配置方案     
	 */
	public static function arc_tpl($chid,$caid = 0,$Nodemode = 0){
		foreach(array('arc_tpls','ca_tpl_cfgs','arc_tpl_cfgs',) as $var){
			$$var = cls_cache::Read($Nodemode ? "o_$var" : $var);
		}
		if(!empty($ca_tpl_cfgs[$caid]) && !empty($arc_tpls[$ca_tpl_cfgs[$caid]])){
			return $arc_tpls[$ca_tpl_cfgs[$caid]];
		}elseif(!empty($arc_tpl_cfgs[$chid]) && !empty($arc_tpls[$arc_tpl_cfgs[$chid]])){
			return $arc_tpls[$arc_tpl_cfgs[$chid]];
		}else return array();
	}
	
	/**
	 * 按通用方式在tplcfgs中获取相关模版名称
	 *
	 * @param  string  $type 类型
	 * @param  array   $id   
	 * @param  string  $name 标识
	 * @return string  $str  返回的模版名称
	 */
	public static function CommonTplname($type,$id,$name,$NodeMode = 0){
		$tplcfgs = cls_cache::Read($NodeMode ? 'o_tplcfgs' : 'tplcfgs');
		return empty($tplcfgs[$type][$id][$name]) ? '' : $tplcfgs[$type][$id][$name];
	}
	/**
	 * 按文档模型或栏目得到文档搜索页的模板名称
	 *
	 * @param  array  $config 来源配置(chid-模型id,caid-栏目id,addno-附加页id,nodemode-是否手机版等)
	 * @return string  $str  返回的模版名称
	 */
	public static function SearchTplname($config = array()){
		$arc_tpl = self::arc_tpl(empty($config['chid']) ? 0 : $config['chid'],empty($config['caid']) ? 0 : $config['caid'],empty($config['nodemode']) ? 0 : $config['nodemode']);
		$re = @$arc_tpl['search'][empty($config['addno']) ? 0 : $config['addno']];
		return $re ? $re : '';
	}
	
	/**
	 * 获取类目节点模版名称
	 *
	 * @param  string  $cnstr	类目节点字串
	 * @param  array   $cnode	已加载cntpl(节点配置)的节点信息，已经区分出是否手机版
	 * @param  string  $addno	附加页
	 * @param  string  $tn		特种类型模板，如rsstpl指定rss模板
	 * @return string  $str		返回的模版名称
	 */
	public static function cn_tplname($cnstr,&$cnode,$addno=0,$tn=''){
		if(!$tn){
			$addno = max(0,intval($addno));
			return empty($cnode['cfgs'][$addno]['tpl']) ? '' : $cnode['cfgs'][$addno]['tpl'];
		}else return empty($cnode[$tn]) ? '' : $cnode[$tn];
	}
	
	/**
	 * 获取会员节点模版名称
	 *
	 * @param  string  $cnstr 会员节点字串
	 * @param  string  $addno 附加页
	 * @return string  $str   返回的模版名称
	 */
	public static function mcn_tplname($cnstr,$addno=0){
		if(!$cnstr){//会员频道首页
			return cls_tpl::SpecialTplname('m_index');
		}else{//节点模板
			$cnode = cls_node::mcnodearr($cnstr);
			return empty($cnode['cfgs'][$addno]['tpl']) ? '' : $cnode['cfgs'][$addno]['tpl'];
		}
	}
	
	# 取得常规模板库中不同类型模板的选择数组，暂时保留以兼容旧版本
	public static function mtplsarr($tpclass = 'archive',$chid = 0){
		return cls_mtpl::mtplsarr($tpclass,$chid);
	}
	
	# 取得手机模板库中不同类型模板的选择数组，暂时保留以兼容旧版本
	public static function o_mtplsarr($tpclass = 'archive'){
		return cls_mtpl::o_mtplsarr($tpclass);
	}
	
	/**
	 * 取得功能页面所绑定的模板名称
	 *
	 * @param  string $name 	功能页面类型
	 * @param  int		$NodeMode  是否手机节点
	 * @return string			返回模板名称
	 */
	 
    public static function SpecialTplname($name,$NodeMode = 0){
		if(!$name) return '';
        $sptpls = cls_cache::Read($NodeMode ? 'o_sptpls' : 'sptpls');
        return empty($sptpls[$name]) ? '' : $sptpls[$name];
    }
	
	/**
	 * 解析某些通用的功能页面的模板并返回页面代码
	 *
	 * @param  string $spname 	功能页面类型
	 * @param  int		$NodeMode  是否手机节点
	 * @return string				返回解析结果代码
	 */
   public static function SpecialHtml($spname='',$_da=array(),$NodeMode = 0,$LoadAdv = false){
	   $re = cls_SpecialPage::Create(array('spname' => $spname,'_da' => $_da,'NodeMode' => $NodeMode,'LoadAdv' => $LoadAdv,));
	   return $re;
    } 
	 

	/*二维码生成  前台调用方式 <img src="<?=cls_tpl::QRcodeImage('文字内容')?>" />
	* @param string $content 二维码文字内容
	* @param int $level   纠错等级 默认为0  其他取值 1 2 3 级别越高图片越大
	* @param int $size    尺寸大小 默认为 4
	* @param int $margin  外边框长度
	* @return string 二维码图片的显示url，二维码图片存在userfiles/qrcode/
	*/
	function QRcodeImage($content,$level=0,$size=4,$margin=3){
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$dir_userfile = cls_env::mconfig('dir_userfile');
		$blankfile = 'images/common/nopic.gif';
		if(!empty($content)){
			$imagefile = $dir_userfile.'/qrcode/'.md5($content.$level.$size.$margin).'.png';
			if(!is_file(M_ROOT.$imagefile)){
				mmkdir($imagefile,1,1);
				include_once M_ROOT."include/phpqrcode.php";
				$content = cls_string::iconv($mcharset,'utf-8',$content);//中文转成UTF-8编码
				QRcode::png($content,M_ROOT.$imagefile,$level,$size,$margin,FALSE);
				if(!is_file(M_ROOT.$imagefile)) $imagefile = $blankfile;
			}
		}else $imagefile = $blankfile;
		return cls_url::view_url($imagefile);
	}	 
	
}
