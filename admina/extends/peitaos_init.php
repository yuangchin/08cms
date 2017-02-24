<?PHP
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
backallow('normal') || cls_message::show('您没有当前项目的管理权限。');

$nowid = empty($nowid) ? 0 : $nowid;
$count = empty($count) ? 100 : $count;
$circum_km = empty($circum_km) ? 100 : $circum_km;

if(!submitcheck('bsubmit')){
	
	tabheader("初始化 周边配套 与 楼盘/小区 关联",'newform',"?entry=extend&extend=peitaos_init&action=init",2,1,1);
	trbasic("每次执行条数",'count',$count,'text',array('w'=>12,'guide'=>''));
	trbasic("自动关联距离",'circum',"{$circum_km} Km，点此：<a href='?entry=extend&extend=exconfigs&action=fccotype' target='_blank'>设置参数</a>",'');
	trbasic('初始化说明','',"1. 本操作根据 周边配套地图坐标 与 楼盘/小区地图坐标 位置，在以上设置范围内，自动关联；
	<br>2. <span style='color:#F00'>建议</span>：对初始的数据，执行一次本操作，后续<span style='color:#F00'>不需重复执行</span>；
	<br>3. <span style='color:#F00'>警告</span>：对手动维护过 楼盘/小区 内的 周边资料的，此操作<span style='color:#F00'>可能会覆盖一些手动操作</span>；
	<br> &nbsp; &nbsp; 比如，手动取消了某个周边与楼盘关联，执行此操作后，又会还原这个关联。
	",'');
	tabfooter('bsubmit');
	
}else{//数据处理

	$timer = microtime(1);
	$sqla = "SELECT aid,dt FROM {$tblprefix}".atbl(4)." WHERE aid>'$nowid' ORDER BY aid LIMIT $count"; 
	$query = $db->query($sqla); $n = 0; 
	while($r = $db->fetch_array($query)){ 
		$_aid = $r['aid']; 
		$_dt = $r['dt'];
		$nowid = $_aid;
		ex_zhoubian($_aid, 4, $_dt, 1);
		$n++;
	} 
	$timer = microtime(1) - $timer;
	$timer = number_format($timer,3); 
	$msg = $n ? "本次处理{$n}条," : "处理完毕,";
	$msg .= "\n用时:{$timer}s；";
	$msg .= "<br>下批执行起始ID: $nowid\n";
	cls_message::show($msg,"?entry=extend&extend=peitaos_init".($n ? "&action=init&nowid=$nowid&count=$count&bsubmit=1" : ""));

}
?>