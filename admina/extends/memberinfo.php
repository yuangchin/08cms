<?PHP
/**
* 管理后台的会员详情脚本
* 根据系统的需要可做分离、定制
*/


/* 参数初始化代码 */
$mid = empty($mid) ? 0 : max(0,intval($mid));
$_init = array(
	'mid' => $mid,//详情一定需要传入mid
);

#-----------------
$oA = new cls_member($_init);

$oA->TopHead();//文件头部

$oA->TopAllow();//分析操作权限

$actuser = &$oA->auser;

tabheader("[{$actuser->info['mname']}] 更多信息");
$cridsarr = cridsarr(1);
foreach($cridsarr as $k => $v){
	trbasic("$v 数量",'',$actuser->info["currency$k"],'');
}
trbasic("静态目录",'',empty($actuser->info["mspacepath"]) ? '未设置静态目录' : $actuser->info["mspacepath"],'');
$actuser->info['mchid'] != 1 && trbasic("空间点击量",'',empty($actuser->info["msclicks"]) ? '0' : $actuser->info['msclicks'],'');//空间点击量
$t_user = array();
if(!empty($actuser->info['trusteeship'])) {
    $db->select('mname')->from('#__members')->where('mid')->_in($actuser->info['trusteeship'])->exec();
    while($row = $db->fetch()) {
        $t_user[] = $row['mname'];
    }
} else {
    $t_user[] = '未设置代管者名单！';
}
trbasic("会员中心的代管会员：",'', implode(', ', $t_user),'');
tabfooter();
