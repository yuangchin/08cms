<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('webparam')) cls_message::show($re);
foreach(array('currencys','commus','channels','cotypes','mconfigs',) as $k) $$k = cls_cache::Read($k);
$mconfigs = cls_cache::Read('mconfigs');
if($action == 'cfsite'){
	backnav('mconfig','cfsite');
	if(!submitcheck('bmconfigs')){
		tabheader('基本设置','cfsite',"?entry=mconfigs&action=cfsite",2,1);
		trbasic('站点名称','mconfigsnew[hostname]',$mconfigs['hostname'],'text',array('guide'=>'前台调用样式:{$hostname}'));
		trbasic('站点域名','mconfigsnew[hosturl]',$mconfigs['hosturl'],'text',array('guide'=>'<li>1、应含http，结尾勿含 /</li><li>2、前台调用样式{$cms_abs}</li>'));
		trbasic('站点在域名下的相对路径','mconfigsnew[cmsurl]',$mconfigs['cmsurl'],'text',array('guide'=>'结尾需含 /'));
		trbasic('会员频道路径','mconfigsnew[memberdir]',$mconfigs['memberdir'],'text',array('guide'=>'会员频道路径，不要带/。{$memberdir}调用路径，{$memberurl}调用url。'));
		trbasic('会员空间路径','mconfigsnew[mspacedir]',$mconfigs['mspacedir'],'text',array('guide'=>'会员空间路径，不要带/。{$mspacedir}调用路径，{$mspaceurl}调用url。'));
		$tzarr=array(
			'+12'=>'(GMT-12) International Date Line (West)',
			'+11'=>'(GMT-11) Midway Island,Samoa',
			'+10'=>'(GMT-10) Hawaii,Honolulu',
			'+9'=>'(GMT-9) Alaska',
			'+8'=>'(GMT-8) Pacific Standard Time,US,Canada',
			'+7'=>'(GMT-7) British Columbia N.E.,Santa Fe,Mountain Time',
			'+6'=>'(GMT-6) Central America,Chicago,Guatamala,Mexico City',
			'+5'=>'(GMT-5) US,Canada,Bogota,Boston,New York',
			'+4'=>'(GMT-4) Canada,Santiago,Atlantic Standard Time',
			'+3'=>'(GMT-3) Brazilia,Buenos Aires,Georgetown,Greenland',
			'+2'=>'(GMT-2) Mid-Atlantic',
			'+1'=>'(GMT-1) Azores,Cape Verde Is.,Western Africa Time',
			'0'=>'(GMT) London,Iceland,Ireland,Morocco,Portugal',
			'-1'=>'(GMT+1) Amsterdam,Berlin,Bern,Madrid,Paris,Rome',
			'-2'=>'(GMT+2) Athens,Cairo,Cape Town,Finland,Greece,Israel',
			'-3'=>'(GMT+3) Ankara,Aden,Baghdad,Beruit,Kuwait,Moscow',
			'-4'=>'(GMT+4) Abu Dhabi,Baku,Kabul,Tehran,Tbilisi,Volgograd',
			'-5'=>'(GMT+5) Calcutta,Colombo,Islamabad,Madras,New Dehli',
			'-6'=>'(GMT+6) Almaty,Dhakar,Kathmandu,Colombo,Sri Lanka',
			'-7'=>'(GMT+7) Bangkok,Hanoi,Jakarta,Phnom Penh,Australia',
			'-8'=>'(GMT+8) Beijing,Hong Kong,Singapore,Taipei',
			'-9'=>'(GMT+9) Seoul,Tokyo,Central Australia',
			'-10'=>'(GMT+10) Brisbane,Canberra,Guam,Melbourne,Sydney',
			'-11'=>'(GMT+11) Magadan,New Caledonia,Solomon Is.',
			'-12'=>'(GMT+12) Auckland,Fiji,Kamchatka,Marshall,Wellington'
		);
		trbasic('设置站点时区','mconfigsnew[timezone]',makeoption($tzarr,isset($timezone)?$timezone:-8),'select');	
		trbasic('积分变更记录有效期',"mconfigsnew[point_interval]",(empty($mconfigs['point_interval']) ? 0 : $mconfigs['point_interval']),'text',array('guide'=>'单位：月，清除多少月之前的除现金以外的所有积分类型的记录，留空为不清除。','validate'=>makesubmitstr("mconfigsnew[point_interval]",0,'int',0,4)));
		tabfooter();
		
		tabheader('地图参数');
		trbasic('地图初始定位坐标','',"<input class='btnmap' type='button' onmouseover='this.onfocus()' onfocus='_08cms.map.setButton(this,\"marker\",\"mconfigsnew[init_map]\",\"\",\"13\");' /> <label for='mconfigsnew[init_map]'>纬度,经度：</label><input type='text' id='mconfigsnew[init_map]' name='mconfigsnew[init_map]' value='".@$mconfigs['init_map']."' style='width:150px'>",'',array('guide'=>'地图字段中的默认初始位置'));
        trbasic('地图初始缩放级别','',"<label for='mconfigsnew[init_map_zoom]'>缩放级别：</label><input type='text' id='mconfigsnew[init_map_zoom]' name='mconfigsnew[init_map_zoom]' value='".@$mconfigs['init_map_zoom']."' style='width:50px'>",'',array('guide'=>'地图初始缩放级别，请输入1-19的整数'));
		trbasic('百度地图KEY','',"<label for='mconfigsnew[bmapkey]'></label><input type='text' id='mconfigsnew[bmapkey]' name='mconfigsnew[bmapkey]' value='".@$mconfigs['bmapkey']."' style='width:320px'>",'',array('guide'=>'可点击<a href=\'http://lbsyun.baidu.com/apiconsole/key?application=key\' style=\'color:blue;\' target=\'_blank\'>>>申请百度地图密钥</a>进行申请。申请成功后，把密钥复制黏贴到文本框中，提交即可。支持地图API2.0版本，不填默认为老版本地图，新版本支持查询公交，路线发手机等功能,但在IE6浏览器上表现不友好'));		
        trbasic('街景地图显示类型','',makeradio('mconfigsnew[streetviewtype]', array('Tencent'=>'腾讯街景','noview'=>'关闭街景'), empty($mconfigs['streetviewtype'])?'noview':$mconfigs['streetviewtype']),'',array('guide'=>'目前街景覆盖的城市只是一小部分大城市。假如您的城市不在覆盖范围内，可选择\'关闭街景\'。<br/>查看<a href=\'http://map.qq.com/jiejing/city.html\' style=\'color:blue;\' target=\'_blank\'>>>腾讯街景城市</a>'));
        trbasic('街景地图KEY','',"<label for='mconfigsnew[streetviewkey]'></label><input type='text' id='mconfigsnew[streetviewkey]' name='mconfigsnew[streetviewkey]' value='".@$mconfigs['streetviewkey']."' style='width:320px'>",'',array('guide'=>'可点击<a href=\'http://open.map.qq.com/key.html\' style=\'color:blue;\' target=\'_blank\'>>>申请腾讯密钥</a>进行申请。申请成功后，把密钥复制黏贴到文本框中，提交即可。'));
        tabfooter();
		
		tabheader('百度参数');
        trbasic('编辑器appkey','',"<label for='mconfigsnew[ueditor_appkey]'></label><input type='text' id='mconfigsnew[ueditor_appkey]' name='mconfigsnew[ueditor_appkey]' value='".@$mconfigs['ueditor_appkey']."' style='width:320px'>",'',array('guide'=>'用于在百度编辑器里插入百度应用，可<a href=\'http://app.baidu.com/static/cms/getapikey.html\' style=\'color:blue;\' target=\'_blank\'>>>点此申请</a>进行申请。申请成功后，把密钥复制黏贴到文本框中，提交即可。'));
        tabfooter();

		
		tabheader('网站统计');
		trbasic('启用网站统计','mconfigsnew[enabelstat]',$mconfigs['enabelstat'],'radio');
		trbasic('点击统计的缓存周期','mconfigsnew[clickscachetime]',$mconfigs['clickscachetime'],'text',array('guide' => '单位：秒，影响文档及空间的点击统计。如统计即时性要求不高，设成大于600为宜。'));
		trbasic('启用文档点击周月统计','mconfigsnew[statweekmonth]',@$mconfigs['statweekmonth'],'radio',array('guide' => '需要配合计划任务中的清除周月统计功能来完成。'));
		tabfooter();
		
		tabheader('广告缓存设置','cfupload','?entry=mconfigs&action=cfupload');
        trbasic('内容缓存周期',"mconfigsnew[adv_period]",(empty($mconfigs['adv_period']) ? 0 : $mconfigs['adv_period']),'text',array('guide'=>'单位：分钟，留空为不缓存。','validate'=>makesubmitstr("mconfigsnew[adv_period]",0,'int',0,4)));
        trbasic('浏览量统计周期',"mconfigsnew[adv_viewscache]",(empty($mconfigs['adv_viewscache']) ? 0 : $mconfigs['adv_viewscache']),'text',array('guide'=>'单位：分钟，留空为即时统计。','validate'=>makesubmitstr("mconfigsnew[adv_viewscache]",0,'int',0,4)));
		tabfooter('bmconfigs');
		a_guide('cfsite');
	}else{
		if(empty($mconfigsnew['hosturl']) || !in_str('http://',$mconfigsnew['hosturl'])){
			cls_message::show('主机URL不合规范',M_REFERER);
		}
		$mconfigsnew['hosturl'] = strtolower($mconfigsnew['hosturl']);
		$mconfigsnew['cmsurl'] = empty($mconfigsnew['cmsurl']) ? '/' : trim(strtolower($mconfigsnew['cmsurl']));
		$mconfigsnew['cmsurl'] .= (substr($mconfigsnew['cmsurl'], strlen($mconfigsnew['cmsurl']) - 1) == '/' ? '' : '/');
		$mconfigsnew['cmsname'] = $mconfigsnew['hostname'] = trim(strip_tags($mconfigsnew['hostname']));//兼容之前定义的cmsname

		foreach(array('mspacedir','memberdir',) as $var){
			$mconfigsnew[$var] = strtolower($mconfigsnew[$var]);
			if($mconfigsnew[$var] == $mconfigs[$var]) continue;
			if(!$mconfigsnew[$var] || preg_match("/[^a-z_0-9]+/",$mconfigsnew[$var])){
				$mconfigsnew[$var] = $mconfigs[$var];
				continue;
			}
			if($mconfigs[$var] && is_dir(M_ROOT.$mconfigs[$var])){
				if(!rename(M_ROOT.$mconfigs[$var],M_ROOT.$mconfigsnew[$var])) $mconfigsnew[$var] = $mconfigs[$var];
			}else mmkdir(M_ROOT.$mconfigsnew[$var],0);
		}
		
		$mconfigsnew['adv_period'] = max(0,intval($mconfigsnew['adv_period']));		       
        $mconfigsnew['init_map_zoom'] = max(0,intval($mconfigsnew['init_map_zoom']));
		$mconfigsnew['adv_viewscache'] = max(0,intval($mconfigsnew['adv_viewscache']));
		
		saveconfig('site');
		adminlog('网站设置','站点信息');
		cls_message::show('网站设置完成',M_REFERER);
	}
}elseif($action == 'cfvisit'){
	backnav('mconfig','cfvisit');
	if(!submitcheck('bmconfigs')){
		tabheader('访问设置','cfvisit','?entry=mconfigs&action=cfvisit');
		trbasic('站点关闭','mconfigsnew[cmsclosed]',$mconfigs['cmsclosed'],'radio');
		trbasic('站点关闭原因','mconfigsnew[cmsclosedreason]',$mconfigs['cmsclosedreason'],'text',array('w'=>50));
		trbasic('会员空间关闭','mconfigsnew[mspacedisabled]',$mconfigs['mspacedisabled'],'radio');
		tabfooter();

		tabheader('会员注册设置');
		trbasic('站点关闭注册','mconfigsnew[registerclosed]',$mconfigs['registerclosed'],'radio');
		trbasic('注册关闭原因','mconfigsnew[regclosedreason]',$mconfigs['regclosedreason'],'text',array('w'=>50));
		trbasic('允许同一 Email 地址注册多个用户','mconfigsnew[unique_email]', @$mconfigs['unique_email'],'radio');
		trbasic('用户名称保留字','mconfigsnew[censoruser]',$mconfigs['censoruser'],'textarea',array('guide'=>'用户名不能使用列表中的关键词，每行填写一个关键词，允许使用通配符 *'));
		tabfooter();

		tabheader('会员访问设置');
		trbasic('登录最大尝试错误次数','mconfigsnew[maxerrtimes]',$mconfigs['maxerrtimes'],'text',array('guide'=>'留空表示不限错误次数，建议设为3。'));
		trbasic('登录失败锁定时间','mconfigsnew[minerrtime]',$mconfigs['minerrtime'],'text',array('guide'=>'单位:分钟，建议为60分钟。'));
		trbasic('会员活动状况更新周期','mconfigsnew[onlinetimecircle]',$mconfigs['onlinetimecircle'],'text',array('guide'=>'单位:分钟，设为10-20分钟为宜。'));
		trbasic('会员活动记录保存时间','mconfigsnew[onlinehold]',$mconfigs['onlinehold'],'text',array('guide'=>'单位:小时，时间长度请大于登录失败锁定时间及会员活动时间更新周期，设为6小时为宜。'));
		tabfooter();

		tabheader('验证码设置');
		$arr = cls_cache::exRead('cfregcodes');
		foreach($arr as $k => $v) $arr[$k] = $v.'-'.$k;
		trbasic('需要启用的验证码','',makecheckbox('mconfigsnew[cms_regcode][]',$arr,empty($mconfigs['cms_regcode']) ? array() : explode(',',$mconfigs['cms_regcode']),5),'');
		trbasic('启用验证码自定义字体样式','mconfigsnew[regcode_style]', @$mconfigs['regcode_style'],'radio', array('guide'=>'如果启用该选项，则直接在 /images/fonts 目录里修改字体文件即可（注：文件名称只能是数字、字母、下划线组合，_08 开头的和 simsun.ttc 为系统字体不能删除）' ));
		trbasic('验证码组合方式','mconfigsnew[regcode_mode]', makeoption(array('1' => '数字', '2' => '字母', '3' => '数字与字母'), @$mconfigs['regcode_mode']), 'select');
		trbasic('验证码图片宽度(像素)','mconfigsnew[regcode_width]',$mconfigs['regcode_width'], 'text', array('guide'=>'建议宽度值：200，如果需要调整请与建议高度值成正比例调整' ));
		trbasic('验证码图片高度(像素)','mconfigsnew[regcode_height]',$mconfigs['regcode_height'], 'text', array('guide'=>'建议高度值：70，如果需要调整请与建议宽度值成正比例调整' ));
		tabfooter();

		tabheader('搜索设置');
		setPermBar('搜索文档的权限设置', 'mconfigsnew[search_pmid]', @$mconfigs['search_pmid'], 'aread', 'open', '');
		setPermBar('搜索会员的权限设置', 'mconfigsnew[msearch_pmid]', @$mconfigs['msearch_pmid'], 'aread', 'open', '');
        trbasic('搜索时间间隔限制(秒)','mconfigsnew[search_repeat]',$mconfigs['search_repeat']);
		tabfooter();

		tabheader('RSS设置');
		trbasic('启用RSS','mconfigsnew[rss_enabled]',$mconfigs['rss_enabled'],'radio');
		trbasic('RSS刷新周期(分钟)','mconfigsnew[rss_ttl]',$mconfigs['rss_ttl']);
		tabfooter('bmconfigs');
		a_guide('cfvisit');
	}else{
		$mconfigsnew['maxerrtimes'] = max(0,intval($mconfigsnew['maxerrtimes']));
		$mconfigsnew['minerrtime'] = max(1,intval($mconfigsnew['minerrtime']));
		$mconfigsnew['onlinetimecircle'] = max(1,intval($mconfigsnew['onlinetimecircle']));
		$mconfigsnew['onlinehold'] = max(1,intval($mconfigsnew['onlinehold']));

		$mconfigsnew['search_repeat'] = max(0,intval($mconfigsnew['search_repeat']));
		$mconfigsnew['regcode_width'] = max(60,intval($mconfigsnew['regcode_width']));
		$mconfigsnew['regcode_height'] = max(20,intval($mconfigsnew['regcode_height']));
		$mconfigsnew['cms_regcode'] = empty($mconfigsnew['cms_regcode']) ? '' : implode(',',$mconfigsnew['cms_regcode']);
		$mconfigsnew['rss_ttl'] = empty($mconfigsnew['rss_ttl']) ? 30 : max(0,intval($mconfigsnew['rss_ttl']));
		saveconfig('visit');
		adminlog('网站设置','访问与注册设置');
		cls_message::show('网站设置完成','?entry=mconfigs&action=cfvisit');
	}
}elseif($action == 'cfview'){
	backnav('mconfig','cfview');
	if(!submitcheck('bmconfigs')){
		tabheader('页面通用设置','cfview',"?entry=mconfigs&action=cfview");
		trbasic('页面Gzip压缩','mconfigsnew[gzipenable]',$mconfigs['gzipenable'],'radio');
		trbasic('默认日期格式','mconfigsnew[dateformat]',makeoption(array('Y-m-d' => '例：'.'2008-01-01','Y-n-j' => '例：'.'2008-1-1',),$mconfigs['dateformat']),'select');
		trbasic('默认时间格式','mconfigsnew[timeformat]',makeoption(array('H:i' => '例：'.'20:30','H:i:s' => '例：'.'20:30:30',),$mconfigs['timeformat']),'select');
		trbasic('前台提示信息停留(毫秒)','mconfigsnew[msgforwordtime]',$mconfigs['msgforwordtime']);
		tabfooter();

		tabheader('伪静态设置');
		trbasic('开启前台url伪静态','mconfigsnew[virtualurl]',$mconfigs['virtualurl'],'radio');
		trbasic('.php?的Rewrite对应字串','mconfigsnew[rewritephp]',$mconfigs['rewritephp'],'text',array('guide'=>'如设置为-htm-，则伪静态url 如archive.php?aid=5，将封装为archive-htm-aid-5.html。<br>设置在url伪静态开启时有效，请保持与站点rewrite规则相对应。'));
        trbasic('服务器软件或Rewrite版本','mconfigsnew[serversoft]',makeoption(array('0' => '自动获取','apache' => 'APACHE', 'nginx' => 'NGINX', 'iis_2' => 'IIS ISAPI Rewrite2或以下', 'iis_3' => 'IIS ISAPI Rewrite3或以上'), empty($mconfigs['serversoft']) ? 0 : $mconfigs['serversoft']),'select',array('guide'=>'<span style="color:red;">1、注：如果网站根目录下已经存在Rewrite规则文件(IIS的是httpd.ini、NGINX或APACHE的是 .htaccess)并且您修改过时，请先自行备份。</span><br />2、Rewrite规则文件只有启用了：前台动态页面url伪静态和修改过：.php?的Rewrite对应字串 时才会重新生成文件到网站根目录。<br />3、请尽量手动选择获取，因为自动选择获取默认的是以最新版本生成,所以可能会有获取不准确或者有网络延时从而影响服务器的问题。'));
		tabfooter();

		tabheader('页面附加页数量');
		trbasic('类目节点附加页最大数量','mconfigsnew[cn_max_addno]',empty($mconfigs['cn_max_addno']) ? 0 : $mconfigs['cn_max_addno']);
		trbasic('文档附加内容页最大数量','mconfigsnew[max_addno]',empty($mconfigs['max_addno']) ? 0 : $mconfigs['max_addno']);
		trbasic('会员频道节点附加页最大数量','mconfigsnew[mcn_max_addno]',empty($mconfigs['mcn_max_addno']) ? 0 : $mconfigs['mcn_max_addno']);
		tabfooter('bmconfigs');
		a_guide('cfview');

	}else{
		$mconfigsnew['msgforwordtime'] = max(0,intval($mconfigsnew['msgforwordtime']));
		$mconfigsnew['cn_max_addno'] = min(empty($_sys_cnaddmax) ? 2 : $_sys_cnaddmax,max(0,intval($mconfigsnew['cn_max_addno'])));
		$mconfigsnew['mcn_max_addno'] = min(empty($_sys_mcnaddmax) ? 0 : $_sys_mcnaddmax,max(0,intval($mconfigsnew['mcn_max_addno'])));
		$mconfigsnew['max_addno'] = min(empty($_sys_addmax) ? 3 : $_sys_addmax,max(0,intval($mconfigsnew['max_addno'])));
        if ( ($mconfigsnew['rewritephp'] != $mconfigs['rewritephp']) || ($mconfigsnew['serversoft'] != $mconfigs['serversoft']) )
        {
            $rewrite = new _08_Rewrite($mconfigsnew['rewritephp'], $mconfigsnew['virtualurl']);
            $rewrite->create($mconfigsnew['serversoft'], $mconfigsnew['virtualurl']);
        }
		saveconfig('view');
		adminlog('网站设置','页面相关设置');
		cls_message::show('网站设置完成',"?entry=mconfigs&action=cfview");
	}
}elseif($action == 'cfppt'){
	backnav('mconfig','cfppt');
	if(!submitcheck('bmconfigs')){
		tabheader('UCenter 客户端配置','cfppt','?entry=mconfigs&action=cfppt');
		trbasic('启用UCenter','mconfigsnew[enable_uc]',$mconfigs['enable_uc'],'radio');
		trbasic('UCenter 连接方式','',makeradio('mconfigsnew[uc_connect]', array('mysql'=>'mysql(比较稳定,推荐)','post'=>'post(不需要UCenter数据库连接)'), $mconfigs['uc_connect']=='post' ? 'post' : 'mysql'),''); //
		trbasic('UCenter API 地址','mconfigsnew[uc_api]',$mconfigs['uc_api'],'text',array('guide' => '末尾不需斜杆。','w' => 50,));
		trbasic('UCenter 主机IP','mconfigsnew[uc_ip]',$mconfigs['uc_ip'],'text',array('guide' => '通常留空，因域名解析而通信失败时请设置该值。',));
		trbasic('UCenter 数据库主机名','mconfigsnew[uc_dbhost]',$mconfigs['uc_dbhost']);
		trbasic('UCenter 数据库名','mconfigsnew[uc_dbname]',$mconfigs['uc_dbname']);
		trbasic('UCenter 数据库用户名','mconfigsnew[uc_dbuser]',$mconfigs['uc_dbuser']);
		trbasic('UCenter 数据库密码','mconfigsnew[uc_dbpwd]',$mconfigs['uc_dbpwd'],'password',array('validate' => ' autocomplete="off"'));#禁止浏览器自动完成表单
		trbasic('UCenter 数据库表前缀','mconfigsnew[uc_dbpre]',$mconfigs['uc_dbpre']);
		trbasic('UCenter 分配的应用ID','mconfigsnew[uc_appid]',$mconfigs['uc_appid']);
		trbasic('UCenter 通信密钥','mconfigsnew[uc_key]',$mconfigs['uc_key']);
		trbasic('UCenter 记录调试信息','mconfigsnew[uc_debug]',$mconfigs['uc_debug'],'radio',array('guide' => '否：正常情况下选否，是：记录调试信息(<a href="'.$cms_abs.'api/uc.php?code=uclog_view" target="_blank">今日调试信息</a>)。'));
		tabfooter();
		$pfilearr = array('08cms' => '08CMS','phpwind' => 'PHPwind',);
		$pcharsetarr = array('gbk' => 'GBK/GB2312','utf-8' => 'UTF-8','big5' => 'BIG5',);
		$pptenable = array(1 => '启用', 0 => '禁用');
	#	$pptmode = array(1 => '服务端', 0 => '客户端');
		tabheader('WindID 通行证客户端配置');
		trbasic('启用通行证','',makeradio('is_enable_ppt', $pptenable, $mconfigs['enable_pptout'] || $mconfigs['enable_pptin']),'');
		trbasic('通行证连接方式','',makeradio('mconfigsnew[pptout_connect]', array('db'=>'mysql(比较稳定,推荐)','http'=>'http(不需要通行证数据库连接)'), @$mconfigs['pptout_connect']=='http' ? 'http' : 'db'),'');
		trbasic('通行证服务端地址','mconfigsnew[pptin_url]',$mconfigs['pptin_url'], 'text',array('guide' => '末尾不需斜杆。','w' => 50,));
	#	trbasic('将本系统作为','',makeradio('ppt_mode', $pptmode, $mconfigs['enable_pptout'] ? 1 : ($mconfigs['enable_pptin'] ? 0 : -1)),'');
		trbasic('接口程序字符集','mconfigsnew[pptout_charset]',makeoption($pcharsetarr,$mconfigs['pptout_charset']),'select',array('guide' => '请保持接口程序字符集与数据库字符集相同。'));
		trbasic('通行证密钥','ppt_key',$mconfigs['pptin_key'] ? $mconfigs['pptin_key'] : $mconfigs['pptout_key']);
	#	echo '<tr><td class="txt txtleft fB borderright" colspan="2"><div style="margin:0 100px; padding:0 10px;color:#134D9D; background:#F1F7FD">服务端</div></td></tr>';
#		trbasic('接口程序URL地址','mconfigsnew[pptout_url]',$mconfigs['pptout_url']);
#		echo '<tr><td class="txt txtleft fB borderright" colspan="2"><div style="margin:0 100px; padding:0 10px;color:#134D9D; background:#F1F7FD">客户端</div></td></tr>';
		trbasic('验证字串有效期(秒)','mconfigsnew[pptin_expire]',$mconfigs['pptin_expire']);
		trbasic('通行证应用ID','mconfigsnew[pptin_appid]', @$mconfigs['pptin_appid']);
		trbasic('通行证数据库主机名','mconfigsnew[pptin_dbhost]', empty($mconfigs['pptin_dbhost']) ? 'localhost' : $mconfigs['pptin_dbhost']);
		trbasic('通行证数据库端口','mconfigsnew[pptin_port]', empty($mconfigs['pptin_port']) ? '3306' : $mconfigs['pptin_port']);
		trbasic('通行证数据库名','mconfigsnew[pptin_dbname]',@$mconfigs['pptin_dbname']);
		trbasic('通行证数据库用户名','mconfigsnew[pptin_dbuser]',@$mconfigs['pptin_dbuser']);
		trbasic('通行证数据库密码','mconfigsnew[pptin_dbpwd]',@$mconfigs['pptin_dbpwd'],'password',array('validate' => ' autocomplete="off"'));#禁止浏览器自动完成表单
		trbasic('通行证数据库表前缀','mconfigsnew[pptin_dbpre]',@$mconfigs['pptin_dbpre'],'text',array('guide' => 'windid的表前缀是默认系统表前缀加上windid_（如pw_，windid的表前缀为pw_windid_）'));
	#	trbasic('接口程序注册地址','mconfigsnew[pptin_register]',$mconfigs['pptin_register']);
	#	trbasic('接口程序登录地址','mconfigsnew[pptin_login]',$mconfigs['pptin_login']);
	#	trbasic('接口程序退出地址','mconfigsnew[pptin_logout]',$mconfigs['pptin_logout']);
  
		tabfooter('bmconfigs');
		a_guide('cfppt');
	}else{
		if(($mconfigsnew['enable_uc'] && empty($mconfigs['enable_uc']) || !$is_enable_ppt)){
			//使用UC
			$mconfigsnew['enable_pptout'] = 0;
			$mconfigsnew['enable_pptin']  = 0;
		}else{
		    $ppt_mode = 0; #指定为客户端
			$mconfigsnew['enable_uc'] = 0;
			if(empty($ppt_mode)){
				//使用客户端
				$mconfigsnew['enable_pptout'] = 0;
				$mconfigsnew['enable_pptin']  = 1;
				$mconfigsnew['pptin_key']	  = $ppt_key;
				$mconfigsnew['pptout_key']	  = '';
			}else{
				//使用服务端
				$mconfigsnew['enable_pptout'] = 1;
				$mconfigsnew['enable_pptin']  = 0;
				$mconfigsnew['pptin_key']	  = '';
				$mconfigsnew['pptout_key']	  = $ppt_key;
			}
		}

		saveconfig('ppt');
		adminlog('网站设置','网站pptput反向通行证设置');
		cls_message::show('网站设置完成','?entry=mconfigs&action=cfppt');
	}
}elseif($action == 'cfpay'){
	backnav('mconfig','cfpay');
	if(!submitcheck('bmconfigs')){
		tabheader('商务相关基本设置','cfpay','?entry=mconfigs&action=cfpay');
		trbasic('在线支付到帐自动充值','mconfigsnew[onlineautosaving]',$mconfigs['onlineautosaving'],'radio');
		$pmodearr = array('0' => '货到付款','1' => '站内帐户支付','2' => '支付宝即时到账','3' => '财付通支付','4' => '支付宝网银支付','5' => '支付宝手机支付');
		$payarr = array();
		for($i = 0; $i < 32; $i++)if(@$mconfigs['cfg_paymode'] & (1 << $i))$payarr[] = $i;
		trbasic('支付模式','',makecheckbox('paymodenew[]',$pmodearr,$payarr),'');
		tabfooter();

		tabheader('支付宝-在线支付设置<span style="color:red;">（注：开启支付功能必须先开启PHP的CURL、OPENSSL扩展）</span>');
		trbasic('支付宝帐户','mconfigsnew[cfg_alipay]',@$mconfigs['cfg_alipay']);
		trbasic('合作者身份(PID)','mconfigsnew[cfg_alipay_partnerid]',@$mconfigs['cfg_alipay_partnerid']);
		trbasic('安全校验码(Key)','mconfigsnew[cfg_alipay_keyt]', @$mconfigs['cfg_alipay_keyt'], 'password');
		tabfooter();
		tabheader('财付通-在线支付设置');
		trbasic('商户编号','mconfigsnew[cfg_tenpay]',@$mconfigs['cfg_tenpay']);
		trbasic('支付密钥','mconfigsnew[cfg_tenpay_keyt]',@$mconfigs['cfg_tenpay_keyt'], 'password');
		tabfooter('bmconfigs');
		a_guide('cfpay');
	}else{
		$mconfigsnew['cfg_paymode'] = 0;
		empty($paymodenew) && $paymodenew = array();
		foreach($paymodenew as $v){
			if($v==='') continue; //第一个空值取消掉,0不能去掉
			$mconfigsnew['cfg_paymode'] = $mconfigsnew['cfg_paymode'] | (1 << $v);
		}   
        if (!$curuser->info['mid'] == 1)
        {
            $salt = $curuser->info['salt'];
        }
        else
        {
            $row = $db->select('salt')->from('#__members')->where(array('mid' => 1))->limit(1)->exec()->fetch();
            $salt = $row['salt'];
        }
        
        if (@$mconfigsnew['cfg_alipay_keyt'] != @$mconfigs['cfg_alipay_keyt'])
        {
            $mconfigsnew['cfg_alipay_keyt'] = authcode($mconfigsnew['cfg_alipay_keyt'], 'ENCODE', $salt);
        }
        if (@$mconfigsnew['cfg_tenpay_keyt'] != @$mconfigs['cfg_tenpay_keyt'])
        {
            $mconfigsnew['cfg_tenpay_keyt'] = authcode($mconfigsnew['cfg_tenpay_keyt'], 'ENCODE', $salt);
        }
		saveconfig('pay');
		adminlog('网站商务支付设置','网站商务支付设置');
		cls_message::show('商务支付设置完成','?entry=mconfigs&action=cfpay');
	}
}elseif($action == 'cfupload'){
	backnav('mconfig','cfupload');
	$vftp_password = $tftp_password = '';
	if(!empty($mconfigs['ftp_password'])){
		$tftp_password = authcode($mconfigs['ftp_password'],'DECODE',md5($authkey));
		@$vftp_password = $tftp_password{0}.'********'.$tftp_password{strlen($tftp_password) - 1};
	}
	if(!submitcheck('bmconfigs')){
		$upatharr = array('0' => '默认'.'('.'附件类型'.')','month' => '附件类型'.'+'.'月','day' => '附件类型'.'+'.'日期');

		tabheader('上传附件设置 &nbsp;>><a href="?entry=localfiles&action=localfilesedit">其它附件方案</a>','cfupload','?entry=mconfigs&action=cfupload');
		trbasic('附件路径(相对系统根路径)','mconfigsnew[dir_userfile]',$mconfigs['dir_userfile']);
		trbasic('附件分类保存','mconfigsnew[path_userfile]',makeoption($upatharr,$mconfigs['path_userfile']),'select');
		if(!empty($watermarks) && is_array($watermarks)) foreach($watermarks as $k => $v) $wmidsarr[$k] = $v['cname'];
		trbasic('默认媒体播放宽度','mconfigsnew[player_width]',$mconfigs['player_width']);
		trbasic('默认媒体播放高度','mconfigsnew[player_height]',$mconfigs['player_height']);
		setPermBar('上传附件权限设置', 'mconfigsnew[pm_upload]', @$mconfigs['pm_upload'] , 'down', 'open', '');
		setPermBar('附件浏览权限设置', 'mconfigsnew[atmbrowser]', @$mconfigs['atmbrowser'], 'down', 'open', '');
        trbasic('游客上传大小限制','mconfigsnew[nouser_capacity]',$mconfigs['nouser_capacity'],'text',array('guide' => '留空或输入0为禁止游客上传，单位:K'));
		trbasic('游客允许上传附件类型','mconfigsnew[nouser_exts]',$mconfigs['nouser_exts'],'text',array('guide' => '此处输入的类型需要同时存在于上传方案中才有效，格式如:gif,jpg，留空则允许上传方案的所有类型'));
		tabfooter();

		tabheader('远程附件FTP设置');
		trbasic('启用附件FTP上传','mconfigsnew[ftp_enabled]',$mconfigs['ftp_enabled'],'radio',array('guide'=>'启用后，仅符合"以下路径的附件使用FTP"的附件才保存到FTP')); // 不指定此项则"ftp远程附件设置"无效。
		trbasic('以下路径的附件使用FTP','mconfigsnew[other_ftp_dir]',$mconfigs['other_ftp_dir'],'text',array('w'=>'60','guide'=>'只有被指定的文件夹才启用远程ftp保存附件如"userfiles"，指定多个路径请以|分格如:userfiles/image|userfiles/video,留空则无任何附件保存到ftp')); //默认ftp_dir,去掉了
		trbasic('FTP 服务器地址','mconfigsnew[ftp_host]',$mconfigs['ftp_host']);
		trbasic('FTP 服务器端口','mconfigsnew[ftp_port]',$mconfigs['ftp_port']);
		trbasic('FTP 帐号','mconfigsnew[ftp_user]',$mconfigs['ftp_user']);
		trbasic('FTP 密码','mconfigsnew[ftp_password]',$vftp_password);
		trbasic('FTP 传输超时时间','mconfigsnew[ftp_timeout]',$mconfigs['ftp_timeout']);
		trbasic('是否使用被动模式(pasv)上传','mconfigsnew[ftp_pasv]',$mconfigs['ftp_pasv'],'radio');
		trbasic('是否启用SSL安全连接','mconfigsnew[ftp_ssl]',$mconfigs['ftp_ssl'],'radio');
		trbasic('FTP的附件储存主目录','mconfigsnew[ftp_dir]',      $mconfigs['ftp_dir'],      'text',array('w'=>'60','guide'=>'建议一个项目(站点)一个目录如"08cms"，开始结尾都不要加斜杠"/"；"."表示 FTP 根目录（或不填），如只有一个项目可用根目录。'));
		trbasic('FTP附件的访问主URL', 'mconfigsnew[ftp_url]',      $mconfigs['ftp_url'],      'text',array('w'=>'60','guide'=>'设置这个url指向"附件储存主目录"，应含http，结尾须加/。如：http://img.domain.com/08cms/'));
		tabfooter('bmconfigs','提交','&nbsp; &nbsp;<input class="button" type="submit" name="ftpcheck" value="检测FTP" onclick="var f=this.form,u=f.action;f.action=\'?entry=checks&action=ftpcheck\';f.target=\'ftpcheckiframe\';f.submit();f.target=\'_self\';f.action=u"><iframe name="ftpcheckiframe" style="display: none"></iframe>');
		a_guide('cfupload');
	}else{
		$mconfigsnew['dir_userfile'] = trim(strip_tags($mconfigsnew['dir_userfile']));
        if(isset($mconfigsnew['atm_smallsite']))
        {
    		$mconfigsnew['atm_smallsite'] = strtolower(trim($mconfigsnew['atm_smallsite']));
    		$mconfigsnew['atm_smallsite'] .= !preg_match("#/$#",$mconfigsnew['atm_smallsite']) ? '/' : '';
    		$mconfigsnew['atm_smallsite'] = (!preg_match("#http://#i",$mconfigsnew['atm_smallsite']) || preg_match('#'.$hosturl.'#i',$mconfigsnew['atm_smallsite'])) ? '' : $mconfigsnew['atm_smallsite'];
        }
		$mconfigsnew['player_width'] = max(0,intval($mconfigsnew['player_width']));
		$mconfigsnew['player_height'] = max(0,intval($mconfigsnew['player_height']));
		$mconfigsnew['nouser_capacity'] = max(0,intval($mconfigsnew['nouser_capacity']));
		$mconfigsnew['nouser_exts'] = strtolower(trim($mconfigsnew['nouser_exts']));
		$mconfigsnew['ftp_host'] = trim(strip_tags($mconfigsnew['ftp_host']));
		$mconfigsnew['ftp_port'] = max(1,intval($mconfigsnew['ftp_port']));
		$mconfigsnew['ftp_user'] = trim(strip_tags($mconfigsnew['ftp_user']));
		if($mconfigsnew['ftp_password'] != $vftp_password){
			$mconfigsnew['ftp_password'] =  $mconfigsnew['ftp_password'] ? authcode($mconfigsnew['ftp_password'],'ENCODE',md5($authkey)) : '';
		}else $mconfigsnew['ftp_password'] = $mconfigs['ftp_password'];
		$mconfigsnew['ftp_timeout'] = max(0,intval($mconfigsnew['ftp_timeout']));
		$mconfigsnew['ftp_dir'] = trim(strip_tags($mconfigsnew['ftp_dir']));
		$mconfigsnew['other_ftp_dir'] = trim(strip_tags($mconfigsnew['other_ftp_dir']));
		$mconfigsnew['ftp_url'] = trim(strip_tags($mconfigsnew['ftp_url']));
		saveconfig('upload');
		adminlog('网站设置','上传与下载设置');
		cls_message::show('网站设置完成','?entry=mconfigs&action=cfupload');
	}
}elseif($action == 'cfmobmail'){
	backnav('mconfig','cfmobmail');
	if(!submitcheck('bmconfigs')){
		$modearr = array(1 => 'PHP的mail函数功能',2 => 'SOCKET 连接SMTP服务器(支持身份验证)',3 => 'PHP的SMTP功能(仅用于Windows主机,不支持身份验证)',);
		$delimiterarr = array(1 => 'CRLF (Windows 主机)',2 => 'LF (Unix/Linux 主机)',3 => 'CR (Mac 主机)',);
		tabheader('Email设置','cfmail','?entry=mconfigs&action=cfmobmail&deal=mail');
		echo "<tr class=\"txt\"><td class=\"txt txtright fB borderright\">Email发送方式</td>\n".
		"<td class=\"txtL\">\n".
		"<input class=\"radio\" type=\"radio\" name=\"mconfigsnew[mail_mode]\" value=\"1\" onclick=\"\$id('mail_mod1').style.display = 'none';\$id('mail_mod2').style.display = 'none';\"".($mconfigs['mail_mode'] <= 1 ? ' checked' : '').">PHP的mail函数功能<br>\n".
		"<input class=\"radio\" type=\"radio\" name=\"mconfigsnew[mail_mode]\" value=\"2\" onclick=\"\$id('mail_mod1').style.display = '';\$id('mail_mod2').style.display = '';\"".($mconfigs['mail_mode'] == 2 ? ' checked' : '').">SOCKET 连接SMTP服务器(支持身份验证)<br>\n".
		"<input class=\"radio\" type=\"radio\" name=\"mconfigsnew[mail_mode]\" value=\"3\" onclick=\"\$id('mail_mod1').style.display = '';\$id('mail_mod2').style.display = 'none';\"".($mconfigs['mail_mode'] == 3 ? ' checked' : '').">PHP的SMTP功能(仅用于Windows主机,不支持身份验证)<br>\n".
		"</td></tr>\n";
		echo "<tbody id=\"mail_mod1\" style=\"display:".($mconfigs['mail_mode'] > 1 ? '' : 'none')."\">";
		trbasic('SMTP 服务器','mconfigsnew[mail_smtp]',$mconfigs['mail_smtp']);
		trbasic('SMTP 端口','mconfigsnew[mail_port]',$mconfigs['mail_port']);
		echo "</tbody>";
		echo "<tbody id=\"mail_mod2\" style=\"display:".($mconfigs['mail_mode'] == 2 ? '' : 'none')."\">";
		trbasic('SMTP 要求身份验证','mconfigsnew[mail_auth]',$mconfigs['mail_auth'],'radio');
		trbasic('发信人邮件地址','mconfigsnew[mail_from]',$mconfigs['mail_from']);
		trbasic('SMTP 身份验证帐户','mconfigsnew[mail_user]',$mconfigs['mail_user']);
		trbasic('SMTP 身份验证密码','mconfigsnew[mail_pwd]',$mconfigs['mail_pwd'],'password');
		echo "</tbody>";
		trbasic('邮件头的分隔符','mconfigsnew[mail_delimiter]',makeoption($delimiterarr,$mconfigs['mail_delimiter']),'select');
		trbasic('屏蔽邮件发送的出错信息','mconfigsnew[mail_silent]',$mconfigs['mail_silent'],'radio');
		trbasic('测试邮件收信地址','mconfigsnew[mail_to]');
		trbasic('测试邮件签名','mconfigsnew[mail_sign]');

		tabfooter();
		echo '<input class="button" type="submit" name="bmconfigs" value="提交">&nbsp; &nbsp;
		<input class="button" type="button" name="mailcheck" value="邮件测试" onclick="var f=this.form,u=f.action;f.action=\'?entry=checks&action=mailcheck\';f.target=\'mailcheckiframe\';f.submit();f.target=\'_self\';f.action=u"><iframe name="mailcheckiframe" style="display: none"></iframe>
		</form>';

		$provides = array('天地连线','吉亚通信');
		tabheader("400电话设置", 'webcall', "?entry=$entry&action=$action&deal=webcall");
		trbasic('网站提供400总机', 'mconfigsnew[webcall_enable]', $mconfigs['webcall_enable'], 'radio');
		trbasic('默认配置','webcall_default', makeoption($provides),'select',array('addstr'=>' &nbsp; &nbsp;<a id="webcall_setdefault" href="javascript:void(0)">恢复默认值</a> &nbsp; &nbsp;<a id="webcall_apply_url" href="http://www.port400.com/" target="_blank">申请网站总机</a>'));
		trbasic('400电话提供商','mconfigsnew[webcall_provide]', $mconfigs['webcall_provide']);
		trbasic('400总机号码','mconfigsnew[webcall_big]', $mconfigs['webcall_big']);
		trbasic('400分机管理链接','mconfigsnew[webcall_small_admin]', @$mconfigs['webcall_small_admin'],'text',array('w'=>60));
		setPermBar('以下会员允许设置400', 'mconfigsnew[webcallpmid]', @$mconfigs['webcallpmid'], 'other', array(0=>'全部不允许'), '');
        tabfooter('bmconfigs');
		echo <<<EOT
<!--?>-->
<script type="text/javascript">
	var url = Array('http://www.port400.com/','http://www.web4008.com/');
	var admin = Array('http://customer.port400.com/Menu/Login.aspx','');

	var webcall_default = document.getElementById("webcall_default");
	webcall_default.onchange = function(){
		document.getElementById("webcall_apply_url").href = url[webcall_default.value];
	}

	var webcall_setdefault = document.getElementById("webcall_setdefault");
	webcall_setdefault.onclick = function(){
		document.getElementById("mconfigsnew[webcall_small_admin]").value = admin[webcall_default.value];
		document.getElementById("mconfigsnew[webcall_provide]").value = webcall_default.options[webcall_default.selectedIndex].text;
	}
</script>
EOT;
#<?
		a_guide('cfmail');
	}else{
		if($deal == 'mail'){
			$mconfigsnew['mail_smtp'] = trim($mconfigsnew['mail_smtp']);
			$mconfigsnew['mail_port'] = trim($mconfigsnew['mail_port']);
			$mconfigsnew['mail_from'] = trim($mconfigsnew['mail_from']);
			$mconfigsnew['mail_user'] = trim($mconfigsnew['mail_user']);
			$mconfigsnew['mail_pwd'] = trim($mconfigsnew['mail_pwd']);
			unset($mconfigsnew['mail_sign'],$mconfigsnew['mail_to']);
			$str = '邮件设置';
		}elseif($deal == 'mobmail'){
			//$str = '手机设置';
		}elseif($deal == 'webcall'){
			$str = '400电话设置';
		}
		saveconfig('mail');
		adminlog($str);
		cls_message::show($str.'完成','?entry=mconfigs&action=cfmobmail');
	}
}elseif($action == 'other_site_connect'){
	backnav('mconfig','other_site_connect');
	if(!submitcheck('bmconfigs')){
	    /**
         * SESSION如果已经存在放到memcache里时不需要启动该项，所以隐藏
         * 如果开启该选项时（即选择数据库存放SESSION时）请增加一个数据表:
         *
         * CREATE TABLE `cms_cross_site_session` (
         *     `session_id` varchar(255) binary NOT NULL default '',
         *     `session_expires` int(10) unsigned NOT NULL default '0',
         *     `session_data` text,
         *     PRIMARY KEY  (`session_id`)
         * ) ENGINE=MyISAM;
         */
	    if ( strtolower(@ini_get('session.save_handler')) != 'memcache' )
        {
            tabheader('公共基本设置','publicsetting','?entry=mconfigs&action=other_site_connect');
    		trbasic('启用跨站SESSION','mconfigsnew[user_session]',@$mconfigs['user_session'],'radio',array('guide'=>'注意：如果网站运行在多服务器上请启用。'));
    		tabfooter();
            $memcache_flag = false;
        }
        else
        {
        	$memcache_flag = true;            
        }

	    // QQ登录设置
		tabheader('QQ登陆基本设置<span style="color:red">（注：开启该登录方式必须先开启PHP的CURL、OPENSSL扩展）</span>','qqconnect','?entry=mconfigs&action=other_site_connect');
		trbasic('QQ登陆','',makeradio('mconfigsnew[qq_closed]',array(0=>'开启',1=>'关闭'),empty($mconfigs['qq_closed']) ? 0 : 1),'');
	#	trbasic('开启绑定功能','mconfigsnew[qq_bind_enabled]',@$mconfigs['qq_bind_enabled'],'radio',array('guide'=>'申请APP ID时请先关闭，通过后再开。'));
		trbasic('APPID','mconfigsnew[qq_appid]',@$mconfigs['qq_appid'],'text',array('guide'=>'没有APPID么？<a style="color:red" href="http://connect.qq.com/manage/" target="_blank" >点击申请</a>'));
		trbasic('APPKEY','mconfigsnew[qq_appkey]',@$mconfigs['qq_appkey'],'text',array('guide'=>'没有APPKEY么？<a style="color:red" href="http://connect.qq.com/manage/" target="_blank" >点击申请</a>','w'=>50));
		trbasic('登录后会员名称显示','',makeradio('mconfigsnew[qq_nickname]',array(0=>'显示QQ昵称',1=>'显示本系统会员名称'),empty($mconfigs['qq_nickname']) ? 0 : 1),'', array('guide'=>'注：该选择只为申请APP KEY时使用，如果申请通过后请选择 显示本系统会员名称'));
		tabfooter();

        // 新浪微博登录设置
		tabheader('新浪微博登陆基本设置','','?entry=mconfigs&action=sinaconnect');
		trbasic('新浪微博登陆','',makeradio('mconfigsnew[sina_closed]',array(0=>'开启',1=>'关闭'),empty($mconfigs['sina_closed']) ? 0 : 1),'');
	#	trbasic('开启绑定功能','mconfigsnew[sina_bind_enabled]',@$mconfigs['sina_bind_enabled'],'radio',array('guide'=>'申请App Key时请先关闭，通过后再开。'));
		trbasic('App Key','mconfigsnew[sina_appid]',@$mconfigs['sina_appid'],'text',array('guide'=>'没有App Key么？<a style="color:red" href="http://open.weibo.com/connect/" target="_blank" >点击申请</a>'));
		trbasic('App Secret','mconfigsnew[sina_appkey]',@$mconfigs['sina_appkey'],'text',array('guide'=>'没有App Secret么？<a style="color:red" href="http://open.weibo.com/connect/" target="_blank" >点击申请</a>','w'=>50));
        $memcache_flag && trhidden('mconfigsnew[user_session]', 1);
		tabfooter('bmconfigs');

		a_guide('qqconnect');
	}else{
	    foreach ( array('qq_appid', 'qq_appkey', 'sina_appid', 'sina_appkey') as $key ) 
        {
            isset($mconfigsnew[$key]) && ($mconfigsnew[$key] = trim($mconfigsnew[$key]));
        }
		saveconfig('other_site_connect');
		adminlog('网站设置','快捷登陆设置');
		cls_message::show('修改快捷登陆设置成功!','?entry=mconfigs&action=other_site_connect');
	}
}
