<?php
/**
 * 交互显示列表中，点击“加载更多”，加载更多交互内容
 *
 * @example   请求范例URL：index.php?/ajax/load_more_content/aid/...
 * @author    lyq <692378514@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Load_More_Content extends _08_Models_Base
{
	//用于查找的字段数组
	private $field_arr;

	public function __construct(){
		parent::__construct();
		$this->field_arr = array();
	}

    public function __toString()
    {
		$mcharset = $this->_mcharset;
		$db = $this->_db;
		$tblprefix = $this->_tblprefix;
		//文档AID
		$aid  = empty($this->_get['aid']) ? 0 : max(1,intval($this->_get['aid']));
		//交互CUID
		$cuid  = empty($this->_get['cuid']) ? 0 : max(1,intval($this->_get['cuid']));
		//栏目
		$caid = empty($this->_get['caid']) ? 0 : max(1,intval($this->_get['caid']));
		//字段组合
		$fieldstr = empty($this->_get['fieldstr']) ? '' : trim($this->_get['fieldstr']);
		$field_arr = explode(',',$fieldstr);
		$this->field_arr = $field_arr;
		//前一次搜索获取到的最后一条数据的cid
		$last_cid  = empty($this->_get['last_cid']) ? 0 : max(1,intval($this->_get['last_cid']));

		//交互已关闭
		$commu = cls_cache::Read('commu',$cuid);
		if(empty($commu)|| !$commu['available']) return 'var data= "交互已关闭";';

		//实例化文档
		$arc = new cls_arcedit;
		$arc->set_aid($aid);

		//请指定正确的文档ID
		if(!$arc->aid || !$arc->archive['checked'] || !in_array($arc->archive['chid'],$commu['chids'])) return 'var data= "请指定正确的文档ID";';

		//查找
		$select_str = $this->select_str($commu['tbl']);

		$from_str = " FROM {$tblprefix}".$commu['tbl']." cu INNER JOIN {$tblprefix}".$arc->tbl." a ON cu.aid=a.aid ";

		//条件str
		$where_str = " WHERE cu.aid = '$aid' AND cu.tocid='0' AND cu.checked=1";
		if(!empty($last_cid)){
			$where_str .= "  AND cu.cid < '$last_cid' ";
		}

		//排序
		$order_str = " ORDER BY cu.cid  DESC ";

		//返回数据
		$data = array();
		$sql = $db->query("SELECT $select_str  $from_str   $where_str  $order_str limit 10 ");
        $i = 0;
		while($row = $db->fetch_array($sql)){//评论
			if(isset($row['createdate']) && !empty($row['createdate'])){
				 $row['createdate'] = date("Y-m-d H:i:s",$row['createdate']);
			}
			$data[$i]['pl'] = $row;
			$hf_sql = $db->query("SELECT $select_str FROM {$tblprefix}".$commu['tbl']." cu WHERE cu.checked=1 AND cu.tocid=".$row['cid']." ORDER BY cid  DESC ");
			while($rows = $db->fetch_array($hf_sql)){//回复
				if(isset($rows['createdate']) && !empty($rows['createdate'])){
					 $rows['createdate'] = date("Y-m-d H:i:s",$rows['createdate']);
				}
				$data[$i]['hf'][] = $rows;
			}
            $i ++;
		}
		if(!empty($data)){
			$data = cls_string::iconv($mcharset, "UTF-8", $data);
			return 'var data = ' . json_encode($data) . ';';
		}else{//暂无数据
			return 'var data = "暂无数据";';
		}

	}

	// 取得数据表的字段信息
	private function fields($tbl){
		$fields = array();
		$sql = $this->db->query("show full fields from $tbl");
		while($row=$this->db->fetch_assoc($sql)){
			$fields[] = $row['Field'];
		}
		return $fields;
	}

	//组sql
	private function select_str($tbl){
		if(!empty($this->fieldstr)){//指定搜索字段
			//搜索字段str
			$select_str = ' cu.cid';
			//获取数据表字段
			$fields = $this->fields($tbl);
			$field_arr = $this->fieldstr;
			foreach($field_arr as $k){
				//对传递进来的搜索字段进行筛选，防止擅改url传递过来的字段变量
				if(array_key_exists($k,$fields)){
					$select_str .= ', cu.'.$k;
				}
			}
		}else{//不指定字段则查找全部
			$select_str = ' cu.* ';
		}
		return $select_str;
	}



}