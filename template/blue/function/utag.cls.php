<?php

// 模版自定义搜索排序类(扩展)
class cls_uso extends cls_usobase{
	
	// caco_url_ext基类为空方法,// 各系统扩展部分: 子类别怎么处理? 为空怎么处理? 
	
	// 参考:order_set(), 这里html代码格式不同
	static function order_set2($template, $by, $orderby, $ordermode, $class){
		$url = cls_uso::$urls['filorder'].cls_uso::$addstr; 
		if($by!=$orderby){
			$defmode = empty($class['defmode']) ? '' : '&ordermode=1';
		}elseif($ordermode){
			$defmode = '';
		}else{
			$defmode = '&ordermode=1';
		}
		$url = cls_uso::format_url("$url&orderby=$by$defmode",1);
		$template = str_replace('(class)', @$class[$by == $orderby ? ($ordermode ? '1' : 0) : 2], $template);

		$template = str_replace('(url)', $url, $template);
		return $template;
	}
	
	/*/ $ext='ccid31_level2'; 商品,商品$pid下所有车型level=2
	static function xx_caco_url_ext(&$caco, $coid, $pid=0, $ext=''){  echo $ext.'xxxx';
		if($ext=='ccid31_level2'){  
			$caco = cls_cache::Read('coclasses', $coid);
			$ids = sonbycoid($pid,$coid,0);
			foreach($caco as $k=>$v){
				if(in_array($k,$ids)){
					if(($v['level']!=2)){
						unset($caco[$k]);
					}
				}else{
					unset($caco[$k]);
				}
			} 
		}else{ 
			//不处理	
		}
	}*/
	
}

// 模版自定义元素显示(扩展)
class cls_uview extends cls_uviewbase{

	/*
	rate: 倍率(乘数)
	dec: 小数位数
	unit: 单位(如：万元）
	null: 为空时提示
	*/
	static function number_view($value, $rate=0.001, $dec=2, $null='面议', $unit='万元'){
		$re = $value*$rate;
		if(empty($re)){
			$re = $null;
		}else{
			$re = sprintf("%.{$dec}f", $re).$unit;
		}
		return $re;
	}
	// CommuFloor($rowid=0)
	
}

// 模版组sql(扩展)
class cls_usql extends cls_usqlbase{
	
	// order_bys()
	// order_str($rearray='0',$deforder='',$fixarr=array())
	// where_str($cfgs=array(array('subject')),$exstr='')
	// sql_arc($extcond='',$skip=array())
	// sql_mem($extcond='',$skip=array())
	
}

// 模版自定义函数 //////////////////

//区间:djfrom=22&djto=33
function u_exsql_area($field,$from,$to){
	$_da = cls_Parse::Get('_da');
	$whrext = ""; $i=0;
	foreach(array('from','to') as $k){
		$val = @$_da[$$k]; 
		if(!empty($val)){ 
			$val = floatval($val); $op = empty($i) ? '>=' : '<=';
			if($val) $whrext .= " AND $field{$op}'$val' ";
		}
		$i++;
	}
	//if(!empty($whrext)) $whrext = substr($whrext,4);
	return $whrext;
}

// 房源:二手,出租,楼盘,小区;搜索附加sql
// $leixing: 楼盘与小区的区分,
// $mid: 经纪公司下的经纪人(mids)
function u_exsql($chid=0,$leixing=0,$mid=0){
	$_da = cls_Parse::Get('_da');
	$addno = @$_da['addno'];
	$mchid = @$_da['mchid'];
	$whrext = "";
	if($chid==4){
        $lx = $leixing ? $leixing : $addno;
        $whrext .= " AND (leixing='0' OR leixing='$lx')";
		if(!empty($_da['letter'])){
			$whrext .= " AND a.letter='$_da[letter]' ";
		}
		$whrext .= u_exsql_area('dj','djfrom','djto');
	}elseif(in_array($chid,array(2,3))){ //
		$whrext .= u_exsql_area('mj','mjfrom','mjto');
		$whrext .= u_exsql_area('zj','zjfrom','zjto');
		if(!empty($mid)){
			$mids = get_subMids($mid);
			$mids && $whrext .= " AND a.mid IN($mids)";
		}
		if(!empty($mchid) && in_array($mchid,array(1,2))){
			$whrext .= $mchid==2 ? " AND a.mchid = '$mchid' " : " AND a.mchid < '2' ";
		}
		if(!empty($_da['goodhouse'])) $whrext .= " AND a.goodhouse='1' "; 
		if(!empty($_da['manyimg'])) $whrext .= " AND a.imgnum>='".intval($_da['manyimg'])."' "; 
	}elseif(in_array($chid,array(115,116))){ //商业地产-楼盘
		$whrext .= u_exsql_area('dj','djfrom','djto');
	}elseif(in_array($chid,array(117,118))){ //商业地产-出售
		$whrext .= u_exsql_area('mj','mjfrom','mjto');
		$whrext .= u_exsql_area('zj','zjfrom','zjto');
	}elseif(in_array($chid,array(119,120))){ //商业地产-出租
		$whrext .= u_exsql_area('mj','mjfrom','mjto');
		$whrext .= u_exsql_area('zj','zjfrom','zjto');
	}else{
		if(!empty($mchid) && in_array($mchid,array(1,2))){
			//$whrext .= " AND a.mchid = '$mchid' ";
		}
		if(!empty($manyimg)){
			//$cnsqls .= " AND a.imgnum > '0'";
		}
        if(!empty($day)){
            //$cnsqls .= " AND date_sub(curdate(), interval $day day) <= from_unixtime(a.createdate)";
        }	
	}
	if(!empty($whrext)) $whrext = substr($whrext,4);
	return $whrext;
	
}

/*/ dhr_sidebar.html,积分兑换榜
function u_duihuan_tops(){
	$tblprefix = cls_env::getBaseIncConfigs('tblprefix');
	return "SELECT COUNT(aid) AS cnt,aid FROM {$tblprefix}commu_duihuan GROUP BY aid";
}*/

////////////////////////////////////////////////////////////////////
// 以下 - 兼容函数 - 暂时保留; 后续删除       ... //////////////////
////////////////////////////////////////////////////////////////////

function u_sql_arc($extcond='',$skip=array()){
	return cls_usql::sql_arc($extcond, $skip);
}

function u_sql_mem($extcond='',$skip=array()){
	return cls_usql::sql_arc($extcond, $skip);
}
