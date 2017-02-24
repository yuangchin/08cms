<?php
 /**
 * 前台首页，获取开盘日历所需信息
 *
 * @example   请求范例URL：index.php?/ajax/calendar/
 * @author    lyq <692378514@qq.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Calendar extends _08_Models_Base
{
    public function __toString()
    {
		$mcharset = $this->_mcharset;
        $chid = 4;
        $dateStr = empty($this->_get['pDate']) ? '' : trim($this->_get['pDate']);
		$db = $this->_db;
        $tblprefix = cls_env::getBaseIncConfigs('tblprefix');
        $coclasses1 = cls_cache::Read('coclasses',1);

        $dateNum = strtotime($dateStr);
        $year = date("Y",$dateNum);
        $month = date("n",$dateNum);

        $timeFrom =   mktime(0,0,0,$month,1,$year);
        $timeTo =   mktime(0,0,0,$month+1,1,$year);
        $query = $db->query(" SELECT a.aid,a.subject,a.chid,a.caid,a.createdate,a.initdate,a.customurl,a.nowurl,a.ccid1,c.kpsj,c.kprq FROM {$tblprefix}".atbl($chid)." a INNER JOIN {$tblprefix}archives_$chid c ON a.aid=c.aid WHERE c.kpsj >= $timeFrom AND c.kpsj < $timeTo ORDER BY c.kpsj ASC");
        $data = array();
        while($row = $db->fetch_array($query)){
      		$_url = $row;
    		$_url = cls_url::view_arcurl($_url);
    		$content = array('url'=>$_url,'title'=>"[".$coclasses1[$row['ccid1']]['title']."]".$row['subject']);
    		$data[]=array(
    			'url' => $_url,
                'year' => $year,
                'month'=> $month,
    			'ccid1' => $coclasses1[$row['ccid1']]['title'],
    			'subject' => $row['subject'],
    			'kprq' => empty($row['kprq'])?date("Y年n月d日",$row['kpsj']):$row['kprq']
    		);
        }

            $data = cls_string::iconv($mcharset, "UTF-8", $data);
            echo 'var data = ' . json_encode($data) . ';';

    }
}
