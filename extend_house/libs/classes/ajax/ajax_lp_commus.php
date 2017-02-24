<?php
/**
 * //楼盘内容页显示印象、评分
 *
 * @example   请求范例URL：index.php?/ajax/lp_commus/aid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Lp_Commus extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;
		header("content-type: text/javascript; charset=$mcharset");
		$aid     = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
		$fields = cls_cache::Read( 'cufields',2);
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;

		//楼盘印象显示
		$commu = cls_cache::Read('commu',44);
		$show_num = $commu['yxnum'];//楼盘印象显示个数
		$_sql = $db->query("SELECT cid,impression,renshu FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1' order by cid DESC  limit $show_num");
		$_total_num = $db->result_one("SELECT SUM(renshu) FROM {$tblprefix}commu_impression WHERE aid = '$aid' AND checked = '1'");
		$s = '';
		$i = 0;
		while($_rows = $db->fetch_array($_sql)){
			$s[$i]['cid'] = $_rows['cid'];
			$s[$i]['impression'] = $_rows['impression'];
			$s[$i]['per'] = round($_rows['renshu']/$_total_num,3)*100;
			$i++;
		}
		$yxData = _08_Documents_JSON::encode($s,1,1); 
		echo 'var yxData = ' . $yxData . ';';

		//楼盘点评分数显示
		$cn = 0;
		$_str = 'total';
		foreach($fields as $k => $v){
			if($v['available']) $_str .= ",".$k;
		}
		$_dp_arr = $db->fetch_one("SELECT ".$_str." FROM {$tblprefix}commu_dp WHERE aid = '$aid' AND mname = '' ");
		$show_pf = '';
		$show_pf['total'] = empty($_dp_arr['total'])?'0':$_dp_arr['total'];
		foreach($fields as $k2=>$v2){
			$key = $v2['ename'];
			if($key!='pjzj'){
				if($v2['datatype'] != 'select'){
					$cn++;
					$cn = $cn%9;
					$show_pf[$cn]['cname']= $v2['cname'];
					$show_pf[$cn]['ename']= substr($k2,0,strpos($k2,'r'));
					$show_pf[$cn]['per']= empty($_dp_arr[substr($k2,0,strpos($k2,'r'))]) ? '0' : $_dp_arr[substr($k2,0,strpos($k2,'r'))];
					$show_pf[$cn]['point']= empty($_dp_arr[substr($k2,0,strpos($k2,'r'))]) ? '0' : $_dp_arr[substr($k2,0,strpos($k2,'r'))];
					$show_pf[$cn]['pren']= empty($_dp_arr[$k2]) ? '0' : $_dp_arr[$k2];
				}
			}
		}
		$show_pf = _08_Documents_JSON::encode($show_pf,1,1); 
		echo 'var pointData = ' . $show_pf . ';';


		//从utags.fun.php移过来 －－－ 楼盘印象，看房报名提交表单时，cookie所需要的数据
		function u_set_cookie($cuid){
			$_cfgs  = cls_cache::Read('commu',$cuid);
			$_arr = array();
			$_arr['cooktime'] = empty($_cfgs['repeattime']) ? 24*60*60 : $_cfgs['repeattime']*60;
			$_arr['totalnum'] = empty($_cfgs['totalnum']) ? 0 : $_cfgs['totalnum'];
			return $_arr;
		}
		$_yinxiang = u_set_cookie(44); //一定要ja动态取出，否则静态文件出错。
		echo "
			var cooktime = parseInt(".(empty($_yinxiang['cooktime']) ? -1 : $_yinxiang['cooktime']).");
			var totalnum = parseInt('".(empty($_yinxiang['totalnum']) ? 0 : $_yinxiang['totalnum'])."');
		";
	}
}