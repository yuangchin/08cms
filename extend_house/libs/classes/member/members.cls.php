<?php
class cls_members extends cls_membersbase{
	
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
	
	/**
	* 针对房产地区分类字段ccid1 与会员所在区域字段szqy不一样，导致搜索区域出问题进行的sql处理
	*
	*/
	function s_sqlstr(){
		global $db,$tblprefix;
		$wherestr = empty($this->A['where']) ? '' : " AND {$this->A['where']}";
		if(empty($this->oS->no_list)){
			foreach($this->oS->wheres as $k => $v){
				$v = str_replace('m.ccid1','s.szqy',$v);
				$wherestr .= " AND $v";//搜索附加产生的where因素
			}
			if(!$this->acount = $db->result_one('SELECT COUNT(*) FROM '.$this->A['from'].($wherestr ? " WHERE ".substr($wherestr,5) : ''))){
				$this->acount = 0;
			}
			if(in_array($this->A['mode'],array('pushload',))){//排除已加载的文档
				if($this->acount && $loadeds = $this->s_loaded_ids()){
					//处理总数
					$this->acount -= count($loadeds);
					$this->acount = max(0,intval($this->acount));
					//处理wherestr
					$wherestr .= " AND {$this->A['pre']}mid ".multi_str($loadeds,1);
				}
			}
			if($wherestr) $wherestr = " WHERE ".substr($wherestr,5);
		}else{
			$wherestr = ' WHERE 0';
			$this->acount = 0;
		}
		$this->sqlall = "SELECT ".$this->A['select'].' FROM '.$this->A['from'].$wherestr.' ORDER BY '. $this->oS->orderby;
	}
}
