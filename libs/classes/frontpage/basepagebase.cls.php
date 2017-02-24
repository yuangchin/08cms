<?php
/**
 * 所有前台处理(页面/模板解析/模板标签)共用的基类
 * 所有类型的前台页面(需要模板或标签解析)的均继承此基类
 */
defined('M_COM') || exit('No Permission');
abstract class cls_BasePageBase{
		
	# 以下变量为静态变量，所有子类(指定模板解析，标签解析)共用 ************************************
	protected static $db = NULL;						# 数据库连接
	protected static $curuser = NULL;					# 当前会员
	protected static $tblprefix = '';					# 数据表前缀
	protected static $timestamp = 0;					# 当前时间戳
	protected static $cms_abs = '';						# 系统完全域名
	
	# 同一页面中的所有页面处理，模板解析，标签解析需要共用以下变量 *************************
#	protected static $G = array();						# 页面共用变量容器$G，暂时维持global，以兼容目前的模板
	protected static $_mp = array();					# 分页配置及结果数组
	
	
}
