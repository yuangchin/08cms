<?php
/**
 * 微信接口控制器
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
define('_08CMS_WEIXIN_CONTROLLER', 1);
class _08_C_Weixin_Controller extends _08_Controller_Base
{
    private $params = array();
	private $wecfg = array(); //公众号配置
    
    public function __construct()
    {
		parent::__construct();
        $this->params = $this->_get;
		//mid,aid,<null>
        if(isset($this->params['mid'])){
            $key = intval($this->params['mid']);
			$type = 'mid';
        }elseif(isset($this->params['aid'])){
            $key = intval($this->params['aid']);
			$type = 'aid';
		}else{
			$key = 0;
			$type = 'sid';
		}
		//公众号配置
		$this->wecfg = cls_w08Basic::getConfig($key, $type); 
		//开关: # print_r($this->wecfg); print_r($this->_mconfigs);
		$enable = empty($this->wecfg['appid']) ? 0 : $this->wecfg['enable']; //appid为空表示不启用
		if(empty($this->_mconfigs['weixin_debug'])){ //正式使用状态
			if(!$enable){ //未启用
				exit(''); //用于[回复]微信服务器
			}
			if(!cls_wmpBasic::checkValid($this->wecfg)){ //不合法请求,显示信息给人看的
				//cls_outbug::main("_08_C_Weixin_Controller::checkValid:",'GET,POST,SERVER','log_'.date('Y_md').'.txt',1);
				//register_shutdown_function(array($this, 'checkStatus'), $postObj);
				exit('非法请求！如测试请设置调试状态。'); //记录...
			}
		}else{ //调试状态
			//	
		}
        # 验证签名
        if(isset($this->params['echostr'])){
			if(cls_wmpBasic::checkSignature($this->wecfg)){
				exit($this->params['echostr']);
        	}else{
				cls_w08Basic::debugError('checkSignature', $this->params, '');
			}
		}
    }
    
    /**
     * 初始化接口，消息回复(关键字,图片处理), 事件回复(点击菜单, 扫描, .... 
     * @since nv50
     */
    public function init(){ 
		$error = ''; 
		$data = file_get_contents('php://input'); //echo "(".htmlspecialchars($data).")";
		$post = false; 
        if(!empty($data)){
            $post = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            if(!empty($post->MsgType)){ //echo $post->MsgType;
                if($post->MsgType=='event'){
					$class = 'cls_w08Event'; //$post->Event=='CLICK' ? 'w08EMenu' : 
				}else{
					$class = 'cls_w08Response';
				}
				$weixin = new $class($post,$this->wecfg);
            }else{
				$error = "Empty post or MsgType!";	
			}
        }else{
			$error = "Empty data!";
		}
		if($error){ 
			$errdata = array('params'=>$this->params, 'data'=>$data, 'post'=>$post);
			//print_r($errdata); echo $error;
			cls_w08Basic::debugError($error, $errdata, '');
		}
		//return array($postObj,$postData);	
    }
    
	/* debug && tools
	#cls_outbug::main("_08_M_Weixin_Event::checkStatus:".$post,'','wetest/log_'.date('Y_md').'.log',1);
	//register_shutdown_function(array($this, 'checkStatus'), $postObj);
	*/
	
}
