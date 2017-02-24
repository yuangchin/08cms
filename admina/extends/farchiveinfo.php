<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');

$aid = empty($aid) ? 0 : max(0,intval($aid));
$detail = empty($detail) ? 0 : max(0,intval($detail));
$arc = new cls_farcedit;
$arc->set_aid($aid);

if($arc->aid && $detail){
	$tplname = cls_tpl::CommonTplname('farchive',$arc->archive['fcaid'],'arctpl'); 
	if($tplname){ 
		$vurl = cls_url::view_farcurl($arc->aid,$arc->archive['arcurl']);
		header("location:$vurl");
		die();
	}else{
		//cls_message::show("副件分类-{$arc->archive['fcaid']}-未定义模板");
		//用下面的代码查看效果。
	}
}

aheader();
!$arc->aid && cls_message::show('请指定正确的信息ID');

if(!$detail){
	tabheader($arc->archive['subject'].' 的更多信息');
	trbasic('排序','',$arc->archive['vieworder'],'');
	trbasic('发布','',$arc->archive['mname'],'');
	trbasic('添加时间','',$arc->archive['createdate'] ? date('Y-m-d H:i:s',$arc->archive['createdate']) : '','');
	trbasic('更新时间','',$arc->archive['updatedate'] ? date('Y-m-d H:i:s',$arc->archive['updatedate']) : '','');
	trbasic('开始时间','',$arc->archive['startdate'] ? date('Y-m-d H:i:s',$arc->archive['startdate']) : '','');
	trbasic('结束时间','',$arc->archive['enddate'] ? date('Y-m-d H:i:s',$arc->archive['enddate']) : '','');
	tabfooter();
}else{
	$chid = $arc->chid;
	$fields = cls_cache::Read('ffields',$chid); //print_r($fields);
	$a_field = new cls_field;
	tabheader('副件详情信息');
	$subject_table = 'farchives';
	foreach($fields as $k => $v){
		$flag = 1;
		$val = isset($arc->archive[$k]) ? $arc->archive[$k] : '';
		//$cms_abs = 'http://192.168.1.20/auto/'; //测试
		if($k=='subject'){
			$color = $arc->archive['color']; //echo "$k,$color";
			if(strlen($color)>0) $val = "<span style='color:$color;'>$val</span>";
		}elseif($v['datatype']=='multitext'){
			if($val){
				$val = "<textarea rows='10' cols='64' style='width:640px; height:120px;'>$val</textarea>";
			}else{
				//$val = "<textarea rows='10' cols='64' style='width:980px; height:60px;'>(null)$v[datatype]</textarea>";
				$flag = 0;
			}
		}elseif($v['datatype']=='htmltext'){
			$val = "<div style='width:640px; min-height:120px; border:1px solid #CCC'>$val</div>";
		}elseif($v['datatype']=='image'){
			if($val){
				$val = view_checkurl($val);
				$val = '<a href="'.$val.'" target="_blank"><img src="'.$val.'" width="980" height="720" onload="javascript:setImgSize(this,980,720);" /></a>';
			}else{
				$flag = 0;
			}
		}elseif($v['datatype']=='flash'){
			if($val){
				$val = view_checkurl($val);
				$val = '<embed wmode="transparent" src="'.$val.'" quality="high" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width=480 height=240></embed>';
			}else{
				$flag = 0;
			}
		}elseif(strstr(',cacc,select,mselect,', ','.$v['datatype'])){
			$a_field->init($v,isset($arc->archive[$k]) ? $arc->archive[$k] : '');
			$a_field->trfield('fmdata');
			$flag = 0;
		}elseif($v['datatype']=='----'){ // 其它要处理的类型
		}else{
			if($val){
				;
			}else{
				$flag = 0;
			}
		}
		if($flag) trbasic($v['cname'],'',$val,''); 
	}
	unset($a_field);
	tabfooter('');
}

/*

<option value="text">单行文本</option>
<option value="multitext">多行文本</option>
<option value="htmltext">Html文本</option>
<option value="image">单图</option>
<option value="images">图集</option>
<option value="flash">Flash</option>
<option value="flashs">Flash集</option>
<option value="media">视频</option>
<option value="medias">视频集</option>
<option value="file">单点下载</option>
<option value="files">多点下载</option>
<option value="select">单项选择</option>
<option value="mselect">多项选择</option>
<option value="cacc">类目选择</option>
<option value="date">日期(时间戳)</option>
<option value="int">整数</option>
<option value="float">小数</option>
<option value="map">地图</option>
<option value="vote">投票</option>
<option value="texts">文本集</option>

*/

?>
