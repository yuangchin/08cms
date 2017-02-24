<?php
/**
 * 支付网关模型基类
 *
 * @since     nv50
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_M_PayGate_Base extends _08_Models_Base
{
    /**
     * 付费金额
     * 
     * @var double
     */
    protected $_total_fee = 0.00;
    
    /**
     * 订单号
     * 
     * @var string
     */
    protected $_out_trade_no = '';
    
    /**
     * 订单名称
     * 
     * @var string
     */
    protected $_subject = '';
    
    /**
     * 支付帐户
     * 
     * @var string
     */
    protected $_seller_email = '';
    
    /**
     * 网关配置
     * 
     * @var array
     */
    protected $_configs = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->setConfigs();
    }
    
    /**
     * 获取配置信息
     * 
     * @return array 返回配置信息
     */
    public function getConfigs()
    {
        return $this->_configs;
    }
    
    /**
     * 设置回调网址
     * 
     * @parma string $url 回调网址
     */
    public function setCallBack( $url )
    {
        msetcookie('pays_callback', $url, 0, true);
    }
    
    /**
     * 显示支付订单状态（同步状态）
     * 
     * @param string $status 状态信息
     */
    public function showPaysStatus($status)
    {
        $mcookie = cls_env::_COOKIE();
        $jumpurl = empty($mcookie['pays_callback']) ? '' : $mcookie['pays_callback'];
        
        if( (string) $status == 'success' )
        {
        	$message = "支付完成！";
        }
        else
        {
        	$message =  "支付失败！";
        }
        
        cls_message::show($message, $jumpurl);
    }
    
    /**
     * 添加支付记录
     * 
     * @param  array $parms 要添加的数据
     * @return bool         返回添加状态
     */
    public function addPays( array $params )
    {
        $params['mid'] = empty($this->_curuser->info['mid']) ? 0 : $this->_curuser->info['mid'];
        $params['mname'] = empty($this->_curuser->info['mname']) ? '游客' : $this->_curuser->info['mname'];
        $params['senddate'] = TIMESTAMP;
        $params['pmode'] = 1;
        $params['ip'] = cls_env::OnlineIP();
		$params['model'] = isset($params['model']) ? $params['model']  : '';
        return _08_factory::getInstance(_08_Loader::MODEL_PREFIX . 'PayGate_Pays',$params['model'])->add($params);
    }
    
    /**
     * 给当前用户增加积分
     * 
     * @param double $currency 积分数量
     * @param int    $mid      要充值的会员ID，如果不传递则为当前请求的会员ID
     **/
    public function addCurrency($currency, $mid = null)
    {
        if (empty($this->_mconfigs['onlineautosaving']))
        {
            return false;
        }
        
        # 开始充值积分
        $currency = doubleval($currency);
        if (is_null($mid))
        {
            $mid = $this->_curuser->info['mid'];
            $this->_curuser->updatecrids(array(0 => $currency), 1, '在线支付');
        }
        else
        {
        	$mid = (int) $mid;
			$user = new cls_userinfo();
			$user->activeuser($mid);
			$user->updatecrids(array(0 => $currency), 1, '在线支付');
        }
        
        return true;
    }
    
    /**
     * 获取订单号
     */
    public function getOrderSN()
    {
        return date("YmdHis") . cls_string::Random(6, 1);
    }
    
    abstract public function setConfigs();
    
    abstract public function send( array $params );
}