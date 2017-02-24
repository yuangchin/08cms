<?php
/**
 * // 房源-小区名称,选择
 * 参考核心exArc_list:文档添加时-选择所属合辑
 * 包含临时小区
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_ajaxloupan extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;
        $chid = 4;
		$keywords = (empty($this->_get['keywords']) ? '' : cls_string::iconv("UTF-8", $mcharset, $this->_get['keywords']));
		$db = $this->_db;

		
		// 小区
    	$result = array(); 
        
        //类系是否关联的情况下的sql部分
        $splitbls = cls_cache::Read('splitbls');  
        //查找已经跟文档关联的类系    
        $loupanCoidArr = $splitbls['15']['coids'];
        $selectStr = 'a.aid,a.subject';
        foreach(array(1,2,3,14) as $k){
            if(in_array($k,$loupanCoidArr)){
                $selectStr .= ',a.ccid'.$k;
            }    
        }
        
        $selectStr .= ',a.dt,a.thumb,c.address ';
        
		$db->select($selectStr)->from('#__archives15 a')
				 ->innerJoin("#__archives_{$chid} c")->_on('a.aid=c.aid')
				 ->where("a.checked=1 AND c.leixing IN(0,2)")
				 ->_and('(a.subject')->like($keywords)
				 ->_or(' c.address')->like($keywords);
		$db->setter('_sql', $db->getter('_sql') . ') ');
		$db->limit(100)->exec();
		while( $row=$db->fetch() )
		{
			$result[] = array('aid' => $row['aid'], 'subject'=>$row['subject'],'ccid1'=>$row['ccid1'],'ccid2'=>@$row['ccid2'],'ccid3'=>@$row['ccid3'],'ccid14'=>@$row['ccid14'],'address'=>$row['address'],'dt'=>$row['dt'],'thumb'=>cls_url::view_atmurl(preg_replace('/#\d*/','',$row['thumb'])));
		}
		
		// 临时小区表(判断?)
		if(count($result)<10){
			$db->select('a.*')->from('#__arctemp15 a')
				 ->where('1=1')
				 ->_and('(a.subject')->like($keywords)
				 ->_or(' a.address')->like($keywords);
			$db->setter('_sql', $db->getter('_sql') . ') ');
			$db->limit(100)->exec();
			while( $row=$db->fetch() )
			{
				$result[] = array('aid' => 0, 'subject'=>$row['subject'],'ccid1'=>$row['ccid1'],'ccid2'=>@$row['ccid2'],'ccid3'=>'','ccid14'=>'','address'=>$row['address'],'dt'=>$row['dt'],'thumb'=>'');
			}
		}

        return $result; 
    }
}
