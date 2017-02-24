<?php
defined('M_COM') || exit('No Permission');
class cls_culist extends cls_culistbase{
	
	function s_footer_area($fix=''){
		$coc1 = array('0'=>array('title'=>'(整站)')) + cls_cache::Read('coclasses',1);
		$links = '';
		foreach($coc1 as $ccid=>$v){ 
			$title = $v['title'];
			if(cls_env::GetG('area')==$ccid) $title = "<b>$title</b>";
			$links .= "<a href='$fix&area=$ccid'>$title</a> &nbsp; ";
		}
		$links .= "<a href='$fix&fill_sites=1'>[补全地区数据]</a> &nbsp; ";
		echo $links;
		tabfooter();
		unset($this->oS);

		
	}
	
	/**
	*用于楼盘订阅列表操作项数据处理               
	*/
	function sv_o_all_lpdy($cfg=array()){
		$ofm = @$GLOBALS[$this->A['ofm']];
		$selectid = @$GLOBALS['selectid'];
		$rs = $this->m_db_array();//再次限制范围，以防跳出权限进行操作
		if($ofm && $selectid && $rs){
			$actcu = &$this->oO->actcu;
			foreach($rs as $r){ 
				if(!in_array($r['mid'],$selectid)) continue;
				$actcu = $r['mid'];        
				if(!empty($ofm['del_lpdy'])){//删除则不继续其它操作
					$this->sv_o_one('del_lpdy');
					continue;
				}
				foreach($ofm as $key => $v){ 
					$this->sv_o_one($key);
				}
				//$auser->updatedb();
			}
		}
	}
	
	function s_footer_ex($url,$orther=array()){
		global $authkey;
		$where_str = '';
		if(!empty($this->oS->wheres)){
			foreach($this->oS->wheres as $k => $v){
				$where_str .= " AND $v";
			}
		}		
		//除了搜索条件组成的sql，另外需要加入的sql组成部分
		if(!empty($orther) && !empty($orther['sql'])){
			$where_str .= " AND $orther[sql]";
		}
		$where_str = cls_string::urlBase64(trim($where_str));
        $p = md5($where_str.$authkey);//防篡改加密参数,传递参数后，判断$where_str+$authkey加密后的字符串与$p是否一致

		$html = "<a style=\"float:right;text-decoration:none;\" onclick=\"return floatwin('open_arcdetail',this)\" href=\"".$url."&q=".$where_str."&p=".$p."\"><input class='excel_button'  type=\"button\" value=\"EXCEL导出\"></a>";
		if(empty($this->A['MoreSet'])){
			echo strbutton('bfilter','筛选');
			echo $html;
		}else{
			echo $html;
			echo "</div></div>";//高级区结尾
		}
		tabfooter();
		unset($this->oS);
	}
    
    //问答答案头部显示
    function m_header_ex($answertype,$entry,$extend_str,$filterstr,$aid){
        $temparr = array(1=>'问题的答案',3=>'问题的补充',2=>'答案的补充与追问');
		$str = '';
		foreach($temparr as $k=>$v){
			$str .= $k == $answertype ? ' - <font color="red">'.$v.'</font>' : " - <a href=\"?entry=$entry$extend_str&aid=$aid$filterstr&answertype=$k\">$v</a>";
		}
		$str = substr($str,3);  
		$exstr = $aid ? " &nbsp; <a href='?entry=extend$extend_str'>全部答案&gt;&gt;</a>" : '';     
		$this->m_header($str.$exstr);
    }
}
