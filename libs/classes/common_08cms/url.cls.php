<?php
/**
* url相关的处理方法
* 
*/
class cls_url{
	
	/**
	 * 对url，绑定域名
	 *
	 * @param  string  $url    处理前的$url
	 * @return string  $url    处理后的$url
	 */
	public static function domain_bind($url){
		$na = cls_cache::Read('domains');
		if(!$url || empty($na['from'])) return $url;
		foreach($na['from'] as $k => $v){
			$nurl = @preg_replace($v,$na['to'][$k],$url);
			$url = $nurl ? $nurl : $url;
		}
		return $url;
	}
	
	/**
	 * 处理：系统设置[隐藏]的url
	 *
	 * @param  string  $url    处理前的$url
	 * @return string  $url    处理后的$url
	 */
	public static function remove_index($url){
		global $hiddensinurl;
		if(!$url || !($arr = explode(',',$hiddensinurl))) return $url;
		return str_replace($arr,'',$url);
	}
	
	/**
	 * 保存html字段时，处理里面的url
	 *
	 * @param  string  &$str    处理前的$str
	 * @return string  &$str    处理后的$str
	 */
	public static function html_atm2tag(&$str){
		global $ftp_url,$ftp_enabled,$cms_abs;
		$re = preg_quote($cms_abs,"/");
		if($ftp_enabled && $ftp_url) $re .= '|'.preg_quote($ftp_url,"/");
		$str = addslashes(preg_replace("/(=\s*['\"]?)($re)(.+?['\" >])/ies",'"$1"."<!cmsurl />"."$3"',stripslashes($str)));
	}
	
	/**
	 * 根据数据库保存路径，判断是否为 ftp附件还是本地附件,只能分析单个附件
	 *
	 * @param  string  $str     原始的数据库保存路径
	 * @return bool    ---      是否为ftp附件
	 */
	public static function is_remote_atm($str){
		global $ftp_enabled,$other_ftp_dir,$ftp_url;
		if(!$ftp_enabled || !$ftp_url || empty($other_ftp_dir)) return false;
		$otherftpdir = str_replace(array('./','/','-'),array('',"\\/","\\-"),$other_ftp_dir);
		return preg_match('/(<\!cmsurl \/>|<\!ftpurl \/>)?('.$otherftpdir.')/i',$str) ? true : false;
	}
	
	/**
	 * 根据url，判断是否为本地文件(附件)
	 *
	 * @param  string  $url     原始的url，可以是保存字串也可以是显示url
	 * @param  int     $isatm   附件标记：0-非附件，1-ftp附件算本地，2-ftp附件不算本地
	 * @return bool    ---      是否为本地文件
	 */
	public static function islocal($url,$isatm=0){
		global $cms_abs,$ftp_url,$ftp_enabled;
		if(strpos($url,':/') === false) return true;
		if(preg_match(u_regcode($cms_abs),$url)) return true;
		if($ftp_enabled && $ftp_url && ($isatm == 1) && preg_match(u_regcode($ftp_url),$url)) return true;
		return false;
	}
	
	/**
	 * 把 原始保存的url字符 转化为 可以浏览的url
	 *
	 * @param  string  $url     原始保存的url字符
	 * @param  bool    $ishtml  默认0；1:表示传入的是html文本,要处理的是内嵌的附件
	 * @return string  $url     处理后可以浏览的url
	 */
	public static function tag2atm($str,$ishtml=0){
		//ishtml:如果是1的话，传入的html文本，要处理的是内嵌的附件
		global $ftp_url;
		if(empty($str)) return '';
		if($ishtml){ //Html
			if(preg_match_all("/(=\s*['\"]?)((<\!cmsurl \/>|<\!ftpurl \/>)(.+?))['\" >]/i",$str,$arr) && !empty($arr[2])){
				foreach($arr[2] as $v) $str = str_replace($v,self::tag2atm($v),$str);
			}
			return $str;
		}else{
			//兼容之前的方式，可能还有这个标记
			$str = str_replace(array('<!cmsurl />','<!ftpurl />'),'',$str);
			if(self::is_remote_atm($str)){	 	
				if(strpos($str,$ftp_url) === 0) return $str;  //如果$str已经是远程服务器地址,直接返回
				else return $ftp_url.$str;
			}else{
				return self::view_url($str);
			}
		}
	}
	

