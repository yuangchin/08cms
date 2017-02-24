<?php
/**
 * 楼盘微信插件后台管理
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 * @version   1.0
 */

class _08_Admin_Weixin_Property extends _08_Plugins_AdminHeader
{ 
    public function __construct()
    {
        parent::__construct();
		if($re = $this->_curuser->NoBackFunc('weixin')) cls_message::show($re);
        aheader();
        $this->_get['aid'] = (isset($this->_get['aid']) ? (int) $this->_get['aid'] : 0);
        if ( empty($this->_get['aid']) || empty($this->_get['cache_id']) )
        {
            cls_message::show('参数非法。', M_REFERER);
        }
    }
    
    public function init()
    {
        $configs = cls_envBase::_GET_POST();
        $Weixin_DataBase = parent::getModels('Weixin_DataBase');
        if( submitcheck('weixin_submit') )
        {
            $mconfigsnew = $configs['mconfigsnew'];
            if ( !empty($mconfigsnew['weixin_enable']) &&
                 (empty($mconfigsnew['weixin_token']) || empty($mconfigsnew['weixin_appid']) || empty($mconfigsnew['weixin_appsecret'])) )
            {
                cls_message::show('账号信息不能为空。', M_REFERER);
            }
            
            $mconfigsnew['weixin_fromid_type'] = 'aid';
            $mconfigsnew['weixin_fromid'] = $this->_get['aid'];
            $mconfigsnew['weixin_cache_id'] = $this->_get['cache_id'];
            if ( isset($mconfigsnew['weixin_url']) )
            {
                unset($mconfigsnew['weixin_url']);
            }
            
            if ( $Weixin_DataBase->saveConfig($mconfigsnew) )
            {
                adminlog('楼盘微信设置','楼盘微信公众平台配置');
    		    cls_message::show('微信配置修改成功。', M_REFERER); 
            }
    		else
            {
              	cls_message::show('微信配置修改失败，请稍候再试。', M_REFERER); 
            }
        }
        $aidString = "&aid={$this->_get['aid']}";
        tabheader('微信公众平台配置 <span style="color:red;">( ID: ' . $this->_get['aid'] . ' ) 将以下信息与微信公众平台 “开发模式” 下的接口配置信息对应即可</span>', 'weixin_form', $this->_url . "&cache_id={$this->_get['cache_id']}" . $aidString);
        
        $config = (array) $Weixin_DataBase->getConfig('aid', $this->_get['aid']);
		_08_Admin_Weixin_Plugin_Header::configUI( $config, "init{$aidString}" );
        $title = "创建楼盘（ID：{$this->_get['aid']}）微信菜单";
        tabfooter('weixin_submit', '保存', '&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn" type="submit" name="weixin_create_menu" value="' . $title . '" onclick="_08cms_layer({type: 2, url:\'' . _08_M_Weixin_Config::getWeixinURL("create_menu&cache_id={$configs['cache_id']}{$aidString}") . '\', title: \'' . $title . '\' }); return false;">');
        a_guide('admin_weixin_config');
    }
}