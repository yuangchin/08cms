<?php
/**
 * 会员中心插件控制器公共头部
 * 注意：扩展子类里必要定义两个变量：
 * 1、$this->_weixin_fromid_type 默认值为mid，如果为aid或其它不同的名称时请定义成该名称
 * 2、$this->_get['cache_id']    这是缓存ID，对应着模板配置里的微信菜单缓存文件名，如：
 *                               /template/default/config/weixin_menus_{$this->_get['cache_id']}.cac.php
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('M_MCENTER') || exit('No Permission');
abstract class _08_Plugins_MemberHeader extends _08_Controller_Base implements _08_IPlugin_Member
{
    protected $_weixin_fromid_type;
    
    public function __construct()
    {        
        parent::__construct();
		if( empty($this->_curuser->info['mid']) ) cls_message::show('请先登录。');
        $this->_weixin_fromid_type = 'mid';
    }
    
    public function init()
    {
		$mconfigs = cls_cache::Read('mconfigs');
        $Weixin_DataBase = parent::getModels('Weixin_DataBase');
        // 该cache_id在扩展子类里定义
        if (empty($this->_get['cache_id']))
        {
            cls_message::show('请先定义：cache_id', M_REFERER);
        }
        $mconfigsnew['weixin_cache_id'] = $this->_get['cache_id'];
        if( submitcheck('weixin_submit') )
        {
            $mconfigsnew = @$this->_get['mconfigsnew'];
            if ( !empty($mconfigsnew['weixin_enable']) && 
                 (empty($mconfigsnew['weixin_token']) || empty($mconfigsnew['weixin_appid']) || empty($mconfigsnew['weixin_appsecret'])) )
            {
                cls_message::show('账号信息不能为空。', M_REFERER);
            }
            
            $mconfigsnew['weixin_fromid_type'] = $this->_weixin_fromid_type;
            $mconfigsnew['weixin_fromid'] = $this->_get[$this->_weixin_fromid_type];
			$mconfigsnew['weixin_cache_id'] = $this->_get['cache_id'];
            if ( isset($mconfigsnew['weixin_url']) )
            {
                unset($mconfigsnew['weixin_url']);
            }
            
            if ( $Weixin_DataBase->saveConfig($mconfigsnew) )
            {
    		    cls_message::show('微信配置修改成功。', M_REFERER); 
            }
    		else
            {
              	cls_message::show('微信配置修改失败，请稍候再试。', M_REFERER); 
            }
        }
        $this->_url = http_build_query($this->_get);
        tabheader('微信公众平台配置 <span style="color:red;">( ID: ' . $this->_get[$this->_weixin_fromid_type] . ' ) 将以下信息与微信公众平台 “开发模式” 下的接口配置信息对应即可</span>', 'weixin_form', "?{$this->_url}");
        
        _08_Loader::import(_08_PLUGINS_PATH . 'weixin::admin::admin_weixin_plugin_header');
        $config = (array) $Weixin_DataBase->getConfig($this->_weixin_fromid_type, $this->_get[$this->_weixin_fromid_type]);
        $type = $this->_weixin_fromid_type . '=' . $this->_get[$this->_weixin_fromid_type];
        _08_Admin_Weixin_Plugin_Header::configUI($config, "init&{$type}");
        $title = "创建微信菜单"; 
        $url = _08_M_Weixin_Config::getWeixinURL("create_menu&cache_id={$mconfigsnew['weixin_cache_id']}&{$type}");
        echo <<<HTML
        <tr><td colspan="2" align="center" height="80">
        <input type="submit" name="weixin_submit" value="保存" style="border:none; background:url(./$mconfigs[mc_dir]/images/icon.gif) no-repeat -297px -35px; width: 65px; height:25px" />&nbsp;&nbsp;
        <input class="use_menu" style="border:none; background:url(./$mconfigs[mc_dir]/images/icon.gif) no-repeat -305px -230px; width: 100px; height:25px" type="submit" name="weixin_create_menu" value="$title" onclick="_08cms_layer({type: 2, url:'$url', title: '$title' }); return false;" />
        </td></tr>
        </table></form>
HTML;
    }
}