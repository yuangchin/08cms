<?php
/**
 * 微信数据库模型类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_DataBase extends _08_Models_Base
{
    /**
     * 数据库对象
     * 
     * @var   object
     * @since nv50
     */
    protected $_Weixin_Config_Table = null; 
    
    /**
     * 获取微信配置
     * 
     * @param  string $id_type ID类型
     * @param  int    $id      ID
     * @return array           返回配置数组
     * 
     * @since  nv50
     */
    public function getConfig( $id_type, $id )
    {
        $id = (int) $id;
        $id_type = preg_replace('/[^\w]/', '', $id_type);
        $row = $this->_Weixin_Config_Table->where(array('weixin_fromid_type' => $id_type))
                                          ->_and(array('weixin_fromid' => $id))
                                          ->read();
        return $row;
    }
    
    /**
     * 保存微信配置
     * 
     * @param  array $configs 要保存的配置数组
     * @return bool           保存成功返回TRUE，否则返回FALSE
     * 
     * @since  nv50
     */
    public function saveConfig( array $configs )
    {
        if ( empty($configs['weixin_fromid_type']) || empty($configs['weixin_fromid']) )
        {
            cls_message::show('参数非法。', M_REFERER);
        }
        empty($configs['weixin_qrcode']) || ($configs['weixin_qrcode'] = cls_url::save_url($configs['weixin_qrcode']));
        $row = $this->getConfig($configs['weixin_fromid_type'], $configs['weixin_fromid']);
        $Weixin_Config_Table = $this->_Weixin_Config_Table;
        
        if ( empty($row) )
        {
            $updateStatus = $Weixin_Config_Table->create($configs);
        }
        else
        {
            $id_type = $configs['weixin_fromid_type'];
            $id = $configs['weixin_fromid'];
            unset($configs['weixin_fromid_type'], $configs['weixin_fromid']);
            $updateStatus = $Weixin_Config_Table->where(array('weixin_fromid_type' => $id_type))
                                                ->_and(array('weixin_fromid' => $id))
                                                ->update($configs);
        }
        
        return ((bool)$updateStatus ? true : false);
    }
    
    public function getNextID($id_type, $id)
    {
       $row = $this->_Weixin_Config_Table->where(array('weixin_fromid_type' => $id_type))
                                          ->_and(array('weixin_fromid' => $id), '>')
                                          ->order('weixin_fromid ASC')
                                          ->read('weixin_fromid');
        
        return $row['weixin_fromid'];
    }
    
    /**
     * 获取缓存配置
     * 
     * @param  string $cache_id 要获取配置的缓存ID
     * @param  string $limit    配置偏移量
     * @return array            返回配置信息
     * 
     * @since  nv50
     */
    public function getCacheConfig( $cache_id, $limit = '' )
    {
        $this->_Weixin_Config_Table->where(array('weixin_cache_id' => $cache_id))->order('weixin_fromid ASC');
        if ( empty($limit) || $limit == 1 )
        {
            return $this->_Weixin_Config_Table->read();
        }
        else
        {
            @list($limit, $offset) = array_map('trim', explode(',', $limit));
        	$this->_Weixin_Config_Table->limit($limit, $offset)->exec();
        }
        
        $rows = array();
        while($row = $this->_Weixin_Config_Table->fetch())
        {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function __construct()
    {
        $this->_Weixin_Config_Table = parent::getModels('Weixin_Config_Table');
    }
}