	/**
	 * 说明：
	 *
	 * @param  array  &$item   
	 * @param  bool   $fmode  
	 * @return NULL   ---  
	 */
	public static function arr_tag2atm(&$item,$fmode=''){
		$fmodearr = array(
		'' => array('fields','chid'),
		'f' => array('ffields','chid'),
		'm' => array('mfields','mchid'),
		'pa' => array('pafields','paid'),
		'ca' => array('cnfields',0),
		'cc' => array('cnfields','coid'),
		);
		if(!empty($fmodearr[$fmode])){
			$fields = @cls_cache::Read($fmodearr[$fmode][0],$fmodearr[$fmode][1] ? $item[$fmodearr[$fmode][1]] : 0);
			foreach($fields as $k => $v){
				if(isset($item[$k]) && $v['datatype'] == 'htmltext'){
					$item[$k] = self::tag2atm($item[$k],1);
				}
			}
		}
	}
	
	/**
	 * 
	 *
	 * @param  string  $url     路径
	 * @return string  $url     处理后的路径
	 */
	public static function local_file($url){
		global $cms_abs;
		return self::islocal($url) ? M_ROOT.preg_replace(u_regcode($cms_abs),'',$url) : $url;
	}
    
    /**
     * 把本地文件转成URL
     * 
     * @param  string $localFile 要转化的本地文件地址
     * @return string            转化后的文件URL地址
     * 
     * @since  nv50
     */
    public static function localToUrl( $localFile )
    {
        return _08_CMS_ABS . str_replace(array(M_ROOT, '\\'), array('', '/'), $localFile);
    }
	
	/**
	 * 根据url得到本地路径//incftp同时处理ftp的url//如果是第三方附件则返回原url
	 *
	 * @param  string  $url     路径
	 * @param  bool    $incftp  处理ftp的url
	 * @return string  $url     处理后的路径
	 */
	public static function local_atm($url,$incftp=0){
		//根据url得到本地路径//incftp同时处理ftp的url//如果是第三方附件则返回原url
		global $cms_abs,$ftp_url,$ftp_enabled;
		$url = preg_replace(u_regcode($cms_abs),'',$url);
		if($incftp && $ftp_enabled && $ftp_url) $url = preg_replace(u_regcode($ftp_url),'',$url);
		return (strpos($url,':/') === false ? M_ROOT : '').$url;
	}
	
	/**
	 * 根据本地路径，得到缩略图的本地路径。
	 *
	 * @param  string  $local   路径
	 * @param  int     $width   宽
	 * @param  int     $height  高
	 * @return string  $local   处理后的路径
	 */
	public static function thumb_local($local,$width,$height){//根据本地路径，得到缩略图的本地路径。
		return preg_replace("/(_\d+_\d+)*\.\w+$/i","_{$width}_{$height}.jpg",$local);
	}
	
	/**
	 * 对url格式化显示，处理 绑定域名,系统设置[隐藏]的url
	 *
	 * @param  string  	$url    	处理前的$url
	 * @param  bool  	$NeedBind   是否需要处理子域名绑定，确定不需要处理域名的url，可省略子域名绑定操作
	 * @return string 	$url    	处理后的$url
	 */
	public static function view_url($url,$NeedBind = TRUE){
		global $cms_abs;
		if(empty($url)) return $url;
		if(strpos($url,$cms_abs) === 0) $url = str_replace($cms_abs,'',$url);
		if(strpos($url,'://') === false){
			if($NeedBind) $url = self::domain_bind($url);
			$url = self::remove_index($url);
			if(strpos($url,'://') === false) $url = $cms_abs.$url;
		}
		return $url;
	}
	
	/**
	 * 对静态文件格式字串进行资料代入，并对最后格式进行清理
	 * 当页码page=1时，清除page在字串中体现
	 * @param  string   $u		传入的静态格式字串  
	 * @param array     $s  	传入的数据资料
	 * @return array    $u  	返回处理过的静态格式字串
	 */
	public static function m_parseurl($u,$s = array()){
		if(!$s || !$u) return $u;
		$u = str_replace(' ','',$u);
		foreach($s as $k => $v){
			if(($k == 'page') && ($v == 1)){
				//考虑类似参数page=1在中间或在最后的情况
				$u = preg_replace("/([&\/]page[-=]\{\\\$page\}*|page[-=]\{\\\$page\}[&\/]*)/",'',$u);
				preg_match("/(^|\/)[\d_-]*(?:[a-z][\d_-]*)+\{\\\$page\}\./i",$u) && $v = '';
			}
			$u = str_replace('{$'.$k.'}',$v,$u);
		}
		$u = preg_replace(array('/(?:_[_-]*)+/','/(?:-[_-]*)+/','/(?:[_-]*\/[\/_-]*)+/','/[\/_-]*\.+[\/_-]*/'),array('_','-','/','.'),$u);
		return str_replace(':/','://',$u);
	}

