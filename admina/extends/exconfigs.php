<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('webparam') || cls_message::show('您没有当前项目的管理权限。');
$exconfigs = cls_cache::cacRead('exconfigs',_08_EXTEND_SYSCACHE_PATH);
empty($action) && $action = 'gaoji';
if($action == 'gaoji'){
	backnav('house','gaoji');
	$rules = $exconfigs['gaoji'];
	if(!submitcheck('bsubmit')){
		$i = 0;
		foreach($rules as $k => $v){
			$i ? tabheader("$v[title] 升级规则") : tabheader("$v[title] 升级规则",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			trbasic('启用',"rulesnew[$k][available]",empty($v['available']) ? 0 : 1,'radio');
			trbasic('升级规则名称',"rulesnew[$k][title]",empty($v['title']) ? '' : $v['title'],'text',array('validate' => makesubmitstr("rulesnew[$k][title]",1,0,3,30)));			
			trbasic('需要支付金额',"rulesnew[$k][price]",empty($v['price']) ? 0 : $v['price'],'text',array('validate' => makesubmitstr("rulesnew[$k][price]",1,0,1,'','int'),'guide' => '以元为单位，输入整数'));
			trbasic('有效期限',"rulesnew[$k][month]",empty($v['month']) ? 0 : $v['month'],'text',array('validate' => makesubmitstr("rulesnew[$k][month]",1,0,1,'','int'),'guide' => '以月为单位，输入整数'));
			trbasic('赠送置顶数量',"rulesnew[$k][zds]",empty($v['zds']) ? '' : $v['zds'],'text',array('validate' => makesubmitstr("rulesnew[$k][zds]",1,0,1,'','int'),'guide' => '以天为单位，输入整数'));
			trbasic('赠送预约刷新数量',"rulesnew[$k][yys]",empty($v['yys']) ? '' : $v['yys'],'text',array('validate' => makesubmitstr("rulesnew[$k][yys]",1,0,1,'','int'),'guide' => '以次为单位，输入整数'));
			$i ++;
			tabfooter($i == count($rules) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['gaoji'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'vipgs'){
	backnav('house','vipgs');
	$rules = $exconfigs['vipgs'];
	if(!submitcheck('bsubmit')){
		$i = 0;
		foreach($rules as $k => $v){
			$i ? tabheader("$v[title] 升级规则") : tabheader("$v[title] 升级规则",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			trbasic('启用',"rulesnew[$k][available]",empty($v['available']) ? 0 : 1,'radio');
			trbasic('升级规则名称',"rulesnew[$k][title]",empty($v['title']) ? '' : $v['title'],'text',array('validate' => makesubmitstr("rulesnew[$k][title]",1,0,3,30)));			
			trbasic('需要支付金额',"rulesnew[$k][price]",empty($v['price']) ? 0 : $v['price'],'text',array('validate' => makesubmitstr("rulesnew[$k][price]",1,0,1,'','int'),'guide' => '以元为单位，输入整数'));
			trbasic('有效期限',"rulesnew[$k][month]",empty($v['month']) ? 0 : $v['month'],'text',array('validate' => makesubmitstr("rulesnew[$k][month]",1,0,1,'','int'),'guide' => '以月为单位，输入整数'));
			trbasic('赠送刷新次数',"rulesnew[$k][refnum]",empty($v['refnum']) ? '' : $v['refnum'],'text',array('validate' => makesubmitstr("rulesnew[$k][refnum]",1,0,1,'','int'),'guide' => '以次为单位，输入整数'));
			$i ++;
			tabfooter($i == count($rules) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['vipgs'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'vipsj'){
	backnav('house','vipsj');
	$rules = $exconfigs['vipsj'];
	if(!submitcheck('bsubmit')){
		$i = 0;
		foreach($rules as $k => $v){
			$i ? tabheader("$v[title] 升级规则") : tabheader("$v[title] 升级规则",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			trbasic('启用',"rulesnew[$k][available]",empty($v['available']) ? 0 : 1,'radio');
			trbasic('升级规则名称',"rulesnew[$k][title]",empty($v['title']) ? '' : $v['title'],'text',array('validate' => makesubmitstr("rulesnew[$k][title]",1,0,3,30)));			
			trbasic('需要支付金额',"rulesnew[$k][price]",empty($v['price']) ? 0 : $v['price'],'text',array('validate' => makesubmitstr("rulesnew[$k][price]",1,0,1,'','int'),'guide' => '以元为单位，输入整数'));
			trbasic('有效期限',"rulesnew[$k][month]",empty($v['month']) ? 0 : $v['month'],'text',array('validate' => makesubmitstr("rulesnew[$k][month]",1,0,1,'','int'),'guide' => '以月为单位，输入整数'));
			trbasic('赠送刷新次数',"rulesnew[$k][refnum]",empty($v['refnum']) ? '' : $v['refnum'],'text',array('validate' => makesubmitstr("rulesnew[$k][refnum]",1,0,1,'','int'),'guide' => '以次为单位，输入整数'));
			$i ++;
			tabfooter($i == count($rules) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['vipsj'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'upmemberhelp'){
	backnav('house','upmemberhelp');
	$rule = $exconfigs['upmemberhelp'];
	if(!submitcheck('bsubmit')){
		$i = 0;
		foreach($rule as $k=>$v){
			$i ? tabheader("$v[title]升级说明") : tabheader("$v[title]升级说明",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			trbasic('会员名称',"rulesnew[$k][title]",empty($v['title']) ? '' : $v['title'],'text',array('guide'=>'升级会员名称'));
			trbasic("$v[title]说明","rulesnew[$k][des]",empty($v['des']) ? '' : $v['des'],'textarea',array('w'=>'500','h'=>'300','guide' => "用户升级$v[title]的提示或帮助信息。"));
			$i++;
			tabfooter($i==count($rule) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['upmemberhelp'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'gssendrules'){
	backnav('house','gssendrules');
	$rule = $exconfigs['gssendrules'];
	$gid = 31;$i = 0;
	$ugname = cls_cache::Read('usergroups',$gid);
	if(!submitcheck('bsubmit')){		
		foreach($rule as $mchid=>$m){	
			$i ? tabheader($ugname[$mchid]['cname'].'发布规则') : tabheader($ugname[$mchid]['cname'].'发布规则','exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
				foreach($m as $k=>$v){
					if(is_array($v)){
						$c = cls_cache::Read('channels');$v['title'] = $c[$k]['cname'];
						trbasic("$v[title]总数量","rulesnew[$mchid][$k][total]",empty($v['total']) ? '' : $v['total'],'text',array('validate' => makesubmitstr("rulesnew[$mchid][$k][total]",1,0,1,'','int'),'guide' => "会员可以发布的$v[title]总数。"));
						//trbasic("会员中心$v[title]有效数量","rulesnew[$mchid][$k][valid]",empty($v['valid']) ? '' : $v['valid'],'text',array('validate' => makesubmitstr("rulesnew[$mchid][$k][valid]",1,0,1,'','int'),'guide' => "允许显示到前台的$v[title]数量。"));
					}else{
						trbasic('每日刷新次数',"rulesnew[$mchid][refresh]",empty($v) ? '' : $v,'text',array('validate' => makesubmitstr("rulesnew[$mchid][refresh]",1,0,1,'','int'),'guide' => "每日允许执行刷新操作的次数。"));
					}					
				}
			$i++;
			tabfooter($i == count($rule) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['gssendrules'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'sjsendrules'){
	backnav('house','sjsendrules');
	$rule = $exconfigs['sjsendrules'];
	$gid = 32;$i = 0;
	$ugname = cls_cache::Read('usergroups',$gid);
	if(!submitcheck('bsubmit')){
		foreach($rule as $mchid=>$m){	
			$i ? tabheader($ugname[$mchid]['cname'].'发布规则') : tabheader($ugname[$mchid]['cname'].'发布规则','exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			foreach($m as $k=>$v){
				if(is_array($v)){
					$c = cls_cache::Read('channels');$v['title'] = $c[$k]['cname'];
					trbasic("$v[title]总数量","rulesnew[$mchid][$k][total]",empty($v['total']) ? '' : $v['total'],'text',array('validate' => makesubmitstr("rulesnew[$mchid][$k][total]",1,0,1,'','int'),'guide' => "会员可以发布的$v[title]总数。"));
					//trbasic("会员中心$v[title]有效数量","rulesnew[$mchid][$k][valid]",empty($v['valid']) ? '' : $v['valid'],'text',array('validate' => makesubmitstr("rulesnew[$mchid][$k][valid]",1,0,1,'','int'),'guide' => "允许显示到前台的$v[title]数量。"));
				}else{
					trbasic('每日刷新次数',"rulesnew[$mchid][refresh]",empty($v) ? '' : $v,'text',array('validate' => makesubmitstr("rulesnew[$mchid][refresh]",1,0,1,'','int'),'guide' => "每日允许执行刷新操作的次数。"));
				}
			}
			$i++;
			tabfooter($i == count($rule) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['sjsendrules'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'zding'){
	backnav('house','zding');
	$rule = $exconfigs['zding'];
	if(!submitcheck('bsubmit')){
		tabheader('房源置顶规则','exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
		trbasic('房源置顶每天费用',"rulenew[price]",empty($rule['price']) ? 0 : $rule['price'],'text',array('validate' => makesubmitstr("rulenew[price]",1,0,1,'','int'),'guide' => '以元为单位，输入整数'));
		trbasic('置顶一次最少多少天',"rulenew[minday]",empty($rule['minday']) ? 0 : $rule['minday'],'text',array('validate' => makesubmitstr("rulenew[minday]",1,0,1,'','int'),'guide' => '以天为单位，输入整数'));
		tabfooter('bsubmit');
	}else{
		$exconfigs['zding'] = $rulenew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'yysx'){
	backnav('house','yysx');
	$rule = $exconfigs['yysx'];
	if(!submitcheck('bsubmit')){
		tabheader('房源预约刷新规则','exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
		$usergroups = cls_cache::Read('usergroups',14);
		$rules = empty($exconfigs['yysx']['allowgroup'])? array() : $exconfigs['yysx']['allowgroup']; 
		$arr = array(); 
		foreach($usergroups as $g => $v){ 
			$arr[$g] = "$v[cname]";
		}		
		trbasic("可允许刷新的会员","rulenew[allowgroup]",makecheckbox('rulenew[allowgroup][]',$arr,$rules,5),'');			
		trbasic("时间点设置","rulenew[time]",empty($rule['time']) ? 0 : $rule['time'],'text',array('validate' => makesubmitstr("rulenew['time']",1,0,1,'','char'),'guide' => '用于设置刷新的时间点，24小时时间制度，可精确到分，多个时间点用，号分开。如6,10:00,11:25,12,18,20:01'));
		trbasic('可预约天数',"rulenew[yyday]",empty($rule['yyday']) ? 0 : $rule['yyday'],'text',array('validate' => makesubmitstr("rulenew[yyday]",1,0,0,'','float'),'guide' => '以天为单位，可以设置今后几天的预约(包括当天)'));
		trbasic('每天可预约房源条数',"rulenew[totalnum]",empty($rule['totalnum']) ? 0 : $rule['totalnum'],'text',array('validate' => makesubmitstr("rulenew[totalnum]",1,0,1,'','int'),'guide' => '以条为单位，输入整数，一天可以对几条房源进行预约刷新设置'));
		trbasic('预约每条房源的费用',"rulenew[price]",empty($rule['price']) ? 0 : $rule['price'],'text',array('validate' => makesubmitstr("rulenew[price]",0,0,0,'','float'),'guide' => '以元为单位，当会员预约刷新数量为0时，按这个扣除费用'));
		trbasic("预约刷新说明","rulenew[directions]",empty($rule['directions']) ? '' : $rule['directions'],'textarea',array('w'=>'500','h'=>'150','guide' => "预约刷新的提示或帮助信息。"));
		tabfooter('bsubmit');
	}else{
		$ugidsnew = implode(',',$rulenew['allowgroup']);		
		$db->query("UPDATE {$tblprefix}permissions SET ugids='$ugidsnew' WHERE pmid='118'");	
		cls_CacheFile::Update('permissions');
		$exconfigs['yysx'] = $rulenew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'fanyuan'){
	backnav('house','fanyuan');
	$rules = $exconfigs['fanyuan'];
	if(!submitcheck('bsubmit')){
		$usergroups = cls_cache::Read('usergroups',14);
		$i = 0;
		foreach($rules as $k => $v){
			$ugname = empty($usergroups[$k]) ? '普通会员' : $usergroups[$k]['cname'];
			$i ? tabheader("$ugname 发布限额") : tabheader("$ugname 发布限额",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
			trbasic('租售房源总数',"rulesnew[$k][total]",empty($v['total']) ? '' : $v['total'],'text',array('validate' => makesubmitstr("rulesnew[$k][total]",1,0,1,'','int'),'guide' => '会员可以发布的房源总数。'));
			trbasic('租售有效期限',"rulesnew[$k][fyvalid]",empty($v['fyvalid']) ? '' : $v['fyvalid'],'text',array('validate' => makesubmitstr("rulesnew[$k][fyvalid]",1,0,1,'','int'),'guide' => '有效期限(天)。'));
			trbasic('租售日发布数',"rulesnew[$k][daymax]",empty($v['daymax']) ? '' : $v['daymax'],'text',array('validate' => makesubmitstr("rulesnew[$k][daymax]",1,0,1,'','int'),'guide' => '会员每日可以发布的出租和出售的数量。'));
			//trbasic('租售房源上架数量',"rulesnew[$k][valid]",empty($v['valid']) ? '' : $v['valid'],'text',array('validate' => makesubmitstr("rulesnew[$k][valid]",1,0,1,'','int'),'guide' => '允许显示到前台的房源数量。'));
			trbasic('每日刷新次数',"rulesnew[$k][refresh]",empty($v['refresh']) ? '' : $v['refresh'],'text',array('validate' => makesubmitstr("rulesnew[$k][refresh]",1,0,1,'','int'),'guide' => '每日允许执行刷新操作的次数。'));
			trbasic('需求发布数量',"rulesnew[$k][xuqiu]",empty($v['xuqiu']) ? '' : $v['xuqiu'],'text',array('validate' => makesubmitstr("rulesnew[$k][xuqiu]",1,0,1,'','int'),'guide' => '允许发布的需求信息数量。'));
			trbasic('需求有效期限',"rulesnew[$k][xqvalid]",empty($v['xqvalid']) ? '' : $v['xqvalid'],'text',array('validate' => makesubmitstr("rulesnew[$k][xqvalid]",1,0,1,'','int'),'guide' => '有效期限(天)。'));
			//trbasic('房源推荐位个数',"rulesnew[$k][tuijian]",empty($v['tuijian']) ? '6' : $v['tuijian'],'text',array('validate' => makesubmitstr("rulesnew[$k][tuijian]",1,0,1,'','int'),'guide' => '允许允许的信息数量。'));
			$i ++; 
			tabfooter($i == count($rules) ? 'bsubmit' : '');
		}
	}else{
		$exconfigs['fanyuan'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
}elseif($action == 'shangye'){
    backnav('house','shangye');
    $rules = $exconfigs['shangye'];
    if(!submitcheck('bsubmit')){
        $usergroups = cls_cache::Read('usergroups',14);
        $i = 0;
        foreach($rules as $k => $v){
            $ugname = empty($usergroups[$k]) ? '普通会员' : $usergroups[$k]['cname'];
            $i ? tabheader("$ugname 发布限额") : tabheader("$ugname 发布限额",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
            trbasic('租售商业地产总数',"rulesnew[$k][total]",empty($v['total']) ? '' : $v['total'],'text',array('validate' => makesubmitstr("rulesnew[$k][total]",1,0,1,'','int'),'guide' => '会员可以发布的商业地产总数。'));
            trbasic('租售有效期限',"rulesnew[$k][fyvalid]",empty($v['fyvalid']) ? '' : $v['fyvalid'],'text',array('validate' => makesubmitstr("rulesnew[$k][fyvalid]",1,0,1,'','int'),'guide' => '有效期限(天)。'));
            trbasic('租售日发布数',"rulesnew[$k][daymax]",empty($v['daymax']) ? '' : $v['daymax'],'text',array('validate' => makesubmitstr("rulesnew[$k][daymax]",1,0,1,'','int'),'guide' => '会员每日可以发布的出租和出售的数量。'));
            //trbasic('租售房源上架数量',"rulesnew[$k][valid]",empty($v['valid']) ? '' : $v['valid'],'text',array('validate' => makesubmitstr("rulesnew[$k][valid]",1,0,1,'','int'),'guide' => '允许显示到前台的房源数量。'));
            trbasic('每日刷新次数',"rulesnew[$k][refresh]",empty($v['refresh']) ? '' : $v['refresh'],'text',array('validate' => makesubmitstr("rulesnew[$k][refresh]",1,0,1,'','int'),'guide' => '每日允许执行刷新操作的次数。'));
            trbasic('需求发布数量',"rulesnew[$k][xuqiu]",empty($v['xuqiu']) ? '' : $v['xuqiu'],'text',array('validate' => makesubmitstr("rulesnew[$k][xuqiu]",1,0,1,'','int'),'guide' => '允许发布的需求信息数量。'));
            trbasic('需求有效期限',"rulesnew[$k][xqvalid]",empty($v['xqvalid']) ? '' : $v['xqvalid'],'text',array('validate' => makesubmitstr("rulesnew[$k][xqvalid]",1,0,1,'','int'),'guide' => '有效期限(天)。'));
            //trbasic('房源推荐位个数',"rulesnew[$k][tuijian]",empty($v['tuijian']) ? '6' : $v['tuijian'],'text',array('validate' => makesubmitstr("rulesnew[$k][tuijian]",1,0,1,'','int'),'guide' => '允许允许的信息数量。'));
            $i ++;
            tabfooter($i == count($rules) ? 'bsubmit' : '');
        }
    }else{
        $exconfigs['shangye'] = $rulesnew;
        cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
        cls_message::show('系统参数设置成功！',M_REFERER);
    }
}elseif($action == 'weituo'){
	backnav('house','weituo');
	$rules = $exconfigs['weituo'];
	if(!submitcheck('bsubmit')){
		tabheader('委托房源推荐经纪人筛选设置','weituo',"?entry=$entry$extend_str&action=$action",2,0,1);
		trbasic('允许推荐普通经纪人',"rulesnew[allowptjjr]",empty($rules['allowptjjr']) ? 0 : 1,'radio',array('guide'=>'是否允许委托房源给普通经纪人。'));
		trbasic('不限经纪人区域',"rulesnew[allowccid1]",empty($rules['allowccid1']) ? 0 : 1,'radio',array('guide'=>'是否允许搜索其它区域的经纪人。'));
		tabfooter('bsubmit');
	}else{
		$exconfigs['weituo'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('委托词源参数设置成功！',M_REFERER);		
	}
}elseif($action == 'distribution'){
	backnav('house','distribution');
	$rules = $exconfigs['distribution'];
	if(!submitcheck('bsubmit')){
		tabheader('楼盘分销参数设置','distribution',"?entry=$entry$extend_str&action=$action",2,0,1);
		trbasic('允许推荐楼盘个数',"rulesnew[num]",empty($rules['num']) ? 3 : max(0,intval(@$rules['num'])),'text',array('guide'=>'每次推荐分销所允许推荐的楼盘个数。'));
		trbasic('允许推荐朋友个数',"rulesnew[pnum]",empty($rules['pnum']) ? 3 : max(0,intval(@$rules['pnum'])),'text',array('guide'=>'每个经纪人总共可以推荐的朋友个数。'));
        trbasic('推荐有效时间',"rulesnew[vtime]",empty($rules['vtime']) ? 15 : max(0,intval(@$rules['vtime'])),'text',array('guide'=>'成功推荐分销后的有效时间(天)。'));
        trbasic('无效客户个数',"rulesnew[unvnum]",empty($rules['unvnum']) ? 10 : max(0,intval(@$rules['unvnum'])),'text',array('guide'=>'经纪人推荐N个无效客户，自动进入黑名单，进入黑名单的经纪人不能进行推荐操作。'));
		trbasic('分销默认推广口号',"rulesnew[fxwords]",@$rules['fxwords'] ,'textarea',array('w' => 400,'h' => 50,'guide'=>'楼盘分销-推广链接-的默认口号。'));
		//trbasic('分销默认推广口号','fxwords','口号口号模版','textarea', array('w' => 400,'h' => 50,'validate' => makesubmitstr('fxwords',1,0,0,100)));
		tabfooter('bsubmit');
	}else{
		$exconfigs['distribution'] = $rulesnew;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_message::show('楼盘分销参数设置成功。',M_REFERER);		
	}
	
}elseif($action == 'closemod'){

	backnav('house','closemod');
	
	$closemstr = empty($exconfigs['closemstr']) ? '' : $exconfigs['closemstr'];
	$sarr = cmod('',$type='seta'); 
	if(!submitcheck('bsubmit')){
		 tabheader("可选模块关闭设置",'exconfigs',"?entry=$entry$extend_str&action=$action",2,0,1);
		 foreach($sarr as $k=>$v){
		 	$ischeck = strstr(",$closemstr,","$k,") ? 'checked="checked"' : '';
			$gmsg = "是否关闭 $v[cname] 模块。 (模块标识ID:$k)";
			trbasic($v['cname']."模块","","关闭".$v['cname']."模块 <input name='closemods[]' type='checkbox' value='$k' $ischeck />",'',array('guide' =>$gmsg ));
		 }
		 tabfooter('bsubmit');
		 a_guide('excmod');
	}else{
		$closestr = empty($closemods) ? 'n.o.n.e' : implode(",", $closemods);
		$exconfigs['closemstr'] = $closestr;
		cls_CacheFile::cacSave($exconfigs,'exconfigs',_08_EXTEND_SYSCACHE_PATH);
		cls_CacheFile::Update('linknodes');
		cmod('','setdb');  
		cls_message::show('系统参数设置成功！',M_REFERER);
	}
	//a_guide('ext_model');

}elseif($action == 'fccotype'){
	
	backnav('house','fccotype');
	if(!submitcheck('bsubmit')){
		tabheader('房产其它参数管理','cffang',"?entry=$entry$extend_str&action=$action");
		//trbasic('关闭家装模块','mconfigsnew[jzmodelset]',empty($mconfigs['jzmodelset']) ? 0 : 1,'radio',array('guide'=>'前台显示或者隐藏家装模块'));
		trbasic('关闭游客发布','mconfigsnew[close_gpub]',empty($mconfigs['close_gpub']) ? 0 : 1,'radio',array('guide'=>'此开关控制游客是否可发布房源,需求。'));
		trbasic('关闭商圈类系','mconfigsnew[fcdisabled2]',empty($mconfigs['fcdisabled2']) ? 0 : 1,'radio');
		trbasic('关闭地铁线路与站点','mconfigsnew[fcdisabled3]',empty($mconfigs['fcdisabled3']) ? 0 : 1,'radio');
		trbasic('会员电话号码是否唯一','mconfigsnew[telisunique]',empty($mconfigs['telisunique']) ? 0 : 1,'radio',array('guide'=>'选择[是],则会检查是否有重复电话号码，重复则不能提交！（此设置会覆盖如下设置:[网站架构-会员架构-认证类型-手机认证:号码是否唯一]）'));
		trbasic('游客发布数量','mconfigsnew[count_gpub]',empty($mconfigs['count_gpub']) ? 5 : $mconfigs['count_gpub'],'text',array('guide'=>'同一号码，一天内可发布房源,需求信息条数。'));
		trbasic('房源图片数量','mconfigsnew[fyimg_count]',empty($mconfigs['fyimg_count']) ? 20 : $mconfigs['fyimg_count'],'text',array('guide'=>'控制前台和会员中心，最多房源图片个数。'));

/*
		trbasic('招聘有效期限',"mconfigsnew[zpvalid]",empty($mconfigs['zpvalid']) ? '30' : $mconfigs['zpvalid'],'text',array('validate' => makesubmitstr("mconfigsnew[zpvalid]",1,0,1,'','int'),'guide' => '有效期限(天)。'));

*/
		trbasic('售楼公司每日刷新次数','mconfigsnew[salesrefreshes]',empty($mconfigs['salesrefreshes']) ? 30 : $mconfigs['salesrefreshes'],'text',array('guide'=>'售楼公司每日可以执行刷新的次数！'));
		trbasic('周边配套自动关联范围','mconfigsnew[circum_km]',empty($mconfigs['circum_km']) ? 3 : $mconfigs['circum_km'],'text',array('guide'=>'单位:公里；此范围内的楼盘/小区会自动与周边关联'));
		trbasic('微信周边配套自动关联范围','mconfigsnew[weixin_circum_km]',empty($mconfigs['weixin_circum_km']) ? 1 : $mconfigs['weixin_circum_km'],'text',array('guide'=>'单位:公里；此范围内的楼盘/小区会自动与周边关联'));
		tabfooter('bsubmit');
	}else{
		$mconfigsnew['pictolp'] = empty($mconfigsnew['pictolp']) ? 0 : 1;
		$mconfigsnew['fcdisabled2'] = empty($mconfigsnew['fcdisabled2']) ? 0 : 1;
		$mconfigsnew['fcdisabled3'] = empty($mconfigsnew['fcdisabled3']) ? 0 : 1;
		$telisunique = empty($mconfigsnew['telisunique']) ? 0 : 1;
		$db->query("UPDATE {$tblprefix}mctypes SET isunique='$telisunique' WHERE mctid='1'");
		cls_CacheFile::Update('mctypes');
		saveconfig('fang');
		adminlog('网站设置','房产其它参数管理');
		cls_message::show('房产其它参数管理完成',"?entry=$entry$extend_str&action=$action");
	}
	
}
?>
