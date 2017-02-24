<?php
/**
 * 微信接口基类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
abstract class _08_M_Weixin_Base extends _08_Models_Base
{
    /**
     * 获取授权的URL
     * 
     * @var string
     */
    protected $_get_access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
    
    /**
     * 获取用户基本信息的URL
     * 
     * @var string
     */
    protected $_get_user_info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
    
    protected $_access_token = '';
    
    protected $_params = array();
    
    /**
     * 数据库里开启插件的表示值
     * 
     * @var   int
     * @since nv50
     */
    const PLUGIN_ENABLE_VALUE = 1;
    
    /**
     * 数据库里关闭插件的表示值
     * 
     * @var   int
     * @since nv50
     */
    const PLUGIN_CLOSE_VALUE = 0;
    
    protected $_message = '';
    
    public function __construct( $params = array() )
    {
        parent::__construct();
        $this->_params = $params;
        
        if ( !isset($this->_get['cache_id']) && !isset($this->_get['weixin_cache_id']) )
        {
            if ( empty($this->_mconfigs['weixin_token']) || empty($this->_mconfigs['weixin_appid']) || empty($this->_mconfigs['weixin_appsecret']) )
            {
                cls_message::ajax_info('请先在网站后台 系统设置 -> 网站参数 -> 通行证 -> 微信公众平台配置 里填上完整信息。', 'CONTENT');
            }
        }
        
        if ( empty($this->_params['weixin_fromid']) )
        {
            $this->_message = '';
        }
        else
        {
        	$this->_message = " ( ID: {$this->_params['weixin_fromid']} ) ";
        }
    }
    
    /**
     * 请求获取access_token
     * 
     * @param string $weixin_appid     微个APPID
     * @param string $weixin_appsecret 微信AppSecret
     */
    protected function _requestGetAccessToken($weixin_appid, $weixin_appsecret)
    {
		$accessInfo = WeixinAccessInfo($this->_get_access_token_url, $weixin_appid, $weixin_appsecret);
		if(empty($accessInfo)){
			cls_message::ajax_info('请求[https://api.weixin.qq.com/cgi-bin/token]失败；<br>获取不到微信服务器数据，可能超时；<br>请检查服务器相关设置或服务器环境！', 'CONTENT');
		}elseif ( isset($accessInfo->access_token) ){
            $this->_access_token = $accessInfo->access_token;
        }else{
            $message = $this->_message . _08_M_Weixin_Error_Message::get(@$accessInfo->errcode);            
        	cls_message::ajax_info($message, 'CONTENT', $this->getNextJumpParams());
        }
    }
    
    /**
     * 获取下一次跳转的参数
     * 
     * @return string $url 返回下一次跳转的参数
     * @since  nv50
     */
    public function getNextJumpParams()
    {
        $jumpParams = array();
        if ( !empty($this->_params['weixin_cache_id']) && isset($this->_get['target']) && strtolower($this->_get['target']) == 'all' )
        {
            isset($this->_params['weixin_fromid_type']) || ($this->_params['weixin_fromid_type'] = '');
            isset($this->_params['weixin_fromid']) || ($this->_params['weixin_fromid'] = 0);
            $next_id = parent::getModels('Weixin_DataBase')->getNextID($this->_params['weixin_fromid_type'], $this->_params['weixin_fromid']);
            
            $uri = "create_menu&cache_id={$this->_params['weixin_cache_id']}&{$this->_params['weixin_fromid_type']}={$next_id}";
            if ( $next_id )
            {
                $jumpParams = array('url' => _08_M_Weixin_Config::getWeixinURL($uri . '&target=all'), 'timeout' => 2);
            }
            else
            {
            	$jumpParams = array('url' => _08_M_Weixin_Config::getWeixinURL($uri . '&target=end'), 'timeout' => 2);
            }
        }
        
        return $jumpParams;
    }
    
    /**
     * 获取当前请求微信的appid和appsecret
     * 
     * @param  int   $pluginStatusValue 是否获取开启与不开启的微信配置信息，默认为获取所有
     * 
     * @return array $config            返回获取到的appid和appsecret
     */
    public function getAppIDAndAppSecret( $pluginStatusValue = null )
    {
        $config = array();
        if ( isset($this->_params['weixin_fromid_type']) && !empty($this->_params['weixin_fromid']) )
        {
            $Weixin_Config_Table = parent::getModels('Weixin_Config_Table');
            $Weixin_Config_Table->where(array('weixin_fromid_type' => $this->_params['weixin_fromid_type']))
                                ->_and(array('weixin_fromid' => $this->_params['weixin_fromid']));
//            if (!empty($this->_params['weixin_cache_id']))
//            {
//                $Weixin_Config_Table->_and(array('weixin_cache_id' => $this->_params['weixin_cache_id']));
//            }
            
            if ( !empty($pluginStatusValue) )
            {
                $Weixin_Config_Table->_and(array('weixin_enable' => (int)$pluginStatusValue));
            }
            
            $config = $Weixin_Config_Table->read('weixin_token, weixin_appid, weixin_appsecret, weixin_enable');
        }
        else if ( empty($this->_params['weixin_cache_id']) && empty($this->_get['cache_id']) )
        {
        	$config = array('weixin_token' => $this->_mconfigs['weixin_token'], 
                            'weixin_appid' => $this->_mconfigs['weixin_appid'], 
                            'weixin_appsecret' => $this->_mconfigs['weixin_appsecret'], 
                            'weixin_enable' => $this->_mconfigs['weixin_enable']);
        }
        
        return $config;
    }
    
    /**
     * 格式化POST数据
     * 
     * @param  mixed  $postDatas 要POST的数据，格式如果是JSON时直接返回，数组时会把数组转成JSON返回
     * @return string            返回JSON格式数据
     * 
     * @since  nv50
     */
    public function formatPostDatasToJSON( $postDatas )
    {
        $postDatas = cls_string::iconv(cls_env::getBaseIncConfigs('mcharset'), 'UTF-8', $postDatas);        
        if ( is_array($postDatas) )
        {
            if (version_compare(PHP_VERSION, '5.4.0') >= 0)
            {
                $postDatas = json_encode($postDatas, JSON_UNESCAPED_UNICODE);
            }
            else
            {
            	$postDatas = jsonEncode($postDatas);
            }
        }
        
        return $postDatas;
    }
    
    /**
     * 开始运行扩展系统响应数据
     */
    public function run( $postObj )
    {
        $class = 'Weixin_';
        $MsgType = isset($postObj->MsgType) ? strtolower($postObj->MsgType) : '';
        // 目前只让支持消息与事件响应两种处理方法
        if ( 'event' === $MsgType )
        {
            $class .= 'Event';
        }
        else
        {
        	$class .= 'Extends_Message';
        }
        
        $instance = parent::getModels($class, $postObj);
        if ( !empty($instance) )
        {
            $method = 'response';
            
            if ( isset($postObj->Event) )
            {
                $method .= ucfirst(strtolower($postObj->Event));
            }
            else
            {
                $method .= ucfirst($MsgType);            	
            }
            
            if ( method_exists($instance, $method) )
            {
                return call_user_func(array($instance, $method));
            }
        }
    }
    
    /**
     * 获取当前会话微信配置
     * 
     * @return array
     * @since  nv50
     */
    public static function getConfigs( SimpleXMLElement $post )
    {
        $datas = array();
        if ( isset($post->FromUserName) )
        {
            $key = '_' . md5($post->FromUserName);
            $savePath = self::_getCachePath($post->FromUserName);
            $datas = cls_cache::cacRead($key, $savePath);
        }
        
        return (array) $datas;
    }
    
    /**
     * 设置当前会话微信配置
     * 
     * @return array
     * @since  nv50
     */
    public static function setConfigs( array $configs, SimpleXMLElement $post )
    {
        if ( isset($post->FromUserName) )
        {
            $key = '_' . md5($post->FromUserName);
            $savePath = self::_getCachePath($post->FromUserName);
            cls_CacheFile::cacSave($configs, $key, $savePath . DS);
            return true;
        }
        
        return false;
    }
    
    /**
     * 获取微信缓存路径
     * 
     * @param  string $FromUserName 当前会话来源用户名
     * @return string $savePath     返回微信缓存路径
     * @since  nv50
     */
    protected static function _getCachePath( $FromUserName )
    {
        $savePath = _08_CACHE_PATH . 'excache';
        
        if ( empty($FromUserName) )
        {
            return $savePath;
        }
        
        $key = md5($FromUserName);
        $savePath .= DS . substr($key, 0, 1);
        _08_FileSystemPath::checkPath($savePath, true);
        return $savePath;
    }
}