	/**
	 * 根据节点字串，更新节点相关连接
	 *
	 * @param  string  $cnstr  节点字串
	 * @return array   &$cnode 节点配置信息
	 * @return NULL    ---     更新$cnode相关连接
	 */
	public static function view_cnurl($cnstr,&$cnode){
		global $enablestatic,$cn_max_addno,$mobiledir;
		if(empty($cnode)){
			for($i = 0;$i <= $cn_max_addno;$i ++) $cnode['indexurl'.($i ? $i : '')] = '#';
		}elseif(!empty($cnode['appurl'])){
			for($i = 0;$i <= @$cnode['addnum'];$i ++) $cnode['indexurl'.($i ? $i : '')] = $cnode['appurl'];
		}else{
            $get = cls_env::_GET('is_weixin');
			for($i = 0;$i <= @$cnode['addnum'];$i ++){
				if(!empty($cnode['nodemode'])){//手机节点
                    $key = 'indexurl'.($i ? $i : '');
					$cnode[$key] = self::view_url("$mobiledir/index.php?$cnstr".($i ? "&addno=$i" : ''));
                    if (!empty($get['is_weixin']))
                    {
                        $cnode[$key] .= "&is_weixin=1";
                    }                    
				}elseif(empty($cnode['cfgs'][$i]['static']) ? $enablestatic : 0){
					$cnode['indexurl'.($i ? $i : '')] = self::view_url(self::m_parseurl(cls_node::cn_format($cnstr,$i,$cnode),array('page' => 1)));
				}else $cnode['indexurl'.($i ? $i : '')] = self::view_url(self::en_virtual("index.php?$cnstr".($i ? "&addno=$i" : '')));
			}
		}
	}

	/**
	 * 会员空间类目页url
	 *
	 * @param  array	$info		指定会员的主表信息数组
	 * @param  array	$params		指定的更多属性，如mcaid(空间栏目)，addno(附加页)，ucid(空间栏目内的个人分类)
	 * @param  bool		$dforce		强制返回动态格式
	 * @return string      			返回会员空间类目页url
	 */
	public static function view_mspcnurl($info,$params = array(),$dforce = false){
		return cls_Mspace::IndexUrl($info,$params,$dforce);
	}
	
	/**
	 * 会员频道节点url
	 * @param  string	$cnstr		会员节点的属性字串
	 * @param  array	$cnode		会员节点的资料数组
	 * @return      				不返回，在$cnode增加所有附加页的url
	 */
	public static function view_mcnurl($cnstr,&$cnode){
		global $enablestatic,$mcn_max_addno,$memberurl;
		if(empty($cnode)) return;
		if(!empty($cnode['appurl'])){
			for($i = 0;$i <= $mcn_max_addno;$i ++) $cnode['mcnurl'.($i ? $i : '')] = $cnode['appurl'];
		}else{
			for($i = 0;$i <= $mcn_max_addno;$i ++){
				if(empty($cnode['cfgs'][$i]['static']) ? $enablestatic : 0){
					$cnode['mcnurl'.($i ? $i : '')] = $i <= @$cnode['addnum'] ? self::view_url($memberurl.self::m_parseurl(empty($cnode['cfgs'][$i]['url']) ? '{$cndir}/index{$addno}_{$page}.html' : $cnode['cfgs'][$i]['url'],array('cndir' => mcn_dir($cnstr),'addno' => $i ? $i : '','page' => 1,))) : '#';
				}else $cnode['mcnurl'.($i ? $i : '')] = $i <= @$cnode['addnum'] ? $memberurl.self::en_virtual("index.php?$cnstr".($i ? "&addno=$i" : '')) : '#';
			}
		}
	}	
	
	/**
	 * 针对单个附件url得到保存到数据库中的格式
	 *
	 * @param  string  $url     单个附件$url
	 * @return string  $url     处理后的$url
	 */
	public static function save_atmurl($url){
		global $cms_abs,$ftp_url,$ftp_enabled;
		$url = preg_replace(u_regcode($cms_abs),'',$url);
		if($ftp_enabled && $ftp_url) $url = preg_replace(u_regcode($ftp_url),'',$url);
		return $url;
	}
	
