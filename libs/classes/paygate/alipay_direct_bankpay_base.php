<?php
/**
 * 支付宝即时到账银行网关模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Alipay_Direct_BankPay_Base extends _08_M_Alipay_Direct_Base
{
    /**
     * SDK版本
     */
    const SDK_VERSION = 'v3.3';
    
    /**
     * 接口名称
     * 
     * @var string
     */
    protected $_poid = 'alipay_direct_bankpay';
    
    /**
     * 支付银行代号
     * 
     * @var string
     */
    protected $_defaultbank;
    
    /**
     * 发送支付请求
     * 
     * @param   array  $params 请求参数
     * @example _08_factory::getPays('alipay_direct_bankpay')->send(
                    array(
                        # 订单名称，必要
                        'subject' => 'test', 
                        # 支付金额，必要
                        'amount' => 10.05,
                        # 回调网址，必要
                        'callback' => '支付完成后，显示的页面网址',
                        # 联系人邮箱，可选
                        'email' => 'test@163.com',
                        # 支付银行代号，必要
                        'defaultbank' => '支付银行代号',
                        # 收款会员ID，可选，如果不传递则为系统帐号，如果传递则会把钱打入该会员对应的帐号上。
                        'to_mid' => 0,
                        # 订单描述，可选
                        'remark' => '订单描述',
                        # 手续费，可选
                        'handfee' => 0,
                        # 支付人名称，可选
                        'truename' => '支付人名称',
                        # 支付人电话，可选
                        'telephone' => '支付人电话'
                    )
                );
     */
    public function send( array $params )
    {
        if ( isset($params['defaultbank']) )
        {
            $params['defaultbank'] = preg_replace('/[^\w]/', '', $params['defaultbank']);
        }
        
        if ( empty($params['defaultbank']) )
        {
            cls_message::show('请先选择支付银行。');
        }
        
        $this->_defaultbank = $params['defaultbank'];
        parent::send($params);
    }
    
    /**
     * 设置参数
     * 
     * @param array $parameter 要设置的参数数组
     */
    public function setParameter( array &$parameter )
    {
        $parameter['paymethod']   = 'bankPay';
		$parameter['defaultbank'] = $this->_defaultbank;
    }
}