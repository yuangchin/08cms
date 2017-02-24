<?php
/*
* 推荐位分类管理
* 注意：避免分类的强制删除
*/
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('pusharea')) cls_message::show($re);
if(empty($action)){
	backnav('pushareas','pushtype');
	$pushtypes = cls_PushType::InitialInfoArray();
	if(!submitcheck('bsubmit')){
		$TitleStr = "推送分类管理 &nbsp; &nbsp;>><a href=\"?entry=$entry&action=pushtypeadd\" onclick=\"return floatwin('open_pushtypesedit',this)\">添加分类</a>";
		tabheader($TitleStr,'pushtypesedit',"?entry=$entry",'7');
		trcategory(array('ID','分类名称|L','备注说明|L','排序','删除',));
		foreach($pushtypes as $k => $v){
			echo "<tr class=\"txt\">\n".
				"<td class=\"txtC w30\">$k</td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][title]\" value=\"".mhtmlspecialchars($v['title'])."\" size=\"25\" maxlength=\"30\"></td>\n".
				"<td class=\"txtL\"><input type=\"text\" name=\"fmdata[$k][remark]\" value=\"".mhtmlspecialchars($v['remark'])."\" size=\"50\" maxlength=\"100\"></td>\n".
				"<td class=\"txtC w50\"><input type=\"text\" name=\"fmdata[$k][vieworder]\" value=\"$v[vieworder]\" size=\"2\"></td>\n".
				"<td class=\"txtC w30\"><a onclick=\"return deltip(this,$no_deepmode)\" href=\"?entry=$entry&action=pushtypedel&ptid=$k\">删除</a></td>\n".
				"</tr>";
		}
		tabfooter('bsubmit');
		a_guide('pushtypesedit');
	}else{
		if(!empty($fmdata)){
			foreach($fmdata as $k => $v){
				cls_pushtype::ModifyOneConfig($v,$k);
			}
		}
		adminlog('编辑推送位分类管理列表');
		cls_message::show('分类编辑完成',"?entry=$entry");
	}
}elseif($action =='pushtypeadd'){
	if(!submitcheck('bsubmit')){
		tabheader('添加推送位分类','pushtypeadd',"?entry=$entry&action=$action",2,0,1);
		trbasic('分类名称','fmdata[title]','','text',array('validate'=>makesubmitstr('fmdata[cname]',1,0,3,30)));
		trbasic('备注说明','fmdata[remark]','','text');
		tabfooter('bsubmit');
	}else{
		$ptid = cls_pushtype::ModifyOneConfig($fmdata,0);
		if($ptid){
			adminlog('添加推送分类');
			cls_message::show('推送分类添加成功。',axaction(6,"?entry=$entry"));
		}else{
			cls_message::show('推送分类添加不成功。');
		}
	}
}elseif($action == 'pushtypedel' && $ptid){
	backnav('pushareas','pushtype');
	deep_allow($no_deepmode);
	if(!submitcheck('confirm')){
		$message = "删除不能恢复，确定删除所选项目?<br><br>";
		$message .= "确认请点击>><a href=?entry=$entry&action=$action&ptid=$ptid&confirm=ok>删除</a><br>";
		$message .= "放弃请点击>><a href=?entry=$entry>返回</a>";
		cls_message::show($message);
	}
	if($re = cls_pushtype::DeleteOne($ptid)) cls_message::show($re);

	adminlog('删除推送位分类');
	cls_message::show('分类删除完成',"?entry=$entry");
	
}else cls_message::show('错误的文件参数');
