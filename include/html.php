<?php
/**
 * HTML元素类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

defined('M_COM') || exit('No Permission');
class _08_HTML
{
	/**
     * 获取验证码元素
     * 
     * @param  string $codeName    定义一个验证码的名称后获取
     * @param  string $formName    验证码所在的表单名称
     * @param  string $class       input的class名称
     * @param  string $inputName   input名称
     * @param  string $inputString input属性字符串
     * @return string              返回以$codeName为名的验证码字符串
     * 
     * @since  1.0
     */
    public static function getCode( $verify = '08cms_regcode', $formName = '', $class = 'regcode', $inputName = '', $inputString = '' )
    {
    	global $regcode_mode, $cms_abs, $timestamp;
        switch($regcode_mode)
        {
            // 仅数字
            case 1 : $rule = 'number'; break;
            // 仅字母
            case 2 : $rule = 'letter'; break;
            // 数字与字母
            default : $rule = 'numberletter'; break;
        }

        if ( !empty($formName) && $formName !== NULL )
        {
            $str = '<script type="text/javascript">var ' . $formName . ' = _08cms.validator(\'' . $formName . '\');</script>';
        }
        else
        {
            $str = '';
        }

        $formName = empty($formName) ? '_08cms_validator' : trim($formName);
        $inputName = empty($inputName) ? 'regcode' : trim($inputName);
		$session_id = session_id();
		$session_name = session_name();
		$ajaxURL = $cms_abs . _08_Http_Request::uri2MVC("ajax=regcode&verify=$verify&regcode=%1&$session_name=$session_id");
        $str .= <<<HTML
<input type="text" name="$inputName" id="$inputName" size="4" maxlength="4" rule="$rule" must="1" min="4" max="4" init="点击输入框显示验证码" rev="验证码" offset="1" onblur="_08_Regcode.hide(this, '$verify');" onfocus="_08_Regcode.show(this, '$verify', event);" onkeyup="_08_Regcode.keyUpHide(this, '$verify', '$formName');" autocomplete="off" class="$class" $inputString />
        <img src="{$cms_abs}tools/regcode.php?verify={$verify}&t={$timestamp}" id="$verify" name="$verify" style="vertical-align: middle; cursor:pointer; position: absolute; z-index: 999; display:none" onclick="this.src += 1;" />
        <script type="text/javascript">
            var ajaxURL = '$ajaxURL';
            if ( typeof(uri2MVC) == 'function' )
            {
                ajaxURL += uri2MVC('&domain=' + document.domain, false);
            }
            window.$formName && $formName.init("ajax","$inputName",{ url: ajaxURL });
        </script>
        <input type="hidden" name="verify" value="$verify"/>
HTML;
?><?PHP
        return trim($str);
    }
	
    public static function Title($title = ''){
		return "<title>$title</title>";
	
	}
	
    public static function AjaxCheckInput($InputName,$Url){
		$jstag = 'script'; 
		return "<$jstag type='text/javascript'>_08cms_validator.init('ajax','$InputName',{url:'$Url'});</$jstag>";
	}
	
    public static function DirectUrl($Url){
		return "<html><head><meta http-equiv=\"expires\" content=\"-1\"><meta http-equiv=\"refresh\" content=\"0;url=".cls_env::mconfig('cms_abs').$Url."\"></head></html>";
	}
	
    /**
     * 获取编辑器插件按钮
     * 
     * @param  string $plugins_names      开启的编辑器插件按钮名称
     * @param  string $varname            编辑器使用的字段名称
     * 
     * @return string                返回获取到的插件按钮
     */
    public static function getEditorPlugins( $plugins_names, $varname )
    {
        $plugins_button = '<div class="_08_plugins_button">';
        if ( is_string($plugins_names) )
        {
            $plugins_names = array_filter(explode(',', $plugins_names));
        }
        else
        {
            $plugins_names = (array) $plugins_names;
        }
        
        # 如果未传递时默认开启一个分页管理插件
		if ( empty($plugins_names) && (defined('M_ADMIN')||defined('M_MCENTER')) )
        {
            $plugins_names = array('08cms_paging_management');
        }
        else
        {
        	$plugins_names = array_map('trim', $plugins_names);
        }
        
        $gets = cls_env::_GET('handlekey');
        $current_wid = (int) @$gets['handlekey'];
        foreach ( $plugins_names as $name ) 
        {
            if ( isset(self::$__editorPluginsMap[$name]) )
            {
                $plugins_name = self::$__editorPluginsMap[$name];
                $name = str_replace('08cms_', '', preg_replace('/[^\w]/', '', $name));
                $url = cls_env::mconfig('cmsurl') . _08_Http_Request::uri2MVC("editor={$name}&varname={$varname}&parent_wid={$current_wid}");
                $plugins_button .= <<<HTML
    <a title="{$plugins_name}" class="_08_plugins_button" onclick="return floatwin('open_{$name}',this)" href="{$url}">{$plugins_name}</a>
HTML;
            }
        }
        $plugins_button .= '</div>';
        
        return $plugins_button;
    }
    
    /**
     * 编辑器插件Map
     * 
     * @var   array
     * @since nv50
     */
    public static $__editorPluginsMap = array(
        '08cms_paging_management' => '分页标题管理',
        '08cms_hangqing' => '汽车行情',
        '08cms_chetu' => '汽车图片',
        '08cms_house_info' => '楼盘信息',
        '08cms_plot_pigure' => '选小区图',
        '08cms_size_chart' => '选户型图'                
    );
    
    /**
     * 生成复制代码
     * 
     * @param string $value 要被复制的值
     * @param string $label 显示代码前的标签
     **/
    public static function createCopyCode($id, $value, $label = '')
    {
		$cms_abs = _08_CMS_ABS; 
        $value = rawurlencode(cls_string::iconv(cls_env::getBaseIncConfigs('mcharset'),'utf-8',$value));
		$csflag = 'script'; //echo $value;
		
return <<<HTML
<$csflag src="$cms_abs/images/common/swfCopy/copyfuncs.js"></$csflag>
<span id="copySwfID_$id"></span>
<$csflag type="text/javascript">
var copyData_$id = "$value";
function copySuccess(){ 
	//如果有多个copy, 设置外部变量var copySwf_cbackID=1;, 使显示提示信息不跟随最后一个button
	if(typeof(copySwf_cbackID)=='undefined'){
		var showid = 'copySwfID_$id';
		layer.tips('复制成功！', '#'+showid, {style:['background-color:#134d9d;color:#FFF;','#134d9d'], time:1}); 
	}else{
		layer.msg('复制成功！',1);
	}
}
$(document).ready(function(){
	loadRun('/images/common/swfCopy/swfobject.js',"copyReset(copyData_$id,'copySwfID_$id',{ isVal:1, noEnc:1 });");
});
</$csflag>
HTML;

    }
    
    /**
     * 可处理的HTML标签数组
     * 
     * @return array 返回可处理的HTML标签数组
     **/
    public static function getDealHtmlTagsMap()
    {
        return array(
            'a' => '链接 <a', 
            'tbody' => '表格体 <tbody', 
            'form' => '表单 <form', 
            'table' => '表格 <table', 
            'img' => '图片 <img', 
            'frame' => '框架 <frame', 
            'tr' => '表格行 <tr', 
            'script' => '脚本 <script', 
            'li,ul,dd,dt' => '框架 <li<ul<dd<dt', 
            'td' => '单元 <td', 
            'b,strong' => '加粗 <b<strong', 
            'tab' => '换行|Tab \r\n\t', 
            'p' => '段落 <p', 
            'br' => '换行 <br', 
            'trim' => '去首尾空白字符', 
            'font' => '字体 <font', 
            'nbsp' => '空格 &nbsp;', 
            'iframe' => '框架 <iframe', 
            'div' => '层 <div', 
            'h' => 'H标签 <h1-7', 
            'sub,sup' => '上下标 <sub<sup', 
            'span' => 'Span <span', 
            'hr' => 'hr标签 <hr', 
            'all' => '所有标签'
        );
    }
}
