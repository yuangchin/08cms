# <?exit();?>
# 08cms InstallPack BasicData Dump
# Version: 08cms House 7.1
# Date: 2016-09-07
# --------------------------------------------------------
# Home: www.08cms.com
# --------------------------------------------------------


INSERT INTO #__pagecaches (pcid,typeid,cname,available,cfgs,period,pagefrom,pageto,vieworder,demourl) VALUES ('2','1','类目节点缓存方案','0','array (\n  \'instr\' => \'caid,ccid\',\n  \'nostr\' => \'\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','index.php?caid=2'),
('3','2','文档页缓存方案','0','array (\n  \'chids\' => \'1,2,3,4,9,10,11,12,13,14\',\n  \'indays\' => \'\',\n  \'instr\' => \'\',\n  \'nostr\' => \'\',\n  \'nomobile\' => \'0\',\n)','3600','1','10','0',''),
('5','3','独立页缓存方案','0','array (\n  \'instr\' => \'fid,aid\',\n  \'nostr\' => \'chid,fid=101,mid,fid=111\',\n)','3600','1','10','0','info.php?fid=102'),
('6','4','楼盘检索缓存方案','0','array (\n  \'instr\' => \'chid=4\',\n  \'nostr\' => \'searchword,tslp,hxs,lcs,zxcd\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','search.php?chid=4&caid=2&ccid3=96'),
('7','4','二手房检索缓存方案','0','array (\n  \'instr\' => \'chid=3\',\n  \'nostr\' => \'searchword,mjfrom,mjto,zjfrom,zjto,ting,wei,chu,fwjg,louxing,fwpt,cx,zxcd,yangtai,ccid34,ccid3,\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','search.php?chid=3&caid=3&mchid=1'),
('8','4','出租检索缓存方案','0','array (\n  \'instr\' => \'chid=2\',\n  \'nostr\' => \'searchword,mjfrom,mjto,zjfrom,zjto,ting,wei,chu,fwjg,louxing,fwpt,cx,zxcd,yangtai,ccid34,ccid3,\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','search.php?chid=2&caid=4&mchid=1'),
('9','4','问答检索缓存方案','0','array (\n  \'instr\' => \'chid=106\',\n  \'nostr\' => \'searchword\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','search.php?chid=106&caid=516&ccid35=3035'),
('10','4','家装案例检索缓存方案','0','array (\n  \'instr\' => \'chid=102\',\n  \'nostr\' => \'searchword\',\n  \'nomobile\' => \'0\',\n)','1200','1','10','0','search.php?chid=102&caid=511&shi=1'),
('11','5','会员节点缓存方案','0','array (\n  \'instr\' => \'mcnid,ccid,ugid\',\n  \'nostr\' => \'\',\n)','1200','1','10','0','member/index.php?mcnid=14&addno=1'),
('12','7','会员空间栏目缓存方案','0','array (\n  \'instr\' => \'mcaid,mid\',\n  \'nostr\' => \'\',\n)','3600','1','10','0',''),
('13','6','会员检索缓存方案','0','array (\n  \'instr\' => \'mchid,szqy,zycp\',\n  \'nostr\' => \'companynm,xingming,lxdh,cmane\',\n)','3600','1','10','0','member/search.php?mchid=3'),
('14','8','空间文档缓存方案','0','array (\n  \'chids\' => \'101,102,103,104\',\n  \'indays\' => \'\',\n  \'instr\' => \'\',\n  \'nostr\' => \'\',\n)','3600','1','10','0',''),
('15','9','js缓存','0','array (\n  \'instr\' => \'tname\',\n  \'nostr\' => \'\',\n)','0','1','10','0','index.php?caid=3'),
('16','1','整站首页','0','array (\n  \'instr\' => \'\',\n  \'nostr\' => \'*\',\n  \'nomobile\' => \'0\',\n)','1200','1','1','0','index.php');
