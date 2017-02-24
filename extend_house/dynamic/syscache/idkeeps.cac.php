<?php
//最小值与最大值都不能为0
	$idkeeps = array(
		'channels' => array(100,200,),//文档模型
		'splitbls' => array(60,100,),//分表
		
		'cotypes' => array(100,200,),//类系 原来为1-20, 30-50, 
		'catalogs' => array(500,800,),//栏目
		'coclass' => array(3000,5000,),//分类
		'abrels' => array(30,50,),//合辑关系
		'acommus' => array(100,200,),//交互项目  原为array(30,50,)
		'cnrels' => array(20,50,),//类目关系
		'cnodes' => array(5480,6480,),//保留节点
		
		'mchannels' => array(10,20,),//会员模型
		'grouptypes' => array(30,50,),//会员组系
		'usergroups' => array(100,200,),//会员组
		'currencys' => array(10,20,),//积分
		'permissions' => array(100,200,),//权限方案
		'mtconfigs' => array(20,50,),//空间方案
		'mcatalogs' => array(30,60,),//空间栏目

		'fcatalogs' => array(100,200,),//副件分类
		'fchannels' => array(30,50,),//副件模型
		'freeinfos' => array(100,200,),//独立页面
		'frcatalogs' => array(1,20,),//碎片分类
		'pushareas' => array(1,300,),//推送位
		'pushtypes' => array(1,100,),//推送分类
		
		'amconfigs' => array(30,50,),//管理角色
		'localfiles' => array(50,80,),//本地上传方案
		'players' => array(10,20,),//播放器
		'rprojects' => array(10,20,),//远程附件本地化方案
		'watermarks' => array(50,100,),//水印方案
		'pagecaches' => array(1,200,),//页面缓存方案
		
		
		'aurls' => array(100,200,),//管理链接
		'mctypes' => array(10,20,),//认证类型
		'mtypes' => array(50,100,),//管理后台菜单分类
		'menus' => array(300,500,),//管理后台菜单
		'mmtypes' => array(50,100,),//会员中心菜单分类
		'mmenus' => array(300,500,),//会员中心菜单
		'usualurls' => array(100,200,),//常用链接
		
		'cntpls' => array(50,100,),//节点配置
		'mcntpls' => array(50,100,),//节点配置
		'cnconfigs' => array(100,200,),//节点组成方案
		'arc_tpls' => array(1,100,),//文档模板方案
		'o_cntpls' => array(1,100,),//手机节点配置
		'o_cnconfigs' => array(1,100,),//手机节点组成方案
	);
