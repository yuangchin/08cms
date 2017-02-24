<?php
/*
其它分类下的索引，把bclass设置成other并sclass设置成以下键名即可：
'mp' => '分页',
'attachment' => '附件',
'vote' => '投票',

****** 标识类型 ***************
    	'common' => '通用信息',
    	'archives' => '文档相关',
    	'catalogs' => '类目相关',
    	'farchives' => '副件相关',
        'pushs' => '推送相关',
    	'commus' => '交互相关',
    	'members' => '会员相关',
    	'others' => '其它',
********************************


****** 数据类型 ***************
    	'text' => '单行文本',
    	'multitext' => '多行文本',
    	'htmltext' => 'Html文本',
    	'image' => '单图',
    	'images' => '图集',
    	'flash' => 'Flash',
    	'flashs' => 'Flash集',
    	'media' => '视频',
    	'medias' => '视频集',
    	'file' => '单点下载',
    	'files' => '多点下载',
    	'select' => '单项选择',
    	'mselect' => '多项选择',
    	'cacc' => '类目选择',
    	'date' => '日期(时间戳)',
    	'int' => '整数',
    	'float' => '小数',
    	'map' => '地图',
    	'vote' => '投票',
    	'texts' => '文本集',
********************************


****** 输入格式 ***************
	public static $aidsdd = array (
		'ename' => 'aidsdd',//标识英文名称
		'cname' => '文档IDssssssssssssssss',//标识中文名称
		'bclass' => 'archives',//标识类型
		'sclass' => 40,//标识子分类ID
		'datatype' => 'int',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 0,//是否在主表
	);
********************************
*/
class cac_btagnames
{
/******** 通用信息 **********/
	public static $hostname = array (
		'ename' => 'hostname',
		'cname' => '站点名称',
		'bclass' => 'common',
		'sclass' => '',
		'datatype' => 'text',
		# 是否为当前子分类下公用
		'iscommon' => 1,
		# 是否在主表
		'maintable' => 1
	);
	
