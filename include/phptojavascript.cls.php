<?php
/**
 * PHP转成JavaScript操作类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_phpToJavascript
{
    /**
     * 插入指定内容到父窗口的指定节点处
     * 
     * @param string $parent_window_id 父窗口ID
     * @param string $dom_id           父窗口节点ID
     * @param string $string           要插入的内容
     * @param int    $caretpos         鼠标所在的父窗口坐标开始点
     *
     * @since 1.0
     */  
    public static function insertParentWindowString($parent_window_id, $dom_id, $string, $caretpos = 0, $flag = true)
    {
        echo '<script type="text/javascript">
                var obj = window.parent.document.getElementById("'.$parent_window_id.'").contentWindow;';
        if($flag) 
        {
            // 插入新标签设置信息
            echo "obj.insertTagStr('{$dom_id}', '$string', $caretpos);";
        }
        echo '</script>';
    }
    
    /**
     * 输出一段引入JQ文件的代码
     */
    public static function loadJQuery( $file_name = '', $return = false )
    {
        global $cms_abs;
    /*	echo self::str_js_src("{$cms_abs}images/common/$file_name");
        */
        if ( empty($file_name) )
        {
            $file_name = 'jquery-1.10.2.min.js';
        }
        $str = <<<EOT
        <script type="text/javascript">window.jQuery || document.write('<script src="{$cms_abs}images/common/$file_name"><\/script>');</script>
EOT;
?><?PHP
        if ( $return )
        {
            return $str;
        }
        else
        {
        	echo $str;
        }
    }
    
    /**
     * 组装一个JS文件，并返回
     * 
     * @param string $val JS文件的src源地址
     * @return string     返回组成好的JS调用代码
     */
    public static function str_js_src($val, $charset = '')
    {
		if(empty($charset)) $charset = cls_env::getBaseIncConfigs('mcharset');
    	return '<script type="text/javascript" src="' . $val . '" charset="'.$charset.'"></script>';
    }
	
    /**
     * 与ptool.php相关的JS调用代码($Params非数组则不生成ToolJS)
     * 
     * @param array			$Params		传参数组
     * @return string		返回组好的JS调用代码
     */
    public static function PtoolJS($Params = '')
    {
		if(is_array($Params)){
			$ParamStr = '';
			foreach($Params as $k => $v){
				$ParamStr .= is_numeric($k) ? "&$v" : "&$k=$v"; # 使用数字键名，传入a=*&b=*等已拼字串
			}
			if($ParamStr) $ParamStr = '?'.substr($ParamStr,1);
			return defined('IN_MOBILE') ? self::str_js_src(cls_env::mconfig('cms_abs').'tools/mptool.php'.$ParamStr) : self::str_js_src(cls_env::mconfig('cms_abs').'tools/ptool.php'.$ParamStr);
		}else return '';
    }
    
    /**
     * 打印快捷登录需要用的JS数据
     */ 
    public static function showOtherBind()
    {
        echo '<script type="text/javascript"> var urls = ' . new otherSiteBind() . ';';
        echo <<<EOT
            var childWindow;
            function OtherWebSiteLogin(type, width, height)
            {
                if(urls[type] == 'close')
                {
                    alert('该登录功能已经关闭！');
                    return false;
                }
                else
                {
                    childWindow = window.open(urls[type], type, "width=" + width + ",height=" + height + ",left="+((window.screen.availWidth-width)/2)+",top="+((window.screen.availHeight-height)/2));
                }
            }
            </script>
EOT;
?><?PHP
    }
    
    #加载广告代码
    public static function LoadAdv()
    {
        $cms_abs = _08_CMS_ABS;
        $str = self::loadJQuery('', true);
     #   $str .= self::str_js_src($cms_abs . 'include/js/common_footer.js');
        $str .= self::str_js_src($cms_abs . 'include/js/common_footer.min.js');
        return $str;
    }
    
    /**
     * 将正常代码转为JS格式代码
     * 
     * @param string		$Content		来源字串
     * @return string		返回转为JS格式的代码
     */
    public static function JsFormat($Content = ''){
		if(!$Content) return $Content; //可能是:''或0； 如果是0则要返回0而不是''
		$Content = trim(addcslashes($Content, "'\\\r\n"));
		return $Content;
    }
    /**
     * 将正常代码封装为document.write的JS代码
     * 
     * @param string		$Content		来源字串
     * @return string		返回转为JS格式的代码
     */
    public static function JsWriteCode($Content = ''){
		$Content = cls_phpToJavascript::JsFormat($Content);
		$Content = "document.write('". $Content ."');";
		return $Content;
    }
    
    /**
     * 把默认的UC/PHPWind同步请求转成AJAX形式
     * 
     * @param string $contents 默认的UC同步请求字符串
     **/
    public static function toAjaxSynchronousRequest($contents)
    {
        if (preg_match_all('/src="(.*)"/isU', $contents, $src))
        {
            foreach ($src[1] as $_src)
            {
                echo <<<JS
                var _document = parent.document;
                if (!_document)
                {
                    _document = document;
                }
                var _script = _document.createElement('script');
                _script.type = 'text/javascript';
                _script.src = '$_src';
                document.body.appendChild(_script);
                
JS;
            }
        }
    }
}