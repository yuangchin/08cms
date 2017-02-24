<?
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
aheader();
if($re = $curuser->NoBackFunc('affix')) cls_message::show($re);
include_once M_ROOT."include/database.fun.php";
$objcron=new cls_cron();
if($action == 'cronedit'){
	backnav('otherset','misc');
	if(!submitcheck('submitcron')  && !submitcheck('bsubmitcron')){
		tabheader('计划任务'."&nbsp; &nbsp; >><a onclick=\"return floatwin('open_channeledit',this)\" href=\"?entry=misc&action=cronadd\" onclick=\"return floatwin('open_inarchive',this)\">增加计划任务</a>",'cronedit',"?entry=misc&action=cronedit",7);
		$cy_arr = array("<input class=\"checkbox\" type=\"checkbox\" name=\"chkall\" onclick=\"checkall(this.form, 'selectid','chkall')\">",array('任务名称','txtL'),'启用','时间','上次/下次执行时间','删?','管理','备注');
		trcategory($cy_arr);
		$query = $db->query("SELECT * FROM {$tblprefix}cron ORDER BY type DESC,cronid");
		$itemstr = '';
		while($row = $db->fetch_array($query)){
			$filenamestr=str_replace(array('..', '/', '\\'), array('', '', ''), $row['filename']);
			$selectstr = "<input class=\"checkbox\" type=\"checkbox\"".($row['type']=='system' ? " disabled=\"disabled\"" : " name=\"delete[$row[cronid]]\" value=\"$row[cronid]\" onclick=\"deltip(this,$no_deepmode)\"").">";
			$namestr ="<input type=\"text\" value=\"$row[name]\" name=\"namenew[$row[cronid]]\" size=\"25\">";
			$beizhu ="<textarea  name=\"beizhunew[$row[cronid]]\" style='width: 250px;height: auto;resize: none;overflow-y:hidden;'>$row[marks]</textarea>";
			$availablestr = $row['available'] ? "<input type=\"checkbox\" checked=\"checked\"  value=\"1\" name=\"availablenew[$row[cronid]]\">" : "<input type=\"checkbox\" class=\"checkbox\" ".((empty($filenamestr) || $row['available']) ? "disabled=\"disabled\"" : "")." value=\"1\" name=\"availablenew[$row[cronid]]\">";
			
			$typestr = $row['type'] == 'system' ? '核心' : '扩展';
			$lastrunstr = $row['lastrun'] ? date('Y-m-d H:i',$row['lastrun']) : '-';
			$nextrunstr = $row['nextrun'] ? date('Y-m-d H:i',$row['nextrun']) : '-';
			if($row['day'] > 0 && $row['day'] < 32) {
				$row['time'] = '每月'.$row['day'].'日';
			} elseif($row['weekday'] >= 0 && $row['weekday'] < 7) {
				switch($row['weekday']){
					case 0:
						$weekstr = '日';
						break;
					case 1:
						$weekstr = '一';
						break;
					case 2:
						$weekstr = '二';
						break;
					case 3:
						$weekstr = '三';
						break;
					case 4:
						$weekstr = '四';
						break;
					case 5:
						$weekstr = '五';
						break;
					case 6:
						$weekstr = '六';
						break;			
				}
				$row['time'] = '每周'.$weekstr;
			} elseif($row['hour'] >= 0 && $row['hour'] < 24) {
				$row['time'] = '每日';
			} else{
				$row['time'] = '每小时';
			}
			if(!in_array($row['hour'], array(-1, ''))) {
				foreach($row['hour'] = explode("\t", $row['hour']) as $k => $v) {
					$row['hour'][$k] = sprintf('%02d', $v);
				}
				$row['hour'] = implode(',', $row['hour']);
				$row['time'] .= $row['hour'].'时';
			}
			
				
			#$row['time'] .= $row['hour'] >= 0 && $row['hour'] < 24 ? sprintf('%02d', $row['hour']).'时' : '';
			
			
			
			if(!in_array($row['minute'], array(-1, ''))) {
				$row['time'] .= '';
				foreach($row['minute'] = explode("\t", $row['minute']) as $k => $v) {
					$row['minute'][$k] = sprintf('%02d', $v);
				}
				$row['minute'] = implode(',', $row['minute']);
				$row['time'] .= $row['minute'].'分';
			} else {
				$row['time'] .= '00'.'分';
			}
			
			$timestr = $row['time'];

			$adminstr = '';
			$adminstr .= "<a href=\"?entry=misc&action=cronrun&cronid=$row[cronid]\" onclick=\"return floatwin('open_inarchive',this)\">执行</a>&nbsp; ";
			$adminstr .= "<a href=\"?entry=misc&action=crondetail&cronid=$row[cronid]\" onclick=\"return floatwin('open_inarchive',this)\">编辑</a>";
			$adminstr .= "&nbsp; <a href=\"?entry=misc&action=runTest&file=$filenamestr\" target='_blank'>测试</a>";
			$itemstr .= "<tr class=\"txt\">";
			$itemstr .= "<td class=\"txtC w40\" ><input class=\"checkbox\" type=\"checkbox\" name=\"selectid[$row[cronid]]\" value=\"$row[cronid]\"></td>\n";
			$itemstr .= "<td class=\"txtL\">$namestr<br/>$filenamestr ($typestr)</td>\n";
			$itemstr .= "<td class=\"txtC\">$availablestr</td>\n";
			#$itemstr .= "<td class=\"txtC\">$typestr</td>\n";
			$itemstr .= "<td class=\"txtC w100\">$timestr</td>\n";
			$itemstr .= "<td class=\"txtC\">$lastrunstr<br/>$nextrunstr</td>\n";
//			$itemstr .= "<td class=\"txtC\">$nextrunstr</td>\n";
			$itemstr .= "<td class=\"txtC w40\" >$selectstr</td><td class=\"txtC\">$adminstr</td>\n";
			$itemstr .= "<td class=\"txtC\">$beizhu</td>\n";
			$itemstr .= "</tr>\n";

		}
		echo $itemstr;
		tabfooter();
		tabfooter('bsubmitcron','执行计划任务','&nbsp; &nbsp;<input class="button" type="submit" name="submitcron" value="提交" >');
		a_guide('misc');		
	}elseif(submitcheck('submitcron')){
		if(!empty($delete) && deep_allow($no_deepmode)){
			foreach($delete as $k){
				$db->query("DELETE FROM {$tblprefix}cron WHERE cronid='$k' AND type='user'");
				unset($delete[$k]);
			}
		}
		if(!empty($namenew)){
			foreach($namenew as $k=>$v){
				$db->query("UPDATE {$tblprefix}cron set name='$v' WHERE cronid='$k'");
			}
		}
		if(!empty($beizhunew)){
			foreach($beizhunew as $k=>$v){
				$db->query("UPDATE {$tblprefix}cron set marks='$v' WHERE cronid='$k'");
			}
		}
		if(!empty($availablenew)){
			$query=$db->query("SELECT cronid FROM {$tblprefix}cron");
			while($row=$db->fetch_array($query)){
				if(empty($availablenew[$row['cronid']])){
					$db->query("UPDATE {$tblprefix}cron set available='0' WHERE cronid='$row[cronid]'");
				}else{
					$db->query("UPDATE {$tblprefix}cron set available='1' WHERE cronid='$row[cronid]'");
				}
			}		
		}else{
			$db->query("UPDATE {$tblprefix}cron set available='0'");
		}
		cls_message::show('计划任务更新成功', "?entry=misc&action=cronedit");
	}elseif(submitcheck('bsubmitcron')){		
		//一键执行计划任务		
		if(empty($selectid)) cls_message::show('请选择要执行的计划任务',axaction(1,M_REFERER));
		$_is_available_arr = array();
		$_is_available_sql = $db->query("SELECT cronid FROM {$tblprefix}cron WHERE available ='1'");
		while($r = $db->fetch_array($_is_available_sql)){
			$_is_available_arr[] = $r['cronid'];
		}			
		foreach($selectid as $m=>$n){
			if(in_array($n,$_is_available_arr))$objcron->run($n);
		}		
		cls_message::show('计划任务执行成功', "?entry=misc&action=cronedit");
	}
}elseif($action == 'crondetail'){
	if(!($cron = $db->fetch_one("SELECT * FROM {$tblprefix}cron WHERE cronid='$cronid'"))) cls_message::show('参数出错！');
	if(!submitcheck('miscedit')){	
		$cronminute = str_replace("\t", ',', $cron['minute']);
		$hours = str_replace("\t", ',', $cron['hour']);
		$weekdayselect=$dayselect=$hourselect="<option value=\"-1\">*</option>";
		for($i = 0; $i <= 6; $i++) {
			switch($i){
					case 0:
						$weekstr = '日';
						break;
					case 1:
						$weekstr = '一';
						break;
					case 2:
						$weekstr = '二';
						break;
					case 3:
						$weekstr = '三';
						break;
					case 4:
						$weekstr = '四';
						break;
					case 5:
						$weekstr = '五';
						break;
					case 6:
						$weekstr = '六';
						break;			
				}
			$weekdayselect .= "<option value=\"$i\" ".($cron['weekday'] == $i ? 'selected' : '').">".$weekstr."</option>";
		}

		for($i = 1; $i <= 31; $i++) {
			$dayselect .= "<option value=\"$i\" ".($cron['day'] == $i ? 'selected' : '').">$i 日</option>";
		}

		for($i = 0; $i <= 23; $i++) {
			$hourselect .= "<option value=\"$i\" ".($cron['hour'] == $i ? 'selected' : '').">$i 时</option>";
		}
		
		tabheader('编辑计划任务','miscedit',"?entry=misc&action=crondetail&cronid=$cronid");
		trbasic('任务名称','namenew',$cron['name'],'text',array('guide'=>'设置任务的名称，有利于理解任务目的。'));
		trbasic('每周','weekdaynew',$weekdayselect,'select',array('guide'=>'设置星期几执行本任务，"*"为不限制，本设置会覆盖下面的"日"设定'));
		trbasic('每月','daynew',$dayselect,'select',array('guide'=>'设置哪一日执行本任务，"*"为不限制'));
		trbasic('小时','hournew',$hours,'text',array('guide'=>'设置哪些小时执行本任务，时间以24小时制。至多可以设置 12 个小时值，多个值之间用半角逗号","隔开，留空为不限制'));
		trbasic('分钟','minutenew',$cronminute,'text',array('guide'=>'设置哪些分钟执行本任务，至多可以设置 12 个分钟值，多个值之间用半角逗号","隔开，留空为不限制'));
		trbasic('任务脚本','filenamenew',$cron['filename'],'text',array('guide'=>'设置本任务的执行程序文件名，请勿包含路径，核心程序脚本统一存放于'.$objcron->getPath(0).'目录中，扩展程序脚本统一放于'.$objcron->getPath(1).'目录中。'));
		trbasic('备注','beizhunew',$cron['marks'],'textarea',array('guide'=>'设置任务备注。'));
		tabfooter('miscedit');
		a_guide('alangdetail');		
	}else{
		$filenamenew = $filenamenew ? $filenamenew : '';
		$cron=array();
		if(empty($filenamenew) || !$objcron->isFile($filenamenew)) cls_message::show('任务执行文件不存在或者重名！请检查后重新填写。',axaction(6,'?entry=misc&action=crondetail&cronid=$cronid'));
		$minutenew=str_replace(',',"\t",$minutenew);
		$hournew=str_replace(',',"\t",$hournew);
		empty($hournew) && $hournew = '-1';
        $db->update( '#__cron', 
            array(
                'name'=>trim($namenew),
                'weekday'=>$weekdaynew,
                'day'=>$daynew,
                'hour'=>$hournew,
                'minute'=>$minutenew,
                'filename'=>trim($filenamenew),
                'nextrun'=>$timestamp,
				'marks'=>$beizhunew
            )
        )->where("cronid = $cronid")->exec();
		adminlog('编辑后台计划任务详情');
		$objcron->run($cronid);
		cls_message::show('计划任务更新成功',axaction(6,'?entry=misc&action=cronedit'));	
	}
}elseif($action == 'cronadd'){
	if(!submitcheck('cronadd')){
		$weekdayselect=$dayselect=$hourselect="<option value=\"-1\">*</option>";
		for($i = 0; $i <= 6; $i++) {
			switch($i){
					case 0:
						$weekstr = '日';
						break;
					case 1:
						$weekstr = '一';
						break;
					case 2:
						$weekstr = '二';
						break;
					case 3:
						$weekstr = '三';
						break;
					case 4:
						$weekstr = '四';
						break;
					case 5:
						$weekstr = '五';
						break;
					case 6:
						$weekstr = '六';
						break;			
				}
			$weekdayselect .= "<option value=\"$i\" >$weekstr</option>";
		}
        
		for($i = 1; $i <= 31; $i++) {
			$dayselect .= "<option value=\"$i\" >$i 日</option>";
		}

		for($i = 0; $i <= 23; $i++) {
			$hourselect .= "<option value=\"$i\" >$i 时</option>";
		}
        
		tabheader('添加任务','cronadd','?entry=misc&action=cronadd');
        trbasic('任务名称','nameadd'); 
		trbasic('每周','weekdayadd',$weekdayselect,'select',array('guide'=>'设置星期几执行本任务，"*"为不限制，本设置会覆盖下面的"日"设定'));
		trbasic('每月','dayadd',$dayselect,'select',array('guide'=>'设置哪一日执行本任务，"*"为不限制'));
		trbasic('小时','houradd','','text',array('guide'=>'设置哪些小时执行本任务，时间以24小时制。至多可以设置 12 个小时值，多个值之间用半角逗号","隔开，留空为不限制'));
		trbasic('分钟','minuteadd','','text',array('guide'=>'设置哪些分钟执行本任务，至多可以设置 12 个分钟值，多个值之间用半角逗号","隔开，留空为不限制'));
		trbasic('任务脚本','filenameadd','','text',array('guide'=>'设置本任务的执行程序文件名，请勿包含路径，核心程序脚本统一存放于'.$objcron->getPath(0).'目录中，扩展程序脚本统一放于'.$objcron->getPath(1).'目录中。'));
		trbasic('备注','marksadd','','textarea');

		tabfooter('cronadd','添加');
	}else{
		$filenameadd = trim($filenameadd);
		$fullpath = $objcron->isFile($filenameadd);
		if(empty($filenameadd) || !$fullpath) cls_message::show('任务执行文件不存在！请重新填写。',M_REFERER);
        $minuteadd=str_replace(',',"\t",$minuteadd);
		$houradd=str_replace(',',"\t",$houradd);
		empty($houradd) && $houradd = '-1';
        $type = strpos($fullpath,_08_EXTEND_DIR) ? 'user' : 'system';
        $db->insert( '#__cron', 
            array(
                'name'=>$nameadd,
                'weekday'=>$weekdayadd,
                'day'=>$dayadd,
                'hour'=>$houradd,
                'minute'=>$minuteadd,
                'filename'=>$filenameadd,
                'available'=>0,
                'type'=>$type,
                'nextrun'=>$timestamp,
				'marks'=>$marksadd
            )
        )->exec();
		$objcron->run($db->insert_id());
		adminlog('添加计划任务');
		cls_message::show('计划任务添加成功',axaction(6,'?entry=misc&action=cronedit'));	
	}

}elseif($action == 'cronrun'){
    $cronid = max(0,intval($cronid));
    $ret = $objcron->run($cronid);
    $msg = $ret ? '计划任务执行成功' : '<span style="color:red;">计划任务没执行</span>';
	cls_message::show($msg,axaction(6,'?entry=misc&action=cronedit'));
}elseif($action == 'runTest'){
    $filenamestr=str_replace(array('..', '/', '\\'), array('', '', ''), $file);    
	$cronfile = $objcron->isFile($filenamestr);    
    if($cronfile) {
        include(M_ROOT.$objcron->getPath(0).'exec.cron.php'); 
        include $cronfile;
    }else{
       cls_message::show("$filenamestr 计划任务脚本不存在!"); 
    }
    $fileClass = trim($file);       
    $fileClass = 'cron_' . str_replace(strrchr($fileClass,'.'),'',$fileClass);
    new $fileClass();	
	cls_message::show("$file:计划任务执行成功.");
}

?>