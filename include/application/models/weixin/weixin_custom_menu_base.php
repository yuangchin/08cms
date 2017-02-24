<?php
/**
 * 微信自定义菜单接口模型
 * 
 * 该接口可向微信服务端发送菜单管理请求，详情请查看：{@link http://mp.weixin.qq.com/wiki/index.php?title=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E6%8E%A5%E5%8F%A3}
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('_08CMS_APP_EXEC') || exit('No Permission');
class _08_M_Weixin_Custom_Menu_Base extends _08_M_Weixin_Base
{    
    protected $_urlFormat = 'https://api.weixin.qq.com/cgi-bin/menu/%s?access_token=%s';
    
    public function __construct( array $params = array() )
    {
        parent::__construct($params);
        $weixin_config = $this->getAppIDAndAppSecret( parent::PLUGIN_ENABLE_VALUE );
        
        if ( empty($weixin_config) || empty($weixin_config['weixin_enable']) )
        {
            cls_message::ajax_info("该{$this->_message}微信公众平台未启用或配置未保存。", 'CONTENT', $this->getNextJumpParams());
        }
                
        $this->_requestGetAccessToken($weixin_config['weixin_appid'], $weixin_config['weixin_appsecret']);
    }
    
    /**
     * 创建菜单，通过POST一个特定结构体，实现在微信客户端创建自定义菜单
     * 
     * @param array $menuInfo 要POST的数据，具体请看{@link http://mp.weixin.qq.com/wiki/index.php?title=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E6%8E%A5%E5%8F%A3#.E8.8F.9C.E5.8D.95.E5.88.9B.E5.BB.BA}
     * @example  # 调用方式一
     *          $Weixin_Custom_Menu = parent::getModels('Weixin_Custom_Menu');
                $Weixin_Custom_Menu->Create(
                    array( 'button' => 
                       array(
                           array('type' => 'click', 'name' => '今日歌曲', 'key' => 'V1001_TODAY_MUSIC'),
                           array('type' => 'view', 'name' => '歌手简介', 'url' => 'http://www.08cms.com/'),
                           array(
                               'name' => '菜单', 
                               'sub_button' => array(
                                    array('type' => 'click', 'name' => 'hello word', 'key' => 'V1001_HELLO_WORLD'),
                                    array('type' => 'click', 'name' => '赞一下我们', 'key' => 'V1001_GOOD')
                               )
                           ),
                       )
                   )
                );
                
                # 调用方式二：
                $Weixin_Custom_Menu->Create(
                       <<<JSON
                        {
                        	"button":[
                        	{
                        		"type":"click",
                        		"name":'今日歌曲',
                        		"key":"V1001_TODAY_MUSIC"
                        	},
                        	{
                        		"type":"view",
                        		"name":'歌手简介',
                        		"url":"http://www.08cms.com/"
                        	},
                        	{
                        		"name":'菜单',
                        		"sub_button":[
                        		{
                        			"type":"click",
                        			"name":"hello word",
                        			"key":"V1001_HELLO_WORLD"
                        		},
                        		{
                        			"type":"click",
                        			"name":'赞一下我们',
                        			"key":"V1001_GOOD"
                        		}]
                        	}]
                        }
JSON
                );
     * 
     * @return object 返回json_decode转回来的状态标志对象
     * @since  1.0
     */
    public function Create( $menuInfo = array() )
    {
        if ( empty($menuInfo) )
        {
            $cacheName = 'weixin_menus';
            if ( !empty($this->_params['weixin_cache_id']) )
            {
                $cacheName .= ('_' . trim($this->_params['weixin_cache_id']));
            }
            $weixin_menus = cls_cache::Read($cacheName, '', '', 0, true);
            if ( empty($weixin_menus) || !is_array($weixin_menus) )
            {
                cls_message::ajax_info('请先在后台模板风格 － 微信设置 － 菜单配置里配置自定义菜单。', 'CONTENT');
            }
            
            $menuInfo = $this->__08ToWeixinConfig($weixin_menus);
        }
        #var_dump($menuInfo);exit;
        $menuInfo = $this->formatPostDatasToJSON($menuInfo);
        $url = sprintf($this->_urlFormat, 'create', $this->_access_token);
        $returnInfo = _08_Http_Request::getResources(array('urls' => $url, 'method' => 'POST', 'postData' => $menuInfo), 5);
        
        return json_decode($returnInfo);
    }
    
    /**
     * 从本系统缓存格式转成微信所使用的菜单格式
     * 
     * @param  array $weixin_menus 本系统缓存格式配置数组
     * @return array $menuInfo     微信菜单格式数组
     * 
     * @since  nv50
     */
    private function __08ToWeixinConfig( array $weixin_menus )
    {
        $menuInfo = array();
        foreach ( $weixin_menus as $menu_id => $menu ) 
        {
            $menu['title'] = strip_tags(trim($menu['title']));
            $menu['url'] = strip_tags(trim($menu['url']));
            $this->_parseTag($menu['url']);
            if ( empty($menu['title']) )
            {
                continue;
            }
            
            if ( $menu['url'] && (strtolower(substr($menu['url'], 0, 4)) == 'http') )
            {
                $menu['url'] .= '&is_weixin=1';
                $array = array('type' => 'view', 'name' => $menu['title'], 'url' => $menu['url']);
            }
            else
            {
                $array = array('type' => 'click', 'name' => $menu['title'], 'key' => $menu['url']);
            } 
            
            # 如果某一级菜单下有二级菜单时，一级菜单只保留名称
            if ( isset($menuInfo['button']) && array_key_exists($menu['pid'], $menuInfo['button']) )
            {
                $menuInfo['button'][$menu['pid']]['sub_button'][] = $array;
                unset($menuInfo['button'][$menu['pid']]['type']);
                if ( isset($menuInfo['button'][$menu['pid']]['url']) )
                {
                    unset($menuInfo['button'][$menu['pid']]['url']);
                }
                else
                {
                	unset($menuInfo['button'][$menu['pid']]['key']);
                }
            }
            else
            {
            	$menuInfo['button'][$menu_id] = $array;
            }
        }
        
        return $menuInfo;
    }
    
    /**
     * 解析标签
     * 
     * @param mixed $value 要解决的值
     * @since nv50
     */
    protected function _parseTag( &$value )
    {
        $value = @str_replace(
                    array('{cms_abs}', '{aid}', '{mid}'),
                    array(_08_CMS_ABS, (int)$this->_get['aid'], (int)$this->_get['mid']),
                    $value
                 );
    }
    
    /**
     * 菜单查询，查询当前使用的自定义菜单结构
     * 
     * @return object 返回json_decode转回来的状态标志对象
     * @since  1.0
     */
    public function Inquiry()
    {
        $url = sprintf($this->_urlFormat, 'get', $this->_access_token);
        $returnInfo = _08_Http_Request::getResources($url, 5);
        
        return json_decode($returnInfo);
    }
    
    /**
     * 菜单删除，取消当前使用的自定义菜单
     * 
     * @return object 返回json_decode转回来的状态标志对象
     * @since  1.0
     */
    public function Delete()
    {
        $url = sprintf($this->_urlFormat, 'delete', $this->_access_token);
        $returnInfo = _08_Http_Request::getResources($url, 5);
        
        return json_decode($returnInfo);
    }
}