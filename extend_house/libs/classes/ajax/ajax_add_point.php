<?php
/**
 * 对楼盘进行评分。
 *
 * @example   请求范例URL：index.php?/ajax/add_point/domain/192.168.1.153/aid/589868/field/wygl/point/10/....
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Add_Point extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		header("Content-Type:text/html;CharSet=$mcharset");

		$aid  = empty($this->_get['aid']) ? 0  : max(1,intval($this->_get['aid']));
		$point  = empty($this->_get['point']) ? 0  : max(1,intval($this->_get['point']));
		$field  = empty($this->_get['field']) ? 0  : trim($this->_get['field']);
		$tblprefix = $this->_tblprefix;
		$db = $this->_db;
		$m_cookie  = cls_env::_COOKIE();


		$fields = cls_cache::Read('cufields',2);
		$_files_name_arr = array_keys($fields);
		if(empty($field) || !in_array($field,$_files_name_arr) || !in_array($field."rs",$_files_name_arr)){
			exit("评分错误，字段{$field}不是正常数据表的字段。");
		}

		$_cookname = "08cms_loupan_".$aid."_".$field;
		$_arr = array();

		if(empty($m_cookie[$_cookname])){
			//限时评分
			$commus = cls_cache::Read('commus');
			$_repeattime = empty($commus['2']['repeattime'])?'-1':$commus['2']['repeattime'] * 60;
			msetcookie($_cookname,1,$_repeattime);
			//查找原来的平均分，人数，然后处理提交过来的数据，计算平局分以及人数再存进数据库
			$point = $point * 10;
			if($_arr = $db->fetch_one("SELECT * FROM {$tblprefix}commu_dp WHERE aid = '$aid' AND mname = '' AND mid = '' ")){
				$_total_point = $_arr[$field] * $_arr[$field.'rs'] + $point;
				$_people_num  = $_arr[$field.'rs'] + 1;
				$_avg_point = round($_total_point/$_people_num,2);
				//查找数据中不为0，并且启用的平均分的字段，算出total字段的分数

				//_all_point:获得启用的平均分的总和
				$_all_point = 0;
				//_all_num:获得启用的平均分的个数
				$_all_num = 0;
				foreach($fields as $k => $v){
					if(strstr($k,'rs')  && !strstr($k,$field)){
						$k = substr($k,0,strpos($k,'rs'));
						$_key_name = substr($k,strpos($k,'rs'));
						$_all_point += $_arr[$k];
						$_all_num ++;
					}
				}
				//因为排除了链接传递进来的平均分字段，下面要加上
				$_total = round(($_all_point + $_avg_point)/($_all_num + 1),2);
				$db->query("UPDATE {$tblprefix}commu_dp SET cuid = '2',".$field." = '$_avg_point',".$field."rs = '$_people_num',total = '$_total' WHERE aid = '$aid' AND mname = ''");
				$_arr = array('field' =>$field,'point'=> $_avg_point, 'renshu' =>$_people_num,'total'=>$_total,'repeattime'=>$_repeattime);
			}else{
				$db->query("INSERT INTO {$tblprefix}commu_dp SET aid = '$aid',cuid = '2',".$field." = '$point',".$field."rs = '1'");
				$_arr = array('field' =>$field,'point'=> $point, 'renshu' =>1,'total'=>$point);
			}
			cls_cubasic::setCridsOuter(2); //cuid=2
		}else{
			$_arr = array('error'=>'1');
		}
		//返回这一个字段的人数以及平均分
		echo 'var dpPerData = ' . json_encode($_arr) . ';';
		// echo json_encode($_arr);
	}
}