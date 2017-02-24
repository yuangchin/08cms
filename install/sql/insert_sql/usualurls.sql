# <?exit();?>
# 08cms InstallPack BasicData Dump
# Version: 08cms House 7.1
# Date: 2016-09-07
# --------------------------------------------------------
# Home: www.08cms.com
# --------------------------------------------------------


INSERT INTO #__usualurls (uid,title,url,logo,ismc,available,vieworder,pmid,newwin,onclick) VALUES ('1','发布出租房','?action=chuzuadd','','1','1','0','109','0',''),
('2','发布二手房','?action=chushouadd','','1','1','0','109','0',''),
('3','更新缓存','?entry=rebuilds','','0','1','0','0','0',''),
('4','标识还原','?entry=tags_restore','','0','1','0','0','0',''),
('5','复合标识','?entry=mtags&action=mtagsedit&ttype=ctag','','0','1','0','0','0',''),
('6','模板库','?entry=mtpls&action=mtplsedit','','0','1','0','0','0',''),
('7','管理链接','?entry=usualurls&action=usualurlsedit','','0','1','1','0','0',''),
('8','常用设置','?entry=tplconfig&action=tplfield','','0','1','0','0','0','');
