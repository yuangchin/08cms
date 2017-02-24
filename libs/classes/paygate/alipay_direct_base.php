<?php
/**
 * 支付宝即时到账网关模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
if ( !defined('ALIPAY_DIRECT_PATH') )
{
    define('ALIPAY_DIRECT_PATH', _08_OUTSIDE_PATH . 'alipay_direct' . DIRECTORY_SEPARATOR);
    _08_Loader::import(ALIPAY_DIRECT_PATH . 'lib:alipay_submit.class');
}
class _08_M_Alipay_Direct_Base extends _08_M_PayGate_Base
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
    protected $_poid = 'alipay_direct';
	
	protected $parameter = array();
	
	protected $extraParameter = '';
    
    /**
     * 发送支付请求
     * 
     * @param   array  $params 请求参数
     * @example _08_factory::getPays('alipay_direct')->send(
                    array(
						# 关联支付模型表 必要
						'model' => 'Pays_Table'  默认值是Pays_Table充值支付表 
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
            $memberInfo = cls_UserMain::CurUser()->getPaysInfo($params['to_mid']);
            if ( empty($memberInfo['alipay_partnerid']) || empty($memberInfo['alipay_seller_account']) || empty($memberInfo['alipay_partnerkey']) )
            {
                cls_message::show('商家支付宝参数配置错误！');
			}

            $this->setConfigs($params['to_mid']);
        }

        $alipaySubmit = new AlipaySubmit($this->_configs);
        //构造要请求的参数数组，无需改动
        $parameter = array(
    		"service" => "create_direct_pay_by_user",
            
    		"partner" => trim($this->_configs['partner']),
            
            //支付类型
    		"payment_type"	=> '1',
            
            //必填，不能修改
            //服务器异步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数，本系统MVC架构作了处理，是可以传递的
    		"notify_url"	=> cls_url::create('paygate/alipay_direct_notify_url'),
            
            //页面跳转同步通知页面路径，需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
    		"return_url"	=> cls_url::create('paygate/alipay_direct_return_url'),
             
            //卖家支付宝帐户
    		"seller_email"	=> $this->_configs['seller_email'],
              
            //商户订单号
    		"out_trade_no"	=> $params['ordersn'],
            
            //订单名称
    		"subject"	=> $params['subject'],
            
            //付款金额
    		"total_fee"	=> doubleval($params['amount']),
            
            //订单描述
    		"body"	=> empty($params['remark']) ? '' : (string) $params['remark'],
            
            //商品展示地址
    		"show_url"	=> $params['callback'],          
            
            //防钓鱼时间戳，若要使用请调用类文件submit中的query_timestamp函数
    		"anti_phishing_key"	=> '',  
            
            //客户端的IP地址    cls_env::OnlineIP()
    		"exter_invoke_ip"	=> '',
			
			//自定义参数
			"extra_common_param" => $this->extraParameter ? $this->extraParameter: '',
            
    		"_input_charset"	=> trim(strtolower($this->_configs['input_charset']))
        );

		$this->parameter = $this->parameter ? array_merge($this->parameter,$parameter) : $parameter;

        $this->setCallBack($params['callback']);
        @ob_end_clean();
        $html_text = $alipaySubmit->buildRequestForm($this->parameter, "get", "正跳转到支付页面，如果长时间不跳转请点击我跳转......");
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
        $memberInfo = cls_UserMain::CurUser()->getPaysInfo($mid);

        $this->_configs = array();
        //合作身份者id，以2088开头的16位纯数字
        $this->_configs['partner'] = $memberInfo['alipay_partnerid'];
        
        //安全检验码，以数字和字母组成的32位字符
        $this->_configs['key'] = $memberInfo['alipay_partnerkey'];
        
        //安全检验码，以数字和字母组成的32位字符
        $this->_configs['seller_email'] = $memberInfo['alipay_seller_account'];
        
        //签名方式 不需修改
    	$this->_configs['sign_type'] = 'MD5';
        
        //字符编码格式 目前支持 gbk 或 utf-8
        $this->_configs['input_charset'] = @$this->_mcharset;
        
        //ca证书路径地址，用于curl中ssl校验，注：暂时不考虑使用证书验证，因为会对用户带来麻烦
        $this->_configs['cacert'] = ALIPAY_DIRECT_PATH . 'cacert.pem';
        
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $this->_configs['transport'] = substr(_08_Browser::getInstance()->getHttpConnection(), 0, -3);
    }
	
	/**
     * 设置配置参数
     * 注意 在类外调用顺序 先调用setParameter 
     */
	public function setParameter($params){
		$this->parameter = $params;
	}
	
	
	/**
     * 设置自定义参数
     * 注意 在类外调用顺序 先调用setExtraParameter 
     * 
     * @params $model  string 模型文件名
	   @params $cuid   int    交互ID
	 * @params $params array  多个参数用 '|'隔开 
     */
	public function setExtraParameter($model,$cuid,$params){
		$model = trim($model);
		$cuid = trim($cuid);
		$params = array_map("trim",$params);
		$this->extraParameter = $model.'|'.$cuid.'|'.implode('|',$params);
	}
}