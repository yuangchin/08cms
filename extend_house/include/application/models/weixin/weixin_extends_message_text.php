<?php
/**
 * 微信消息管理扩展模型
 * 注：该文件在升级核心时请不要替换
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Extends_Message_Text extends _08_M_Weixin_Message
{
    ### 演示站数据 ###
    # 楼盘推送位ID
//    const PROPERTY_PAID = 301;
//    
//    # 二手房推送位ID
//    const SECOND_HAND_HOUSING_PAID = 302;
//    
//    # 出租房推送位ID
//    const RENTING_PAID = 303;
    
    ### 开发版数据 ###
    # 楼盘推送位ID
    const PROPERTY_PAID = 'push_130';
    
    # 二手房推送位ID
    const SECOND_HAND_HOUSING_PAID = 'push_131';
    
    # 出租房推送位ID
    const RENTING_PAID = 'push_132';
    
    /**
     * 按区域响应楼盘数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText10( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::REGION_COID;
        $chid = _08_M_Weixin_Extends_Message::PROPERTY_CHID;
        $rows = $this->getTextDatas(self::PROPERTY_PAID);
        $this->getPropertyDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按物业类型响应楼盘数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText11( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::PROPERTY_COID;
        $chid = _08_M_Weixin_Extends_Message::PROPERTY_CHID;        
        $rows = $this->getTextDatas(self::PROPERTY_PAID);
        $this->getPropertyDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }    
    
    /**
     * 按价格响应楼盘数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText12( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::PROPERTY_PRICE_COID;
        $chid = _08_M_Weixin_Extends_Message::PROPERTY_CHID;    
        $rows = $this->getTextDatas(self::PROPERTY_PAID);
        $this->getPropertyDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }    
    
    /**
     * 按周边响应楼盘数据
     * 
     * @param  string $where SQL查询条件
     * @return string        返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText13( $where )
    {
        if ( empty($where) )
        {
            return false;
        }
        
        $ccid = $coid = 0;
        $chid = _08_M_Weixin_Extends_Message::PROPERTY_CHID;    
        $rows = array();
        $this->getPropertyDatas($chid, $coid, $ccid, $rows, $where);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 获取楼盘展示数据内容
     * 
     * @param int    $chid  文档模型ID
     * @param int    $coid  类系分类ID
     * @param int    $ccid  类系ID
     * @param string $where SQL查询条件
     */
    public function getPropertyDatas( $chid, $coid, $ccid, &$rows, $where = '' )
    {
        $chid = (int) $chid;
        $coid = (int) $coid;
        $ccid = (int) $ccid;
        $coid && ($coclasses = cls_cache::Read('coclasses', $coid));
        $archiveTable = parent::getModels('Archives_Table', atbl($chid) . ' AS a');
        $archiveTable->select('a.aid, a.subject, a.jumpurl, a.caid, a.chid, a.initdate, a.createdate, a.customurl, a.abstract, a.thumb, b.tel, a.dj',true)
                     ->innerJoin("#__archives_$chid AS b")->_on('a.aid = b.aid');
        # 判断所谓的自动类系
        if ( isset($coclasses[$ccid]['conditions']['sqlstr']) )
        {
            $coclassesSQL = str_replace(array('{$pre}', "\'", '\"'), array('a.', '', ''), $coclasses[$ccid]['conditions']['sqlstr']);
            $archiveTable->where($coclassesSQL);
        }
        else if ($where)
        {
            $archiveTable->where($where);
        }
        else
        {
        	$archiveTable->where("FIND_IN_SET($ccid, a.ccid{$coid})");
        }
        
        if ( empty($rows) )
        {
            $limit = 10;
        }
        else
        {
        	$limit = 9;
        }
           
        $archiveTable->_and('b.leixing')->_in('0, 1')->_and(array('a.checked' => 1))
                     ->order('a.aid DESC')
                     ->limit($limit)#->setDebug()
                     ->exec();                     
        while($row = $archiveTable->fetch())
        {
            $row['nodemode'] = true;
            cls_url::view_arcurl($row);
            $imgurl = self::getImgURL($row['thumb']);
            if ( empty($row['tel']) )
            {
                $row['tel'] = '暂无';
            }
            $rows[$row['aid']] = array(
                'Title' => $row['subject'] . " ( {$row['dj']}元/平方 )\n\n电话：{$row['tel']}", 
                'Description' => $row['abstract'], 
                'PicUrl' => $imgurl, 
                'Url' => ($row['arcurl'] . '&is_weixin=1')
            );
        }
    }    
    
    /**
     * 按区域响应二手房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText20( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::REGION_COID;
        $chid = _08_M_Weixin_Extends_Message::SECOND_HAND_HOUSING_CHID;    
        $rows = $this->getTextDatas(self::SECOND_HAND_HOUSING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按价格响应二手房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText21( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::SECOND_HAND_HOUSING_PRICE_COID;
        $chid = _08_M_Weixin_Extends_Message::SECOND_HAND_HOUSING_CHID;  
        $rows = $this->getTextDatas(self::SECOND_HAND_HOUSING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按面积响应二手房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText22( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::SECOND_HAND_HOUSING_AREA_COID;
        $chid = _08_M_Weixin_Extends_Message::SECOND_HAND_HOUSING_CHID;
        $rows = $this->getTextDatas(self::SECOND_HAND_HOUSING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按周边响应出租房数据
     * 
     * @param  string $where SQL查询条件
     * @return string        返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText23( $where )
    {
        if ( empty($where) )
        {
            return false;
        }
        
        $ccid = $coid = 0;
        $chid = _08_M_Weixin_Extends_Message::RENTING_CHID;    
        $rows = array();
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows, $where);
        return $this->_ReplyNews( $rows );
    }  
    
    /**
     * 获取二手房/出租房展示数据内容
     * 
     * @param  int    $chid  文档模型ID
     * @param  int    $coid  类系分类ID
     * @param  int    $ccid  类系ID
     * @param  string $where SQL查询条件
     */
    public function getSecondHandHousingDatas( $chid, $coid, $ccid, &$rows, $where = '' )
    {
        $chid = (int) $chid;
        $coid = (int) $coid;
        $ccid = (int) $ccid;
        $coid && ($coclasses = cls_cache::Read('coclasses', $coid));
        $archiveTable = parent::getModels('Archives_Table', atbl($chid) . ' AS a');
        $archiveTable->select('a.aid, a.subject, a.jumpurl, a.caid, a.chid, a.initdate, a.createdate, a.customurl, a.abstract, a.thumb, a.shi, a.ting, a.wei, a.chu, a.yangtai, a.zj, a.mj',true)
                     ->innerJoin("#__archives_$chid AS b")->_on('a.aid = b.aid');
        # 判断所谓的自动类系
        if ( isset($coclasses[$ccid]['conditions']['sqlstr']) )
        {
            $coclassesSQL = str_replace(array('{$pre}', "\'", '\"'), array('a.', '', ''), $coclasses[$ccid]['conditions']['sqlstr']);
            $archiveTable->where($coclassesSQL);
        }
        else if ($where)
        {
            $archiveTable->where($where);
        }
        else
        {
        	$archiveTable->where(array("a.ccid{$coid}" => $ccid));
        }
        
        if ( empty($rows) )
        {
            $limit = 10;
        }
        else
        {
        	$limit = 9;
        }
        
        $archiveTable->_and(array('a.checked' => 1))
                     ->order('a.aid DESC')
                     ->limit($limit)#->setDebug()
                     ->exec();                     
        while($row = $archiveTable->fetch())
        {
            $string = '';
            if ( !empty($row['shi']) )
            {
                $string .= ($row['shi'] . '室');
            }
            if ( !empty($row['ting']) )
            {
                $string .= ($row['ting'] . '厅');
            }
            if ( !empty($row['wei']) )
            {
                $string .= ($row['wei'] . '卫');
            }
            if ( !empty($row['chu']) )
            {
                $string .= ($row['chu'] . '厨');
            }
            if ( !empty($row['yangtai']) )
            {
                $string .= ($row['yangtai'] . '阳台');
            }
            $row['mj'] = (int) $row['mj'];
            $row['nodemode'] = true;
            cls_url::view_arcurl($row);
            $imgurl = self::getImgURL($row['thumb']);
            if ( empty($row['tel']) )
            {
                $row['tel'] = '暂无';
            }
            
            if ( $chid == 2 )
            {
                $unit = '元';
            }
            else
            {
            	$unit = '万元';
            }
            
            $rows[$row['aid']] = array(
                'Title' => $row['subject'] . "\n\n$string / {$row['mj']}平方米 / {$row['zj']}{$unit}", 
                'Description' => $row['abstract'], 
                'PicUrl' => $imgurl, 
                'Url' => ($row['arcurl'] . '&is_weixin=1')
            );
        }
    }
    
    /**
     * 按区域响应出租房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText30( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::REGION_COID;
        $chid = _08_M_Weixin_Extends_Message::RENTING_CHID;
        $rows = $this->getTextDatas(self::RENTING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按价格响应出租房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText31( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::RENTING_PRICE_COID;
        $chid = _08_M_Weixin_Extends_Message::RENTING_CHID;
        $rows = $this->getTextDatas(self::RENTING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按面积响应出租房数据
     * 
     * @param  int    $ccid    类系ID
     * @return string $message 返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText32( $ccid )
    {
        $coid = _08_M_Weixin_Extends_Event_Click::SECOND_HAND_HOUSING_AREA_COID;
        $chid = _08_M_Weixin_Extends_Message::RENTING_CHID;
        $rows = $this->getTextDatas(self::RENTING_PAID);
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows);
        return $this->_ReplyNews( $rows );
    }
    
    /**
     * 按周边响应出租房数据
     * 
     * @param  string $where SQL查询条件
     * @return string        返回要回复的XML格式内容
     * 
     * @since  4.2+
     */
    public function responseText33( $where )
    {
        if ( empty($where) )
        {
            return false;
        }
        
        $ccid = $coid = 0;
        $chid = _08_M_Weixin_Extends_Message::RENTING_CHID;    
        $rows = array();
        $this->getSecondHandHousingDatas($chid, $coid, $ccid, $rows, $where);
        return $this->_ReplyNews( $rows );
    }  
    
    /**
     * 获取响应的文本数据
     * 
     * @param  int   $paid  推送位ID
     * 
     * @return array        返回获取到的文本数据
     * @since  4.2+
     */
    public function getTextDatas( $paid )
    {
        
        $rows = array();   
        $Push_Table = parent::getModels('Push_Table', $paid);        
        $push = $Push_Table->where(array('checked' => 1))->order('pushid DESC')->read('subject, thumb, url, fromid');
        if ( !empty($push) )
        {
            # 获取该aid所在的模型表ID
            $archiveSubTable = parent::getModels('Archives_Table', '_sub');
            $chidRow = $archiveSubTable->where(array('aid' => $push['fromid']))->read('chid', false);
            
            # 从获取到的模型表ID里查询数据
            $archiveTable_3 = parent::getModels('Archives_Table', atbl($chidRow['chid']));
            $abstractRow = $archiveTable_3->read('abstract');     
            # 组合推送信息
            $rows[0] = array(
                'Title' => $push['subject'], 
                'Description' => $abstractRow['abstract'], 
                'PicUrl' => self::getImgURL($push['thumb']), 
                'Url' => (_08_CMS_ABS . str_replace(_08_CMS_ABS, '', $push['url']))
            );
        }
        
        return $rows;
    }
    
    private static function getImgURL( $imgPath )
    {
        if ( $imgPath )
        {	
            $oldarr = explode('#', $imgPath);
            $imgurl = cls_url::tag2atm($oldarr[0]);
        }
        else
        {
        	$imgurl = '';
        }
        
        return $imgurl;
    }
}