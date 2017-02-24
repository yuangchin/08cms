<?php
/**
 * 选择所属合辑(特价房/团购/装修案例添加),参考tools下的相关代码
 * 参考核心Arc_list:文档添加时-选择所属合辑,这里扩展处理leixing和mid
 * 
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_exArc_list extends _08_Models_Base
{
    public function __toString()
    {
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
    	$chid = (isset($this->_get['chid']) ? intval($this->_get['chid']) : 0);
		$mid = (isset($this->_get['mid']) ? intval($this->_get['mid']) : 0); 
        $keywords = (empty($this->_get['keywords']) ? '' : @cls_string::iconv("UTF-8", $mcharset, $this->_get['keywords']));
    	$result = array(); 
    	if( $ntbl = atbl($chid) )
        { 
    	    $archives = parent::getModels('Archives_Table', "{$ntbl} a");
            $archives->select('a.*,c.*',true)					 
                     ->innerJoin("#__archives_{$chid} c")->_on('a.aid=c.aid')
                     ->where("checked=1 AND ".(empty($mid) ? "leixing IN(0,1)" : "mid=$mid")."")->_and('a.subject')
                     ->like($keywords)
                     ->limit(100)
                     ->exec();
           
			while( $r=$archives->fetch() )
            {
				$thumb = $r['thumb'];
				$thumb = empty($thumb) ? '' : '[图]';
				$subject = $r['subject']; 
				$result[] = array('aid' => $r['aid'], 'subject'=>$thumb.$subject,'create'=>date('Y-m-d',$r['createdate']));
			}			
    	}
        
        return $result; 
    }
}