<?php
/**
 * 获取经纪人会员空间的按小区分类下的二手房/出租信息。
 *
 * @example   请求范例URL：index.php?/ajax/get_house_for_area/mid/6/mchid/2/chid/3/mcaid/1
 * @author    lyq <692378514@qq.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_Get_House_For_Area extends _08_Models_Base
{
    public function __toString()
    {
		$mid     = empty($this->_get['mid'])   ? 0 : max(1,intval($this->_get['mid']));
		$mchid   = empty($this->_get['mchid']) ? 0 : max(1,intval($this->_get['mchid']));
		$chid    = empty($this->_get['chid'])  ? 0 : max(1,intval($this->_get['chid']));
		$mcaid   = empty($this->_get['mcaid']) ? 0 : max(1,intval($this->_get['mcaid']));
		$page    = empty($this->_get['page'])  ? 1 : max(1,intval($this->_get['page']));
		$db      = $this->_db;		
		$timestamp = TIMESTAMP; 
		
		$mconfigs = cls_cache::Read('mconfigs');
		$cms_abs = $mconfigs['cms_abs'];
		$mspacedir = $mconfigs['mspacedir'];
		
		
		# 获取经纪公司所有经纪人ID
		if($mchid == 3)
		{
			$db->select('m.mid')
			   ->from('#__members m')
			   ->innerJoin('#__members_2 d')->_on('m.mid=d.mid')
			   ->where(array('pid4'=>$mid))->_and(array('m.incheck4'=>1))			   
			   ->exec();
			   
			$mids = array();
			while($row = $db->fetch())
			{
				$mids[] = $row['mid'];
			}
			if ( !empty($mids) )
			{
				$mids = ($mid . ',' . implode(',', $mids));
			}
			else
			{
				$mids = $mid;
			}
		}
		else
		{
			$mids = $mid;
		}
		$offset = 18;
		# 查询数据分页最大页数
		$db->select('pid3, lpmc')
		   ->from('#__' . atbl($chid))
		   ->where('mid')->_in($mids)
		   ->_and("(enddate=0 OR enddate>$timestamp)")
		   ->_and("checked=1")		  
		   ->exec();
		$rows = array();
		while($row = $db->fetch())
		{
			$rows[$row['lpmc']] = $row;
			if ( isset($counts[$row['lpmc']]) )
			{
				$counts[$row['lpmc']]++;
			}
			else
			{
				$counts[$row['lpmc']] = 1;
			}
		}
		
		$page_num = ceil(count($rows) / $offset);
		
		# 如果当前页面大于数据最大页数则设置当前页面与数据最大页同步
		$page > $page_num && $page = $page_num;
		if ( in_array($page, array(0, 1)) )
		{
			$limit = 0;
		}
		else
		{
			$limit = ($page - 1) * $offset;
		}
		
		# 判断上一页页码
		if ( $page - 1 > 0 )
		{
			$prevPage = $page - 1;
			$prevPageStr = '<a style="cursor: pointer;" onclick="showHouseList'.$chid.'('.$prevPage.')"  class="page_prev"><<上一页</a>';
		}
		else
		{
			$prevPage = 0;
			$prevPageStr = '<a style="color:#ccc; text-decoration: none;" class="page_prev page_prev-dis"><<上一页</a>';
		}
		
		if ( $page + 1 > $page_num )
		{
			$nextPage = $page_num;
			$nextPageStr = '<a style="color:#ccc; text-decoration: none;" class="page_next page_next-dis">下一页>></a>';
		}
		else
		{
			$nextPage = $page + 1;
			$nextPageStr = '<a style="cursor: pointer;" onclick="showHouseList'.$chid.'('.$nextPage.')"  class="page_next">下一页>></a>';
		}
		      
		$page_string = "<div class='page1'>{$prevPageStr}&nbsp;{$page}/{$page_num}&nbsp;{$nextPageStr}</div>";
		$string = '';
		$baseurl = "{$cms_abs}{$mspacedir}/index.php"; //伪静态,需要这个参数
		
		foreach(cls_Array::limit($rows, $limit, $offset) as $key => $value)
		{
			$title = cls_string::CutStr($value['lpmc'], 13);
			$string .= <<<EOT
				<a href="{$baseurl}?mcaid={$mcaid}&addno=1&mid={$mid}&extra=area:{$value['pid3']}" class="STYLE5">{$title}({$counts[$value['lpmc']]})</a><br />
EOT;
		}
		exit('<div class="xq-list">' . $string . '</div>' . ($page_num > 1 ? $page_string : ''));
		   
	}
}