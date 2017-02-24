<?php
/**
 * 微信插件后台公共管理类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 * @version   1.0
 */

class _08_Admin_Weixin_Plugin_Header extends _08_Models_Base
{
    private static $instance = null;
    
    /**
     * 公众平台配置UI
     */
    public static function configUI( array $configs = array(), $uri = '' )
    {
        if ( defined('M_ADMIN') )
        {
            $br = '';
        }
        else
        {
        	$br = '<br />';
        }
        empty($configs['weixin_enable']) && ($configs['weixin_enable'] = 0);
		trbasic('开启系统菜单','mconfigsnew[weixin_enable]', $configs['weixin_enable'],'radio', array('guide' => $br . '如果不开启可关闭，然后直接上传外站的微信二维码使用外站的微信号即可。', 'validate' =>'onclick="useSystemMenu(this.value);"'));
        
        echo '<tr id="use_menu"><td colspan="2" style="border-bottom: none"><table width="100%" border="0" cellpadding="0" cellspacing="0" class=" tb tb2 bdbot">';
        trbasic('网址URL','mconfigsnew[weixin_url]', _08_M_Weixin_Config::getWeixinURL($uri) . '/', 'text', array('guide' => $br . '该URL由系统自动生成，暂时不开放编辑，直接把它复制到微信公众平台即可。','w' => 65,'validate' =>'maxlength="250" readonly'));
        trbasic('Token *','mconfigsnew[weixin_token]', @$configs['weixin_token'], 'text', array('guide' => $br . 'Token可以任意填写，但必须与公众平台一致，用作生成签名，没有公众账号？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a>','w' => 65, 'validate' => 'maxlength="250"'));
        trbasic('AppId *','mconfigsnew[weixin_appid]', @$configs['weixin_appid'], 'text', array('guide' => $br . '没有AppId？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有服务号或订阅号认证过后才能申请。）','w' => 65, 'validate' => 'maxlength="250"'));
        trbasic('AppSecret *','mconfigsnew[weixin_appsecret]', @$configs['weixin_appsecret'], 'text', array('guide' => $br . '没有AppSecret？<a style="color:red" href="https://mp.weixin.qq.com/" target="_blank">点击申请</a> （注：只有有服务号或订阅号认证过后才能申请。）','w' => 65, 'validate' => 'maxlength="250"'));
        echo '</table></td></tr>';
        # 暂时保留原来的微信二信码图片地址
        if ( empty($configs['weixin_qrcode']) && isset(self::getInstance()->_get['entry']) && self::getInstance()->_get['entry'] == 'weixin' )
        {
            $var = 'weixin';
            $tpl_fields = cls_cache::Read('tpl_fields');
            $tpl_mconfigs = cls_cache::Read('tpl_mconfigs');
            
            if ( !empty($tpl_fields['wxewmpic']) )
            {
                $var = 'wxewmpic';
            }
            
            if ( !empty($tpl_mconfigs['user_' . $var]) )
            {
                $configs['weixin_qrcode'] = $tpl_mconfigs['user_' . $var];
                echo <<<JAVASCRIPT
                <div id="settings"></div>
                <style type="text/css">
                    #settings {
                    	z-index: 8000; position: fixed; width: 43px; display: block; left: 540px; top: 430px; cursor: pointer;
                    }
                </style>
    <script type="text/javascript">
        layer.tips('升级用户请先点击这里保存一下。', $('#settings'), {
            style: ['background-color:#134D9D; color:#fff; height: 40px', '#134D9D'],
            maxWidth:185,
            guide: 1,
            time: 0,               
            closeBtn:[0, true]
        });
    </script>
JAVASCRIPT;
            }
        }
            
        echo <<<JAVASCRIPT
        <script type="text/javascript">
            function useSystemMenu(_value)
            {
                _value = parseInt(_value);
                if (_value)
                {
                    jQuery('#use_menu, .use_menu').show();
                }
                else
                {
                	jQuery('#use_menu, .use_menu').hide();
                }
            }
            
            jQuery(function(){
                useSystemMenu({$configs['weixin_enable']});
            });         
        </script>
JAVASCRIPT;
        empty($configs['weixin_qrcode']) || ($configs['weixin_qrcode'] = cls_url::save_atmurl($configs['weixin_qrcode']));
        $config = array('type' => 'image','varname' => 'mconfigsnew[weixin_qrcode]','value' => @$configs['weixin_qrcode']);
        if (defined('M_ADMIN'))
        {
            $config['guide'] = '前台调用样式：{$weixin_qrcode}';
        }
        trspecial('微信二维码',specialarr($config));
    }
    
    public static function getInstance()
    {
        if ( !(self::$instance instanceof self) )
        {
            self::$instance = new self();
        }
        
        return self::$instance;
    } 
}