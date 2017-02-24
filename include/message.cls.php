<?php
/**
 * 信息提示模板类
 * floatwin Demo（该例程为提交表单后同时打印三个按钮并点击要跳转的地址）: 
 *  cls_message::show('副件信息编辑完成', array(
        '返回上一步' => 'history.go(-1)',
        '返回列表页' => axaction(64, '?entry=extend&extend=members'),
        '关闭窗口' => axaction(2)
    ));
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
class cls_message
{
    protected static $_instance = null;
    
    protected $_params = array();
    
    /**
     * 显示信息
     * 如果要显示多个按钮可直接这样调用：
     * cls_message::show(
     *      '提示信息提示信息提示信息', 
     *      array('返回上一步' => '?return=go-back', '返回列表页' => '?return=go-list', '关闭窗口' => '?return=go-close')
     * );
     * 
     * @param string $str   要打印的信息
     * @param mixed  $url   打印信息后跳转的URL，该参数可为数组，如果为数组时则会在信息的下面打印按钮。
     * @param int    $mtime 显示信息时停留的时间
     */
    public static function show( $str='', $urls = '', $mtime = 1250 )
    {
        global $inajax, $infloat, $handlekey;
        if (defined('_08CMS_AJAX_EXEC'))
        {
            /**
             * 让cls_message兼容MVC的AJAX脚本，注cls_message会返回一个JSON格式的状态数据：
             * {'error'=> '错误信息', 'message'=>'')如果URL里带一个callback=funName 时该JSON格式的数据会被这个callback调用，
             * 如：  funName({'error'=>'错误信息', 'message'=>''});
             **/
            $gets = cls_env::_GET('callback');
            $status = array('error' => $str, 'message' => '');
            $ajax = _08_C_Ajax_Controller::getInstance();
            if (!empty($gets['callback']))
            {
                $status = $ajax->format($status, $gets['callback']);
            }
            else
            {
            	$status = $ajax->format($status);
            }
            
            exit($status);
        }
        
		if (!empty($inajax))
        {
			self::ajax_info($str);
		}
       
//        empty($urls) && empty($infloat) && $urls = M_REFERER;
        # 在此方法里把 $this 转成 self::$_instance 方式调用
        self::setInstance();
        self::_setParamsToTpl(
            array( 'str' => (string)$str, 'urls' => $urls, 'mtime' => (int) $mtime, 
                   'infloat' => (int)$infloat, 'handlekey' => (int)$handlekey )
        );
        
        if ( defined('M_ADMIN') )
        {
            self::$_instance->_amessage();
        }
        else if ( defined('M_MCENTER') )
        {
            self::$_instance->_mcmessage();
        }
        else
        {
            self::$_instance->_message();
        }
    }
   
    # 后台
    protected function _amessage()
    {
    	global $amsgforwordtime;	
    	empty($amsgforwordtime) || $this->_params['mtime'] = $amsgforwordtime;  
        if ( !function_exists('aheader') || !function_exists('afooter') )
        {
            include _08_INCLUDE_PATH . 'admina.fun.php';
            function_exists('aheader') && aheader();
        }
        $this->setButton();
        
        $this->_display();
    }
    
    #会员中心
    protected function _mcmessage()
    {
    	defined('MMSGFORWORDTIME') && ($this->_params['mtime'] = defined('MMSGFORWORDTIME'));  
    	$this->_params['no_mcfooter'] = defined('NO_MCFOOTER');    
             
        $this->setButton();
        $this->_params['str'] .= '&nbsp; <a href="javascript:window.close();"'.($this->_params['infloat']?" onclick=\"return floatwin('close_" . $this->_params['handlekey'] . "')\"":'').'>[关闭窗口]</a>';
        $this->_display();
    }
    
    # 前台
    protected function _message()
    {
		$cms_abs = cls_env::mconfig('cms_abs');
		$mcharset = cls_env::getBaseIncConfigs('mcharset');
		$msgforwordtime = cls_env::mconfig('msgforwordtime');
		empty($msgforwordtime) || $this->_params['mtime'] = $msgforwordtime;  
        
        self::_setParamsToTpl(
            array('cms_abs' => $cms_abs, 'mcharset' => $mcharset )
        );
        if ( !empty($this->_params['infloat']) )
        {
            $this->_params['str'] .= cls_phpToJavascript::str_js_src("{$cms_abs}include/js/floatwin.js");
        }
        
        $this->setButton();
        
        if ( !is_array($this->_params['urls']) )
        {
            $this->_params['str'] .= "<a href=\"javascript:\" onclick=\"return top.floatwin?top.floatwin('close_" . $this->_params['handlekey'] . "'):window.close()\">[关闭窗口]</a>";
        }
        
        $this->_params['str'] = "<br><br>{$this->_params['str']}<br><br>";
        
        $this->_display();
    }
    
    /**
     * 设置按钮提示信息
     * 该方法看起来有点乱，但为兼容以前调用，目前只能暂时这样处理
     */
    private function setButton()
    {
        if ( empty($this->_params['urls']) ) return false;
        if ( is_array($this->_params['urls']) ) { return $this->setButtons(); }
        $url = $this->_params['urls'];
        
        $this->_params['str'] .= '<br />';
        if(preg_match('/^javascript:/',$url)) {  # 让以前只能调用axaction方法的情况也能直接传递JS，即用不用浮动窗都通用
			$this->_params['str'] .= "<script type=\"text/javascript\" reload=\"1\">var t = " . $this->_params['mtime'] . ";".substr($url,11)."</script>";
		} else if ( false !== strpos($url,'history') ) { # 传递参数为：history.go(-1)返回上一步的情况
        	$this->_params['str'] .= "<br /><br /><a href=\"javascript:$url\">如果浏览器没有跳转请点这里</a><script>setTimeout('$url', " . $this->_params['mtime'] . ");</script>";
        } else if(strpos($this->_params['str'],'返回') === false && !defined('M_ADMIN') ){ # 前台返回按钮
            $url = cls_env::repGlobalURL($url);
       		$this->_params['str'] .= "<br /><br /><a href=\"$url\">[立即跳转]</a><script>setTimeout(\"window.location.replace('$url');\", ". $this->_params['mtime'] .");</script>&nbsp; ";
        } else {
            $url = cls_env::repGlobalURL($url);
            if ( empty($this->_params['infloat']) )
            {
                $this->_params['str'] .= "<br /><br /><a href=\"$url\">如果浏览器没有跳转请点这里</a><script>setTimeout(\"redirect('$url');\", " . $this->_params['mtime'] . ");</script>";
            }
			else
            {
               	$this->_params['str'] .= "<a href=\"$url\" onclick=\"return floatwin('update_" . $this->_params['handlekey'] . "', this);\">如果浏览器没有跳转请点这里</a><script type=\"text/javascript\" reload=\"1\">setDelay(\"floatwin('update_" . $this->_params['handlekey'] . "', '$url');\", " . $this->_params['mtime'] . ");</script>";
            }
		}
    }
    
    /**
     * 打印提示信息
     */
    protected function _display()
    {
        global $message_class;
    	$this->_params['class'] = empty($message_class) ? 'tabmain' : $message_class;
        _08_Loader::import(_08_INCLUDE_PATH . 'message_tpl.cls', $this->_params);
    }
    
    /**
     * 生成多个按钮
     */
    private function setButtons()
    {
        if ( empty($this->_params['urls']) ) return false;
        
		$this->_params['str'] .= '<br /><br /><br />';
		$i = 1;
        foreach ( (array) $this->_params['urls'] as $message => $url )
        {
            if( (false !== strpos($url, 'history.')) || (false !== strpos($url, 'window.')) )
            {
                $url = 'javascript:' . $url;
            } 
			$this->_params['str'] .= "　<a href=\"$url\">[$message]</a>"; //注意前面是个[全角空格],避免某些情况下会编码&nbsp;中的&字符
			$i == 1 && $this->_params['str'] .= "<script>setTimeout(\"window.location.replace('$url');\", " . $this->_params['mtime'] . ");</script>";
			$i ++;
        }
    }
    
    /**
     * 设置参数应用到信息模板
     * 
     * @param array $params 要设置的参数，key 为 变量名， value 为 值
     */
    protected static function _setParamsToTpl( array $params )
    {
        if ( !(self::$_instance instanceof self) )
        {
            return false;
        }
        
        foreach ($params as $key => $value) 
        {
            self::$_instance->_params[$key] = $value;
        }
    }
    
    # ajax
    public static function ajax_info($str, $format = 'XML', $param = array())
    {
    	global $mcharset,$callback;
        switch ( strtoupper($format) )
        {
           case 'CONTENT':
               if ( !empty($param['url']) )
               {
                   empty($param['timeout']) && $param['timeout'] = 2;
                   $jsString = <<<HTML
                   <br /><br /><span style="font-size:12px;">{$param['timeout']} 秒后自动跳转，<a href="{$param['url']}">如果浏览器没有跳转请点这里</a></span>
                   <script type="text/javascript">
                        setTimeout(function(){location.href="{$param['url']}";}, {$param['timeout']}000);
                   </script>
HTML;
               }
               else
               {
               	   $jsString = '';
               }
               
               
               exit('<span style="width:100%; text-align:center; display:block; margin-top: 60px">' . $str . $jsString . '</span>');
           case 'JSON':
               // TODO: JSON格式有待完成
               break;
        }
    	$callback && js_callback($str);
    	@header("Expires: -1");
    	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
    	@header("Pragma: no-cache");
    	header("Content-type: application/xml");
    	echo "<?xml version=\"1.0\" encoding=\"$mcharset\"?>\n<root><![CDATA[";
    	echo $str;
    	echo ']]></root>';
    	die();
    }
    
    protected function __construct(){}
    
    public static function setInstance()
    {
        if ( empty(self::$_instance) )
        {
            self::$_instance = new self();
        }
    }
}
