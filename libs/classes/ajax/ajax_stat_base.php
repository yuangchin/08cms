<?php
/**
 * 
 *
 * @example   请求范例URL：http://nv50.08cms.com/index.php?/ajax/stat/datatype/xml/aids/1,2,5/&callback=$_iNp$JgYF8
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Stat_Base extends _08_Models_Base
{
    public function __toString()
    {
        preg_match("/^\d+(,\d+)?(?:,\d+)*$/", $this->_get['aids'], $match) || exit();
        $archives = parent::getModels('Archives_Table');
        $archives->select('clicks,comments,scores,orders,favorites,praises,debases,answers,adopts,price,crid,currency,closed,downs,
                           plays,mclicks,mplays,mdowns,wclicks,wdowns,wplays',true)
                 ->where(array('checked' => 1))
                 ->_and('aid')->_in($this->_get['aids'])
                 ->exec();
    	$output = '';
    	while($row = $archives->fetch())
        {
    		$output .= ",$row[aid]:{";
    		unset($row['aid']);
    		$row = array_filter($row);
    		$tmp = '';
    		foreach($row as $k => $v)$tmp .= ",$k:$v";
    		$output .= substr($tmp, 1) . '}';
    	}
        
    	return ('{' . substr($output, 1) . '}');
    }
}