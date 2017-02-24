<?php
/**
 * 支付网关控制器
 *
 *  支付宝返回链接例子 可以用来测试签名是否匹配
http://192.168.1.28/home/index.php?/paygate/alipay_direct_return_url&buyer_email=13712926549&buyer_id=2088802403051784&exterface=create_direct_pay_by_user&extra_common_param=Pays_Payment_Table%7C12%7C7%7Czhuangxiu%7C6727%7C%C8%FD%B6%E4%BD%F0%BB%AA%7C13456789021%7C%7C&is_success=T&notify_id=RqPnCoPT3K9%252Fvwbh3InTvaXPmweHHzms5oKJF%252FRAaJj%252FDDE9EQlEkckHBw%252FkBZe7lxhp&notify_time=2015-03-25+10%3A57%3A26&notify_type=trade_status_sync&out_trade_no=20150325105417568314&payment_type=1&seller_email=910377558%40qq.com&seller_id=2088311817647422&subject=%B5%D8%B0%E5%D7%A9%CD%C5%B9%BA&total_fee=0.01&trade_no=2015032500001000780050115871&trade_status=TRADE_SUCCESS&sign=8f0580b9ffdac65577eb2769be3313e8&sign_type=MD5
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */
defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_C_PayGate_Controller extends _08_Controller_Base
{    
    /**
     * 支付模型对象
     * 
     * @var object
     */
    protected $_08_M_PayGate_Pays = null;
    
	protected $extra_param = array();
    /**
     * 订单状态map
     * 
     * @var array
     */
    protected $_paysStatusMap = array(
        'PAY_FAIL' => -2,         # 订单失败
        'PAY_FINISHED' => -1,     # 交易完成
        'PAY_WAIT_PAY' => 0,      # 等待付款
        'PAY_WAIT_GOODS' => 1,    # 已付款，等待发货
        'PAY_CONFIRM_GOODS' => 2, # 已经发货
        'PAY_CONFIRM_GOODS' => 3  # 确认收货
    );
    
    public function __construct()
    {
        parent::__construct();
		if(isset($this->_get['extra_common_param'])){
			$this->extra_param = @explode('|',$this->_get['extra_common_param']);
		}
        $this->_08_M_PayGate_Pays = _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays',isset($this->extra_param[0]) && !empty($this->extra_param[0]) ? $this->extra_param[0] : '');
    }
    
    /**
     * 支付宝即时到账异步通知处理
     * 注：异步通知为当支付状态改变时发送过来的请求，一般用于更新订单状态。
     * 
     * @param bool $returnStatus 是否返回状态，TRUE为返回，FALSE为不返回
     */
    public function alipay_direct_notify_url($returnStatus = false)
    {
        $status = 'fail';
        if ( !empty($this->_get['out_trade_no']) &&
             ($payInfo = $this->_08_M_PayGate_Pays->read('status, to_mid, amount', array('ordersn' => @$this->_get['out_trade_no']))) )
        {
			;
            _08_Loader::import(_08_OUTSIDE_PATH . 'alipay_direct' . DIRECTORY_SEPARATOR . 'lib:alipay_notify.class');
            $paysObject = _08_factory::getPays('alipay_direct');
            $alipay_config = $paysObject->getConfigs();
            if ( !empty($payInfo['to_mid']) )
            {
                $memberInfo = cls_UserMain::CurUser()->getPaysInfo($payInfo['to_mid']);
                $alipay_config['partner'] = $memberInfo['alipay_partnerid'];
                $alipay_config['key'] = $memberInfo['alipay_partnerkey'];
				$alipay_config['seller_email'] = $memberInfo['alipay_seller_account'];

            }
			
            //计算得出通知验证结果
            $alipayNotify = new AlipayNotify($alipay_config);
            $_POST = $this->_get;
            @$verify_result = $alipayNotify->verifyNotify();
			#var_dump($verify_result); exit();
            if ( $verify_result && isset($this->_get['trade_status']) )
            #if ( isset($this->_get['trade_status']) )  // 调试时用
            {
                switch ( $this->_get['trade_status'] )
                {
                    /**
                     * 注意：该种交易状态只在两种情况下出现
                     * 1、开通了普通即时到账，买家付款成功后。
                     * 2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
                     */
                    case 'TRADE_FINISHED':
                        
                    // 注意：该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
                    case 'TRADE_SUCCESS':
                        # 该订单未处理，则自动充值积分，否则不作积分处理，只显示成功，防止用户狂刷新
                        if (($payInfo['status'] == $this->_paysStatusMap['PAY_WAIT_PAY']) && $this->_08_M_PayGate_Pays->setStatus(
                                $this->_paysStatusMap['PAY_WAIT_GOODS'], array('ordersn' => $this->_get['out_trade_no'])))
                        {
							if(isset($this->extra_param[1]) && !empty($this->extra_param[1])) #在线支付 判断cuid的值
							{
								array_shift($this->extra_param);  //移除第一个model参数
								#payment方法定义在扩展核心paygate目录下 如extend_home/libs/classes/paygate/
								if(method_exists($this->_08_M_PayGate_Pays, 'payment'))
									$this->_08_M_PayGate_Pays->payment($this->extra_param[0],$payInfo['amount'],$this->extra_param);
							}else                         #充值积分
                            	$paysObject->addCurrency($payInfo['amount']);
                        }
                        
                        $status = 'success';   
                        
                        break;
                }
            }            
        }
        
        if ( $returnStatus )
        {
            return $status;
        }
        
        exit($status);
    }
    
    /**
     * 支付宝即时到账同步通知处理
     */
    public function alipay_direct_return_url()
    {
        $status = $this->alipay_direct_notify_url(true);
        _08_factory::getPays('alipay_direct')->showPaysStatus($status);
    }
    
    /**
     * 手机支付宝即时到账异步通知处理
     */
    public function alipay_direct_wap_notify_url()
    {
        $status = 'fail';
        if ( !empty($this->_get['out_trade_no']) &&
             ($payInfo = $this->_08_M_PayGate_Pays->read('status, to_mid, amount', array('ordersn' => @$this->_get['out_trade_no']))) )
        {
            _08_Loader::import(_08_OUTSIDE_PATH . 'alipay_direct_wap' . DIRECTORY_SEPARATOR . 'lib:alipay_notify.class');
            $paysObject = _08_factory::getPays('alipay_direct_wap');
            $alipay_config = $paysObject->getConfigs();
            if ( !empty($payInfo['to_mid']) )
            {
                $memberInfo = cls_UserMain::CurUser()->getPaysInfo($payInfo['to_mid']);
                $alipay_config['partner'] = $memberInfo['alipay_partnerid'];
                $alipay_config['key'] = $memberInfo['alipay_partnerkey'];
            }
            
            //计算得出通知验证结果
            $alipayNotify = new AlipayNotify($alipay_config);
            $_POST = $this->_get;
            @$verify_result = $alipayNotify->verifyNotify();
            
            if ( $verify_result )
            {                
            	$doc = new DOMDocument();	
            	if ($alipay_config['sign_type'] == 'MD5') {
            		$doc->loadXML($_POST['notify_data']);
            	}
            	
            	if ($alipay_config['sign_type'] == '0001') {
            		$doc->loadXML($alipayNotify->decrypt($_POST['notify_data']));
            	}
            	
            	if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) )
                {
            		//商户订单号
            		$out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
            		//支付宝交易号
            		$trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
            		//交易状态
            		$trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
                    
                    switch ( $trade_status )
                    {
                        /**
                         * 注意：该种交易状态只在两种情况下出现
                         * 1、开通了普通即时到账，买家付款成功后。
                         * 2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
                         */
                        case 'TRADE_FINISHED':
                            
                        // 注意：该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。
                        case 'TRADE_SUCCESS':
                            # 该订单未处理，则自动充值积分，否则不作积分处理，只显示成功，防止用户狂刷新
                            if (($payInfo['status'] == $this->_paysStatusMap['PAY_WAIT_PAY']) && $this->_08_M_PayGate_Pays->setStatus(
                                    $this->_paysStatusMap['PAY_WAIT_GOODS'], array('ordersn' => $this->_get['out_trade_no'])))
                            {
                                $paysObject->addCurrency($payInfo['amount']);
                            }
                            
                            $status = 'success';                            
                            break;
                    }
            	}
            }
        }
        
        exit($status);
    }
    
    /**
     * 手机支付宝即时到账同步通知处理
     */
    public function alipay_direct_wap_return_url()
    {
        $status = 'fail';
        if ( !empty($this->_get['out_trade_no']) &&
             ($payInfo = $this->_08_M_PayGate_Pays->read('status, to_mid, amount', array('ordersn' => @$this->_get['out_trade_no']))) )
        {
            _08_Loader::import(_08_OUTSIDE_PATH . 'alipay_direct_wap' . DIRECTORY_SEPARATOR . 'lib:alipay_notify.class');
            $paysObject = _08_factory::getPays('alipay_direct_wap');
            $alipay_config = $paysObject->getConfigs();
            if ( !empty($payInfo['to_mid']) )
            {
                $memberInfo = cls_UserMain::CurUser()->getPaysInfo($payInfo['to_mid']);
                $alipay_config['partner'] = $memberInfo['alipay_partnerid'];
                $alipay_config['key'] = $memberInfo['alipay_partnerkey'];
            }
            
            //计算得出通知验证结果
            $alipayNotify = new AlipayNotify($alipay_config);
            $verify_result = $alipayNotify->verifyReturn();
            
            if ( $verify_result )
            {
            	if (isset($this->_get['result']) && strtolower($this->_get['result']) === 'success')
                {
                    # 该订单未处理，则自动充值积分，否则不作积分处理，只显示成功，防止用户狂刷新
                    if (($payInfo['status'] == $this->_paysStatusMap['PAY_WAIT_PAY']) && $this->_08_M_PayGate_Pays->setStatus(
                            $this->_paysStatusMap['PAY_WAIT_GOODS'], array('ordersn' => $this->_get['out_trade_no'])))
                    {
                        $paysObject->addCurrency($payInfo['amount']);
                    }
                    
                    $status = 'success';
                }
            }
        }
        
        _08_factory::getPays('alipay_direct_wap')->showPaysStatus($status);
    }
    
    /**
     * 财付通支付异步通知处理
     * 
     * @param bool $returnStatus 是否返回状态，TRUE为返回，FALSE为不返回
     */
    public function tenpay_notify_url($returnStatus = false)
    {
        if ( isset($this->_get['out_trade_no']) )
        {
            $this->_get['out_trade_no'] = preg_replace('/[^\w]/', '', $this->_get['out_trade_no']);
        }
        else
        {
        	$this->_get['out_trade_no'] = '';
        }
        
        if ( !isset($this->_get['sign']) || empty($this->_get['out_trade_no']) ||
             !($payInfo = $this->_08_M_PayGate_Pays->read('status, to_mid, amount', array('ordersn' => $this->_get['out_trade_no']))) )
        {
            die('参数非法。');
        }
        
        $status = 'fail';
        define('TENPAY_PATH', _08_OUTSIDE_PATH . 'tenpay' . DIRECTORY_SEPARATOR);
        $paysObject = _08_factory::getPays('tenpay');
        $config = $paysObject->getConfigs();
        
        if ( !empty($payInfo['to_mid']) )
        {
            $memberInfo = cls_UserMain::CurUser()->getPaysInfo($payInfo['to_mid']);
            $config['appid'] = $memberInfo['partnerid'];
            $config['key'] = $memberInfo['partnerkey'];
        }
        $_GET = $this->_get;
        if ( empty($_GET['input_charset']) )
        {
            $_GET['input_charset'] = @$this->_mcharset;
        }
        _08_Loader::import(TENPAY_PATH . 'PayResponse.class');
        _08_Loader::import(TENPAY_PATH . 'NotifyQueryRequest.class');
        
        /* 创建支付应答对象 */
        $resHandler = new PayResponse($config['key']);
        
        //获取通知id:支付结果通知id，支付成功返回通知id，要获取订单详细情况需用此ID调用通知验证接口。
        $notifyId = $resHandler->getNotifyId();
        
        ob_start();
        // 告知财付通通知发送成功，如不加上下行代码会导致财付通不停里通知财付通app，即不停里调用财付通app的notify_url进行通知
        $resHandler->acknowledgeSuccess();
        $status = ob_get_clean();
        ob_end_clean();
        
        /* 初始化通知验证请求:财付通APP接收到财付通的支付成功通知后，通过此接口查询订单的详细情况，以确保通知是从财付通发起的，没有被篡改过。 */
        // 设置在沙箱中运行:正式环境请设置为false
        $noqHandler = new NotifyQueryRequest($config['key']);
        
        // 设置在沙箱中运行，正式环境请设置为false
        $noqHandler->setInSandBox(_08_factory::getPays('tenpay')->isInSandBox());
        //----------------------------------------
        //以下请求业务参数名称参考开放平台sdk文档-PHP
        //----------------------------------------
        // 设置财付通App-id: 财付通App注册时，由财付通分配
        $noqHandler->setAppid($config['appid']);
        
        // 设置通知id:支付结果通知id，支付成功返回通知id，要获取订单详细情况需用此ID调用通知验证接口。
        $noqHandler->setParameter("notify_id", $notifyId);
        // ************************************end*******************************
        
        // 发送请求，并获取返回对象
        $Response = $noqHandler->send();
        
        // ********************以下返回业务参数名称参考开放平台sdk文档-PHP*************************
        if( $Response->isPayed() && ($payInfo['status'] == $this->_paysStatusMap['PAY_WAIT_PAY']) )
        {    
             $flag = $this->_08_M_PayGate_Pays->setStatus( $this->_paysStatusMap['PAY_WAIT_GOODS'], 
                                                           array('ordersn' => $this->_get['out_trade_no']) );
             $paysObject->addCurrency($payInfo['amount']);
             
             // 已经支付
             if ( !$flag )
             {
                 $status = 'fail';
             }
        }
        else
        {
        	$status = 'fail';
        }
        
        if ( $returnStatus )
        {
            return $status;
        }
        
        exit($status);
    }
    
    /**
     * 财付通支付同步通知处理
     */
    public function tenpay_return_url()
    {
        $status = $this->tenpay_notify_url(true);
        _08_factory::getPays('tenpay')->showPaysStatus($status);        
    }
}