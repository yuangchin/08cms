<?php
/**
 * 数据导出到excel类
 * 链接上传递chid/cuid/aid  
 * 调用方法：
 *     模型中调用：$oL->s_footer_ex($cms_abs."admina.php?entry=extend&extend=export_excel&chid=$chid&filename=userhouse");
 *     交互中调用：	echo "<a style=\"float:right;text-decoration:none;\" onclick=\"return floatwin('open_arcdetail',this)\" href=\"".$cms_abs."admina.php?entry=extend&extend=export_excel&chid=$chid&cuid=$cuid&filename=".($chid==3?'esfyyx':'czfyyx')."&q=".$where_str."&p=".$p."\"><input class='button' type=\"button\" value=\"EXCEL导出\"></a>";
 *
 * 根据传参，可显示文档模型字段，文档主表字段以及交互字段 或  文档主表/文档模型字段  
 * 或  交互字段(字段中不含datatype为image、images、map、htmltext的字段)
 *
 * 处理数据，导出excel文件 
 */
class cls_exportexcels{	

	public $mc=0;
	private $chid;
	private $cuid;
	private $mchid;
	
	function __construct(){
		global $chid,$cuid,$mchid;
		$this->mc = defined('M_ADMIN') ? 0 : 1;	
		$this->chid = empty($chid) ? 0 : max(1,intval($chid));
		$this->cuid = empty($cuid) ? 0 : max(1,intval($cuid));
		$this->mchid = empty($mchid) ? 0 : max(1,intval($mchid));
    }

	/**
	  *显示可选取导出到excel的字段
	  *@param string $table_title		table顶部的标题说明
	*/
	public function ShowFieldsTable($chid,$cuid,$mchid,$table_title,$where_str,$url){
		$fields_arr = $this->GetExportFields($chid,$cuid,$mchid);
		$this->ShowExportFields($fields_arr[0],$fields_arr[1],$table_title,$where_str,$url);
	}


	##获取可导出到Excel的字段
	protected function GetExportFields($chid,$cuid,$mchid){
		$chid_fields = $chid ? cls_cache::Read('fields',$chid) : array();
		$cuid_fields = $cuid ? cls_cache::Read('cufields',$cuid) : array();	
		$mchid_fields = $mchid ? cls_cache::Read('mfields',$mchid) : array();	
		$chk_arr = array();
		$chk_count = 0;	
		foreach(array($chid_fields,$cuid_fields,$mchid_fields) as $arr){
			if(!empty($arr)){
				foreach($arr as $k => $v){
					if($v['available'] && !in_array($v['datatype'],array('image','images','map','htmltext','vote'))){
						$chk_arr[$v['tbl']][$k] = $v['cname'];
						$chk_count ++;
					}
				}
			}
		}
		return array($chk_arr,$chk_count);
	}
	
	##将可导出到Excel的字段制成表格
	protected function ShowExportFields($chk_arr,$chk_count,$table_title,$where_str,$url){
		global $extend_str,$chid,$cuid,$mchid,$aid,$mid,$filename,$authkey;
		$chid = empty($chid) ? 0 : max(1,intval($chid));
		$cuid = empty($cuid) ? 0 : max(1,intval($cuid));
		$mchid = empty($mchid) ? 0 : max(1,intval($mchid));
		$aid = empty($aid) ? 0 : max(1,intval($aid));
		$mid = empty($mid) ? 0 : max(1,intval($mid));
		$td_num = 5;//table中每行的单元格个数
		
		$where_str = urlencode($where_str);
        $p = md5($where_str.$authkey);//防篡改加密参数,传递参数后，判断$where_str+$authkey加密后的字符串与$p是否一致
		$table_title || $table_title = "请选择导出数据的项目";
		$table_title .="<input type=\"checkbox\" id=\"checkall\" style=\"margin:0 0 0 30px;\" onclick=\"check_all('newform')\">(全选)";
		echo form_str('newform',"$url&chid=$chid&cuid=$cuid&mchid=$mchid&aid=$aid&mid=$mid&filename=$filename&q=$where_str&p=$p",1,1,1,'post');
		echo "<div class=\"conlist1\">$table_title</div>";
		$checkbox_str = 111;//该值随便赋值，只是用作区分checkbox的一段字符串
		foreach($chk_arr as $k => $v){ 
			$_k = substr($k,0,7); $_a = array('archive'=>'文档','members'=>'会员',); 
			$title = isset($_a[$_k]) ? $_a[$_k] : '交互';
			echo "<div class=\"conlist1\">{$title}信息 [$k]表</div>";
			echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\" tb tb2 bdbot\" style=\"text-align:left !important; \">\n";
			$_count = count($v);
			$i = 0;				
			foreach($v as $key => $val){
				$i ++;
				if($i % $td_num == 1){
					$checkbox_str ++;
					echo "<tr><td width=\"60\"><input style=\"margin-left:20px;\" type=\"checkbox\" onclick=\"check_each_row_input('newform','checkbox".$checkbox_str."',this)\"></td>";
				}
				echo "<td width=\"140\"><input type=\"checkbox\" id=\"checkbox".$checkbox_str.$key."\" name=\"fmdata[$k][$key]\" value=\"$val\">$val</td>";				
				if(($i % $td_num != 0) && ($i == $_count)){
					for($j=0;$j<$td_num - $i % $td_num;$j++){
						 echo "<td width=\"140\"></td>";
					}
				}
				if(($i % $td_num == 0) || ($i == $_count)) echo "</tr>";
			}
			echo "</table>";
		}
			
		echo "<br /><span style=\"margin-right: 50px;\">导出<input id=\"fmdata[limit]\" name=\"fmdata[limit]\" type=\"text\" value=\"\" style=\"width:30px;\">条数据</span><input class=\"btn\" type=\"submit\" name=\"bsubmit\" value=\"导出\"></form>";
		echo "<div class=\"blank9\"></div>";
		?>
        <script type="text/javascript">
        	function check_all(formname){
				var form = document.forms[formname];
				for(var i = 0; i < form.elements.length; i++){
					var e = form.elements[i];
					e.checked = document.getElementById('checkall').checked;					
				}
			}			
			function check_each_row_input(formname, sid, obj){	
				var form = document.forms[formname];
				for(var i = 0; i < form.elements.length; i++) {
					var e = form.elements[i];		
					if(e.type == 'checkbox' && e.id.indexOf(sid) != -1) {									
						e.checked = obj.checked;
					}					
				}
			}
			function ajaxform(form){
				var num = 0;
				for(var i = 0; i < form.elements.length; i++) {
					var e = form.elements[i];		
					if(e.type == 'checkbox' && e.checked == true) {									
						num ++;
					}					
				}
				if(num == 0){alert("请选择导出项目");return false ;}
				return true;
			}
		</script>
        <?php
	}
	
