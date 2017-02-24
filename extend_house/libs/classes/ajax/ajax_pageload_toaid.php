<?php
/**
 * 评论列表（含回复）
 *
 * @example   请求范例URL：index.php?/ajax/pageload_rems/aj_model/cu,1/aid/542753/aj_pagesize/5/aj_pagenum/4/domain/192.168.1.11/
 * @author    Peace@08cms.com
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_pageload_toaid extends _08_M_Ajax_pageload_Base{
    
	private $tplurl = ''; //模版目录
	
	public function __toString(){
		//初始化及模拟da处理
		$this->_initDa(array('aid'));
		if(!in_array($this->mcfgs[1],array(101,999))) die('Error::cuid='.$this->mcfgs[1]);
		//常规sql条件
		$this->_getSql();
		//初始化模版目录
		//$this->tplurl = cls_tpl::TemplateTypeDir();
		$btags = cls_cache::Read('btags'); 
		$this->tplurl = $btags['tplurl'];  
		//主评论(交互)
		$result = $this->_cuList($this->_ajda['aid']);
        return $result;
    }
	
	public function _cuList($aid=0){
		foreach($this->sqlarr as $k){
			$$k = $this->$k;
		} 
		//扩展where条件
		$where .= " AND aid='$aid'";  
		//全部sql及结果
		$order = $this->_getOrder(array('cid'),'c.aid DESC');
		$sql = "SELECT $select FROM $from WHERE $where ORDER BY $order LIMIT $limit";
		$result = $this->_getData($sql); //echo "\n<br>$sql;\n<br><br>";
		//扩展result结果处理
		foreach($result as $k=>$r){
			$r['content'] = str_replace(array('{:',':}'),array("<img src=".$this->tplurl."newscommon/images/face/",".gif>"),$r['content']); 
			$r['content'] = cls_url::tag2atm($r['content'],1);
			$result[$k] = $r; 	
		}
		return $result;
	}
	
}