<?php
	foreach(array('mtcid','gsid','chid','fcaid','aid','cid') as $v) $$v = $GLOBALS[$v];
	$bknavurls = array(
		'house' => array(
			'title' => '房产参数',		   
			'menus' => array(
				'gaoji' => array('经纪人升级',"?entry=extend&extend=exconfigs&action=gaoji"),
				'vipgs' => array('装修商升级',"?entry=extend&extend=exconfigs&action=vipgs"),
				'vipsj' => array('品牌商升级',"?entry=extend&extend=exconfigs&action=vipsj"),
				'upmemberhelp' => array('升级说明',"?entry=extend&extend=exconfigs&action=upmemberhelp"),
				'gssendrules' => array('装修商发布',"?entry=extend&extend=exconfigs&action=gssendrules"),
				'sjsendrules' => array('品牌商发布',"?entry=extend&extend=exconfigs&action=sjsendrules"),				
				'fanyuan' => array('房源发布',"?entry=extend&extend=exconfigs&action=fanyuan"),
				'shangye' => array('商业地产发布',"?entry=extend&extend=exconfigs&action=shangye"),
				'zding' => array('房源置顶',"?entry=extend&extend=exconfigs&action=zding"),
				'weituo' => array('委托房源',"?entry=extend&extend=exconfigs&action=weituo"),
				'yysx' => array('预约刷新',"?entry=extend&extend=exconfigs&action=yysx"),
				'distribution' => array('楼盘分销',"?entry=extend&extend=exconfigs&action=distribution"),
				'closemod' => array('可选模块',"?entry=extend&extend=exconfigs&action=closemod"),
				'fccotype' => array('其它参数',"?entry=extend&extend=exconfigs&action=fccotype"),
			),
		),
		'mobile' => array(
			'title' => '手机版',
			'menus' => array(
				'system'   => array('系统设置',"?entry=o_tplconfig&action=system"),
				'archive'   => array('文档模板',  "?entry=o_tplconfig&action=tplchannel"),
				'cnodes' => array('节点管理',"?entry=o_cnodes&action=cnodescommon"),
				'cnconfigs' => array('节点方案',"?entry=o_cnodes&action=cnconfigs"),
				'cntpls' => array('节点配置',"?entry=o_cnodes&action=cntplsedit"),
				'farchive' => array('副件模板',"?entry=o_tplconfig&action=tplfcatalog"),
				'mtpls'   => array('手机模板库',"?entry=o_mtpls&action=mtplsedit"),
			),
		),
		'sms_admin' => array(
		'title' => '手机短信',
		'menus' => array(
			'sendlog'   => array('发送记录',  "?entry=sms_admin&action=sendlog"),
			'balance'   => array('余额与充值',"?entry=sms_admin&action=balance"),
			'chargelog' => array('充值记录',  "?entry=sms_admin&action=chargelog"),
			'sendsms'   => array('短信发送',  "?entry=sms_admin&action=sendsms"),
			'setapi'    => array('接口设置',  "?entry=sms_admin&action=setapi"), //?entry=mconfigs&action=cfmobmail
            'enable'   => array('模块启用',"?entry=sms_admin&action=enable"),
			'apiwarn'   => array('统计与报警',"?entry=sms_admin&action=apiwarn"),
		),
		),		
		'tpl' => array(
			'title' => '模板设置',
			'menus' => array(
				'base' => array('基本设置',"?entry=tplconfig&action=tplbase"),
				'tplfield' => array('模板变量',"?entry=tplconfig&action=tplfield"),
				'retpl' => array('常规模板库',"?entry=mtpls&action=mtplsedit"),
				'cssjs' => array('CSS/JS文件管理',"?entry=csstpls"),
			),
		),
		'bindtpl' => array(
			'title' => '模板绑定',
			'menus' => array(
				'system' => array('系统模板',"?entry=tplconfig&action=system"),
				//'exhouse' => array('房产扩展',"?entry=tplcfgex&action=exhouse"),
				'channel' => array('文档内容页',"?entry=tplconfig&action=tplchannel"),
				'mchannel' => array('会员模板',"?entry=tplconfig&action=tplmchannel",1),
				'fcatalog' => array('副件内容页',"?entry=tplconfig&action=tplfcatalog",1),
				'freeinfos' => array('独立页面',"?entry=freeinfos&action=freeinfosedit"),
				'cnodes' => array('>类目节点',"?entry=cnodes&action=cnodescommon"),
				'mcnodes' => array('>会员频道节点',"?entry=mcnodes&action=mcnodesedit",1),
			),
		),
		'othertpl' => array(
			'title' => '模板相关',		   
			'menus' => array(
				'tcah' => array('重建模板缓存',"?entry=tplcache"),
				'cssjs' => array('CSS与JS管理',"?entry=csstpls"),
				'db' => array('外部数据源',"?entry=dbsources&action=dbsourcesedit"),
			),
		),
		'catalog' => array(
			'title' => '栏目管理',
			'menus' => array(
				'admin' => array('栏目管理',"?entry=catalogs&action=catalogedit"),
				'adds' => array('批量添加',"?entry=catalogs&action=catalogadds"),
				'fields' => array('栏目字段',"?entry=catalogs&action=cafieldsedit"),
				'mconfigs' => array('栏目参数',"?entry=catalogs&action=mconfigs"),
			),
		),		
		'cata' => array(
			'title' => '类目管理',		   
			'menus' => array(
				'cotype' => array('类系管理',"?entry=cotypes&action=cotypesedit"),
				'cnrel' => array('类目关联管理',"?entry=cnrels&action=cnrelsedit"),
			),
		),
		'faces' => array(
			'title' => '表情相关',		   
			'menus' => array(
				'face' => array('表情管理',"?entry=faces"),
				'update' => array('加载新表情',"?entry=faces&action=update"),
			),
		),
		'project' => array(
			'title' => '网站方案',		   
			'menus' => array(
				'pm' => array('权限方案',"?entry=permissions&action=permissionsedit"),
				'localfile' => array('上传方案',"?entry=localfiles&action=localfilesedit"),
				'rproject' => array('远程下载',"?entry=rprojects&action=rprojectedit"),
				'player' => array('播放器',"?entry=players&action=playersedit"),
				'watermark' => array('水印方案',"?entry=watermark&action=watermarkedit"),
				'pagecache' => array('页面缓存',"?entry=pagecaches"),
			),
		),
		'mtdetail' => array(
			'title' => '空间模板',		   
			'menus' => array(
				'base' => array('基本设置',"?entry=mtconfigs&action=mtconfigdetail&mtcid=$mtcid"),
				'tpl' => array('内容模板',"?entry=mtconfigs&action=mtconfigtpl&mtcid=$mtcid"),
			),
		),
		'backarea' => array(
			'title' => '管理后台配置',
			'menus' => array(
				'bkparam' => array('后台参数',"?entry=backparams&action=bkparams"),
				'amember' => array('后台管理员',"?entry=amembers&action=edit"),
				'm' => array('后台菜单',"?entry=menus&action=menusedit"),
				'config' => array('管理角色',"?entry=amconfigs&action=amconfigsedit"),
				'caedit' => array('后台节点区',"?entry=amconfigs&action=amconfigcaedit"),
				'ausual' => array('常用链接',"?entry=usualurls&action=usualurlsedit"),
			),
		),		
		'mcenter' => array(
			'title' => '会员中心配置',		   
			'menus' => array(
				'mcparam' => array('会员中心参数',"?entry=backparams&action=mcparams"),
				'c' => array('菜单管理',"?entry=mmenus&action=mmenusedit"),
				'musual' => array('常用链接',"?entry=usualurls&action=usualurlsedit&ismc=1"),
				'mguides' => array('会员中心注释',"?entry=mguides"),
			),
		),
		'bannedip' => array(
			'title' => '禁止IP',		   
			'menus' => array(
				'ip' => array('禁止IP',"?entry=bannedips"),
				'cfg' => array('访问记录',"?entry=bannedips&action=visitors"),
			),
		),
		'btags' => array(
			'title' => '原始标识',		   
			'menus' => array(
				'btag' => array('原始标识列表',"?entry=btags"),
				'search' => array('搜索原始标识',"?entry=btagsearch"),
			),
		),
		'channel' => array(
			'title' => '文档模型',		   
			'menus' => array(
				'channel' => array('文档模型管理',"?entry=channels&action=channeledit"),
				'dbsplit' => array('文档主表管理',"?entry=splitbls"),
			),
		),
		'cnode' => array(
			'title' => '类目节点',		   
			'menus' => array(
				'cnodescommon' => array('类目节点管理',"?entry=cnodes&action=cnodescommon"),
				'cnconfigs' => array('节点组成方案',"?entry=cnodes&action=cnconfigs"),
				'cntpls' => array('节点配置管理',"?entry=cnodes&action=cntplsedit"),
				'tpl' => array('>其它模板绑定',"?entry=tplconfig&action=system"),
			),
		),
		'mcnode' => array(
			'title' => '会员节点',		   
			'menus' => array(
				'mcnodesedit' => array('会员频道节点',"?entry=mcnodes&action=mcnodesedit"),
				'mcnodeadd' => array('添加会员节点',"?entry=mcnodes&action=mcnodeadd"),
				'cntpls' => array('节点配置管理',"?entry=mcnodes&action=cntplsedit"),
				'tpl' => array('>其它模板绑定',"?entry=tplconfig&action=system"),
			),
		),
		'currency' => array(
			'title' => '网站积分',		   
			'menus' => array(
				'type' => array('积分类型',"?entry=currencys&action=currencysedit"),
				'project' => array('积分互兑方案',"?entry=currencys&action=crprojects"),
				'price' => array('积分价格方案',"?entry=currencys&action=crprices"),
			),
		),
		'cysave' => array(
			'title' => '积分&现金',
			'menus' => array(
				'pays' => array('会员支付管理',"?entry=pays&action=paysedit"),
				'record' => array('管理员充扣积分',"?entry=currencys&action=cradminlogs"),
				'currency' => array('积分变更记录',"?entry=currencys&action=crlogs"),
			),
		),		
		'data' => array(
			'title' => '数据库相关',		   
			'menus' => array(
				'dbbackup' => array('数据库备份',"?entry=database&action=dbexport"),
				'dbimport' => array('导入数据库备份',"?entry=database&action=dbimport"),
				'dboptimize' => array('优化与修复',"?entry=database&action=dboptimize"),
				'dbsql' => array('执行SQL',"?entry=database&action=dbsql"),
				'dbsource' => array('外部数据源',"?entry=dbsources&action=dbsourcesedit"),
				'dbdict' => array('数据库词典',"?entry=dbdict"),
				'dbdebug' => array('SQL诊断分析',"?entry=dbdebug"),
				'dbstkeys' => array('索引对比',"?entry=dbstkeys&action=compare"),
			),
		),
		'fchannel' => array(
			'title' => '副件架构',		   
			'menus' => array(
				'coclass' => array('副件分类',"?entry=fcatalogs&action=fcatalogsedit"),
				'channel' => array('副件模型',"?entry=fchannels&action=fchannelsedit"),
			),
		),
		'adv' => array(
			'title' => '广告位管理',
			'menus' => array(
				'adv_tpl' => array('模板',"?entry=extend&extend=adv_management&action=adv_tpl&src_type=other&fcaid=$fcaid"),
				'view' => array('预览',"?entry=extend&extend=adv_management&action=view&fcaid=$fcaid"),
			),
		),
		'fragment' => array(
			'title' => '碎片管理',		   
			'menus' => array(
				'fragment' => array('碎片管理',"?entry=fragments&action=fragmentsedit"),
				'catalog' => array('碎片分类',"?entry=frcatalogs&action=frcatalogsedit"),
			),
		),
		'gmiss' => array(
			'title' => '采集任务',		   
			'menus' => array(
				'admin' => array('采集任务管理',"?entry=gmissions&action=gmissionsedit"),
				'model' => array('采集模型管理',"?entry=gmodels&action=gmodeledit"),
			),
		),
		'grule' => array(
			'title' => '采集管理',		   
			'menus' => array(
				'netsite' => array('网址采集',"?entry=gmissions&action=gmissionurls&gsid=$gsid"),
				'content' => array('内容采集',"?entry=gmissions&action=gmissionfields&gsid=$gsid"),
				'output' => array('内容入库',"?entry=gmissions&action=gmissionoutput&gsid=$gsid"),
				'test' => array('测试规则',"?entry=gmissions&action=urlstest&gsid=$gsid"),
			),
		),
		'channelex' => array(
			'title' => '高级设置',		   
			'menus' => array(
				'search' => array('搜索选项',"?entry=channels&action=channeladv&chid=$chid&deal=search"),
				'other' => array('其它扩展',"?entry=channels&action=channeladv&chid=$chid&deal=other"),
				//'group' => array('字段分组',"?entry=channels&action=channeladv&chid=$chid&deal=group"),
				//'region' => array('列表区块',"?entry=channels&action=channeladv&chid=$chid&deal=region"),
			),
		),
		'exconfig' => array(
			'title' => '扩展架构',		   
			'menus' => array(
				'commu' => array('交互项目管理',"?entry=commus&action=commusedit"),
				'abrel' => array('合辑项目管理',"?entry=abrels&action=abrelsedit"),
			),
		),
		'otherset' => array(
			'title' => '附属设置',		   
			'menus' => array(
				'misc' => array('计划任务',"?entry=misc&action=cronedit"),
				'domain' => array('域名管理',"?entry=domains"),
				'email' => array('邮件模板',"?entry=splangs&action=splangsedit"),
			),
		),
		'mchannel' => array(
			'title' => '会员模型',		   
			'menus' => array(
				'grouptype' => array('会员组系管理',"?entry=grouptypes&action=grouptypesedit"),
				'channel' => array('会员模型管理',"?entry=mchannels&action=mchannelsedit"),
				'field' => array('会员通用字段',"?entry=mchannels&action=initmfieldsedit"),
				'mctype' => array('会员认证类型',"?entry=mctypes&action=mctypesedit"),
			),
		),
		'mconfig' => array(
			'title' => '网站参数',		   
			'menus' => array(
				'cfsite' => array('站点设置',"?entry=mconfigs&action=cfsite"),
				'cfvisit' => array('访问注册',"?entry=mconfigs&action=cfvisit",1),
				'cfview' => array('页面设置',"?entry=mconfigs&action=cfview"),
				'cfppt' => array('通行证',"?entry=mconfigs&action=cfppt",1),
				'cfpay' => array('电子商务',"?entry=mconfigs&action=cfpay",1),
				'cfupload' => array('附件设置',"?entry=mconfigs&action=cfupload",1),
				'cfmobmail' => array('邮箱和400电话',"?entry=mconfigs&action=cfmobmail",1),
				'other_site_connect' => array('快捷登陆设置',"?entry=mconfigs&action=other_site_connect",1),				
			),
		),
		'pms' => array(
			'title' => '站内短信',		   
			'menus' => array(
				'manage' => array('短信管理',"?entry=pms&action=pmsmanage"),
				'batch' => array('发送管理',"?entry=pms&action=batchpms"),
				'clear' => array('清理短信',"?entry=pms&action=clearpms"),			
			),
		),
		'record' => array(
			'title' => '站点日志',		   
			'menus' => array(
				'bad' => array('登录出错日志',"?entry=records&action=badlogin"),
				'admin' => array('管理操作日志',"?entry=records&action=adminlog"),
			),
		),
		'static' => array(
			'title' => '页面静态',
			'menus' => array(
				'index' => array('首页静态',"?entry=static&action=index"),
				'cnodes' => array('类目页静态',"?entry=static&action=cnodes"),
				'archives' => array('内容页静态',"?entry=static&action=archives"),
				'mcnodes' => array('会员频道静态',"?entry=static&action=mcnodes",1),
				'freeinfos' => array('独立页静态',"?entry=freeinfos&action=static"),
				'cfstatic' => array('静态参数配置',"?entry=static&action=cfstatic"),
				'statichelp' => array('<span style="color:#00F;font-weight:normal">[静态帮助]</span>',"tools/taghelp.html#p_jtscsm\" target='_blank'"),
			),
		),
			'usualtags' => array(
			'title' => '常用标识',		   
			'menus' => array(
				'usualtags' => array('常用标识',"?entry=usualtags"),
				'tagclasses' => array('常用标识分类',"?entry=usualtags&action=tagclasses"),
			),
		),
		'vote' => array(
			'title' => '投票管理',		   
			'menus' => array(
				'vcata' => array('投票分类',"?entry=vcatalogs&action=vcatalogsedit"),
				'admin' => array('投票管理',"?entry=votes&action=votesedit"),
				'add' => array('添加投票',"?entry=votes&action=voteadd"),
			),
		),
		'wap' => array(
			'title' => 'WAP相关',		   
			'menus' => array(
				'set' => array('WAP设置',"?entry=wap"),
				'lang' => array('WAP语言包',"?entry=wap&action=lang"),
			),
		),
		'memcert' => array(
			'title' => '会员认证相关',		   
			'menus' => array(
				'' => array('认证申请管理',"?entry=memcerts"),
				'memcerts' => array('认证类型管理',"?entry=memcerts&action=memcerts"),
				'add' => array('认证类型添加',"?entry=memcerts&action=add"),
				'cfmobmail' => array('手机和邮箱','?entry=mconfigs&action=cfmobmail'),
				'email' => array('邮件模板',"?entry=splangs&action=splangsedit"),
			),
		),
		'rebuilds' => array(
			'title' => '缓存更新',		   
			'menus' => array(
				'system' => array('更新系统缓存',"?entry=rebuilds"),
				'pagecache' => array('清理页面缓存',"?entry=rebuilds&action=pagecache"),
				'backup' => array('缓存备份',"?entry=rebuilds&action=backup"),
			),
		),
		'mtconfigs' => array(
			'title' => '空间模板方案',
			'menus' => array(
				'mtconfigs' => array('空间模板',"?entry=mtconfigs&action=mtconfigsedit"),
				'mcatalogs' => array('空间栏目',"?entry=mcatalogs&action=mcatalogsedit"),
			),
		),
		'pushareas' => array(
			'title' => '推送位',
			'menus' => array(
				'pusharea' => array('推送位管理',"?entry=pushareas"),
				'pushtype' => array('推送位分类',"?entry=pushtypes"),
			),
		),	

    	'weixin' => array(
    		'title' => '微信设置',
    		'menus' => array(
    			'config' => array('公众平台配置',"?entry=weixin&action=config"),
    			'menu' => array('菜单配置',"?entry=weixin&action=menu"),
    	#		'architecture' => array('功能架构',"?entry=weixin&action=architecture"),
    		),
    	),

        'estate' => array(//楼盘
            'title' => '楼盘价格编辑',
            'menus' => array(
                'price' => array('当前价格',"?entry=extend&extend=jiagearchive&aid=$aid&isnew=1"),
                'list' => array('历史价格列表',"?entry=extend&extend=jiagearchive&aid=$aid&action=list&isnew=1"),
                #'mcnodes' => array('>会员频道节点',"?entry=mcnodes&action=mcnodesedit",1),
                #'historical' => array('历史价格编辑',"?entry=weixin&action=architecture"),
            ),
        ),
        'estate_historical' => array(//楼盘历史价格
            'title' => '楼盘价格编辑',
            'menus' => array(
                'price' => array('当前价格',"?entry=extend&extend=jiagearchive&aid=$aid&isnew=1"),
                'list' => array('历史价格列表',"?entry=extend&extend=jiagearchive&action=list&isnew=1&aid=$aid"),
                #'historical' => array('历史价格编辑',"?entry=weixin&action=architecture"),
            ),
        ),

        'housing_estate' => array(//小区
            'title' => '小区价格编辑',
            'menus' => array(
                'price' => array('当前价格',"?entry=extend&extend=jiagearchive&isnew=0&aid=$aid"),
                'list' => array('历史价格列表',"?entry=extend&extend=jiagearchive&action=list&isnew=0&aid=$aid"),
            ),
        ),
        'housing_estate_historical' => array(//小区历史价格
            'title' => '小区价格编辑',
            'menus' => array(
                'price' => array('当前价格',"?entry=extend&extend=jiagearchive&isnew=0&aid=$aid"),
                'list' => array('历史价格列表',"?entry=extend&extend=jiagearchive&action=list&isnew=0&aid=$aid"),
                #'historical' => array('历史价格编辑',"?entry=weixin&action=architecture"),
            ),
        ),



    );
?>
