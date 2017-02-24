<?php
/* 
** 常规模板库的方法汇总，是mtpl.cls.php的基类
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
abstract class cls_mtplbase{
	
	# 取得常规模板库的分类数组
	# $NodeMode=1为手机版
	public static function ClassArray($NodeMode = 0){
		$ClassArray = array(
			'index' => '站点首页',
			'cindex' => '类目节点',
			'archive' => '文档模板',
			'freeinfo' => '副件信息',
			'marchive' => '会员相关',
			'space' => '会员空间',
			'special' => '功能页面',
			'other' => '其它模板',
			'xml' => 'RSS/SiteMap',
		);
		if($NodeMode){
			foreach(array('marchive','space','other','xml') as $k){
				unset($ClassArray[$k]);
			}
		}
		return $ClassArray;
	}
	
	
	# 常规模板库的指引向导
	# $ismobile : 是否为手机模版
	public static function mtplGuide($Class = 'index',$OnlyUrl = false,$ismobile = 0){
		$ClassArray = cls_mtpl::ClassArray();
		$re = '';
		if(!empty($ClassArray[$Class])){
			$re = ">>";
			$re .= "<a href=\"?entry=".($ismobile ? 'o_' : '')."mtpls&action=mtplsedit&tpclass=$Class&isframe=1\" target=\"_08cms_mtpl\">";
			$re .= $OnlyUrl ? "模板库" : ("常规模板库-".$ClassArray[$Class]);
			$re .= "</a>";
		}
		return $re;
	}
	
	
	
	/**
	 * 取得常规模板库中不同类型模板的选择数组
	 *
	 * @param  string $tpclass 	模板类型
	 * @param  int $chid 		文档模型chid，用于单文档指定模板时返回特定模型有关的模板
	 * @return array			返回模板数组
	 */
	public static function mtplsarr($tpclass = 'archive',$chid = 0){
		$mtpls = cls_cache::Read('mtpls');
		$re = array();
		if(empty($mtpls)) return $re;
		foreach($mtpls as $k => $v) {
			if($v['tpclass'] == $tpclass){
				if(!$chid || $chid == @$v['chid']){
					$re[$k] = $v['cname'].' '.$k;
				}
			}
		}
		return $re;
	}
	
	/**
	 * 取得手机模板库中不同类型模板的选择数组
	 *
	 * @param  string $tpclass 	模板类型
	 * @return array			返回模板数组
	 */
	public static function o_mtplsarr($tpclass = 'archive'){
		$o_mtpls = cls_cache::Read('o_mtpls');
		$re = array();
		if(empty($o_mtpls)) return $re;
		foreach($o_mtpls as $k => $v) {
			if($v['tpclass'] == $tpclass){
				$re[$k] = $v['cname'].' '.$k;
			}
		}
		return $re;
	}
	
	
}
