<?php
/**
 * 功能
 *
 * @example
 * @author    icms <icms@foxmail.com>
 * @copyright 2008 - 2014 08CMS, Inc. All rights reserved.
 *
 */

/**
 * // 房源-小区名称,选择
 * 参考核心exArc_list:文档添加时-选择所属合辑
 * 包含临时小区
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Ajax_ajaxbus_loupan extends _08_Models_Base
{
    public function __toString()
    {
        $mcharset = $this->_mcharset;
        $chid = $this->_get['chid'];
        $stid = $chid == 115 ? 68 : 69;
        $keywords = (empty($this->_get['keywords']) ? '' : cls_string::iconv("UTF-8", $mcharset, $this->_get['keywords']));
        $keywords = trim($keywords);
        $db = $this->_db;


        // 小区
        $result = array();

        //类系是否关联的情况下的sql部分
        $splitbls = cls_cache::Read('splitbls');
        //查找已经跟文档关联的类系
        $loupanCoidArr = $splitbls[$stid]['coids'];
        $selectStr = 'a.aid,a.subject';

        foreach(array(1,2,46,47,48,49) as $k){
            if(in_array($k,$loupanCoidArr)){
                $selectStr .= ',a.ccid'.$k;
            }
        }

        $selectStr .= ',a.dt,a.thumb,c.address ';
        $tableStr = '#__archives'.$stid.' a';

        $db->select($selectStr)->from( $tableStr )
            ->innerJoin("#__archives_{$chid} c")->_on('a.aid=c.aid')
            ->where("a.checked=1")
            ->_and('(a.subject')->like($keywords)
            ->_or(' c.address')->like($keywords);
        $db->setter('_sql', $db->getter('_sql') . ') ');
        $db->limit(100)->exec();
        while( $row=$db->fetch() )
        {
            $tmp = array(
                'aid' => $row['aid'],
                'subject'=>$row['subject'],
                'address'=>$row['address'],
                'dt'=>$row['dt'],
                'thumb'=>cls_url::view_atmurl(preg_replace('/#\d*/','',$row['thumb']))
            );

            foreach(array(1,2,46,47,48,49) as $k){
                if(isset($row["ccid$k"])){
                    $tmp["ccid$k"] = $row["ccid$k"];
                }
            }
            $result[] = $tmp;

        }
        //var_dump($result);die;

        return $result; 
    }
}