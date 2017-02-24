<?php
/**
 * 对楼盘添加印象。
 *
 * @example   请求范例URL：index.php?/ajax/addyinxiang/domain/192.168.1.153/aid/589868/yinxiang/....
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_AddYinXiang extends _08_Models_Base
{
    public function __toString()
    {
		global $onlineip;
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		header("Content-Type:text/html;CharSet=$mcharset");
		$tblprefix = $this->_tblprefix;
		$db = $this->_db;
		$curuser   = $this->_curuser;
		$timestamp = TIMESTAMP;

		$aid  = empty($this->_get['aid']) ? 0  : max(1,intval($this->_get['aid']));
		$yinxiang  = empty($this->_get['yinxiang']) ? ''  : cls_string::iconv('utf-8',$mcharset,$this->_get['yinxiang']);

		if(empty($yinxiang)){
			exit('var info = "印象不能为空。";');
		}
		if(!$_cfgs = cls_cache::Read('commu',44)){
			exit('var info = "请指定正确的交互项目。";');
		}
		$commu = cls_cache::Read('commu',44);
		$show_num = $commu['yxnum'];//楼盘印象显示个数
		$_cid = $db->result_one("SELECT cid FROM {$tblprefix}commu_impression WHERE aid='$aid' AND impression = '$yinxiang'");
		if(empty($_cid)){
			$check = $curuser->pmautocheck(@$_cfgs['autocheck'],'cuadd') ? 1 : 0 ;
			$_insert_sql = "INSERT INTO {$tblprefix}commu_impression SET aid = '$aid',impression = '$yinxiang',createdate = '$timestamp',renshu = '1',checked = '$check',ip = '$onlineip'";
			if($db->query($_insert_sql)){
				exit('var info = "'.($curuser->pmautocheck($_cfgs['autocheck'],'cuadd')?'添加印象成功，刷新即可查看。':'添加印象成功，请等待审核。').'";');
			}
		}else{
			//subm用来区分是通过点击印象还是提交印象传递过来的参数
			if(!empty($this->_get['subm'])){
				exit('var info = "印象已存在，请重新添加！";');
			}
			$db->query("UPDATE {$tblprefix}commu_impression SET renshu = renshu + 1 WHERE cid = '$_cid'");
			$_sql = $db->query("SELECT cid,impression,renshu FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1' ORDER BY cid DESC limit $show_num");
			$_total_num = $db->result_one("SELECT SUM(renshu) FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1'");
			$_yx_arr = '';
			$i = 0;
			while($_rows = $db->fetch_array($_sql)){
				$_yx_arr[$i]['cid'] = $_rows['cid'];
				$_yx_arr[$i]['baifenbi'] = round($_rows['renshu']/$_total_num,3)*100;
				$i++;
			}
			$_cid_check = $db->result_one("SELECT * FROM {$tblprefix}commu_impression WHERE cid = '$_cid' and checked = '1'");

			// echo json_encode($_yx_arr);
			echo 'var yxPerData = ' . json_encode($_yx_arr) . ';';
		}
	}
}