	public static $hosturl = array (
	  'ename' => 'hosturl',
	  'cname' => '站点域名',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cmsname = array (
	  'ename' => 'cmsname',
	  'cname' => '站点名称',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cms_abs = array (
	  'ename' => 'cms_abs',
	  'cname' => '站点首页URL',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $memberurl = array (
	  'ename' => 'memberurl',
	  'cname' => '会员频道首页',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $mspaceurl = array (
	  'ename' => 'mspaceurl',
	  'cname' => '个人空间首页',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cmsindex = array (
	  'ename' => 'cmsindex',
	  'cname' => '站点首页',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cmstitle = array (
	  'ename' => 'cmstitle',
	  'cname' => '站点标题',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	public static $cmskeyword = array (
	  'ename' => 'cmskeyword',
	  'cname' => '站点关键词',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cmsdescription = array (
	  'ename' => 'cmsdescription',
	  'cname' => '站点描述',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $tplurl = array (
	  'ename' => 'tplurl',
	  'cname' => '模板位置URL',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $mcharset = array (
	  'ename' => 'mcharset',
	  'cname' => '站点页面编码',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cms_version = array (
	  'ename' => 'cms_version',
	  'cname' => 'cms版本编号',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cmslogo = array (
	  'ename' => 'cmslogo',
	  'cname' => '站点LOGO',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'image',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $copyright = array (
	  'ename' => 'copyright',
	  'cname' => '站点版权信息',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $cms_icpno = array (
	  'ename' => 'cms_icpno',
	  'cname' => '站点备案信息',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $bazscert = array (
	  'ename' => 'bazscert',
	  'cname' => '备案证书',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'text',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	
	public static $timestamp = array (
	  'ename' => 'timestamp',
	  'cname' => '当前系统时间戳',
	  'bclass' => 'common',
	  'sclass' => '',
	  'datatype' => 'int',
	  # 是否为当前子分类下公用
	  'iscommon' => 1,
	  # 是否在主表
	  'maintable' => 1
	);
	public static $cms_statcode = array (
		'ename' => 'cms_statcode',//标识英文名称
		'cname' => '第三方统计代码',//标识中文名称
		'bclass' => 'common',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $sn_row = array (
		'ename' => 'sn_row',//标识英文名称
		'cname' => '列表中的行编号',//标识中文名称
		'bclass' => 'common',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $arcurl = array (
		'ename' => 'arcurl',//标识英文名称
		'cname' => '内容页_URL',//标识中文名称
		'bclass' => 'archives',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $arcurl1 = array (
		'ename' => 'arcurl1',//标识英文名称
		'cname' => '附加页1_URL',//标识中文名称
		'bclass' => 'archives',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $marcurl = array (
		'ename' => 'marcurl',//标识英文名称
		'cname' => '文档的空间内容页',//标识中文名称
		'bclass' => 'archives',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpnav = array (
		'ename' => 'mpnav',//标识英文名称
		'cname' => '分页导航',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mptitle = array (
		'ename' => 'mptitle',//标识英文名称
		'cname' => '(文本)分页标题',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mppage = array (
		'ename' => 'mppage',//标识英文名称
		'cname' => '分页当前页',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpcount = array (
		'ename' => 'mpcount',//标识英文名称
		'cname' => '分页总页数',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpstart = array (
		'ename' => 'mpstart',//标识英文名称
		'cname' => '分页首页URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpend = array (
		'ename' => 'mpend',//标识英文名称
		'cname' => '分页尾页URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mppre = array (
		'ename' => 'mppre',//标识英文名称
		'cname' => '分页上页URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpnext = array (
		'ename' => 'mpnext',//标识英文名称
		'cname' => '分页下页URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mpacount = array (
		'ename' => 'mpacount',//标识英文名称
		'cname' => '分页总记录数',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mp',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $url = array (
		'ename' => 'url',//标识英文名称
		'cname' => '附件URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title = array (
		'ename' => 'title',//标识英文名称
		'cname' => '附件说明',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $url_s = array (
		'ename' => 'url_s',//标识英文名称
		'cname' => '图片缩略图URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $width = array (
		'ename' => 'width',//标识英文名称
		'cname' => '图片宽度',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $height = array (
		'ename' => 'height',//标识英文名称
		'cname' => '图片高度',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
    	public static $link = array (
		'ename' => 'link',//标识英文名称
		'cname' => '图片属性2',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'attachment',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $vid = array (
		'ename' => 'vid',//标识英文名称
		'cname' => '投票项目ID',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $caid = array (
		'ename' => 'caid',//标识英文名称
		'cname' => '投票分类ID',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $subject = array (
		'ename' => 'subject',//标识英文名称
		'cname' => '投票标题',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $content = array (
		'ename' => 'content',//标识英文名称
		'cname' => '投票说明',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $totalnum = array (
		'ename' => 'totalnum',//标识英文名称
		'cname' => '总票数',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mid = array (
		'ename' => 'mid',//标识英文名称
		'cname' => '发起人会员ID',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mname = array (
		'ename' => 'mname',//标识英文名称
		'cname' => '发起人会员',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $createdate = array (
		'ename' => 'createdate',//标识英文名称
		'cname' => '投票添加时间',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $vopid = array (
		'ename' => 'vopid',//标识英文名称
		'cname' => '投票选项ID',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title_1 = array (
		'ename' => 'title',//标识英文名称
		'cname' => '投票选项标题',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $votenum = array (
		'ename' => 'votenum',//标识英文名称
		'cname' => '投票选项票数',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $input = array (
		'ename' => 'input',//标识英文名称
		'cname' => '投票选项控件',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $percent = array (
		'ename' => 'percent',//标识英文名称
		'cname' => '投票选项百分比',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'vote',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $indexurl = array (
		'ename' => 'indexurl',//标识英文名称
		'cname' => '类目节点_URL',//标识中文名称
		'bclass' => 'catalogs',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $indexurl1 = array (
		'ename' => 'indexurl1',//标识英文名称
		'cname' => '节点附加页1_URL',//标识中文名称
		'bclass' => 'catalogs',//标识类型
		'sclass' => '',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 1,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $word = array (
		'ename' => 'word',//标识英文名称
		'cname' => '关键词',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'keywords',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $wordlink = array (
		'ename' => 'wordlink',//标识英文名称
		'cname' => '关键词关联的URL',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'keywords',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $mcaid = array (
		'ename' => 'mcaid',//标识英文名称
		'cname' => '空间栏目id',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mcatalogs',//标识子分类ID
		'datatype' => 'int',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title_mc = array (
		'ename' => 'title',//标识英文名称
		'cname' => '空间栏目名称',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'mcatalogs',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title0 = array (
		'ename' => 'title0',//标识英文名称
		'cname' => '第一个选项内容',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'texts',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title1 = array (
		'ename' => 'title1',//标识英文名称
		'cname' => '第二个选项内容',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'texts',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	public static $title2 = array (
		'ename' => 'title2',//标识英文名称
		'cname' => '第三个选项内容',//标识中文名称
		'bclass' => 'others',//标识类型
		'sclass' => 'texts',//标识子分类ID
		'datatype' => 'text',//数据类型
		'iscommon' => 0,//是否为当前子分类下公用
		'maintable' => 1,//是否在主表
	);
	
}