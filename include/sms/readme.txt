


=== 接口使用 申明 ================================================================================

本系统，仅提供短信接口，与各短信提供商，无任何商业合作！
如接口改变，本系统将按本公司计划进行升级接口或做相关处理；
如接口有改变导致的问题或损失，本系统不负任何责任！
请自行与短信提供商，联系并购买短信接口相关帐号！

=== 短信接口 设置/管理 ===========================================================================

- 设置入口：其他内容->短信接口管理->接口设置->
- 管理入口：其他内容->短信接口管理->
      功能：发送记录/充值与余额/短信发送/接口设置

=== 短信接口 发送/使用 ===========================================================================

$sms = new cls_sms();

- 发送函数：$sms->sendSMS($mobiles,$content,$type='scom')
    $sms->sendSMS($mobiles,$content); //默认,普通会员发送,检测会员余额;
	$sms->sendSMS($mobiles,$content,'sadm'); //管理员后台发送,(不检测余额);
	$sms->sendSMS($mobiles,$content,'ctel'); //手机认证(不检测登陆,自行设置权限,每次一个号码,内容70字以内);
	$sms->sendSMS($mobiles,$content,'1234'); //1234=会员id(整数),以$mid的用户发送并扣余额,(!!!)调用发送的地方请控制好权限,否则,会扣完$mid的余额
- 参数说明：
    mobiles: 手机号码,array/string(英文逗号分开)
    content: 255个字符以内
    type: 发送方式 
- 返回：array($flag,$msg);
    $flag: 标记, 1为成功；
	$msg: 提示信息

=== 文件目录和接口 说明 ============================================================================

- 目录结构 ---------------------

sms/api_0test.php  [流程测试] 接口
sms/api_dxqun.php  [短信群]   接口
sms/api_emay.php   [亿美软通] 接口(webservice发送)
sms/api_emhttp.php [亿美软通] 接口(http发送)
sms/api_eshang8.php[E商网络]  接口
sms/api_winic.php  [移动商务] 接口 
sms/api_***.php    其它扩展的 接口
sms/basic_cfg.php  配置文件
sms/cer_code.js    ajax调用发认证码的js
sms/extra_act.php  某些接口 的扩展操作
sms/readme.txt     说明文件
/libs/classes/api/sms.cls.php 手机短信接口 主控类

- 接口api说明 -------------------

 ------ sms_0test.php  [流程测试] 
测试接口,用于测试系统其它流程,
具体操作不会发短信,仅写一个文件记录表示发短信 

 ------ sms_emay.php   [亿美软通] 北京市朝阳区 
调用方式 (Web Services + soap) 
http://www.emay.cn/

 ------ sms_emhttp.php   [亿美软通] 北京市朝阳区 
调用方式 (Http + Post) 
http://www.emay.cn/

 ------ sms_winic.php  [移动商务] 深圳总部 [php168(qibosoft)v7使用] [08cms-已有接口]
调用方式 (Http + Post) 
http://www.winic.org/index.asp

 ------ sms_dxqun.php 短信群 - 浙江乐清市 [php168(qibosoft)v7使用]
调用方式 (Http + Post) 
http://www.dxqun.com/

 ------ sms_eshang8.php E商网络 - 山东省德州市 [08cms-已有接口]
调用方式 (Http + Get) --- 处理xml
http://www.eshang8.cn
http://sms.eshang8.cn/

 ------ sms_bucp.php 博星科技 - 北京 [未实现]
http://www.bucp.net/

 ------ 商脉无限
www.41186.com

 ------ 广州国宇
sms.gysoft.cn

 ------ 东时方
www.xhsms.com

=== 扩展接口/开发规范 ============================================================================

1. 选用接口及调用方式：考虑系统要适合win/linux平台，所以要选支持跨平台调用方式(如http，Web Services)的接口；
   [2013-08-13]，为调试方便，二此开发时，笔者推荐http+Post调用为首选。
2. 上述“接口api说明”中，选取了几种典型的调用方式，增加的接口可以参考；
3. 现在，拟增加一个接口，在basic_cfg.php中增加一组配置，设配置的数组键为（myapi）；
4. 增加一个类sms_myapi，放在文件sms/api_myapi.php中，
   实现发短信sendSMS($mobiles,$content)，查余额getBalance()方法；
5. 统一返回值：array($flag,$msg)格式; $flag为标记, 1为成功，-1为失败；$msg: 为提示信息；查余额$msg为具体余额；

=== 数据库-结构变更 =====================================================================================

ALTER TABLE  `cms_members` ADD  `sms_charge` mediumint(8) DEFAULT '0' COMMENT  '短信余额(条)'

CREATE TABLE cms_sms_recharge (
  cid mediumint(8) NOT NULL auto_increment COMMENT '自动ID',
  mid mediumint(8) NOT NULL default '0' COMMENT '会员ID',
  mname varchar(15) character set gb2312 NOT NULL COMMENT '会员名',
  stamp int(10) NOT NULL default '0' COMMENT '时间',
  ip varchar(48) default NULL COMMENT 'ip',
  cnt int(11) NOT NULL default '0',
  msg varchar(255) default NULL COMMENT '信息',
  note varchar(255) default NULL,
  PRIMARY KEY  (cid)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk COMMENT='短信充值记录';

CREATE TABLE `cms_sms_sendlogs` (
  `cid` mediumint(8) NOT NULL auto_increment COMMENT '自动ID',
  `mid` mediumint(8) NOT NULL default '0' COMMENT '会员ID',
  `mname` varchar(15) character set gb2312 NOT NULL COMMENT '会员名',
  `stamp` int(10) NOT NULL default '0' COMMENT '时间',
  `ip` varchar(48) default NULL COMMENT 'ip',
  `tel` varchar(255) default NULL COMMENT '号码',
  `msg` varchar(255) default NULL COMMENT '信息',
  `res` varchar(255) default NULL COMMENT '结果',
  `api` varchar(24) default NULL COMMENT '接口/发送方式',
  `cnt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=gbk COMMENT='短信发送记录';

=== End End  =====================================================================================