	/**
	 * 针对绝对url得到保存到数据库中的格式，按相对url保存
	 *
	 * @param  string  $url     单个附件$url
	 * @return string  $url     处理后的$url
	 */
	public static function save_url($url){
		global $cms_abs;
		$url = preg_replace(u_regcode($cms_abs),'',$url);
		return $url;
	}
	
	/**
	 * 参考cls_url::tag2atm(用 tag2atm 代替 ??? )
	 *
	 * @param  string  $url     url
	 * @return string  $url     处理后的url
	 */
	public static function view_atmurl($url=''){
		if(!$url) return '';
		return self::tag2atm($url);
	}
	
	/**
	 * 获取副件内容页url
	 *
	 * @param  int     $id      附件id
	 * @param  $url    $archive url
	 * @return string  $url     处理后的url
	 */
	public static function view_farcurl($id,$url=''){
		if(!$url) $url = self::en_virtual("info.php?aid=$id");
		return self::view_url($url);
	}
	
	/**
	 * 处理虚拟静态地址
	 *
	 * @param  string   $str      原始url  
	 * @param  bool     $novu     禁止虚拟静态
	 * @return string   $str      虚拟静态地址
	 */
	public static function en_virtual($str = '',$novu=0){
		$virtualurl = cls_env::mconfig('virtualurl');
		$rewritephp = cls_env::mconfig('rewritephp');
		if(empty($str) || empty($virtualurl) || $novu) return $str;
		$str = str_replace('=','-',str_replace('&','/',$str));
		$str .= '.html';
		if(!empty($rewritephp)) $str = str_replace('.php?',$rewritephp,$str);
		return $str;
	}
	
    /**
     * 创建MVC应用URL
     *
     * @param  string $route  路由规则
     * @param  array  $params URL配置参数
     * @return string         返回根据参数创建好的URL
     *
     * @since  1.0
     */
    public static function create( $route, $params = array() )
    {
        if ( is_string($route) )
        {
            $route = explode('/', $route);
            $route = array($route[0] => $route[1]);
        }
        else
        {
            $route = (array) $route;
        }
        
        $params = array_merge($route, $params);
        
        $route = _08_CMS_ABS . _08_Http_Request::uri2MVC($params);
        return $route;
    }

    /**
     * 对URL进行urlencode编码（让支持数组）
     * 
     * @param  mixed $urls              要编码的URL
     * @param  bool  $onlyEncodeChinese 只编码中文
     * @return mixed                    返回已经编码的URL
     * 
     * @since  nv50
     */
	public static function encode( $urls, $onlyEncodeChinese = true )
    {
        if (!$onlyEncodeChinese)
        {
            return cls_Array::map('rawurlencode', $urls);
        }
        
        if (is_array($urls))
        {
            foreach ($urls as &$url)
            {
                if (is_array($url))
                {
                    $url = self::encode($url);
                }
                else
                {
                    $url = self::getEncodeChinese($url);
                }
            }
        }
        else
        {
            $urls = self::getEncodeChinese((string) $urls);
        }
        
        return $urls;
    } 
    
    /**
     * 获取只编码中文的字符串（目前只支持UTF-8编码的字符串）
     * 
     * @param  string $string 要编码的字符串
     * @return string         返回只有中文经过了编码后的字符串
     **/
    public static function getEncodeChinese($string)
    {
        if (preg_match_all('/[\x7f-\xff]+/', (string) $string, $chinse))
        {
            $array = array();
            foreach ($chinse[0] as &$_chinse)
            {
                $array[] = urlencode($_chinse);
            }
            
            $string = str_replace($chinse[0], $array, $string);
        }
        
        return $string;
    }

    /**
     * 对URL进行{@see self::encode}编码过的URL解码
     * 
     * @param  mixed $urls 要解码的URL
     * @return mixed       返回已经解码的URL
     * @since  nv50
     */
	public static function decode( $urls )
    {
        return cls_Array::map('rawurldecode', $urls);
    } 
# ----------------------------------------------------------------------------------	
	# 获取文档内容页的url。暂时保留以兼容旧版本
	public static function view_arcurl(&$archive,$addno = 0){
		return cls_ArcMain::Url($archive,$addno);
	}

	
}