	##从数据库中获取将被导出到excel的数据	 
	protected function GetDataForExcel($fmdata,$where_str){
		global $db,$tblprefix;
		//表单中传递过来的 sql查找数据数量限制
		$limit = empty($fmdata['limit']) ? 100 : max(1,intval($fmdata['limit']));
		//表单中传递过来的 列表的搜索条件（即对列表搜索所组成的字符串）		
		unset($fmdata['limit']);

		$_afields = array();
		$_cufields = array();
		$_mfields = array();
		$this->chid && $_afields = cls_cache::Read('fields',$this->chid);	
		$this->cuid && $_cufields = cls_cache::Read('cufields',$this->cuid);
		$this->mchid && $_mfields = cls_cache::Read('mfields',$this->mchid);
		$field_arr = array();//存放字段类型为select,mselect,data的字段的innertext拆分后的数组
		$datatype_arr = array('select','mselect');
		$mselect_arr = array();
		foreach($fmdata as $k => $v){//获取innertext并进行拆分
			foreach($v as $key => $val){
				if(strstr($k,'archives')) $_cache_name = '_afields';
				elseif(strstr($k,'members')) $_cache_name = '_mfields';
				else $_cache_name = '_cufields';
				$arr_name = $$_cache_name;			

				if(in_array($arr_name[$key]['datatype'],$datatype_arr)){
					$re = array();
					$arr_name[$key]['datatype'] == 'mselect' && ($mselect_arr[] = $key);
					if(!$arr_name[$key]['fromcode']){
						$temps = explode("\n",$arr_name[$key]['innertext']);
						foreach($temps as $v){
							$temparr = explode('=',str_replace(array("\r","\n"),'',$v));
							$temparr[1] = isset($temparr[1]) ? $temparr[1] : $temparr[0];
							$re[$temparr[0]] = $temparr[1];
						}
						unset($temps,$temparr);
					}else{
						$re = @eval($arr_name[$key]['innertext']);
					}
					$field_arr[$key] = $re;
				}elseif($arr_name[$key]['datatype'] == 'date'){
					$field_arr[$key] = 'date';
				}
			}
			
		}
		$field_arr_key = array_keys($field_arr);		
		
		$sql = $this->contruct_full_sql($where_str,$limit,$fmdata); 
		$query = $db->query($sql);
		$data = array();
		while($row = $db->fetch_array($query)){
			$r =array();
			foreach($row as $k => $v){
				if(in_array($k,$field_arr_key)){
					if($field_arr[$k] != 'date'){					
						if(in_array($k,$mselect_arr)){//多项选择
							$str = '';
							$v_arr = array_filter(explode("\t",$v));
							foreach($v_arr as $key){		
								$str .= $field_arr[$k][$key]."/";
							}
							$r[] = $str;
						}else{//单项选择							
							$r[] = (empty($v) || !isset($field_arr[$k][$v])) ?'-': $field_arr[$k][$v];
						}
						
					}else $r[] = empty($v) ? '-' : date('Y-m-d',$v);
				}else{
					$r[] = $v;
				}
			}
			$data[] = $r;
		}

		
		$title_arr = array();
		foreach($fmdata as $k => $v){
			foreach($v as $key => $val){
				$title_arr[] = $val;
			}
		}
		return array("content"=>$data,"title"=>$title_arr);
	}
	
