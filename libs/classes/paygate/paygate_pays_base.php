<?php
/**
 * 支付模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_PayGate_Pays_Base extends _08_Models_Base
{
    protected $_pays = null;
    
    /**
     * 添加一条支付记录
     * 
     * @param array $recordInfo 记录信息
     */
    public function add( array $recordInfo )
    {
        $recordInfo = $this->_filterFields($recordInfo);
        
        # 把超级管理员ID也视为系统帐号处理。
        if ( isset($recordInfo['to_mid']) && ($recordInfo['to_mid'] == 1) )
        {
            $recordInfo['to_mid'] = 0;
        }
        return $this->_pays->create($recordInfo);
    }
    
    /**
     * 过滤不存在本表的字段
     * 
     * @return array 返回过滤后的字段值
     */
    protected function _filterFields( array $fields )
    {
        $newFileds = array();
        foreach ( $fields as $field => $value ) 
        {
            if ( in_array($field, array('mid', 'pmode', 'senddate', 'receivedate', 'transdate', 'to_mid', 'status')) )
            {
                $newFileds[$field] = intval($value);
            }
            else if ( in_array($field, array('mname', 'ordersn', 'poid', 'ip', 'truename', 'telephone', 'email',
                                             'remark', 'warrant')) )
            {
                $newFileds[$field] = trim($value);
            }
            else if ( in_array($field, array('amount', 'handfee')) )
            {
                $newFileds[$field] = doubleval($value);
            }
        }
        
        return $newFileds;
    }
    
    /**
     * 读取一行支付信息
     * 
     * @param  string $fields 要读取的字段
     * @param  array  $where  筛选条件
     * @return array          返回读取到的支付信息
     */
    public function read($fields = '*', $where = array())
    {
        if ( $where )
        {
            $this->_pays->where($where);
        }
        
        return $this->_pays->read($fields);
    }
    
    /**
     * 更新订单状态
     * 
     * @param  int  $statusCode 状态码
     * @return bool             如果更新成功返回TRUE，否则返回FALSE
     */
    public function setStatus($statusCode, $where = array())
    {
        if ( $where )
        {
            $this->_pays->where($where);
        }
        
        return (bool) $this->_pays->update(array('status' => (int) $statusCode, 'receivedate' => TIMESTAMP, 'receivedate' => TIMESTAMP));  
    }
    
    /**
     * 获取现有的支付网关接口信息
     * 
     * @return array 返回现有的支付网关接口信息
     */
    public function getPays()
    {
		$pays = $payarr = array();
		for($i = 0; $i < 32; $i++)if(@$this->_mconfigs['cfg_paymode'] & (1 << $i))$payarr[] = $i;
        if ( _08_Browser::getInstance()->isMobile() && in_array(5, $payarr) )
        {
            $pays = array('alipay_direct_wap' => '支付宝手机即时到账');
        }
        else
        {
            foreach ( $payarr as $value ) 
            {
                if ( $value == 2 )
                {
                    $pays['alipay_direct'] = '支付宝即时到账';
                }
                elseif ( $value == 3 )
                {
                    $pays['tenpay'] = '财付通即时到账';
                }                
                elseif ( $value == 4 )
                {
                    $pays['alipay_direct_bankpay'] = '支付宝网银支付';
                }
            }
            
            if (defined('M_ADMIN'))
            {
                $pays['alipay_direct_wap'] = '支付宝手机即时到账';
            }
        }
        
        return $pays;
    }
    
    public function __construct($model = '')
    {
        parent::__construct();
        $this->_pays = parent::getModels(empty($model) ? 'Pays_Table' : $model);
    }
}