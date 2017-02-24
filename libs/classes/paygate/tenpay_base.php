<?php
/**
 * 财付通即时到账网关模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
if ( !defined('TENPAY_PATH') )
{
    define('TENPAY_PATH', _08_OUTSIDE_PATH . 'tenpay' . DIRECTORY_SEPARATOR);
}
class _08_M_Tenpay_Base extends _08_M_PayGate_Base
{
    /**
     * SDK版本
     */
    const SDK_VERSION = 'v1.0.1';
    
    /**
     * 接口名称
     * 
     * @var string
     */
    protected $_poid = 'tenpay';
    
    /**
     * 设置在沙箱中运行，正式环境请设置为false
     * 
     * @var bool
     */
    private $isInSandBox = false;
    
    /**
     * 发送支付请求
     * 
     * @param   array  $params 请求参数
     * @example _08_factory::getPays('tenpay')->send(
                    array(
                        # 订单名称，必要
                        'subject' => 'test', 
                        # 支付金额，必要
                        'amount' => 10.05,
                        # 回调网址，必要
                        'callback' => '支付完成后，显示的页面网址',
                        # 联系人邮箱，可选
                        'email' => 'test@163.com',
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
        if ( empty($params['subject']) || empty($params['amount']) || empty($params['callback']) )
        {
            cls_message::show('参数错误。');
        }
        
        $params['ordersn'] = $this->getOrderSN();
        $params['poid'] = $this->_poid;
        $this->addPays($params);
        $this->setCallBack($params['callback']);
        
        if ( isset($params['to_mid']) )
        {
            $this->setConfigs($params['to_mid']);
        }
        
        _08_Loader::import(TENPAY_PATH . 'PayRequest.class');
                
        /* 创建支付请求对象 */
        $reqHandler = new PayRequest($this->_configs['key']);
        
        // 设置在沙箱中运行，正式环境请设置为false
        $reqHandler->setInSandBox($this->isInSandBox());
        
        // 设置财付通appid: 财付通app注册时，由财付通分配
        $reqHandler->setAppid($this->_configs['appid']);
        
        // 设置商户系统订单号：财付通APP系统内部的订单号,32个字符内、可包含字母,确保在财付通APP系统唯一
        $reqHandler->setParameter("out_trade_no", $params['ordersn']);  
        
        // 设置订单总金额，单位为分
        $reqHandler->setParameter("total_fee", doubleval($params['amount']) * 100);
        
        // 设置通知url：接收财付通后台通知的URL，用户在财付通完成支付后，财付通会回调此URL，向财付通APP反馈支付结果。
        // 此URL可能会被多次回调，请正确处理，避免业务逻辑被多次触发。需给绝对路径，例如：http://wap.isv.com/notify.asp
        $reqHandler->setParameter("notify_url", $this->_configs['notify_url']);				
        
        // 设置返回url：用户完成支付后跳转的URL，财付通APP应在此页面上给出提示信息，引导用户完成支付后的操作。
        // 财付通APP不应在此页面内做发货等业务操作，避免用户反复刷新页面导致多次触发业务逻辑造成不必要的损失。
        // 需给绝对路径，例如：http://wap.isv.com/after_pay.asp，通过该路径直接将支付结果以Get的方式返回
        $reqHandler->setParameter("return_url", $this->_configs['return_url'] . _08_Http_Request::uri2MVC("out_trade_no={$params['ordersn']}", false) . '/');
        
        // 设置商品名称:商品描述，会显示在财付通支付页面上
        $reqHandler->setParameter("body", $params['subject']);	            
        
        // 设置用户客户端ip:用户IP，指用户浏览器端IP，不是财付通APP服务器IP
        $reqHandler->setParameter("spbill_create_ip", cls_env::OnlineIP());
        // **********************end*************************
        
        //支付请求的URL
        $reqUrl = $reqHandler->getURL();
        #exit(urldecode($reqUrl));
        @ob_end_clean();
        header('Location:' . $reqUrl);
        exit;
    }
    
    /**
     * 设置配置参数
     * 注：在使用多个号收款时必须要保证同时传递本函数两个参数，否则钱有可能会打入到系统管理号里
     * 
     * @param string $appid 设置财付通App-id: 财付通App注册时，由财付通分配，如果传递则以传递为标准，不传递时默认使用系统配置
     * @param string $key   签名密钥: 开发者注册时，由财付通分配，不传递时默认使用系统配置
     */
    public function setConfigs( $mid = 0 )
    {
        $memberInfo = cls_UserMain::CurUser()->getPaysInfo($mid, 'tenpay');
        $this->_configs = array();
            
        //设置财付通App-id: 财付通App注册时，由财付通分配
        $this->_configs['appid'] = $memberInfo['tenpay_seller_account'];
        
        //签名密钥: 开发者注册时，由财付通分配
        $this->_configs['key'] = $memberInfo['tenpay_partnerkey'];
        
        // 设置通知url：接收财付通后台通知的URL，用户在财付通完成支付后，财付通会回调此URL，向财付通APP反馈支付结果。
        // 此URL可能会被多次回调，请正确处理，避免业务逻辑被多次触发。需给绝对路径，例如：http://wap.isv.com/notify.asp
        $this->_configs['notify_url'] = cls_url::create('paygate/tenpay_notify_url');
        
        // 设置返回url：用户完成支付后跳转的URL，财付通APP应在此页面上给出提示信息，引导用户完成支付后的操作。
        // 财付通APP不应在此页面内做发货等业务操作，避免用户反复刷新页面导致多次触发业务逻辑造成不必要的损失。
        // 需给绝对路径，例如：http://wap.isv.com/after_pay.asp，通过该路径直接将支付结果以Get的方式返回
        $this->_configs['return_url'] = cls_url::create('paygate/tenpay_return_url');
    }
    
    /**
     * 是否沙箱状态
     * 
     * @return bool TRUE为是，FALSE为否
     */
    public function isInSandBox()
    {
        return (bool) $this->isInSandBox;
    }
}