	/**
	 *构造完整的sql
	 *@param  string  $filter   列表的搜索条件，即filterstr
	 *@param  limit   $limit    sql搜索数据库条数限制
	 *@param  data    $fmdata  	表单传递的字段数据
	 *@return string  			完成的查询语句
	 */
	protected function contruct_full_sql($where_str,$limit,$fmdata){
		global $tblprefix,$chid,$cuid,$mchid,$aid,$mid;
		$chid = empty($chid) ? 0 : max(1,intval($chid));
		$cuid = empty($cuid) ? 0 : max(1,intval($cuid));
		$mchid = empty($mchid) ? 0 : max(1,intval($mchid));
		$aid = empty($aid) ? 0 : max(1,intval($aid));
		$mid = empty($mid) ? 0 : max(1,intval($mid));
		$_select_str = '';
		$_from_str = '';
		$_where_str = '';
		
		/*
		echo "\n<br>$where_str\n";
		echo "\n<br>$limit\n";
		echo "\n<br>"; //print_r($fmdata); echo "\n";
		*/
		
		// 组合：mchid, chid, cuid, chid+cuid
		if(!empty($cuid)){
			$cucfg = cls_cache::Read('commu',$cuid);
			$cutab = $cucfg['tbl'];
		} 
		if(!empty($chid) && !empty($cuid)){ // && !empty($aid)
			$_from_str .= " {$tblprefix}".atbl($chid)." as a ";
			$_from_str .= " INNER JOIN {$tblprefix}archives_$chid as ac ON ac.aid=a.aid";
			$_from_str .= " INNER JOIN {$tblprefix}$cutab as cu ON cu.aid=a.aid";
			$orderby_str = " cu.cid DESC ";
			if(!empty($aid)){
				$_where_str .=  " AND cu.aid='$aid' ";
			}
		}elseif(!empty($chid)){
			$_from_str .= " {$tblprefix}".atbl($chid)." a ";
			$_from_str .= " INNER JOIN {$tblprefix}archives_$chid as ac ON ac.aid=a.aid";
			$orderby_str = " a.aid DESC ";
		}elseif(!empty($mchid)){
			$_from_str .= " {$tblprefix}members as m ";
			$_from_str .= " INNER JOIN {$tblprefix}members_sub as ms ON ms.mid=m.mid";
			$_from_str .= " INNER JOIN {$tblprefix}members_$mchid as mc ON mc.mid=m.mid";
			$orderby_str = " m.mid DESC ";
		}else{ // cuid
			$_from_str .= " {$tblprefix}$cutab as cu ";	
			$orderby_str = " cu.cid DESC ";
		}
		
		foreach($fmdata as $k => $v){
			$_tblpre = '';
			if(strstr($k,'archives')){	
				$_tblpre = strstr($k,'_')? "ac" : "a";	
			}elseif(strstr($k,'members')){
				$_tblpre = strstr($k,'_sub') ? 'ms' : (strstr($k,'_') ? 'mc' : 'm');	
			}else{
				$_tblpre = "cu";		
			}
			$_tblpre_arr[] = $_tblpre;
			foreach($v as $key => $val){
				$table_header_arr[] = $val;
				$_select_str .= ",".$_tblpre.".$key ";
			}
		}
		
		$_where_str .= $this->exadmin_full_sql($where_str); 
		$_where_str .= $this->exuser_full_sql($where_str); 
		
		$sql = "SELECT ".substr($_select_str,1)." FROM $_from_str WHERE 1=1 ".$_where_str." ORDER BY ".$orderby_str." LIMIT $limit";
		echo $sql;
		return $sql;
	}
	
	//导出excel表
	public function  ExportExcel($filename,$fmdata,$where_str){
		global $mcharset,$timestamp;		
		$data = $this->GetDataForExcel($fmdata,$where_str);	
		$charset = strstr(strtoupper($mcharset),'GB') ? 'GB2312' : 'UTF-8';
		$xls = new cls_XmlExcelExport($charset);//默认UTF-8编码
		$xls->generateXMLHeader($filename.date('Y-md-His',$timestamp));  //excel文件名
		$xls->worksheetStart('message');
		$xls->setTableHeader($data['title']);  //表字段名
		$xls->setTableRows($data['content']); //内容
		$xls->worksheetEnd();
		$xls->generateXMLFoot();
		unset($xls);
		die();
	}
}







?>