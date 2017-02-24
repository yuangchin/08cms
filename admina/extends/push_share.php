<?PHP
/*
** 共享推送信息到其它分类的窗口操作
** 
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('normal')) cls_message::show($re);

$paid = cls_PushArea::InitID(@$paid);//接受外部传paid
if(!($pusharea = cls_PushArea::Config($paid))) cls_message::show('请指定正确的推送位');
if(empty($pusharea['copyspace'])) cls_message::show('未指定共享分类');
$pushid = empty($pushid) ? 0 : max(0,intval($pushid));//接受外部传pushid
if(!($push = cls_pusher::oneinfo($pushid,$paid))) cls_message::show('请指定正确的推送信息');

$copyspace = "classid{$pusharea['copyspace']}";
$field = cls_PushArea::Field($paid,$copyspace);
$classes = cls_field::options_simple($field,array('onlysel' => 1));
$copyinfos = cls_pusher::copyinfos($push,$paid);

if(!submitcheck('bsubmit')){
	tabheader("[{$push['subject']}]共享设置",'pushshare',"?entry=extend&extend=$extend&paid=$paid&pushid=$pushid",2,0,1);
	trbasic('目前共享状态','',$copyinfos ? "已共享至".count($copyinfos)."个分类" : '未共享到其它分类','');
//	trbasic('操作模式','',makeradio('fmdata[isclear]',array('增加共享','移除共享'),0),'');
	if($classes){
		$classidshared = array();
		if($copyinfos){
			foreach($copyinfos as $k => $v) in_array($v[$copyspace],$classidshared) || $classidshared[] = $v[$copyspace];
		}
		$modearr = array(0 => '共享至所有分类',2 => '取消所有分类的共享',1 => '手动指定共享分类',);
		sourcemodule("请选择[{$field['cname']}]分类",
			"fmdata[mode]",
			$modearr,
			$copyinfos ? 1 : 0,
			1,
			"fmdata[ids]",
			$classes,
			$classidshared,
			'25%',1,'',1
		);
	}
	tabfooter('bsubmit');
}else{
	if(isset($fmdata['mode'])){
		switch($fmdata['mode']){
			case 0:
				foreach($classes as $k => $v){
					cls_pusher::AddCopy($pushid,$k,$paid);
				}
			break;
			case 1:
				$selectid = empty($fmdata['ids']) ? array() : array_filter(explode(',',$fmdata['ids']));
				foreach($classes as $k => $v){
					if(in_array($k,$selectid)){
						cls_pusher::AddCopy($pushid,$k,$paid);
					}else{
						cls_pusher::DelCopy($pushid,$k,$paid);
					}
				}
			break;
			case 2:
				foreach($classes as $k => $v){
					cls_pusher::DelCopy($pushid,$k,$paid);
				}
			break;
		}
	}
	cls_message::show('共享操作完成。',axaction(6,"?entry=extend&extend=pushs&paid=$paid"));
}
?>