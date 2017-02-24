<?php
$_mconfigs = array (

	//不同系统可能会有差异的设置,注意维护!!!!!!!!!!==================== 
	'templatedir' => 'blue', // 模板目录 : 默认值：default
	'cn_max_addno' => '5', //类目节点附加页最大数量
	'mcn_max_addno' => '1', //会员频道节点附加页最大数量
	'max_addno' => '12', //文档附加内容页最大数量 
	'cms_regcode' => 'register,login,admin,payonline,archive,archive8,archive106,archive108,archive101,commu1,commu2,commu3,commu4,commu5,commu8,commu32,commu33,commu35,commu40,commu45,commu46', //启用验证码
	'hostname' => '08cms房产门户系统',
	'cmsname' => '08CMS房产门户网站', //保留,兼容之前定义的cmsname
	'enable_mobile' => '1', //开启手机版-是
	'unique_email' => '1',//一个邮箱只能注册一个账号-否
	
	// house特殊参数
	'nouser_exts' => 'gif,jpg', //游客允许上传附件类型-
	'nouser_capacity' => '300', //游客上传大小限制-300K 
	'close_gpub' => '0', //关闭游客发布房源-否
	'count_gpub' => '3', //游客发布房源条数-3
	
	//所有系统共同保持的设置，基本上不需要变动==================== 
	
	// 1. api,plus : 接口,插件
	'sms_cfg_api' => '(close)', //手机短信-接口提供商
	'enable_uc' => '0', //启用UCenter-否
	'enable_pptout' => '0', //启用通行证-服务端   
	'enable_pptin' => '0', //启用通行证-客户端   
	'onlineautosaving' => '1', //在线支付到帐自动充值-否 
	'ftp_enabled' => '0', //启用附件FTP上传-否
	'webcall_enable' => '0', //网站提供400总机-否
	'user_session' => '0', //启用跨站SESSION-否
	'qq_closed' => '0', //QQ登陆-关闭 
	'sina_closed' => '0', //新浪微博登陆-关闭 
	
	// 2. model,function : 功能模块,开关设置
	'cmsclosed' => '0', // 站点关闭-否
	'registerclosed' => '0', //站点关闭注册-否
	'gzipenable' => '0', //是否启用页面Gzip压缩
	'enablestatic' => '0', //是否启用静态-否
	'virtualurl' => '0', //前台动态页面url虚拟静态-否

	// 3. 路径相关
	'disable_htmldir' => '1',//不启用文档及类目节点静态总目录(html)
	'dir_userfile' => 'userfiles', //附件路径(相对系统根路径)
	'memberdir' => 'member', //会员频道路径
	'mspacedir' => 'mspace', //会员空间路径
	'mobiledir' => 'mobile', //手机版路径
	'infohtmldir' => 'info', //独立页静态路径:默认值：info
	'mc_dir' => 'adminc', //会员中心目录  默认值：adminc
	
	// 4. 核心,杂项
	'no_deepmode' => '1', //启用架构保护模式-是
	'cms_idkeep' => '0', //启用架构升级模式插入相关id-否
	'viewdebug' => '0', //前台页面显示查询统计-否
	
	'debugenabled' => '0', //是否收集页面SQL记录(SQL诊断设置)
	'mallowfloatwin' => '1', //启用浮动窗口(会员中心)-是
	'debugtag' => '0', //模板解析设为调试状态-否
#	'arccustomurl' => '{$topdir}/{$y}{$m}{$d}/{$aid}_{$addno}_{$page}.html',  //  文档页静态保存格式
	
	'timezone' => '-8', //站点时区,+-相反了
#	'cmslogo' => 'images/common/indlogo.gif', //站点Logo, logo.png
	'regcode_width' => '60', //验证码图片宽度(像素)
	'regcode_height' => '25', //验证码图片高度(像素)
	'aeisablepinyin' => '0', //启用自动拼音-是 ---------- 注意这个值是相反的
	'aallowfloatwin' => '1', //启用浮动窗口-是
	
	// 5. 访问,查看,统计
	'search_repeat' => '0',                //搜索时间间隔限制(秒),默认为0  
	'enabelstat' => '1',                  //启用网站统计:默认为1
	'clickscachetime' => '10',            //点击统计的缓存周期 :默认为10
	'statweekmonth' => '1',               //启用文档点击周月统计  :默认为1
	'amaxerrtimes' => '3',              //登录最大尝试错误次数:  默认值：3
	'aminerrtime' => '60',              //闲置自动退出时间(分钟):  默认值：60

	// 6. 微信相关
	'weixin_enable' => '0',
	'weixin_login_register' => '0',
	'weixin_url' => '',
	'weixin_token' => '',
	'weixin_appid' => '',
	'weixin_appsecret' => '',
	'weixin_qrcode' => '',

	'vs_holdtime' => '0', //记录最近访问记录
	'adminipaccess' => '', //记录最近访问记录
	'censoruser' => '', //记录最近访问记录
	'jsrefsource' => '', //js动态调用只允许以下来路
	'debugtag' => '1', //模板解析设为调试状态
	'search_pmid' => '0',                  //搜索文档的权限设置,本值设为O即可
	'msearch_pmid' => '0',                  //搜索文档的权限设置,本值设为O即可
	
	// x. 附加
	'cfg_alipay' => '',
	'cfg_alipay_partnerid' => '2088*',
	'cfg_alipay_keyt' => '',
	'cfg_tenpay' => '',
	'cfg_tenpay_keyt' => '',
	'mail_from' => '*@163.com',
	'mail_user' => '*@163.com',
	'mail_pwd' => '*',
	'qq_appid' => '',
	'qq_appkey' => '',
	'sina_appid' => '',
	'sina_appkey' => '',
	'baidu_push_api' => 'http://data.zz.baidu.com/urls?site=www.example.com&token=*',
	
	// del-无用的配置,后续删除
	'css_dir' => 'css', //模板css目录  默认值：css
	'js_dir' => 'js', //模板js目录  默认值：js
	//'msgcode_mode' => '0', //(手机设置)认证模式-关闭认证 ??? 	
	//'o_index_tpl' => '', //手机版首页模板 ---- 关闭了？？？
	
	'noinj_cfg_GET' => '1', //暂保留兼容;$_GET 防注入-是
	'noinj_cfg_POST' => '0', //暂保留兼容;$_POST 防注入-否
	//'hometpl' => 'v4_index.html', //首页模板

) ;
?>