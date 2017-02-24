<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('project')) cls_message::show($re);
$channels = cls_cache::Read('channels');
if($action == 'watermarkedit'){
	backnav('project','watermark');

	if(!submitcheck('watermarkadd') && !submitcheck('watermarkedit')){
		$watermarks = array();
		$query = $db->query("SELECT * FROM {$tblprefix}watermarks");
		tabheader('水印方案管理','watermarkedit','?entry=watermark&action=watermarkedit','5');
		trcategory(array('ID','方案名称|L','是否可用','方案类型','水印类型|L','删除','编辑'));
		while($watermark = $db->fetch_array($query)){
			$watermark['issystemstr'] = empty($watermark['issystem']) ? '自定' : '系统';
			$k = $watermark['wmid'];
			switch($watermark['watermarktype']){
				case '0':
					$watermark['watermarktype']='GIF图片水印';
					break;
				case '1':
					$watermark['watermarktype']='PNG图片水印';
					break;
				case '2':
					$watermark['watermarktype']='文字水印';
					break;
			}
			echo "<tr class=\"txt\">".
				"<td class=\"txtC w40\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" size=\"30\" name=\"watermarksnew[$k][cname]\" value=\"$watermark[cname]\"".(!empty($watermark['issystem']) ? " unselectable=\"on\"" : "")."></td>\n".
				"<td class=\"txtC\"><input type=\"checkbox\" class=\"checkbox\" name=\"watermarksnewable[$k]\" value=\"1\" ".($watermark['Available'] ? "checked=\"checked\"" : "")."></input></td>\n".
				"<td class=\"txtC w80\">$watermark[issystemstr]</td>\n".
				"<td class=\"txtL\">$watermark[watermarktype]</td>\n".
				"<td class=\"txtC w40\"><input class=\"checkbox\" type=\"checkbox\"".(!empty($watermark['issystem']) ? ' disabled' : " name=\"delete[$k]\" value=\"$k\" onclick=\"deltip(this,$no_deepmode)\"")."></td>\n".
				"<td class=\"txtC w40\"><a onclick=\"return floatwin('open_channeledit',this)\" href=\"?entry=watermark&action=watermarkdetail&wmid=$k\">".'详情'."</a></td></tr>\n";
		}

		tabfooter('watermarkedit','修改');

		tabheader('添加水印方案','watermarkadd','?entry=watermark&action=watermarkedit');
		$watermarktypearr = array('0' => 'GIF图片水印','1' => 'PNG图片水印','2'=>'文字水印');
		trbasic('水印类型','addwatermark[watermarktype]',makeoption($watermarktypearr,$watermark['watermarktype']),'select');
		trbasic('方案名称','addwatermark[cname]');
		tabfooter('watermarkadd','添加');
		a_guide('watermarkedit');
	}elseif(submitcheck('watermarkedit')) {
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}watermarks WHERE wmid=$k");
				unset($watermarksnew[$k]);
			}
		}
		foreach($watermarksnew as $k => $watermarknew){
			if(empty($watermarks[$k]['issystem'])){
				$watermarknew['cname'] = empty($watermarknew['cname']) ? $watermarks[$k]['cname'] : $watermarknew['cname'];
				$db->query("UPDATE {$tblprefix}watermarks SET cname='$watermarknew[cname]' WHERE wmid=$k");
			}
		}
		if(!empty($watermarksnewable)){
			$query=$db->query("SELECT wmid FROM {$tblprefix}watermarks");
			while($row=$db->fetch_array($query)){
				if(!empty($watermarksnewable[$row['wmid']])){
					$db->query("UPDATE {$tblprefix}watermarks set Available='1' WHERE wmid='$row[wmid]'");
				}else{
					$db->query("UPDATE {$tblprefix}watermarks set Available='0' WHERE wmid='$row[wmid]'");
				}
			}
		}else{
			$db->query("UPDATE {$tblprefix}watermarks set available='0'");
		}

		cls_CacheFile::Update('watermarks');
		adminlog('编辑水印方案','编辑方案列表');
		cls_message::show('方案修改完成', '?entry=watermark&action=watermarkedit');
	}
	elseif(submitcheck('watermarkadd')) {
		if(!$addwatermark['cname']) {
			cls_message::show('方案资料missiong', '?entry=watermark&action=watermarkedit');
		}
		$db->query("INSERT INTO {$tblprefix}watermarks SET wmid=".auto_insert_id('watermarks').",cname='$addwatermark[cname]',watermarktype='$addwatermark[watermarktype]'");
		cls_CacheFile::Update('watermarks');
		adminlog('添加水印方案','编辑方案列表');
		cls_message::show('方案添加完成', '?entry=watermark&action=watermarkedit');
	}
}
if($action =='watermarkdetail' && $wmid){
	$setwatermark = $db->fetch_one("SELECT * FROM {$tblprefix}watermarks WHERE wmid='".$wmid."'");
	if(empty($setwatermark)) cls_message::show('参数出错！',axaction(2,'?entry=watermark&action=watermarkedit'));

	if(!submitcheck('setmarkedit')){
		$waterfontpath = M_ROOT.'images/common/';
		$opendir=@opendir($waterfontpath);
		$fontfile = array('0'=>'请选择');
		while($entry=readdir($opendir)){
			if($entry != '.' && $entry != '..' && preg_match('/\s*\.[ttf|TTF]/',$entry))	$fontfile[$entry]=$entry;
		}
		tabheader('设置水印'.'&nbsp; - &nbsp;'.$setwatermark['cname'],'setmarkedit',"?entry=watermark&action=watermarkdetail&wmid=$wmid",6);
		$arr = array(1 => '左上',2 => '中上',3 => '右上',4 => '左中',5 => '居中',6 => '右中',7 => '左下',8 => '中下',9 => '右下',);
		$starr = empty($setwatermark['watermarkstatus']) ? array() : explode(',',$setwatermark['watermarkstatus']);
		trbasic('添加水印位置','',makecheckbox('setwatermarknew[watermarkstatus][]',$arr,$starr,3),'',array('guide' => '请最多选择3个位置，不选则当前水印方案不生效。'));
		trbasic('超过以下宽度的图片加水印','setwatermarknew[watermarkminwidth]',$setwatermark['watermarkminwidth'],'text',array('guide'=>'单位：px，请输入不小于100的整数'));
		trbasic('超过以下高度的图片加水印','setwatermarknew[watermarkminheight]',$setwatermark['watermarkminheight'],'text',array('guide'=>'单位：px，请输入不小于100的整数'));
		trbasic('水印图片的水平边矩','setwatermarknew[watermarkoffsetx]',$setwatermark['watermarkoffsetx'],'text',array('guide'=>'单位：px，请输入5至100的整数'));
		trbasic('水印图片的垂直边矩','setwatermarknew[watermarkoffsety]',$setwatermark['watermarkoffsety'],'text',array('guide'=>'单位：px，请输入5至100的整数'));
		if($setwatermark['watermarktype']!='2'){
			trbasic('图片水印融合度','setwatermarknew[watermarktrans]',$setwatermark['watermarktrans'],'text',array('guide'=>'设置 GIF 类型水印图片与原始图片的融合度，范围为 1～100 的整数，数值越大水印图片透明度越低。PNG 类型水印本身具有真彩透明效果，无须此设置。本功能需要开启水印功能后才有效'));
			trbasic('JPEG图片水印后质量','setwatermarknew[watermarkquality]',$setwatermark['watermarkquality'],'text',array('guide'=>'设置 JPEG 类型的图片附件添加水印后的质量参数，范围为 0～100 的整数，数值越大结果图片效果越好，但尺寸也越大。本功能需要开启水印功能后才有效'));
		}else{
			trbasic('文本水印内容','setwatermarknew[watermarktext]',$setwatermark['watermarktext']);
			trbasic('文本水印字体','setwatermarknew[waterfontfile]',makeoption($fontfile,$setwatermark['waterfontfile']),'select');
			trbasic('文本水印字体大小','setwatermarknew[watermarkfontsize]',$setwatermark['watermarkfontsize'],'text',array('guide'=>'文字在图片中字体的大小。'));
			trbasic('文本水印显示角度','setwatermarknew[watermarkangle]',$setwatermark['watermarkangle'],'text',array('guide'=>'文字在图片设定位置显示的角度。'));
			trbasic('文本水印字体颜色','setwatermarknew[watermarkcolor]','<div style="position:relative;"><input type="text" value="'.$setwatermark['watermarkcolor'].'" name="setwatermarknew[watermarkcolor]" id="setwatermarknew[watermarkcolor]" size="25">&nbsp;&nbsp;<input type="button" id="colorbtn" style="width:40px; height:21px;"><div id="colordiv" style="position: absolute; z-index: 301; left: 380px; top: 180px; display: none;"><iframe id="c_frame" name="c_frame" scrolling="no" height="186" width="166"></iframe></div></div>','',array('guide'=>'输入 16 进制颜色代表文本水印字体颜色'));
echo <<<END
<!--?>-->
<script>
var colortxt = document.getElementById('setwatermarknew[watermarkcolor]');
var colorbtn = document.getElementById('colorbtn');
colorbtn.style.background = colortxt.value;
var colordiv = document.getElementById('colordiv');
var cf = document.getElementById('c_frame');
cf.onmouseout = function(){
	colordiv.style.display = 'none';
}
colorbtn.onclick = function(){
	colordiv.style.display ='' ;
	colordiv.style.left = 177 + 'px';
	colordiv.style.top = -185 + 'px';
	c_frame.location = './images/common/getcolor.htm?setwatermarknew[watermarkcolor]';
}
</script>
END;
#<?
		}
		tabfooter('setmarkedit','修改');
		a_guide('watermarkdetail');
	}else{
		$setwatermarknew['watermarkstatus'] = empty($setwatermarknew['watermarkstatus']) ? '' : implode(',',$setwatermarknew['watermarkstatus']);
		$setwatermarknew['watermarkminwidth']=max(100,$setwatermarknew['watermarkminwidth']);
		$setwatermarknew['watermarkminheight']=max(100,$setwatermarknew['watermarkminheight']);
		$setwatermarknew['watermarkoffsetx']=max(5,min(100,intval($setwatermarknew['watermarkoffsetx'])));
		$setwatermarknew['watermarkoffsety']=max(5,min(100,intval($setwatermarknew['watermarkoffsety'])));


		$updatestr = '';
		$updatestr.="watermarkminwidth='$setwatermarknew[watermarkminwidth]',";
		$updatestr.="watermarkminheight='$setwatermarknew[watermarkminheight]',";
		$updatestr.="watermarkoffsetx='$setwatermarknew[watermarkoffsetx]',";
		$updatestr.="watermarkoffsety='$setwatermarknew[watermarkoffsety]',";
		if($setwatermark['watermarktype']=='2'){
			empty($setwatermarknew['watermarkangle']) && $setwatermarknew['watermarkangle']=min(100,$setwatermarknew['watermarkangle']);
			$updatestr.="watermarkangle='$setwatermarknew[watermarkangle]',";
		}else{
			$setwatermarknew['watermarktrans']=min(100,$setwatermarknew['watermarktrans']);
			$updatestr.="watermarktrans='$setwatermarknew[watermarktrans]',";
			$setwatermarknew['watermarkquality']=min(100,$setwatermarknew['watermarkquality']);
			$updatestr.="watermarkquality='$setwatermarknew[watermarkquality]',";
		}
		!empty($setwatermarknew['watermarktext']) &&  $updatestr.="watermarktext='$setwatermarknew[watermarktext]',";
		$updatestr.= (!empty($setwatermarknew['waterfontfile']) ? "waterfontfile='$setwatermarknew[waterfontfile]'," : 'waterfontfile=0,');
		!empty($setwatermarknew['watermarkfontsize']) && $updatestr.="watermarkfontsize='$setwatermarknew[watermarkfontsize]',";

		!empty($setwatermarknew['watermarkcolor']) &&  $updatestr.="watermarkcolor='$setwatermarknew[watermarkcolor]',";
		$updatestr.="watermarkstatus='$setwatermarknew[watermarkstatus]'";
		$db->query("UPDATE {$tblprefix}watermarks SET $updatestr WHERE wmid='$wmid'");
		cls_CacheFile::Update('watermarks');
		adminlog('修改水印方案','修改水印方案');
		cls_message::show('修改水印成功','?entry=watermark&action=watermarkdetail&wmid='.$wmid);
	}
}
?>
