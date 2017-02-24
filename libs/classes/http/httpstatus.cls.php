<?php
/**
 * HTTP状态码操作类
 *
 * @author    Wilson <Wilsonnet@163.com>
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_HttpStatus
{
    private static $statuses = array (
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        102 => 'HTTP/1.1 102 Processing',
        
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        207 => 'HTTP/1.1 207 Multi-Status',
        
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        306 => 'HTTP/1.1 306 Switch Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => array (
                  'HTTP/1.1 404 Not Found',
                  'status: 404 Not Found'
               ),
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Timeout',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Long',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        418 => 'HTTP/1.1 418 I\'m a teapot',
        421 => 'HTTP/1.1 421 There are too many connections from your internet address',
        422 => 'HTTP/1.1 422 Unprocessable Entity',
        423 => 'HTTP/1.1 423 Locked',
        424 => 'HTTP/1.1 424 Failed Dependency',
        425 => 'HTTP/1.1 425 Unordered Collection',
        426 => 'HTTP/1.1 426 Upgrade Required',
        449 => 'HTTP/1.1 449 Retry With',
        
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Timeout',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
        506 => 'HTTP/1.1 506 Variant Also Negotiates',
        507 => 'HTTP/1.1 507 Insufficient Storage',
        509 => 'HTTP/1.1 509 Bandwidth Limit Exceeded',
        510 => 'HTTP/1.1 510 Not Extended',
        'P3P' => 'P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"',
    );
    
    /**
     * 打印HTTP状态码
     * 
     * @param int $code 状态码号
     * @since 1.0
     */ 
    public static function trace( $code )
    {
        if ( is_array($code) )
        {
            foreach ( $code as $key => $value ) 
            {
                @header($key . ':' . $value);              
            }
        }
        else
        {
            if ( is_array(self::$statuses[$code]) )
            {
                foreach(self::$statuses[$code] as $status)
                {
                    @header($status);
                }
            }
            else 
            {
                @header(self::$statuses[$code]);
            }
        }
        
        if ( class_exists('_08_SystemInfo') )
        {            
            $system = _08_SystemInfo::getInstance();
            $systemInfo = $system->getInfo();
            @header('X-Content-System-By: 08CMS ' . $systemInfo['cmsversion']);
        }
    }
}