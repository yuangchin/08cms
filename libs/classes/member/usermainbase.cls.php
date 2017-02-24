<?php
/* 
** 与用户有关的基本方法汇总，是cls_UserMain的基类
** 架构意图：结构轻简，其它应用模块中常用，通常以静态方法表现
** 注意：为了在基类中使用扩展的静态方法，在基类中使用：扩展类::method（如果使用：self::method，将不支持扩展）
*/
!defined('M_COM') && exit('No Permission');
class cls_UserMainBase{
	
	protected static $CurUser = NULL;		# 当前会员实例
	
	# 隐藏会员信息中的未认证字段信息
	public static function CurUser(){
		if(empty(self::$CurUser)){
			self::$CurUser = new cls_userinfo();
			self::$CurUser->currentuser();
		}
		return self::$CurUser;
	}
	
	# 隐藏会员信息中的未认证字段信息
	public static function HiddenUncheckCertField(&$info){
		if(empty($info) || !is_array($info) || empty($info['mid'])) return;
		$mctypes = cls_cache::Read('mctypes');
		foreach($mctypes as $k => $v){
			if($v['available'] && !empty($v['field']) && !empty($info[$v['field']]) && empty($info["mctid$k"]) && strstr(",$v[mchids],",",".$info['mchid'].",")){
				$info[$v['field']] = '';
			}
		}
	}
	
	/**
	 * 会员资料在前台模板解析中从数据库或类中读出后，需要追加处理的事务
	 *
	 * @param  array     &$info			会员资料数组
	 * @param  bool      $inList		是否在列表中，在列表中会简化一些处理流程,此参数暂无用，暂放在这兼容旧版本
	 * @return NULL   ---       --- 
	 */
	function Parse(&$info,$inList = false){	
		#if(defined('IN_MOBILE') && !$inList) cls_atm::arr_image2mobile($info,'m');//在<!cmsurl>转换之前执行，处理手机版中html中图片大小
		defined('IN_MOBILE') || cls_url::arr_tag2atm($info,'m');
		$info['mspacehome'] = cls_Mspace::IndexUrl($info);
		cls_UserMain::HiddenUncheckCertField($info);
		$grouptypes = cls_cache::Read('grouptypes');
		foreach($grouptypes as $k => $v){
			$info['grouptype'.$k.'name'] = '';
			if(!empty($info['grouptype'.$k])){
				$usergroups = cls_cache::Read('usergroups',$k);
				$info['grouptype'.$k.'name'] = $usergroups[$info['grouptype'.$k]]['cname'];
			}
		}
	}
}
