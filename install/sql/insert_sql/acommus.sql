# <?exit();?>
# 08cms InstallPack BasicData Dump
# Version: 08cms House 7.1
# Date: 2016-09-07
# --------------------------------------------------------
# Home: www.08cms.com
# --------------------------------------------------------


INSERT INTO #__acommus (cuid,cname,remark,available,tbl,cfgs0,cfgs,vieworder,content) VALUES ('1','评论','资讯,专题,看房团,图库,楼盘,写字楼楼盘,商铺楼盘','1','commu_zixun','','array (\n  \'chids\' => \n  array (\n    1 => \'4\',\n    2 => \'112\',\n    3 => \'115\',\n    4 => \'116\',\n    5 => \'110\',\n    6 => \'1\',\n    7 => \'14\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'0\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0','模板资料调用--------------------------------------------------\r\nopt1支持次数\r\n表单使用说明--------------------------------------------------\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}'),
('2','楼盘评分','楼盘的评分','1','commu_dp','','array (\n  \'chids\' => \n  array (\n    1 => \'4\',\n    2 => \'115\',\n    3 => \'116\',\n  ),\n  \'pmid\' => \'23\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'5\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0','模板资料调用--------------------------------------------------\r\ntotal为单条总评分\r\n\r\n表单使用说明--------------------------------------------------\r\n前台点评链接：etools/dianpin.php?aid=xx\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}'),
('3','楼盘订阅','楼盘意向','1','commu_yx','','array (\n  \'chids\' => \n  array (\n    1 => \'4\',\n    2 => \'115\',\n    3 => \'116\',\n  ),\n  \'pmid\' => \'0\',\n  \'issms\' => \'1\',\n  \'smscon\' => \'您好！您订阅的楼盘是:{$subject}；地址:{$address}；楼盘联系电话:{$tel}\',\n  \'issmshout\' => \'1\',\n  \'repeattime\' => \'3\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0','模板资料调用--------------------------------------------------\r\n\r\n表单使用说明--------------------------------------------------\r\n前台点评链接：etools/yixiang.php?aid=xx\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}'),
('4','房源举报','','1','commu_jubao','','array (\n  \'chids\' => \n  array (\n    1 => \'3\',\n    2 => \'2\',\n  ),\n  \'pmid\' => \'0\',\n  \'repeattime\' => \'\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0','表单使用说明--------------------------------------------------\r\n前台点评链接：etools/jubao.php?aid=xx\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}'),
('5','空间留言','经纪人','1','commu_liuyan','','array (\n  \'chids\' => \n  array (\n    1 => \'2\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'\',\n)','0','表单使用说明--------------------------------------------------\r\n前台链接：{$mspaceurl}liuyan.php?mid=xx\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}\r\n\r\n调用记录\r\ntomid 为被留言的经纪人\r\nmid 为发出留言的会员，可能是游客。'),
('6','文档收藏','','1','commu_sc','','array (\n  \'chids\' => \n  array (\n    1 => \'115\',\n    2 => \'116\',\n    3 => \'117\',\n    4 => \'118\',\n    5 => \'119\',\n    6 => \'120\',\n    7 => \'3\',\n    8 => \'2\',\n    9 => \'9\',\n    10 => \'10\',\n    11 => \'13\',\n    12 => \'103\',\n    13 => \'106\',\n  ),\n  \'pmid\' => \'0\',\n)','0','表单使用说明--------------------------------------------------\r\n前台链接：etools/scang.php?aid=xx'),
('7','楼盘收藏','','1','commu_gz','','array (\n  \'chids\' => \n  array (\n    0 => \'4\',\n  ),\n  \'pmid\' => \'0\',\n)','0','表单使用说明--------------------------------------------------\r\n前台链接：etools/guanzu.php?aid=xx&new=1&old=1&rent=1\r\n后三个参数分别表示：新房动态，新增二手房，新增出租时通知我。可以传0-3个参数。'),
('8','新房团购','','1','commu_df','','array (\n  \'chids\' => \n  array (\n    1 => \'5\',\n  ),\n  \'pmid\' => \'0\',\n  \'repeattime\' => \'0\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0','模板资料调用--------------------------------------------------\r\n\r\n表单使用说明--------------------------------------------------\r\n前台点评链接：etools/dfang.php?aid=xx\r\n表单按钮：bsubmit，表单数组前缀：fmdata\r\n表单中来源页链接传送：forward={forward}'),
('9','需求收藏_暂不用','','0','commu_xgz','','array (\n  \'chids\' => \n  array (\n    0 => \'9\',\n    1 => \'10\',\n  ),\n  \'pmid\' => \'0\',\n)','0','表单使用说明--------------------------------------------------\r\n前台链接：etools/xguanzu.php?aid=xx'),
('10','公司资金','','1','commu_zj','','','0','1. 前台无界面，在会员中心操作，由经济公司 分配/提取 资金给 经纪人；\r\n2. 此资金 直接更新到 会员现金字段(currency0)；'),
('11','店铺收藏','','1','commu_dsc','','array (\n  \'chids\' => \n  array (\n    1 => \'2\',\n    2 => \'3\',\n    3 => \'11\',\n    4 => \'12\',\n  ),\n  \'pmid\' => \'0\',\n)','0','表单使用说明--------------------------------------------------\r\n前台链接：{$spaceurl}scang.php?mid=xx'),
('31','业主评论','装修公司','1','commu_yezhupl','','array (\n  \'chids\' => \n  array (\n    0 => \'11\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'5\',\n)','0','说明：\r\n业主等会员给 装修公司 的评论。\r\n提交地址：\r\nmspace/yezhupl.php?mid=$mid'),
('32','免费量房','','1','commu_housemeasure','','array (\n  \'chids\' => \n  array (\n    1 => \'11\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'5\',\n)','0',''),
('33','品牌建材商品购买','品牌建材商品购买','1','commu_brandbuy','','array (\n  \'chids\' => \n  array (\n    0 => \'103\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'6\',\n)','0','http://192.168.1.12/house/etools/brandbuy.php?aid=589899'),
('34','空间留言','品牌商家','1','commu_brandsjly','','array (\n  \'chids\' => \n  array (\n    0 => \'12\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'5\',\n)','0',''),
('35','商品团购报名','商品团购报名','1','commu_huodongbm','','array (\n  \'chids\' => \n  array (\n    1 => \'105\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'5\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0',''),
('36','委托房源','委托房源(手机短信通知功能)','1','commu_weituo','','array (\n  \'issms\' => \'1\',\n  \'smsfee\' => \'sadm\',\n  \'smscon\' => \'您好！联系人{$lxr}（电话{$tel}）委托了房源给您，请尽快回复！\',\n  \'chids\' => \n  array (\n  ),\n)','0','1. 使用独立页（101），前台调用方式\r\n{$cms_abs}info.php?fid=101&chid=2委托出租\r\n{$cms_abs}info.php?fid=101&chid=3委托出售\r\n2. 由业主或任何人，前台添加资料，选择经纪人，\r\n   由经济人决定 是否 接受该资料。'),
('37','问答答案','问答交互','1','commu_answers','','array (\n  \'chids\' => \n  array (\n    0 => \'106\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeatanswer\' => \'1\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0',''),
('38','问答举报','举报','1','commu_jbask','','array (\n  \'chids\' => \n  array (\n    0 => \'106\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'\',\n  \'acurrency\' => \'1\',\n  \'ccurrency\' => \'3\',\n)','0',''),
('39','问答收藏_暂不用','问答收藏','0','commu_scask','','array (\n  \'chids\' => \n  array (\n    0 => \'106\',\n  ),\n  \'pmid\' => \'0\',\n)','0',''),
('40','网站提问','网站提问','1','commu_webtw','','array (\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'0\',\n  \'repeattime\' => \'0\',\n)','0',''),
('41','资讯读后感','资讯读后感','1','commu_zxdp','','array (\n  \'chids\' => \n  array (\n    1 => \'1\',\n  ),\n  \'repeattime\' => \'\',\n)','0',''),
('42','专家团申请','只放状态,具体资料存在会员通用字段','1','commu_expert','','','0','1. 前台无界面，由会员中心申请\r\n2. 审核后 成为正式专家组；'),
('43','房源预约刷新','预约刷新房源','1','commu_yuyue','','','0','1. 前台无界面，经纪人针对房源 设置 预约刷新 并扣费；\r\n2. 具体执行，由计划任务执行；'),
('44','楼盘印象','存储楼盘印象数据','1','commu_impression','','array (\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'0\',\n  \'totalnum\' => \'3\',\n  \'yxnum\' => \'15\',\n  \'chids\' => \n  array (\n  ),\n)','0',''),
('45','看房活动报名','看房团','1','commu_kanfang','','array (\n  \'chids\' => \n  array (\n    0 => \'\',\n    1 => \'110\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'0\',\n  \'acurrency\' => \'5\',\n  \'ccurrency\' => \'10\',\n)','0',''),
('46','房源意向','二手房,出租的意向(手机短信通知功能)','1','commu_fyyx','','array (\n  \'chids\' => \n  array (\n    1 => \'117\',\n    2 => \'118\',\n    3 => \'119\',\n    4 => \'120\',\n    5 => \'3\',\n    6 => \'2\',\n  ),\n  \'issms\' => \'1\',\n  \'smsfee\' => \'sadm\',\n  \'smscon\' => \'您好！联系人{$uname}（电话{$utel}）对你的房源（{$subject}）有意向需求，请尽快回复！\',\n  \'pmid\' => \'0\',\n  \'repeattime\' => \'1\',\n)','0','调用：\r\netools/yixiang2.php?aid=xxxx&chid=xx\r\n注意带chid参数。\r\n---------------------------------------------------------\r\n时间：2013-04-xx  // 添加人：peace // 版本：v4.0\r\n修改1：201x-xx-xx  // 添加人：xx // 版本：vxx\r\n修改2：201x-xx-xx  // 添加人：xx // 版本：vxx\r\n说明：---------------------------------------------------\r\n\r\n1. 目的：本系统房源栏目很具有商业价值的栏目；\r\n   特添加 [房源意向] 交互。\r\n   因为考虑会员可能长时间不上网 而部署短信通知，\r\n   所以：针对本交互等，发送一条短信通知；\r\n\r\n2.  *. 当有人发布 房源意向（交互）时，发送短信 给 经纪人；\r\n    － 扣费方式 由 后台 交互设置，\r\n       网站运营初期，可设置为（管理员）即网站主 是 短信费用承担者 ；\r\n       网站运营后期，可设置为（意向接收者）即经纪人 是 短信费用承担者 ；\r\n       [意向发布者] 一般不用这个设置；\r\n\r\n3.  *. 短信发送记录\r\n\r\n - 如果 会员作为 短信费用承担者，则会员中心 会有 “系统代发”的 短信发送记录。\r\n   权限配置 请根据这个设置一致；\r\n\r\n - 手机短信 成功发送 条件 ：\r\n   开启交互的 手机短信通知 + 开启系统的 手机短信接口（并有有效余额）\r\n   如果  短信费用承担者 为 会员，且会员 有 短信余额，则发短信，\r\n   如 会员 没有 短信余额，则不真实发短信，只有会员中心一条代发记录；'),
('47','价格趋势','楼盘、二手房、出租的价格趋势(时间：month)','1','commu_pricetrend','','','0',''),
('48','楼盘评论暂不用','楼盘、写字楼、商铺评论及回复','0','commu_lppl','','array (\n  \'chids\' => \n  array (\n    1 => \'4\',\n    2 => \'115\',\n    3 => \'116\',\n  ),\n  \'pmid\' => \'0\',\n  \'autocheck\' => \'1\',\n  \'repeattime\' => \'\',\n)','0',''),
('49','分销推荐信息','存放推荐客户看房信息','1','commu_customer','','','0','权限等判断到代码中去实现。'),
('50','佣金提款信息','存放从分销中获得佣金的提款信息','1','commu_fxtikuan','','','0','权限等判断到代码中去实现。'),
('101','直播信息','发言人的信息','1','commu_live','','','0','');