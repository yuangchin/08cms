<?php
foreach(array('chid','lpchid','aid') as $v) $$v = $GLOBALS[$v];//链接中需要传送的变量，需要加进来
$wxnavname = $aid ? "楼盘[$aid]公众号" : '公众号配置';
$mcnavurls = array(
	'payonline' => array(
		'record' => array('支付记录',"?action=pays"),
		'online' => array('在线支付',"?action=payonline"),
		'other' => array('其它支付',"?action=payother"),
	),
	'currency' => array(
		'record' => array('积分记录',"?action=crrecords"),
		'exchange' => array('积分兑换',"?action=crexchange"),
	),
	'pm' => array(
		'box' => array('短信列表',"?action=pmbox"),
		'send' => array('发送短信',"?action=pmsend"),
	),
	'loupanbar' => array(
		'loupan' => array('管理楼盘',"?action=loupans"),
		'zixun' => array('楼盘资讯',"?action=zixuns"),	
		'xiangce' => array('楼盘相册',"?action=xiangces"),
		'huxing' => array('楼盘户型',"?action=huxings"),	
	),	
	'loupanbus' => array(
		'loupan' => array('管理楼盘',"?action=loupans&lpchid=$lpchid"),
		'zixun' => array('楼盘资讯',"?action=zixuns&lpchid=$lpchid"),	
		'xiangce' => array('楼盘相册',"?action=xiangces&lpchid=$lpchid"),
		//'huxing' => array('楼盘户型',"?action=huxings"),	
	),	
	'tuijian' => array(
		'tjchid2' => array('出租',"?action=tuijianarchives&chid=2"),
		'tjchid3' => array('出售',"?action=tuijianarchives&chid=3"),		
	),
	'cuxuqiu' => array(
		'list9' => array('求租信息',"?action=xuqiugzs&chid=9"),
		'list10' => array('求购信息',"?action=xuqiugzs&chid=10"),
	),
	'chushou' => array(
		'manage' => array('全部二手房源',"?action=chushouarchives&chid=3&valid=-1"),
		'shangjia' => array('已上架房源',"?action=chushouarchives&chid=3&valid=1"),
		'cangku' => array('已下架房源',"?action=chushouarchives&chid=3&valid=0"),
		'ershoufabu' => array('发布二手房',"?action=chushouadd&chid=3&caid=3"),
		'maifang' => array('买房意向',"?action=commu_yixiang&chid=3&valid=3"),		
	),
	'chuzu' => array(
		'manage' => array('全部出租房源',"?action=chuzuarchives&chid=2&valid=-1"),
		'shangjia' => array('已上架房源',"?action=chuzuarchives&chid=2&valid=1"),		
		'cangku' => array('已下架房源',"?action=chuzuarchives&chid=2&valid=0"),
		'czfabu' => array('发布出租',"?action=chuzuadd&chid=2"),		
		'zufang' => array('租房意向',"?action=commu_yixiang&chid=2&valid=2"),	
	),
    'bussell_office' => array(
        'manage' => array('全部写字楼',"?action=bus_chushouarchives&chid=117&valid=-1"),
        'shangjia' => array('已上架写字楼',"?action=bus_chushouarchives&chid=117&valid=1"),
        'cangku' => array('已下架写字楼',"?action=bus_chushouarchives&chid=117&valid=0"),
        'ershoufabu' => array('发布写字楼出售',"?action=bus_chushouadd&chid=117&caid=613"),
        'maifang' => array('买写字楼意向',"?action=commu_yixiang&chid=117&valid=3"),
    ),
    'busrent_office' => array(
        'manage' => array('全部出租写字楼',"?action=bus_chuzuarchives&chid=119&valid=-1"),
        'shangjia' => array('已上架出租写字楼',"?action=bus_chuzuarchives&chid=119&valid=1"),
        'cangku' => array('已下架出租写字楼',"?action=bus_chuzuarchives&chid=119&valid=0"),
        'czfabu' => array('发布出租写字楼',"?action=bus_chuzuadd&chid=119&caid=614"),
        'zufang' => array('租写字楼意向',"?action=commu_yixiang&chid=119&valid=2"),
    ),
    'bussell_shop' => array(
        'manage' => array('全部出售商铺',"?action=bus_chushouarchives&chid=118&valid=-1"),
        'shangjia' => array('已上架出售商铺',"?action=bus_chushouarchives&chid=118&valid=1"),
        'cangku' => array('已下架出售商铺',"?action=bus_chushouarchives&chid=118&valid=0"),
        'ershoufabu' => array('发布出售商铺',"?action=bus_chushouadd&chid=118&caid=617"),
        'maifang' => array('买商铺意向',"?action=commu_yixiang&chid=118&valid=3"),
    ),
    'busrent_shop' => array(
        'manage' => array('全部出租商铺',"?action=bus_chuzuarchives&chid=120&valid=-1"),
        'shangjia' => array('已上架出租商铺',"?action=bus_chuzuarchives&chid=120&valid=1"),
        'cangku' => array('已下架出租商铺',"?action=bus_chuzuarchives&chid=120&valid=0"),
        'czfabu' => array('发布出租商铺',"?action=bus_chuzuadd&chid=120&caid=618"),
        'zufang' => array('租商铺意向',"?action=commu_yixiang&chid=120&valid=2"),
    ),
    'company' => array(
		'manage' => array('我的经纪公司',"?action=tocomp"),
		'cash' => array('公司资金',"?action=zijing"),
	),
	'xuqiu' => array(
		'list9' => array('求租信息',"?action=xuqiuarchives&chid=9"),
		'list10' => array('求购信息',"?action=xuqiuarchives&chid=10"),		
		'qzadd' => array('发布求租',"?action=xuqiuarchive&chid=9"),
		'qgadd' => array('发布求购',"?action=xuqiuarchive&chid=10"),	
	),
	'zhaopin' => array(
		'manage' => array('全部招聘信息',"?action=zhaopinarchives"),
		'fubuzp' => array('发布招聘',"?action=zhaopinadd")
	),
	'kuaiwen' => array(
		'qget' => array('给我的问题',"?action=wenda_manage&actext=qget"),
		'qout' => array('我的提问',"?action=wenda_manage&actext=qout"),		
		'answer' => array('我的回答',"?action=wenda_manage&actext=answer")	
	),
	'designNews' => array(
		'manage' => array('内容列表',"?action=designNews_s"),
		'add' => array('添加公司动态',"?action=designNews_a")
	),
	'agents' => array(
		'incheck1' => array('正式经纪人',"?action=agents&incheck=1"),
		'incheck0' => array('待审经纪人',"?action=agents")
	),
	'design' => array(
		'manage' => array('设计师列表',"?action=design_s"),
		'add' => array('设计师添加',"?action=design_a&chid=101&caid=510")
	),
	'designCase' => array(
		'manage' => array('案例列表',"?action=designCase_s"),
		'nocheck' => array('案例添加',"?action=designCase_a&chid=$chid&caid=511&pid31=-1")
	),
	'designGoods' => array(
		'manage' => array('内容列表',"?action=designGoods_s"),
		'add' => array('添加商品',"?action=designGoods_a&chid=103&caid=513")
	),
	'sms_member' => array(
		'sendlog'   => array('发送记录',  "?action=sms_member&section=sendlog"),
		'balance'   => array('余额与充值',"?action=sms_member&section=balance"),
		'chargelog' => array('充值记录',  "?action=sms_member&section=chargelog"),
		'sendsms'   => array('短信发送',  "?action=sms_member&section=sendsms"),
	),
	'account' => array(
		'pwd' => array('修改密码',"?action=memberpwd"),
		'bind' => array('帐号绑定',"?action=memberbind"),
	),	
	'scangs' => array(		
		'ch3' => array('出售房源',"?action=scangs&chid=3"),
		'ch2' => array('出租房源',"?action=scangs&chid=2"),
	),
	'scxuqiu' => array(		
		'ch9' => array('关注求租',"?action=scangs&chid=9"),
		'ch10' => array('关注求购',"?action=scangs&chid=10"),
	),
	'scshye' => array( //商业地产		
		'ch115' => array('写字楼楼盘',"?action=scangs&chid=115"),
		'ch116' => array('商铺楼盘',"?action=scangs&chid=116"),
		'ch117' => array('写字楼出售',"?action=scangs&chid=117"),
		'ch118' => array('商铺出售',"?action=scangs&chid=118"),
		'ch119' => array('写字楼出租',"?action=scangs&chid=119"),
		'ch120' => array('商铺出租',"?action=scangs&chid=120"),
	),
	'tejia' => array(		
		'manage' => array('特价房管理',"?action=tejiaarchives"),
		'add' => array('添加特价房',"?action=tejiaarchive"),
	),
	'weituo' => array(		
		'chushou' => array('出售房源委托管理',"?action=delegations&chid=3"),
		'chuzu' => array('出租房源委托管理',"?action=delegations&chid=2"),
	),
	'yongjin' => array(		
		'yjgets' => array('提取佣金',"?action=fxmy_brokerage&part=yjgets"),
		'yjlist' => array('提取记录',"?action=fxmy_brokerage&part=yjlist"),
	),
	
	'weixin' => array(
		'config' => array($wxnavname,"?action=weixin&section=config&aid=$aid&tab=$tab"),
		'menu' => array('菜单配置',"?action=weixin&section=menu&aid=$aid&tab=$tab"),
		'follow' => array('关注者管理',"?action=weixin&section=follow&aid=$aid&tab=$tab"),
		'message' => array('消息管理',"?action=weixin&section=message&aid=$aid&tab=$tab"),
		'keyword' => array('关键词管理',"?action=weixin&section=keyword&aid=$aid&tab=$tab"),
	), //&mid=$mid
	
);
?>
