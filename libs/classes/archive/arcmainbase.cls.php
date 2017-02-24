<?php
/* 
** 与文档有关的基本方法汇总，是cls_ArcMain的基类
** 架构意图：结构轻简，其它应用模块中常用，通常以静态方法表现
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_ArcMainBase{
	
	
	/**
	 * 获取文档内容页的url
	 * 
	 * @param  array 	$archive 文档的资料数组，aid,chid,caid,initdate,customurl,nowurl,jumpurl一定传到$archive中,(可选)nodemode手机版标记
	 * @param  int		$addno     -1为全部页面(含会员空间内容页),不返回，否则返回指定附加页的URL
	 */
	public static function Url(&$archive,$addno = 0){
		$arc_tpl = cls_tpl::arc_tpl($archive['chid'],$archive['caid'],!empty($archive['nodemode']));
		
		if($addno == -1){
			$AddnoArray = array();
			for($i = 0;$i <= @$arc_tpl['addnum'];$i ++){
				$AddnoArray[] = $i;
			}
		}else{
			$AddnoArray = array((int)$addno);
		}
		
		if(!empty($archive['jumpurl'])){ # 跳转Url
			foreach($AddnoArray as $k){
				$archive['arcurl'.($k ? $k : '')] =  cls_url::view_url($archive['jumpurl'],false);
			}
			if($addno == -1) $archive['marcurl'] = cls_url::view_url($archive['jumpurl'],false);
		}elseif(!empty($archive['nodemode'])){ # 手机版
            $get = cls_env::_GET('is_weixin');
			foreach($AddnoArray as $k){
			    $key = 'arcurl'.($k ? $k : '');
				$archive[$key] = cls_url::view_url(cls_env::mconfig('mobiledir')."/archive.php?aid=$archive[aid]".($k ? "&addno=$k" : ''));
                if (!empty($get['is_weixin']))
                {
                    $archive[$key] .= "&is_weixin=1";
                }
			}
		}else{ # 常规Url，存在动静态
			$archive = ArchiveStaticFormat($archive);
			foreach($AddnoArray as $k){
				if(isset($archive['arcurl'.($k ? $k : '')])) continue; # 避免重复执行
				if(empty($arc_tpl['cfg'][$k]['static']) ? cls_env::mconfig('enablestatic') : 0){ # 静态Url
					if($archive['nowurl']){
						$archive['arcurl'.($k ? $k : '')] = cls_url::view_url(cls_url::m_parseurl($archive['nowurl'],array('addno' => arc_addno($k,@$arc_tpl['cfg'][$k]['addno']),'page' => 1,)));
					}else $archive['arcurl'.($k ? $k : '')] = '#';
				}else{ # 动态Url
					$archive['arcurl'.($k ? $k : '')] = cls_url::view_url(cls_url::en_virtual("archive.php?aid=$archive[aid]".($k ? "&addno=$k" : ''),@$arc_tpl['cfg'][$k]['novu']));
				}
			}
			if(!empty($archive['mid']) && $addno == -1){
				$archive['marcurl'] = cls_url::view_url(cls_env::mconfig('mspaceurl').cls_url::en_virtual("archive.php?mid=".$archive['mid']."&aid=".$archive['aid']));
			}
		}
		return $addno == -1 ? true : $archive['arcurl'.($addno ? $addno : '')];
	}
	
	/**
	 * 文档在模板解析中从数据库或类中读出后，需要追加处理的事务
	 *
	 * @param  array     &$archive		文档资料数组
	 * @param  bool      $inList		是否在列表中，在列表中会简化一些处理流程
	 * @return NULL   ---       --- 
	 */
	function Parse(&$archive,$inList = false){
		cls_ArcMain::Url($archive,-1);	
		#if(!empty($archive['nodemode']) && !$inList) cls_atm::arr_image2mobile($archive);//在<!cmsurl>转换之前执行，处理手机版中html中图片大小
		if(empty($archive['nodemode'])) cls_url::arr_tag2atm($archive);//pc版提前处理html中<!cmsurl>转换问题
		$cotypes = cls_cache::Read('cotypes');
		$catalogs = cls_cache::Read('catalogs');
		$archive['catalog'] = $catalogs[$archive['caid']]['title'];
		foreach($cotypes as $k => $v){
			if(isset($archive["ccid$k"])){
				$archive['ccid'.$k.'title'] = empty($archive["ccid$k"]) ? '' : cls_catalog::cnstitle($archive["ccid$k"],$v['asmode'],cls_cache::Read('coclasses',$k));
			}		
		}
	}
	
	/**
	 * 当前会员是否有权限允许下载文档中的附件//查附件扣值不属此范围
	 *
	 * @param  int     $archive		文档资料数组
	 * @return bool    --- 			是否有权限允许下载
	 */
	function AllowDown($archive){//当前会员是否有权限允许下载文档中的附件//查附件扣值不属此范围
		$curuser = cls_env::GetG('curuser');
		if($curuser->isadmin()) return true;
		if($curuser->info['mid'] && $curuser->info['mid'] == $archive['mid']) return true;//发布者本人
		$pmid = 0;
		if(empty($archive['dpmid'])){
			return true;//单文档完全开放
		}elseif($archive['dpmid'] == -1){//继承类目权限
			$catalog = cls_cache::Read('catalog',$archive['caid']);
			if(!empty($catalog['dpmid'])) $pmid = $catalog['dpmid'];
			unset($catalog);
		}else $pmid = $archive['dpmid'];//单文档设置的权限方案
		return $curuser->pmbypmid($pmid);
	}
	
}
