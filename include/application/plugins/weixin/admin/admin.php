<?php
/**
 * 微信插件后台管理
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 * @version   1.0
 */

class _08_Admin_Weixin_Plugin extends _08_Plugins_AdminHeader
{
    public function __construct()
    {
        parent::__construct();
		if($re = $this->_curuser->NoBackFunc('tpl')) cls_message::show($re);
        aheader();
        (empty($this->_params['action']) || ($this->_params['action'] == 'init')) && $this->_params['action'] = 'config';
        backnav('weixin', $this->_params['action']);
    }
    
    /**
     * 公众平台配置
     */
    public function config()
    {
        if( submitcheck('weixin_submit') )
        {
            $configs = cls_envBase::_POST();
            $mconfigsnew = @$configs['mconfigsnew'];
            if ( !empty($mconfigsnew['weixin_enable']) && 
                 (empty($mconfigsnew['weixin_token']) || empty($mconfigsnew['weixin_appid']) || empty($mconfigsnew['weixin_appsecret'])) )
            {
                cls_message::show('账号信息不能为空。', M_REFERER);
            }
            if (!empty($mconfigsnew['weixin_qrcode']))
            {
                $mconfigsnew['weixin_qrcode'] = cls_url::save_atmurl($mconfigsnew['weixin_qrcode']);
                if (!empty($this->_mconfigs['ftp_enabled']))
                {
                    $mconfigsnew['weixin_qrcode'] = '<!ftpurl />' . $mconfigsnew['weixin_qrcode'];
                }
            }
    		saveconfig('weixin', $mconfigsnew);
    		adminlog('微信设置','微信公众平台配置');
    		cls_message::show('微信设置完成', M_REFERER);
        }
        tabheader('微信公众平台配置 <span style="color:red;">（将以下信息与微信公众平台 “开发模式” 下的接口配置信息对应即可，使用该功能必须先开启PHP的CURL、OPENSSL扩展）</span>', 'weixin_form', $this->_url);
        
		trbasic('开启二维码扫描登录与注册功能','mconfigsnew[weixin_login_register]', @$this->_mconfigs['weixin_login_register'],'radio', array('guide' => '注：该功能必须是微信认证后才能使用。'));
        
        _08_Admin_Weixin_Plugin_Header::configUI( $this->_mconfigs ); 
        $title = "创建总站微信菜单";       
        tabfooter('weixin_submit', '保存', '&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn use_menu" type="submit" name="weixin_create_menu" value="' . $title . '" onclick="_08cms_layer({type: 2, url:\'' . _08_M_Weixin_Config::getWeixinURL('create_menu') . '\', title: \'' . $title . '\' }); return false;">');
        a_guide('admin_weixin_config');
    }
    
    /**
     * 菜单配置
     */
    public function menu()
    {
        $cacheName = 'weixin_menus';
        $configs = cls_envBase::_GET_POST();
        if ( empty($configs['cache_id']) )
        {
            $configs['cache_id'] = '';
        }
        else
        {
            $configs['cache_id'] = trim($configs['cache_id']);
        	$cacheName .= ('_' . $configs['cache_id']);
        }
        $weixin_menus = cls_cache::Read($cacheName, '', '', 0, true);
        if( submitcheck('weixin_submit') )
        {
            foreach ($configs['catalogsnew'] as $key => $value )
            {
                if (isset($weixin_menus[$key]))
                {
                    $weixin_menus[$key] = $value;
                }
            }
            
            cls_CacheFile::Save($weixin_menus, $cacheName, '', 0, true);
    		cls_message::show('保存完成', M_REFERER);
        }
        
        $weixin_list_menu = _08_Loader::import(_08_EXTEND_PLUGINS_PATH . basename(dirname(dirname(__FILE__))) . '::admin::config');
        if ( empty($weixin_list_menu[$configs['cache_id']]) )
        {
            $weixin_list_menu[$configs['cache_id']] = '总站微信菜单';
        }
        tabheader('配置' . $weixin_list_menu[$configs['cache_id']] . ' <span style="color:red;">（请注意，创建自定义菜单后，请重新关注，否则由于微信客户端缓存，需要24小时微信客户端才会展现出来）</span>', 'weixin_form');
        
        if ( empty($weixin_menus) )
        {
            for($i = 1; $i <= 3; ++$i)
            {
                $ii = $i * 10;
                $weixin_menus[$ii] = array(
                    "caid"=> $ii,
                    "level" => 0,
                    "pid" => 0,
                    "title"=>"",
                    "vieworder"=> 0,
                    "url"=>''
                );
                for($j = ($ii + 1); $j <= ($ii + 5); ++$j)
                {
                    $weixin_menus[$j] = array(
                        "caid"=> $j,
                        "level" => 1,
                        "pid" => $ii,
                        "title"=>'',
                        "vieworder"=> 0,
                        "url"=>''
                    );
                }
            }
        }
        
        $menu_list = array();
        $menu_list['menus'] = array('' => '总站微信菜单');        
        $menu_list['menus'] += $weixin_list_menu;        
        $menu_list['menus_ids'] = self::getIDs($configs['cache_id']);
        $menu_list['tip'] = '点击这可编辑其它模型的菜单。';
        $this->_build->TableTree($weixin_menus, $menu_list);
        $title = '创建所有' . $weixin_list_menu[$configs['cache_id']];
        tabfooter('weixin_submit', '保存', '&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn" type="submit" name="weixin_create_menu" value="' . $title . '" onclick="_08cms_layer({type: 2, url:\'' . _08_M_Weixin_Config::getWeixinURL('create_menu&target=all&cache_id=' . $configs['cache_id']) . '\', title: \'' . $title . '\' }); return false;">');
    }
    
    /**
     * 获取自定义的菜单ID组
     * 
     * @param  string $cache_id 缓存ID
     * @return array  $menu_ids 菜单ID组
     * 
     * @since  nv50
     */
    private static function getIDs($cache_id)
    {
        _08_FilesystemFile::filterFileParam($cache_id);
        $filename = 'config_' . $cache_id;        
        $menu_ids = _08_Loader::import(_08_EXTEND_PLUGINS_PATH . basename(dirname(dirname(__FILE__))) . '::admin::' . $filename);
        return _08_Documents_JSON::encode($menu_ids, true);
    }
    
    public function init()
    {
        $this->config();
    }
}