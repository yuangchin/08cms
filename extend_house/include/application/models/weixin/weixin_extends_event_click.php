<?php
/**
 * 微信点击按钮事件响应管理扩展模型
 * 注：该文件在升级核心时请不要替换，该类的所有方法应该是您创建菜单时类型为：click 的 key 值的小写
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Extends_Event_Click extends _08_M_Weixin_Message
{    
    const REGION_COID = 1;
    
    const PROPERTY_COID = 12;
    
    const PROPERTY_PRICE_COID = 17;
    
    const SECOND_HAND_HOUSING_PRICE_COID = 4;
    
    const SECOND_HAND_HOUSING_AREA_COID = 6;
    
    const RENTING_PRICE_COID = 5;
    
    protected $_string = "请回复中括号内的数字以选择：\n\n";
    
    /**
     * 按区域响应楼盘数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function property_region()
    {
        return $this->_ReplyText( $this->_getCoclasses('10', self::REGION_COID) );
    }
    
    /**
     * 按物业响应楼盘数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function property_property()
    {
        return $this->_ReplyText( $this->_getCoclasses('11', self::PROPERTY_COID) );
    }
    
    /**
     * 按价格响应楼盘数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function property_price()
    {
        return $this->_ReplyText( $this->_getCoclasses('12', self::PROPERTY_PRICE_COID) );
    }
    
    /**
     * 按周边响应楼盘数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function property_periphery()
    {
        return $this->_responsePeriphery(13);
    }
    
    /**
     * 按区域响应二手房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function second_hand_housing_region()
    {
        return $this->_ReplyText( $this->_getCoclasses('20', self::REGION_COID) );
    }
    
    /**
     * 按价格响应二手房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function second_hand_housing_price()
    {
        return $this->_ReplyText( $this->_getCoclasses('21', self::SECOND_HAND_HOUSING_PRICE_COID) );
    }
    
    /**
     * 按面积响应二手房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function second_hand_housing_area()
    {
        return $this->_ReplyText( $this->_getCoclasses('22', self::SECOND_HAND_HOUSING_AREA_COID) );
    }
    
    /**
     * 按周边响应二手房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function second_hand_housing_periphery()
    {
        return $this->_responsePeriphery(23);
    }
    
    /**
     * 按区域响应出租房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function renting_region()
    {
        return $this->_ReplyText( $this->_getCoclasses('30', self::REGION_COID) );
    }
    
    /**
     * 按价格响应出租房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function renting_price()
    {
        return $this->_ReplyText( $this->_getCoclasses('31', self::RENTING_PRICE_COID) );
    }
    
    /**
     * 按面积响应出租房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function renting_area()
    {
        return $this->_ReplyText( $this->_getCoclasses('32', self::SECOND_HAND_HOUSING_AREA_COID) );
    }
    
    /**
     * 按周边响应出租房数据
     * 
     * @return string $message 返回要回复的XML格式内容
     * @since  4.2+
     */
    public function renting_periphery()
    {
        return $this->_responsePeriphery(33);
    }
    
    /**
     * 获取类系数据
     * 
     * @param  string $prefixes 数据标识前缀
     * @return string           返回组装后的区域数据
     * @since  4.2+
     */
    protected function _getCoclasses( $prefixes, $coid )
    {
        $string = $this->_string;
        $coclasses = cls_cache::Read('coclasses' . $coid);
        foreach ( $coclasses as $key => $coclass ) 
        {
            $string .= "[ {$prefixes}{$key} ] {$coclass['title']}\n";
        }
        
        return $string;
    }
    
    /**
     * 响应周边数据
     * 
     * @param  string $prefixes 数据标识前缀
     */
    protected function _responsePeriphery( $prefixes )
    {   
        if ( ($this->_post instanceof SimpleXMLElement) && isset($this->_post->FromUserName) )
        {
            $configs = _08_M_Weixin_Base::getConfigs( $this->_post );
            $Weixin_Extends_Message_Text = parent::getModels('Weixin_Extends_Message_Text', $this->_post);
            
            if ( isset($configs['Longitude']) && isset($configs['Latitude']) )
            {
                $diff = (isset($this->_mconfigs['weixin_circum_km']) ? floatval($this->_mconfigs['weixin_circum_km']) : 1);
                $where = cls_dbother::MapSql($configs['Latitude'], $configs['Longitude'], $diff, 1, 'a.dt');
                $function = 'responseText' . $prefixes;
                return $Weixin_Extends_Message_Text->$function($where);
            }
            else
            {
            	return $Weixin_Extends_Message_Text->_ReplyText('暂无相关数据。');
            }
        }
        
        return false;        
    }
}


