<?php
/**
 * 支付宝手机即时到账网关模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
define('ALIPAY_DIRECT_PATH', _08_OUTSIDE_PATH . 'alipay_direct_wap' . DIRECTORY_SEPARATOR);
_08_Loader::import(ALIPAY_DIRECT_PATH . 'lib:alipay_submit.class');
class _08_M_Alipay_Direct_Wap_Base extends _08_M_Alipay_Direct_Base
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
    protected $_poid = 'alipay_direct_wap';
    
    /**
     * 发送支付请求
     * 
     * @param   array  $params 请求参数
     * @example _08_factory::getPays('alipay_direct_wap')->send(
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
        
        if ( isset($params['to_mid']) )
        {
            $this->setConfigs($params['to_mid']);
        }
        
        $input_charset = trim(strtolower($this->_configs['input_charset']));
        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . cls_url::create('paygate/alipay_direct_wap_notify_url') . '</notify_url><call_back_url>' . cls_url::create('paygate/alipay_direct_wap_return_url') . '</call_back_url><seller_account_name>' . $this->_configs['seller_email'] . '</seller_account_name><out_trade_no>' . $params['ordersn'] . '</out_trade_no><subject>' . $params['subject'] . '</subject><total_fee>' . doubleval($params['amount']) . '</total_fee><merchant_url>' . $params['callback'] . '</merchant_url></direct_trade_create_req>';
        
        //构造要请求的参数数组，无需改动
        $para_token = array(
        		"service" => "alipay.wap.trade.create.direct",
        		"partner" => trim($this->_configs['partner']),
        		"sec_id" => trim($this->_configs['sign_type']),
        		"format"	=> 'xml',
        		"v"	=> '2.0',
        		"req_id"	=> $params['ordersn'],
        		"req_data"	=> $req_data,
        		"_input_charset"	=> $input_charset
        );
        $para_token = cls_string::iconv($input_charset, 'UTF-8', $para_token);
        //建立请求
        $alipaySubmit = new AlipaySubmit($this->_configs);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);
        #$html_text = mhtmlspecialchars($html_text);
        
        //URLDECODE返回的信息
        $html_text = cls_string::iconv('UTF-8', $input_charset, urldecode($html_text));
        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);
        if ( !empty($para_html_text['res_error']) )
        {
            exit($para_html_text['res_error']);
        }
        
        //获取request_token
        $request_token = $para_html_text['request_token'];
        
        
        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
        
        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填
        
        //构造要请求的参数数组，无需改动
        $parameter = array(
        		"service" => "alipay.wap.auth.authAndExecute",
        		"partner" => trim($this->_configs['partner']),
        		"sec_id" => trim($this->_configs['sign_type']),
        		"format"	=> 'xml',
        		"v"	=> '2.0',
        		"req_id"	=> $params['ordersn'],
        		"req_data"	=> $req_data,
        		"_input_charset"	=> $input_charset
        );
        
        //建立请求
        $alipaySubmit = new AlipaySubmit($this->_configs);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "正跳转到支付页面，如果长时间不跳转请点击我跳转......");
        @ob_end_clean(); 
        #$html_text = mhtmlspecialchars($html_text); 
        exit($html_text);
    }
    
    /**
     * 设置配置参数
     * 注：在使用多个号收款时必须要保证同时传递本函数两个参数，否则钱有可能会打入到系统管理号里
     * 
     * @param int $mid 商家会员ID
     */
    public function setConfigs($mid = 1)
    {
        parent::setConfigs($mid);
            
        //商户的私钥（后缀是.pen）文件相对路径
        //如果签名方式设置为“0001”时，请设置该参数
        $this->_configs['private_key_path']	= 'key/rsa_private_key.pem';
        
        //支付宝公钥（后缀是.pen）文件相对路径
        //如果签名方式设置为“0001”时，请设置该参数
        $this->_configs['ali_public_key_path']= 'key/alipay_public_key.pem';
    }
}