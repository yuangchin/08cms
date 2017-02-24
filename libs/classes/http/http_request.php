<?php
/**
 * HTTP请求操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

_08_Loader::import(_08_INCLUDE_PATH . 'http.cls');
class _08_Http_Request extends Http
{
    /**
     * 重定向
     * 
     * @param string $url        要跳转的目标URL
     * @param bool   $terminate  是否更换的前一类似的报头，如果想强制多个相同类型的头请设置为false
     * @param int    $statusCode HTTP状态码
     */
    public function redirect( $url, $terminate = true, $statusCode = 302 )
    {
        if( 0 === strpos($url,'/') )
        {
            $url = _08_CMS_ABS . substr($url, 1);
        }
        
        header('Location: '.$url, $terminate, $statusCode);
    }
    
    /**
     * cURL并发获取资源，功能有点类似于多线程，但要注意这与多线程是不同的
     * 
     * @param  mixed $params  要获取资源的链接信息参数
     * @param  int   $timeOut 超时时间值
     * @param  bool  $getInfo 是否返回连接资源句柄信息，TRUE 返回，FALSE 不返回
     * @return array          返回获取到的资源链接
     * 
     * @example $contents = _08_Http_Request::getResources('http://www.baidu.com/', 1);
     * 
                $contents = _08_Http_Request::getResources(array('http://www.baidu.com/', 'http://www.google.com.hk/'), 1);
                
                // 该调用方法参数可以不对应，但urls必须存在
                // 未定义'method'时默认为 GET, postData 可以是GET或DELETE方法时的URL，也可以是POST时的数据
                // timeOut按urls对齐，如果未设置则自动使用getResources方法参数二的值
                $contents = _08_Http_Request::getResources(
                    array( 'urls' => array('http://www.baidu.com/', 'http://www.google.com.hk/'), 
                           'timeOut' => array(5),
                           'method' => 'POST',
                           'postData' => array('test' => 'postdatas') )
                );
     *
     * @since 1.0
     */
    public static function getResources( $params, $timeOut = 5, $getInfo = false )
    {
        $responses = array();
        if ( $_params = self::getCurlParams($params) )
        {
            $queue = curl_multi_init();
            $map = array();
            
            foreach ($_params['urls'] as $key => $url)
            {
                if ( empty($url) ) { continue; }
                
                $method = (isset($_params['method'][$key]) ? strtoupper($_params['method'][$key]) : 'GET');
                $ch = curl_init();
                
                if( !empty($_params['postData'][$key]) )
                {
                    if ( $method == 'POST' )
                    {
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $_params['postData'][$key]);                    
                    }
                    else if ( in_array($method, array('GET', 'DELETE')) )
                    {
                        $method == 'DELETE' && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                        $url .= (strpos($url, '?') ? '&' : '?') . 
                                (is_array($_params['postData'][$key]) ? http_build_query($_params['postData'][$key]) : $_params['postData'][$key]);
                    }
                }
     
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, isset($_params['timeOut'][$key]) ? (int)$_params['timeOut'][$key] : $timeOut);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_NOSIGNAL, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSLVERSION, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.2; rv:27.0) Gecko/20100101 Firefox/27.0 FirePHP/0.7.4');
            
                # 向curl批处理会话中添加单独的curl句柄
                curl_multi_add_handle($queue, $ch);
                $map[(string) $ch] = md5($url);
            }
            
            do
            {
                while ( ($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM )
                {
                    continue;
                }
         
                if ($code != CURLM_OK) { break; }
         
                # 对刚刚完成的请求进行相关传输信息分析
                while ($done = curl_multi_info_read($queue))
                {
                    if ( $getInfo )
                    {
                        # 获取一个cURL连接资源句柄信息
                        $info = curl_getinfo($done['handle']);
                        $responses[$map[(string) $done['handle']]]['info'] = $info;
                    }
                    
                    # 返回一个保护当前会话最近一次错误的字符串
                    $error = curl_error($done['handle']);
                    if ( !empty($error) )
                    {
                        $responses[$map[(string) $done['handle']]]['error'] = $error;
                    }
                    
                    $results = curl_multi_getcontent($done['handle']);
                    $responses[$map[(string) $done['handle']]]['results'] = $results;
         
                    # 移除刚刚完成的句柄资源
                    curl_multi_remove_handle($queue, $done['handle']);
                    curl_close($done['handle']);
                }
         
                // 等待所有cURL批处理中的活动连接
                if ($active > 0)
                {
                    curl_multi_select($queue, 0.5);
                }
            }
            while ($active);
         
            curl_multi_close($queue);
        }
        
        # 如果只有数据资源时，只返回资源数据
        if ( count($responses) == 1 )
        {
            $responses = $responses[key($responses)]['results'];
        }
        
        return $responses;
    }
    
    /**
     * 获取CURL处理参数
     * 
     * @param mixed $params 传递
     */
    private static function getCurlParams( $params )
    {
        if ( empty($params) )
        {
            return false;
        }
        
        $_params = array();
        if ( is_array($params) )
        {
            if ( isset($params['urls']) )
            {
                # 一次获取多个网址的资源
                if ( is_array($params['urls']) )
                {
                    $_params = $params;
                }
                else
                {
                    # 一个网址中有多个参数
                    foreach ( $params as $key => $param ) 
                    {
                        if ( is_array($param) )
                        {
                            $_params[$key][] = $param;
                        }
                        else
                        {
                        	$_params[$key] = (array) $param;
                        }
                    }
                }
            }
            else # 传递一维数组的多个网址
            {
                $_params['urls'] = $params;
            }
        }
        else # 传递字符串型的单个网址
        {
            $_params['urls'][] = (string) $params;
        }
        
        return $_params;
    }
    
    /**
     * 把URI转成MVC路由的URI
     * 注：转换后的URI后面如果没有 / 会自动增加一个 /
     * 
     * @param  mixed  $uri         要转换的原始URI，可用字符串或数组传递，但参数一的键和值必需是控制器名和action名
     * @param  bool   $addFileName 是否要增加路由文件名称，默认为增加，传递false为不增加
     * @return string              返回转换后的MVC架构URI
     * 
     * @since  nv50
     */
    public static function uri2MVC ( $uri, $addFileName = true )
    {
        $split = '/';
        if ( is_array($uri) )
        {
            $uriString = '';
            foreach ( $uri as $key => $value ) 
            {
                if ( !empty($uriString) )
                {
                    $uriString .= $split;
                }
                
                $uriString .= ($key . $split . $value);
            }
            
            $uri = $uriString;
        }
        else
        {
            $uri = str_replace(array('&', '='), $split, (string) $uri);
        }        
        
        # 如果要添加路由入口文件则添加
        if ( $addFileName && defined('_08_ROUTE_ENTRANCE') )
        {
            $uri = _08_ROUTE_ENTRANCE . (substr($uri, 0, 1) == $split ? substr($uri, 1) : $uri);
        }
        
        # 在URI最后减少一个 /
        if ( substr($uri, strlen($uri) - 1) == $split )
        {
            $uri = substr($uri, 0, -1);
        }
        
        return $uri;
    }
}