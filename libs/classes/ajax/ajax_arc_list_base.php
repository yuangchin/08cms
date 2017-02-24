<?php
/**
 * 文档添加时-选择所属合辑
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/arc_list/datatype/xml/chid/4/keywords/d/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Arc_list_Base extends _08_Models_Base
{
    public function __toString()
    {
        $mcharset = cls_env::getBaseIncConfigs('mcharset');
    	$chid = (isset($this->_get['chid']) ? intval($this->_get['chid']) : 0);
        $keywords = (empty($this->_get['keywords']) ? '' : @cls_string::iconv("UTF-8", $mcharset, $this->_get['keywords']));
    	$result = array(); 
    	if( $keywords && ($ntbl = atbl($chid)) )
        { 
    	    $archives = parent::getModels('Archives_Table', "{$ntbl} a");
            $archives->select('a.*,c.*')
                     ->innerJoin("#__archives_{$chid} c")->_on('a.aid=c.aid')
                     ->where('checked=1')->_and('a.subject')
                     ->like($keywords)
                     ->limit(100)
                     ->exec();
           
			while( $r=$archives->fetch() )
            {
				$thumb = $r['thumb'];
				$thumb = empty($thumb) ? '' : '[图]';
				$subject = $r['subject']; //str_replace(array("'",'"'),'',$r['subject']);
				$result[] = array('aid' => $r['aid'], 'subject'=>$thumb.$subject,'create'=>date('Y-m-d',$r['createdate']));
			}
    	}
        return $result; 
    }
}